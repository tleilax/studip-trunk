<?php
/*
admin_admission.php - Terminmetadatenverwaltung von Stud.IP
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, data-quest <info@data-quest.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$perm->check("tutor");

?>
<html>
<head>
	<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
	<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
	<body bgcolor=white>

	<script language="javascript" src="md5.js"></script>

	<script language="javascript">
	<!--
	function doCrypt() {
		document.form_3.hashpass.value = MD5(document.form_3.password.value);
		document.form_3.password.value = "";
		document.form_3.password2.value = "";
		return true;
	}
	
	function checkpassword(){
		var checked = true;
		if ((document.details.password.value.length<4) && (document.details.password.value.length != 0)) {
			alert("Das Passwort ist zu kurz \n- es sollte mindestens 4 Zeichen lang sein.");
			document.details.password.focus();
			checked = false;
		}
		return checked;
	}

	function checkpassword2(){
	var checked = true;
	if (document.details.password.value != document.details.password2.value) {
		alert("Das Passwort stimmt nicht mit dem Wiederholungspasswortt �berein!");
		document.details.password2.focus();
		checked = false;
		}
		return checked;
	}
	// -->
	</script>
</head>

<?

include "$ABSOLUTE_PATH_STUDIP/seminar_open.php"; 	//hier werden die sessions initialisiert
include "$ABSOLUTE_PATH_STUDIP/header.php";   		//hier wird der "Kopf" nachgeladen
include "$ABSOLUTE_PATH_STUDIP/links_admin.inc.php";	//hier wird das Reiter- und Suchsystem des Adminbereichs eingebunden

require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");	//Ausgaben
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");	//Settings....
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");	//basale Funktionen
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");	//Darstellungsfunktionen
require_once("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");	//Nachrichtenfunktionen

$db=new DB_Seminar;
$db2=new DB_Seminar;
$cssSw=new cssClassSwitcher;
$sess->register("admin_admission_data");
$messaging=new messaging;

//wenn wir frisch reinkommen, werden benoetigte Daten eingelesen
if (($seminar_id) && (!$uebernehmen_x) &&(!$adm_null_x) &&(!$adm_los_x) &&(!$adm_chrono_x) && (!$add_studg_x) && (!$delete_studg)) {
	$db->query("SELECT admission_turnout, admission_type, admission_selection_take_place, admission_endtime, admission_binding, status, Passwort, Institut_id, Name, start_time, metadata_dates, Lesezugriff, Schreibzugriff FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();
	$admin_admission_data='';	
	$admin_admission_data["metadata_dates"]=unserialize($db->f("metadata_dates"));
	$admin_admission_data["admission_turnout"]=$db->f("admission_turnout");
	$admin_admission_data["admission_type"]=$db->f("admission_type");
	$admin_admission_data["admission_type_org"]=$db->f("admission_type");	
	$admin_admission_data["admission_selection_take_place"]=$db->f("admission_selection_take_place");	
	$admin_admission_data["admission_endtime"]=$db->f("admission_endtime");
	$admin_admission_data["admission_binding"]=$db->f("admission_binding");
	$admin_admission_data["heimat_inst_id"]=$db->f("Institut_id"); 
	$admin_admission_data["passwort"]=$db->f("Passwort");	
	$admin_admission_data["name"]=$db->f("Name");	
	$admin_admission_data["status"]=$db->f("status");	
	$admin_admission_data["start_time"]=$db->f("start_time");	
	$admin_admission_data["read_level"]=$db->f("Lesezugriff");	
	$admin_admission_data["write_level"]=$db->f("Schreibzugriff");	
	$admin_admission_data["sem_id"]=$seminar_id;
	if (!$admin_admission_data["admission_endtime"]) $admin_admission_data["admission_endtime"] =-1;
	$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id = '$seminar_id'");
	while ($db->next_record()) {
		if ($db->f("studiengang_id") == "all")
			$admin_admission_data["all_ratio"]	=$db->f("quota");
		else
			$admin_admission_data["studg"][$db->f("studiengang_id")]=array("name"=>$db->f("name"), "ratio"=>$db->f("quota"));
	}

//nur wenn wir schon Daten haben kann was zurueckkommen
} else {
	//Sicherheitscheck ob ueberhaupt was zum Bearbeiten gewaehlt ist.
	if (!$admin_admission_data["sem_id"]) {
		echo "</tr></td></table>";
		die;
	}
	
	//Umschalter zwischen den Typen
	if ($adm_null_x)
		$admin_admission_data["admission_type"]=0;
	if ($adm_los_x)
		$admin_admission_data["admission_type"]=1;
	if ($adm_chrono_x)
		$admin_admission_data["admission_type"]=2;

	//Aenderungen ubernehmen
	$admin_admission_data["admission_binding"]=$admission_binding;
	if ($admin_admission_data["admission_binding"])
		$admin_admission_data["admission_binding"]=TRUE;
	
	if (!$admin_admission_data["admission_type"]) { 
		$admin_admission_data["read_level"]=$read_level;
		$admin_admission_data["write_level"]=$write_level;
		
	//Alles was mit der Anmeldung zu tun hat ab hier
	} elseif (!$delete_studg) { 
		if (!$commit_no_admission_data)
			$admin_admission_data["admission_turnout"]=$admission_turnout;	

		//Hat der User an den automatischen Werte rumgefuscht? Dann denkt er sich wohl was :) (und wir benutzen die Automatik spaeter nicht!)
		if ($all_ratio_old != $all_ratio) {
			$admin_admission_data["admission_ratios_changed"]=TRUE;
			$admin_admission_data["all_ratio"]=$all_ratio;
		}

		//Studienbereiche entgegennehmen
		if (is_array($studg_id)) {
			foreach ($studg_id as $key=>$val)
				if ($studg_ratio_old[$key] != $studg_ratio[$key])
					$admin_admission_data["admission_ratios_changed"]=TRUE;
			if ($admin_admission_data["admission_ratios_changed"]) {
				$admin_admission_data["studg"]='';
				foreach ($studg_id as $key=>$val)
					$admin_admission_data["studg"][$val]=array("name"=>$studg_name[$key], "ratio"=>$studg_ratio[$key]);
			}
		}	
	
		//Datum fuer Ende der Anmeldung umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
		if (!$commit_no_admission_data) { //wenn Ansicht gesperrt ist (Dozentenview) hier keine Ubernahmen

			if ($admin_admission_data["admission_type"] == 1)
				$end_date_name="Losdatum";
			else
				$end_date_name="Enddatum der Kontingentierung";		

			if (($adm_jahr>0) && ($adm_jahr<100))
				 $adm_jahr=$adm_jahr+2000;

			if ($adm_monat == "mm") $adm_monat=0;
			if ($adm_tag == "tt") $adm_tag=0;
			if ($adm_jahr == "jjjj") $adm_jahr=0;	
			if ($adm_stunde == "hh") $adm_stunde=0;
			if ($adm_minute == "mm") $adm_minute=0;

			if (!checkdate($adm_monat, $adm_tag, $adm_jahr) && ($adm_monat) && ($adm_tag) && ($adm_jahr)) {
				$errormsg=$errormsg."error�Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r das $end_date_name ein!�";
				$check=FALSE;			
			} else
				$check=TRUE;

			if (($adm_monat) && ($adm_tag) && ($adm_jahr))
				if (!$adm_stunde) {
					$errormsg=$errormsg."error�Bitte geben Sie g&uuml;ltige Werte f&uuml;r das $end_date_name ein!�"; 
					$check=FALSE;
				} else
					$check=TRUE;

			if ($check)
				$admin_admission_data["admission_endtime"] = mktime($adm_stunde,$adm_minute,59,$adm_monat,$adm_tag,$adm_jahr);
			else
				$admin_admission_data["admission_endtime"] = -1;
		}
		
		//in Anmeldeverfahren beides immer auf '3'
		$admin_admission_data["read_level"]=3;
		$admin_admission_data["write_level"]=3;
	}
	
	//Studiengang hinzufuegen
	if ($add_studg_x)
		if ($add_studg) {
			$db->query("SELECT name FROM studiengaenge WHERE studiengang_id='".$add_studg."' ");
			$db->next_record();
			$admin_admission_data["studg"][$add_studg]=array("name"=>$db->f("name"), "ratio"=>$add_ratio);
		}

	//Studiengang loeschen
	if ($delete_studg)
		unset($admin_admission_data["studg"][$delete_studg]);
 
	//Checks performen
	if (!$admin_admission_data["admission_type"]) {
		if (($admin_admission_data["write_level"]) <($admin_admission_data["read_level"])) 
			$errormsg=$errormsg."error�Es macht keinen Sinn, die Sicherheitsstufe f&uuml;r den Lesezugriff h&ouml;her zu setzen als f&uuml;r den Schreibzugriff!�";

		if (($admin_admission_data["read_level"] ==2) ||  ($admin_admission_data["write_level"] ==2)) {
       			//Password bei Bedarf dann doch noch verschlusseln
			if (empty($hashpass)) { // javascript disabled 											
   				if (!$password)
       					$admin_admission_data["passwort"] = "";
				elseif($password != "*******") {
					$admin_admission_data["passwort"] = md5($password);
	     					if($password2 != "*******")
    							$check_pw = md5($password2);
	    			}
    			} elseif ($hashpass != md5("*******")) { // javascript enabled
				$admin_admission_data["passwort"]= $hashpass;
				$check_pw = $hashpass2;
			}
	
			if ($admin_admission_data["passwort"]=="")
       			  	$errormsg=$errormsg."error�Sie haben kein Passwort eingegeben! Bitte geben Sie ein Passwort ein!�";
		      	elseif (isset($check_pw) AND $admin_admission_data["passwort"] != $check_pw) {
					$errormsg=$errormsg."error�Das eingegebene Passwort und das Wiederholungspasswort stimmen nicht &uuml;berein!�";
     					$admin_admission_data["passwort"] = "";
			}
		}

	//Checks bei Anmeldeverfahren
	} elseif ((!$adm_chrono_x) && (!$adm_los_x))  {
		//max. Teilnehmerzahl checken
		if ($uebernehmen_x)
			if (($admin_admission_data["admission_turnout"] < 5) && ($admin_admission_data["admission_type"])) {
				$errormsg=$errormsg."error�Wenn Sie sie die Teilnahmebeschr&auml;nkung benutzen wollen, m&uuml;ssen sie wenigsten 5 Teilnehmer zulassen.�";
				$admin_admission_data["admission_turnout"] =5;
			}
	
		//Prozentangabe checken/berechnen wenn neueer Studiengang, einer geloescht oder Seite abgeschickt
		if (($add_studg_x) || ($delete_studg) || ($uebernehmen_x)) {
			if (($admin_admission_data["admission_type"]) && (!$admin_admission_data["admission_type_org"])) {
				if ((!$admin_admission_data["admission_ratios_changed"]) && (!$add_ratio)) {//User hat nichts veraendert oder neuen Studiengang mit Wert geschickt, wir koennen automatisch rechnen
					if (is_array($admin_admission_data["studg"]))
						foreach ($admin_admission_data["studg"] as $key=>$val)
							$admin_admission_data["studg"][$key]["ratio"]=round(100 / (sizeof ($admin_admission_data["studg"]) + 1));
					$admin_admission_data["all_ratio"]=100 - (sizeof ($admin_admission_data["studg"])) * round(100 / (sizeof ($admin_admission_data["studg"]) + 1));
				} else {
					$cnt=0;
					if (is_array($admin_admission_data["studg"]))
						foreach ($admin_admission_data["studg"] as $val)
							$cnt+=$val["ratio"];
					if (($cnt + $admin_admission_data["all_ratio"]) < 100)
						$admin_admission_data["all_ratio"]=100 - $cnt;
					if (($cnt + $admin_admission_data["all_ratio"]) > 100)
						if ($cnt < 100)
							$admin_admission_data["all_ratio"]=(100 - $cnt);
						else 
							$errormsg.=sprintf ("error�Die Werte der einzelnen Kontigente &uuml;bersteigen 100%%. Bitte &auml;ndern Sie die Kontigente!�");	
				}
			}
		}
	
		//Ende der Anmeldung checken
		if ($uebernehmen_x)
			if (($admin_admission_data["admission_type"]) && ($admin_admission_data["admission_endtime"])) {
				if ($admin_admission_data["admission_type"] == 1)
					$end_date_name="Losdatum";
				else
					$end_date_name="Enddatum der Kontingentierung";		
				if ($admin_admission_data["admission_endtime"] == -1) 
					$errormsg.="error�Bitte geben Sie einen Termin f&uuml;r das $end_date_name an!�";	
				$tmp_first_date=veranstaltung_beginn ($admin_admission_data["metadata_dates"]["art"], $admin_admission_data["start_time"], $admin_admission_data["metadata_dates"]["start_woche"], $admin_admission_data["metadata_dates"]["start_termin"], $admin_admission_data["metadata_dates"]["turnus_data"], "int");
				if ($admin_admission_data["admission_endtime"] > $tmp_first_date)
					if ($tmp_first_date > 0)
						$errormsg.= sprintf ("error�Das $end_date_name liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern sie das $end_date_name!�", date ("d.m.Y", $tmp_first_date));
				if (!$admin_admission_data["admission_selection_take_place"]) {
					if (($admin_admission_data["admission_endtime"] < time()) && ($admin_admission_data["admission_endtime"] != -1))
						$errormsg.=sprintf ("error�Das $end_date_name liegt in der Vergangenheit. Bitte &auml;ndern sie das $end_date_name!�");	
					elseif (($admin_admission_data["admission_endtime"] < (time() + (24 * 60 *60))) && ($admin_admission_data["admission_endtime"] != -1))
						$errormsg.=sprintf ("error�Das $end_date_name liegt zu nah am aktuellen Datum. Bitte &auml;ndern sie das $end_date_name!�");	
				}
			}
	}

	//Meldung beim Wechseln des Modis
	if (($adm_type_old != $admin_admission_data["admission_type"]) && (!$commit_no_admission_data))
		if ($admin_admission_data["admission_type"] > 0)
			$infomsg.=sprintf ("info�Sie haben ein Anmeldeverfahren vorgesehen. Beachten Sie bitte, dass nach dem &Uuml;bernehmen dieser Einstellung alle bereits eingetragenen Nutzer aus der Veranstaltung entfernt werden und das Anmeldeverfahren anschliessend nicht mehr abgeschaltet werden kann!�");

	//Daten speichern
	if (($uebernehmen_x) && (!$errormsg)) {
		
		$db->query ("UPDATE seminare SET 
				admission_turnout = '".$admin_admission_data["admission_turnout"]."' , 
				admission_type = '".$admin_admission_data["admission_type"]."', 
				admission_endtime= '".$admin_admission_data["admission_endtime"]."', 
				admission_binding = '".$admin_admission_data["admission_binding"]."', 
				Passwort = '".$admin_admission_data["passwort"]."',
				Lesezugriff = '".$admin_admission_data["read_level"]."', 
				Schreibzugriff  = '".$admin_admission_data["write_level"]."' 
				WHERE seminar_id = '".$admin_admission_data["sem_id"]."' ");
				
		if ($db->affected_rows()) {
			$errormsg.="msg�Die Berechtigungseinstellungen wurden aktualisiert�";
			$db->query ("UPDATE seminare SET chdate='".time()."' WHERE Seminar_id ='".$admin_admission_data["sem_id"]."'");
			}

		//Variante nachtraeglich Anmeldeverfahren starten, alle alten Teilnehmer muessen raus
		if (($admin_admission_data["admission_type"] >$admin_admission_data["admission_type_org"]) && ($admin_admission_data["admission_type_org"]==0)) {	
			$db->query("SELECT seminar_user.user_id, username FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE Seminar_id ='".$admin_admission_data["sem_id"]."' AND status IN ('autor', 'user') ");
			$db2->query("DELETE FROM seminar_user WHERE Seminar_id ='".$admin_admission_data["sem_id"]."' AND status IN ('autor', 'user') ");
			if ($db2->affected_rows()) {
				while ($db->next_record()) {
					$message="Ihr Abonnement der Veranstaltung **".$admin_admission_data["name"]."** wurde aufgehoben, da die Veranstaltung mit einem teilnehmerbeschr�nkten Anmeldeverfahren versehen wurde. \nWenn Sie einen Platz in der Veranstaltrung bekommen wollen, melden Sie sich bitte erneut an.";
					$messaging->insert_sms ($db->f("username"), $message, "____%system%____");
					}
			}

			//Kill old data
			$db2->query ("DELETE FROM admission_seminar_studiengang WHERE seminar_id= '".$admin_admission_data["sem_id"]."' ");
			$admin_admission_data["write_level"]='';
			$admin_admission_data["read_level"]='';
			$admin_admission_data["passwort"]='';
		}

		//Variante nachtraeglich Anmeldeverfahren beenden, alle aus Warteliste kommen in die Veranstaltung
		if (($admin_admission_data["admission_type"] == 0) && ($admin_admission_data["admission_type_org"] > 0)) {
			$db->query("SELECT admission_seminar_user.user_id, username  FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id ='".$admin_admission_data["sem_id"]."' ");
			while ($db->next_record()) {
				$group=select_group ($admin_admission_data["start_time"], $db->f("user_id"));		
				$db2->query("INSERT INTO seminar_user SET user_id = '".$db->f("user_id")."', Seminar_id = '".$admin_admission_data["sem_id"]."', status='autor', gruppe='$group', mkdate ='".time()."' ");
				$message="Sie wurden in die Veranstaltung **".$admin_admission_data["name"]."** eingetragen, da das Anmeldeverfahren aufgehoben wurde. Damit sind Sie als Teilnehmer der Pr�senzveranstaltung zugelassen.";
				$messaging->insert_sms ($db->f("username"), $message, "____%system%____");
			}
			if ($db->num_rows())
				$db2->query("DELETE FROM admission_seminar_user  WHERE seminar_id ='".$admin_admission_data["sem_id"]."' ");
			
			//Kill old Studiengang entries and data
			$db2->query ("DELETE FROM admission_seminar_studiengang WHERE seminar_id= '".$admin_admission_data["sem_id"]."' ");
			$admin_admission_data["studg"]='';
			$admin_admission_data["all_ratio"]='';
			$admin_admission_data["admission_ratios_changed"]='';
			$admin_admission_data["admission_endtime"]='';
		}
		
		//Eintrag der zugelassen Studienbereiche
		if ($admin_admission_data["admission_type"]) {
			$query = "DELETE FROM admission_seminar_studiengang WHERE seminar_id= '".$admin_admission_data["sem_id"]."' ";
			$db->query($query); // Alle Eintraege rauswerfen

			if (is_array($admin_admission_data["studg"]))
				foreach($admin_admission_data["studg"] as $key=>$val)
					if ($val["ratio"]) {
						$query = "INSERT INTO admission_seminar_studiengang VALUES('".$admin_admission_data["sem_id"]."', '$key', '".$val["ratio"]."' )";
						$db->query($query);// Studiengang eintragen
					}

			if ($admin_admission_data["all_ratio"]) {
				$query = "INSERT INTO admission_seminar_studiengang VALUES('".$admin_admission_data["sem_id"]."', 'all', '".$admin_admission_data["all_ratio"]."' )";
				$db->query($query);// Studiengang eintragen
			}
		}
	}
}

//Beim Umschalten keine Fehlermeldung
 if (($errormsg) && ((!$uebernehmen_x) &&(!$adm_null_x) &&(!$adm_los_x) &&(!$adm_chrono_x) && (!$add_studg_x) && (!$delete_studg)))
 	$errormsg='';	
 
?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2>&nbsp; <b>
		<?
		if ($SEM_TYPE[$admin_admission_data["status"]]["name"] == $SEM_TYPE_MISC_NAME) 	
			$tmp_typ = "Veranstaltung"; 
		else
			$tmp_typ = $SEM_TYPE[$admin_admission_data["status"]]["name"];

		echo $tmp_typ, ": ",htmlReady(substr($admin_admission_data["name"], 0, 60));
		if (strlen($admin_admission_data["name"]) > 60)
			echo "... ";
		echo " -  Zugangsberechtigungen";
		?>
		</td>
	</tr>
	<?
	$errormsg.=$infomsg;
	if (isset($errormsg)) {
	?>
	<tr> 
		<td class="blank" colspan=2><br />
		<?parse_msg($errormsg);?>
		</td>
	</tr>
	<? } ?>
 	<tr>
		<td class="blank" valign="top">
			<br />
			<blockquote>
			<b>Zugangsberechtigungen der Veranstaltung bearbeiten</b><br /><br />
			Sie k&ouml;nnen hier die Zugangsberechtigungen bearbeiten. <br />
			Sie haben auf dieser Seite ebenfalls die M&ouml;glichkeit, ein Anmeldeverfahren f&uuml;r die Veranstaltung festzulegen<br />
			</blockqoute>
		</td>
		<td class="blank" align="right">
			<img src="pictures/board2.jpg" border="0">
		</td>
	</tr>
	<tr>
	<td class="blank" colspan=2>
	<form method="POST" action="<? echo $PHP_SELF ?>">
		<table width="99%" border=0 cellpadding=2 cellspacing=0 align="center">
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>		
				<font size=-1>Diese Daten&nbsp; </font>
				<input type="IMAGE" name="uebernehmen" src="./pictures/buttons/uebernehmen-button.gif" border=0 value="uebernehmen">
			</td>
		</tr>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align="left">
				<font size=-1><b>Anmeldeverfahren:</b><br /></font>
				<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
					$db->query("SELECT username, Vorname, Nachname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) WHERE institut_id ='".$admin_admission_data["heimat_inst_id"]."' AND perms = 'admin'");
					printf ("<font size=-1>Sie haben das Anmeldeverfahren %s aktiviert. Dieser Schritt kann </font><font size=-1 color=\"red\"><b>nicht</b></font><font size=-1> r&uuml;ckg&auml;ngig gemacht werden! Bei Problemen wenden sie sich bitte an einen der %sAdministratoren.<br /></font>", ($admin_admission_data["admission_type_org"] == 1) ? "per Los" : "in Anmeldereihenfolge", ($db->num_rows()) ? "folgenden " : "");
					printf ("<input type=\"HIDDEN\" name=\"commit_no_admission_data\" value=\"TRUE\" />");
					while ($db->next_record()) {
						printf ("<li><font size=-1><a href=\"about?username=%s\">%s %s</a></font></li>", $db->f("username"), $db->f("Vorname"), $db->f("Nachname"));
					}
				} else { ?>
				<font size=-1>Sie k&ouml;nnen hier eine Teilnehmerbeschr&auml;nkung per Anmeldeverfahren festlegen. Sie k&ouml;nnen per Losverfahren beschr&auml;nken oder chronologisches Anmelden zulassen.<br /></font>
				<br /><input type="IMAGE" name="adm_null" src="./pictures/buttons/keins<? if ($admin_admission_data["admission_type"] == 0) echo "2" ?>-button.gif" border=0 value="keins">&nbsp; 
				<input type="IMAGE" name="adm_los" src="./pictures/buttons/los<? if ($admin_admission_data["admission_type"] == 1) echo "2" ?>-button.gif" border=0 value="los">&nbsp; 
				<input type="IMAGE" name="adm_chrono" src="./pictures/buttons/chronolog<? if ($admin_admission_data["admission_type"] == 2) echo "2" ?>-button.gif" border=0 value="chronolog">
				<input type="HIDDEN" name="adm_type_old" value="<? echo $admin_admission_data["admission_type"] ?>" />
				<? } ?>
			</td>
		</tr>
		<?
		if (!$admin_admission_data["admission_type"]) {
		?>
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>" colspan=2 align="left">
				<font size=-1><b>Berechtigungen:</b><br /></font>
				<font size=-1>Legen Sie hier fest, welche Teilnehmer Zugriff auf die Veranstaltung haben.<br /></font>
			</td>
		</tr>
		<tr>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="20%" align="left">
			<?					
			if (!isset($admin_admission_data["read_level"]) || $admin_admission_data["read_level"]==3)
				$admin_admission_data["read_level"]= "1";	//Vorgabe: nur angemeldet oder es war Teilnahmebegrenzung gesetzt
				?>
				<font size=-1><u>Lesezugriff:</u> </font><br />
				<font size=-1>
				<input type="radio" name="read_level" value="0" <?php print $admin_admission_data["read_level"] == 0 ? "checked" : ""?>> freier Zugriff &nbsp;<br />
				<input type="radio" name="read_level" value="1" <?php print $admin_admission_data["read_level"] == 1 ? "checked" : ""?>> in Stud.IP angemeldet &nbsp;<br />
				<input type="radio" name="read_level" value="2" <?php print $admin_admission_data["read_level"] == 2 ? "checked" : ""?>> nur mit Passwort &nbsp;<br />
				</font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="76%" align="left">
					&nbsp;<font size=-1><u>Schreibzugriff:</u> </font><br />
					<font size=-1>
			<?
			if (!isset($admin_admission_data["write_level"]) || $admin_admission_data["write_level"]==3)
				$admin_admission_data["write_level"] = "1";	//Vorgabe: nur angemeldet
				if ($SEM_CLASS[$SEM_TYPE[$admin_admission_data["status"]]["class"]]["write_access_nobody"]) {
				?>
				<input type="radio" name="write_level" value="0" <?php print $admin_admission_data["write_level"] == 0 ? "checked" : ""?>> freier Zugriff &nbsp;<br />
				<?
				} else {
				?>
				<font color=#BBBBBB>&nbsp; &nbsp; &nbsp;  freier Zugriff &nbsp;</font><br />
				<?
				}
				?>
				<input type="radio" name="write_level" value="1" <?php print $admin_admission_data["write_level"] == 1 ? "checked" : ""?>> in Stud.IP angemeldet &nbsp;<br />
				<input type="radio" name="write_level" value="2" <?php print $admin_admission_data["write_level"] == 2 ? "checked" : ""?>> nur mit Passwort &nbsp;<br />
				</font>
			</td>
			</tr>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b>Passwort: </b></font><br />
					<font size=-1>Bitte geben Sie hier ein Passwort ein, wenn sie Zugriff nur mit Passwort gew&auml;hlt haben.</font><br /><br />
					<?
					if ($admin_admission_data["passwort"]!="")
						echo "<font size=-1><input type=\"password\" name=\"password\"  onchange=\"checkpassword()\" size=12 maxlength=31 value=\"*******\">&nbsp; Passwort-Wiederholung:&nbsp; <input type=\"password\" name=\"password2\" onchange=\"checkpassword2()\" size=12 maxlength=31 value=\"*******\"></font>";
					else	
						echo "<font size=-1><input type=\"password\" name=\"password\" onchange=\"checkpassword()\" size=12 maxlength=31> &nbsp; Passwort-Wiederholung:&nbsp; <input type=\"password\" name=\"password2\" onchange=\"checkpassword2()\" size=12 maxlength=31></font>";
					?>
				</td>
			</tr>
		<?
		} else {
		?>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b>maximale Teilnehmeranzahl: </b></font><br />
					<font size=-1>Diese Teilnehmeranzahl dient als Grundlage zur Berechnung der Pl&auml;tze pro Kontingent.</font><br /><br />
					<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
						printf ("<font size=-1>%s Teilnehmer </font>", $admin_admission_data["admission_turnout"]);
					} else { ?>
					<font size=-1><input type="TEXT" name="admission_turnout" size=2 maxlength=2 value="<? echo $admin_admission_data["admission_turnout"]; ?>" /> Teilnehmer</font>
					<? } ?>
				</td>
			</tr>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<table border=0 cellpadding=2 cellspacing=0>
						<tr>
							<font size=-1><b>zugelassenene Studieng&auml;nge: </b></font><br />
							<td class="<? echo $cssSw->getClass() ?>" colspan=3 width="100%">
								<font size=-1>Bitte geben Sie hier ein, welche Studieng&auml;nge im Anmeldeverfahren zugelassen sind.</font>
							</td>
						</tr>
						<tr>
							<td class="<? echo $cssSw->getClass() ?>" valign="bottom" width="25%">
									<font size=-1>
									<?
									printf ("%s", ($admin_admission_data["studg"]) ? "Alle anderen Studieng&auml;nge" : "Alle Studieng&auml;nge");
									?>
									</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" valign="bottom"  colspan=2 nowrap width="75%">
								<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
									printf ("&nbsp; &nbsp; <font size=-1>%s %%</font>", $admin_admission_data["all_ratio"]);
								} else {
									printf ("<input type=\"HIDDEN\" name=\"all_ratio_old\" value=\"%s\" />", ($admin_admission_data["studg"]) ? $admin_admission_data["all_ratio"] : "100");
									printf ("<input type=\"TEXT\" name=\"all_ratio\" size=5 maxlength=5 value=\"%s\" /> <font size=-1> %%</font>", ($admin_admission_data["studg"]) ? $admin_admission_data["all_ratio"] : "100");
									} ?> 
								</td>
							</tr>
							<?
							if ($admin_admission_data["studg"]) {
								foreach ($admin_admission_data["studg"] as $key=>$val) {
							?>
							<tr>
								<td class="<? echo $cssSw->getClass() ?>" width="25%">
								<font size=-1>
								<?
								echo (htmlReady(my_substr($val["name"], 0, 40)));
								?>
								</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" nowrap colspan=2 width="75%">
								<input type="HIDDEN" name="studg_id[]" value="<? echo $key ?>" />
								<input type="HIDDEN" name="studg_name[]" value="<? echo $val["name"] ?>" />
								<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
									printf ("&nbsp; &nbsp; <font size=-1>%s %%</font>", $val["ratio"]);
								} else {
									printf ("<input type=\"HIDDEN\" name=\"studg_ratio_old[]\" value=\"%s\" />", $val["ratio"]);
									printf ("<input type=\"TEXT\" name=\"studg_ratio[]\" size=5 maxlength=5 value=\"%s\" /><font size=-1> %%</font>", $val["ratio"]);
									printf ("&nbsp; <a href=\"%s?delete_studg=%s\"><img border=0 src=\"./pictures/trash.gif\" alt=\"Den Studiengang %s aus der Liste l&ouml;schen\" />", $PHP_SELF, $key, $val["name"]);
								}
								?>
								</td>
							</tr>
							<?
								}
							}
							$db->query("SELECT * FROM studiengaenge");
							if ($db->num_rows() != sizeof($admin_admission_data["studg"])) {
								if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
									;
									} else {
								?>
							<tr>
								<td class="<? echo $cssSw->getClass() ?>" width="25%">
								<font size=-1>
								<select name="add_studg">
								<option value="">-- bitte ausw&auml;hlen --</option>
								<?
								while ($db->next_record()) {
									if (is_array($admin_admission_data["studg"])) {
										if (!$admin_admission_data["studg"][$db->f("studiengang_id")])
											printf ("<option value=%s>%s</option>", $db->f("studiengang_id"), htmlReady(my_substr($db->f("name"), 0, 40)));
										}
									else
										printf ("<option value=%s>%s</option>", $db->f("studiengang_id"), htmlReady(my_substr($db->f("name"), 0, 40)));					
								}
								?>
								</select>
								</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" nowrap width="5%">
								<input type="TEXT" name="add_ratio" size=5 maxlength=5 /><font size=-1> %</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="25%">
									&nbsp;<input type="IMAGE" src="./pictures/buttons/hinzufuegen-button.gif" name="add_studg" border=0 />&nbsp;
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="40%">&nbsp; 
								</td>
							</tr>
								<?
								} 
							}
							?>
					</table>
				</td>
			</tr>
			<tr  <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b><? if ($admin_admission_data["admission_type"] == 1) echo "Losdatum"; else echo "Enddatum der Kontingentierung";?>:</b></font><br />
					<? 
					if ($admin_admission_data["admission_type"] == 1) {
						?>
						<font size=-1>Bitte geben Sie hier ein, wann die Anw&auml;rter auf der Anmeldeliste in die Veranstaltung gelost werden.</font><br /><br />
						<? 
					} else {
						?>
						<font size=-1>Bitte geben Sie hier ein, wann das Anmelderverfahren die Kontingentierung aufheben soll. </font><br /><br />
						<?
					}
					?>
					<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
						printf ("<font size=-1>%s um %s Uhr </font>", date("d.m.Y",$admin_admission_data["admission_endtime"]), date("H:i",$admin_admission_data["admission_endtime"]));
					} else { ?>
					<font size=-1><input type="text" name="adm_tag" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("d",$admin_admission_data["admission_endtime"]); else echo"tt" ?>">.
					<input type="text" name="adm_monat" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("m",$admin_admission_data["admission_endtime"]); else echo"mm" ?>">.
					<input type="text" name="adm_jahr" size=4 maxlength=4 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("Y",$admin_admission_data["admission_endtime"]); else echo"jjjj" ?>">um&nbsp;
					<font size=-1><input type="text" name="adm_stunde" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("H",$admin_admission_data["admission_endtime"]); else echo"23" ?>">:
					<input type="text" name="adm_minute" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("i",$admin_admission_data["admission_endtime"]); else echo"59" ?>">&nbsp;Uhr</font>&nbsp; 
					<? } ?>
					</td>					
			</tr>
		<?
		}
		?>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b>verbindliche Anmeldung: </b></font><br />
					<font size=-1>Bitte aktivieren sie diese Einstellung, wenn die Anmeldung f&uuml;r Veranstaltungem verbindlich erfolgen soll:</font><br />
					<font size=-1 color="red"><b>Achtung:</b></font>&nbsp;<font size=-1>Verwenden Sie diese Option nur bei entsprechenden Bedarf, etwa zusammen mit einer Teilnehmerbeschr&auml;nkung!</font><br /><br />
					<font size=-1><input type="CHECKBOX" name="admission_binding" <? if ($admin_admission_data["admission_binding"]) echo "checked"; ?> />Anmeldung ist <u>verbindlich</u>. (Teilnehmer k&ouml;nnen sich nicht austragen.)</font>
				</td>
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>		
				<font size=-1>Diese Daten&nbsp; </font>
				<input type="IMAGE" name="uebernehmen" src="./pictures/buttons/uebernehmen-button.gif" border=0 value="uebernehmen">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=3>&nbsp; 
			</td>
		</tr>
		<?
page_close();
?>
	</table>
</td>
</tr>
</table>
</body>
</html>