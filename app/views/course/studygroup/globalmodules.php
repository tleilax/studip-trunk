<?php

/* * * * * * * * * * * * *
 * * * I N F O B O X * * *
  * * * * * * * * * * * * */
$infobox['picture'] = 'groups.jpg';
$infobox['content'] = array(
    array(
        'kategorie' => _("Information"), 
        'eintrag'   => array(
            array(
                'text' => 'Hier k�nnen Sie angeben, welche Module/Plugins in Studiengruppen verwendet werden d�rfen.',
                'icon' => 'ausruf_small.gif'
            )
        )
    )   
);

/* * * * * * * * * * * *
 * * * O U T P U T * * * 
 * * * * * * * * * * * */

$cssSw = new cssClassSwitcher();

?>

<?= $this->render_partial("course/studygroup/_feedback") ?>
<? if (!$configured): ?>
    <?= MessageBox::info(_('Keine Veranstaltungsart f�r Studiengruppen gefunden'),
        array(sprintf(_('Die Standardkonfiguration f�r Studiengruppen in der Datei <b>%s</b> fehlt oder ist unvollst�ndig.'),
                'config.inc.php'))) ?>
<? endif ?>
<? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
    <?= MessageBox::info( _("Die Studiengruppen sind derzeit <b>nicht</b> aktiviert.") 
            . '<br>'. _("Zum Aktivieren f�llen Sie bitte das Formular aus und klicken Sie auf \"Speichern\".")); ?>
<? else: ?>
    <? if ($can_deactivate) : ?>
        <?= MessageBox::info( _("Die Studiengruppen sind aktiviert.")) ?>
        <form action="<?= $controller->url_for('course/studygroup/deactivate') ?>" method="post">
        <?= makebutton('deaktivieren', 'input') ?>
        </form>
    <? else: ?>
        <?= MessageBox::info(_("Sie k�nnen die Studiengruppen nicht deaktivieren, solange noch welche in Stud.IP vorhanden sind!")) ?>
    <? endif; ?>
    <br />
<?php endif;?>
<form action="<?= $controller->url_for('course/studygroup/savemodules') ?>" method="post">
    <!-- Title -->
<table class="default">
    <tr>
        <th colspan="2"><b><?= _("Aktivierbare Module / Plugins") ?></b></th>
    </tr>
    <?= $cssSw->switchClass(); ?>
    <tr>
        <td <?= $cssSw->getFullClass() ?>> <?=_("TeilnehmerInnen") ?> </td>
        <td <?= $cssSw->getFullClass() ?>> <?=_("immer aktiv")?> </td>
    </tr>

    <!-- Modules / Plugins -->
<? if (is_array($modules)) foreach( $modules as $key => $name ) : 
    if (in_array($key, array('participants', 'schedule'))) continue; 
    $cssSw->switchClass(); ?>

    <tr>
        <td <?= $cssSw->getFullClass() ?>> <?= $name ?> </td>
        <td <?= $cssSw->getFullClass() ?>>
            <select name='modules[<?= $key ?>]'>
                <? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
                <option value='invalid' selected><?= _("-- bitte ausw�hlen --")?></option>
                <? endif ?>
                <option value='on' <?= $enabled[$key] ? 'selected' : '' ?>><?= _("aktivierbar")?></option>
                <option value='off' <?= $enabled[$key] ? '' : 'selected' ?>><?= _("nicht aktivierbar")?></option>
            </select>
        </td>
    </tr>

<? endforeach; ?>
</table>
    <br />
    <? $cssSw->resetClass(); ?>
    <!-- Title -->
<table class="default">
    <tr>
        <th colspan="2"> <b><?= _("Einrichtungszuordnung") ?></b> </th>
    </tr>
    <tr>
        <td <?= $cssSw->getFullClass() ?>>
            <?= _("Alle Studiengruppen werden folgender Einrichtung zugeordnet:") ?><br>
        </td>
        <td <?= $cssSw->getFullClass() ?>>
            <select name="institute">
            <? if (!Config::getInstance()->getValue('STUDYGROUPS_ENABLE')):?>
                <option value='invalid' selected><?= _("-- bitte ausw�hlen --")?></option>
            <? endif ?>
            <? foreach ($institutes as $fak_id => $faculty) : ?>
                <option value="<?= $fak_id ?>" style="font-weight: bold" 
                    <?= ($fak_id == $default_inst) ? 'selected="selected"' : ''?>>
                    <?= htmlReady(my_substr($faculty['name'], 0, 60)) ?>
                </option>
                <? foreach ($faculty['childs'] as $inst_id => $inst_name) : ?>
                <option value="<?= $inst_id ?>"
                    <?= ($inst_id == $default_inst) ? 'selected="selected"' : ''?>>
                    <?= htmlReady(my_substr($inst_name, 0, 60)) ?>
                </option>
                <? endforeach; ?>
            <? endforeach; ?>
            </select>
        </td>
    </tr>
</table>

<br />
    
<? $cssSw->resetClass(); ?>
<!-- Title -->
<table class="default">
    <tr>
        <th colspan="2"> <b><?= _("Nutzungsbedingugen") ?></b> </th>
    </tr>
        <td colspan="2" <?= $cssSw->getFullClass() ?>>
        <?= _("Geben Sie hier Nutzungsbedingungen f�r die Studiengruppen ein. ".
                "Diese m�ssen akzeptiert werden, bevor eine Studiengruppe angelegt werden kann.") ?>
        </td>
    </tr>
    <? $cssSw->switchClass(); ?>
    <tr>
        <td colspan="2" <?= $cssSw->getFullClass() ?>>
        <br />
        <textarea name="terms" style="width: 90%" rows="10" style='align:middle;'><?= $terms ?></textarea>
        <br />
        </td>
    </tr>
</table>
<p style="text-align: center">
    <br>
    <input type="image" <?= makebutton('speichern', 'src') ?>>
</p>
</form>
