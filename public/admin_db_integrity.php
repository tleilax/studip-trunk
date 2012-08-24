<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Frontend for the db integrity checks
*
*
*
* @author       Andr� Noack <andre.noack@gmx.net>
* @access       public
* @modulegroup  admin_modules
* @module       admin_db_integrity
* @package      Admin
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_db_integrity.php
// Integrity checks for the Stud.IP database
//
// Copyright (c) 2002 Andr� Noack <noack@data-quest.de>
// Suchi & Berg GmbH <info@data-quest.de>
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

use Studip\Button, Studip\LinkButton;

require '../lib/bootstrap.php';

unregister_globals();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("root");

require_once 'lib/msg.inc.php';
require_once 'lib/visual.inc.php';

PageLayout::setTitle(_("�berpr�fen der Datenbank-Integrit�t"));
Navigation::activateItem('/tools/db_integrity');

include 'lib/seminar_open.php'; //hier werden die sessions initialisiert
include 'lib/include/html_head.inc.php';
include 'lib/include/header.php';   //hier wird der "Kopf" nachgeladen

//global variables
$_integrity_plugins = array("User","Seminar","Institut","Archiv","Studiengang");
$_csw = new cssClassSwitcher();

?>
<table class="blank" cellspacing="0" cellpadding="2" border="0" width="100%">
    <tr><td  align="center">

<?
//check, if a plugin is activated
$plugin = Request::get('plugin');
if(!empty($plugin) AND in_array($plugin,$_integrity_plugins)) {

    include_once $RELATIVE_PATH_ADMIN_MODULES."/IntegrityCheck".Request::get('plugin').".class.php";
    $plugin_name = "IntegrityCheck".Request::get('plugin');
    $plugin_obj = new $plugin_name;

    //query the user, if he really wants to delete
    if(Request::option('cmd') == "assure" AND Request::option('checkid')) {
        $result = $plugin_obj->doCheck(Request::quoted('checkid'));
        $anzahl = $result->num_rows();
        $msg = "info�" . sprintf(_("Sie beabsichtigen %s Datens&auml;tze der Tabelle <b>%s</b> zu l&ouml;schen."), $anzahl, $plugin_obj->getCheckDetailTable(Request::option('checkid'))) . "<br>"
        ._("Dieser Schritt kann <u>nicht</u> r&uuml;ckg&auml;ngig gemacht werden! Sind Sie sicher?") . " <br>\n"
        ."<br>".LinkButton::createAccept(_('Ja'), URLHelper::getLink('?plugin='.Request::get('plugin').'&cmd=delete&checkid='.Request::quoted('checkid'))) . '&nbsp;'
        .LinkButton::createCancel(_('Nein'))."\n";
        ?><table border="0" width="80%" cellpadding="2" cellspacing="0" class="table_row_even">
        <tr><td class="blank">&nbsp; </td></tr>
        <?
        parse_msg($msg,"�","table_row_even",1,FALSE);
        ?>
        <tr><td class="blank">&nbsp; </td></tr>
        </table><?

    //delete the rows in the according table
    } elseif(Request::option('cmd') == "delete" AND (Request::option('checkid'))) {
        $result = $plugin_obj->doCheckDelete(Request::quoted('checkid'));
        if ($result === false) {
            $msg = "error�" . _("Beim L&ouml;schen der Datens&auml;tze trat ein Fehler auf!");
        } else {
            $msg = "msg�" . sprintf(_("Es wurden %s Datens&auml;tze der Tabelle <b>%s</b> gel�scht!"), $result, $plugin_obj->getCheckDetailTable(Request::quoted('checkid')));
        }
        Request::set('plugin',NULL);

    //show the found rows in the according table
    } elseif(Request::option('cmd') == "show" AND (Request::option('checkid'))) {
        ?>
        <table border="0" width="80%" cellpadding="2" cellspacing="2">
        <tr><td class="blank" colspan="2">&nbsp; </td></tr>
        <tr><td class="blank"><b>
        <?
        printf(_("Bereich: <i>%s</i> Datens&auml;tze der Tabelle %s</b></td>"), Request::get('plugin'), $plugin_obj->getCheckDetailTable(Request::quoted('checkid')));
        print("<td class=\"blank\" align=\"center\">");
        $link = URLHelper::getURL('?plugin='.Request::get('plugin').'&cmd=assure&checkid='.Request::option('checkid'));
        print(LinkButton::create(_('L�schen'), $link));
        print(LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL()).'</td></tr>');
        ?>
        <tr><td class="blank" colspan="2">&nbsp; </td></tr>
        <tr><td class="table_row_even" align="center" colspan="2">
        <?
        $db = $plugin_obj->getCheckDetailResult(Request::option('checkid'));
        ?><table border=1 class="table_row_odd" style="font-size:smaller" align="center"><tr><?
        $meta = $db->metadata();
        for($i = 0;$i < count($meta);++$i){
            echo "<th>" . $meta[$i]['name'] . "</th>";
        }
        echo "</tr>";
        while ($db->next_record()) {
            echo"<tr>";
            for($i = 0;$i < count($meta);++$i){
                echo "<td>&nbsp;".htmlReady(substr($db->f($i),0,50))."</td>";
                }
            echo"</tr>";
        }
        ?></table></td></tr>
        <tr><td class="blank" colspan="2">&nbsp; </td></tr>
        </table><?

    //no command is given, do all checks of the activated plugin
    } else {
        ?>
        <table border="0" width="80%" cellpadding="2" cellspacing="0">
        <tr><td class="blank" colspan="3">&nbsp; </td></tr>
        <tr><td class="blank" colspan="2"><b>
        <?
        printf(_("Bereich: <i>%s</i> der Datenbank wird gepr&uuml;ft!"), Request::get('plugin'));
        ?>
        </b></td>
        <td class="blank" align="center"><?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getLink()) ?></td> </tr>
        <tr><td class="blank" colspan="3">&nbsp; </td></tr>
        <tr><th width="20%"><?=_("Tabelle")?></th><th width="60%"><?=_("Ergebnis")?></th><th width="20%"><?=_("Aktion")?></th></tr>
        <?
        for($i=0; $i < $plugin_obj->getCheckCount(); ++$i){
            echo "\n<tr><td ".$_csw->getFullClass().">".$plugin_obj->getCheckDetailTable($i)."</td>";
            echo "\n<td ".$_csw->getFullClass().">";
            $result = $plugin_obj->doCheck($i);
            $anzahl = $result->num_rows();
            printf("\n" . _("%s Datens�tze gefunden") . "</td>", $anzahl);
            echo "\n<td ".$_csw->getFullClass().">";
            if( $anzahl == 0 ) {
                echo "&nbsp;";
            } else {
                $link_anzeigen = URLHelper::getURL('?plugin='.Request::get('plugin').'&cmd=show&checkid='.$i);
                $link_loeschen = URLHelper::getURL('?plugin='.Request::get('plugin').'&cmd=assure&checkid='.$i);
                $button_anzeigen = LinkButton::create(_('Anzeigen'), $link_anzeigen);
                $button_loeschen = LinkButton::create(_('L�schen'), $link_loeschen);
                echo $button_anzeigen.'&nbsp;'.$button_loeschen.'</td></tr>';
            }
            $_csw->switchClass();
        }
        ?><tr><td colspan="3">&nbsp;</td></tr></table><?
    }
}

