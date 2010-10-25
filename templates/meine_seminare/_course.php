<?
global $SEM_CLASS, $SEM_TYPE, $auth;

$cssSw->resetClass();

foreach ($group_members as $member) {
    $semid = $member['seminar_id'];
    $values = $my_obj[$semid];
    $studygroup_mode = $SEM_CLASS[$SEM_TYPE[$my_obj[$semid]['sem_status']]["class"]]["studygroup_mode"];

    if ($values['obj_type'] == "sem") {

        $cssSw->switchClass();

        $lastVisit = $values['visitdate'];
        ?>
        <tr <?= $cssSw->getHover() ?>>
            <td class="gruppe<?= $values['gruppe'] ?>">
                <a href='gruppe.php'>
                    <?= Assets::img('blank.gif', array('size' => '7@12') + tooltip2(_("Gruppe �ndern"))) ?>
                </a>
            </td>

            <td class="<?= $cssSw->getClass() ?>">
                <? if ($studygroup_mode) { ?>
                    <?= StudygroupAvatar::getAvatar($semid)->getImageTag(Avatar::SMALL, array('title' => htmlReady($values['semname']))) ?>
                <? } else { ?>
                    <?= CourseAvatar::getAvatar($semid)->getImageTag(Avatar::SMALL, array('title' => htmlReady($values['semname']))) ?>
                <? } ?>
            </td>

            <td align="left" class="<?= $cssSw->getClass() ?>">
                <a href="seminar_main.php?auswahl=<?= $semid ?>"
                   <?= $lastVisit <= $values["chdate"] ? 'style="color: red;"' : '' ?>>

                    <? if ($studygroup_mode) { ?>

                        <?= htmlReady($values['semname']) ?>

                        <? if ($values['prelim']) { ?>
                            (<?= _("Studiengruppe") ?>, <?= _("geschlossen") ?>)
                        <? } else { ?>
                            (<?= _("Studiengruppe") ?>)
                        <? } ?>

                    <? } else { ?>

                        <?= htmlReady($values['name']) ?>

                    <? } ?>
                </a>


                <? if ($values["visible"] == 0) {

                    $infotext = _("Versteckte Veranstaltungen k�nnen �ber die Suchfunktionen nicht gefunden werden.");
                    $infotext .= " ";
                    if (get_config('ALLOW_DOZENT_VISIBILITY')) {
                        $infotext .= _("Um die Veranstaltung sichtbar zu machen, w�hlen Sie den Punkt \"Sichtbarkeit\" im Administrationsbereich der Veranstaltung.");
                    }
                    else {
                        $infotext .= _("Um die Veranstaltung sichtbar zu machen, wenden Sie sich an eineN der zust�ndigen AdministratorInnen.");
                    }
                ?>
                        <?= _("[versteckt]") ?>
                        <? $attributes = tooltip2($infotext, TRUE, TRUE) ?>
                        <? $attributes['class'] = 'text-top'; ?>
                        <?= Assets::img('icons/16/grey/info-circle.png', $attributes) ?>
                <? } ?>
            </td>
            <td class="<?= $cssSw->getClass() ?>" align="left" nowrap="nowrap">
                <? print_seminar_content($semid, $values); ?>
            </td>

            <td class="<?= $cssSw->getClass() ?>" align="right" nowrap="nowrap">
            <? if (get_config('CHAT_ENABLE') && $values["modules"]["chat"]) { ?>

                <a href="<?= !$auth->auth['jscript'] ? 'chat_online.php' : '#' ?>"
                   onClick="return open_chat(<?= $chat_info[$semid]['is_active'] ? 'false' : "'$semid'" ?>);">
                    <?= chat_get_chat_icon($chat_info[$semid]['chatter'], $chat_invs[$chat_info[$semid]['chatuniqid']], $chat_info[$semid]['is_active'], true, 'grey', 'red', '') ?>
                </a>
            <? } else { ?>
                <?= Assets::img("blank.gif", array('size' => '16')) ?>
            <? }  ?>

            <? if (in_array($values["status"], array("dozent", "tutor"))) { ?>
                <?
                    if ($SEM_CLASS[$SEM_TYPE[$values['sem_status']]["class"]]["studygroup_mode"]) {
                        $course_url = 'dispatch.php/course/studygroup/edit/'. $semid .'?cid='. $semid;
                    }
                    else {
                        $course_url = 'dispatch.php/course/management?cid='. $semid;
                    }
                ?>

                    <a href="<?= URLHelper::getUrl($course_url) ?>">
                        <?= Assets::img('icons/16/grey/admin.png', tooltip2(_("Veranstaltung administrieren"))) ?>
                    </a>

            <? } else if ($values["binding"]) { ?>

                    <a href="<?= URLHelper::getLink($_SERVER['PHP_SELF'], array('auswahl' => $semid, 'cmd' => 'no_kill')) ?>">
                        <?= Assets::img('icons/16/grey/decline/door-leave.png', tooltip2(_("Das Abonnement ist bindend. Bitte wenden Sie sich an die Dozentin oder den Dozenten."))) ?>
                    </a>

            <? } else { ?>

                    <a href="<?= URLHelper::getLink($_SERVER['PHP_SELF'], array('auswahl' => $semid, 'cmd' => 'suppose_to_kill')) ?>">
                        <?= Assets::img('icons/16/grey/door-leave.png', tooltip2(_("aus der Veranstaltung abmelden"))) ?>
                    </a>
            <? } ?>
            </td>
        </tr>
    <?
    }
}

