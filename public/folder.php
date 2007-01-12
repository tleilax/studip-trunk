<?php
/*
folder.php - Anzeige und Verwaltung des Ordnersystems
Copyright (C) 2001 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>

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
//$Id$

ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once('datei.inc.php');
include ('seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once('msg.inc.php');
require_once('visual.inc.php');
require_once('config.inc.php');
require_once 'lib/functions.php';
require_once('lib/classes/StudipDocumentTree.class.php');


$sess->register('folder_system_data');
$db=new DB_Seminar;
$db2=new DB_Seminar;

$folder_tree =& TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

if ($folderzip) {
	$zip_file_id = createFolderZip($folderzip);
	$query = sprintf ('SELECT name FROM folder WHERE folder_id = "%s" ', $folderzip);
	$db->query($query);
	$db->next_record();
	$zip_name = prepareFilename(_("Dateiordner").'_'.$db->f('name').'.zip');
	header('Location: ' . getDownloadLink( $zip_file_id, $zip_name, 4));
	page_close();
	die;
}

if ($download_selected_x) {
	if (is_array($download_ids)) {
		$zip_file_id = createSelectedZip($download_ids);
		$zip_name = prepareFilename($SessSemName[0].'-'._("Dokumente").'.zip');
		header('Location: ' . getDownloadLink( $zip_file_id, $zip_name, 4));
		page_close();
		die;
	}
}

//Switch fuer die Ansichten
if ($cmd == 'tree') {
	$folder_system_data = '';
	$folder_system_data['cmd'] = 'tree';
	}
if ($cmd == 'all') {
	$folder_system_data = '';
	$folder_system_data['cmd'] = 'all';
	}

// Start of Output

$HELP_KEYWORD="Basis.Dateien";

include ('html_head.inc.php'); // Output of html head


include ('header.php');   // Output of Stud.IP head

checkObject();
checkObjectModule('documents');
object_set_visit_module('documents');

include ('links_openobject.inc.php');

//obskuren id+_?_ string zerpfl�cken
if (strpos($open, "_") !== false){
	list($open_id, $open_cmd) = explode('_', $open);
}

//Wenn nicht Rechte und Operation uebermittelt: Ist das mein Dokument und ist der Ordner beschreibbar?
if ((!$rechte) && $open_cmd) {
	$db->query("SELECT user_id,range_id FROM dokumente WHERE dokument_id = '".$open_id."'");
	$db->next_record();
	if (($db->f("user_id") == $user->id) && ($db->f("user_id") != "nobody") && $folder_tree->isWritable($db->f('range_id'), $user->id))
		$owner=TRUE;
	else
		$owner=FALSE;
} else
	$owner=FALSE;

//verschiebemodus abbrechen, wenn andere Aktion ausgew�hlt wurde
if($folder_system_data["mode"] != '' && ($open_cmd && !in_array($open_cmd, array('n','md')))){
	$folder_system_data["move"]='';
	$folder_system_data["mode"]='';
}

if (($rechte) || ($owner)) {
	//wurde Code fuer Anlegen von Ordnern ubermittelt (=id+"_n_"), wird entsprechende Funktion aufgerufen
	if ($open_cmd == 'n' && (!$cancel_x)) {
		$change = create_folder(_("Neuer Ordner"), '', $open_id );
		$open = $change;
		$open_cmd = null;
		}

	//wurde Code fuer Anlegen von Ordnern der obersten Ebene ubermittelt (=id+"_a_"), wird entsprechende Funktion aufgerufen
	if ($open_cmd == 'a') {
		if ($open_id == $SessionSeminar) {
			$titel=_("Allgemeiner Dateiordner");
			$description= sprintf(_("Ablage f�r allgemeine Ordner und Dokumente der %s"), $SessSemName["art_generic"]);
		} else if ($open_id == md5('new_top_folder')){
			$titel = $_REQUEST['top_folder_name'] ? stripslashes($_REQUEST['top_folder_name']) : _("Neuer Ordner");
			$open_id = md5($SessionSeminar . 'top_folder');
		} else {
			$db->query("SELECT date, date_typ, content FROM termine WHERE termin_id='".$open_id."'");
			if ($db->next_record()) {
				//Titel basteln
				$titel=$TERMIN_TYP[$db->f("date_typ")]["name"].": ".substr($db->f("content"), 0, 35);
				if (strlen($db->f("content")) >=35)
					$titel.="...";
				$titel.=" " . _("am") . " ".date("d.m.Y ", $db->f("date"));
				$description= _("Ablage f�r Ordner und Dokumente zu diesem Termin");
				}
		}
		$change = create_folder(addslashes($titel), $description, $open_id);
		$folder_system_data["open"][$change] = TRUE;
		$folder_system_data['open']['anker'] = $change;
		}

	//wurde Code fuer Loeschen von Ordnern ubermittelt (=id+"_d_"), wird entsprechende Funktion aufgerufen
	if ($open_cmd == 'd') {
		if ( ($count = doc_count($open_id)) ){
			$msg="info�" . sprintf(_("Der ausgew�hlte Ordner enth�lt %s Datei(en). Wollen Sie den Ordner wirklich l�schen?"), $count) . "<br>";
			$msg.="<b><a href=\"$PHP_SELF?open=".$open_id."_rd_\">" . makeButton("ja2", "img") . "</a>&nbsp;&nbsp; <a href=\"$PHP_SELF\">" . makeButton("nein", "img") . "</a>�";
		} else {
			delete_folder($open_id, true);
		}
	}

	//Loeschen von Ordnern im wirklich-ernst Mode
	if ($open_cmd == 'rd') {
		delete_folder($open_id, true);
	}

	//wurde Code fuer Loeschen von Dateien ubermittelt (=id+"_fd_"), wird erstmal nachgefragt
	if ($open_cmd == 'fd') {
		$db->query("SELECT filename, ". $_fullname_sql['full'] ." AS fullname, username FROM dokumente LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE dokument_id ='".$open_id."'");
		$db->next_record();
		if (getLinkPath($open_id)) {
			$msg="info�" . sprintf(_("Wollen sie die Verlinkung zu <b>%s</b> von %s wirklich l�schen?"), htmlReady($db->f("filename")), "<a href=\"about.php?username=".$db->f("username")."\">".htmlReady($db->f("fullname"))."</a>") . "<br>";
			$msg.="<b><a href=\"$PHP_SELF?open=".$open_id."_rl_\">" . makeButton("ja2", "img") . "</a>&nbsp;&nbsp; <a href=\"$PHP_SELF\">" . makeButton("nein", "img") . "</a>�";
		} else {
			$msg="info�" . sprintf(_("Wollen sie die Datei <b>%s</b> von %s wirklich l�schen?"), htmlReady($db->f("filename")), "<a href=\"about.php?username=".$db->f("username")."\">".htmlReady($db->f("fullname"))."</a>") . "<br>";
			$msg.="<b><a href=\"$PHP_SELF?open=".$open_id."_rm_\">" . makeButton("ja2", "img") . "</a>&nbsp;&nbsp; <a href=\"$PHP_SELF\">" . makeButton("nein", "img") . "</a>�";
		}
	}

	//Loeschen von Dateien im wirklich-ernst Mode
	if ($open_cmd == 'rm') {
		if (delete_document($open_id))
			$msg.="msg�" . _("Die Datei wurde gel&ouml;scht") . "�";
		else
			$msg.="error�" . _("Die Datei konnte nicht gel&ouml;scht werden") . "�";
		}

	//Loeschen von verlinkten Dateien im wirklich-ernst Mode
	if ($open_cmd == 'rl') {
		if (delete_link($open_id))
			$msg.="msg�" . _("Die Verlinkung wurde gel�scht") . "�";
		else
			$msg.="error�" . _("Die Verlinkung konnte nicht gel�scht werden") . "�";
		}

	//wurde Code fuer Aendern des Namens und der Beschreibung von Ordnern oder Dokumenten ubermittelt (=id+"_c_"), wird entsprechende Funktion aufgerufen
	if ($open_cmd ==  'c') {
		$change=$open_id;
		}

	//wurde Code fuer Speichern von Aenderungen uebermittelt (=id+"_sc_"), wird entsprechende Funktion aufgerufen
	if ($open_cmd == 'sc' && (!$cancel_x)) {
		edit_item($open_id, $type, $change_name, $change_description, $change_protected);
		}

	//wurde Code fuer Verschieben-Vorwaehlen uebermittelt (=id+"_m_"), wird entsprechende Funktion aufgerufen
	if ($open_cmd == 'm' && (!$cancel_x)) {
		$folder_system_data["move"]=$open_id;
		$folder_system_data["mode"]='move';
		}

	//wurde Code fuer Kopieren-Vorwaehlen uebermittelt (=id+"_co_"), wird entsprechende Funktion aufgerufen
	if ($open_cmd == 'co' && (!$cancel_x)) {
		$folder_system_data["move"]=$open_id;
		$folder_system_data["mode"]='copy';
		}
		
	//wurde Code fuer Aktualisieren-Hochladen uebermittelt (=id+"_rfu_"), wird entsprechende Variable gesetzt
	if ($open_cmd == 'rfu' && (!$cancel_x)) {
		$folder_system_data["upload"]=$open_id;
		$folder_system_data["refresh"]=$open_id;
		unset($folder_system_data["zipupload"]);
	}

	//wurde Code fuer Aktualisieren-Verlinken uebermittelt (=id+"_led_"), wird entsprechende Variable gesetzt
	if ($open_cmd == 'led' && (!$cancel_x)) {
		$folder_system_data["link"]=$open_id;
		$folder_system_data["update_link"]=TRUE;
	}
}


//Upload, Check auf Konsistenz mit Seminar-Schreibberechtigung
if (($SemUserStatus == "autor") || ($rechte)) {
	//wurde Code fuer Hochladen uebermittelt (=id+"_u_"), wird entsprechende Variable gesetzt
	if ($open_cmd == 'u' && (!$cancel_x)) {
		$folder_system_data["upload"]=$open_id;
		unset($folder_system_data["zipupload"]);
	}
	if ($open_cmd == 'z' && $rechte  && !$cancel_x) {
		$folder_system_data["upload"]=$open_id;
		$folder_system_data["zipupload"]=$open_id;
	}


	//wurde Code fuer Verlinken uebermittelt (=id+"_l_"), wird entsprechende Variable gesetzt
	if ($open_cmd == 'l' && (!$cancel_x)) {
		$folder_system_data["link"]=$open_id;
	}

	//wurde eine Datei hochgeladen/aktualisiert?
	if (($cmd=="upload") && (!$cancel_x) && ($folder_system_data["upload"])) {
		if (!$folder_system_data["zipupload"]){
			upload_item ($folder_system_data["upload"], TRUE, FALSE, $folder_system_data["refresh"]);
			$open = $dokument_id;
			$close = $folder_system_data["refresh"];
			$folder_system_data["upload"]='';
			$folder_system_data["refresh"]='';
		} elseif ($rechte && get_config('ZIP_UPLOAD_ENABLE')) {
			upload_zip_item();
			$folder_system_data["upload"]='';
			$folder_system_data["zipupload"]='';
		}
		unset($cmd);
		}

	//wurde eine Datei verlinkt?
	if (($cmd=="link") && (!$cancel_x) && ($folder_system_data["link"])) {
		if (link_item ($folder_system_data["link"], TRUE, FALSE, $folder_system_data["refresh"],FALSE)) {
			$open = $dokument_id;
			$close = $folder_system_data["refresh"];
			$folder_system_data["link"]='';
			$folder_system_data["refresh"]='';
			$folder_system_data["update_link"]='';
			unset($cmd);
		} else {
			$folder_system_data["linkerror"]=TRUE;
		}
	}

	//wurde ein Link aktualisiert?
	if (($cmd=="link_update") && (!$cancel_x) && ($folder_system_data["link"])) {
		if (link_item ($range_id, TRUE, FALSE, FALSE, $link_update)) {
			$open = $link_update;
			$close = $folder_system_data["refresh"];
			$folder_system_data["link"]='';
			$folder_system_data["refresh"]='';
			$folder_system_data["update_link"]='';
			unset($cmd);
		} else {
			$folder_system_data["linkerror"]=TRUE;
		}
	}

	if ($_POST['move_to_sem_x'] || $_POST['move_to_inst_x']){
		$new_range_id = ($_POST['move_to_sem_x'] ? $_POST['sem_move_id'] : $_POST['inst_move_id']);
		if ($new_range_id){
			if ($folder_system_data["mode"] == 'move'){
				$done = move_item($folder_system_data["move"], $new_range_id, true);
				if (!$done){
					$msg .= "error�" . _("Verschiebung konnte nicht durchgef�hrt werden.") . "�";
				} else {
					$msg .= "msg�" . sprintf(_("%s Ordner, %s Datei(en) wurden verschoben."), $done[0], $done[1]) . '�';
				}
			} else {
				$done = copy_item($folder_system_data["move"], $new_range_id, true);
				if (!$done){
					$msg .= "error�" . _("Kopieren konnte nicht durchgef�hrt werden.") . "�";
				} else {
					$msg .= "msg�" . sprintf(_("%s Ordner, %s Datei(en) wurden kopiert."), $done[0], $done[1]) . '�';
				}
			}
		}
		$folder_system_data["move"]='';
		$folder_system_data["mode"]='';
	}

	if ($cancel_x)  {
		$folder_system_data["upload"]='';
		$folder_system_data["refresh"]='';
		$folder_system_data["link"]='';
		$folder_system_data["update_link"]='';
		$folder_system_data["move"]='';
		$folder_system_data["mode"]='';
		$folder_system_data["zipupload"]='';
		unset($cmd);
	}
}

//wurde Code fuer Starten der Verschiebung uebermittelt (=id+"_md_"), wird entsprechende Funktion aufgerufen (hier kein Rechtecheck noetig, da Dok_id aus Sess_Variable.
if ($open_cmd == 'md' && $folder_tree->isWritable($open_id) && !$cancel_x) {
	if ($folder_system_data["mode"] == 'move'){
		$done = move_item($folder_system_data["move"], $open_id);
		if (!$done){
			$msg .= "error�" . _("Verschiebung konnte nicht durchgef�hrt werden.") . "�";
		} else {
			$msg .= "msg�" . sprintf(_("%s Ordner, %s Datei(en) wurden verschoben."), $done[0], $done[1]) . '�';
		}
	} else {
		$done = copy_item($folder_system_data["move"], $open_id);
		if (!$done){
			$msg .= "error�" . _("Kopieren konnte nicht durchgef�hrt werden.") . "�";
		} else {
			$msg .= "msg�" . sprintf(_("%s Ordner, %s Datei(en) wurden kopiert."), $done[0], $done[1]) . '�';
		}
	}
	$folder_system_data["move"]='';
	$folder_system_data["mode"]='';
}

//wurde ein weiteres Objekt aufgeklappt?
if (!$open_cmd && isset($open)) {
	$folder_system_data["open"][$open] = true;
	$folder_system_data["open"]['anker'] = $open;
}
//wurde ein Objekt zugeklappt?
if ($close) {
	unset($folder_system_data["open"][$close]);
	$folder_system_data["open"]['anker'] = $close;
}

// Hauptteil

 if (!isset($range_id))
 	$range_id = $SessionSeminar ;

//JS Routinen einbinden, wenn benoetigt. Wird in der Funktion gecheckt, ob noetig...
JS_for_upload();
//we need this <body> tag, sad but true :)
echo "\n<body onUnLoad=\"upload_end()\">";
?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td class="topic" colspan="3"><b>&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/icon-disc.gif" align=absmiddle>&nbsp; <? echo $SessSemName["header_line"] . " - " . _("Dateien"); ?></b></td></tr>

		<tr>
			<td class="blank" colspan=3>&nbsp;
				<?
				if ($msg) parse_msg($msg);
				?>
			</td>
		</tr>


<?
	//Ordner die fehlen, anlegen: Allgemeiner, wenn nicht da, Ordner zu Terminen, die keinen Ordner haben
	if ($rechte){
		if ($folder_system_data['mode']){
			$module_check = new Modules();
			$my_sem = $my_inst = array();
			foreach(search_range('%') as $key => $value){
				if ($module_check->getStatus('documents', $key, $value['type']) && $key != $SessionSeminar){
					if ($value['type'] == 'sem'){
						$my_sem[$key] = $value['name'];
					} else {
						$my_inst[$key] = $value['name'];
					}
				}
			}
			asort($my_sem, SORT_STRING);
			asort($my_inst, SORT_STRING);
			$button_name = ($folder_system_data["mode"] == 'move' ? 'verschieben' : 'kopieren');
			echo '<form action="'.$PHP_SELF.'" method="post">';
			echo "\n" . '<tr><td class="blank" colspan="3" width="100%" style="font-size:80%;">';
			echo "\n" . '<div style="margin-left:25px;">';
			echo "\n<b>" . ($folder_system_data["mode"] == 'move' ? _("Verschiebemodus") : _("Kopiermodus")) . "</b><br>";
			echo _("Ausgew�hltes Objekt in den Allgemeinen Dateiordner einer anderen Veranstaltung oder einer anderen Einrichtung verschieben / kopieren:");
			echo "\n</div></td></tr><tr>";
			echo "\n" .'<td class="blank" width="20%" style="font-size:80%;">';
			echo "\n" . '<div style="margin-left:25px;">';
			echo _("Veranstaltung") .':';
			echo '</div></td><td class="blank" width="60%">';
			echo "\n" . '<input type="image" border="0" src="'.$GLOBALS['ASSETS_URL'].'images/move.gif" name="move_to_sem" ' . tooltip(_("In diese Veranstaltung verschieben / kopieren")) . '>';
			echo "\n" . '<select name="sem_move_id" style="width:90%">';
			foreach ($my_sem as $id => $name){
				echo "\n" . '<option value="'.$id.'">' . htmlReady(my_substr($name,0,70)) . '</option>';
			}
			echo "\n" . '</select>';
			echo "\n</td>";
			echo "\n" . '<td class="blank"><input type="image" border="0" vspace="2"' . makeButton($button_name,'src') . ' name="move_to_sem" ' . tooltip(_("In diese Veranstaltung verschieben / kopieren")) . '>';

			echo "\n</td></tr><tr>";
			echo "\n" .'<td class="blank" width="20%"  style="font-size:80%;">';
			echo "\n" . '<div style="margin-left:25px;">';
			echo _("Einrichtung").':';
			echo '</div></td><td class="blank" width="60%">';
			echo "\n" . '<input type="image" border="0" src="'.$GLOBALS['ASSETS_URL'].'images/move.gif" name="move_to_inst" ' . tooltip(_("In diese Einrichtung verschieben / kopieren")) . '>';
			echo "\n" . '<select name="inst_move_id" style="width:90%">';
			foreach ($my_inst as $id => $name){
				echo "\n" . '<option value="'.$id.'">' . htmlReady(my_substr($name,0,70)) . '</option>';
			}
			echo "\n" . '</select>';
			echo "\n</td>";
			echo "\n" . '<td class="blank"><input type="image" border="0" vspace="2" ' . makeButton($button_name,'src') . ' name="move_to_inst" ' . tooltip(_("In diese Einrichtung verschieben / kopieren")) . '>';

			echo "\n</td></tr><tr>";
			echo "\n" . '<td class="blank" align="center" colspan="3" width="100%" >';
			echo "\n" . '<input type="image" border="0" vspace="2" '.makeButton("abbrechen", "src").' name="cancel" ' . tooltip(_("Verschieben / Kopieren abbrechen")) . '>';
			echo "\n" . '</td></tr></form>';


		} elseif($folder_system_data["cmd"]=="tree") {
			$select = '<option value="' . md5("new_top_folder") . '_a_">' . _("ausw&auml;hlen oder wie Eingabe").' --&gt;</option>';
			$db2->query("SELECT name FROM folder WHERE range_id='$range_id'");
			if (!$db2->affected_rows())
				$select.="\n<option value=\"".$range_id."_a_\">" . _("Allgemeiner Dateiordner") . "</option>";

			$db2->query("SELECT termine.date, folder.name, termin_id, date_typ FROM termine LEFT JOIN folder ON (termin_id = folder.range_id) WHERE termine.range_id='$range_id' ORDER BY name, termine.date");
			while (($db2->next_record()) && (!$db2->f("name"))) {
				$select.="\n<option value=\"".$db2->f("termin_id")."_a_\">" . sprintf(_("Dateiordner zum Termin am %s [%s]"), date("d.m.Y", $db2->f("date")), $TERMIN_TYP[$db2->f("date_typ")]["name"]) . "</option>";
				}

			if ($select) {
				?>
				<tr>
				<td class="blank" colspan="3" width="100%">
				<blockquote>
				<p valign="middle">
				<form action="<? echo $PHP_SELF?>#anker" method="POST">
					<select name="open" style="vertical-align:middle">
						<? echo $select ?>
					</select>
					<input type="text" name="top_folder_name" size="50">
					&nbsp;&nbsp;
					<input type="image" name="anlegen" value="<?=_("Neuer Ordner")?>" align="absmiddle" <?=makeButton("neuerordner", "src")?> border=0 />
				</form>
				</p>
				</blockquote>
				<?
				}
			}
	}

	//when changing, uploading or show all (for download selector), create a form
	if ((($change) || ($folder_system_data["cmd"]=="all")) && (!$folder_system_data["upload"])) {
		echo "<form method=\"post\" action=\"$PHP_SELF\">";
		}

	print "<tr><td class=\"blank\" colspan=\"3\" width=\"100%\">";


	if ($folder_system_data["cmd"]=="all") {
		?>
		<blockquote>
		<? printf (_("Hier sehen Sie alle Dateien, die zu dieser %s eingestellt wurden. Wenn Sie eine neue Datei einstellen m&ouml;chten, w&auml;hlen Sie bitte die Ordneransicht und &ouml;ffnen den Ordner, in den Sie die Datei einstellen wollen."), $SessSemName["art_generic"]); ?>
		</blockquote>
		<?
		if (!$folder_system_data["upload"] && !$folder_system_data["link"])
			print ("<div align=\"right\"><a href=\"$PHP_SELF?check_all=TRUE\">".makeButton("alleauswaehlen")."</a>&nbsp;<input style=\"vertical-align: middle;\" type=\"IMAGE\" name=\"download_selected\" border=\"0\" ".makeButton("herunterladen", "src")." />&nbsp;</div>");
		}

	//Treeview
	if ($folder_system_data["cmd"]=="tree") {
		//Seminar...
		display_folder_system($range_id, 0,$folder_system_data["open"], '', $change, $folder_system_data["move"], $folder_system_data["upload"], FALSE, $folder_system_data["refresh"], $folder_system_data["link"]);

		display_folder_system(md5($SessionSeminar . 'top_folder'), 0,$folder_system_data["open"], '', $change, $folder_system_data["move"], $folder_system_data["upload"], FALSE, $folder_system_data["refresh"], $folder_system_data["link"]);

		//Alle Termine der Veranstaltung holen
		$db->query("SELECT termin_id FROM termine INNER JOIN folder ON(termin_id=folder.range_id) WHERE termine.range_id='$range_id' ORDER BY date");
		while ($db->next_record()) {
			//und einzelne Termine
			display_folder_system($db->f("termin_id"), 0,$folder_system_data["open"], '', $change, $folder_system_data["move"], $folder_system_data["upload"], FALSE, $folder_system_data["refresh"], $folder_system_data["link"]);
			}
		}

	//Alle / Listview
	else {
		?><table border=0 cellpadding=0 cellspacing=0 width="100%"><tr><?
		display_folder_system($range_id, 0,$folder_system_data["open"], '', $change, $folder_system_data["move"], $folder_system_data["upload"], TRUE, $folder_system_data["refresh"], $folder_system_data["link"]);
		?><td class="blank" width="*">&nbsp;</td></tr></table><?
		}

	//und Form wieder schliessen
	if ($change)
		echo "</form>";

	$folder_system_data["linkerror"]="";
?>
<br>
</td>
</tr>
</table>
<br>
<?php
// Save data back to database.
page_close();
?>
</body>
</html>