//show all available plugins
if(!Request::quoted('plugin')) {
    for($i=0; $i < count($_integrity_plugins); ++$i){
        include_once $RELATIVE_PATH_ADMIN_MODULES."/IntegrityCheck".$_integrity_plugins[$i].".class.php";
    }
    ?>
    <table border="0" width="80%" cellpadding="2" cellspacing="0">
    <?if ($msg) {
        echo "<tr><td class=\"blank\" colspan=\"4\">&nbsp; </td></tr>";
        parse_msg($msg,"�","blank", 4, FALSE);
    }
    ?>
    <tr><td class="blank" colspan="4">&nbsp; </td></tr>
    <tr><td class="blank" colspan="4"><b><?=_("Folgende Bereiche der Datenbank k&ouml;nnen gepr&uuml;ft werden:")?></b><br>&nbsp; </td></tr>
    <tr><th width="20%"><?=_("Bereich")?></th><th width="60%"><?=_("Beschreibung")?></th><th width="10%"><?=_("Anzahl")?></th><th width="10%"><?=_("Aktion")?></th></tr>
    <?
    for($i=0; $i < count($_integrity_plugins); ++$i) {
        $plugin_name = "IntegrityCheck".$_integrity_plugins[$i];
        $plugin_obj = new $plugin_name;
        echo "\n<tr><td ".$_csw->getFullClass().">&nbsp; ".$_integrity_plugins[$i]."</td>";
        echo "\n<td ".$_csw->getFullClass()." style=\"font-size:smaller\">" . _("&Uuml;berpr&uuml;ft Tabelle:") . " <b>".$plugin_obj->getCheckMasterTable()
            ."</b> " . _("gegen") . " <i>".join(", ",$plugin_obj->getCheckDetailList())."</i></td>";
        echo "\n<td align=\"center\" ".$_csw->getFullClass().">".$plugin_obj->getCheckCount()."</td>";
        echo "\n<td align=\"center\" ".$_csw->getFullClass().">";
        echo LinkButton::create(_('Jetzt testen'), URLHelper::getURL('?plugin='.$_integrity_plugins[$i]));
        echo "</td></tr>";
        $_csw->switchClass();
    }
    ?><tr><td colspan="3">&nbsp;</td></tr></table>
    <?php
}
echo '</td></tr></table>';
include ('lib/include/html_end.inc.php');
page_close();
?>
