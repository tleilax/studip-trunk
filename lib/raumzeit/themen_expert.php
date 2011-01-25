<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/*
themen_expert.php: GUI for the expert mode of the theme management
Copyright (C) 2005-2007 Till Gl�ggler <tgloeggl@uos.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/


// -- here you have to put initialisations for the current page
$sess->register('issue_open');
$sess->register('raumzeitFilter');
$sess->register('chronoGroupedFilter');

require_once ('lib/classes/Seminar.class.php');
require_once ('lib/raumzeit/raumzeit_functions.inc.php');
require_once ('lib/raumzeit/themen_expert.inc.php');
require_once 'lib/admin_search.inc.php';

if ($RESOURCES_ENABLE) {
    include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
    include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
    include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
    include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
    $resList = new ResourcesUserRoomsList($user->id, TRUE, FALSE, TRUE);
}

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
include 'lib/include/admin_search_form.inc.php';

if (!$perm->have_studip_perm('tutor', $id)) {
    die;
}

define('SELECTED', ' checked');
define('NOT_SELECTED', '');

$powerFeatures = true;

$sem = new Seminar($id);
$sem->checkFilter();
$themen =& $sem->getIssues();
if (isset($_REQUEST['cmd'])) {
    $cmd = $_REQUEST['cmd'];
}

//workarounds for multiple submit-buttons

foreach ($_REQUEST as $key => $val) {
    if ( (strlen($key) == 34) && ($key[33] == 'x') ) {
        $keys = explode('_', $key);
        $submitter_id = $keys[0];
    }

    if ( (strlen($key) == 45) && ($key[44] == 'x') ) {
        $keys = explode('_', $key);
        $submitter_id = $keys[0];
        $cycle_id = $keys[1];
    }

    if ( (strlen($key) == 67) && ($key[66] == 'x') ) {
        $keys = explode('_', $key);
        $submitter_id = $keys[0];
        $cycle_id = $keys[1];
    }
    if ($_REQUEST['allOpen']) {
        if (strstr($key, 'theme_title')) {
            $keys = explode('�', $key);
            $changeTitle[$keys[1]] = $val;
        }
        if (strstr($key, 'theme_description')) {
            $keys = explode('�', $key);
            $changeDescription[$keys[1]] = $val;
        }
        if (strstr($key, 'forumFolder')) {
            $keys = explode('�', $key);
            $changeForum[$keys[1]] = $val;
        }
        if (strstr($key, 'fileFolder')) {
            $keys = explode('�', $key);
            $changeFile[$keys[1]] = $val;
        }
    }
}

if (isset($_REQUEST['doAddIssue_x'])) {
    $cmd = 'doAddIssue';
}

if (isset($_REQUEST['changeIssue_x'])) {
    $cmd = 'changeIssue';
}

if (isset($_REQUEST['addIssue_x'])) {
    $cmd = 'addIssue';
}

if (isset($_REQUEST['saveAll_x'])) {
    $cmd = 'saveAll';
}

if (isset($_REQUEST['checkboxAction_x'])) {
    $cmd = 'checkboxAction';
}

if (isset($_REQUEST['chronoAutoAssign_x'])) {
    $cmd = 'chronoAutoAssign';
}

if (isset($submitter_id)) {
    if ($submitter_id == 'autoAssign') {
        $cmd = 'autoAssign';
    } else {
        if (is_array($_REQUEST['themen'])) {
            $termin =& $sem->getSingleDate($submitter_id, $cycle_id);
            foreach ($_REQUEST['themen'] as $iss_id) {
                $termin->addIssueID($iss_id);
            }
            $termin->store();
        } else {
            $sem->createInfo(_("Sie haben kein Thema f�r die Zuordnung ausgew�hlt!"));
        }
    }
}

if (($chronoGroupedFilter) == '') {
    $chronoGroupedFilter = 'grouped';
}

$sem->registerCommand('autoAssign', 'themen_autoAssign');
$sem->registerCommand('changeChronoGroupedFilter', 'themen_changeChronoGroupedFilter');
$sem->registerCommand('chronoAutoAssign', 'themen_chronoAutoAssign');
$sem->registerCommand('open', 'themen_open');
$sem->registerCommand('close', 'themen_close');
$sem->registerCommand('doAddIssue', 'themen_close');
$sem->registerCommand('doAddIssue', 'themen_doAddIssue');
$sem->registerCommand('deleteIssueID', 'themen_deleteIssueID');
$sem->registerCommand('changeIssue', 'themen_changeIssue');
$sem->registerCommand('deleteIssue', 'themen_deleteIssue');
$sem->registerCommand('addIssue', 'themen_addIssue');
$sem->registerCommand('changePriority', 'themen_changePriority');
$sem->registerCommand('openAll', 'themen_openAll');
$sem->registerCommand('saveAll', 'themen_saveAll');
$sem->registerCommand('checkboxAction', 'themen_checkboxAction');
$sem->processCommands();

unset($themen);
$themen =& $sem->getIssues(true);   // read again, so we have the actual sort order and so on
?>
<form action="<?= URLHelper::getLink() ?>" method="post">
<?= CSRFProtection::tokenTag() ?>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
    <tr>
        <td class="blank" colspan="2">
            <table border="0" cellspacing="0" cellpadding="2" width="100%">
                <tr>
                    <td class="blank">
                        <a name="filter">
                        <?
                            $all_semester = $semester->getAllSemesterData();
                            $passed = false;
                            foreach ($all_semester as $val) {
                                if ($sem->getStartSemester() <= $val['vorles_beginn']) $passed = true;
                                if ($passed && ($sem->getEndSemesterVorlesEnde() >= $val['vorles_ende'])) {
                                    $tpl['semester'][$val['beginn']] = $val['name'];
                                    if ($raumzeitFilter != ($val['beginn'])) {
                                    } else {
                                        $tpl['seleceted'] = $val['beginn'];
                                    }
                                }
                            }
                            $tpl['selected'] = $raumzeitFilter;
                            $tpl['semester']['all'] = _("Alle Semester");
                            include('lib/raumzeit/templates/choose_filter.tpl');
                        ?>
                     </td>
                     <td class="blank" align="right">
                        <?
                            $tpl['view']['simple'] = 'Standard';
                            $tpl['view']['expert'] = 'Erweitert';
                            $tpl['selected'] = $viewModeFilter;
                            include('lib/raumzeit/templates/choose_view.tpl');
                        ?>
                    </td>
                </tr>
            </table>
        </td>
  </tr>
    <tr>
        <td class="blank" colspan="2">
        <?php 
            // show messages
            if ($messages = $sem->getStackedMessages()) :
                foreach ($messages as $type => $message_data) :
                    echo MessageBox::$type( $message_data['title'], $message_data['details'] );
                endforeach;
            endif;
        ?>  
        </td>
    </tr>

    <tr>
        <td class="blank" width="50%" height="15"></td>
        <td class="blank" width="50%" height="15"></td>
    </tr>
  <tr>
        <td align="center" class="blank" width="50%" valign="top">
            <table width="90%" cellspacing="0" cellpadding="2" border="0">
                <tr>
                    <td colspan="3" height="28">
                        <font size="-1">&nbsp;</font>
                    </td>
                </tr>
                <tr>
                <td class="printhead" colspan="3">
                    <font size="-1">
                        &nbsp;<b><?=_("Sitzungsthemen")?></b>
                    </font>
                </td>
                </tr>
                <tr>
                    <td class="blank" colspan="3">
                        <font size="-1">
                            <select name="numIssues">
                                <? for ($i = 1; $i <= 15; $i++) { ?>
                                <option value="<?=$i?>"><?=$i?></option>
                                <? } ?>
                            </select>
                            <?=("neue Themen")?>
                        </font>
                        <input type="image" <?=makebutton('anlegen', 'src')?> align="absmiddle" name="addIssue">
                    </td>
                </tr>
                <tr>
                    <td class="blank" colspan="3">
                        &nbsp;
                    </td>
                </tr>
                <tr>
                    <td class="steelgraulight" colspan="3" align="center">
                        <a href="<?= URLHelper::getLink("?cmd=openAll") ?>">
                            <IMG src="<?= $GLOBALS['ASSETS_URL'] ?>images/icons/16/blue/arr_1down.png" title="<?=_("Alle Themen aufklappen")?>" border="0">
                        </a>
                    </td>
                </tr>
                <?
                if ( isset($cmd) && ($cmd == 'addIssue') && ($numIssues == 1)) {
                    $tpl['submit_name'] = 'doAddIssue';
                    $tpl['first'] = true;
                    $tpl['last'] = true;
                    $issue_open[''] = true;
                    include('lib/raumzeit/templates/thema.tpl');
                }

                $count = 0;
                $max = sizeof($themen);
                $max--;
                if (is_array($themen))  foreach ($themen as $themen_id => $thema) {
                    if (isset($_REQUEST['checkboxAction'])) {
                        switch ($_REQUEST['checkboxAction']) {
                            case 'chooseAll':
                                $tpl['selected'] = SELECTED;
                                break;

                            case 'invert':
                                if ($choosen[$themen_id] == TRUE) {
                                    $tpl['selected'] = NOT_SELECTED;
                                } else {
                                    $tpl['selected'] = SELECTED;
                                }
                                break;
                        }
                    }

                    $tpl['theme_title'] = htmlReady($thema->getTitle());
                    $tpl['class'] = 'steel';
                    $tpl['issue_id'] = $thema->getIssueID();
                    $tpl['priority'] = $thema->getPriority();

                    $tpl['first'] = false;
                    $tpl['last'] = false;
                    if ($count == 0) {
                        $tpl['first'] = true;
                    }  // no else condition here, because it can be first and last at same time
                    if ($count == $max) {       // instead of an else condition
                        $tpl['last'] = true;
                    }

                    if ($openAll) {
                        $tpl['openAll'] = TRUE;
                        $issue_open[$themen_id] = TRUE;
                    }

                    if (($issue_open[$themen_id] && $open_close_id == $themen_id) || $openAll) {
                        $tpl['submit_name'] = 'changeIssue';
                        $tpl['theme_description'] = htmlReady($thema->getDescription());
                        $tpl['forumEntry'] = ($thema->hasForum()) ? SELECTED : NOT_SELECTED;
                        $tpl['fileEntry'] = ($thema->hasFile()) ? SELECTED : NOT_SELECTED;
                        include('lib/raumzeit/templates/thema.tpl');
                    } else {
                        unset($issue_open[$themen_id]);
                        include('lib/raumzeit/templates/thema.tpl');
                    }
                    $count++;
                }
                if ($openAll) {
                ?>
                <tr>
                    <td class="blank" colspan="3" align="center">
                        <input type="hidden" name="allOpen" value="1">
                        <input type="image" <?=makebutton('allesuebernehmen', 'src')?> name="saveAll">&nbsp;
                        <a href="<?= URLHelper::getLink() ?>">
                            <IMG <?=makebutton('abbrechen', 'src')?> border="0">
                        </a>
                    </td>
                </tr>
                <?
                } else {
                ?>
                <tr>
                    <td class="blank" colspan="3" align="left">
                    <?
                        include('lib/raumzeit/templates/actions_thema.tpl');
                    ?>
                    </td>
                </tr>
                <?
                }
                ?>
            </table>
        </td>
        <td align="center" class="blank" width="50%" valign="top">
            <table width="90%" cellspacing="0" cellpadding="2" border="0">
                <tr>
                    <td colspan="3" align="right" height="28">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <? if ($chronoGroupedFilter == 'grouped') { ?>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel1info.jpg">
                                <IMG src="<?= $GLOBALS['ASSETS_URL'] ?>images/reiter1.jpg" align="middle">
                            </td>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel1info.jpg">
                                <font size="-1">
                                    &nbsp;&nbsp;<?=_("gruppiert")?>&nbsp;&nbsp;
                                </font>
                            </td>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel1info.jpg">
                                <IMG src="<?= $GLOBALS['ASSETS_URL'] ?>images/reiter1.jpg" align="middle">
                            </td>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel2.jpg">
                                <font size="-1">
                                    <a href="<?= URLHelper::getLink("?cmd=changeChronoGroupedFilter&newFilter=chrono") ?>">
                                        &nbsp;&nbsp;<?=_("chronologisch")?>&nbsp;&nbsp;
                                    </a>
                                </font>
                            </td>
                        <? } else { ?>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel2.jpg">
                                <font size="-1">
                                    <a href="<?= URLHelper::getLink("?cmd=changeChronoGroupedFilter&newFilter=grouped") ?>">
                                        &nbsp;&nbsp;<?=_("gruppiert")?>&nbsp;&nbsp;
                                    </a>
                                </font>
                            </td>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel1info.jpg">
                                <IMG src="<?= $GLOBALS['ASSETS_URL'] ?>images/reiter1.jpg" align="middle">
                            </td>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel1info.jpg">
                                <font size="-1">
                                    &nbsp;&nbsp;<?=_("chronologisch")?>&nbsp;&nbsp;
                                </font>
                            </td>
                            <td background="<?= $GLOBALS['ASSETS_URL'] ?>images/steel1info.jpg">
                                <IMG src="<?= $GLOBALS['ASSETS_URL'] ?>images/reiter1.jpg" align="middle">
                            </td>
                            <? } ?>
                        </font>
                        </table>
                    </td>
                </tr>
                <? if ($chronoGroupedFilter == 'grouped') { ?>
                    <tr>
                        <td class="printhead" colspan="3">
                            <font size="-1">
                                &nbsp;<b><?=_("Allgemeine Zeiten")?></b>
                            </font>
                        </td>
                    </tr>
                    <?
                    $turnus = $sem->getFormattedTurnusDates();

                    foreach ($sem->metadate->cycles as $metadate_id => $val) {
                        $tpl['md_id'] = $metadate_id;
                        $tpl['date'] = $turnus[$metadate_id];
                        include('lib/raumzeit/templates/metadate_themen.tpl');

                        if ($issue_open[$metadate_id]) {
                            $all_semester = $semester->getAllSemesterData();
                            $grenze = 0;

                            $termine =& $sem->getSingleDatesForCycle($metadate_id);
                            foreach ($termine as $singledate_id => $singledate) {

                                if ( ($grenze == 0) || ($grenze < $singledate->getStartTime()) ) {
                                    foreach ($all_semester as $zwsem) {
                                        if ( ($zwsem['beginn'] < $singledate->getStartTime()) && ($zwsem['ende'] > $singledate->getStartTime()) ) {
                                            $grenze = $zwsem['ende'];
                                            ?>
                                                <tr>
                                                <td class="steelgraulight" align="center" colspan="9">
                                                <font size="-1"><b><?=$zwsem['name']?></b></font>
                                                </td>
                                                </tr>
                                                <?
                                        }
                                    }
                                }

                                // Template fuer einzelnes Datum
                                $tpl = getTemplateDataForSingleDate($singledate, $metadate_id);
                                $tpl['space'] = true;
                                $tpl['cycle_id'] = $metadate_id;
                                if ($tpl['type'] != 1) {
                                    $tpl['art'] = $TERMIN_TYP[$tpl['type']]['name'];
                                } else {
                                    $tpl['art'] = FALSE;
                                }

                                include('lib/raumzeit/templates/singledate_themen.tpl');
                                if ($iss = $singledate->getIssueIDs()) {
                                    foreach ($iss as $issue_id) {
                                        if ($themen[$issue_id]) {
                                            $tpl['name'] = htmlReady($themen[$issue_id]->getTitle());
                                            $tpl['class'] = 'steelgraulight';
                                            $tpl['space'] = true;
                                            $tpl['issue_id'] = $issue_id;
                                            $tpl['sd_id'] = $singledate_id;
                                            $tpl['cycle_id'] = $metadate_id;
                                        } else {
                                            $tpl['name'] = '<font color="red">Fehlerhafter Eintrag!</font>';
                                            $tpl['class'] = 'steelgraulight';
                                            $tpl['space'] = true;
                                            $tpl['issue_id'] = $issue_id;
                                            $tpl['sd_id'] = $singledate_id;
                                            $tpl['cycle_id'] = $metadate_id;
                                        }
                                            include('lib/raumzeit/templates/thema_short.tpl');
                                    }
                                }

                            }
                        }
                        ?>
                        <tr>
                            <td class="blank" height="4" colspan="3"></td>
                        </tr>
                            <?
                    }
                    ?>
                    <tr>
                        <td class="blank" colspan="3">
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td class="printhead" colspan="3">
                            <font size="-1">
                                &nbsp;<b><?=_("unregelm&auml;&szlig;ige Termine / Blocktermine")?></b>
                            </font>
                        </td>
                    </tr>
                    <?
                    $termine =& $sem->getSingleDates(true);
                    foreach ($termine as $singledate_id => $singledate) {
                        $tpl = getTemplateDataForSingleDate($singledate);
                        $tpl['space'] = false;

                        include('lib/raumzeit/templates/singledate_themen.tpl');
                        if ($iss = $singledate->getIssueIDs()) {
                            foreach ($iss as $issue_id) {
                                $tpl['name'] = htmlReady($themen[$issue_id]->getTitle());
                                $tpl['class'] = 'steelgraulight';
                                $tpl['space'] = false;
                                $tpl['issue_id'] = $issue_id;
                                $tpl['sd_id'] = $singledate_id;
                                $tpl['cycle_id'] = '';
                                include('lib/raumzeit/templates/thema_short.tpl');
                            }
                        }
                    }
                } else {
                    /* * * * * * * * * * * * * * * * * * * * * * * * * *
                     *   C H R O N O L O G I S C H E   A N S I C H T   *
                     * * * * * * * * * * * * * * * * * * * * * * * * * */
                    ?>
                    <tr>
                        <td class="printhead" colspan="3">
                            <font size="-1">
                                &nbsp;<b><?=_("Zeiten")?></b>
                            </font>
                        </td>
                    </tr>
                    <tr>
                        <td class="steel1" colspan="3">
                            &nbsp;
                            <font size="-1"><?=_("ausgew�hlte Themen freien Terminen")?></font>&nbsp;
                            <input type="image" <?=makebutton('zuordnen', 'src')?> align="absMiddle" border="0" name="chronoAutoAssign">
                        </td>
                    </tr>
                    <?

                    $termine = getAllSortedSingleDates($sem);

                    $all_semester = $semester->getAllSemesterData();
                    $grenze = 0;

                    foreach ($termine as $singledate_id => $singledate) {

                        // show semester heading
                        if ( ($grenze == 0) || ($grenze < $singledate->getStartTime()) ) {
                            foreach ($all_semester as $zwsem) {
                                if ( ($zwsem['beginn'] < $singledate->getStartTime()) && ($zwsem['ende'] > $singledate->getStartTime()) ) {
                                    $grenze = $zwsem['ende'];
                                    ?>
                                        <tr>
                                            <td class="steelgraulight" align="center" colspan="9">
                                                <font size="-1"><b><?=$zwsem['name']?></b></font>
                                            </td>
                                        </tr>
                                        <?
                                }
                            }
                        }
                        // end "show semester heading"

                        $tpl = getTemplateDataForSingleDate($singledate, $metadate_id);
                        $tpl['space'] = false;
                        $tpl['cycle_id'] = $singledate->getCycleID();
                        if ($tpl['type'] != 1) {
                            $tpl['art'] = $TERMIN_TYP[$tpl['type']]['name'];
                        } else {
                            $tpl['art'] = FALSE;
                        }

                        include('lib/raumzeit/templates/singledate_themen.tpl');

                        if ($iss = $singledate->getIssueIDs()) {
                            foreach ($iss as $issue_id) {
                                $tpl['name'] = htmlReady($themen[$issue_id]->getTitle());
                                $tpl['class'] = 'steelgraulight';
                                $tpl['space'] = false;
                                $tpl['issue_id'] = $issue_id;
                                $tpl['sd_id'] = $singledate_id;
                                $tpl['cycle_id'] = $singledate->getCycleID();
                                include('lib/raumzeit/templates/thema_short.tpl');
                            }
                        }

                    } // foreach termine
                }
                ?>
            </table>
        </td>
  </tr>
    <tr>
        <td class="blank" width="50%">
            &nbsp;
        </td>
        <td class="blank" width="50%">
            &nbsp;
    </tr>
</table>
</form>
<?
    $sem->store();
    include 'lib/include/html_end.inc.php';
    page_close();
?>
