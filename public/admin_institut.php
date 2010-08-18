<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/*
admin_institut.php - Einrichtungs-Verwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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


require '../lib/bootstrap.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("admin");

if ($_REQUEST['admin_inst_id']) {
    $_REQUEST['cid'] = $_REQUEST['admin_inst_id'];
}

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

## Set this to something, just something different...
  $hash_secret = "hgeisgczwgebt";

## If is set 'cancel', we leave the adminstration form...
if (isset($cancel)) unset ($i_view);

require_once('lib/msg.inc.php'); //Funktionen f&uuml;r Nachrichtenmeldungen
require_once('lib/visual.inc.php');
require_once('config.inc.php');
require_once('lib/forum.inc.php');
require_once('lib/datei.inc.php');
require_once('lib/statusgruppe.inc.php');
require_once 'lib/functions.php';
require_once('lib/classes/Modules.class.php');
require_once('lib/classes/DataFieldEntry.class.php');
require_once('lib/classes/StudipLitList.class.php');
require_once('lib/classes/StudipLitSearch.class.php');
require_once('lib/classes/StudipNews.class.php');
require_once('lib/log_events.inc.php');
require_once 'lib/classes/InstituteAvatar.class.php';

if (get_config('RESOURCES_ENABLE')) {
    include_once($RELATIVE_PATH_RESOURCES."/lib/DeleteResourcesUser.class.php");
}

if (get_config('EXTERN_ENABLE')) {
    require_once($RELATIVE_PATH_EXTERN . "/lib/ExternConfig.class.php");
}


// Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$cssSw = new cssClassSwitcher;
$Modules = new Modules;

// Check if there was a submission
while ( is_array($_REQUEST)
     && list($key, $val) = each($_REQUEST)) {

  switch ($key) {

    // Create a new Institut
    case "create_x":
        if (!$perm->have_perm("root") && !($perm->is_fak_admin() && get_config('INST_FAK_ADMIN_PERMS') != 'none'))  {
            $msg = "error�<b>" . _("Sie haben nicht die Berechtigung, um neue Einrichtungen zu erstellen!") . "</b>";
            break;
        }
        // Do we have all necessary data?
        if (empty($Name)) {
            $msg="error�<b>" . _("Bitte geben sie eine Bezeichnung f&uuml;r die Einrichtung ein!") . "</b>";
            $i_view="new";
            break;
        }

        // Does the Institut already exist?
        // NOTE: This should be a transaction, but it is not...
        $db->query("select * from Institute where Name='$Name'");
        if ($db->nf()>0) {
            $msg="error�<b>" . sprintf(_("Die Einrichtung \"%s\" existiert bereits!"), htmlReady(stripslashes($Name)));
            break;
        }

        // Create an id
        $i_id=md5(uniqid($hash_secret));
        if (!$Fakultaet) {
            if ($perm->have_perm("root")) {
                $Fakultaet = $i_id;
            } else {
                $msg = "error�<b>" . _("Sie haben nicht die Berechtigung neue Fakult&auml;ten zu erstellen");
                break;
            }
        }

        $query = "insert into Institute (Institut_id,Name,fakultaets_id,Strasse,Plz,url,telefon,email,fax,type,lit_plugin_name,mkdate,chdate) values('$i_id','$Name','$Fakultaet','$strasse','$plz', '$home', '$telefon', '$email', '$fax', '$type','$lit_plugin_name', '".time()."', '".time()."')";

        $db->query($query);

        if ($db->affected_rows() == 0) {
            $msg="error�<b>" . _("Datenbankoperation gescheitert:") . " " . $query . "</b>";
            break;
        }


        log_event("INST_CREATE",$i_id,NULL,NULL,$query); // logging

        // Set the default list of modules
        $Modules->writeDefaultStatus($i_id);

        // Create default folder and discussion
        CreateTopic(_("Allgemeine Diskussionen"), " ", _("Hier ist Raum f�r allgemeine Diskussionen"), 0, 0, $i_id, 0);
        $db->query("INSERT INTO folder SET folder_id='".md5(uniqid(rand()))."', range_id='".$i_id."', name='" . _("Allgemeiner Dateiordner") . "', description='" . _("Ablage f�r allgemeine Ordner und Dokumente der Einrichtung") . "', mkdate='".time()."', chdate='".time()."'");

        $msg="msg�" . sprintf(_("Die Einrichtung \"%s\" wurde erfolgreich angelegt."), htmlReady(stripslashes($Name)));

        $i_view = $i_id;

        //This will select the new institute later for navigation (=>admin_search_form.inc.php)
        $admin_inst_id = $i_id;
        openInst($i_id);
      break;

    //change institut's data
    case "i_edit_x":

        if (!$perm->have_studip_perm("admin",$i_id)){
            $msg = "error�<b>" . _("Sie haben nicht die Berechtigung diese Einrichtungen zu ver&auml;ndern!") . "</b>";
            break;
        }

        //do we have all necessary data?
        if (empty($Name)) {
            $msg="error�<b>" . _("Bitte geben Sie eine Bezeichnung f&uuml;r die Einrichtung ein!") . "</b>";
            break;
        }

        //update Institut information.
        $query = "UPDATE Institute SET Name='$Name', fakultaets_id='$Fakultaet', Strasse='$strasse', Plz='$plz', url='$home', telefon='$telefon', fax='$fax', email='$email', type='$type', lit_plugin_name='$lit_plugin_name' ,chdate=".time()." where Institut_id = '$i_id'";
        $db->query($query);
        if ($db->affected_rows() == 0) {
            $msg="error�<b>" . _("Datenbankoperation gescheitert:") . " " . $query . "</b>";
            break;
        }

        $msg="msg�" . sprintf(_("Die �nderung der Einrichtung \"%s\" wurde erfolgreich gespeichert."), htmlReady(stripslashes($Name)));

        // update additional datafields
        if (is_array($_REQUEST['datafields'])) {
            $invalidEntries = array();
            foreach (DataFieldEntry::getDataFieldEntries($i_id, 'inst') as $entry) {
                if(isset($_REQUEST['datafields'][$entry->getId()])){
                    $entry->setValueFromSubmit($_REQUEST['datafields'][$entry->getId()]);
                    if ($entry->isValid())
                        $entry->store();
                    else
                        $invalidEntries[$entry->getId()] = $entry;
                }
            }
            if (count($invalidEntries)  > 0)
                $msg='error�<b>' . _('ung&uuml;ltige Eingaben (s.u.) wurden nicht gespeichert') .'</b>';
            else
                $msg="msg�<b>" . sprintf(_("Die Daten der Einrichtung \"%s\" wurden ver&auml;ndert."), htmlReady(stripslashes($Name))) . "</b>";
        }
        break;

    // Delete the Institut
    case "i_kill_x":
        if (!check_ticket($_GET['studipticket']))
        {
            $msg="error�<b>" . _("Ihr Ticket ist abgelaufen. Versuchen Sie die letzte Aktion erneut.") . "</b>";
            break;
        }

        if (!$perm->have_perm("root") && !($perm->is_fak_admin() && get_config('INST_FAK_ADMIN_PERMS') == 'all'))
        {
            $msg="error�<b>" . _("Sie haben nicht die Berechtigung Fakult&auml;ten zu l&ouml;schen!") . "</b>";
            break;
        }
        // Institut in use?
        $db->query("SELECT * FROM seminare WHERE Institut_id = '$i_id'");
        if ($db->next_record()) {
            $msg="error�<b>" . _("Diese Einrichtung kann nicht gel&ouml;scht werden, da noch Veranstaltungen an dieser Einrichtung existieren!") . "</b>";
            break;
        }

        $db->query("SELECT a.Institut_id,a.Name, IF(a.Institut_id=a.fakultaets_id,1,0) AS is_fak, count(b.Institut_id) as num_inst FROM Institute a LEFT JOIN Institute b ON (a.Institut_id=b.fakultaets_id) WHERE a.Institut_id ='$i_id' AND b.Institut_id!='$i_id' AND a.Institut_id=a.fakultaets_id GROUP BY a.Institut_id ");
        $db->next_record();
        if($db->f("num_inst")) {
            $msg="error�<b>" . _("Diese Einrichtung kann nicht gel&ouml;scht werden, da sie den Status Fakult&auml;t hat, und noch andere Einrichtungen zugeordnet sind!") . "</b>";
            break;
        }

        if ($db->f("is_fak") && !$perm->have_perm("root")){
            $msg="error�<b>" . _("Sie haben nicht die Berechtigung Fakult&auml;ten zu l&ouml;schen!") . "</b>";
            break;
        }

        // delete users in user_inst
        $result = DBManager::get()->query("SELECT user_id FROM user_inst WHERE institut_id = '$i_id'");
        while ($data = $result->fetch()) {
            log_event('INST_USER_DEL', $i_id, $data['user_id']);
        }

        $query = "DELETE FROM user_inst WHERE Institut_id='$i_id'";
        $db->query($query);
        if (($db_ar = $db->affected_rows()) > 0) {
            $msg.="msg�" . sprintf(_("%s Mitarbeiter gel&ouml;scht."), $db_ar) . "�";
        }

        // delete participations in seminar_inst
        $query = "DELETE FROM seminar_inst WHERE Institut_id='$i_id'";
        $db->query($query);
        if (($db_ar = $db->affected_rows()) > 0) {
            $msg.="msg�" . sprintf(_("%s Beteiligungen an Veranstaltungen gel&ouml;scht"), $db_ar) . "�";
        }


        // delete literatur
        $del_lit = StudipLitList::DeleteListsByRange($i_id);
        if ($del_lit) {
            $msg.="msg�" . sprintf(_("%s Literaturlisten gel&ouml;scht."),$del_lit['list'])  . "�";
        }

        // SCM l�schen
        $query = "DELETE FROM scm where range_id='$i_id'";
        $db->query($query);
        if (($db_ar = $db->affected_rows()) > 0) {
            $msg .= "msg�" . _("Freie Seite der Einrichtung gel&ouml;scht") . "�";
        }

        // delete news-links
        StudipNews::DeleteNewsRanges($i_id);

        //delete entry in news_rss_range
        StudipNews::UnsetRssId($i_id);

        //updating range_tree
        $query = "UPDATE range_tree SET name='$Name " . _("(in Stud.IP gel�scht)") . "',studip_object='',studip_object_id='' WHERE studip_object_id='$i_id'";
        $db->query($query);
        if (($db_ar = $db->affected_rows()) > 0) {
            $msg.="msg�" . sprintf(_("%s Bereiche im Einrichtungsbaum angepasst."), $db_ar) . "�";
        }

        // Statusgruppen entfernen
        if ($db_ar = DeleteAllStatusgruppen($i_id) > 0) {
            $msg .= "msg�" . sprintf(_("%s Funktionen/Gruppen gel&ouml;scht"), $db_ar) . ".�";
        }

        //kill the datafields
        DataFieldEntry::removeAll($i_id);

        //kill all wiki-pages
        $query = sprintf ("DELETE FROM wiki WHERE range_id='%s'", $i_id);
        $db->query($query);

        $query = sprintf ("DELETE FROM wiki_links WHERE range_id='%s'", $i_id);
        $db->query($query);

        $query = sprintf ("DELETE FROM wiki_locks WHERE range_id='%s'", $i_id);
        $db->query($query);


        // kill all the ressources that are assigned to the Veranstaltung (and all the linked or subordinated stuff!)
        if ($RESOURCES_ENABLE) {
            $killAssign = new DeleteResourcesUser($i_id);
            $killAssign->delete();
        }

        // delete all configuration files for the "extern modules"
        if ($EXTERN_ENABLE) {
            $counts = ExternConfig::DeleteAllConfigurations($i_id);
            if ($counts) {
                $msg .= "msg�" . sprintf(_("%s Konfigurationsdateien f&uuml;r externe Seiten gel&ouml;scht."), $counts);
                $msg .= "�";
            }
        }

        // delete folders and discussions
        $query = "DELETE from px_topics where Seminar_id='$i_id'";
        $db->query($query);
        if (($db_ar = $db->affected_rows()) > 0) {
            $msg.="msg�" . sprintf(_("%s Postings aus dem Forum der Einrichtung gel&ouml;scht."), $db_ar) . "�";
            }

        $db_ar = delete_all_documents($i_id);
        if ($db_ar > 0)
            $msg.="msg�" . sprintf(_("%s Dokumente gel&ouml;scht."), $db_ar) . "�";

        //kill the object_user_vists for this institut

        object_kill_visits(null, $i_id);

        // Delete that Institut.
        $query = "DELETE FROM Institute WHERE Institut_id='$i_id'";
        $db->query($query);
        if ($db->affected_rows() == 0) {
            $msg="error�<b>" . _("Datenbankoperation gescheitert:") . "</b> " . $query;
            break;
        } else {

            $msg.="msg�" . sprintf(_("Die Einrichtung \"%s\" wurde gel&ouml;scht!"), htmlReady(stripslashes($Name))) . "�";
            $i_view="delete";
            log_event("INST_DEL",$i_id,NULL,$Name); // logging - put institute's name in info - it's no longer derivable from id afterwards
        }

        // We deleted that intitute, so we have to unset the selection
        closeObject();
        break;
    case 'i_trykill_x':
        $message = "Sind Sie sicher, dass Sie diese Einrichtung l�schen wollen?";
        $post['i_id'] = Request::option('i_id');
        $post['i_kill_x'] = 1;
        $post['Name'] = Request::get('Name');
        $post['studipticket'] = get_ticket();
        echo createQuestion($message, $post);
        break;

    default:

    }
}

//workaround
if ($i_view == "new")
    closeObject();

require_once 'lib/admin_search.inc.php';

PageLayout::setTitle(_("Verwaltung der Grunddaten"));

if (Request::get('section') == 'details') {
    UrlHelper::bindLinkParam('section', $section);
    Navigation::activateItem('/course/admin/details');
} else {
    Navigation::activateItem('/admin/institute/details');
}

//get ID from a open Institut
if ($SessSemName[1])
    $i_view=$SessSemName[1];

$header_line = getHeaderLine($i_view);
if ($header_line)
    PageLayout::setTitle($header_line." - ".PageLayout::getTitle());

include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
include 'lib/include/admin_search_form.inc.php';

?>
<table class="blank" width="100%" cellpadding="2" cellspacing="0">
<? if (isset($msg)) : ?><? parse_msg($msg) ?><? endif ?>

<? if ($i_view=="delete") {
    echo "<tr><td class=\"blank\" colspan=\"2\"><table width=\"70%\" align=\"center\" class=\"steelgraulight\" >";
    echo "<tr><td><br>" . _("Die ausgew�hlte Einrichtung wurde gel&ouml;scht.") . "<br>";
    printf(_("Bitte w�hlen Sie �ber den Reiter %s eine andere Einrichtung aus."), "<a href=\"admin_institut.php?list=TRUE\"><b>"._('Einrichtungen')."</b></a>");
    echo '<br><br></td></tr></table><br><br></td></tr></table>';
    include ('lib/include/html_end.inc.php');
    page_close();
    die;
}

if ($perm->have_studip_perm("admin",$i_view) || $i_view == "new") {

    if ($i_view != "new") {
        $db->query("SELECT a.*,b.Name AS fak_name, count(Seminar_id) AS number FROM Institute a LEFT JOIN Institute b ON (b.Institut_id=a.fakultaets_id) LEFT JOIN seminare c ON (a.Institut_id=c.Institut_id) WHERE a.Institut_id ='$i_view' GROUP BY a.Institut_id");
        $db->next_record();
        $db2->query("SELECT a.Institut_id,a.Name,count(b.Institut_id) as num_inst FROM Institute a LEFT JOIN Institute b ON (a.Institut_id=b.fakultaets_id) WHERE a.Institut_id ='$i_view' AND b.Institut_id!='$i_view' AND a.Institut_id=a.fakultaets_id GROUP BY a.Institut_id ");
        $db2->next_record();
        $_num_inst = $db2->f("num_inst");
    }
    $i_id= $db->f("Institut_id");
    ?>
<tr>
    <td class="blank" valign="top">
    <form method="POST" name="edit" action="<?= UrlHelper::getLink() ?>">
    <table class="default">
    <tr <? $cssSw->switchClass() ?>>
        <td class="<? echo $cssSw->getClass() ?>" ><?=_("Name:")?> </td>
        <td class="<? echo $cssSw->getClass() ?>" ><input style="width: 98%" type="text" name="Name" size=50 maxlength=254 value="<?php echo htmlReady($db->f("Name")) ?>"></td>
    </tr>
    <tr <? $cssSw->switchClass() ?>>
        <td class="<? echo $cssSw->getClass() ?>" ><?=_("Fakult&auml;t:")?></td>
        <td class="<? echo $cssSw->getClass() ?>" align=left>
        <?php
        if ($perm->is_fak_admin() && ($perm->have_studip_perm("admin",$db->f("fakultaets_id")) || $i_view == "new")) {
            if ($_num_inst) {
                echo "\n<font size=\"-1\"><b>" . _("Diese Einrichtung hat den Status einer Fakult&auml;t.") . "<br>";
                printf(_("Es wurden bereits %s andere Einrichtungen zugeordnet."), $_num_inst) . "</b></font>";
                echo "\n<input type=\"hidden\" name=\"Fakultaet\" value=\"$i_id\">";
            } else {
                echo "\n<select name=\"Fakultaet\" style=\"width: 98%\">";
                if ($perm->have_perm("root")) {
                    printf ("<option %s value=\"%s\">" . _("Diese Einrichtung hat den Status einer Fakult&auml;t.") . "</option>", ($db->f("fakultaets_id") == $db->f("Institut_id")) ? "selected" : "", $db->f("Institut_id"));
                    $db2->query("SELECT Institut_id,Name FROM Institute WHERE Institut_id=fakultaets_id AND fakultaets_id !='". $db->f("institut_id") ."' ORDER BY Name");
                } else {
                    $db2->query("SELECT a.Institut_id,Name FROM user_inst a LEFT JOIN Institute USING (Institut_id) WHERE user_id='$user->id' AND inst_perms='admin' AND a.Institut_id=fakultaets_id AND fakultaets_id !='". $db->f("institut_id") ."' ORDER BY Name");
                }
                while ($db2->next_record()) {
                    printf ("<option %s value=\"%s\"> %s</option>", ($db2->f("Institut_id") == $db->f("fakultaets_id"))  ? "selected" : "", $db2->f("Institut_id"),htmlReady($db2->f("Name")));
                }
                echo "</select>";
            }
        } else {
            echo htmlReady($db->f("fak_name")) . "\n<input type=\"hidden\" name=\"Fakultaet\" value=\"" . $db->f("fakultaets_id") . "\">";
        }

        ?>
        </td>
    </tr>
    <tr <? $cssSw->switchClass() ?>>
        <td class="<? echo $cssSw->getClass() ?>" ><?=_("Bezeichnung:")?> </td>
        <td class="<? echo $cssSw->getClass() ?>" ><select style="width: 98%" name="type">
    <?
    $i=0;
    foreach ($INST_TYPE as $a) {
        $i++;
        if ($i==$db->f("type"))
            echo "<option selected value=\"$i\">".$INST_TYPE[$i]["name"]."</option>";
        else
            echo "<option value=\"$i\">".$INST_TYPE[$i]["name"]."</option>";
    }
    ?></select></td>
    </tr>
    <tr <? $cssSw->switchClass() ?>>
        <td class="<? echo $cssSw->getClass() ?>" ><?=_("Stra�e:")?> </td>
        <td class="<? echo $cssSw->getClass() ?>" ><input style="width: 98%" type="text" name="strasse" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Strasse")) ?>"></td>
    </tr>
    <tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Ort:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width: 98%" type="text" name="plz" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Plz")) ?>"></td></tr>
    <tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Telefonnummer:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width: 98%" type="text" name="telefon" size=32 maxlength=254 value="<?php echo htmlReady($db->f("telefon")) ?>"></td></tr>
    <tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Faxnummer:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width: 98%" type="text" name="fax" size=32 maxlength=254 value="<?php echo htmlReady($db->f("fax")) ?>"></td></tr>
    <tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("E-Mail-Adresse:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width: 98%" type="text" name="email" size=32 maxlength=254 value="<?php echo htmlReady($db->f("email")) ?>"></td></tr>
    <tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Homepage:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width: 98%" type="text" name="home" size=32 maxlength=254 value="<?php echo htmlReady($db->f("url")) ?>"></td></tr>
    <?
    //choose preferred lit plugin
    if (get_config('LITERATURE_ENABLE') && $db->f("Institut_id") == $db->f("fakultaets_id")){
        ?><tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Bevorzugter Bibliothekskatalog:")?></td>
        <td class="<? echo $cssSw->getClass() ?>" >
        <select name="lit_plugin_name" style="width: 98%">
        <?
        foreach (StudipLitSearch::GetAvailablePlugins() as $plugin_name => $plugin_display_name){
            echo '<option value="'.$plugin_name.'" ' . ($db->f('lit_plugin_name') == $plugin_name ? 'selected' : '') .' >' . htmlReady($plugin_display_name) . '</option>';
        }
        ?>
        </select>
        </td></tr>
        <?
    }
    //add the free administrable datafields
    $localEntries = DataFieldEntry::getDataFieldEntries($i_id, "inst");
    if ($localEntries) {
      foreach ($localEntries as $entry) {
        $value = $entry->getValue();
        $color = '#000000';
        $id = $entry->structure->getID();
        if ($invalidEntries[$id]) {
            $entry = $invalidEntries[$id];
            $color = '#ff0000';
        }
        if ($entry->structure->accessAllowed($perm)) {
            ?>
            <tr>
                <td class="<? $cssSw->switchClass(); echo $cssSw->getClass(); ?>" >
                   <font color="<?=$color?>"><?=htmlReady($entry->getName())?>:</font>
               </td>
                <td class="<? echo $cssSw->getClass() ?>" >
                    <?
                    if ($perm->have_perm($entry->structure->getEditPerms())) {
                        print $entry->getHTML("datafields");
                    }
                    else
                        print $entry->getDisplayValue();
                    ?>
                </td>
            </tr>
            <?
        }
      }
    }
    ?>
    <tr <? $cssSw->switchClass() ?>>
        <td class="steel2" colspan="2" align="center">

    <?
    if ($i_view != "new") {
        ?>
        <input type="hidden" name="i_id"   value="<?php $db->p("Institut_id") ?>">
        <input type="image" name="i_edit" <?=makeButton("uebernehmen", "src")?> border=0 value=" Ver&auml;ndern ">
        <?
        if ($db->f("number") < 1 && !$_num_inst && ($perm->have_perm("root") || ($perm->is_fak_admin() && get_config('INST_FAK_ADMIN_PERMS') == 'all'))) {
            ?>
            &nbsp;<input type="image" name="i_trykill" <?=makeButton("loeschen", "src")?> border=0 value="L&ouml;schen">
            <?
        }
    } else {
        echo "<input type=\"IMAGE\" name=\"create\" " . makeButton("anlegen", "src") . " border=0 value=\"Anlegen\">";
    }
    ?>
        <input type="hidden" name="i_view" value="<? printf ("%s", ($i_view=="new") ? "create" : $i_view);  ?>">
        </td>
    </tr>
    </table>
    </form>
    </td>
    <td width="270" class="blank" align="right" valign="top">
            <?
            $aktionen = array();
            $aktionen[] = array(
              "icon" => "edit_transparent.gif",
              "text" => '<a href="' .
                        URLHelper::getLink('dispatch.php/institute/avatar/update/' . $i_id) .
                        '">' . _("Bild �ndern") . '</a>');
            $aktionen[] = array(
              "icon" => "trash.gif",
              "text" => '<a href="' .
                        URLHelper::getLink('dispatch.php/institute/avatar/delete/'. $i_id) .
                        '">' . _("Bild l�schen") . '</a>');

            $infobox = array(
                array("kategorie" => _("Aktionen:"),
                      "eintrag"   => $aktionen
            ));
            ?>
            <?= $template_factory->render('infobox/infobox_avatar',
            array('content' => $infobox,
                  'picture' => InstituteAvatar::getAvatar($i_id)->getUrl(Avatar::NORMAL)
            )) ?>
    </td>
    </tr>
    <?
}
echo '</table>';
include ('lib/include/html_end.inc.php');
page_close();
