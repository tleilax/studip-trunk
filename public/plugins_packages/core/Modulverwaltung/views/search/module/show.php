<table class="mvv-modul-details">
    <tr>
        <th class="mvv-modul-details-head" width="30%"><?= $modul->code ?></td>
        <th class="mvv-modul-details-head" width="30%"><?= $institut->name ?></td>
        <th class="mvv-modul-details-head" width="40%"><?= sprintf("%d CP", $modul->kp) ?> </td>
    </tr>
    <tr>
        <td colspan="2">
            <?= $modul->getDisplayName() ?><br>
            <?= _('Lehrveranstaltungen') ?> <?= $semester['name'] ?>
        </td>
        <td>
            <dl>
            <? foreach ($modulVerantwortung as $gruppe): ?>
                <dt><?= htmlReady($gruppe['name']) ?></dt>
                <? foreach ($gruppe['users'] as $user): ?>
                    <dd><?= htmlReady($user['name']) ?></dd>
                <? endforeach; ?>
                </dl>
            <? endforeach; ?>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 0;">
            <table style="margin: -1px; padding: 0; border-collapse: collapse;">
                <? if (strlen($teilnahmeVoraussetzung) > 0): ?>
                    <tr>
                        <th width="20%"><?= _('Teilnahmevoraussetzungen') ?></th>
                        <td ><?= htmlReady($teilnahmeVoraussetzung) ?></td>
                    </tr>
                <? endif; ?>
                <? if (strlen($deskriptor->kommentar)) : ?>
                    <tr>
                        <th><?= _('Hinweise') ?></th>
                        <td><?= formatReady($deskriptor->kommentar) ?></td>
                    </tr>
                <? endif; ?>
                <? if (strlen($deskriptor->ersatztext) > 0): ?>
                    <tr>
                        <th></th>
                        <td><?= formatReady($deskriptor->ersatztext) ?></td>
                    </tr>
                <? else: ?>

                    <? if ($modul->kapazitaet > 0): ?>
                        <tr>
                            <th><?= _('Kapazit�t Modul') ?></th>
                            <td>
                                <?= $modul->kapazitaet ?>
                                <? if (strlen($deskriptor->kommentar_kapazitaet) > 0): ?>
                                    (<?= formatReady($deskriptor->kommentar_kapazitaet) ?>)
                                <? endif; ?>

                            </td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($pruef_ebene) > 0): ?>
                        <tr>
                            <th><?= _('Pr�fungsebene') ?></th>
                            <td><?= $pruef_ebene ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($deskriptor->pruef_vorleistung)) : ?>
                        <tr>
                            <th><?= _('Pr�fungsvorleistung Modul') ?></th>
                            <td><?= formatReady($deskriptor->pruef_vorleistung) ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($deskriptor->pruef_leistung)) : ?>
                        <tr>
                            <th><?= _('Pr�fungsleistung Modul') ?></th>
                            <td><?= formatReady($deskriptor->pruef_leistung) ?></td>
                        </tr>
                    <? endif; ?>
                    <? if (strlen($deskriptor->kompetenzziele)): ?>
                        <tr>
                            <th><?= _('Kompetenzziele') ?></th>
                            <td><?= formatReady($deskriptor->kompetenzziele) ?></td>
                        </tr>
                    <? endif; ?>

                <? endif; ?>
            </table>
        </td>
    </tr>

    <? if ($type !== 3): ?>
        <tr>
            <? if ($type === 1): ?>
                <th><?= _('Modulteile') ?></th>
            <? endif; ?>
            <th <? if ($type === 2): ?> colspan="3" <? endif; ?> ><?= _('Semesterveranstaltungen') ?></th>
            <? if ($type === 1): ?>
                <th><?= _('Pr�fungsleistung') ?></th>    
            <? endif; ?>
        </tr>

        <? foreach ($modulTeile as $lvGruppe): ?>
            <tr>
                <? if ($type === 1): ?>
                    <td>  
                        <b> <?= $lvGruppe['name'] ?> </b> 
                        <? if (strlen($lvGruppe['kommentar']) > 0): ?>
                            (<?= htmlReady($lvGruppe['kommentar']) ?>)
                        <? endif; ?>
                        <? if ($lvGruppe['kapazitaet'] > 0): ?>
                            <br/>
                            <b><?= _('Kapazit�t') ?>: </b> <?= htmlReady($lvGruppe['kapazitaet']) ?>
                        <? endif; ?>
                        <? if (strlen($lvGruppe['voraussetzung']) > 0): ?>
                            <br/>
                            <b><?= _('Teilnahmevoraussetzungen') ?>:</b> <?= htmlReady($lvGruppe['voraussetzung']) ?>
                        <? endif; ?>
                    </td>
                <? endif; ?>

                <td  <? if ($type === 2): ?> colspan="3" <? endif; ?>>
                    <? foreach ($lvGruppe['lvGruppen'] as $gruppe): ?>
                        <? if (strlen($gruppe['alt_texte']) > 0): ?>
                            <b><?= formatReady($gruppe['alt_texte']) ?></b>
                        <? endif; ?>
                        <ul>  
                            <? foreach ($gruppe['courses'] as $seminar_id => $course): ?>
                                <li>
                                    <a href="<?= URLHelper::getLink('dispatch.php/course/details', ['sem_id' => $seminar_id]) ?>">
                                    <?= htmlReady($course['Name']) ?>
                                    </a>
                                </li>
                            <? endforeach; ?>
                        </ul>
                    <? endforeach; ?>
                </td>
                <? if ($type === 1): ?>
                    <td width="40%">
                        <? if (strlen($lvGruppe['pruef_vorleistung']) > 0) : ?>
                            <b><?= _('Pr�fungsvorleistung') ?>:</b> <?= htmlReady($lvGruppe['pruef_vorleistung']) ?>
                        <? endif; ?>
                        <? if (strlen($lvGruppe['pruef_leistung']) > 0) : ?>
                            <b><?= _('Pr�fungsform') ?>:</b> <br/><?= htmlReady($lvGruppe['pruef_leistung']) ?> (<?= htmlReady($lvGruppe['anteil_note']) ?> %)
                        <? endif; ?>
                    </td>
                <? endif; ?>
            </tr>
        <? endforeach; ?>
    <? endif; ?>

    <tr>
        <td colspan="3">
            <?
            if (trim($modul->fassung_nr) != '' && $modul->beschlussdatum) {
                printf(_('In der Fassung des <b>%d</b>. Beschlusses vom <b>%s</b>. Bitte beachten Sie: Die Modulinformationen stehen an dieser Stelle ohne Gew�hr.'), htmlReady($modul->fassung_nr), date('d.m.Y', $modul->beschlussdatum));
            } else {
                echo _('Bitte beachten Sie: Die Modulinformationen stehen an dieser Stelle ohne Gew�hr.');
            }
            ?>
        </td>
    </tr>
</table>
