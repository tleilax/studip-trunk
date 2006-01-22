<?php
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("admin");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

## Set this to something, just something different...
  $hash_secret = "hgeisgczwgebt";
  
## If is set 'cancel', we leave the adminstration form...
if (isset($cancel)) unset ($i_view);

require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php"); //Funktionen f&uuml;r Nachrichtenmeldungen
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/forum.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/datei.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/Modules.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/DataFields.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/StudipLitList.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipLitSearch.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipNews.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/log_events.inc.php");


if ($RESOURCES_ENABLE) {
	include_once($RELATIVE_PATH_RESOURCES."/lib/DeleteResourcesUser.class.php");
}

if ($EXTERN_ENABLE) {
	require_once($RELATIVE_PATH_EXTERN . "/lib/extern_functions.inc.php");
}
	

// Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$cssSw = new cssClassSwitcher;
$Modules = new Modules;
$DataFields = new DataFields();

// Check if there was a submission
while ( is_array($HTTP_POST_VARS) 
     && list($key, $val) = each($HTTP_POST_VARS)) {

  switch ($key) {
 	
	// Create a new Institut
	case "create_x":
	if (!$perm->is_fak_admin()) {
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
	log_event("INST_CREATE",$i_id); // logging

		// Set the default list of modules
		$Modules->writeDefaultStatus($i_id);
		
		// Create default folder and discussion
		CreateTopic(_("Allgemeine Diskussionen"), " ", _("Hier ist Raum f�r allgemeine Diskussionen"), 0, 0, $i_id, 0);
		$db->query("INSERT INTO folder SET folder_id='".md5(uniqid(rand()))."', range_id='".$i_id."', name='" . _("Allgemeiner Dateiordner") . "', description='" . _("Ablage f�r allgemeine Ordner und Dokumente der Einrichtung") . "', mkdate='".time()."', chdate='".time()."'");
 
		$msg="msg�<b>" . sprintf(_("Die Einrichtung \"%s\" wurde angelegt."), htmlReady(stripslashes($Name))) . "</b>";
   
		$i_view = $i_id;

		//This will select the new institute later for navigation (=>links_admin.inc.php)
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
		
		//Update the additional data-fields
		if (StudipForm::IsSended('edit')) {
			$DataFields->storeContentFromForm('edit', $i_id, 'inst');
		}
		$msg="msg�<b>" . sprintf(_("Die Daten der Einrichtung \"%s\" wurden ver&auml;ndert."), htmlReady(stripslashes($Name))) . "</b>";
		break;

	// Delete the Institut
  	case "i_kill_x":

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
		$DataFields->killAllEntries($i_id);
		
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
			$counts = delete_all_configs($i_id);
			if ($counts["records"]) {
				$msg .= "msg�" . sprintf(_("%s Konfigurationsdateien gel&ouml;scht."), $counts["records"]);
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
	
	default:
	break;
	}
}

//workaround
if ($i_view == "new")
	closeObject();
	
//Output starts here

include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   //hier wird der "Kopf" nachgeladen 
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins

//get ID from a open Institut
if ($SessSemName[1])
	$i_view=$SessSemName[1];

?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;
	<?
	if ($i_view == "new") {
		echo _("Anlegen einer neuen Einrichtung");
	} elseif ($i_view == "delete"){
		echo _("Einrichtung gel&ouml;scht");
	} else {
		print getHeaderLine($i_view)." -  " . _("Grunddaten");
	}
	?></b></td>
</tr>
<?
if (isset($msg)) {
?>
<tr> 
	<td class="blank" colspan=2><br />
		<?parse_msg($msg);?>
	</td>
</tr>
<? } ?>
<tr>
	<td class="blank" colspan=2>
		&nbsp;
	</td>
</tr>

<?
if ($i_view=="delete") {
	echo "<tr><td class=\"blank\" colspan=\"2\"><table width=\"70%\" align=\"center\" class=\"steelgraulight\" >";
	echo "<tr><td><br>" . _("Die ausgew�hlte Einrichtung wurde gel&ouml;scht.") . "<br>";
	printf(_("Bitte w�hlen Sie �ber das Schl�sselsymbol %s eine andere Einrichtung aus."), "<a href=\"admin_institut.php?list=TRUE\"><img " . tooltip(_("Neue Auswahl")) . " align=\"absmiddle\" src=\"pictures/admin.gif\" border=\"0\"></a>");
	echo "<br><br></td></tr></table><br><br></td></tr></table></html>";
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
<tr><td class="blank" colspan=2>
<table border=0 align="center" width="60%" cellspacing=0 cellpadding=2>
	<form method="POST" name="edit" action="<? echo $PHP_SELF?>">
	<tr <? $cssSw->switchClass() ?>><td width="40%" class="<? echo $cssSw->getClass() ?>" ><?=_("Name:")?> </td><td width="60%" class="<? echo $cssSw->getClass() ?>" ><input style="width:98%" type="text" name="Name" size=50 maxlength=254 value="<?php echo htmlReady($db->f("Name")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Fakult&auml;t:")?></td>
		<td class="<? echo $cssSw->getClass() ?>" align=left>
		<?php
		if ($perm->is_fak_admin() && ($perm->have_studip_perm("admin",$db->f("fakultaets_id")) || $i_view == "new")) {
			if ($_num_inst) {
				echo "\n<font size=\"-1\"><b>" . _("Diese Einrichtung hat den Status einer Fakult&auml;t.") . "<br>";
				printf(_("Es wurden bereits %s andere Einrichtungen zugeordnet."), $_num_inst) . "</b></font>";
				echo "\n<input type=\"hidden\" name=\"Fakultaet\" value=\"$i_id\">";
			} else {
				echo "\n<select name=\"Fakultaet\" style=\"width:98%\">";
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
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Bezeichnung:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><select style="width:98%" name="type">
	<? 
	$i=0;
	foreach ($INST_TYPE as $a) {
		$i++;
		if ($i==$db->f("type"))
			echo "<option selected value=\"$i\">".$INST_TYPE[$i]["name"]."</option>";
		else
			echo "<option value=\"$i\">".$INST_TYPE[$i]["name"]."</option>";		
	}
	?></select></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Strasse:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width:98%" type="text" name="strasse" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Strasse")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Ort:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width:98%" type="text" name="plz" size=32 maxlength=254 value="<?php echo htmlReady($db->f("Plz")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Telefonnummer:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width:98%" type="text" name="telefon" size=32 maxlength=254 value="<?php echo htmlReady($db->f("telefon")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Faxnummer:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width:98%" type="text" name="fax" size=32 maxlength=254 value="<?php echo htmlReady($db->f("fax")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Emailadresse:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width:98%" type="text" name="email" size=32 maxlength=254 value="<?php echo htmlReady($db->f("email")) ?>"></td></tr>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Homepage:")?> </td><td class="<? echo $cssSw->getClass() ?>" ><input style="width:98%" type="text" name="home" size=32 maxlength=254 value="<?php echo htmlReady($db->f("url")) ?>"></td></tr>
	<?
	//choose preferred lit plugin
	if ($db->f("Institut_id") == $db->f("fakultaets_id")){
		?><tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" ><?=_("Bevorzugter Bibliothekskatalog:")?></td>
		<td class="<? echo $cssSw->getClass() ?>" >
		<select name="lit_plugin_name" style="width:98%">
		<?
		foreach (StudipLitSearch::GetAvailablePlugins() as $plugin_name){
			echo '<option ' . ($db->f('lit_plugin_name') == $plugin_name ? 'selected' : '') .' >' . htmlReady($plugin_name) . '</option>';
		}
		?>
		</select>
		</td></tr>
		<?
	}
	
	//add the free adminstrable datafields
			$datafield_form =& $DataFields->getLocalFieldsFormObject('edit', $i_id, "inst");
			$datafield_form->field_attributes_default = array('style' => 'width:98%');
			echo $datafield_form->getHiddenField(md5("is_sended"),1);
			foreach ($datafield_form->getFormFieldsByName() as $field_id) {
				$cssSw->switchClass();
			
			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>">
					<?=$datafield_form->getFormFieldCaption($field_id)?>:
				</td>
				<td class="<? echo $cssSw->getClass() ?>">
				<?=$datafield_form->getFormField($field_id);?>
				</td>
			</tr>
			<?
			}
	?>
	<tr <? $cssSw->switchClass() ?>><td class="<? echo $cssSw->getClass() ?>" colspan=2 align="center">
	
	<? 
	if ($i_view != "new") {
		?>
		<input type="hidden" name="i_id"   value="<?php $db->p("Institut_id") ?>">
		<input type="IMAGE" name="i_edit" <?=makeButton("uebernehmen", "src")?> border=0 value=" Ver&auml;ndern ">
		<?
		if ($db->f("number") < 1 && !$_num_inst) {
			?>
			&nbsp;<input type="IMAGE" name="i_kill" <?=makeButton("loeschen", "src")?> border=0 value=" L&ouml;schen ">
			<?
		}
	} else {
		echo "<input type=\"IMAGE\" name=\"create\" " . makeButton("anlegen", "src") . " border=0 value=\"Anlegen\">";
	}
	?>
	<input type="hidden" name="i_view" value="<? printf ("%s", ($i_view=="new") ? "create" : $i_view);  ?>">
	</td></tr></table>
	</form>
	<br>
	<?
}


echo"</table>";
page_close();
?>
</body>
</html>
<!-- $Id$ -->
