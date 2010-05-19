<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
* blockveranstaltungs_assistent.php - Terminverwaltung von Stud.IP
*
* @author       Andr� Noack
* @author       Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @access       public
* @module       blockveranstaltungs_assistent.php
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2007 Stud.IP
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require '../lib/bootstrap.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("tutor");

require_once("lib/seminar_open.php"); // initialise Stud.IP-Session
require_once("lib/blockveranstaltungs_assistent.inc.php");
require_once("lib/functions.php");
require_once("lib/exceptions/AccessDeniedException.php");

$_NOHEADER = true; //keinen Header anzeigen
/* Ausgabe erzeugen---------------------------------------------------------- */
//Header
include ('lib/include/html_head.inc.php');
include ('lib/include/header.php');

if (isset($_REQUEST['seminar_id'])) {
    $seminar_id = $_REQUEST['seminar_id'];
} else {
    $seminar_id = $SessSemName[1];
}

if (!$perm->have_studip_perm('tutor', $seminar_id)) {
    throw new AccessDeniedException();
}

//Content
$destination=$ABSOLUTE_URI_STUDIP."raumzeit.php?newFilter=all&x=4&y=4&cmd=applyFilter#irregular_dates";
echo "<SCRIPT> function reload_opener() {
                if (opener.location.href != '$destination')
                        opener.location.href = '$destination';
                else
                        opener.location.reload();
                return true;
                }  </SCRIPT>";

if (isset($_POST['command']) && ($_POST['command'] == 'create')) {
    $return = create_block_schedule_dates($seminar_id,$_POST);
    ?><script>reload_opener();</script><?
}

$cssSw = new cssClassSwitcher();
// HTML Template
?>
<form method="post" action="<?=$PHP_SELF?>">
<table width="100%" border="0" cellspacing="0" cellpadding="1" >
    <tr>
        <td class="topic"><b><?=_("Blockveranstaltungstermine anlegen")?></b></td>
    </tr>
    <tr>
        <th><?=getHeaderLine($SessSemName[1])?></th>
    </tr>
    <? if (!$return['ready'] && ($return['errors'])) :
            foreach($return['errors'] as $error) {
                $error_msg .= $error.'<br>';
                //echo "&nbsp;<font align=\"center\" color=\"red\"><b>$error</b></font><br>&nbsp;";
            }
            parse_msg('error�'.$error_msg.'�');
        endif;

        if ($return['ready']) :
            $msg = "<b>"._("F�r folgende Termine wurden die gew�hlten Aktionen durchgef�hrt").":</b>";
            $msg .= "<br>";
            foreach ($return['status'] as $status) {
                $msg .= "<li>".$status."</li>";
            }
            parse_msg('msg�'.$msg.'�');
        endif; ?>
    <tr>
        <td class="blank" colspan="2">
                <input type="hidden" name="seminar_id" value="<?=htmlReady($seminar_id)?>" />
                <input type="hidden" name="command" value="create" />
                <table border="0" cellspacing="0" cellpadding="3" width="100%">
                    <tr>
                        <td class="<?=$cssSw->getClass()?>" colspan="2" align="left">
                            <b><?=_("Die Veranstaltung findet in folgendem Zeitraum statt")?>:</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>">
                            &nbsp;&nbsp;<?=_("Startdatum")?>:
                        </td>
                        <td class="<?=$cssSw->getClass()?>">
                            <input type="text" size="2" maxlength="2" name="start_day" value="<?=$_POST['start_day']?>" />.
                            <input type="text" size="2" maxlength="2" name="start_month" value="<?=$_POST['start_month']?>" />.
                            <input type="text" size="4" maxlength="4" name="start_year" value="<?=$_POST['start_year']?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>">
                            &nbsp;&nbsp;<?=_("Enddatum")?>:
                        </td>
                        <td class="<?=$cssSw->getClass()?>">
                            <input type="text" size="2" maxlength="2" name="end_day" value="<?=$_POST['end_day']?>" />.
                            <input type="text" size="2" maxlength="2" name="end_month" value="<?=$_POST['end_month']?>" />.
                            <input type="text" size="4" maxlength="4" name="end_year" value="<?=$_POST['end_year']?>" />
                        </td>
                    </tr>
                    <?$cssSw->switchClass()?>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>" colspan="2" align="left">
                            <b><?=_("Die Veranstaltung findet zu folgenden Zeiten statt")?>:</b>
                        </td>
                    </tr>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>">
                            &nbsp;&nbsp;<?=_("Start:")?>
                        </td>
                        <td class="<?=$cssSw->getClass()?>">
                            <input type="text" size="2" maxlength="2" name="start_hour" value="<?=$_POST['start_hour']?>" />:
                            <input type="text" size="2" maxlength="2" name="start_minute" value="<?=$_POST['start_minute']?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>">
                            &nbsp;&nbsp;<?=_("Ende:")?>
                        </td>
                        <td class="<?=$cssSw->getClass()?>">
                            <input type="text" size="2" maxlength="2" name="end_hour" value="<?=$_POST['end_hour']?>" />:
                            <input type="text" size="2" maxlength="2" name="end_minute" value="<?=$_POST['end_minute']?>" />
                        </td>
                    </tr>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>">
                            &nbsp;&nbsp;<?=_("Art der Termine:")?>
                        </td>
                        <td class="<?=$cssSw->getClass()?>">
                            <select name="art">
                            <? foreach ($TERMIN_TYP as $key => $val) : ?>
                                <option value="<?= $key ?>"<?= ($tpl['type'] == $key) ? ' selected' : '';?>><?= $val['name'] ?></option>
                            <? endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?$cssSw->switchClass()?>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>" colspan="2">
                            <b><?=_("Die Veranstaltung findet an folgenden Tagen statt")?>:</b>
                            <br><br>
                            <input type="checkbox" name="every_day" value="1" <?=($_POST["every_day"]=='1'?"checked=checked":"")?> />&nbsp;Jeden Tag<br>
                                <br>
                            <input type="checkbox" name="days[]" value="Monday"<?=day_checked('Monday')?> />&nbsp;Montag<br>
                            <input type="checkbox" name="days[]" value="Tuesday"<?=day_checked('Tuesday')?> />&nbsp;Dienstag<br>
                            <input type="checkbox" name="days[]" value="Wednesday"<?=day_checked('Wednesday')?> />&nbsp;Mittwoch<br>
                            <input type="checkbox" name="days[]" value="Thursday"<?=day_checked('Thursday')?> />&nbsp;Donnerstag<br>
                            <input type="checkbox" name="days[]" value="Friday"<?=day_checked('Friday')?> />&nbsp;Freitag<br>
                            <input type="checkbox" name="days[]" value="Saturday"<?=day_checked('Saturday')?> />&nbsp;Samstag<br>
                            <input type="checkbox" name="days[]" value="Sunday"<?=day_checked('Sunday')?> />&nbsp;Sonntag<br>
                            <br>
                        </td>
                    </tr>
                    <?$cssSw->switchClass()?>
                    <tr>
                        <td class="<?=$cssSw->getClass()?>" colspan="2" align="center">
                            <input type="image" name="block_submit" align="absmiddle" <?=makebutton('erstellen', 'src')?>>
                            <a href="javascript:window.close()"><?=makebutton('schliessen')?></a>
                        </td>
                    </tr>
                </table>
        </td>
    </tr>
</table>
</form>
<br>

<?php
    include ('lib/include/html_end.inc.php');
    page_close();
?>
