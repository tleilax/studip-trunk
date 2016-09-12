<? use Studip\Button; ?>

<h3 style="text-align: center;"><?= _('Ich studiere folgende F�cher und Abschl�sse:') ?></h3>

<? if ($allow_change['sg']): ?>
<form action="<?= $controller->url_for('settings/studies/store_sg') ?>" method="post">
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
<? endif; ?>
<? $modulemanagement_enabled = PluginEngine::getPlugin('MVVPlugin'); ?>
<table class="default" id="select_fach_abschluss">
    <colgroup>
        <col>
        <col>
        <?= $modulemanagement_enabled ? '<col>' : '' ?>
        <col width="100px">
        <col width="100px">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Fach') ?></th>
            <th><?= _('Abschluss') ?></th>
            <? if ($modulemanagement_enabled) : ?>
            <th id="version_label"><?= _('Versionen') ?></th>
            <? endif; ?>
            <th id="fachsemester_label"><?= _('Fachsemester') ?></th>
            <th style="text-align:center;" id="austragen_label">
            <? if ($allow_change['sg']): ?>
                <?= _('austragen') ?>
            <? else: ?>
                &nbsp;
            <? endif; ?>
            </th>
        </tr>
    </thead>
    <tbody>
    <? if (count($about->user_fach_abschluss) === 0 && $allow_change['sg']): ?>
        <tr>
            <td colspan="<?= $modulemanagement_enabled ? '5' : '4' ?>" style="background: inherit;">
                <strong><?= _('Sie haben sich noch keinem Studiengang zugeordnet.') ?></strong><br>
                <br>
                <?= _('Tragen Sie bitte hier die Angaben aus Ihrem Studierendenausweis ein!') ?>
            </td>
        <tr>
    <? endif; ?>
    <? foreach ($about->user_fach_abschluss as $details): ?>
        <tr>
            <td><?= htmlReady($details['studycourse_name']) ?></td>
            <td><?= htmlReady($details['degree_name']) ?></td>
        <? if ($allow_change['sg']): ?>
            <? if ($modulemanagement_enabled) : ?>
            <td>
                <? $versionen = StgteilVersion::findByFachAbschluss($details['fach_id'], $details['abschluss_id']); ?>
                <? $versionen = array_filter($versionen, function ($ver) {
                    return $ver->hasPublicStatus('genehmigt');
                }); ?>
                <? if (count($versionen)) : ?>
                <select name="change_version[<?= $details['fach_id'] ?>][<?= $details['abschluss_id'] ?>]" aria-labelledby="version_label">
                    <option value=""><?= _('-- Bitte Version ausw�hlen --') ?></option>
                <? foreach ($versionen as $version) : ?>
                    <option<?= $version->getId() == $details['version_id'] ? ' selected' : '' ?> value="<?= htmlReady($version->getId()) ?>">
                        <?= htmlReady($version->getDisplayName()) ?>
                    </option>
                <? endforeach; ?>
                </select>
                <? else : ?>
                <?= tooltipIcon(_('Keine Version in der gew�hlten Fach-Abschluss-Kombination verf�gbar.'), true) ?>
                <? endif; ?>
            </td>
            <? endif; ?>
            <td>
                <select name="change_fachsem[<?= $details['fach_id'] ?>][<?= $details['abschluss_id'] ?>]" aria-labelledby="fachsemester_label">
                <? for ($i = 0; $i <= 50; $i += 1): ?>
                    <option <? if ($i == $details['semester']) echo 'selected'; ?>><?= $i ?></option>
                <? endfor; ?>
                </select>
            </td>
            <td style="text-align:center">
                <input type="checkbox" aria-labelledby="austragen_label" name="fach_abschluss_delete[<?= $details['fach_id'] ?>]" value="<?= $details['abschluss_id'] ?>">
            </td>
        <? else: ?>
            <td><?= htmlReady($details['semester']) ?></td>
            <td>
                <?= Icon::create('accept', 'inactive')->asImg(['class' => 'text-top']) ?>
            </td>
        <? endif; ?>
        </tr>
    <? endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="<?= $modulemanagement_enabled ? '5' : '4' ?>">
            <? if ($allow_change['sg']): ?>
                <p>
                    <?= _('W�hlen Sie die F�cher, Abschl�sse und Fachsemester in der folgenden Liste aus:') ?>
                </p>
                <p>
                    <a name="studiengaenge"></a>
                    <select name="new_studiengang" id="new_studiengang" aria-label="<?= _('-- Bitte Fach ausw�hlen --')?>">
                        <option selected value="none"><?= _('-- Bitte Fach ausw�hlen --')?></option>
                        <? foreach ($faecher as $fach) :?>
                            <?= sprintf('<option value="%s">%s</option>', $fach->id, htmlReady(my_substr($fach->name, 0, 50)));?>
                        <? endforeach?>
                    </select>

                    <a name="abschluss"></a>
                    <select name="new_abschluss" id="new_abschluss" aria-label="<?= _('-- Bitte Abschluss ausw�hlen --')?>">
                        <option selected value="none"><?= _('-- Bitte Abschluss ausw�hlen --')?></option>
                        <? foreach ($abschluesse as $abschluss) :?>
                            <?= sprintf('<option value="%s">%s</option>' . "\n", $abschluss->id, htmlReady(my_substr($abschluss->name, 0, 50)));?>
                        <? endforeach?>
                    </select>

                    <a name="semester"></a>
                    <select name="fachsem" aria-label="<?= _("Bitte Fachsemester w�hlen") ?>">
                    <? for ($i = 1; $i <= 50; $i += 1): ?>
                        <option><?= $i ?></option>
                    <? endfor; ?>
                    </select>
                </p>

                <p>
                    <?= _('Wenn Sie einen Studiengang wieder austragen m�chten, '
                         .'markieren Sie die entsprechenden Felder in der oberen Tabelle.') ?>
                    <?= _('Mit einem Klick auf <b>�bernehmen</b> werden die gew�hlten �nderungen durchgef�hrt.') ?><br>
                    <br>
                    <?= Button::create(_('�bernehmen'), 'store_sg', array('title' => _('�nderungen �bernehmen'))) ?>
                </p>
            <? else: ?>
                <?= _('Die Informationen zu Ihrem Studiengang werden vom System verwaltet, '
                     .'und k�nnen daher von Ihnen nicht ge�ndert werden.') ?>
            <? endif; ?>
            </td>
        </tr>
    </tfoot>
</table>
<? if ($allow_change['sg']): ?>
</form>
<? endif; ?>
