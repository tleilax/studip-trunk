<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
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
*/


require '../lib/bootstrap.php';

require_once 'lib/classes/CourseAvatar.class.php';
require_once 'lib/classes/StudygroupAvatar.class.php';
require_once 'lib/classes/InstituteAvatar.class.php';

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

global $SEM_CLASS,
       $SEM_TYPE;

ob_start(); //Outputbuffering f�r maximal Performance

function print_seminar_content ($semid, $my_obj_values, $type = 'seminar') {

  foreach (words('forum files news scm literature schedule wiki elearning vote') as $key) {
    $navigation[$key] = $my_obj_values[$key];
  }

  foreach (PluginEngine::getPlugins('StandardPlugin', $semid) as $plugin) {
    $navigation[] = $plugin->getIconNavigation($semid, $my_obj_values['visitdate']);
  }

  foreach ($navigation as $key => $nav) {
    if (isset($nav) && $nav->isVisible(true)) {
        // need to use strtr() here to deal with seminar_main craziness
        $url = $type.'_main.php?auswahl='.$semid.'&redirect_to='.strtr($nav->getURL(), '?', '&');
        printf('&nbsp; <a href="%s"><img ', htmlspecialchars($url));
        foreach ($nav->getImage() as $key => $value) {
            printf('%s="%s" ', $key, htmlReady($value));
        }
        echo '></a>';
    } else if (is_string($key)) {
        $width = $key == 'wiki' ? 20 : ($key == 'elearning' ? 18 : 13);
        echo '&nbsp; '.Assets::img('icon-leer.gif', array('width' => $width, 'height' => 17));
    }
  }

  echo "&nbsp;";

} // Ende function print_seminar_content


include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ('config.inc.php');            // Klarnamen fuer den Veranstaltungsstatus
require_once ('lib/visual.inc.php');            // htmlReady fuer die Veranstaltungsnamen
require_once ('lib/dates.inc.php');         // Semester-Namen fuer Admins
require_once ('lib/admission.inc.php');     // Funktionen der Teilnehmerbegrenzung
require_once ('lib/messaging.inc.php');
require_once ('lib/classes/Modules.class.php'); // modul-config class
require_once ('lib/classes/ModulesNotification.class.php');
require_once ('lib/statusgruppe.inc.php');      // Funktionen f�r Statusgruppen
require_once ('lib/object.inc.php');
require_once ('lib/meine_seminare_func.inc.php');
require_once ('lib/classes/LockRules.class.php');

if (get_config('CHAT_ENABLE')){
    include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
    $chatServer = ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
    $chatServer->caching = true;
    $sms = new messaging();
}

$cssSw = new cssClassSwitcher();                                    // Klasse f�r Zebra-Design
$cssSw->enableHover();
$db = new DB_Seminar();
$Modules = new Modules();
$userConfig = UserConfig::get($GLOBALS['user']->id);

// we are defintely not in an lexture or institute
closeObject();
$links_admin_data='';    //Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

$HELP_KEYWORD="Basis.MeineVeranstaltungen";
$CURRENT_PAGE=_("Meine Veranstaltungen und Einrichtungen");
if (!$perm->have_perm("root")) {
    Navigation::activateItem('/browse/my_courses/list');
}

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
if (get_config('CHAT_ENABLE')){
    chat_get_javascript();
}

