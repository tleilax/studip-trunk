<?php
/*
meine_seminare.php - Anzeige der eigenen Seminare (anhaengig vom Status)
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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
$Id$
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

ob_start(); //Outputbuffering f�r maximal Performance

function print_seminar_content ($semid, $my_obj_values, $type = 'seminar') {
  $link = $type."_main.php";

  // Postings
  if ($my_obj_values["neuepostings"])
		echo "<a href=\"$link?auswahl=$semid&redirect_to=forum.php&view=neue&sort=age\">&nbsp; <img src='".$GLOBALS['ASSETS_URL']."images/icon-posting2.gif' border=0 ".tooltip(sprintf(_("%s Postings, %s neue"), $my_obj_values["postings"], $my_obj_values["neuepostings"]))."></a>";
  elseif ($my_obj_values["postings"])
		echo "<a href=\"$link?auswahl=$semid&redirect_to=forum.php&view=reset&sort=age\">&nbsp; <img src='".$GLOBALS['ASSETS_URL']."images/icon-posting.gif' border=0 ".tooltip(sprintf(_("%s Postings"), $my_obj_values["postings"]))."></a>";
  else
		echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" border=\"0\">";

  //Dokumente
  if ($my_obj_values["neuedokumente"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=folder.php&cmd=all\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-disc2.gif' border=0 ".tooltip(sprintf(_("%s Dokumente, %s neue"), $my_obj_values["dokumente"], $my_obj_values["neuedokumente"]))."></a>";
  elseif ($my_obj_values["dokumente"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=folder.php&cmd=tree\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-disc.gif' border=0 ".tooltip(sprintf(_("%s Dokumente"), $my_obj_values["dokumente"]))."></a>";
  else
		echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" border=\"0\">";

  //News
  if ($my_obj_values["neuenews"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-news2.gif' border=0 ".tooltip(sprintf(_("%s News, %s neue"), $my_obj_values["news"], $my_obj_values["neuenews"]))."></a>";
  elseif ($my_obj_values["news"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-news.gif' border=0 ".tooltip(sprintf(_("%s News"), $my_obj_values["news"]))."></a>";
  else
		echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" border=\"0\">";

  //SCM
  if ($my_obj_values["scmcontent"]) {
		echo "<a href=\"$link?auswahl=$semid&redirect_to=scm.php\">";
		if($my_obj_values["scmcontent"] == 1){
			if ($my_obj_values["neuscmcontent"]){
				$ttip = $my_obj_values["scmtabname"]._(" (ge�ndert)");
			} else {
				$ttip = $my_obj_values["scmtabname"];
			}
		} else {
			if ($my_obj_values["neuscmcontent"]){
				$ttip = sprintf(_("%s Eintr�ge, %s neue"), $my_obj_values["scmcontent"] ,$my_obj_values["neuscmcontent"]);
			} else {
				$ttip = sprintf(_("%s Eintr�ge"), $my_obj_values["scmcontent"]);
			}
		}
		if ($my_obj_values["neuscmcontent"])
			echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-cont2.gif\" border=0 ".tooltip($ttip)."></a>";
		else
			echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-cont.gif\" border=0 ".tooltip($ttip)."></a>";
  }
  else
		echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" border=\"0\">";

	// Literaturlisten
  if ($my_obj_values["neuelitlist"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=literatur.php\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-lit2.gif' border=0 ".tooltip(sprintf(_("%s Literaturlisten, %s neue"), $my_obj_values["litlist"], $my_obj_values["neuelitlist"]))."></a>";
  elseif ($my_obj_values["litlist"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=literatur.php\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-lit.gif' border=0 ".tooltip(sprintf(_("%s Literaturlisten"), $my_obj_values["litlist"]))."></a>";
  else
		echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" border=\"0\">";

  // Termine
  if ($my_obj_values["neuetermine"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=dates.php#a\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-uhr2.gif' border=0 ".tooltip(sprintf(_("%s Termine, %s neue"), $my_obj_values["termine"], $my_obj_values["neuetermine"]))."></a>";
  elseif ($my_obj_values["termine"])
		echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=dates.php\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-uhr.gif' border=0 ".tooltip(sprintf(_("%s Termine"), $my_obj_values["termine"]))."></a>";
  else
		echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" border=\"0\">";


  // Wikiseiten
  if ($GLOBALS['WIKI_ENABLE']) {
	  if ($my_obj_values["neuewikiseiten"])
			echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=wiki.php&view=listnew\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-wiki2.gif' border=0 ".tooltip(sprintf(_("%s WikiSeiten, %s �nderungen"), $my_obj_values["wikiseiten"], $my_obj_values["neuewikiseiten"]))."></a>";
	  elseif ($my_obj_values["wikiseiten"])
			echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=wiki.php\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-wiki.gif' border=0 ".tooltip(sprintf(_("%s WikiSeiten"), $my_obj_values["wikiseiten"]))."></a>";
	  else
			echo "&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" width=\"20\" height=\"17\" border=\"0\">";
  }

   //elearning
  if ($GLOBALS['ELEARNING_INTERFACE_ENABLE']) {
  	if ($my_obj_values["neuecontentmodule"])
			echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=elearning_interface.php&view=show\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-lern2.gif' border=0 ".tooltip(sprintf(_("%s Content-Modul(e), %s neue"), $my_obj_values["contentmodule"], $my_obj_values["neuecontentmodule"]))."></a>";
	  elseif ($my_obj_values["contentmodule"])
			echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=elearning_interface.php&view=show\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-lern.gif' border=0 ".tooltip(sprintf(_("%s Content-Modul(e)"), $my_obj_values["contentmodule"]))."></a>";
	  else
			echo "&nbsp; <img src='".$GLOBALS['ASSETS_URL']."images/icon-leer.gif' width=\"18\" height=\"17\" border=0>";
  }

 //votes
  if ($GLOBALS['VOTE_ENABLE']) {
  	if ($my_obj_values["neuevotes"])
			echo "&nbsp; <a href=\"$link?auswahl=$semid#vote\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-vote2.gif' border=0 ".tooltip(sprintf(_("%s Umfrage(n), %s neue"), $my_obj_values["votes"], $my_obj_values["neuevotes"]))."></a>";
	  elseif ($my_obj_values["votes"])
			echo "&nbsp; <a href=\"$link?auswahl=$semid#vote\"><img src='".$GLOBALS['ASSETS_URL']."images/icon-vote.gif' border=0 ".tooltip(sprintf(_("%s Umfrage(n)"), $my_obj_values["votes"]))."></a>";
	  else
			echo '&nbsp; <img src="'.$GLOBALS['ASSETS_URL'].'images/icon-leer.gif" width="13" height="17" border=0>';
  }

  // plugins
  if ($GLOBALS["PLUGINS_ENABLE"]){

  	  if (is_array($my_obj_values["activatedplugins"])){
		  foreach ($my_obj_values["activatedplugins"] as $plugin){

		  	if ($plugin->hasChanged($my_obj_values["visitdate"])){
		  		// something new
		  		echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=plugins.php&cmd=show&id=" 
				. $plugin->getPluginId() . "\"><img src='" . $plugin->getChangeindicatoriconname() . "' border=0 "
				. tooltip($plugin->getOverviewMessage(true)) ."></a>";
		  	}
		  	else {
		  		// nothing changed, show empty icon
				echo "&nbsp; <a href=\"$link?auswahl=$semid&redirect_to=plugins.php&cmd=show&id=" 
				. $plugin->getPluginId() . "\"><img src='" . $plugin->getPluginiconname() 
				. "' border=0 "
				. tooltip($plugin->getOverviewMessage())."></a>";
		  	}
		  }
  	  }
  	  else {

  	  	 echo '&nbsp; <img src="'. $GLOBALS['ASSETS_URL'] . 'images/icon-leer.gif" width="13" height="17" border=0>';
  	  }
  }
  echo "&nbsp;";

} // Ende function print_seminar_content


include ("seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ("config.inc.php");			// Klarnamen fuer den Veranstaltungsstatus
require_once ("visual.inc.php");			// htmlReady fuer die Veranstaltungsnamen
require_once ("dates.inc.php");			// Semester-Namen fuer Admins
require_once ("admission.inc.php");		// Funktionen der Teilnehmerbegrenzung
require_once ("messaging.inc.php");
require_once ("lib/classes/Modules.class.php");	// modul-config class
require_once ("lib/classes/ModulesNotification.class.php");
require_once ("statusgruppe.inc.php");		// Funktionen f�r Statusgruppen
require_once ("object.inc.php");
require_once ("meine_seminare_func.inc.php");
if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
	$sms = new messaging();
}
if ($GLOBALS['ILIAS_CONNECT_ENABLE']){
	include_once ($RELATIVE_PATH_LEARNINGMODULES."/lernmodul_db_functions.inc.php");
}

$cssSw = new cssClassSwitcher();									// Klasse f�r Zebra-Design
$cssSw->enableHover();
$db = new DB_Seminar();
$Modules = new Modules();

// we are defintely not in an lexture or institute
closeObject();
$links_admin_data='';	 //Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

$HELP_KEYWORD="Basis.MeineVeranstaltungen";

// Start of Output
include ("html_head.inc.php"); // Output of html head
include ("header.php");   // Output of Stud.IP head

echo "\n" . $cssSw->GetHoverJSFunction() . "\n";

if (!$perm->have_perm("root"))
	include ("links_seminare.inc.php");	   //hier wird die Navigation nachgeladen

//Ausgabe bei bindenden Veranstaltungen, loeschen nicht moeglich!
if ($cmd == "no_kill") {
	$db->query("SELECT Name, admission_type FROM seminare WHERE Seminar_id = '$auswahl'");
	$db->next_record();
	$meldung = "info�" . sprintf(_("Die Veranstaltung <b>%s</b> ist als <b>bindend</b> angelegt. Wenn Sie sich austragen wollen, m&uuml;ssen Sie sich an die Dozentin oder den Dozenten der Veranstaltung wenden."), htmlReady($db->f("Name"))) . "<br />";
}

//Sicherheitsabfrage fuer abonnierte Veranstaltungen
if ($cmd == "suppose_to_kill") {
	$db->query("SELECT * FROM seminare WHERE Seminar_id = '$auswahl'");
	$db->next_record();
	if ($db->f("admission_type") || ($db->f("admission_prelim") == 1)) {
		$meldung = "info�" . sprintf(_("Wollen Sie das Abonnement der teilnahmebeschr&auml;nkten Veranstaltung <b>%s</b> wirklich aufheben? Sie verlieren damit die Berechtigung f&uuml;r die Veranstaltung und m&uuml;ssen sich ggf. neu anmelden!"), htmlReady($db->f("Name"))) . "<br />";
		$meldung.= "<a href=\"$PHP_SELF?cmd=kill&auswahl=$auswahl\">" . makeButton("ja2") . "</a>&nbsp; \n";
		$meldung.= "<a href=\"$PHP_SELF\">" . makeButton("nein") . "</a>\n";
	} else if ($db->f("admission_endtime_sem")!="-1" && $db->f("admission_endtime_sem") < time()) {
		$meldung = "info�" . sprintf(_("Wollen Sie das Abonnement der Veranstaltung <b>%s</b> wirklich aufheben? Der Anmeldzeitraum ist abgelaufen und Sie k&ouml;nnen sich nicht wieder anmelden!"), htmlReady($db->f("Name"))) . "<br />";
		$meldung.= "<a href=\"$PHP_SELF?cmd=kill&auswahl=$auswahl\">" . makeButton("ja2") . "</a>&nbsp; \n";
		$meldung.= "<a href=\"$PHP_SELF\">" . makeButton("nein") . "</a>\n";
	} else {
		$meldung = "info�" . sprintf(_("Wollen Sie das Abonnement der Veranstaltung <b>%s</b> wirklich aufheben?"), htmlReady($db->f("Name"))) . "<br />";
		$meldung.= "<a href=\"$PHP_SELF?cmd=kill&auswahl=$auswahl\">" . makeButton("ja2") . "</a>&nbsp; \n";
		$meldung.= "<a href=\"$PHP_SELF\">" . makeButton("nein") . "</a>\n";
	}
}

//Sicherheitsabfrage fuer Wartelisteneintraege
if ($cmd=="suppose_to_kill_admission") {
	$db->query("SELECT Name FROM seminare WHERE Seminar_id = '$auswahl'");
	$db->next_record();
	$meldung = "info�" . sprintf(_("Wollen Sie den Eintrag auf der Warteliste der Veranstaltung <b>%s</b> wirklich aufheben? Sie verlieren damit die bereits erreichte Position und m&uuml;ssen sich ggf. neu anmelden!"), htmlReady($db->f("Name"))) . "<br />";
	$meldung.="<a href=\"$PHP_SELF?cmd=kill_admission&auswahl=$auswahl\">" . makeButton("ja2") . "</a>&nbsp; \n";
	$meldung.="<a href=\"$PHP_SELF\">" . makeButton("nein") . "</a>\n";
}

//bei Bedarf aus seminar_user austragen
if ($cmd=="kill") {
	$db->query("SELECT Name, admission_binding, a.status FROM seminar_user a LEFT JOIN seminare USING(Seminar_id) WHERE a.Seminar_id = '$auswahl' AND a.user_id='$user->id' AND a.status IN('user','autor')");
	$db->next_record();
	if ($db->f("admission_binding")) {
		$meldung = "info�" . sprintf(_("Die Veranstaltung <b>%s</b> ist als <b>bindend</b> angelegt. Wenn Sie sich austragen wollen, m&uuml;ssen Sie sich an die Dozentin oder den Dozenten der Veranstaltung wenden."), htmlReady($db->f("Name"))) . "<br />";
	} elseif ($db->f("status")) {
		$db->query("DELETE FROM seminar_user WHERE user_id='$user->id' AND Seminar_id='$auswahl'");
		if ($db->affected_rows() == 0)
			$meldung="error�" . _("Datenbankfehler!");
		else {
		  	// L�schen aus Statusgruppen
		  	RemovePersonStatusgruppeComplete ($user->username, $auswahl);

		  	//Pruefen, ob es Nachruecker gibt
		  	update_admission($auswahl);

	  		$db->query("SELECT Name FROM seminare WHERE Seminar_id = '$auswahl'");
		  	$db->next_record();
		  	$meldung = "msg�" . sprintf(_("Das Abonnement der Veranstaltung <b>%s</b> wurde aufgehoben. Sie sind nun nicht mehr als TeilnehmerIn dieser Veranstaltung im System registriert."), htmlReady($db->f("Name")));
		}
	}
}

//bei Bedarf aus admission_seminar_user austragen
if ($cmd=="kill_admission") {
	$db->query("DELETE FROM admission_seminar_user WHERE user_id='$user->id' AND seminar_id='$auswahl'");
	if ($db->affected_rows() == 0)  $meldung="error�" . _("Datenbankfehler!");
	else {
	  //Warteliste neu sortieren
	  renumber_admission($auswahl);

	  $db->query("SELECT Name FROM seminare WHERE Seminar_id = '$auswahl'");
	  $db->next_record();
	  $meldung="msg�" . sprintf(_("Der Eintrag in der Anmelde- bzw. Warteliste der Veranstaltung <b>%s</b> wurde aufgehoben. Wenn Sie an der Veranstaltung teilnehmen wollen, m&uuml;ssen Sie sich erneut bewerben."), htmlReady($db->f("Name")));
	}
}

//bei Bedarf aus seminar_user austragen
if ($cmd=="inst_kill") {
	$db->query("DELETE FROM user_inst WHERE user_id='$user->id' AND Institut_id='$auswahl'");
	if ($db->affected_rows() == 0)
		$meldung="error�" . _("Datenbankfehler!");
	else {

	  $db->query("SELECT Name FROM Institute WHERE Institut_id = '$auswahl'");
	  $db->next_record();
	  $meldung="msg�" . sprintf(_("Die Zuordnung zur Einrichtung %s wurde aufgehoben."), "<b>".htmlReady($db->f("Name"))."</b>");
	}
}


// Update der Gruppen
if ($gruppesent == '1'){
	$_my_sem_group_field = $_REQUEST['select_group_field'];
	if (is_array($_REQUEST['gruppe'])){
		foreach($_REQUEST['gruppe'] as $key => $value){
			$db->query ("UPDATE seminar_user SET gruppe = '$value' WHERE Seminar_id = '$key' AND user_id = '$user->id'");
		}
	}
}

// Update der Benachrichtigungsfunktion
if ($cmd == 'set_sem_notification') {
	if (is_array($_REQUEST['m_checked'])) {
		$m_notification =& new ModulesNotification();
		$m_notification->setModuleNotification($_REQUEST['m_checked'], 'sem');
	}
}

//Anzeigemodul fuer eigene Seminare (nur wenn man angemeldet und nicht root oder admin ist!)
if ($auth->is_authenticated() && $user->id != "nobody" && !$perm->have_perm("admin")) {

	//Alle fuer das Losen anstehenden Veranstaltungen bearbeiten (wenn keine anstehen wird hier nahezu keine Performance verbraten!)
	check_admission();
	if (!$user->is_registered('_my_sem_open')){
		$user->register('_my_sem_open');
	}
	if (!$user->is_registered('_my_sem_group_field')){
		$user->register('_my_sem_group_field');
		$_my_sem_group_field = "not_grouped";
		$_my_sem_open['not_grouped'] = true;
	}
	$group_field = $_my_sem_group_field;

	if (isset($_REQUEST['open_my_sem'])) $_my_sem_open[$_REQUEST['open_my_sem']] = true;

	if (isset($_REQUEST['close_my_sem'])) unset($_my_sem_open[$_REQUEST['close_my_sem']]);

	if (!isset($sortby))
		$sortby="gruppe, Name";
	if ($sortby == "count")
		$sortby = "count DESC";

	$groups = array();

	$all_semester = SemesterData::GetSemesterArray();
	
	$add_fields = '';
	$add_query = '';
	
	if($group_field == 'sem_tree_id'){
		$add_fields = ',sem_tree_id';
		$add_query = "LEFT JOIN seminar_sem_tree sst ON (sst.seminar_id=seminar_user.seminar_id)";
	}
	
	if($group_field == 'dozent_id'){
		$add_fields = ', su1.user_id as dozent_id';
		$add_query = "LEFT JOIN seminar_user as su1 ON (su1.seminar_id=seminare.Seminar_id AND su1.status='dozent')";
	}
	
	
	$db->query ("SELECT seminare.Name, seminare.Seminar_id, seminare.status as sem_status, seminar_user.status, seminar_user.gruppe,
				seminare.chdate, seminare.visible, admission_binding,modules,IFNULL(visitdate,0) as visitdate,
				{$_views['sem_number_sql']} as sem_number, {$_views['sem_number_end_sql']} as sem_number_end $add_fields
				FROM seminar_user LEFT JOIN seminare  USING (Seminar_id)
				LEFT JOIN object_user_visits ouv ON (ouv.object_id=seminar_user.Seminar_id AND ouv.user_id='$user->id' AND ouv.type='sem')
				$add_query
				WHERE seminar_user.user_id = '$user->id'");
	$num_my_sem = $db->num_rows();

	if (!$num_my_sem)
		$meldung = "info�" . sprintf(_("Sie haben zur Zeit keine Veranstaltungen abonniert, an denen Sie teilnehmen k&ouml;nnen. Bitte nutzen Sie %s<b>Veranstaltung suchen / hinzuf&uuml;gen</b>%s um neue Veranstaltungen aufzunehmen."), "<a href=\"sem_portal.php\">", "</a>") . "�" . $meldung;


	while ($db->next_record()) {
			$my_obj[$db->f("Seminar_id")]=array("name" => $db->f("Name"),"status" => $db->f("status"),"visible" => $db->f("visible"), "gruppe" => $db->f("gruppe"),
				"chdate" => $db->f("chdate"), "binding" => $db->f("admission_binding"), "modules" =>$Modules->getLocalModules($db->f("Seminar_id"),"sem",$db->f("modules"),$db->f("sem_status")),
				"obj_type" => "sem", "sem_status" => $db->f("sem_status"), "visitdate" => $db->f("visitdate"), "sem_number" => $db->f("sem_number"),"sem_number_end" => $db->f("sem_number_end") );
			if (($GLOBALS['CHAT_ENABLE']) && ($my_obj[$db->f("Seminar_id")]["modules"]["chat"])) {
				$chatter = $chatServer->isActiveChat($db->f("Seminar_id"));
				$chat_info[$db->f("Seminar_id")] = array("chatter" => $chatter, "chatuniqid" => $chatServer->chatDetail[$db->f("Seminar_id")]["id"],
												"is_active" => $chatServer->isActiveUser($user->id,$db->f("Seminar_id")));
				if ($chatter){
					$active_chats[$chatServer->chatDetail[$db->f("Seminar_id")]["id"]] = $db->f("Seminar_id");
				}
			}
			if ($group_field){
				fill_groups($groups, $db->f($group_field), array('seminar_id' => $db->f('Seminar_id'), 'name' => $db->f("Name"), 'gruppe' => $db->f('gruppe')));
			}
		}

		if (is_array($my_obj)){
			$num_my_sem = count($my_obj);
			if ($group_field == 'sem_number') {
				correct_group_sem_number($groups, $my_obj);
			} else {
				add_sem_name($my_obj);
			}
		}

	$sortby = "Name";
	if ($sortby == "count")
		$sortby = "count DESC";
	$db->query ("SELECT b.Name, b.Institut_id,b.type, user_inst.inst_perms,if(b.Institut_id=b.fakultaets_id,1,0) AS is_fak,
				modules,IFNULL(visitdate,0) as visitdate FROM user_inst LEFT JOIN Institute b USING (Institut_id)
				LEFT JOIN object_user_visits ouv ON (ouv.object_id=user_inst.Institut_id AND ouv.user_id='$user->id' AND ouv.type='inst')
				WHERE user_inst.user_id = '$user->id' GROUP BY Institut_id ORDER BY $sortby");
	$num_my_inst = $db->num_rows();
	while ($db->next_record()) {
		$my_obj[$db->f("Institut_id")]= array("name" => $db->f("Name"),"status" => $db->f("inst_perms"),
											"type" =>($db->f("type")) ? $db->f("type") : 1, "modules" => $Modules->getLocalModules($db->f("Institut_id"),"inst",$db->f("modules"),($db->f("type") ? $db->f("type") : 1)),
											"obj_type" => "inst","visitdate" => $db->f("visitdate"));
		if (($GLOBALS['CHAT_ENABLE']) && ($my_obj[$db->f("Institut_id")]["modules"]["chat"])) {
			$chatter = $chatServer->isActiveChat($db->f("Institut_id"));
			$chat_info[$db->f("Institut_id")] = array("chatter" => $chatter, "chatuniqid" => $chatServer->chatDetail[$db->f("Institut_id")]["id"],
											"is_active" => $chatServer->isActiveUser($user->id,$db->f("Institut_id")));
			if ($chatter){
				$active_chats[$chatServer->chatDetail[$db->f("Institut_id")]["id"]] = $db->f("Institut_id");
			}
		}
	}
	if (($num_my_sem + $num_my_inst) > 0){
		get_my_obj_values($my_obj, $GLOBALS['user']->id);
	}
	if ($GLOBALS['CHAT_ENABLE']){
		if (is_array($active_chats)){
			$chat_invs = $sms->check_list_of_chatinv(array_keys($active_chats));
		}
	}

	?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="topic" colspan="2">
				<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/meinesem.gif" border="0" align="texttop">&nbsp;<b><? echo(_("Meine Veranstaltungen")) ?></b>
			</td>
		</tr>
	<?

	if ($num_my_sem) {
	?>
		<tr valign="top">
			<td class="blank" colspan="2">&nbsp;
			</td>
		</tr>
		<tr valign="top">
			<td valign="top" class="blank" align="center">
				<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center" valign="top" class="blank">
						<? if ($meldung) {
							parse_msg($meldung, "�", "blank",3);
							}?>
							<tr align="center" valign="top">
									<th width="2%" colspan=2 nowrap align="center">&nbsp;<a href="gruppe.php"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/gruppe.gif" <? echo tooltip(_("Gruppe �ndern")) ?> border="0">&nbsp;</a></th>
									<th width="85%" align="left"><? echo(_("Name")) ?></th>
									<th width="10%"><b><? echo(_("Inhalt")) ?></b></th>
									<?
									if ($view=="ext") { ?>
										<th width="10%"><b>&nbsp;<? echo(_("besucht")) ?>&nbsp;</b></th>

										<th width="10%">&nbsp;<? echo(_("Status")) ?>&nbsp;</a></th>
										<th width="10%"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/nutzer.gif" <? echo tooltip(_("TeilnehmerInnen der Veranstaltung")) ?>></th>
									<?	}?>
									<th width="3%"><b>&nbsp; </b></th>
							</tr>
		<?
		ob_end_flush(); //Buffer leeren, damit der Header zu sehen ist

		ob_start();

		sort_groups($group_field, $groups);
		$group_names = get_group_names($group_field, $groups);

		foreach ($groups as $group_id => $group_members){
			if ($group_field != 'not_grouped'){
				$last_modified = check_group_new($group_members, $my_obj);
			  	echo '<tr><td class="blank" colspan="' . ($view == 'ext' ? 7 : 5) . '"><img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="1px" height="5px"></td></tr>';
				echo '<tr><td class="blue_gradient" valign="top" height="20" colspan="2"><img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" style="vertical-align: middle;" width="1px" height="20px">';
				if (isset($_my_sem_open[$group_id])){
					echo '<a class="tree" style="font-weight:bold"  name="' . $group_id . '" href="' . $PHP_SELF . '?view=' . $view . '&close_my_sem=' . $group_id . '#' .$group_id . '" ' . tooltip(_("Gruppierung schlie�en"), true) . '>';
					if ($last_modified){
						echo '<img src="'.$GLOBALS['ASSETS_URL'].'images/forumrotrunt.gif" border="0">';
					} else {
						echo '<img src="'.$GLOBALS['ASSETS_URL'].'images/forumgraurunt.gif" border="0">';
					}
				} else {
					echo '<a class="tree"  name="' . $group_id . '" href="' . $PHP_SELF . '?view=' . $view . '&open_my_sem=' . $group_id . '#' .$group_id . '" ' . tooltip(_("Gruppierung �ffnen"), true) . '>';
					if ($last_modified){
						echo '<img src="'.$GLOBALS['ASSETS_URL'].'images/forumrot.gif"  hspace="3" border="0">';
					} else {
						echo '<img src="'.$GLOBALS['ASSETS_URL'].'images/forumgrau.gif"  hspace="3" border="0">';
					}
				}

				if (is_array($group_names[$group_id])){
					if ($group_names[$group_id][1]) {
						$group_name = $group_names[$group_id][1] . " > " . $group_names[$group_id][0];
					} else {
						$group_name =  $group_names[$group_id][0];
					}
				} else {
					$group_name = $group_names[$group_id];
				}

				echo '</td><td class="blue_gradient" valign="middle" colspan="' . ($view == 'ext' ? 3 : 1) . '">';
				echo '<a class="tree" '.(($_my_sem_open[$group_id]) ? 'style="font-weight:bold"' : '' ).' name="' . $group_id . '" href="' . $PHP_SELF . '?view=' . $view . '&'.(($_my_sem_open[$group_id]) ? 'close' : 'open' ).'_my_sem=' . $group_id . '#' .$group_id . '" ' . tooltip(_("Gruppierung �ffnen"), true) . '>';
				echo htmlReady(($group_field == "sem_tree_id") ? $group_names[$group_id][0] : $group_names[$group_id]);
				echo '</a>';
				if ($group_field == "sem_tree_id")
					echo "<br><span style=\"font-size:0.8em\"><sup>(".htmlReady($group_name).")</sup></span>";

				echo '</td><td class="blue_gradient" align= "right" valign="top" colspan="4" nowrap>';

				if ($last_modified){
					echo '&nbsp;<span style="font-size:0.8em"><sup>letzte &Auml;nderung:&nbsp;</sup></span><span style="color:red;font-size:0.8em"><sup>' . date("d.m.Y, H:m",$last_modified) . '</sup></span>';
				}
				echo '</a></td></tr>';
			} else {
				$_my_sem_open['not_grouped'] = true;
			}

		if (isset($_my_sem_open[$group_id])){
			$cssSw->resetClass();
			foreach ($group_members as $member){
				$semid = $member['seminar_id'];
				$values = $my_obj[$semid];
				  if ($values['obj_type'] == "sem"){
				$cssSw->switchClass();
				$lastVisit = $values['visitdate'];
				echo "<tr ".$cssSw->getHover()."><td class=gruppe";
				echo $values["gruppe"];
				echo "><a href='gruppe.php'><img src='".$GLOBALS['ASSETS_URL']."images/blank.gif' ".tooltip(_("Gruppe �ndern"))." border=0 width=7 height=12></a></td>";
				echo "<td class=\"".$cssSw->getClass()."\">&nbsp; </td>";
				// Name-field
				echo "<td class=\"".$cssSw->getClass()."\" ><a href=\"seminar_main.php?auswahl=$semid\">";
				if ($lastVisit <= $values["chdate"])
					print ("<font color=\"red\">");    // red color for new metadates
				echo "<font size=-1>".htmlReady($values["name"]);
				echo "</font>";
				if ($lastVisit <= $values["chdate"])
					print ("</font>");
				print ("</a>");
				if ($values["visible"]==0) {
					echo "<font size=-1>&nbsp;"._("(versteckt)")."<img src=\"".$GLOBALS['ASSETS_URL']."images/info.gif\" ".tooltip(_("Versteckte Veranstaltungen k�nnen �ber die Suchfunktionen nicht gefunden werden. Um die Veranstaltung sichtbar zu machen, wenden Sie sich an eineN der zust�ndigen AdministratorInnen."),TRUE,TRUE)." border=0></font>";
				}
				print "</td>";
				// Content-field
				echo "<td class=\"".$cssSw->getClass()."\" align=\"left\" nowrap>";
				print_seminar_content($semid, $values);
				if (($GLOBALS['CHAT_ENABLE']) && ($values["modules"]["chat"])){
					echo "<a href=\"".((!$auth->auth["jscript"]) ? "chat_online.php" : "#")."\" onClick=\"return open_chat(" . (($chat_info[$semid]['is_active']) ? "false" : "'$semid'") . ");\">&nbsp;";
					echo chat_get_chat_icon($chat_info[$semid]['chatter'], $chat_invs[$chat_info[$semid]['chatuniqid']], $chat_info[$semid]['is_active'],true);
					echo "</a>&nbsp;";
				} else
					echo "&nbsp; <img src='".$GLOBALS['ASSETS_URL']."images/icon-leer.gif' width=\"15\" height=\"17\" border=0>";

				if (($GLOBALS['ILIAS_CONNECT_ENABLE']) && ($values["modules"]["ilias_connect"])) {
					$mod_count = get_seminar_modules($semid);
					if ($mod_count) {
						echo "<a href=\"seminar_main.php?auswahl=$semid&redirect_to=seminar_lernmodule.php&view=show&seminar_id=$semid\">&nbsp;";
						echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-lern.gif\" ";
						if (sizeof($mod_count) == 1)
							echo tooltip(sprintf(_("Die Veranstaltung ist mit %s ILIAS-Lernmodul verbunden."), sizeof($mod_count)))."border=\"0\">";
						else
							echo tooltip(sprintf(_("Die Veranstaltung ist mit %s ILIAS-Lernmodulen verbunden."), sizeof($mod_count)))."border=\"0\">";
						echo "</a>&nbsp;";
					}
					else
						echo "&nbsp;<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" width=\"18\" height=\"20\" border=\"0\">";
				}
				echo "</td>";


				// Extendet views:

				// last visited-field
				if ($view=="ext") {
					if ($lastVisit == 0) {
						echo "<td class=\"".$cssSw->getClass()."\"  align=\"center\" nowrap><font size=-1>" . _("n.b.") . "</font></td>";
					} else {
						echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>", date("d.m.", $lastVisit),"</font></td>";
					}
					// Status-field
					echo "<td class=\"".$cssSw->getClass()."\"  align=\"center\" nowrap><font size=-1>". $values["status"]."&nbsp;</font></td>";
					// Teilnehmer
					$db2=new DB_Seminar;
					$db2->query ("SELECT count(*) as teilnehmer FROM seminar_user WHERE Seminar_id ='$semid'");
					 while($db2->next_record())
						 echo "<td class=\"".$cssSw->getClass()."\"  nowrap align=\"right\"><font size=-1>". $db2->f("teilnehmer")."&nbsp;</font></td>";
				}


				// delete Entry from List:

				if (($values["status"]=="dozent") || ($values["status"]=="tutor"))
					echo "<td class=\"".$cssSw->getClass()."\"  align=center><img width=\"19\" height=\"17\" src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" />&nbsp;</td>";
				elseif ($values["binding"]) //anderer Link und andere Tonne wenn Veranstaltungszuordnung bindend ist.
					printf("<td class=\"".$cssSw->getClass()."\"  align=center nowrap><a href=\"$PHP_SELF?auswahl=%s&cmd=no_kill\"><img src=\"".$GLOBALS['ASSETS_URL']."images/logout_seminare_no.gif\" ".tooltip(_("Das Abonnement ist bindend. Bitte wenden Sie sich an die Dozentin oder den Dozenten."))." border=\"0\"></a>&nbsp; </td>", $semid);
				else
					printf("<td class=\"".$cssSw->getClass()."\"  align=center nowrap><a href=\"$PHP_SELF?auswahl=%s&cmd=suppose_to_kill\"><img src=\"".$GLOBALS['ASSETS_URL']."images/logout_seminare.gif\" ".tooltip(_("aus der Veranstaltung abmelden"))." border=\"0\"></a>&nbsp;</td>", $semid);
				echo "</tr>\n";
			}
		}
	}
}
	echo "</table><br><br>";


	} else {  // es sind keine Veranstaltungen abboniert

	 ?>
	 <tr>
	 	<td class="blank" colspan="2">&nbsp;
	 	</td>
	 </tr>
		 <td valign="top" class="blank">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
		<?
		if ($meldung)	{
			parse_msg($meldung);
		}?>
			</table>

<?
	}

// Anzeige der Wartelisten
  $db->query("SELECT admission_seminar_user.*, seminare.Name, seminare.admission_endtime, seminare.admission_turnout, quota FROM admission_seminar_user LEFT JOIN seminare USING(seminar_id) LEFT JOIN admission_seminar_studiengang ON (admission_seminar_user.studiengang_id = admission_seminar_studiengang.studiengang_id AND seminare.seminar_id = admission_seminar_studiengang.seminar_id) WHERE user_id = '$user->id' ORDER BY admission_type, name");
  if ($db->num_rows()) {

		// echo "<b><div align=\"left\">&nbsp;" . _("Anmelde- und Wartelisteneintr&auml;ge:") . "</div>&nbsp;";

		echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"98%\" align=\"center\" class=\"blank\">";
		echo "<tr>";
		echo "<th width=\"67%\" align=\"left\" colspan=3>&nbsp;" . _("Anmelde- und Wartelisteneintr&auml;ge") . "</th>";
		echo "<th width=\"10%\"><b>" . _("Datum") . "</b></th>";
		echo "<th width=\"10%\" nowrap><b>" . _("Position/Chance") . "</b></th>";
		echo "<th width=\"10%\"><b>" . _("Art") . "</b></th>";
		echo "<th width=\"3%\">&nbsp; </tr></th>";

		$cssSw->resetClass();

		while ($db->next_record()) {
			if ($db->f("status") == "claiming") { // wir sind in einer Anmeldeliste und brauchen Prozentangaben
				$db2=new DB_Seminar;
				$admission_studiengang_id = $db->f("studiengang_id");
				$admission_seminar_id = $db->f("seminar_id");
				if ($admission_studiengang_id == 'all'){
					$plaetze = get_all_quota($db->f("seminar_id"));
				} else {
					$plaetze = round ($db->f("admission_turnout") * ($db->f("quota") / 100));  // Anzahl der Plaetze in dem Studiengang in den ich will
				}
				$db2->query("SELECT count(*) AS wartende FROM admission_seminar_user WHERE seminar_id = '$admission_seminar_id' AND studiengang_id = '$admission_studiengang_id'");
				if ($db2->next_record()) {
					$wartende = ($db2->f("wartende"));   // Anzahl der Personen die auch in diesem Studiengang auf einen Platz lauern
				}
				if ($plaetze >= $wartende)
					$admission_chance = 100;   // ich komm auf jeden Fall rein
				else
					$admission_chance = round (($plaetze / $wartende) * 100); // mehr Bewerber als Plaetze
				$chance_color = dechex(255-(200-($admission_chance*2)));  // Gruen der Farbe nimmt mit Wahrscheinlichkeit ab
			} else {  // wir sind in einer Warteliste
				if ($db->f("position") >= 30)
					$chance_color = 44; // das wird wohl nix mehr mit nachr�cken
				else
					$chance_color = dechex(255-($db->f("position")*6)); // da gibts vielleicht noch Hoffnung, also gr�n
			}

			//$cssSw->disableHover();
			$cssSw->switchClass();
			printf ("<tr".$cssSw->getHover()."><td width=\"1%%\" bgcolor=\"#44%s44\"><img src='".$GLOBALS['ASSETS_URL']."images/blank.gif' " . tooltip(_("Position oder Wahrscheinlichkeit")) . " border=0 width=7 height=12></td>",$chance_color);
			printf ("<td width=\"1%%\" class=\"%s\">&nbsp;</td>",$cssSw->getClass());
			printf ("<td width=\"55%%\" class=\"%s\">",$cssSw->getClass());
			print "<a href=details.php?sem_id=".$db->f("seminar_id")."&send_from_search_page=meine_seminare.php&send_from_search=TRUE><font size=-1>".htmlReady($db->f("Name"))."</font></a></td>";
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s</font></td>", $cssSw->getClass(), ($db->f("status") == "claiming") ? date("d.m.", $db->f("admission_endtime")) : "-");
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s %s</font></td>",$cssSw->getClass(), ($db->f("status") == "claiming") ? $admission_chance : $db->f("position"), ($db->f("status") == "claiming") ? "%" : "");
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s</font></td>", $cssSw->getClass(),  ($db->f("status") == "claiming") ? _("Los") : (($db->f("status") == "accepted") ? _("Vorl.") :_("Wartel.")));
			printf("<td width=\"3%%\" class=\"%s\" align=\"center\"><a href=\"$PHP_SELF?auswahl=%s&cmd=%skill_admission\"><img src=\"".$GLOBALS['ASSETS_URL']."images/logout_seminare.gif\" ".tooltip(_("aus der Veranstaltung abmelden"))." border=\"0\"></a>&nbsp;</td></tr>", $cssSw->getClass(), $db->f("seminar_id"), ($db->f("status") == "awaiting") ? "suppose_to_" : "");
		}
		print "</table>";
		?>

		<br><br>

		<?
	}	 // Ende Wartelisten

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//This view is only for users up to admin
if ( !$perm->have_perm("root")) {

	if (!$num_my_inst)
		if ($perm->have_perm("dozent"))
			$meldung = "info�" . sprintf(_("Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zust&auml;ndigen %sAdministratoren%s."), "<a href=\"impressum.php?view=ansprechpartner\">", "</a>") . "�";
		else
			$meldung = "info�" . sprintf(_("Sie haben sich noch keinen Einrichtungen zugeordnet. Um sich Einrichtungen zuzuordnen, nutzen Sie bitte die entsprechende %sOption%s unter \"universit&auml;re Daten\" auf Ihrer pers&ouml;nlichen Einstellungsseite."), "<a href=\"edit_about.php?view=Karriere#einrichtungen\">", "</a>") . "�";

	if ($num_my_inst) {
	 ?>

							<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center" class="blank">
								<tr valign="top" align="center">
									<th width="1%">&nbsp; </th>
									<th width="86%" align="left"><?=_("Meine Einrichtungen")?></th>
									<th width="10%"><b><?=_("Inhalt")?></b></th>
									<?
									if ($view=="ext") {
									?>
										<th width="10%"><b>&nbsp;<?=_("besucht")?>&nbsp;</b></th>
										<th width="10%"><b>&nbsp;<?=_("Status")?>&nbsp;</b></th>
									<? }?>
									<th width="3%"><b>&nbsp;&nbsp;</b></th>
								</tr>
		<?

		foreach ($my_obj as $instid=>$values) {
			if ($values['obj_type'] == "inst"){
				$cssSw->switchClass();
				$lastVisit = $values['visitdate'];
				echo "<tr ".$cssSw->getHover().">";
				echo "<td class=\"".$cssSw->getClass()."\">&nbsp; </td>";
				// Name-field
				echo "<td class=\"".$cssSw->getClass()."\"><a href=\"institut_main.php?auswahl=$instid\">";
				echo "<font size=-1>".htmlReady($INST_TYPE[$values["type"]]["name"] . ": " . $values["name"])."</font>";
				print ("</a></td>");
				// Content-field
				echo "<td class=\"".$cssSw->getClass()."\"  align=\"left\" nowrap>";
				print_seminar_content($instid, $values, "institut");
				if (($GLOBALS['CHAT_ENABLE']) && ($values["modules"]["chat"])) {
					echo "<a href=\"".((!$auth->auth["jscript"]) ? "chat_online.php" : "#")."\" onClick=\"return open_chat(" . (($chat_info[$instid]['is_active']) ? "false" : "'$instid'") . ");\">&nbsp;";
					echo chat_get_chat_icon($chat_info[$instid]['chatter'], $chat_invs[$chat_info[$instid]['chatuniqid']], $chat_info[$instid]['is_active'],true);
					echo "</a>&nbsp;";
				} else
				echo "&nbsp; <img src='".$GLOBALS['ASSETS_URL']."images/icon-leer.gif' width=\"15\" height=\"17\" border=0>";

				if (($GLOBALS['ILIAS_CONNECT_ENABLE']) && ($values["modules"]["ilias_connect"])) {
					$mod_count = get_seminar_modules($instid);
					if ($mod_count) {
						echo "<a href=\"institut_main.php?view=show&auswahl=$instid&redirect_to=seminar_lernmodule.php\">&nbsp;";
						echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-lern.gif\" ";
						if (sizeof($mod_count) == 1)
						echo tooltip(sprintf(_("Die Einrichtung ist mit %s ILIAS-Lernmodul verbunden."), sizeof($mod_count)))."border=\"0\">";
						else
						echo tooltip(sprintf(_("Die Einrichtung ist mit %s ILIAS-Lernmodulen verbunden."), sizeof($mod_count)))."border=\"0\">";
						echo "</a>&nbsp;";
					}
					else
					echo "&nbsp;<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-leer.gif\" width=\"18\" height=\"20\" border=\"0\">";
				}
				echo "</td>";

				// Extendet views:

				// last visited-field
				if ($view=="ext") {
					if ($lastVisit == 0) {
						echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>n.b.</font></td>";
					} else  {
						echo "<td class=\"".$cssSw->getClass()."\"align=\"center\" nowrap><font size=-1>", date("d.m.", $lastVisit),"</font></td>";
					}
					// Status-field
					echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>". $values["status"]."&nbsp;</font></td>";
				}

				// delete Entry from List:
				if (($values["status"]=="dozent") || ($values["status"]=="tutor") || ($values["status"]=="admin") || ($values["status"]=="autor"))
				echo "<td class=\"".$cssSw->getClass()."\" align=center><img width=\"19\" height=\"17\" src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" />&nbsp;</td>";
				else
				printf("<td class=\"".$cssSw->getClass()."\" align=center align=center><a href=\"$PHP_SELF?auswahl=%s&cmd=inst_kill\"><img src=\"".$GLOBALS['ASSETS_URL']."images/logout_seminare.gif\" ".tooltip(_("aus der Einrichtung austragen"))." border=\"0\">&nbsp;</a></td>", $instid);
				echo "</tr>\n";
			}
		}
		echo "</table>\n";
	} else {
	?>
		<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
		<?
		if ($meldung)	{
			parse_msg($meldung);
		}
		?>
		</table>
		<?
	}
}

////////////////////

//Info-field on the right side
	?>

	</td>
	<td class="blank" width="270" align="right" valign="top">
	<?

	// Berechnung der uebrigen Seminare und Einrichtungen

	$db->query("SELECT count(*) as count  FROM Institute");
	$db->next_record();
	$anzahlinst = $db->f("count")-$num_my_inst;

	$db->query("SELECT count(*) as count  FROM seminare");
	$db->next_record();
	$anzahltext = sprintf(_("Es sind noch %s weitere Veranstaltungen sowie %s weitere Einrichtungen vorhanden."), ($db->f("count")-$num_my_sem),$anzahlinst);


	// View for Teachers

	if ($perm->have_perm("dozent")) {
		$infobox = array	(
			array  ("kategorie"  => _("Information:"),
				"eintrag" => array	(
					array (	"icon" => "ausruf_small.gif",
									"text"  => $anzahltext
					)
				)
			),
			array  ("kategorie" => _("Veranstaltungen:"),
				"eintrag" => array	(
					array	 (	"icon" => "suche2.gif",
										"text"  => sprintf(_("Um weitere Veranstaltungen in Ihre pers&ouml;nliche Auswahl aufzunehmen, nutzen Sie bitte die %sSuchfunktion%s"), "<a href=\"sem_portal.php\">", "</a>")
					),
					array	 (	"icon" => "admin.gif",
										"text"  => sprintf(_("Um Veranstaltungen anzulegen, nutzen Sie bitte den %sVeranstaltungs-Assistenten%s"), "<a href=\"admin_seminare_assi.php?new_session=TRUE\">", "</a>")
					)
				)
			),
			array  ("kategorie" => _("Einrichtungen:"),
				"eintrag" => array	(
					array	 (	"icon" => "cont_res1.gif",
										"text"  => sprintf(_("Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die %sEinrichtungssuche%s."), "<a href=\"institut_browse.php\">", "</a>")
					)
				)
			)
		);
	}	else {

	// View for Students

		$infobox = array	(
			array  ("kategorie"  => _("Information:"),
				"eintrag" => array	(
					array (	"icon" => "ausruf_small.gif",
									"text"  => $anzahltext
					)
				)
			),
			array  ("kategorie" => _("Aktionen:"),
				"eintrag" => array	(
					array	 (	"icon" => "suche2.gif",
										"text"  => sprintf(_("Um weitere Veranstaltungen in Ihre pers&ouml;nliche Auswahl aufzunehmen, nutzen Sie bitte die %sSuchfunktion%s"), "<a href=\"sem_portal.php\">", "</a>")
					),
					array	 (	"icon" => "cont_res1.gif",
										"text"  => sprintf(_("Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die %sEinrichtungssuche%s."), "<a href=\"institut_browse.php\">", "</a>")
					),
					array	 (	"icon" => "meinesem.gif",
										"text"  => sprintf(_("Wenn Sie weitere Einrichtungen in ihre pers&ouml;nliche Auswahl aufnehmen m&ouml;chten, k&ouml;nnen sie sich hier %szuordnen%s."), "<a href=\"edit_about.php?view=Karriere#einrichtungen\">", "</a>")
					)
				)
			)
		);
	}

	$infobox[] = array('kategorie' => _("Einstellungen:"),
					'eintrag' => array(array("icon" => "gruppe.gif",
												"text"  => sprintf(
												_("Gruppierung der angezeigten Veranstaltungen %s&auml;ndern%s."),
												"<a href=\"gruppe.php\">", "</a>")
												)));
	if (get_config('MAIL_NOTIFICATION_ENABLE')){
		$infobox[count($infobox)-1]['eintrag'][] = array(	'icon' => 'cont_nachricht_pfeil.gif',
															'text' => sprintf(_("Benachrichtigung �ber neue Inhalte %sanpassen%s."),
																	'<a href="sem_notification.php">', '</a>'));
	}


// print the info_box

	print_infobox ($infobox,"seminare.jpg");

?>

     	</td>
    </tr>
    <tr>
    	<td class="blank" colspan="2">&nbsp;
    	</td>
    </tr>

<?
}


elseif ($auth->auth["perm"]=="admin") {

	$db2=new DB_Seminar();

	if(isset($_REQUEST['select_sem'])){
			$_default_sem = $_REQUEST['select_sem'];
	}
	if ($_default_sem){
		$semester =& SemesterData::GetInstance();
		$one_semester = $semester->getSemesterData($_default_sem);
		$sem_condition = "AND seminare.start_time <=".$one_semester["beginn"]." AND (".$one_semester["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
	}
	$db->query("SELECT a.Institut_id,b.Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak,count(seminar_id) AS num_sem FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
				LEFT JOIN seminare ON(seminare.Institut_id=b.Institut_id $sem_condition )	WHERE a.user_id='$user->id' AND a.inst_perms='admin' GROUP BY a.Institut_id ORDER BY is_fak,Name,num_sem DESC");

	while($db->next_record()){
		$_my_inst[$db->f("Institut_id")] = array("name" => $db->f("Name"), "is_fak" => $db->f("is_fak"), "num_sem" => $db->f("num_sem"));
		if ($db->f("is_fak")){
			$db2->query("SELECT a.Institut_id, a.Name,count(seminar_id) AS num_sem FROM Institute a
					LEFT JOIN seminare ON(seminare.Institut_id=a.Institut_id $sem_condition ) WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND a.Institut_id!='" .$db->f("Institut_id") . "'
					GROUP BY a.Institut_id ORDER BY a.Name,num_sem DESC");
			$num_inst = 0;
			while ($db2->next_record()){
				if(!$_my_inst[$db2->f("Institut_id")]){
					++$num_inst;
				}
				$_my_inst[$db2->f("Institut_id")] = array("name" => $db2->f("Name"), "is_fak" => 0 , "num_sem" => $db2->f("num_sem"));
			}
			$_my_inst[$db->f("Institut_id")]["num_inst"] = $num_inst;
		}
	}

	if (!is_array($_my_inst))
		$meldung="info�" . sprintf(_("Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zust&auml;ndigen %sAdministratoren%s."), "<a href=\"impressum.php?view=ansprechpartner\">", "</a>") . "�".$meldung;
	else {
		$_my_inst_arr = array_keys($_my_inst);
		if(!$user->is_registered("_my_admin_inst_id")){
			$_my_admin_inst_id = $_my_inst_arr[0];
			$user->register("_my_admin_inst_id");
		}
		if($_REQUEST['institut_id']){
			$_my_admin_inst_id = ($_my_inst[$_REQUEST['institut_id']]) ? $_REQUEST['institut_id'] : $_my_inst_arr[0];
		}

		if (!isset($sortby)) $sortby="start_time DESC, Name ASC";
		if ($sortby == "teilnehmer")
		$sortby = "teilnehmer DESC";
		$db->query("SELECT Institute.Name AS Institut, seminare.Seminar_id,seminare.Name,seminare.status,seminare.chdate,
					seminare.start_time,seminare.admission_binding,seminare.visible, seminare.modules,
					COUNT(seminar_user.user_id) AS teilnehmer,IFNULL(visitdate,0) as visitdate,
					sd1.name AS startsem,IF(duration_time=-1, '"._("unbegrenzt")."', sd2.name) AS endsem
					FROM Institute INNER JOIN seminare ON(seminare.Institut_id=Institute.Institut_id $sem_condition ) INNER JOIN seminar_user USING(Seminar_id)
					LEFT JOIN object_user_visits ouv ON (ouv.object_id=seminare.Seminar_id AND ouv.user_id='$user->id' AND ouv.type='sem')
					LEFT JOIN semester_data sd1 ON ( start_time BETWEEN sd1.beginn AND sd1.ende)
					LEFT JOIN semester_data sd2 ON ((start_time + duration_time) BETWEEN sd2.beginn AND sd2.ende)
					WHERE Institute.Institut_id='$_my_admin_inst_id' GROUP BY seminare.Seminar_id ORDER BY $sortby");
		$num_my_sem=$db->num_rows();
		if (!$num_my_sem)
			$meldung = "msg�"
					. sprintf(_("An der Einrichtung: <b>%s</b> sind zur Zeit keine Veranstaltungen angelegt."), htmlReady($_my_inst[$_my_admin_inst_id]['name']))
					. "�"
					. $meldung;
	}
	?>
		<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" ><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/meinesem.gif" border="0" align="texttop">
			&nbsp;<b><?=_("Veranstaltungen an meinen Einrichtungen") .($_my_admin_inst_id ? " - ".htmlReady($_my_inst[$_my_admin_inst_id]['name']) : "")?></b></td>
		</tr>

	<tr>
		<td class="blank" width="100%" >&nbsp;
			<?
			if ($meldung) parse_msg($meldung);
			?>
		</td>
	</tr>
	<?
	if (is_array($_my_inst)) {
	?>
		<tr>
			<form action="<?=$PHP_SELF?>" method="post">
			<td class="blank" width="100%" >
				<div style="font-weight:bold;font-size:10pt;margin-left:10px;">
				<?=_("Bitte w&auml;hlen Sie eine Einrichtung aus:")?>
				</div>
				<div style="margin-left:10px;">
				<select name="institut_id" style="vertical-align:middle;">
					<?
					reset($_my_inst);
					while (list($key,$value) = each($_my_inst)){
						printf ("<option %s value=\"%s\" style=\"%s\">%s (%s)</option>\n",
								($key == $_my_admin_inst_id) ? "selected" : "" , $key,($value["is_fak"] ? "font-weight:bold;" : ""),
								htmlReady($value["name"]), $value["num_sem"]);
						if ($value["is_fak"]){
							$num_inst = $value["num_inst"];
							for ($i = 0; $i < $num_inst; ++$i){
								list($key,$value) = each($_my_inst);
								printf("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s (%s)</option>\n",
									($key == $_my_admin_inst_id) ? "selected" : "", $key,
									htmlReady($value["name"]), $value["num_sem"]);
							}
						}
					}
					?>
					</select>&nbsp;
					<?=SemesterData::GetSemesterSelector(array('name'=>'select_sem', 'style'=>'vertical-align:middle;'), $_default_sem)?>
					<input <?=makeButton("auswaehlen","src")?> <?=tooltip(_("Einrichtung ausw�hlen"))?> type="image" border="0" style="vertical-align:middle;">
					<br>&nbsp;
				</div>
			</td>
			</form>
		</tr>


		 <?
		 if ($num_my_sem) {
		 ?>
		<tr>
			<td class="blank" >
				<table border="0" cellpadding="0" cellspacing="0" width="99%" align="center" class=blank>
					<tr valign"top" align="center">
						<th width="50%" colspan=2><a href="<? echo $PHP_SELF ?>?sortby=Name"><?=_("Name")?></a></th>
						<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=status"><?=_("Status")?></a></th>
						<th width="15%"><b><?=_("DozentIn")?></b></th>
						<th width="10%"><b><?=_("Inhalt")?></b></th>
						<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=teilnehmer"><?=_("TeilnehmerInnen")?></a></th>
						<th width="5%"><b>&nbsp; </b></th>
					</tr>
		<?

		while ($db->next_record()){
			$my_sem[$db->f("Seminar_id")] = array(
					'visitdate' => $db->f('visitdate'),
					'institut' => $db->f("Institut"),
					'teilnehmer' => $db->f("teilnehmer"),
					'name' => $db->f("Name"),
					'status' => $db->f("status"),
					'chdate' => $db->f("chdate"),
					'start_time' => $db->f("start_time"),
					'startsem' => $db->f('startsem'),
					'endsem' => $db->f('endsem'),
					'binding' => $db->f("admission_binding"),
					'visible' => $db->f('visible'),
					'modules' => $Modules->getLocalModules($db->f("Seminar_id"),
								"sem",
								$db->f("modules"),
								$db->f("status"))
					);
		}
		get_my_obj_values($my_sem, $GLOBALS['user']->id);
		$cssSw->enableHover();
		foreach ($my_sem as $semid=>$values){
			$cssSw->switchClass();
			$class = $cssSw->getClass();

			$lastVisit = $values['visitdate'];

			echo "<tr ".$cssSw->getHover()."><td class=\"$class\">&nbsp;&nbsp;</td>";
			echo "<td class=\"$class\"><a href=\"seminar_main.php?auswahl=$semid\">";
			if ($lastVisit <= $values["chdate"])
				print ("<font color=\"red\">");
			echo htmlReady($values["name"]);
			if ($lastVisit <= $values["chdate"])
				echo "</font>";
			echo "</a>";
			if (!$_default_sem || $values['startsem'] != $values['endsem']){
				echo "<font size=-1>&nbsp;";
				echo htmlReady(" (".$values['startsem']
					. ($values['startsem'] != $values['endsem'] ? " - ".$values['endsem'] : "")
					. ")");
				echo "</font>";
			}
			if ($values["visible"] == 0) {
					echo "<font size=-1>&nbsp;"._("(versteckt)")."</font>";
				}
			echo "</td>";

			echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">&nbsp;" . $SEM_TYPE[$values["status"]]["name"] . "&nbsp;</font></td>";
			// Dozenten
			$db2->query ("SELECT Nachname, username FROM  seminar_user LEFT JOIN auth_user_md5  USING (user_id) WHERE Seminar_id='$semid' AND status='dozent' ORDER BY Nachname ASC");
			$temp = "";
			while ($db2->next_record()) {
				$temp .= "<a href=\"about.php?username=" . $db2->f("username") . "\">" . htmlReady($db2->f("Nachname")) . "</a>, ";
			}
			$temp = substr($temp, 0, -2);
			print ("<td class=\"$class\" align=\"center\"><font size=\"-1\">&nbsp;$temp</font></td>");

			// Inhalt
			echo "<td class=\"$class\" align=\"left\" nowrap>";
			print_seminar_content($semid, $values);
			echo "</td>";

			echo "<td class=\"$class\" align=\"center\" nowrap>". $values["teilnehmer"]."&nbsp;</td>";
			printf("<td class=\"$class\" align=center align=center><a href=\"seminar_main.php?auswahl=$semid&redirect_to=adminarea_start.php&new_sem=TRUE\"><img src=\"".$GLOBALS['ASSETS_URL']."images/admin.gif\" ".tooltip(_("Veranstaltungsdaten bearbeiten"))." border=\"0\"></a></td>", $semid);
			 echo "</tr>\n";
			}
		echo "		</table>
				</td>
			</tr>";

		 }
	}

?>
	<tr>
		<td class="blank">&nbsp;
		</td>
	</tr>
<?
}

ELSEIF ($perm->have_perm("root")){


//Anzeigemodul fuer alle Seminare f�r root
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/meinesem.gif" border="0" align="texttop"><b><?=_("&Uuml;bersicht &uuml;ber Veranstaltungen")?></></td>
		</tr>
		<tr>
			<td class="blank" align = left colspan=2><br /><blockquote>
				<?=_("Um eine Veranstaltung zu bearbeiten, w&auml;hlen Sie sie &uuml;ber die Suchfunktion aus.")?>
			</blockquote>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
			</td>
		</tr>
		<tr>
			<td class="blank" align="center" colspan=2>
			<b>Sie sind 'root', sie sollten eigentlich nicht hier sein!<b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
				&nbsp;
			</td>
		</tr>
	</table>
<?php
}

echo '</table>';

include ('html_end.inc.php');
  // Save data back to database.
ob_end_flush(); //Outputbuffering beenden
page_close();
?>