$cmd = Request::option('cmd');
if(in_array($cmd, words('no_kill suppose_to_kill suppose_to_kill_admission kill kill_admission'))){
    $current_seminar = Seminar::getInstance(Request::option('auswahl'));
    $ticket_check = Seminar_Session::check_ticket(Request::option('studipticket'));
    UrlHelper::addLinkParam('studipticket', Seminar_Session::get_ticket());

    //Ausgabe bei bindenden Veranstaltungen, loeschen nicht moeglich!
    if ($cmd == "no_kill") {
        $meldung = "info�" . sprintf(_("Die Veranstaltung <b>%s</b> ist als <b>bindend</b> angelegt. Wenn Sie sich austragen wollen, m&uuml;ssen Sie sich an die Dozentin oder den Dozenten der Veranstaltung wenden."), htmlReady($current_seminar->getName())) . "<br>";
    }

    //Sicherheitsabfrage fuer abonnierte Veranstaltungen
    if ($cmd == "suppose_to_kill") {
        if(LockRules::Check($current_seminar->getId(), 'participants')){
            $lockRule = new LockRules();
            $lockdata = $lockRule->getSemLockRule($current_seminar->getId());
            $meldung = "error�" . sprintf(_("Sie k�nnen das Abonnement der Veranstaltung <b>%s</b> nicht aufheben."), htmlReady($current_seminar->getName()));
            if($lockdata['description']) $meldung .= '�info�' . fixLinks($lockdata['description']);
        } else {
            if ($current_seminar->admission_type || $current_seminar->admission_prelim == 1) {
                $meldung = sprintf(_('Wollen Sie das Abonnement der teilnahmebeschr�nkten Veranstaltung "%s" wirklich aufheben? Sie verlieren damit die Berechtigung f�r die Veranstaltung und m�ssen sich ggf. neu anmelden!'), $current_seminar->getName());
            } else if ($current_seminar->admission_endtime_sem != -1 && $current_seminar->admission_endtime_sem < time()) {
                $meldung = sprintf(_('Wollen Sie das Abonnement der Veranstaltung "%s" wirklich aufheben? Der Anmeldzeitraum ist abgelaufen und Sie k�nnen sich nicht wieder anmelden!'), $current_seminar->getName());
            } else {
                $meldung = sprintf(_('Wollen Sie das Abonnement der Veranstaltung "%s" wirklich aufheben?'), $current_seminar->getName());
            }
            echo createQuestion($meldung, array('cmd' => 'kill', 'auswahl' => $current_seminar->getId()));
        }
    }

    //Sicherheitsabfrage fuer Wartelisteneintraege
    if ($cmd=="suppose_to_kill_admission") {
        if(admission_seminar_user_get_position($user->id, $current_seminar->getId()) == 'na'){
            $meldung = sprintf(_('Wollen Sie den Eintrag auf der Anmeldeliste der Veranstaltung "%s" wirklich aufheben?'), $current_seminar->getName());
        } else {
            $meldung = sprintf(_('Wollen Sie den Eintrag auf der Warteliste der Veranstaltung "%s" wirklich aufheben? Sie verlieren damit die bereits erreichte Position und m�ssen sich ggf. neu anmelden!'), $current_seminar->getName());
        }
        echo createQuestion($meldung, array('cmd' => 'kill_admission', 'auswahl' => $current_seminar->getId()));
    }

    //bei Bedarf aus seminar_user austragen
    if ($cmd=="kill"
        && !LockRules::Check($current_seminar->getId(), 'participants')
        && $ticket_check) {

        if ($current_seminar->admission_binding) {
            $meldung = "info�" . sprintf(_("Die Veranstaltung <b>%s</b> ist als <b>bindend</b> angelegt. Wenn Sie sich austragen wollen, m&uuml;ssen Sie sich an die Dozentin oder den Dozenten der Veranstaltung wenden."), htmlReady($current_seminar->getName())) . "<br>";
        } elseif (!$perm->have_studip_perm('tutor', $current_seminar->getId())) {

            // LOGGING
            log_event('SEM_USER_DEL', $current_seminar->getId(), $user->id, 'Hat sich selbst ausgetragen');

            $db->query("DELETE FROM seminar_user WHERE user_id='$user->id' AND Seminar_id='".$current_seminar->getId()."'");
            if ($db->affected_rows() == 0)
                $meldung="error�" . _("Datenbankfehler!");
            else {
                // L�schen aus Statusgruppen
                RemovePersonStatusgruppeComplete (get_username(), $current_seminar->getId());

                //Pruefen, ob es Nachruecker gibt
                update_admission($current_seminar->getId());

                $meldung = "msg�" . sprintf(_("Das Abonnement der Veranstaltung <b>%s</b> wurde aufgehoben. Sie sind nun nicht mehr als TeilnehmerIn dieser Veranstaltung im System registriert."), htmlReady($current_seminar->getName()));
            }
        }
    }

    //bei Bedarf aus admission_seminar_user austragen
    if ($cmd=="kill_admission" && $ticket_check) {

        // LOGGING
        log_event('SEM_USER_DEL', $current_seminar->getId(), $user->id, 'Hat sich selbst aus der Wartliste ausgetragen');

        $db->query("DELETE FROM admission_seminar_user WHERE user_id='$user->id' AND seminar_id='".$current_seminar->getId()."'");
        if ($db->affected_rows() == 0)  $meldung="error�" . _("Datenbankfehler!");
        else {
            //Warteliste neu sortieren
            renumber_admission($current_seminar->getId());

            $meldung="msg�" . sprintf(_("Der Eintrag in der Anmelde- bzw. Warteliste der Veranstaltung <b>%s</b> wurde aufgehoben. Wenn Sie an der Veranstaltung teilnehmen wollen, m&uuml;ssen Sie sich erneut bewerben."), htmlReady($current_seminar->getName()));
        }
    }
}
//bei Bedarf aus seminar_user austragen
if ($cmd=="inst_kill" && $GLOBALS['ALLOW_SELFASSIGN_INSTITUTE']) {
    $db->query("DELETE FROM user_inst WHERE user_id='$user->id' AND Institut_id='$auswahl' AND inst_perms='user'");
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
        $m_notification = new ModulesNotification();
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

    $dbv = new DbView();

    $db->query ("SELECT seminare.Name, seminare.Seminar_id, seminare.status as sem_status, seminar_user.status, seminar_user.gruppe,
                seminare.chdate, seminare.visible, admission_binding,modules,IFNULL(visitdate,0) as visitdate, admission_prelim,
                {$dbv->sem_number_sql} as sem_number, {$dbv->sem_number_end_sql} as sem_number_end $add_fields
                FROM seminar_user LEFT JOIN seminare  USING (Seminar_id)
                LEFT JOIN object_user_visits ouv ON (ouv.object_id=seminar_user.Seminar_id AND ouv.user_id='$user->id' AND ouv.type='sem')
                $add_query
                WHERE seminar_user.user_id = '$user->id' ORDER BY seminare.VeranstaltungsNummer ASC");
    $num_my_sem = $db->num_rows();

    if (!$num_my_sem)
        $meldung = "info�" . sprintf(_("Sie haben zur Zeit keine Veranstaltungen abonniert, an denen Sie teilnehmen k&ouml;nnen. Bitte nutzen Sie %s<b>Veranstaltung suchen / hinzuf&uuml;gen</b>%s um neue Veranstaltungen aufzunehmen."), "<a href=\"sem_portal.php\">", "</a>") . "�" . $meldung;


    while ($db->next_record()) {
            $my_obj[$db->f("Seminar_id")] = array(
                "name"       => $db->f("Name"),
                'semname'    => $db->f('Name'),
                "status"     => $db->f("status"),
                "visible"    => $db->f("visible"),
                "gruppe"     => $db->f("gruppe"),
                "chdate"     => $db->f("chdate"),
                "binding"    => $db->f("admission_binding"),
                "modules"    => $Modules->getLocalModules($db->f("Seminar_id"), "sem", $db->f("modules"), $db->f("sem_status")),
                "obj_type"   => "sem",
                "sem_status" => $db->f("sem_status"),
                'prelim'     => $db->f('admission_prelim'),
                "visitdate"  => $db->f("visitdate"),
                "sem_number" => $db->f("sem_number"),
                "sem_number_end"   => $db->f("sem_number_end")
            );
            if ((get_config('CHAT_ENABLE')) && ($my_obj[$db->f("Seminar_id")]["modules"]["chat"])) {
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

    $db->query ("SELECT b.Name, b.Institut_id,b.type, user_inst.inst_perms,if(b.Institut_id=b.fakultaets_id,1,0) AS is_fak,
                modules,IFNULL(visitdate,0) as visitdate FROM user_inst LEFT JOIN Institute b USING (Institut_id)
                LEFT JOIN object_user_visits ouv ON (ouv.object_id=user_inst.Institut_id AND ouv.user_id='$user->id' AND ouv.type='inst')
                WHERE user_inst.user_id = '$user->id' GROUP BY Institut_id ORDER BY Name");
    $num_my_inst = $db->num_rows();
    while ($db->next_record()) {
        $my_obj[$db->f("Institut_id")]= array("name" => $db->f("Name"),"status" => $db->f("inst_perms"),
                                            "type" =>($db->f("type")) ? $db->f("type") : 1, "modules" => $Modules->getLocalModules($db->f("Institut_id"),"inst",$db->f("modules"),($db->f("type") ? $db->f("type") : 1)),
                                            "obj_type" => "inst","visitdate" => $db->f("visitdate"));
        if ((get_config('CHAT_ENABLE')) && ($my_obj[$db->f("Institut_id")]["modules"]["chat"])) {
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
    if (get_config('CHAT_ENABLE')){
        if (is_array($active_chats)){
            $chat_invs = $sms->check_list_of_chatinv(array_keys($active_chats));
        }
    }

    // Anzeige der Wartelisten

    $stmt = DBManager::get()->prepare(
        "SELECT admission_seminar_user.*, seminare.status as sem_status, ".
        "seminare.Name, seminare.admission_endtime, ".
        "seminare.admission_turnout, quota ".
        "FROM admission_seminar_user ".
        "LEFT JOIN seminare USING(seminar_id) ".
        "LEFT JOIN admission_seminar_studiengang ".
        "ON (admission_seminar_user.studiengang_id = admission_seminar_studiengang.studiengang_id ".
        "AND seminare.seminar_id = admission_seminar_studiengang.seminar_id) ".
        "WHERE user_id = ? ".
        "ORDER BY admission_type, name");
    $stmt->execute(array($user->id));

    $waitlists = $stmt->fetchAll();

    // Berechnung der uebrigen Seminare und Einrichtungen

    $db->cache_query("SELECT count(*) as count  FROM Institute");
    $db->next_record();
    $anzahlinst = $db->f("count")-$num_my_inst;

    $db->cache_query("SELECT count(*) as count  FROM seminare");
    $db->next_record();
    $anzahltext = sprintf(_("Es sind noch %s weitere Veranstaltungen sowie %s weitere Einrichtungen vorhanden."), ($db->f("count")-$num_my_sem),$anzahlinst);


    // View for Teachers

    if ($perm->have_perm("dozent")) {
        $infobox = array    (
            array  ("kategorie"  => _("Information:"),
                "eintrag" => array  (
                    array ( "icon" => "ausruf_small.gif",
                                    "text"  => $anzahltext
                    )
                )
            ),
            array  ("kategorie" => _("Veranstaltungen:"),
                "eintrag" => array  (
                    array    (  "icon" => "suche2.gif",
                                        "text"  => sprintf(_("Um weitere Veranstaltungen in Ihre pers&ouml;nliche Auswahl aufzunehmen, nutzen Sie bitte die %sSuchfunktion%s"), "<a href=\"sem_portal.php\">", "</a>")
                    ),
                    array    (  "icon" => "admin.gif",
                                        "text"  => sprintf(_("Um Veranstaltungen anzulegen, nutzen Sie bitte den %sVeranstaltungs-Assistenten%s"), "<a href=\"admin_seminare_assi.php?new_session=TRUE\">", "</a>")
                    )
                )
            ),
            array  ("kategorie" => _("Einrichtungen:"),
                "eintrag" => array  (
                    array    (  "icon" => "cont_res1.gif",
                                        "text"  => sprintf(_("Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die %sEinrichtungssuche%s."), "<a href=\"institut_browse.php\">", "</a>")
                    )
                )
            )
        );
        $sem_create_perm = (in_array(get_config('SEM_CREATE_PERM'), array('root','admin','dozent')) ? get_config('SEM_CREATE_PERM') : 'dozent');
        if ($sem_create_perm != 'dozent') unset($infobox[1]['eintrag'][1]);
    }   else {

    // View for Students

        $infobox = array    (
            array  ("kategorie"  => _("Information:"),
                "eintrag" => array  (
                    array ( "icon" => "ausruf_small.gif",
                                    "text"  => $anzahltext
                    )
                )
            ),
            array  ("kategorie" => _("Aktionen:"),
                "eintrag" => array  (
                    array    (  "icon" => "suche2.gif",
                                        "text"  => sprintf(_("Um weitere Veranstaltungen in Ihre pers&ouml;nliche Auswahl aufzunehmen, nutzen Sie bitte die %sSuchfunktion%s"), "<a href=\"sem_portal.php\">", "</a>")
                    ),
                    array    (  "icon" => "cont_res1.gif",
                                        "text"  => sprintf(_("Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die %sEinrichtungssuche%s."), "<a href=\"institut_browse.php\">", "</a>")
                    ),
                    array    (  "icon" => "meinesem.gif",
                                        "text"  => sprintf(_("Wenn Sie weitere Einrichtungen in ihre pers&ouml;nliche Auswahl aufnehmen m&ouml;chten, k&ouml;nnen sie sich hier %szuordnen%s."), "<a href=\"edit_about.php?view=Studium#einrichtungen\">", "</a>")
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
        $infobox[count($infobox)-1]['eintrag'][] = array(   'icon' => 'cont_nachricht_pfeil.gif',
                                                            'text' => sprintf(_("Benachrichtigung �ber neue Inhalte %sanpassen%s."),
                                                                    '<a href="sem_notification.php">', '</a>'));
    }


    $template = $GLOBALS["template_factory"]->open("meine_seminare/index_autor");

    echo $template->render(compact(words("num_my_sem meldung group_field groups my_obj view _my_sem_open cssSw meldung chat_info chat_invs waitlists num_my_inst infobox")));
}


elseif ($auth->auth["perm"]=="admin") {

    $db2=new DB_Seminar();

    if(isset($_REQUEST['select_sem'])){
            $_default_sem = $_REQUEST['select_sem'];
    }
    if ($_default_sem){
        $semester = SemesterData::GetInstance();
        $one_semester = $semester->getSemesterData($_default_sem);
        $sem_condition = "AND seminare.start_time <=".$one_semester["beginn"]." AND (".$one_semester["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
    } else {
        $sem_condition = '';
    }
    $db->query("SELECT a.Institut_id,b.Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak,count(seminar_id) AS num_sem FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
                LEFT JOIN seminare ON(seminare.Institut_id=b.Institut_id $sem_condition )   WHERE a.user_id='$user->id' AND a.inst_perms='admin' GROUP BY a.Institut_id ORDER BY is_fak,Name,num_sem DESC");

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
        $meldung="info�" . sprintf(_("Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zust&auml;ndigen %sAdministratoren%s."), "<a href=\"dispatch.php/siteinfo/show\">", "</a>") . "�".$meldung;
    else {
        $_my_inst_arr = array_keys($_my_inst);
        if(!$user->is_registered("_my_admin_inst_id")){
            $_my_admin_inst_id = $_my_inst_arr[0];
            $user->register("_my_admin_inst_id");
        }
        if($_REQUEST['institut_id']){
            $_my_admin_inst_id = ($_my_inst[$_REQUEST['institut_id']]) ? $_REQUEST['institut_id'] : $_my_inst_arr[0];
        }

        //tic #650 sortierung in der userconfig merken
        if (isset($sortby) && in_array($sortby, words('vnummer Name status teilnehmer'))) {
            $userConfig->store('MEINE_SEMINARE_SORT', $sortby);
        } else {
            $sortby=$userConfig->getValue('MEINE_SEMINARE_SORT');

            if ($sortby=="" || $sortby==false) {
                $sortby="VeranstaltungsNummer ASC, Name ASC";
            }
        }
        if ($sortby == "teilnehmer") {
            $sortby = "teilnehmer DESC";
        } elseif ($sortby == "vnummer") {
            $sortby = "VeranstaltungsNummer ASC";
        } elseif ($sortby == "status") {
            $sortby = "status ASC, VeranstaltungsNummer ASC";
        }

        $db->query("SELECT Institute.Name AS Institut, seminare.VeranstaltungsNummer, seminare.Seminar_id,seminare.Name,seminare.status,seminare.chdate,
                    seminare.start_time,seminare.admission_binding,seminare.visible, seminare.modules,
                    COUNT(seminar_user.user_id) AS teilnehmer,IFNULL(visitdate,0) as visitdate,
                    sd1.name AS startsem,IF(duration_time=-1, '"._("unbegrenzt")."', sd2.name) AS endsem
                    FROM Institute INNER JOIN seminare ON(seminare.Institut_id=Institute.Institut_id $sem_condition )
                    STRAIGHT_JOIN seminar_user on seminare.seminar_id=seminar_user.seminar_id
                    LEFT JOIN object_user_visits ouv ON (ouv.object_id=seminare.Seminar_id AND ouv.user_id='$user->id' AND ouv.type='sem')
                    LEFT JOIN semester_data sd1 ON ( start_time BETWEEN sd1.beginn AND sd1.ende)
                    LEFT JOIN semester_data sd2 ON ((start_time + duration_time) BETWEEN sd2.beginn AND sd2.ende)
                    WHERE Institute.Institut_id='$_my_admin_inst_id' GROUP BY seminare.Seminar_id ORDER BY $sortby");
        $num_my_sem=$db->num_rows();
        if (!$num_my_sem) {
            $meldung = "msg�"
                    . sprintf(_("An der Einrichtung <i>%s</i> sind zur Zeit keine Veranstaltungen angelegt."), htmlReady($_my_inst[$_my_admin_inst_id]['name']))
                    . "�"
                    . $meldung;
        } else {
            while ($db->next_record()) {

                $db2->query("SELECT position, Nachname, username FROM  seminar_user ".
                            "LEFT JOIN auth_user_md5  USING (user_id) ".
                            "WHERE Seminar_id='".$db->f("Seminar_id")."' AND status='dozent' ".
                            "ORDER BY position, Nachname ASC");
                $dozenten = array();
                while ($db2->next_record()) {
                    $dozenten[] = array('username' => $db2->f("username"),
                                        'Nachname' => $db2->f("Nachname"));
                }

                $my_sem[$db->f("Seminar_id")] = array(
                        'visitdate' => $db->f('visitdate'),
                        'institut' => $db->f("Institut"),
                        'teilnehmer' => $db->f("teilnehmer"),
                        'vn' => $db->f("VeranstaltungsNummer"),
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
                                    $db->f("status")),
                        'dozenten' => $dozenten
                        );
            }
            get_my_obj_values($my_sem, $GLOBALS['user']->id);
        }
    }



    $template = $GLOBALS["template_factory"]->open("meine_seminare/index_admin");
    echo $template->render(compact(words("meldung _my_inst _my_admin_inst_id _default_sem num_my_sem Modules cssSw my_sem")));
}
    include ('lib/include/html_end.inc.php');
    ob_end_flush(); //Outputbuffering beenden
    page_close();
