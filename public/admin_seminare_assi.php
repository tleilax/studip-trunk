<?
# Lifter001: TODO - URLHelper installed, but still no multi-tab-compatibilty
# Lifter002: TODO
# Lifter005: TODO - md5 hash
# Lifter007: TODO
# Lifter003: TODO
/*
admin_seminare_assi.php - Seminar-Assisten von Stud.IP.
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>

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

page_open(array('sess' => 'Seminar_Session', 'auth' => 'Seminar_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));

require_once ('lib/msg.inc.php');       //Funktionen fuer Nachrichtenmeldungen
require_once ('config.inc.php');        //wir brauchen die Seminar-Typen
require_once ('config_tools_semester.inc.php');  //Bereitstellung weiterer Daten
require_once 'lib/functions.php';       //noch mehr Stuff
require_once ('lib/forum.inc.php');     //damit wir Themen anlegen koennen
require_once ('lib/visual.inc.php');        //Aufbereitungsfunktionen
require_once ('lib/dates.inc.php');     //Terminfunktionen
require_once ('lib/log_events.inc.php');
require_once ('lib/classes/StudipSemTreeSearch.class.php');
require_once ('lib/classes/Modules.class.php');
require_once ('lib/classes/DataFieldEntry.class.php');
require_once ('lib/classes/UserDomain.php'); // Nutzerdom�nen
require_once ('lib/classes/SeminarCategories.class.php');
require_once ('lib/classes/LockRules.class.php');
require_once 'lib/classes/Seminar.class.php';
require_once 'lib/classes/StudipStudyAreaSelection.class.php';

$sem_create_perm = (in_array(get_config('SEM_CREATE_PERM'), array('root','admin','dozent')) ? get_config('SEM_CREATE_PERM') : 'dozent');

$perm->check($sem_create_perm);

// Set this to something, just something different...
$hash_secret = "nirhtak";

include ('lib/seminar_open.php');   //hier werden die sessions initialisiert

if ($RESOURCES_ENABLE) {
    include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
    include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
    include_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
    $resAssign = new VeranstaltungResourcesAssign();
}

//cancel
if ($cancel_x) {
    header ("Location: admin_seminare1.php?");
}

// Get a database connection and Stuff
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;
$cssSw = new cssClassSwitcher;
$Modules = new Modules;
$semester = new SemesterData;


//Registrieren der Sessionvariablen
$sess->register("sem_create_data");
$sess->register("links_admin_data");

# init of study area selection
$study_areas = isset($_REQUEST['study_area_selection'])
   ? remove_magic_quotes($_REQUEST['study_area_selection'])
   : array();

$area_selection = new StudipStudyAreaSelection();

if (isset($study_areas['last_selected'])) {
    $area_selection->setSelected((string) $study_areas['last_selected']);
}

if (isset($study_areas['showall'])) {
    $area_selection->setShowAll((boolean) $study_areas['showall']);
}

if (isset($study_areas['areas'])) {
    $area_selection->setAreas((array) $study_areas['areas']);
} else if (isset($sem_create_data["sem_bereich"])) {
    $area_selection->setAreas((array) $sem_create_data["sem_bereich"]);
}

$user_id = $auth->auth["uid"];
$errormsg='';

//initialisations for room-requests
if ($RESOURCES_ENABLE && $RESOURCES_ALLOW_ROOM_REQUESTS && $form <7) {
    $sem_create_data["resRequest"] = @unserialize($sem_create_data["resRequest"]);
    if (!$sem_create_data["resRequest"]) {
        $sem_create_data["resRequest"] = new RoomRequest();
    }
}



//verbotene Kategorien checken
if (($cmd == 'do_copy' && SeminarCategories::GetBySeminarId($cp_id)->course_creation_forbidden)
    || ( $form && (SeminarCategories::Get($sem_create_data['sem_class']) === false || SeminarCategories::Get($sem_create_data['sem_class'])->course_creation_forbidden))){
    unset($cmd);
    unset($start_level);
    unset($form);
    $sem_create_data = '';
    $errormsg = "error�" . sprintf(_("Veranstaltungen dieser Kategorie d�rfen in dieser Installation nicht angelegt werden!"));
}

// Kopieren einer vorhandenen Veranstaltung
//
if (isset($cmd) && ($cmd == 'do_copy') && $perm->have_studip_perm('tutor',$cp_id)) {
    if(LockRules::Check($cp_id, 'seminar_copy')) {
        $lockRule = new LockRules();
        $lockdata = $lockRule->getSemLockRule($cp_id);
        $errormsg = 'error�' . _("Die Veranstaltung kann nicht kopiert werden.").'�';
        if ($lockdata['description']){
            $errormsg .= "info�" . fixlinks($lockdata['description']).'�';
        }
        unset($cmd);
        unset($start_level);
        unset($form);
        $sem_create_data = '';
    } else {
        // Eintr�ge in generischen Datenfelder auslesen und zuweisen
        $sql = "SELECT datafields_entries.datafield_id, datafields_entries.content, datafields.name, datafields.type FROM datafields_entries LEFT JOIN datafields USING (datafield_id) WHERE range_id = '$cp_id'";
        $db->query($sql);
        while ($db->next_record()) {
            $s_d_fields[$db->f("datafield_id")] = array("type"=>$db->f("type"), "name"=>$db->f("name"), "value"=>$db->f("content"));
        }

        // Beteiligte Einrichtungen finden und zuweisen
        $sql = "SELECT institut_id FROM seminar_inst WHERE seminar_id = '$cp_id'";
        $db->query($sql);
        while ($db->next_record()) {
            $sem_bet_inst[] = $db->f("institut_id");
        }

        // Veranstaltungsgrunddaten finden
        $sql = "SELECT * FROM seminare WHERE Seminar_id = '$cp_id'";
        $db->query($sql);
        $db->next_record();
        $sem_create_data = '';
        $sem_create_data["sem_datafields"] = $s_d_fields;
        $sem_create_data["sem_bet_inst"] = $sem_bet_inst;

        // Termine
        $serialized_metadata = $db->f("metadata_dates");
        $data = unserialize($serialized_metadata);
        $term_turnus = $data["turnus_data"];
        $sem_create_data["term_turnus"] = $data["turnus"];
        $sem_create_data["term_start_woche"] = $data["start_woche"];
        $sem_create_data["sem_start_termin"] = $data["start_termin"];
        $sem_create_data["turnus_count"] = count($term_turnus);
        $sem_create_data["term_art"] = count($term_turnus) > 0 ? 0 : 1;

        // Nutzerdom�nen
        $sem_create_data["sem_domain"] = UserDomain::getUserDomainsForSeminar($cp_id);

        if ($sem_create_data["term_art"] == 1) { //unregelmaessige Veranstaltung oder Block -> Termine kopieren
            // Sitzungen
            $db2->query('SELECT * FROM termine WHERE range_id=\''. $cp_id . '\' AND date_typ=\'1\' ORDER by date');
            $db2_term_count = 0;
            while ($db2->next_record()) {
                $db2_start_date = $db2->f('date');
                $db2_end_date = $db2->f('end_time');
                $db2_raum = $db2->f('raum');
                $sem_create_data['term_tag'][$db2_term_count] = intval(date('j', $db2_start_date));
                $sem_create_data['term_monat'][$db2_term_count] = intval(date('n', $db2_start_date));
                $sem_create_data['term_jahr'][$db2_term_count] = intval(date('Y', $db2_start_date));
                $sem_create_data['term_start_stunde'][$db2_term_count] = intval(date('G', $db2_start_date));
                $sem_create_data['term_start_minute'][$db2_term_count] = intval(date('i', $db2_start_date));
                $sem_create_data['term_end_stunde'][$db2_term_count] = intval(date('G', $db2_end_date));
                $sem_create_data['term_end_minute'][$db2_term_count] = intval(date('i', $db2_end_date));
                $sem_create_data['term_room'][$db2_term_count] = ($db2_raum)? $db2_raum : '';
                $db2_term_count++;
            }
            $sem_create_data['term_count'] = $db2_term_count;
            // Vorbesprechung
            //      $db2->query('SELECT * FROM termine WHERE range_id=\'' . $cp_id. '\' AND date_typ=\'2\' ORDER by date');
            //      if ($db2->next_record()) {
            //          $sem_create_data['sem_vor_termin'] = $db2->f('date');
            //          $sem_create_data['sem_vor_end_termin']  = $db2->f('end_time');
            //          if ($db2->f('raum'))
            //              $sem_create_data['sem_vor_raum'] = $db2->f('raum');
            //      } else {
            $sem_create_data['sem_vor_end_termin'] = -1;
            $sem_create_data['sem_vor_termin'] = -1;
            //      }
        } else {
            // Keine Vorbesprechungstermine kopieren
            $sem_create_data['sem_vor_end_termin'] = -1;
            $sem_create_data['sem_vor_termin'] = -1;
        }

        for ($i=0;$i<$sem_create_data["turnus_count"];$i++) {
            $sem_create_data["term_turnus_start_stunde"][$i] = $term_turnus[$i]["start_stunde"];
            $sem_create_data["term_turnus_start_minute"][$i] = $term_turnus[$i]["start_minute"];
            $sem_create_data["term_turnus_end_stunde"][$i] = $term_turnus[$i]["end_stunde"];
            $sem_create_data["term_turnus_end_minute"][$i] = $term_turnus[$i]["end_minute"];
            $sem_create_data["term_turnus_resource_id"][$i] = $term_turnus[$i]["resource_id"];
            $sem_create_data["term_turnus_room"][$i] = $term_turnus[$i]["room"];
            $sem_create_data["term_turnus_date"][$i] = $term_turnus[$i]["day"];
            $sem_create_data["term_turnus_desc"][$i] = $term_turnus[$i]["desc"];
        }

        // Sonstiges
        $sem_create_data["sem_id"] = $db->f("Seminar_id");
        $sem_create_data["sem_nummer"] = $db->f("VeranstaltungsNummer");
        $sem_create_data["sem_inst_id"] = $db->f("Institut_id");
        $sem_create_data["sem_name"] = $db->f("Name");
        $sem_create_data["sem_untert"] = $db->f("Untertitel");
        $sem_create_data["sem_status"] = $db->f("status");
        $class = $SEM_TYPE[$sem_create_data["sem_status"]]["class"];
        $sem_create_data["sem_class"] = $class;
        $sem_create_data["sem_desc"] = $db->f("Beschreibung");
        $sem_create_data["sem_room"] = $db->f("Ort");
        $sem_create_data["sem_sonst"] = $db->f("Sonstiges");
        $sem_create_data["sem_pw"] = $db->f("Passwort");
        $sem_create_data["sem_sec_lese"] = $db->f("Lesezugriff");
        $sem_create_data["sem_sec_schreib"] = $db->f("Schreibzugriff");
        $sem_create_data["sem_start_time"] = $db->f("start_time");
        $sem_create_data["sem_duration_time"] = $db->f("duration_time");
        $sem_create_data["sem_art"] = $db->f("art");
        $sem_create_data["sem_teiln"] = $db->f("teilnehmer");
        $sem_create_data["sem_voraus"] = $db->f("vorrausetzungen");
        $sem_create_data["sem_orga"] = $db->f("lernorga");
        $sem_create_data["sem_leistnw"] = $db->f("leistungsnachweis");
        $sem_create_data["sem_ects"] = $db->f("ects");
        //$sem_create_data["sem_admission_date"] = $db->f("admission_endtime");
        $sem_create_data["sem_admission_date"] = -1;
        $sem_create_data["sem_turnout"] = $db->f("admission_turnout");
        //$sem_create_data["sem_admission"] = $db->f("admission_type");
        $sem_create_data["sem_payment"] = $db->f("admission_prelim");
        $sem_create_data["sem_paytxt"] = $db->f("admission_prelim_txt");
        //$sem_create_data["sem_admission_start_date"] = $db->f("admission_starttime");
        //$sem_create_data["sem_admission_end_date"] = $db->f("admission_endtime_sem");
        $sem_create_data["sem_admission_start_date"] = -1;
        $sem_create_data["sem_admission_end_date"] = -1;
        $sem_create_data["timestamp"] = time(); // wichtig, da sonst beim ersten Aufruf sofort sem_create_data resetted wird!
        // eintragen der sem_tree_ids
        $sem_create_data["sem_bereich"] = get_seminar_sem_tree_entries($cp_id);

        // Modulkonfiguration �bernehmen
        $sem_create_data['modules_list'] = $Modules->getLocalModules($cp_id,'sem');
        $sem_create_data['sem_modules'] = $db->f('modules');

        // Pluginkonfiguration �bernehmen
        $enabled_plugins = PluginEngine::getPlugins('StandardPlugin', $cp_id);

        foreach ($enabled_plugins as $plugin) {
            $sem_create_data["enabled_plugins"][] = $plugin->getPluginId();
        }

        // Dozenten und Tutoren eintragen
        $sem_create_data["sem_doz"] = get_seminar_dozent($cp_id);
        if (!$sem_create_data["sem_tut"] = get_seminar_tutor($cp_id)) {
            unset($sem_create_data["sem_tut"]);
        }
    }
}

//Assi-Modus an und gesetztes Object loeschen solange keine Veranstaltung angelegt
if (!$sem_create_data["sem_entry"]) {
    $links_admin_data["assi"]=TRUE;
    closeObject();
} else
    $links_admin_data["assi"]=FALSE;

if (($auth->lifetime != 0 && ((time() - $sem_create_data["timestamp"]) >$auth->lifetime*60)) || ($new_session))
    {
    $sem_create_data='';
    $links_admin_data='';
    $sem_create_data["sem_start_termin"]=-1;
    $sem_create_data["sem_vor_termin"]=-1;
    $sem_create_data["sem_vor_end_termin"]=-1;
    $sem_create_data["sem_admission_date"]=-1;
    $sem_create_data["sem_admission_ratios_changed"]=FALSE;
    $sem_create_data["sem_admission_start_date"]=-1;
    $sem_create_data["sem_admission_end_date"]=-1;
    $sem_create_data["sem_payment"]=0;

    # reset study area selection
    $area_selection = new StudipStudyAreaSelection();
    $sem_create_data["sem_bereich"] = array();

    if ($GLOBALS['ASSI_SEMESTER_PRESELECT']){
        if ($_default_sem){
            $one_sem = $semester->getSemesterData($_default_sem);
            if ($one_sem["vorles_ende"] > time()) $sem_create_data['sem_start_time'] = $one_sem['beginn'];
        }
    }
    $sem_create_data["timestamp"]=time();
    }
else
    $sem_create_data["timestamp"]=time();

//wenn das Seminar bereits geschrieben wurde und wir trotzdem frisch reinkommen, soll die Variable geloescht werden
if (($sem_create_data["sem_entry"]) && (!$form))
    {
    $sem_create_data='';
    $sem_create_data["sem_start_termin"]=-1;
    $sem_create_data["sem_vor_termin"]=-1;
    $sem_create_data["sem_vor_end_termin"]=-1;
    }

//empfangene Variablen aus diversen Formularen auswerten
if ($start_level) { //create defaults
    if (!array_key_exists('sem_class', $sem_create_data))
        $sem_create_data['sem_class'] = $class;

    if (!array_key_exists('sem_modules', $sem_create_data)){
        foreach ($SEM_TYPE as $key => $val) {
            if ($val['class'] == $class) {
                $sem_create_data['modules_list'] = $Modules->getLocalModules('', 'sem', false, $key);
                break;
            }
        }
    }

    if ($SEM_CLASS[$class]['turnus_default'] && !array_key_exists('term_art', $sem_create_data))
        $sem_create_data['term_art'] = $SEM_CLASS[$class]['turnus_default'];

    if ($SEM_CLASS[$class]['default_read_level'] && !array_key_exists('sem_sec_lese', $sem_create_data))
        $sem_create_data['sem_sec_lese'] = $SEM_CLASS[$class]['default_read_level'];

    if ($SEM_CLASS[$class]['default_write_level'] && !array_key_exists('sem_sec_schreib', $sem_create_data))
        $sem_create_data['sem_sec_schreib'] = $SEM_CLASS[$class]['default_write_level'];

    if ($auth->auth['perm'] == 'dozent')
        $sem_create_data['sem_doz'][$user->id] = 1;
}

if ($form == 1)
    {
    $sem_create_data["sem_name"]=$sem_name;
    $sem_create_data["sem_untert"]=$sem_untert;
    $sem_create_data["sem_nummer"]=$sem_nummer;
    $sem_create_data["sem_ects"]=$sem_ects;
    $sem_create_data["sem_desc"]=$sem_desc;
    $sem_create_data["sem_inst_id"]=$sem_inst_id;
    $sem_create_data["term_art"]=$term_art;
    $sem_create_data["sem_start_time"]=$sem_start_time;
    $sem_create_data["sem_domain"] = array();

    # pre-select Heimatinstitut
    if (!isset($study_areas['last_selected']) &&
        !isset($study_areas['selected'])) {

        #   1.) get the ID of the faculty of the chosen institute
        $stmt = DBManager::get()->prepare('SELECT fakultaets_id FROM Institute '.
                                          'WHERE Institut_id = ?');
        $stmt->execute(array($sem_create_data["sem_inst_id"]));
        $row = $stmt->fetch();

        #   2.) get the sem_tree ID of that faculty
        $stmt = DBManager::get()->prepare('SELECT sem_tree_id FROM sem_tree '.
                                          'WHERE studip_object_id = ?');
        $stmt->execute(array($row['fakultaets_id']));
        $row = $stmt->fetch();
        #   3.) pre-select that ID
        if ($row !== FALSE) {
            $area_selection->setSelected($row['sem_tree_id']);
        }
    }

    if (isset($_default_sem)){
        $one_sem = $semester->getSemesterDataByDate($sem_create_data["sem_start_time"]);
        $_default_sem = $one_sem['semester_id'];
    }
    if (($sem_duration_time == 0) || ($sem_duration_time == -1))
        $sem_create_data["sem_duration_time"]=$sem_duration_time;
    else
        $sem_create_data["sem_duration_time"]=$sem_duration_time - $sem_start_time;

    $sem_create_data["sem_turnout"]=$sem_turnout;

    //save max. count of participants for room-request
    if (($RESOURCES_ENABLE) && (is_object($sem_create_data["resRequest"])))
        $sem_create_data["resRequest"]->setDefaultSeats($sem_turnout);

    //Anmeldeverfahren festlegen
    if($sem_create_data["sem_admission"] = $sem_admission){
        if(!is_array($sem_create_data["sem_studg"]) || !count($sem_create_data["sem_studg"])) $sem_create_data["sem_studg"]['all'] = array('name' => _("Alle Studieng�nge"), 'ratio' => 100);
    } else {
        $sem_create_data["sem_studg"] = array();
    }

    //accept only temporaly?
    $sem_create_data["sem_payment"]=$sem_payment;

    if ($sem_bet_inst)
        {
        foreach ($sem_bet_inst as $tmp_array)
                $tmp_create_data_bet_inst[]=$tmp_array;
        $sem_create_data["sem_bet_inst"]=$tmp_create_data_bet_inst;
        }
    $i=0;
    $sem_create_data["sem_status"]=$sem_status;
    $sem_create_data["sem_art"]=$sem_art;
    }

if ($form == 2) {

    # evaluate study area selection

    # action: add
    if (isset($study_areas['add'])) {
        foreach ($study_areas['add'] as $key => $value) {
            $area_selection->add($key);
      }
    }

    # action: remove
    else if (isset($study_areas['remove'])) {
        foreach ($study_areas['remove'] as $key => $value) {
            $area_selection->remove($key);
        }
    }

    # action: switch show all
    else if (isset($study_areas['showall_button'])) {
        $area_selection->toggleShowAll();
    }

    # action: search
    else if (isset($study_areas['search_key']) &&
             $study_areas['search_key'] != '') {
        $area_selection->setSearchKey($study_areas['search_key']);
    }

    # action: expand
    else if (isset($study_areas['selected'])) {
        $area_selection->setSelected($study_areas['selected']);
    }

    $sem_create_data["sem_bereich"] = $area_selection->getAreaIDs();


    if (!$sem_create_data["sem_admission"]) {
        $sem_create_data["sem_sec_lese"]=$sem_sec_lese;
        $sem_create_data["sem_sec_schreib"]=$sem_sec_schreib;
    } else {
        $sem_create_data["sem_sec_lese"]=3;
        $sem_create_data["sem_sec_schreib"]=3;
    }
    }

if ($form == 3)
    {
    $sem_create_data["term_start_woche"]=$term_start_woche;
    $sem_create_data["term_turnus"]=$term_turnus;

    if ($sem_create_data["term_art"] == 0)
        {
        //Arrays fuer Turnus loeschen
        $sem_create_data["term_turnus_date"]='';
        $sem_create_data["term_turnus_start_stunde"]='';
        $sem_create_data["term_turnus_start_minute"]='';
        $sem_create_data["term_turnus_end_stunde"]='';
        $sem_create_data["term_turnus_end_minute"]='';
        $sem_create_data["term_turnus_desc"]='';

        //Alle eingegebenen Turnus-Daten in Sessionvariable uebernehmen
        for ($i=0; $i<$sem_create_data["turnus_count"]; $i++) {
            $sem_create_data["term_turnus_date"][$i]=$term_turnus_date[$i];
            $sem_create_data["term_turnus_start_stunde"][$i] = (strlen($term_turnus_start_stunde[$i]))? intval($term_turnus_start_stunde[$i]) : '';
            $sem_create_data["term_turnus_start_minute"][$i] = (strlen($term_turnus_start_minute[$i]))? intval($term_turnus_start_minute[$i]) : '';
            $sem_create_data["term_turnus_end_stunde"][$i] = (strlen($term_turnus_end_stunde[$i]))? intval($term_turnus_end_stunde[$i]) : '';
            $sem_create_data["term_turnus_end_minute"][$i] = (strlen($term_turnus_end_minute[$i]))? intval($term_turnus_end_minute[$i]) : '';
            $sem_create_data["term_turnus_desc"][$i]=($term_turnus_desc[$i] ? $term_turnus_desc[$i] : $term_turnus_desc_chooser[$i]);
        }

        //Turnus-Metadaten-Array erzeugen
        $sem_create_data["metadata_termin"]='';
        $sem_create_data["metadata_termin"]["start_termin"]=$sem_create_data["sem_start_termin"];
        $sem_create_data["metadata_termin"]["start_woche"]=$sem_create_data["term_start_woche"];
        $sem_create_data["metadata_termin"]["turnus"]=$sem_create_data["term_turnus"];

        //indizierte (=sortierbares Temporaeres Array erzeugen)
        if ($sem_create_data["term_art"] == 0)
            {
            for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
                if (($sem_create_data["term_turnus_start_stunde"][$i] !== '')  && ($sem_create_data["term_turnus_end_stunde"][$i] !== '')) {
                    //Index erzeugen
                    $tmp_idx=$sem_create_data["term_turnus_date"][$i];
                    if ($sem_create_data["term_turnus_start_stunde"][$i] < 10)
                        $tmp_idx.="0";
                    $tmp_idx.=$sem_create_data["term_turnus_start_stunde"][$i];
                    if ($sem_create_data["term_turnus_start_minute"][$i] < 10)
                        $tmp_idx.="0";
                    $tmp_idx.=$sem_create_data["term_turnus_start_minute"][$i];
                    $tmp_metadata_termin["turnus_data"][]=array("idx"=>$tmp_idx,
                                                                "day" => $sem_create_data["term_turnus_date"][$i],
                                                                "start_stunde" => $sem_create_data["term_turnus_start_stunde"][$i],
                                                                "start_minute" => $sem_create_data["term_turnus_start_minute"][$i],
                                                                "end_stunde" => $sem_create_data["term_turnus_end_stunde"][$i],
                                                                "end_minute" => $sem_create_data["term_turnus_end_minute"][$i],
                                                                // they are not needed anymore, but who knows...
                                                                "room"=>$sem_create_data["term_turnus_room"][$i],
                                                                //"resource_id"=>$sem_create_data["term_turnus_resource_id"][$i],
                                                                "desc"=>$sem_create_data["term_turnus_desc"][$i]
                                                                );
                }

            if (is_array($tmp_metadata_termin["turnus_data"])) {
                //check for dublettes
                $tmp_array_assi = $tmp_metadata_termin["turnus_data"];
                foreach ($tmp_array_assi as $key1=>$val1)  {
                    foreach ($tmp_metadata_termin["turnus_data"] as $key2=>$val2) {
                        if (($val1["day"] == $val2["day"]) &&
                            ($val1["start_stunde"] == $val2["start_stunde"]) &&
                            ($val1["start_minute"] == $val2["start_minute"]) &&
                            ($val1["end_stunde"] == $val2["end_stunde"]) &&
                            ($val1["end_minute"] == $val2["end_minute"]) &&
                            ($val1["room"] == $val2["room"]) &&
                            ($val1["ressource_id"] == $val2["ressource_id"]) &&
                            ($key1 != $key2))
                            unset ($tmp_metadata_termin["turnus_data"][$key1]);
                    }
                }

                //sortieren
                sort ($tmp_metadata_termin["turnus_data"]);

                foreach ($tmp_metadata_termin["turnus_data"] as $tmp_array)
                    {
                    $sem_create_data["metadata_termin"]["turnus_data"][]=$tmp_array;
                    }
                }
            }
        }
    else
        {
        //Arrays fuer Termine loeschen
        $sem_create_data["term_tag"]='';
        $sem_create_data["term_monat"]='';
        $sem_create_data["term_jahr"]='';
        $sem_create_data["term_start_stunde"]='';
        $sem_create_data["term_start_minute"]='';
        $sem_create_data["term_end_stunde"]='';
        $sem_create_data["term_end_minute"]='';
        $sem_create_data["term_first_date"]='';


        //Alle eingegebenen Termin-Daten in Sessionvariable uebernehmen
        for ($i=0; $i<$sem_create_data["term_count"]; $i++) {
            $sem_create_data["term_tag"][$i]=$term_tag[$i];
            $sem_create_data["term_monat"][$i]=$term_monat[$i];
            $sem_create_data["term_jahr"][$i]=$term_jahr[$i];
            $sem_create_data["term_start_stunde"][$i] = (strlen($term_start_stunde[$i]))? intval($term_start_stunde[$i]) : '';
            $sem_create_data["term_start_minute"][$i] = (strlen($term_start_minute[$i]))? intval($term_start_minute[$i]) : '';
            $sem_create_data["term_end_stunde"][$i] = (strlen($term_end_stunde[$i]))? intval($term_end_stunde[$i]) : '';
            $sem_create_data["term_end_minute"][$i] = (strlen($term_end_minute[$i]))? intval($term_end_minute[$i]) : '';

            //erster Termin wird gepeichert, wird fuer spaetere Checks benoetigt
            if ((($sem_create_data["term_first_date"] == 0)
                || ($sem_create_data["term_first_date"] >mktime((int)$sem_create_data["term_start_stunde"][$i], (int)$sem_create_data["term_start_minute"][$i], 0, (int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i])))
                && (mktime((int)$sem_create_data["term_start_stunde"][$i], (int)$sem_create_data["term_start_minute"][$i], 0, (int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i]) > 0)) {
                $sem_create_data["term_first_date"]=mktime((int)$sem_create_data["term_start_stunde"][$i], (int)$sem_create_data["term_start_minute"][$i], 0, (int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i]);
            }
        }
    }

    //set the term_art in every case...
    $sem_create_data["metadata_termin"]["art"]=$sem_create_data["term_art"];

    //Datum fuer Vobesprechung umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
    if (!$vor_tag && !$vor_monat && !$vor_jahr){
        $sem_create_data["sem_vor_termin"] = $sem_create_data["sem_vor_end_termin"] = -1;
    } else {
        if (!check_and_set_date($vor_tag, $vor_monat, $vor_jahr, $vor_stunde, $vor_minute, $sem_create_data, "sem_vor_termin")
        || !check_and_set_date($vor_tag, $vor_monat, $vor_jahr, $vor_end_stunde, $vor_end_minute, $sem_create_data, "sem_vor_end_termin")){
            $errormsg=$errormsg."error�"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r Start- und Endzeit der Vorbesprechung ein!")."�";
        } elseif ($sem_create_data["sem_vor_termin"] >= $sem_create_data["sem_vor_end_termin"]) {
            $errormsg .= "error�"._("Die Endzeit der Vorbesprechung darf nicht vor der Startzeit liegen!")."�";
        }
    }
}

if ($form == 4) {
    $sem_create_data["sem_room"]=$sem_room;
    //The room for the prelimary discussion
    $sem_create_data["sem_vor_raum"]=$vor_raum;
    $sem_create_data["sem_vor_resource_id"]=($vor_resource_id == "FALSE") ? FALSE : $vor_resource_id;
    //if we have a resource_id, we take the room name from resource_id (deprecated at the moment)
    /*if ($RESOURCES_ENABLE && $sem_create_data["sem_vor_resource_id"]) {
        $resObject = ResourceObject::Factory($sem_create_data["sem_vor_resource_id"]);
        $sem_create_data["sem_vor_raum"]=$resObject->getName();
    }*/

    if (($RESOURCES_ENABLE) && (is_object($sem_create_data["resRequest"]))) {
        //Room-Requests
        $sem_create_data['skip_room_request'] = (isset($_REQUEST['skip_room_request']));

        if ($send_room_x)
            $sem_create_data["resRequest"]->setResourceId($select_room);
        if ($reset_resource_id_x)
            $sem_create_data["resRequest"]->setResourceId(FALSE);
        if ($send_room_type_x)
            $sem_create_data["resRequest"]->setCategoryId($select_room_type);
        if ($reset_room_type_x)
            $sem_create_data["resRequest"]->setCategoryId(FALSE);

        $sem_create_data["resRequest"]->setComment(remove_magic_quotes($sem_room_comment));

        //Property Requests
        if ($sem_create_data["resRequest"]->getCategoryId()) {
            $availableProperties = $sem_create_data["resRequest"]->getAvailableProperties();
            if (is_array($availableProperties)) {
                foreach ($sem_create_data["resRequest"]->getAvailableProperties() as $key=>$val) {
                    if ($val["system"] == 2) { //it's the property for the seat/room-size!
                        if ($seats_are_admission_turnout)
                            $sem_create_data["resRequest"]->setPropertyState($key, $sem_create_data["sem_turnout"]);
                        elseif (!$send_room_type_x)
                            $sem_create_data["resRequest"]->setPropertyState($key, abs($request_property_val[$key]));
                    } else {
                        $sem_create_data["resRequest"]->setPropertyState($key, $request_property_val[$key]);
                    }
                }
            }
        }

    }

    if ($sem_create_data["term_art"]==0) {
        //get incoming room-data
        if (is_array($sem_create_data["metadata_termin"]["turnus_data"]))
            foreach ($sem_create_data["metadata_termin"]["turnus_data"] as $key=>$val) {
                //echo $term_turnus_room[$key], $term_turnus_resource_id[$key];

                $sem_create_data["metadata_termin"]["turnus_data"][$key]["room"] = $term_turnus_room[$key];
                $sem_create_data["metadata_termin"]["turnus_data"][$key]["resource_id"] = ($term_turnus_resource_id[$key] == "FALSE") ? FALSE : $term_turnus_resource_id[$key];

                //if we have a resource_id, we take the room name from resource_id (deprecated at the moment)
                /*if ($RESOURCES_ENABLE && $sem_create_data["metadata_termin"]["turnus_data"][$key]["resource_id"]) {
                    $resObject = ResourceObject::Factory($sem_create_data["metadata_termin"]["turnus_data"][$key]["resource_id"]);
                    $sem_create_data["metadata_termin"]["turnus_data"][$key]["room"]=$resObject->getName();
                }*/
            }
    } else {
        for ($i=0; $i<$sem_create_data["term_count"]; $i++) {
            $sem_create_data["term_room"][$i]=$term_room[$i];
            $sem_create_data["term_resource_id"][$i]=($term_resource_id[$i] == "FALSE") ? FALSE : $term_resource_id[$i];
            //if we have a resource_id, we take the room name from resource_id (deprecated at the moment)
            /*if ($RESOURCES_ENABLE && $sem_create_data["term_resource_id"][$i]) {
                $resObject = ResourceObject::Factory($sem_create_data["term_resource_id"][$i]);
                $sem_create_data["term_room"][$i]=$resObject->getName();
            }*/
        }
    }
}

if ($form == 5) {

    if(isset($_REQUEST['toggle_admission_quota_x'])){
        $sem_create_data["admission_enable_quota"] = (int)($_REQUEST["admission_enable_quota"]);
        if(!$sem_create_data["admission_enable_quota"]){
            $sem_create_data["sem_admission_date"] = -1;
            $sem_create_data["sem_admission_ratios_changed"] = false;
        }

    }
    // create a timestamp for begin and end of the seminar
        if (!check_and_set_date($adm_s_tag, $adm_s_monat, $adm_s_jahr, $adm_s_stunde, $adm_s_minute, $sem_create_data, "sem_admission_start_date")) {
        $errormsg=$errormsg."error�"._("Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r den Start des Anmeldezeitraums ein!")."�";
    }
        if (!check_and_set_date($adm_e_tag, $adm_e_monat, $adm_e_jahr, $adm_e_stunde, $adm_e_minute, $sem_create_data, "sem_admission_end_date")) {
        $errormsg=$errormsg."error�"._("Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r das Ende des Anmeldezeitraums ein!")."�";
    }
    if ($sem_create_data["sem_admission_end_date"] != -1) {
        if ($sem_create_data["sem_admission_end_date"] < time())
        {
            $errormsg=$errormsg."error�"._("Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r das Ende des Teilnahmeverfahrens ein!")."�";
        }
        if ($sem_create_data["sem_admission_end_date"] <= $sem_create_data["sem_admission_start_date"]) {
            $errormsg=$errormsg."error�"._("Das Enddatum des Teilnahmeverfahrens muss nach dem Startdatum liegen!")."�";
        }
    }

    $sem_create_data["sem_teiln"]=$sem_teiln;
    $sem_create_data["sem_voraus"]=$sem_voraus;
    $sem_create_data["sem_orga"]=$sem_orga;
    $sem_create_data["sem_leistnw"]=$sem_leistnw;
    $sem_create_data["sem_sonst"]=$sem_sonst;
    $sem_create_data["sem_paytxt"]=$sem_paytxt;
    $sem_create_data["sem_datafields"]='';

    if (is_array($_REQUEST['sem_datafields'])) {
        foreach ($_REQUEST['sem_datafields']as $id => $df_values) {
            $struct = new DataFieldStructure(array("datafield_id"=>$id));
            $struct->load();
            $entry  = DataFieldEntry::createDataFieldEntry($struct);
            $entry->setValueFromSubmit($df_values);
            $sem_create_data['sem_datafields'][$id] = array('name'=>$entry->getName(), 'type'=>$entry->getType(), 'value'=> $entry->getValue());
        }
    }

    //Studienbereiche entgegennehmen
    if (is_array($sem_studg_id)) {
        foreach ($sem_studg_id as $key=>$val)
            if ($sem_studg_ratio_old[$key] != $sem_studg_ratio[$key])
                $sem_create_data["sem_admission_ratios_changed"]=TRUE;
        if ($sem_create_data["sem_admission_ratios_changed"]) {
            $sem_create_data["sem_studg"]='';
            foreach ($sem_studg_id as $key=>$val)
                $sem_create_data["sem_studg"][$val]=array("name"=>$sem_studg_name[$key], "ratio"=>(int)$sem_studg_ratio[$key]);
        }
    }

    //Datum fuer Ende der Anmeldung umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
    if (!check_and_set_date($adm_tag, $adm_monat, $adm_jahr, $adm_stunde, $adm_minute, $sem_create_data, "sem_admission_date")) {
            if ($sem_create_data["sem_admission"] == 1) {
                $errormsg=$errormsg."error�"._("Bitte geben Sie g&uuml;ltige Werte f&uuml;r das Losdatum ein!")."�";
            } elseif ($sem_create_data["sem_admission"] == 2 && $sem_create_data["admission_enable_quota"] == 1) {
                $errormsg=$errormsg."error�"._("Bitte geben Sie g&uuml;ltige Werte f&uuml;r das Enddatum der Kontingentierung ein!")."�";
            }
    }

    //Datum fuer ersten Termin umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
    if ($sem_create_data["term_start_woche"] == -1 && $sem_create_data["term_art"] == 0){
        if (($jahr>0) && ($jahr<100)) $jahr=$jahr+2000;

        if ($monat == _("mm")) $monat=0;
        if ($tag == _("tt")) $tag=0;
        if ($jahr == _("jjjj")) $jahr=0;
        if (!checkdate((int)$monat, (int)$tag, (int)$jahr))
        {
            $errormsg=$errormsg."error�"._("Bitte geben Sie ein g&uuml;ltiges Datum ein!")."�";
            $sem_create_data["sem_start_termin"] = -1;
        }
        else {
            $sem_create_data["sem_start_termin"] = mktime((int)$stunde,(int)$minute,0,(int)$monat,(int)$tag,(int)$jahr);
            $sem_create_data["metadata_termin"]["start_termin"] = $sem_create_data["sem_start_termin"];
            //check overlaps...
            if ($RESOURCES_ENABLE) {
                $checkResult = $resAssign->changeMetaAssigns($sem_create_data["metadata_termin"], $sem_create_data["sem_start_time"], $sem_create_data["sem_duration_time"],TRUE);
            }
        }
    }
}

if ($form == 8)
    {
    $sem_create_data["sem_scm_content"]=$sem_scm_content;
    if (!$sem_scm_name) {
        $sem_create_data["sem_scm_name"]=$SCM_PRESET[$sem_scm_preset]["name"];
        $sem_create_data["sem_scm_preset"]=$sem_scm_preset;
    } else
        $sem_create_data["sem_scm_name"]=$sem_scm_name;
}

//jump-logic
if ($jump_back_x) {
    if ($form > 1) {
        // if we have chosen to not enter dates, skip room-requests
        if ($form == 5) {
            if ($sem_create_data["term_art"] == -1) {
                $level = 2;
            } else {
                $level = 4;
            }
        }

        //jump normal a form back
        else {
            $level = $form - 1;
        }
    }
}

//not pressed any button? Send user to next page and checks...
if (!$jump_back_x
    && !$jump_next_x
    && !$add_doz
    && !$add_tut
    && !$delete_doz
    && !$delete_tut
    && !$add_turnus_field_x
    && !$delete_turnus_field_x
    && !$send_doz_x
    && !$reset_search_x
    && !$add_term_field_x
    && !$delete_term_field_x
    && !$add_studg_x
    && !$delete_studg_x
    && !$search_doz
    && !$search_tut
    && !$search_room_x
    && !$reset_room_search_x
    && !$send_room_x
    && !$search_properties_x
    && !$send_room_type_x
    && !$reset_room_type_x
    && !$reset_resource_id_x
    && !$reset_admission_time_x
    && !$toggle_admission_quota_x) {
    $jump_next_x=TRUE;
}


//Check auf korrekte Eingabe und Sprung in naechste Level, hier auf Schritt 2
if (($form == 1) && ($jump_next_x))
    {
    if (($sem_create_data["sem_duration_time"]<0) && ($sem_create_data["sem_duration_time"] != -1))
        {
        $level=3;
        $errormsg=$errormsg."error�"._("Das Endsemester darf nicht vor dem Startsemester liegen. Bitte &auml;ndern Sie die entsprechenden Einstellungen!")."�";
        }
    if (strlen($sem_create_data["sem_name"]) <3)
        {
        $level=1; //wir bleiben auf der ersten Seite
        $errormsg=$errormsg."error�"._("Bitte geben Sie einen g&uuml;ltigen Namen f&uuml;r die Veranstaltung ein!")."�";
        }
     if ($sem_create_data["sem_start_time"] <0) 
        { 
        $level=1; //wir bleiben auf der ersten Seite 
        $errormsg=$errormsg."error�"._("Bitte geben Sie ein g�ltiges Semester f�r die Veranstaltung ein!")."�"; 
        }   
    if (!$sem_create_data["sem_inst_id"])
        {
        $level=1;
        $errormsg=$errormsg.sprintf ("error�"._("Da Ihr Account keiner Einrichtung zugeordnet ist, k&ouml;nnen Sie leider noch keine Veranstaltung anlegen. Bitte wenden Sie sich an den/die zust&auml;ndigeN AdministratorIn der Einrichtung oder einen der %sAdministratoren%s des Systems!")."�", "<a href=\"".URLHelper::getLink("dispatch.php/siteinfo/show")."\">", "</a>");
        }
    if (($sem_create_data["sem_turnout"] < 1) && ($sem_create_data["sem_admission"]))
        {
        $level=1;
        $errormsg=$errormsg."error�"._("Wenn Sie die Teilnahmebeschr&auml;nkung benutzen wollen, m&uuml;ssen Sie wenigstens einen Teilnehmer zulassen.")."�";
        $sem_create_data["sem_turnout"] =1;
        }
        if(!$sem_create_data["sem_admission"]){
            $sem_create_data["sem_admission_start_date"] = -1;
            $sem_create_data["sem_admission_end_date"] = -1;
        }

    if (!$errormsg)
        $level=2;
    }

// move Dozenten
if ($moveup_doz)
{
   $move_uid = get_userid($moveup_doz);
   $move_pos = $sem_create_data["sem_doz"][$move_uid];

   foreach($sem_create_data["sem_doz"] as $key=>$val)
   {
      if ($val == ($move_pos - 1))
      {
         $sem_create_data["sem_doz"][$key]      = $move_pos;
         $sem_create_data["sem_doz"][$move_uid] = $move_pos - 1;
      }
   }
    $level=2;
}
if ($movedown_doz)
{
   $move_uid = get_userid($movedown_doz);
   $move_pos = $sem_create_data["sem_doz"][$move_uid];

   foreach($sem_create_data["sem_doz"] as $key=>$val)
   {
      if ($val == ($move_pos + 1))
      {
         $sem_create_data["sem_doz"][$key]      = $move_pos;
         $sem_create_data["sem_doz"][$move_uid] = $move_pos + 1;
      }
   }
    $level=2;
}
// move Tutoren
if ($moveup_tut)
{
   $move_uid = get_userid($moveup_tut);
   $move_pos = $sem_create_data["sem_tut"][$move_uid];

   foreach($sem_create_data["sem_tut"] as $key=>$val)
   {
      if ($val == ($move_pos - 1))
      {
         $sem_create_data["sem_tut"][$key]      = $move_pos;
         $sem_create_data["sem_tut"][$move_uid] = $move_pos - 1;
      }
   }
    $level=2;
}
if ($movedown_tut)
{
   $move_uid = get_userid($movedown_tut);
   $move_pos = $sem_create_data["sem_tut"][$move_uid];

   foreach($sem_create_data["sem_tut"] as $key=>$val)
   {
      if ($val == ($move_pos + 1))
      {
         $sem_create_data["sem_tut"][$key]      = $move_pos;
         $sem_create_data["sem_tut"][$move_uid] = $move_pos + 1;
      }
   }
    $level=2;
}

//delete Tutoren/Dozenten
if ($delete_doz) {
  $position = $sem_create_data["sem_doz"][get_userid($delete_doz)];
  unset($sem_create_data["sem_doz"][get_userid($delete_doz)]);

  foreach($sem_create_data["sem_doz"] as $key => $val) {
    if ($val > $position) {
      $sem_create_data["sem_doz"][$key] -= 1;
    }
  }

  $level=2;
}

if ($delete_tut) {
  $position = $sem_create_data["sem_tut"][get_userid($delete_tut)];
  unset($sem_create_data["sem_tut"][get_userid($delete_tut)]);

  foreach($sem_create_data["sem_tut"] as $key => $val) {
    if ($val > $position) {
      $sem_create_data["sem_tut"][$key] -= 1;
    }
  }

  $level=2;
}

if (($send_doz_x) && (!$reset_search_x)) {
   $next_position = sizeof($sem_create_data["sem_doz"]) + 1;
    $sem_create_data["sem_doz"][get_userid($add_doz)]= $next_position;
    $level=2;
}

if (($send_tut_x) && (!$reset_search_x)) {
   $next_position = sizeof($sem_create_data["sem_tut"]) + 1;
    $sem_create_data["sem_tut"][get_userid($add_tut)]= $next_position;
    $level=2;
}

// delete user domain
if (isset($_REQUEST['delete_domain'])) {
    $index = array_search($_REQUEST['delete_domain'], $sem_create_data["sem_domain"]);
    unset($sem_create_data["sem_domain"][$index]);
}

if ($search_doz_x || $search_tut_x || $reset_search_x ||
    $sem_bereich_do_search_x ||
        isset($_REQUEST['add_domain_x']) || isset($_REQUEST['delete_domain']) ||
    $study_areas['add'] || $study_areas['remove'] ||
    $study_areas['showall_button'] || $study_areas['search_button'] ||
    $study_areas['search_key'] || $study_areas['selected'] ||
    $study_areas['rewind_button']) {

    $level=2;

}

elseif (($form == 2) && ($jump_next_x)) //wenn alles stimmt, Checks und Sprung auf Schritt 3
    {
    if (is_array($sem_create_data['sem_tut']))
        foreach ($sem_create_data['sem_tut'] as $key=>$val){
            if (array_key_exists($key, $sem_create_data['sem_doz']))
                $badly_dozent_is_tutor = TRUE;
        }
    if ($badly_dozent_is_tutor) {
        $level=2; //wir bleiben auf der zweiten Seite
        $errormsg=$errormsg."error�". sprintf(_("Sie d&uuml;rfen eine Person mit Status %s nicht gleichzeitig als %s eintragen!"), get_title_for_status('dozent', 1, $sem_create_data["sem_status"]), get_title_for_status('tutor', 1, $sem_create_data["sem_status"]))."�";
    }

    if (sizeof($sem_create_data["sem_doz"])==0)
        {
        $level=2; //wir bleiben auf der zweiten Seite
        $errormsg=$errormsg."error�". sprintf(_("Bitte geben Sie mindestens eine Person mit Status %s f&uuml;r die Veranstaltung an!"), get_title_for_status('dozent', 1, $sem_create_data["sem_status"]))."�";
        }
    elseif ((!$perm->have_perm("root")) && (!$perm->have_perm("admin")))
        {
        if (!array_key_exists($user_id, $sem_create_data['sem_doz'])) {
            $level=2;
            $errormsg=$errormsg."error�". sprintf(_("Sie m&uuml;ssen wenigstens sich selbst als %s f&uuml;r diese Veranstaltung angeben! Der Eintrag wird automatisch gesetzt."), get_title_for_status('dozent', 1, $sem_create_data["sem_status"]))."�";
            $sem_create_data['sem_doz'][$user_id]= count($sem_create_data['sem_doz']) + 1;
            }
        }
    if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"]) {
        if (sizeof($sem_create_data["sem_bereich"]) == 0) {
            $level=2;
            $errormsg = $errormsg . "error�" .
            _("Bitte geben Sie mindestens einen Studienbereich f&uuml;r die Veranstaltung an!")."�";
        } else if ($false_mark) {
            $level=2;
            $errormsg = $errormsg . "error�" .
              _("Sie haben eine oder mehrere Fach&uuml;berschriften (unterstrichen) ausgew&auml;hlt. Diese dienen nur der Orientierung und k&ouml;nnen nicht ausgew&auml;hlt werden!")."�";
            }
        }

    if (($sem_create_data["sem_sec_schreib"]) <($sem_create_data["sem_sec_lese"]))
        {
        $level=2; //wir bleiben auf der zweiten Seite
        $errormsg=$errormsg."error�"._("Es macht keinen Sinn, die Sicherheitsstufe f&uuml;r den Lesezugriff h&ouml;her zu setzen als f&uuml;r den Schreibzugriff!")."�";
        }
    if (!$errormsg) {
        if ($sem_create_data["term_art"]== -1) {
            $sem_create_data['skip_room_request'] = true;
            $level=5;
        } else {
            unset($sem_create_data['skip_room_request']);
            $level=3;
        }
    } else
        $level=2;
    }

//Felder fuer Standardtermine oder Blocktermine, Studiengaenge hinzufuegen/loeschen
if ($add_turnus_field_x)
    {
    $sem_create_data["turnus_count"]++;
    $level=3;
    }
if ($add_term_field_x)
    {
    $sem_create_data["term_count"]++;
    $level=3;
    }
if ($delete_turnus_field)
    {
    for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
        if ($i != ($delete_turnus_field-1))
            {
            $tmp_term_turnus_date[]=$sem_create_data["term_turnus_date"][$i];
            $tmp_term_turnus_start_stunde[]=$sem_create_data["term_turnus_start_stunde"][$i];
            $tmp_term_turnus_start_minute[]=$sem_create_data["term_turnus_start_minute"][$i];
            $tmp_term_turnus_end_stunde[]=$sem_create_data["term_turnus_end_stunde"][$i];
            $tmp_term_turnus_end_minute[]=$sem_create_data["term_turnus_end_minute"][$i];
            $tmp_term_turnus_resource_id[]=$sem_create_data["term_turnus_resource_id"][$i];
            $tmp_term_turnus_room[]=$sem_create_data["term_turnus_room"][$i];
            $tmp_term_turnus_desc[]=$sem_create_data["term_turnus_desc"][$i];
            }
    $sem_create_data["term_turnus_date"]=$tmp_term_turnus_date;
    $sem_create_data["term_turnus_start_stunde"]=$tmp_term_turnus_start_stunde;
    $sem_create_data["term_turnus_start_minute"]=$tmp_term_turnus_start_minute;
    $sem_create_data["term_turnus_end_stunde"]=$tmp_term_turnus_end_stunde;
    $sem_create_data["term_turnus_end_minute"]=$tmp_term_turnus_end_minute;
    $sem_create_data["term_turnus_resource_id"]=$tmp_term_turnus_resource_id;
    $sem_create_data["term_turnus_room"]=$tmp_term_turnus_room;
    $sem_create_data["term_turnus_desc"]=$tmp_term_turnus_desc;

    $sem_create_data["turnus_count"]--;
    $level=3;
    }
if ($delete_term_field)
    {
    for ($i=0; $i<$sem_create_data["term_count"]; $i++)
        if ($i != ($delete_term_field-1))
            {
            $tmp_term_tag[]=$sem_create_data["term_tag"][$i];
            $tmp_term_monat[]=$sem_create_data["term_monat"][$i];
            $tmp_term_jahr[]=$sem_create_data["term_jahr"][$i];
            $tmp_term_start_stunde[]=$sem_create_data["term_start_stunde"][$i];
            $tmp_term_start_minute[]=$sem_create_data["term_start_minute"][$i];
            $tmp_term_end_stunde[]=$sem_create_data["term_end_stunde"][$i];
            $tmp_term_end_minute[]=$sem_create_data["term_end_minute"][$i];
            $tmp_term_resource_id[]=$sem_create_data["term_resource_id"][$i];
            $tmp_term_room[]=$sem_create_data["term_room"][$i];
            }
    $sem_create_data["term_tag"]=$tmp_term_tag;
    $sem_create_data["term_monat"]=$tmp_term_monat;
    $sem_create_data["term_jahr"]=$tmp_term_jahr;
    $sem_create_data["term_start_stunde"]=$tmp_term_start_stunde;
    $sem_create_data["term_start_minute"]=$tmp_term_start_minute;
    $sem_create_data["term_end_stunde"]=$tmp_term_end_stunde;
    $sem_create_data["term_end_minute"]=$tmp_term_end_minute;
    $sem_create_data["term_resource_id"]=$tmp_term_resource_id;
    $sem_create_data["term_room"]=$tmp_term_room;

    $sem_create_data["term_count"]--;
    $level=3;
    }


//Termin-Metaddaten-Check, wenn alles stimmt, Sprung auf Schritt 4
if (($form == 3) && ($jump_next_x))
    {
    if ($sem_create_data["term_art"]==0)
        {
        for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
            if ((($sem_create_data["term_turnus_start_stunde"][$i] !== '') || ($sem_create_data["term_turnus_end_stunde"][$i] !== '')))
                {
                if (($sem_create_data["term_turnus_start_stunde"][$i] !== '') xor ($sem_create_data["term_turnus_end_stunde"][$i]) !== '')
                        {
                        if (!$just_informed)
                            $errormsg=$errormsg."error�"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der regul&auml;ren Termine aus!")."�";
                        $just_informed=TRUE;
                        }
                if ((($sem_create_data["term_turnus_start_stunde"][$i]>23) || ($sem_create_data["term_turnus_start_stunde"][$i]<0))  ||  (($sem_create_data["term_turnus_start_minute"][$i]>59) || ($sem_create_data["term_turnus_start_minute"][$i]<0))  ||  (($sem_create_data["term_turnus_end_stunde"][$i]>23) ||($sem_create_data["term_turnus_end_stunde"][$i]<0))  || (($sem_create_data["term_turnus_end_minute"][$i]>59) || ($sem_create_data["term_turnus_end_minute"][$i]<0)))
                        {
                        if (!$just_informed3)
                            $errormsg=$errormsg."error�"._("Sie haben eine ung&uuml;ltige Zeit eingegeben. Bitte korrigieren Sie dies!")."�";
                        $just_informed3=TRUE;
                        }
                if (mktime((int)$sem_create_data["term_turnus_start_stunde"][$i], (int)$sem_create_data["term_turnus_start_minute"][$i], 0, 1, 1, 2001) >= mktime((int)$sem_create_data["term_turnus_end_stunde"][$i], (int)$sem_create_data["term_turnus_end_minute"][$i], 0, 1, 1, 2001))
                    if ((!$just_informed5) && (!$just_informed)) {
                        $errormsg=$errormsg."error�"._("Der Endzeitpunkt eines regul&auml;ren Termins muss nach dem jeweiligen Startzeitpunkt liegen!")."�";
                        $just_informed5=TRUE;
                    }
                }
                elseif(!$just_informed4)
                    if (($sem_create_data["term_turnus_start_stunde"][$i] === '') && ($sem_create_data["term_turnus_start_minute"][$i] === '') && ($sem_create_data["term_turnus_end_stunde"][$i] === '') && ($sem_create_data["term_turnus_end_minute"][$i] === ''))
                        $empty_fields++;
                    else
                        {
                        $errormsg=$errormsg."error�"._("Sie haben nicht alle Felder der regul&auml;ren Termine ausgef&uuml;llt. Bitte f&uuml;llen Sie alle Felder aus!")."�";
                        $just_informed4=TRUE;
                        }
        }
    else {
        for ($i=0; $i<$sem_create_data["term_count"]; $i++)
            if ((($sem_create_data["term_start_stunde"][$i] !== '') || ($sem_create_data["term_end_stunde"][$i] !== '')) && (($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_jahr"][$i]))) {
                if (($sem_create_data["term_start_stunde"][$i] !== '') xor ($sem_create_data["term_end_stunde"][$i] !== ''))
                        {
                        if (!$just_informed)
                            $errormsg=$errormsg."error�"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der jeweiligen Termine aus!")."�";
                        $just_informed=TRUE;
                        }
                if (!checkdate ((int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i]))
                        {
                        if (!$just_informed2)
                            $errormsg=$errormsg."error�"._("Sie haben ein ung&uuml;ltiges Datum eingegeben. Bitte korrigieren Sie das Datum!")."�";
                        $just_informed2=TRUE;
                        }
                if ((($sem_create_data["term_start_stunde"][$i]>23) || ($sem_create_data["term_start_stunde"][$i]<0))  ||  (($sem_create_data["term_start_minute"][$i]>59) || ($sem_create_data["term_start_minute"][$i]<0))  ||  (($sem_create_data["term_end_stunde"][$i]>23) ||($sem_create_data["term_end_stunde"][$i]<0))  || (($sem_create_data["term_end_minute"][$i]>59) || ($sem_create_data["term_end_minute"][$i]<0)))
                        {
                        if (!$just_informed3)
                            $errormsg=$errormsg."error�"._("Sie haben eine ung&uuml;ltige Zeit eingegeben, bitte korrigieren Sie dies!")."�";
                        $just_informed3=TRUE;
                        }
                if (mktime((int)$sem_create_data["term_start_stunde"][$i], (int)$sem_create_data["term_start_minute"][$i], 0, 1, 1, 2001) > mktime((int)$sem_create_data["term_end_stunde"][$i], (int)$sem_create_data["term_end_minute"][$i], 0, 1, 1, 2001))
                    if ((!$just_informed5) && (!$just_informed)) {
                        $errormsg=$errormsg."error�"._("Der Endzeitpunkt der Termine muss nach dem jeweiligen Startzeitpunkt liegen!")."�";
                        $just_informed5=TRUE;
                    }
            }
            elseif(!$just_informed4)
                if (($sem_create_data["term_tag"][$i] === '') && ($sem_create_data["term_monat"][$i] === '') && ($sem_create_data["term_jahr"][$i] === '') && ($sem_create_data["term_start_stunde"][$i] === '') && ($sem_create_data["term_start_minute"][$i] === '') && ($sem_create_data["term_end_stunde"][$i] === '') && ($sem_create_data["term_end_minute"][$i] === ''))
                    $empty_fields++;
                else {
                    $errormsg=$errormsg."error�"._("Sie haben nicht alle Felder bei der Termineingabe ausgef&uuml;llt. Bitte f&uuml;llen Sie alle Felder aus!")."�";
                    $just_informed4=TRUE;
                    }
    }

    if ($sem_create_data["sem_vor_termin"] == -1);
    else {
        if ($vor_stunde xor $vor_end_stunde)
            $errormsg=$errormsg."error�"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der Vorbesprechung aus!")."�";

        //check for room management: we dont allow the preliminary discussion matches a turnus date (in this case, a schedule schoudl be used!)
        if ((!$sem_create_data["term_art"]) && ($RESOURCES_ENABLE)) {
            $sem_start_timestamp = veranstaltung_beginn_from_metadata($sem_create_data["term_art"],$sem_create_data["sem_start_time"],$sem_create_data['term_start_woche'],$sem_create_data['sem_start_termin'],$sem_create_data['metadata_termin']['turnus_data']);
            if ($sem_start_timestamp > 0 && $sem_create_data["sem_vor_termin"] >= $sem_start_timestamp){
                $tmp_vor_day = date("w", $sem_create_data["sem_vor_termin"]);
                if ($tmp_vor_day == 0)
                    $tmp_vor_day = 7;
                for ($i=0; $i<$sem_create_data["turnus_count"]; $i++) {
                    if (($sem_create_data["term_turnus_start_stunde"][$i] == $vor_stunde) &&
                        ($sem_create_data["term_turnus_end_stunde"][$i] == $vor_end_stunde) &&
                        ($sem_create_data["term_turnus_start_minute"][$i] == $vor_minute) &&
                        ($sem_create_data["term_turnus_end_minute"][$i] == $vor_end_minute) &&
                        ($sem_create_data["term_turnus_date"][$i] == $tmp_vor_day)){
                            $errormsg=$errormsg."error�"._("Der Termin f&uuml;r die Vorbesprechung findet zu den gleichen Zeiten wie die Veranstaltung statt. Bitte legen Sie in diesem Fall einen Ablaufplan in einem sp&auml;teren Schritt an und &auml;ndern einen Termin in den Typ \"Vorbesprechung\"")."�";
                            break;
                        }
                }
            }
        }
    }

    if (!$errormsg)
        $level=4;
    else
        $level=3;
    }

if (($search_room_x) ||($search_properties_x) || ($reset_room_search_x) || ($reset_room_type_x) || ($reset_resource_id_x)
    || ($send_room_x) || ($send_room_type_x)) {
    $level=4;
}

if (($form == 4) && ($jump_next_x)) {
    //checks for room-request
    if (is_object($sem_create_data["resRequest"])) {
        if (($sem_create_data["resRequest"]->getSettedPropertiesCount() === 0)
        && ($sem_create_data["resRequest"]->getResourceId() === NULL)
        && (!(get_config('RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST') && $sem_create_data['skip_room_request']))) {
            $errormsg.="error�"._("Die Anfrage konnte nicht gespeichert werden, da Sie mindestens einen Raumwunsch oder eine gew&uuml;nschte Eigenschaft (z.B. Anzahl der Sitzpl&auml;tze) angeben m&uuml;ssen!");
            if(get_config('RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST')){
                $errormsg.="�info�"._("Wenn Sie keinen Raum ben&ouml;tigen, aktivieren Sie die entsprechende Option. Die freien Angaben zu R&auml;umen werden auch ohne Raumwunsch gespeichert.");
            }
            $dont_anchor = TRUE;
        }
    }

    //checks for direct ressources-assign
    if ($sem_create_data["term_art"]==0) {
        for ($i=0; $i<$sem_create_data["turnus_count"]; $i++) {
            //check overlaps...
            if ($RESOURCES_ENABLE) {
                $checkResult = $resAssign->changeMetaAssigns($sem_create_data["metadata_termin"], $sem_create_data["sem_start_time"], $sem_create_data["sem_duration_time"],TRUE);
            }
        }
    } else {
        for ($i=0; $i<$sem_create_data["term_count"]; $i++) {
            //check overlaps
            if ((!$errormsg) && ($RESOURCES_ENABLE)) {
                $tmp_chk_date=mktime((int)$sem_create_data["term_start_stunde"][$i], (int)$sem_create_data["term_start_minute"][$i], 0, (int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i]);
                $tmp_chk_end_time=mktime((int)$sem_create_data["term_end_stunde"][$i], (int)$sem_create_data["term_end_minute"][$i], 0, (int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i]);
                $checkResult = array_merge((array)$checkResult, (array)$resAssign->insertDateAssign(FALSE, $sem_create_data["term_resource_id"][$i], $tmp_chk_date, $tmp_chk_end_time, TRUE));
            }
        }
    }

    if ($sem_create_data["sem_vor_termin"] == -1);
    else {
        //check overlaps...
        if ($RESOURCES_ENABLE) {
            $checkResult = array_merge((array)$checkResult, (array)$resAssign->insertDateAssign(FALSE, $sem_create_data["sem_vor_resource_id"], $sem_create_data["sem_vor_termin"], $sem_create_data["sem_vor_end_termin"],TRUE));
        }
    }

    //generate bad messages
    if ($RESOURCES_ENABLE) {
        $errormsg.=getFormattedResult($checkResult);
    }

    if (!$errormsg)
        $level=5;
    else
        $level=4;
}


//Neuen Studiengang zur Begrenzung aufnehmen
if ($add_studg_x) {
    if ($sem_add_studg && $sem_add_studg != 'all') {
        $db->query("SELECT name FROM studiengaenge WHERE studiengang_id='".$sem_add_studg."' ");
        $db->next_record();
        $sem_create_data["sem_studg"][$sem_add_studg]=array("name"=>$db->f("name"), "ratio"=>(int)$sem_add_ratio);
    } else if ($sem_add_studg == 'all'){
        $sem_create_data["sem_studg"][$sem_add_studg]=array("name"=>_("Alle Studieng�nge"), "ratio"=>(int)$sem_add_ratio);
    }
    $level=5;
}

if(isset($_REQUEST['reset_admission_time_x'])) {
    $sem_create_data["sem_admission_end_date"] = -1;
    $sem_create_data["sem_admission_start_date"] = -1;
    $level = 5;
}

if(isset($_REQUEST['toggle_admission_quota_x'])) {
    $level = 5;
}
//Studiengang zur Begrenzung loeschen
if ($sem_delete_studg) {
    unset($sem_create_data["sem_studg"][$sem_delete_studg]);
    $level=5;
    if(!count($sem_create_data["sem_studg"])){
         $sem_create_data["sem_studg"]['all'] = array('name' => _("Alle Studieng�nge"), 'ratio' => 100);
    }
}

//Prozentangabe checken/berechnen wenn neuer Studiengang, einer geloescht oder Seite abgeschickt
if ((($form == 5) && ($jump_next_x)) || ($add_studg_x) || ($sem_delete_studg) || $toggle_admission_quota_x) {
    if ($sem_create_data["sem_admission"] && $sem_create_data["admission_enable_quota"]) {
        if (!$sem_create_data["sem_admission_ratios_changed"] && (($add_studg_x && !$sem_add_ratio && $sem_add_studg) || $sem_delete_studg || $toggle_admission_quota_x)) {//User hat nichts veraendert und neuen Studiengang ohne Wert geschickt oder studiengang gel�scht, wir versuchen automatisch zu rechnen
            if (is_array($sem_create_data["sem_studg"])){
                $ratio = round(100 / (sizeof ($sem_create_data["sem_studg"]) ));
                foreach ($sem_create_data["sem_studg"] as $key=>$val){
                    $sem_create_data["sem_studg"][$key]["ratio"] = $ratio;
                    $cnt += $ratio;
                }
                if($cnt < 100){ //letzten Studiengang auff�llen, wg. evtl. Rundungsfehler
                    $sem_create_data["sem_studg"][$key]["ratio"] = (100 - $cnt + $ratio);
                }
            }
        }
        if (is_array($sem_create_data["sem_studg"])){
            $cnt = 0;
            if(count($sem_create_data["sem_studg"]) > 1){
                foreach ($sem_create_data["sem_studg"] as $key => $val){
                    $cnt += $val["ratio"];
                }
                if ($cnt <= 100 && $sem_create_data["sem_admission_ratios_changed"] && $add_studg_x && $sem_add_studg && !$sem_add_ratio){
                    $sem_create_data["sem_studg"][$key]["ratio"] = (100 - $cnt + $val["ratio"]);
                    $cnt = 100;
                }
            } else {
                reset($sem_create_data["sem_studg"]);
                $cnt = $sem_create_data["sem_studg"][key($sem_create_data["sem_studg"])]["ratio"];
            }
        }
        if($cnt > 100){
            $errormsg.= "error�". _("Die Summe der Kontigente �bersteigt 100%. Bitte �ndern Sie die Kontigente!") . "�";
            $level=5;
        }
        if($cnt < 100){
            $errormsg.= "error�". _("Die Summe der Kontigente liegt unter 100%. Bitte �ndern Sie die Kontigente!") . "�";
            $level=5;
        }
    }
}

//wenn alles stimmt, Sprung auf Schritt 5 (Anlegen)
if (($form == 5) && ($jump_next_x))
    {
    if (($sem_create_data["sem_sec_lese"] ==2) ||  ($sem_create_data["sem_sec_schreib"] ==2))
        {
            //Password bei Bedarf dann doch noch verschlusseln
            if (!$sem_passwd)
                $sem_create_data["sem_pw"] = "";
            elseif($sem_passwd != "*******") {
                $sem_create_data["sem_pw"] = md5($sem_passwd);
                if($sem_passwd2 != "*******")
                    $check_pw = md5($sem_passwd2);
            }

        if (($sem_create_data["sem_pw"]=="") || ($sem_create_data["sem_pw"] == md5("")))
                {
                $errormsg=$errormsg."error�"._("Sie haben kein Passwort eingegeben! Bitte geben Sie ein Passwort ein!")."�";
                $level=5;
                }
              elseif (isset($check_pw) AND $sem_create_data["sem_pw"] != $check_pw)
                {
            $errormsg=$errormsg."error�"._("Das eingegebene Passwort und das Passwort zur Best&auml;tigung stimmen nicht &uuml;berein!")."�";
                $sem_create_data["sem_pw"] = "";
                $level=5;
                }
    }

    //Ende der Anmeldung checken
    if ($sem_create_data["sem_admission"]) {
        if ($sem_create_data["sem_admission_date"] == -1)
            if ($sem_create_data["sem_admission"] == 1)
                $errormsg.= "error�"._("Bitte geben Sie einen Termin f&uuml;r das Losdatum an!")."�";
            elseif ($sem_create_data["sem_admission"] == 2 && $sem_create_data["admission_enable_quota"] == 1)
                $errormsg.= "error�"._("Bitte geben Sie einen Termin f&uuml;r das Enddatum der Kontingentierung an!")."�";
        elseif ($sem_create_data["term_art"]==0){
            $tmp_first_date=veranstaltung_beginn_from_metadata ($sem_create_data["term_art"], $sem_create_data["sem_start_time"], $sem_create_data["term_start_woche"], $sem_create_data["sem_start_termin"], $sem_create_data["metadata_termin"]["turnus_data"], "int");
            if (($sem_create_data["sem_admission_date"] > $tmp_first_date) && ($tmp_first_date >0)){
                if ($tmp_first_date > 0)
                    if ($sem_create_data["sem_admission"] == 1)
                        $errormsg.= sprintf ("error�"._("Das Losdatum liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern Sie das Losdatum!")."�", date ("d.m.Y", $tmp_first_date));
                    else
                        $errormsg.= sprintf ("error�"._("Das Enddatum der Kontingentierung liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern Sie das Enddatum der Kontingentierung!")."�", date ("d.m.Y", $tmp_first_date));
                $level=5;
            }
        } elseif (($sem_create_data["sem_admission_date"] > $sem_create_data["term_first_date"]) && ($sem_create_data["term_first_date"])) {
                if ($sem_create_data["sem_admission"] == 1)
                    $errormsg.=sprintf ("error�"._("Das Losdatum liegt nach dem eingetragenen Veranstaltungsbeginn am %s. Bitte &auml;ndern Sie das Losdatum!")."�", date ("d.m.Y", $sem_create_data["term_first_date"]));
                else
                    $errormsg.=sprintf ("error�"._("Das Enddatum der Kontingentierung liegt nach dem eingetragenen Veranstaltungsbeginn am %s. Bitte &auml;ndern Sie das Enddatum der Kontingentierung!")."�", date ("d.m.Y", $sem_create_data["term_first_date"]));
                $level=5;
        }
        if (($sem_create_data["sem_admission_date"] < time()) && ($sem_create_data["sem_admission_date"] != -1)) {
                if ($sem_create_data["sem_admission"] == 1)
                    $errormsg.=sprintf ("error�"._("Das Losdatum liegt in der Vergangenheit. Bitte &auml;ndern sie das Losdatum!")."�");
                else
                    $errormsg.=sprintf ("error�"._("Das Enddatum der Kontingentierung liegt in der Vergangenheit. Bitte &auml;ndern Sie das Enddatum der Kontingentierung!")."�");
                $level=5;
        } elseif (($sem_create_data["sem_admission_date"] < (time() + (24 * 60 *60))) && ($sem_create_data["sem_admission_date"] != -1)) {
                if ($sem_create_data["sem_admission"] == 1)
                    $errormsg.=sprintf ("error�"._("Das Losdatum liegt zu nah am aktuellen Datum. Bitte &auml;ndern Sie das Losdatum!")."�");
                else
                    $errormsg.=sprintf ("error�"._("Das Enddatum der Kontingentierung liegt zu nah am aktuellen Datum. Bitte &auml;ndern sie das Enddatum der Kontingentierung!")."�");
                $level=5;
        }
    }

    //Erster Termin wenn angegeben werden soll muss er auch da sein
    if (($sem_create_data["sem_start_termin"] == -1) && ($sem_create_data["term_start_woche"] ==-1))
        $errormsg=$errormsg."error�"._("Bitte geben Sie einen ersten Termin an!")."�";
    else
        if ((($stunde) && (!$end_stunde)) || ((!$stunde) && ($end_stunde)))
            $errormsg=$errormsg."error�"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit des ersten Termins aus!")."�";
    //create overlap array
    if (is_array($checkResult)) {
        $overlaps_detected=FALSE;
        foreach ($checkResult as $key=>$val)
            if ($val["overlap_assigns"] == TRUE)
                    $overlaps_detected[] = array("resource_id"=>$val["resource_id"], "overlap_assigns"=>$val["overlap_assigns"]);
    }

    //generate bad msg if overlaps exists
    if ($overlaps_detected) {
        $errormsg=$errormsg."error�"._("Folgende gew&uuml;nschte Raumbelegungen &uuml;berschneiden sich mit bereits vorhandenen Belegungen. Bitte &auml;ndern Sie die R&auml;ume oder Zeiten!");
        $i=0;
        foreach ($overlaps_detected as $val) {
            $resObj = ResourceObject::Factory($val["resource_id"]);
            $errormsg.="<br><font size=\"-1\" color=\"black\">".htmlReady($resObj->getName()).": ";
            //show the first overlap
            list(, $val2) = each($val["overlap_assigns"]);
            $errormsg.=date("d.m, H:i",$val2["begin"])." - ".date("H:i",$val2["end"]);
            if (sizeof($val) >1)
                $errormsg.=", ... ("._("und weitere").")";
            $errormsg.=", ".$resObj->getFormattedLink($val2["begin"], _("Raumplan anzeigen"));
            $i++;
        }
        $errormsg.="</font>�";
        unset($overlaps_detected);
    }

    if (!$errormsg)
        $level=6;
    else
        $level=5;
    }

//OK, nun wird es ernst, wir legen das Seminar an.
if (($form == 6) && ($jump_next_x))
    {
    $run = TRUE;

    //Rechte ueberpruefen
    if (!$perm->have_perm("dozent"))
        {
        $errormsg .= "error�"._("Sie haben keine Berechtigung Veranstaltungen anzulegen Um eine Veranstaltung anlegen zu k&ouml;nnen, ben&ouml;tigen Sie mindestens den globalen Status &raquo;Dozent&laquo;. Bitte kontaktieren Sie den/die f&uuml;r Sie zust&auml;ndigeN AdministratorIn.")."�";
        $run = FALSE;
        }
    if (!$perm->have_studip_perm("dozent",$sem_create_data["sem_inst_id"]))
        {
        $errormsg .= "error�"._("Sie haben keine Berechtigung f&uuml;r diese Einrichtung Veranstaltungen anzulegen.")."�";
            $run = FALSE;
        }

    //Nochmal Checken, ob wirklich alle Daten vorliegen. Kann zwar eigentlich hier nicht mehr vorkommen, aber sicher ist sicher.
    if (empty($sem_create_data["sem_name"]))
        {
        $errormsg  .= "error�"._("Bitte geben Sie einen Namen f&uuml;r die Veranstaltung ein!")."�";
        $run = FALSE;
            }

    if (empty($sem_create_data["sem_inst_id"]))
        {
        $errormsg .= "error�"._("Bitte geben Sie eine Heimat-Einrichtung f&uuml;r die Veranstaltung an!")."�";
        $run = FALSE;
        }
    if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])  {
        if (empty($sem_create_data["sem_bereich"]))
            {
            $errormsg .= "error�"._("Bitte geben Sie wenigstens einen Studienbereich f&uuml;r die Veranstaltung an!")."�";
            $run = FALSE;
            }
        }

        if ($perm->have_perm("admin") && empty($sem_create_data["sem_doz"]))
            {
            $errormsg .= "error�"._("Bitte geben Sie wenigstens eine Dozentin oder einen Dozenten f&uuml;r die Veranstaltung an!")."�";
            $run = FALSE;
            }

    if ($run) {
        //Termin-Metadaten-Array zusammenmatschen zum besseren speichern in der Datenbank
        if ($sem_create_data['term_art'] == -1) {
            $sem_create_data['metadata_termin'] = array();
            $sem_create_data['metadata_termin']['art'] = 1;

            $sem_create_data["term_monat"]='';
            $sem_create_data["term_jahr"]='';
            $sem_create_data["term_start_stunde"]='';
            $sem_create_data["term_start_minute"]='';
            $sem_create_data["term_end_stunde"]='';
            $sem_create_data["term_end_minute"]='';
            $sem_create_data["term_room"]='';
            $sem_create_data["term_count"]= 0;
            $sem_create_data["term_first_date"]='';

            $sem_create_data["term_turnus"] = '';
            $sem_create_data["term_start_woche"] = '';
            $sem_create_data["sem_start_termin"] = '';
            $sem_create_data["turnus_count"] = 0;

            $sem_create_data["term_turnus_start_stunde"] = '';
            $sem_create_data["term_turnus_start_minute"] = '';
            $sem_create_data["term_turnus_end_stunde"] = '';
            $sem_create_data["term_turnus_end_minute"] = '';
            $sem_create_data["term_turnus_resource_id"] = '';
            $sem_create_data["term_turnus_room"] = '';
            $sem_create_data["term_turnus_date"] = '';
            $sem_create_data["term_turnus_desc"] = '';

            //set temporary entry (for skip dates field) to the right valuem_create_data["term_tag"]='';
            $sem_create_data['term_art'] = 1;
        } else if ($sem_create_data['term_art'] == 1) {
            $sem_create_data["term_turnus"] = '';
            $sem_create_data["term_start_woche"] = '';
            $sem_create_data["sem_start_termin"] = '';
            $sem_create_data["turnus_count"] = 0;
        }
        $serialized_metadata = mysql_escape_string(serialize($sem_create_data['metadata_termin']));

        //for admission it have to always 3
        if ($sem_create_data["sem_admission"]) {
            $sem_create_data["sem_sec_lese"]=3;
            $sem_create_data["sem_sec_schreib"]=3;
        }



        if ($Schreibzugriff < $Lesezugriff) // hier wusste ein Dozent nicht, was er tat
            $Schreibzugriff = $Lesezugriff;

        //Visibility
        if ($SEM_CLASS[$sem_create_data["sem_class"]]["visible"] !== FALSE)
            $visible = TRUE;
        else
            $visible = FALSE;

        $sem = new Seminar();

        // MetaDate erstellen
        $sem->metadate = new MetaDate();
        if ($sem_create_data['metadata_termin']['art'] == 0)  {
            if(is_array($sem_create_data["metadata_termin"]["turnus_data"])){
                foreach ($sem_create_data["metadata_termin"]["turnus_data"] as $key=>$val) {
                    $sem_create_data["metadata_termin"]["turnus_data"][$key]["room"] = stripslashes($sem_create_data["metadata_termin"]["turnus_data"][$key]["room"]);
                    $sem_create_data["metadata_termin"]["turnus_data"][$key]["desc"] = stripslashes($sem_create_data["metadata_termin"]["turnus_data"][$key]["desc"]);
                }
            }
            $sem->metadate->createMetaDateFromArray($sem_create_data['metadata_termin']);
        }   else {
            if ($sem_create_data['term_count'] > 0)
            for ($i = 0; $i < $sem_create_data['term_count']; $i++) {
                $termin = new SingleDate(array('seminar_id' => $sem->getId()));
                $new_date = array('start' => 0, 'end' => 0);
                if (check_and_set_date($sem_create_data['term_tag'][$i], $sem_create_data['term_monat'][$i], $sem_create_data['term_jahr'][$i], 
                    $sem_create_data['term_start_stunde'][$i], $sem_create_data['term_start_minute'][$i], $new_date, 'start') && 
                    check_and_set_date($sem_create_data['term_tag'][$i], $sem_create_data['term_monat'][$i], $sem_create_data['term_jahr'][$i], 
                    $sem_create_data['term_end_stunde'][$i], $sem_create_data['term_end_minute'][$i], $new_date, 'end')) 
                {
                    $termin->setTime($new_date['start'], $new_date['end']);

                    if ($sem_create_data['term_resource_id'][$i]) {
                        $termin->bookRoom($sem_create_data['term_resource_id'][$i]);
                    } else if ($sem_create_data['term_room'][$i]){
                        $termin->setFreeRoomText(stripslashes($sem_create_data['term_room'][$i]));
                    }
                    if ($termin->validate()) {
                        $sem->addSingleDate($termin);
                    }
                    unset($termin);
                }
            }
        }
        $sem->metadate->setSeminarStartTime($sem_create_data['sem_start_time']);
        $sem->metadate->setSeminarDurationTime($sem_create_data['sem_duration_time']);
        $sem->metadate->seminar_id = $sem->getId();

        $sem->semester_start_time = $sem_create_data['sem_start_time'];
        $sem->semester_duration_time = $sem_create_data['sem_duration_time'];
        $sem->seminar_number = stripslashes($sem_create_data['sem_nummer']);
        $sem->institut_id = $sem_create_data['sem_inst_id'];
        $sem->name = stripslashes($sem_create_data['sem_name']);
        $sem->subtitle = stripslashes($sem_create_data['sem_untert']);
        $sem->status = $sem_create_data['sem_status'];
        $sem->description = stripslashes($sem_create_data['sem_desc']);
        $sem->location = stripslashes($sem_create_data['sem_room']);
        $sem->misc = stripslashes($sem_create_data['sem_sonst']);
        $sem->password = $sem_create_data['sem_pw'];
        $sem->read_level = $sem_create_data['sem_sec_lese'];
        $sem->write_level = $sem_create_data['sem_sec_schreib'];
        $sem->form = stripslashes($sem_create_data['sem_art']);
        $sem->participants = stripslashes($sem_create_data['sem_teiln']);
        $sem->requirements = stripslashes($sem_create_data['sem_voraus']);
        $sem->orga = stripslashes($sem_create_data['sem_orga']);
        $sem->leistungsnachweis = stripslashes($sem_create_data['sem_leistnw']);
        $sem->ects = stripslashes($sem_create_data['sem_ects']);
        $sem->admission_endtime = $sem_create_data['sem_admission_date'];
        $sem->admission_turnout = $sem_create_data['sem_turnout'];
        $sem->admission_type = $sem_create_data['sem_admission'];
        $sem->admission_prelim = $sem_create_data['sem_payment'];
        $sem->admission_prelim_txt = stripslashes($sem_create_data['sem_paytxt']);
        $sem->admission_starttime = $sem_create_data['sem_admission_start_date'];
        $sem->admission_endtime_sem = $sem_create_data['sem_admission_end_date'];
        $sem->admission_enable_quota = $sem_create_data['admission_enable_quota'];
        $sem->visible = (($visible) ? '1' : '0');
        $sem->showscore = '0';
        $sem->modules = $sem_create_data['sem_modules'];

        $sem->user_number = ($sem_create_data['user_number']) ? '1' : '0';

        $sem_create_data["sem_id"] = $sem->getId();

        // Raumanfrage erzeugen
        if ($RESOURCES_ENABLE && $RESOURCES_ALLOW_ROOM_REQUESTS) {
            if (!$sem_create_data['skip_room_request']) {
                $sem_create_data['resRequest']->setSeminarId($sem->getId());
                $sem_create_data['resRequest']->store();
            }
        }

        // logging
        log_event("SEM_CREATE",$sem_create_data['sem_id'],NULL,NULL,$query);
        log_event(($visible ? "SEM_VISIBLE" : "SEM_INVISIBLE"), $sem_create_data['sem_id'],NULL,NULL,'admin_seminare_assi',"SYSTEM");


        // create singledates for the regular entrys
        if (!$sem_create_data["sem_entry"]) {
            foreach ($sem->getMetaDates() as $key => $val) {
                $sem->metadate->createSingleDates($key);

                // Raum buchen, wenn eine Angabe gemacht wurde, oder Freitextangabe, falls vorhanden
                if (($val['resource_id'] != '') || ($val['room'] != '')) {
                    $singleDates =& $sem->getSingleDatesForCycle($key);
                    foreach ($singleDates as $sd_key => $sd_val) {
                        if ($RESOURCES_ENABLE && $val['resource_id'] != '') {
                            $singleDates[$sd_key]->bookRoom($val['resource_id']);
                            if ($msg = $singleDates[$sd_key]->getMessages()) {
                                $errormsg .= $msg;
                            }
                        } else {
                            $singleDates[$sd_key]->setFreeRoomText($val['room']);
                            $singleDates[$sd_key]->store();
                        }
                    }
                }

            }

            // Speichern der Veranstaltungsdaten -> anlegen des Seminars
            $sem->store();

            // speichere die Nutzerdom�nen f�r das neue Seminar
            $count_doms = 0;
            foreach ($sem_create_data["sem_domain"] as $domain_id){
                $domain = new UserDomain($domain_id);
                $domain->addSeminar($sem->id);
                $count_doms ++;
            }

            //completing the internal settings....
            $successful_entry=1;
            $sem_create_data["sem_entry"]=TRUE;

            // Logging
            log_event("SEM_CREATE",$sem_create_data['sem_id'],NULL,NULL,$query);
            log_event(($visible ? "SEM_VISIBLE" : "SEM_INVISIBLE"), $sem_create_data['sem_id'],NULL,NULL,'admin_seminare_assi',"SYSTEM");
            $links_admin_data["referred_from"]="assi";
            $links_admin_data["assi"]=FALSE; //protected Assi-mode off

            if (!array_key_exists('sem_modules', $sem_create_data)){
                //write the default module-config
                $Modules = new Modules();
                $Modules->writeDefaultStatus($sem_create_data["sem_id"]);
            }
            //$Modules->writeStatus("scm", $sem_create_data["sem_id"], FALSE); //the scm has to be turned off, because an empty free informations page isn't funny

            if (is_array($sem_create_data["sem_doz"]))  // alle ausgew�hlten Dozenten durchlaufen
            {
                $self_included = FALSE;
                $count_doz=0;
                foreach ($sem_create_data["sem_doz"] as $key=>$val)
                {
                    $group=select_group($sem_create_data["sem_start_time"]);

                    if ($key == $user_id)
                        $self_included=TRUE;

               $next_pos = get_next_position("dozent",$sem_create_data["sem_id"]);

                    $query = "insert into seminar_user SET Seminar_id = '".
                    $sem_create_data["sem_id"]."', user_id = '".
                    $key."', status = 'dozent', gruppe = '$group', visible = 'yes',".
                    " mkdate = '".time()."', position = '$next_pos'";
                    $db3->query($query);// Dozenten eintragen:w

                    if ($db3->affected_rows() >=1)
                        $count_doz++;
                }
            }

            if (!$perm->have_perm("admin") && !$self_included) // wenn nicht admin, aktuellen Dozenten eintragen
            {
                $group=select_group($sem_create_data["sem_start_time"]);

                $next_pos = get_next_position("dozent",$sem_create_data["sem_id"]);

                $query = "insert into seminar_user SET Seminar_id = '".
                    $sem_create_data["sem_id"]."', user_id = '".
                    $user_id."', status = 'dozent', gruppe = '$group', mkdate = '".time()."', position = '$next_pos'";
                $db3->query($query);
                if ($db3->affected_rows() >=1)
                    $count_doz++;
            }

            if (is_array($sem_create_data["sem_tut"]))  // alle ausgew�hlten Tutoren durchlaufen
            {
                $count_tut=0;
                foreach ($sem_create_data["sem_tut"] as $key=>$val)
                {
                    $group=select_group($sem_create_data["sem_start_time"]);

                    $query = "SELECT user_id FROM seminar_user WHERE Seminar_id = '".
                        $sem_create_data["sem_id"]."' AND user_id ='$key'";
                    $db4->query($query);
                    if ($db4->next_record())    // User schon da, kann beim Anlegen nur als Dozent sein, also ignorieren
                        ;
                    else // User noch nicht da
                        {
                  $next_pos = get_next_position("tutor",$sem_create_data["sem_id"]);
                        $query = "insert into seminar_user SET Seminar_id = '".
                            $sem_create_data["sem_id"]."', user_id = '".
                            $key."', status = 'tutor', gruppe = '$group', mkdate = '".time()."', position = '$next_pos', visible='yes'";
                        $db3->query($query);                 // Tutor eintragen
                            if ($db3->affected_rows() >= 1)
                                $count_tut++;
                        }
                    }
                }

            //Eintrag der Studienbereiche
            if (is_array($sem_create_data["sem_bereich"])) {
                $seminar = Seminar::getInstance($sem_create_data["sem_id"]);
                $seminar->setStudyAreas($sem_create_data["sem_bereich"]);
                $count_bereich = sizeof($sem_create_data["sem_bereich"]);
                }

            //Eintrag der zugelassen Studieng�nge
            if ($sem_create_data["sem_admission"]) {
                if (is_array($sem_create_data["sem_studg"])){
                    foreach($sem_create_data["sem_studg"] as $key=>$val){
                        $query = "INSERT INTO admission_seminar_studiengang VALUES('".$sem_create_data["sem_id"]."', '$key', '".$val["ratio"]."' )";
                        $db3->query($query);// Studiengang eintragen
                    }
                }
            }

            //Eintrag der beteiligten Institute
            if (is_array($sem_create_data["sem_bet_inst"])>0)
                {
                $count_bet_inst=0;
                foreach ($sem_create_data["sem_bet_inst"] as $tmp_array) //Alle beteiligten Institute durchlaufen
                    {
                    $query = "INSERT INTO seminar_inst VALUES('".$sem_create_data["sem_id"]."', '$tmp_array')";
                    $db3->query($query);// Institut eintragen
                    if ($db3->affected_rows() >= 1)
                        $count_bet_inst++;
                    }
                }

            //Heimat-Institut ebenfalls eintragen, wenn noch nicht da
            $query = "INSERT IGNORE INTO seminar_inst values('".$sem_create_data["sem_id"]."', '".$sem_create_data["sem_inst_id"]."')";
            $db3->query($query);

            //Standard Thema im Forum anlegen, damit Studis auch ohne Zutun des Dozenten diskutieren koennen
            if ($sem_create_data["modules_list"]["forum"])
                CreateTopic(_("Allgemeine Diskussionen"), get_fullname($user_id), _("Hier ist Raum f�r allgemeine Diskussionen"), 0, 0, $sem_create_data["sem_id"]);

            //Standard Ordner im Foldersystem anlegen, damit Studis auch ohne Zutun des Dozenten Uploaden k&ouml;nnen
            if ($sem_create_data["modules_list"]["documents"])
                $db3->query("INSERT INTO folder SET folder_id='".md5(uniqid("sommervogel"))."', range_id='".$sem_create_data["sem_id"]."', user_id='".$user_id."', name='"._("Allgemeiner Dateiordner")."', description='"._("Ablage f�r allgemeine Ordner und Dokumente der Veranstaltung")."', mkdate='".time()."', chdate='".time()."'");

            //Vorbesprechung, falls vorhanden, in Termintabelle eintragen
            if ($sem_create_data["sem_vor_termin"] <>-1) {
                $vorbesprechung = new SingleDate(array('seminar_id' => $sem->getId()));
                $vorbesprechung->setTime($sem_create_data['sem_vor_termin'], $sem_create_data['sem_vor_end_termin']);
                foreach ($TERMIN_TYP as $key => $val) {
                    if ($val['name'] == 'Vorbesprechung') {
                        $vorbesprechung->setDateType($key);
                    }
                }
                if ($sem_create_data['sem_vor_resource_id']) {
                    $vorbesprechung->bookRoom($sem_create_data['sem_vor_resource_id']);
                } else {
                    $vorbesprechung->setFreeRoomText(stripslashes($sem_create_data['sem_vor_raum']));
                }

                $issue = new Issue(array('seminar_id' => $sem->getId()));
                $issue->setTitle('Vorbesprechung');
                $issue->store();

                $vorbesprechung->addIssueId($issue->getIssueId());
                $vorbesprechung->store();

                /*//update/insert the assigned roomes
                if ($RESOURCES_ENABLE && $db->affected_rows()) {
                    $updateAssign = new VeranstaltungResourcesAssign($sem_create_data["sem_id"]);
                    $updateResult = array_merge((array)$updateResult, (array)$updateAssign->insertDateAssign($termin_id, $sem_create_data["sem_vor_resource_id"]));
                }

                //create a request
                if (!$sem_create_data['skip_room_request'] && is_object($sem_create_data["resRequest"])) {
                    $sem_create_data["resRequest"]->copy();
                    $sem_create_data["resRequest"]->setSeminarId($sem_create_data["sem_id"]);
                    $sem_create_data["resRequest"]->setTerminId($termin_id);
                    $sem_create_data["resRequest"]->store();
                    $sem_create_data["resRequest"]->checkOpen(true);
                }*/
            }

            //Wenn der Veranstaltungs-Termintyp Blockseminar ist, dann tragen wir diese Termine auch schon mal ein
            /* hmmm, nochmal?
            if ($sem_create_data["term_art"] ==1) {
                for ($i=0; $i<$sem_create_data["term_count"]; $i++)
                    if (($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_jahr"][$i]) && ($sem_create_data["term_start_stunde"][$i] !== '') && ($sem_create_data["term_end_stunde"][$i] !== '')) {
                        $termin_id=md5(uniqid(rand()));
                        $mkdate=time();
                        $date=mktime((int)$sem_create_data["term_start_stunde"][$i], (int)$sem_create_data["term_start_minute"][$i], 0, (int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i]);
                        $end_time=mktime((int)$sem_create_data["term_end_stunde"][$i], (int)$sem_create_data["term_end_minute"][$i], 0, (int)$sem_create_data["term_monat"][$i], (int)$sem_create_data["term_tag"][$i], (int)$sem_create_data["term_jahr"][$i]);

                        //if we have a resource_id, we flush the room name
                        if ($sem_create_data["term_resource_id"][$i])
                            $sem_create_data["term_room"][$i]='';

                        $db->query("INSERT INTO termine SET termin_id = '$termin_id', range_id='".$sem_create_data["sem_id"]."', autor_id='$user_id', content ='".($i+1).". Seminartermin (ohne Titel)', date='$date', mkdate='$mkdate', chdate='$mkdate', date_typ='1', end_time='$end_time', raum='".$sem_create_data["term_room"][$i]."' ");

                        //update/insert the assigned roomes
                        if ($RESOURCES_ENABLE && $db->affected_rows()) {
                            $updateAssign = new VeranstaltungResourcesAssign($sem_create_data["sem_id"]);
                            $updateResult = array_merge((array)$updateResult,(array)$updateAssign->insertDateAssign($termin_id, $sem_create_data["term_resource_id"][$i]));
                        }
                    }
            }
            */
            //Store the additional datafields
            if (is_array($sem_create_data["sem_datafields"])) {
                foreach ($sem_create_data['sem_datafields'] as $id=>$val) {
                    $struct = new DataFieldStructure(array("datafield_id"=>$id, 'type'=>$val['type'], 'name'=>$val['name']));
                    $entry  = DataFieldEntry::createDataFieldEntry($struct, $sem_create_data['sem_id'], $val['value']);
                    if ($entry->isValid())
                        $entry->store();
                    else
                        $errormsg .= "error�" . sprintf(_("Fehlerhafte Eingabe im Feld '%s': %s (Eintrag wurde nicht gespeichert)"), $entry->getName(), $entry->getDisplayValue());
                }
            }

            //if room-reqquest stored in the session, destroy (we don't need it anymore)
            if (is_object($sem_create_data["resRequest"]))
                $sem_create_data["resRequest"] = '';
            //BIEST00072
            if ($sem_create_data["modules_list"]["scm"]){
                $sem_create_data["sem_scm_name"] = ($SCM_PRESET[1]['name'] ? $SCM_PRESET[1]['name'] : _("Informationen"));
                $sem_create_data["sem_scm_id"] = md5(uniqid(rand()));
                $db->query("INSERT INTO scm SET scm_id='".$sem_create_data["sem_scm_id"]."', tab_name='".$sem_create_data["sem_scm_name"]."', range_id='".$sem_create_data["sem_id"]."', user_id='$user_id', content='".$sem_create_data["sem_scm_content"]."', mkdate='".time()."', chdate='".time()."' ");
            }

            // save activation of plugins
            if (count($sem_create_data["enabled_plugins"]) > 0) {
                $enabled_plugins = PluginEngine::getPlugins('StandardPlugin');
                $context = $sem->getId();

                foreach ($enabled_plugins as $plugin) { 
                    $plugin_id = $plugin->getPluginId();
                    $plugin_status = in_array($plugin_id, $sem_create_data['enabled_plugins']);

                    if (PluginManager::getInstance()->isPluginActivated($plugin_id, $context) != $plugin_status) {
                        PluginManager::getInstance()->setPluginActivated($plugin_id, $context, $plugin_status);
                    }
                }
            }
            //end of the seminar-creation process
            openSem($sem_create_data["sem_id"]); //open Veranstaltung to administrate in the admin-area
        } else {
            $errormsg .= "error�"._("<b>Fehler:</b> Die Veranstaltung wurde schon eingetragen!")."�";
                $successful_entry=2;
        }
    }
    $level=7;
}

//Nur der Form halber... es geht weiter zur SCM-Seite
if (($form == 7) && ($jump_next_x)) {
    if (!$sem_create_data["modules_list"]["scm"] && !$sem_create_data["modules_list"]["schedule"]) {
        header ("Location: admin_seminare1.php");
        die;
    } elseif (!$sem_create_data["modules_list"]["scm"]) {
        //header ("Location: admin_dates.php?assi=yes&ebene=sem&range_id=".$sem_create_data["sem_id"]);
        // ## RAUMZEIT : Welche Zeile ist hier die richtige? raumzeit.php oder themen.php?
        header ("Location: raumzeit.php?seminar_id=".$sem_create_data["sem_id"]);
        die;
    }
    $level=8;
}

//Eintragen der Simple-Content Daten
if (($form == 8) && ($jump_next_x)) {
    if ($sem_create_data["sem_scm_content"]) { //BIEST00072
        //if content is created, we enable the module again (it was turned off above)
        $Modules->writeStatus("scm", $sem_create_data["sem_id"], TRUE);
        if ($sem_create_data["sem_scm_id"]) {
            $db->query("UPDATE scm SET content='".$sem_create_data["sem_scm_content"]."', tab_name='".$sem_create_data["sem_scm_name"]."', chdate='".time()."' WHERE scm_id='".$sem_create_data["sem_scm_id"]."'");
        } else {
            $sem_create_data["sem_scm_id"]=md5(uniqid(rand()));
            $db->query("INSERT INTO scm SET scm_id='".$sem_create_data["sem_scm_id"]."', tab_name='".$sem_create_data["sem_scm_name"]."', range_id='".$sem_create_data["sem_id"]."', user_id='$user_id', content='".$sem_create_data["sem_scm_content"]."', mkdate='".time()."', chdate='".time()."' ");
        }
        if ($db->affected_rows()) {
            //if ($sem_create_data["modules_list"]["schedule"]) // ## RAUMZEIT : schedule duerfte veraltet sein, muesste als komplett weg
                //header ("Location: admin_dates.php?assi=yes&ebene=sem&range_id=".$sem_create_data["sem_id"]);
            //else
                header ("Location: admin_seminare1.php");
            page_close();
            die;
            }
        else
            {
            $errormsg .= "error�"._("Fehler! Der Eintrag konnte nicht erfolgreich vorgenommen werden!")."";
            $level=8;
            }
    } else {
        //if no content is created yet, we disable the module and jump to the schedule (if activated)
        //$Modules->writeStatus("scm", $sem_create_data["sem_id"], FALSE); //BIEST00072
        //if ($sem_create_data["modules_list"]["schedule"]) // ## RAUMZEIT : siehe oben
        //  header ("Location: admin_dates.php?assi=yes&ebene=sem&range_id=".$sem_create_data["sem_id"]);
        //else
            header ("Location: admin_seminare1.php");
        page_close();
        die;
    }
}

//Gibt den aktuellen View an, brauchen wir in der Hilfe
$sem_create_data["level"]=$level;

// Help-Keywords
switch ($level) {
    case '1':
        $HELP_KEYWORD="Basis.VeranstaltungsAssistentGrunddaten";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent - Schritt 1: Grunddaten");
        break;
    case '2':
        $HELP_KEYWORD="Basis.VeranstaltungsAssistentPersonendatenTypUndSicherheit";
        if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])
            $CURRENT_PAGE = _("Veranstaltungs-Assistent - Schritt 2: Personendaten, Typ, Sicherheit und Bereiche");
        else
            $CURRENT_PAGE = _("Veranstaltungs-Assistent - Schritt 2: Personendaten, Typ und Sicherheit");
        break;
    case '3':
        $HELP_KEYWORD="Basis.VeranstaltungsAssistentTermindaten";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent - Schritt 3: Zeiten und Termine");
        break;
    case '4':
        $HELP_KEYWORD="Basis.VeranstaltungsAssistentSonstiges";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent - Schritt 4: Orts- und Raumangaben");
        break;
    case '5':
        $HELP_KEYWORD="Basis.VeranstaltungsAssistentBereitZumAnlegen";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent - Schritt 5: Sonstige Daten");
        break;
    case '6':
        $HELP_KEYWORD="Basis.VeranstaltungsAssistentVeranstaltungAngelegt";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent - Schritt 6: Anlegen der Veranstaltung");
        break;
    case '7':
        $HELP_KEYWORD="Basis.VeranstaltungsAssistent";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent");
        break;
    case '8':
        //This Help-Page won't help.... $HELP_KEYWORD="Basis.VeranstaltungsAssistentLiteratur-UndLinkliste";
        $HELP_KEYWORD="Basis.VeranstaltungsAssistent";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent - Schritt 7: Freie Informationsseite");
        break;
    default:
        $HELP_KEYWORD="Basis.VeranstaltungsAssistent";
        $CURRENT_PAGE=_("Veranstaltungs-Assistent");
        break;
}

# change <body>'s id attribute
$GLOBALS['body_id'] = 'admin_seminare_assi';

Navigation::activateItem('/admin/course/create');

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head


if (!$sem_create_data["sem_class"])
    include ('lib/include/startup_checks.inc.php');

//Before we start, let's decide the category (class) of the Veranstaltung
if ((!$sem_create_data["sem_class"]) && (!$level)){
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <?
        if ($errormsg) parse_msg($errormsg);
        ?>
        <tr>
            <td class="blank" valign="top">
                <blockquote><br>
                <?=_("Willkommen beim Veranstaltungs-Assistenten. Der Veranstaltungs-Assistent wird Sie Schritt f&uuml;r Schritt durch die notwendigen Schritte zum Anlegen einer neuen Veranstaltung in Stud.IP leiten."); ?><br><br>
                <?=_("Bitte geben Sie zun&auml;chst an, welche Art von Veranstaltung Sie neu anlegen m&ouml;chten:"); ?>
                </blockquote>
            </td>
            <td class="blank" align="right" valign="top" rowspan="2">
                <img src="<?= localePictureUrl('assistent.jpg') ?>" border="0">
            </td>
        </tr>
        <tr>
            <td class="blank" colspan="1">&nbsp;
                <blockquote>
                    <table cellpadding=0 cellspacing=2 width="90%" border="0">
                    <?
                    foreach (SeminarCategories::GetAll() as $category) {
                        if(!$category->course_creation_forbidden){
                            echo "<tr><td width=\"3%\" class=\"blank\"><a href=\"".URLHelper::getLink("?start_level=TRUE&class=".$category->id)."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/forumrot.gif\" border=0 /></a><td>";
                            echo "<td width=\"97%\" class=\"blank\"><a href=\"".URLHelper::getLink("?start_level=TRUE&class=".$category->id)."\">".$category->name."</a><td></tr>";
                        echo "<tr><td width=\"3%\" class=\"blank\">&nbsp; <td>";
                            echo "<td width=\"97%\" class=\"blank\"><font size=-1>".$category->create_description."</font><td></tr>";
                        }
                    }
                    ?>
                    </table>
                </blockquote>
            </td>
        </tr>
        <? if ($GLOBALS['STUDYGROUPS_ENABLE']) : ?>
        <tr>
            <td class="blank">
            <blockquote>
            <br>
            <?=_("Sie k�nnen auch Studiengruppen anlegen, die funktional deutlich eingeschr�nkt sind und vor allem Formen selbstorganisierten Lernens unterst�tzen sollen:")?>
            &nbsp;<a href="<?=URLHelper::getLink('dispatch.php/course/studygroup/new')?>"><?=_("Studiengruppen anlegen")?></a>.
            </blockquote>
            </td>
            <td class="blank">&nbsp;</td>
        </tr>
        <? endif; ?>
    </table>
    <?
}

//Level 1: Hier werden die Grunddaten abgefragt.
elseif ((!$level) || ($level == 1))
    {
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td class="blank" colspan=2>&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <tr>
            <td class="blank" valign="top">
                <blockquote>
                <?=_("Willkommen beim Veranstaltungs-Assistenten. Der Veranstaltungs-Assistent wird Sie nun Schritt f&uuml;r Schritt durch die notwendigen Schritte zum Anlegen einer neuen Veranstaltung in Stud.IP leiten."); ?><br><br>
                <?
                if ($cmd=="do_copy") {
                    echo _("Die Daten der zu kopierenden Veranstaltung werden &uuml;bernommen. Bitte &auml;ndern Sie die Informationen, die sich f&uuml;r die kopierte Veranstaltung ergeben.");
                }
                ?><br><br>
                <b><?=_("Schritt 1: Grunddaten der Veranstaltung angeben"); ?></b><br><br>
                <font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
                </blockquote>
            </td>
            <td class="blank" align="right" valign="top">
                <img src="<?= localePictureUrl('hands01.jpg') ?>" border="0">
            </td>
        </tr>
        <tr>
            <td class="blank" colspan=2>
            <form method="POST" action="<? echo URLHelper::getLink() ?>">
            <input type="HIDDEN" name="form" value=1>
                <table cellspacing=0 cellpadding=2 border=0 width="99%" align="center">
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Name der Veranstaltung:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            &nbsp; <input type="text" name="sem_name" size=58 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_name"])) ?>">
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Bitte geben Sie hier einen aussagekr�ftigen, aber m�glichst knappen Titel f�r die Veranstaltung ein. Dieser Eintrag erscheint innerhalb Stud.IPs durchgehend zur Identifikation der Veranstaltung."), TRUE, TRUE) ?>
                            >
                            <font color="red" size=+2>*</font>
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Untertitel:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            &nbsp; <input type="text" name="sem_untert" size=58 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_untert"]))?>">
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Der Untertitel erm�glicht eine genauere Beschreibung der Veranstaltung."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Typ der Veranstaltung:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            &nbsp; <select name="sem_status">
                            <?
                                foreach ($SEM_TYPE as $sem_type_id => $sem_type) {
                                    if ($sem_type["class"] == $sem_create_data["sem_class"])
                                        printf("<option %s value=%s>%s</option>",
                                               $sem_create_data["sem_status"] == $sem_type_id
                                                 ? "selected"
                                                 : "",
                                               $sem_type_id,
                                               $sem_type["name"]);
                                }
                            ?>
                            </select> <br>
                            &nbsp; <font size="-1"> <?=_("in der Kategorie"); ?> <b><? echo $SEM_CLASS[$sem_create_data["sem_class"]]["name"] ?></b></font>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("�ber den Typ der Veranstaltung werden die Veranstaltungen innerhalb von Listen gruppiert."), TRUE, TRUE) ?>
                            >
                            <font color="red" size=+2>*</font>
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Art der Veranstaltung:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            &nbsp; <input type="text" name="sem_art" size=30 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_art"])) ?>">
                            <font size=-1><?=_("(eigene Beschreibung)"); ?></font>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Hier k�nnen Sie eine frei w�hlbare Bezeichnung f�r die Art der Veranstaltung w�hlen."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <?
                    if (!$SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) {
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Veranstaltungsnummer:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="30%">
                            &nbsp; <input type="text" name="sem_nummer" size=20 maxlength=32 value="<? echo  htmlReady(stripslashes($sem_create_data["sem_nummer"])) ?>">
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Fall Sie eine eindeutige Veranstaltungsnummer f�r diese Veranstaltung kennen, geben Sie diese bitte hier ein."), TRUE, TRUE) ?>
                            >
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("ECTS-Punkte:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="60%">
                            &nbsp; <input type="text" name="sem_ects" size=6 maxlength=32 value="<? echo  htmlReady(stripslashes($sem_create_data["sem_ects"])) ?>">
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("ECTS-Kreditpunkte, die in dieser Veranstaltung erreicht werden k�nnen."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <?
                    }
                    if (!$SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) {
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Teilnahme- beschr&auml;nkung:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" nowrap width="30%" colspan=1>
                            &nbsp; <input type="RADIO" name="sem_admission" value=0 <? if (!$sem_create_data["sem_admission"]) echo 'checked'?>>
                            <?=_("keine"); ?> &nbsp; <br>
                            &nbsp; <input type="RADIO" name="sem_admission" value=2 <? if ($sem_create_data["sem_admission"]=="2") echo 'checked'?>>
                            <?=_("nach Anmeldereihenfolge"); ?> <br>
                            &nbsp; <input type="RADIO" name="sem_admission" value=1 <? if ($sem_create_data["sem_admission"]=="1") echo 'checked'?>>
                            <?=_("per Losverfahren"); ?>&nbsp;
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Sie k�nnen die Anzahl der Teilnehmenden beschr�nken. M�glich ist die Zulassung von Interessierten �ber das Losverfahren oder �ber die Reihenfolge der Anmeldung. Sie k�nnen sp�ter Angaben �ber zugelassene Teilnehmer machen."), TRUE, TRUE) ?>
                            >
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("maximale Teilnehmeranzahl:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="50%">
                            &nbsp; <input type="text" name="sem_turnout" size=6 maxlength=5 value="<? echo (int)$sem_create_data["sem_turnout"] ?>">
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Geben Sie hier die erwartete Teilnehmerzahl an. Stud.IP kann auf Wunsch f�r Sie ein Anmeldeverfahren starten, wenn Sie �Teilnahmebeschr�nkung: per Losverfahren / nach Anmeldereihenfolge� benutzen."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr<? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Anmeldemodus:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" nowrap width="90%" colspan=3>
                            &nbsp; <input type="RADIO" name="sem_payment" value=0 <? if ($sem_create_data["sem_payment"]=="0") echo 'checked'?>>
                            <?=_("direkter Eintrag"); ?>&nbsp;
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Neue Teilnehmer werden direkt in die Veranstaltung eingetragen."), TRUE, TRUE) ?>
                            >
                            &nbsp; <input type="RADIO" name="sem_payment" value=1 <? if ($sem_create_data["sem_payment"]=="1") echo 'checked'?>>
                            <?=_("vorl&auml;ufiger Eintrag"); ?>&nbsp;
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Neue Teilnehmer bekommen den Status \"vorl�ufig aktzeptiert\". Sie k�nnen von Hand die zugelassenen Teilnehmer ausw�hlen. Vorl�ufig akzeptierte Teilnehmer haben keinen Zugriff auf die Veranstaltung."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <?
                    }
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Beschreibung/ Kommentar:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            &nbsp; <textarea name="sem_desc" cols=58 rows=6><? echo htmlReady(stripslashes($sem_create_data["sem_desc"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Hier geben Sie bitte den eigentlichen Kommentartext der Veranstaltung (analog zum Vorlesungskommentar) ein."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Semester:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="20%">
                            &nbsp;
                            <?
                            $all_semester = $semester->getAllSemesterData();

                            echo "<select name=\"sem_start_time\">";
                            if(!$GLOBALS['ASSI_SEMESTER_PRESELECT'])
                            {
                                echo "<option value=\"-1\" >"._('Bitte ausw�hlen')."</option>";
                            }
                            foreach ($all_semester as $key => $semester) {
                                if ((!$semester["past"]) && ($semester["ende"] > time())) {
                                    if ($sem_create_data["sem_start_time"] ==$semester["beginn"]) {
                                        echo "<option value=".$semester["beginn"]." selected>", $semester["name"], "</option>";
                                    } else {
                                        echo "<option value=".$semester["beginn"].">", $semester["name"], "</option>";
                                    }
                                }
                            }
                            echo "</select>";

                            ?>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Bitte geben Sie hier ein, welchem Semester die Veranstaltung zugeordnet werden soll."), TRUE, TRUE) ?>
                            >
                            <font color="red" size=+2>*</font>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Dauer:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="60%">
                            &nbsp; <select name="sem_duration_time">
                            <?
                                if ($sem_create_data["sem_duration_time"] == 0)
                                    echo "<option value=0 selected>"._("1 Semester")."</option>";
                                else
                                    echo "<option value=0>"._("1 Semester")."</option>";
                                for ($i=0; $i<sizeof($all_semester); $i++)
                                    if ((!$all_semester[$i]["past"]) && ($all_semester[$i]["name"] != $SEM_NAME) && (($all_semester[$i]["vorles_ende"] > time())))
                                        {
                                        if (($sem_create_data["sem_start_time"] + $sem_create_data["sem_duration_time"]) == $all_semester[$i]["beginn"])
                                            {
                                            if (!$sem_create_data["sem_duration_time"] == 0)
                                                echo "<option value=",$all_semester[$i]["beginn"], " selected>"._("bis")." ", $all_semester[$i]["name"], "</option>";
                                            else
                                                echo "<option value=",$all_semester[$i]["beginn"], ">"._("bis")." ", $all_semester[$i]["name"], "</option>";
                                            }
                                        else
                                            echo "<option value=",$all_semester[$i]["beginn"], ">"._("bis")." ", $all_semester[$i]["name"], "</option>";
                                        }
                                if ($sem_create_data["sem_duration_time"] == -1)
                                    echo "<option value=-1 selected>"._("unbegrenzt")."</option>";
                                else
                                    echo "<option value=-1>"._("unbegrenzt")."</option>";
                            ?>
                            </select>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Falls die Veranstaltung mehrere Semester l�uft, k�nnen Sie hier das Endsemester w�hlen. Dauerveranstaltungen k�nnen �ber die entsprechende Einstellung markiert werden."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Turnus:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" nowrap width="30%" colspan=3>
                            &nbsp; <select  name="term_art">
                            <?
                            if ($sem_create_data["term_art"] == 0)
                                echo "<option selected value=\"0\">"._("regelm&auml;&szlig;ig")."</option>";
                            else
                                echo "<option value=\"0\">"._("regelm&auml;&szlig;ig")."</option>>";
                            if ($sem_create_data["modules_list"]["schedule"]) {
                                if ($sem_create_data["term_art"] == 1)
                                    echo "<option selected value=\"1\">"._("unregelm&auml;&szlig;ig oder Blockveranstaltung")."</option>";
                                else
                                    echo "<option value=\"1\">"._("unregelm&auml;&szlig;ig oder Blockveranstaltung")."</option>";
                            }
                            if ($sem_create_data["term_art"] == -1)
                                echo "<option selected value=\"-1\">"._("keine Termine eingeben")."</option>";
                            else
                                echo "<option value=\"-1\">"._("keine Termine eingeben")."</option>";
                            ?>
                            </select>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Bitte w�hlen Sie hier aus, ob die Veranstaltung regelm��ig stattfindet, oder ob es nur Sitzungen an bestimmten Terminen gibt (etwa bei einem Blockseminar)"), TRUE, TRUE) ?>
                            >
                            <font color="red" size=+2>*</font>
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Heimat-Einrichtung:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            &nbsp;
                            <?
                            if (!$perm->have_perm("admin"))
                                $db->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'dozent' ) ORDER BY is_fak,Name");
                            else if (!$perm->have_perm("root"))
                                $db->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst  a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'admin') ORDER BY is_fak,Name");
                            else
                                $db->query("SELECT Name,Institut_id,1 AS is_fak,'admin' AS inst_perms FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
                            if ($db->affected_rows()){
                                echo "<select name=\"sem_inst_id\">";
                                while ($db->next_record()) {
                                    printf ("<option %s style=\"%s\" value=%s>%s</option>", $db->f("Institut_id") == $sem_create_data["sem_inst_id"] ? "selected" : "",
                                        ($db->f("is_fak")) ? "font-weight:bold;" : "",$db->f("Institut_id"), my_substr($db->f("Name"),0,60));
                                    if ($db->f("is_fak") && $db->f("inst_perms") == "admin"){
                                        $db2->query("SELECT a.Institut_id, a.Name FROM Institute a
                                            WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND a.Institut_id!='" .$db->f("Institut_id") . "' ORDER BY Name");
                                        while($db2->next_record()){
                                            printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $db2->f("Institut_id") == $sem_create_data["sem_inst_id"] ? "selected" : "",
                                                $db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
                                        }
                                    }
                                }
                                echo "</select>";
                            }
                            ?>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Die Heimat-Einrichtung ist die Einrichtung, die offiziell f�r die Veranstaltung zust�ndig ist."), TRUE, TRUE) ?>
                            >
                            <font color="red" size=+2>*</font>
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("beteiligte Einrichtungen:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            &nbsp; <select  name="sem_bet_inst[]" MULTIPLE SIZE=7>
                            <?
                                $db->query("SELECT Institut_id,Name FROM Institute WHERE Institut_id = fakultaets_id ORDER BY Name");
                                while ($db->next_record()) {
                                    $selected="";
                                    if(is_array($sem_create_data["sem_bet_inst"]) && in_array($db->f("Institut_id"),$sem_create_data["sem_bet_inst"])){
                                        $selected = "selected";
                                    }
                                    printf ("<option %s style=\"font-weight:bold;\" value=\"%s\">%s</option>",$selected,$db->f("Institut_id")
                                        , htmlReady(my_substr($db->f("Name"),0,60)));
                                    $db2->query("SELECT Institut_id, Name FROM Institute
                                        WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND Institut_id!='" .$db->f("Institut_id") . "' ORDER BY Name" );
                                    while($db2->next_record()){
                                        $selected="";
                                        if(is_array($sem_create_data["sem_bet_inst"]) && in_array($db2->f("Institut_id"),$sem_create_data["sem_bet_inst"])){
                                        $selected = "selected";
                                        }
                                        printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $selected,
                                            $db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
                                    }
                                }
                            ?>
                            </select>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(sprintf(_("Bitte markieren Sie hier alle Einrichtungen, an denen die Veranstaltung ebenfalls angeboten wird. Bitte beachten Sie: Sie k�nnen sp�ter nur %s aus den Einrichtungen ausw�hlen, die entweder als Heimat- oder als beteiligte Einrichtung markiert worden sind. Sie k�nnen mehrere Eintr�ge markieren, indem sie die STRG bzw. APPLE Taste gedr�ckt halten und dann auf die Eintr�ge klicken."), get_title_for_status('dozent', 2, $sem_create_data["sem_status"])), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                </table>
            </form>
            </td>
        </tr>
    </table>
    <?
    }

//Level 2: Hier werden weitere Einzelheiten (Personendaten und Zeiten) abgefragt
if ($level == 2)
    {
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr >
            <td class="blank" colspan=2>&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <tr>
            <td class="blank" valign="top">
                <blockquote>
                <?
                if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])
                    echo "<b>"._("Schritt 2: Personendaten, Studienbereiche und weitere Angaben zur Veranstaltung")."</b><br><br>";
                else
                    echo "<b>"._("Schritt 2: Personendaten und weitere Angaben zur Veranstaltung")." </b><br><br>";
                ?>
                <font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
                </blockquote>
            </td>
            <td class="blank" align="right" valign="top">
                <img src="<?= localePictureUrl('hands02.jpg') ?>" border="0">
            </td>
        </tr>
        <tr>
            <td class="blank" colspan=2>
            <form method="POST" action="<? echo URLHelper::getLink() ?>#anker">
            <input type="HIDDEN" name="form" value=2>
            <input type="HIDDEN" name="level" value=2>
                <table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                        <?
                        echo get_title_for_status('dozent', count($sem_create_data["sem_doz"]), $sem_create_data["sem_status"]);
                        ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="40%">
                            <?
                            if (sizeof($sem_create_data["sem_doz"]) >0) {
                        asort($sem_create_data["sem_doz"]);
                        echo "<table>";
                        $i = 0;
                                foreach($sem_create_data["sem_doz"] as $key=>$val) {
                                    echo "<tr>";
                                     $img_src = "images/trash.gif";
                                     $href = "?delete_doz=".get_username($key)."#anker";

                                     echo "<td>";
                                     echo "<a href='".URLHelper::getLink($href)."'>";
                                     echo "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
                                     echo "</a>";
                                     echo "</td>";

                           // move up (if not first)
                           echo "<td>";
                           if ($i > 0)
                           {
                                                            $href = "?moveup_doz=".get_username($key)."&foo=".time()."#anker";
                                                            $img_src = "images/move_up.gif";
                                                            echo "<a href='".URLHelper::getLink($href)."'>";
                                                            echo "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
                                                            echo "</a>";
                           }
                           echo "</td>";
                           // move down (if not last)
                           echo "<td>";
                           if ($i < (sizeof($sem_create_data["sem_doz"]) - 1))
                           {
                                                            $href = "?movedown_doz=".get_username($key)."&foo=".time()."#anker";
                                                            $img_src = "images/move_down.gif";
                                                            echo "<a href='".URLHelper::getLink($href)."'>";
                                                            echo "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
                                                            echo "</a>";
                           }
                           echo "</td>";
                              echo "<td>";
                              echo "<font size=\"-1\"><b>". get_fullname($key, "full_rev", true).
                           " (". get_username($key) . ")</b></font>";

                              echo "</td>";

                                   echo "</tr>";// end of row
                           $i++;
                        }
                           echo "</table>";
                     //     printf ("&nbsp; <a href=\"%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\"></a> &nbsp; <font size=\"-1\"><b>%s (%s)&nbsp; &nbsp; <br>", URLHelper::getLink($PHP_SELF."?delete_doz=".get_username($key)), get_fullname($key,"full_rev",true), get_username($key));
                         } else {
                                printf ("<font size=\"-1\">&nbsp;  ". sprintf(_("Keine %s gew&auml;hlt."), get_title_for_status('dozent', 2, $sem_create_data["sem_status"]))."</font><br >");
                            }
                            ?>
                            &nbsp; <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <?
                                echo tooltip(sprintf(_("Die Namen der Benutzer, die die Veranstaltung leiten. Nutzen Sie die Suchfunktion, um weitere Eintragungen vorzunehmen, oder das M�lltonnensymbol, um Eintr�ge zu l�schen."), get_title_for_status('dozent', 2, $sem_create_data["sem_status"])), TRUE, TRUE);
                                ?>
                            >
                            <font color="red" size=+2>*</font>
                        </td>
				<td <? echo $cssSw->getFullClass() ?> width="50%" colspan="2">
				<?php
				print sprintf(_("%s hinzuf&uuml;gen"), get_title_for_status('dozent', 1, $seminar_type));
				print "<br><input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/move_left.gif\" ".tooltip(_("NutzerIn hinzuf�gen"))." border=\"0\" name=\"send_doz\" />";

				$clause="AND Institut_id IN ('".$sem_create_data["sem_inst_id"]."'";
                foreach($sem_create_data["sem_bet_inst"] as $val) {
                    $clause.=",'$val'";
                }
                $clause.=") ";
				$Dozentensuche = new SQLSearch("SELECT DISTINCT username, ". 
				        $_fullname_sql['full_rev'] ." AS fullname " .
				        "FROM user_inst " .
				            "LEFT JOIN auth_user_md5 USING (user_id) " .
				            "LEFT JOIN user_info USING(user_id) " .
				        "WHERE inst_perms = 'dozent' " .
				            $clause .
				            "AND (username LIKE :input OR Vorname LIKE :input OR Nachname LIKE :input) " .
				        "ORDER BY Nachname");
				print QuickSearch::get("add_doz", $Dozentensuche)
				            ->render();
				?> 
				<br><font size=-1><?=_("Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein.")?></font>
				</td>
			</tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                        <?
                        echo get_title_for_status('tutor', count($sem_create_data["sem_tut"]), $sem_create_data["sem_status"]);
                        ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="40%">
                            <?
                            if (sizeof($sem_create_data["sem_tut"]) >0) {
                        asort($sem_create_data["sem_tut"]);
                        echo "<table>";
                        $i = 0;
                                foreach($sem_create_data["sem_tut"] as $key=>$val) {
                                                            echo "<tr>";
                                                            echo "<td>";

                                                            $href = "?delete_tut=".get_username($key)."#anker";
                                                            $img_src = "images/trash.gif";

                                                            echo "<a href='".URLHelper::getLink($href)."'>";
                                                            echo "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
                                                            echo "</a>";
                                                            echo "</td>";

                           // move up (if not first)
                           echo "<td>";
                           if ($i > 0)
                           {
                                                            $href = "?moveup_tut=".get_username($key)."&foo=".time()."#anker";
                                                            $img_src ="images/move_up.gif";

                                                            echo "<a href='".URLHelper::getLink($href)."'>";
                                                            echo "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
                                                            echo "</a>";
                           }
                           echo "</td>";
                           // move down (if not last)
                           echo "<td>";
                           if ($i < (sizeof($sem_create_data["sem_tut"]) - 1))
                           {
                                                            $href = "?movedown_tut=".get_username($key)."&foo=".time()."#anker";
                                                            $img_src = "images/move_down.gif";

                                                            echo "<a href='".URLHelper::getLink($href)."'>";
                                                            echo "<img src='{$GLOBALS['ASSETS_URL']}{$img_src}' border='0'>";
                                                            echo "</a>";
                           }
                           echo "</td>";
                              echo "<td>";
                              echo "<font size=\"-1\"><b>".get_fullname($key, "full_rev",true).
                           " (". get_username($key) . ")</b></font>";

                              echo "</td>";

                                   echo "</tr>";// end of row
                           $i++;
                        }
                        echo "</table>";
                            } else {
                                printf ("<font size=\"-1\">&nbsp;  ". sprintf(_("Keine %s gew&auml;hlt."), get_title_for_status('tutor', 2, $sem_create_data["sem_status"]))."</font><br >");
                            }
                            ?>
                            &nbsp; <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <?
                                echo tooltip(sprintf(_("Die Namen der %s, die in der Veranstaltung weitergehende Rechte erhalten (meist studentische Hilfskr�fte). Nutzen Sie die Suchfunktion (Lupensymbol), um weitere Eintragungen vorzunehmen, oder das M�lltonnensymbol, um Eintr�ge zu l�schen."), get_title_for_status('tutor', 2, $sem_create_data["sem_status"])), TRUE, TRUE);
                                ?>
                            >
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="50%" colspan="2">
                        <?php
                        print sprintf(_("%s hinzuf&uuml;gen"), get_title_for_status('tutor', 1, $seminar_type));
                        print "<br><input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/move_left.gif\" ".tooltip(_("NutzerIn hinzuf�gen"))." border=\"0\" name=\"send_tut\" />";

                        $clause="AND Institut_id IN ('".$sem_create_data["sem_inst_id"]."'";
                        foreach($sem_create_data["sem_bet_inst"] as $val) {
                            $clause.=",'$val'";
                        }
                        $clause.=") ";
                        $Dozentensuche = new SQLSearch("SELECT DISTINCT username, ". 
                            $_fullname_sql['full_rev'] ." AS fullname " .
                            "FROM user_inst " .
                                "LEFT JOIN auth_user_md5 USING (user_id) " .
                                "LEFT JOIN user_info USING(user_id) " .
                            "WHERE inst_perms in ('dozent', 'tutor') " .
                                $clause .
                                "AND (username LIKE :input OR Vorname LIKE :input OR Nachname LIKE :input) " .
                            "ORDER BY Nachname");
                        print QuickSearch::get("add_tut", $Dozentensuche)
                                ->render();
                        ?> 
                        <br><font size=-1><?=_("Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein.")?></font>
                        </td>
                    </tr>

                    <? if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"]) : ?>
                        <tr <? $cssSw->switchClass() ?>>
                            <td colspan="4" class="<? echo $cssSw->getClass() ?>">

                                <?= _("Studienbereiche:") ?>
                                <img src="<?= Assets::image_path('info') ?>"
                                    <?= tooltip( _("Sie m�ssen mindestens einen Studienbereich ausw�hlen! Der Studienbereich legt z.B. fest, wo die Veranstaltung im Vorlesungsverzeichnis auftaucht."), TRUE, TRUE ) ?>
                                >   
                                <font color="red" size=+2>*</font>

                                <?
                                $trails_views = $GLOBALS['STUDIP_BASE_PATH'] . '/app/views';
                                $factory = new Flexi_TemplateFactory($trails_views);
                                list(,$smm_semester_id) = array_values(SemesterData::GetInstance()->getSemesterDataByDate($sem_create_data["sem_start_time"]));

                                echo $factory->render('course/study_areas/form',
                                                      array('selection' => $area_selection,
                                                            'semester_id' => $smm_semester_id));
                                ?>

                            </td>
                        </tr>
                    <? endif ?>

                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Lesezugriff:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            <?
                            if (!$sem_create_data["sem_admission"]) {
                                if (!isset($sem_create_data["sem_sec_lese"]) || $sem_create_data["sem_sec_lese"]==3)
                                    $sem_create_data["sem_sec_lese"] = "1"; //Vorgabe: nur angemeldet oder es war Teilnahmebegrenzung gesetzt
                                if (get_config('ENABLE_FREE_ACCESS')){
                                    ?>
                                    <input type="radio" name="sem_sec_lese" value="0" <?php print $sem_create_data["sem_sec_lese"] == 0 ? "checked" : ""?>> <?=_("freier Zugriff"); ?> &nbsp;
                                    <?
                                } else {
                                    ?>
                                    <font color=#BBBBBB>&nbsp; &nbsp; &nbsp;  <?=_("freier Zugriff")?> &nbsp;</font>
                                    <?
                                }
                                ?>
                                <input type="radio" name="sem_sec_lese" value="1" <?php print $sem_create_data["sem_sec_lese"] == 1 ? "checked" : ""?>> <?=_("in Stud.IP angemeldet"); ?> &nbsp;
                                <input type="radio" name="sem_sec_lese" value="2" <?php print $sem_create_data["sem_sec_lese"] == 2 ? "checked" : ""?>> <?=_("nur mit Passwort"); ?> &nbsp;
                                <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                    <? echo tooltip(_("Hier geben Sie an, ob der Lesezugriff auf die Veranstaltung frei (jeder), normal beschr�nkt (nur registrierte Stud.IP-User) oder nur mit einem speziellen Passwort m�glich ist."), TRUE, TRUE) ?>
                                >
                            <?
                            } else
                                print "&nbsp; <font size=-1>"._("Leseberechtigung nach erfolgreichem Anmeldeprozess")."</font>"
                            ?>
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Schreibzugriff:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            <?
                            if (!$sem_create_data["sem_admission"]) {
                                if (!isset($sem_create_data["sem_sec_schreib"]) || $sem_create_data["sem_sec_schreib"]==3)
                                    $sem_create_data["sem_sec_schreib"] = "1";  //Vorgabe: nur angemeldet
                                if (get_config('ENABLE_FREE_ACCESS') && $SEM_CLASS[$sem_create_data["sem_class"]]["write_access_nobody"]) {
                                    ?>
                                <input type="radio" name="sem_sec_schreib" value="0" <?php print $sem_create_data["sem_sec_schreib"] == 0 ? "checked" : ""?>> <?=_("freier Zugriff"); ?> &nbsp;
                                    <?
                                    }
                                else {
                                    ?>
                                <font color=#BBBBBB>&nbsp; &nbsp; &nbsp;  <?=_("freier Zugriff")?> &nbsp;</font>
                                    <?
                                    }
                            ?>
                                <input type="radio" name="sem_sec_schreib" value="1" <?php print $sem_create_data["sem_sec_schreib"] == 1 ? "checked" : ""?>> <?=_("in Stud.IP angemeldet"); ?> &nbsp;
                                <input type="radio" name="sem_sec_schreib" value="2" <?php print $sem_create_data["sem_sec_schreib"] == 2 ? "checked" : ""?>> <?=_("nur mit Passwort"); ?> &nbsp;
                                <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                    <? echo tooltip(_("Hier geben Sie an, ob der Schreibzugriff auf die Veranstaltung frei (jeder), normal beschr�nkt (nur registrierte Stud.IP-User) oder nur mit einem speziellen Passwort m�glich ist."), TRUE, TRUE) ?>
                            >
                            <?
                            } else
                                print "&nbsp; <font size=-1>"._("Schreibberechtigung nach erfolgreichem Anmeldeprozess")."</font>"
                            ?>
                        </td>
                    </tr>
                    <? if (count(($all_domains = UserDomain::getUserDomains()))) {?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
                            <font size=-1><b><?=_("zugelassenene Nutzerdom�nen:")?> </b></font><br>
                            <table border=0 cellpadding=2 cellspacing=0>
                                <tr>
                                    <td class="<? echo $cssSw->getClass() ?>" colspan=3 >
                                        <font size=-1><?=_("Bitte geben Sie hier ein, welche Nutzerdom�nen zugelassen sind.")."</font>"?>
                                    </td>
                                </tr>
                                    <?
                                    if (isset($_REQUEST['add_domain_x']) && $_REQUEST['sem_domain'] !== '' &&
                                        !in_array($_REQUEST['sem_domain'], $sem_create_data["sem_domain"])) {
                                        $sem_create_data["sem_domain"][]= $_REQUEST['sem_domain'];
                                    }

                                    foreach ($sem_create_data["sem_domain"] as $domain_id) {
                                        $domain = new UserDomain($domain_id);
                                        ?>
                                            <tr>
                                                <td class="<? echo $cssSw->getClass() ?>" >
                                                <font size=-1>
                                                <?= htmlReady($domain->getName()) ?>
                                                </font>
                                                </td>
                                                <td class="<?= $cssSw->getClass() ?>" nowrap colspan=2 >
                                                <a href="<?= URLHelper::getLink('?delete_domain='.$domain_id) ?>">
                                                <img src="<?= $GLOBALS['ASSETS_URL'].'images/trash.gif'.'" '.tooltip(_('Nutzerdom�ne aus der Liste l�schen')) ?> />
                                                </a>
                                                </td>
                                            </tr>
                                        <?
                                     }
                                    // get all user domains that can be added
                                    $domains = array_diff($all_domains, $sem_create_data["sem_domain"]);
                                    if (count($domains)) {
                                        ?>
                                    <tr>
                                        <td class="<? echo $cssSw->getClass() ?>" >
                                        <font size=-1>
                                        <select name="sem_domain">
                                        <option value="">-- <?=_("bitte ausw�hlen")?> --</option>
                                        <?

                                        foreach ($domains as $domain) {
                                            printf ("<option value=\"%s\">%s</option>", $domain->getID(), htmlReady(my_substr($domain->getName(), 0, 40)));
                                        }
                                        ?>
                                        </select>
                                        </font>
                                        </td>

                                        <td class="<? echo $cssSw->getClass() ?>">
                                            <?=makeButton("hinzufuegen", "input", _("Ausgew�hlte Nutzerdom�ne hinzuf�gen"), 'add_domain')?>
                                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                                <? // TODO: Find appropriate Infotext
                                                echo tooltip(_("Bitte markieren Sie hier alle Nutzerdom�nen, f�r die die Veranstaltung angeboten wird."), TRUE, TRUE) ?>
                                            >
                                        </td>

                                    </tr>
                                        <?
                                        }
                                    ?>
                            </table>
                        </td>
                    </tr>
                    <?} ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE"  <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                </table>
            </form>
            </td>
        </tr>
    </table>
    <?
    }

//Level 3: Metadaten ueber Terminstruktur
if ($level == 3) {
    $semester = new SemesterData;
    $all_semester = $semester->getAllSemesterData();
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td class="blank" colspan=2>&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <tr>
            <td class="blank" valign="top">
                <blockquote>
                <b><?=_("Schritt 3: Termindaten"); ?></b><br><br>
                <? if ($sem_create_data["term_art"] ==0)
                    print _("Bitte geben Sie hier ein, an welchen Tagen die Veranstaltung stattfindet. Wenn Sie nur einen Wochentag wissen, brauchen Sie nur diesen angeben.<br>Sie haben sp&auml;ter noch die M&ouml;glichkeit, weitere Einzelheiten zu diesen Terminen anzugeben.")."<br><br>";
                else
                    print _("Bitte geben Sie hier die einzelnen Termine an, an denen die Veranstaltung stattfindet.<br> Sie haben sp&auml;ter noch die M&ouml;glichkeit, weitere Einzelheiten zu diesen Terminen anzugeben.")."<br><br>";
                ?>
                <font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
                </blockquote>
            </td>
            <td class="blank" align="right" valign="top">
                <img src="<?= localePictureUrl('hands03.jpg') ?>" border="0">
            </td>
        </tr>
        <tr>
            <td class="blank" colspan=2>
            <form method="POST" name="Formular" action="<? echo URLHelper::getLink() ?>">
            <input type="HIDDEN" name="form" value=3>
                <table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                    <?
                        if ($sem_create_data["term_art"] == 0)
                            {
                            ?>
                            <tr <? $cssSw->switchClass() ?>>
                                <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                                    &nbsp; <?=_("Daten &uuml;ber die Termine:"); ?>
                                </td>
                                <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                                    &nbsp; <b><font size=-1><?=_("Regelm&auml;&szlig;ige Veranstaltung"); ?></font></b><br><br>
                                    &nbsp;  <font size=-1><?=_("Wenn Sie den Typ der Veranstaltung &auml;ndern m&ouml;chten, gehen Sie bitte auf die erste Seite zur&uuml;ck."); ?></font><br><br>
                                    &nbsp;  <font size=-1><?=_("Turnus:"); ?> </font>&nbsp;
                                    <select name="term_turnus">
                                    <?
                                    if ($sem_create_data["term_turnus"]==0)
                                        echo "<option selected value=0>"._("w&ouml;chentlich")."</option>";
                                    else
                                        echo "<option value=0>"._("w&ouml;chentlich")."</option>";
                                    if ($sem_create_data["term_turnus"]==1)
                                        echo "<option selected value=1>"._("zweiw&ouml;chentlich")."</option>";
                                    else
                                        echo "<option value=1>"._("zweiw&ouml;chentlich")."</option>";
                                    ?>
                                    </select>&nbsp;  <font size=-1><?=_("erster Termin in der"); ?></font>
                                    <select name="term_start_woche">
                                    <?
                                    $semester_index = get_sem_num($sem_create_data["sem_start_time"]);
                                    $tmp_first_date = getCorrectedSemesterVorlesBegin($semester_index);
                                    $end_date = $all_semester[$semester_index]['vorles_ende'];

                                    $i = 0;
                                    while ($tmp_first_date < $end_date) {
                                        echo '<option';
                                        if ($sem_create_data["term_start_woche"] == $i) {
                                            echo ' selected="selected"';
                                        }
                                        echo ' value="'.$i.'">';
                                        echo ($i+1).'. '._("Semesterwoche")." ("._("ab")." ".date("d.m.Y",$tmp_first_date).")</option>";
                                        $i++;
                                        $tmp_first_date = $tmp_first_date + (7 * 24 * 60 * 60);
                                    }
                                    ?>
                                    </select>
                                    <br><br>&nbsp; <font size=-1><?=_("Die Veranstaltung findet immer zu diesen Zeiten statt:"); ?></font><br><br>
                                    <?
                                    if (empty($sem_create_data["turnus_count"]))
                                        $sem_create_data["turnus_count"]=1;
                                    for ($i=0; $i<$sem_create_data["turnus_count"]; $i++) {
                                        if ($i>0) echo "<br>\n";
                                        echo '&nbsp; <font size=-1><select name="term_turnus_date[', $i, ']">';
                                        $ttd = (empty($sem_create_data["term_turnus_date"][$i]))? 1 : $sem_create_data["term_turnus_date"][$i];
                                        for($kk = 0; $kk <= 6; $kk++ ){
                                            echo '<option ', (($kk == $ttd)? 'selected ':'');
                                            echo 'value="',$kk,'">';
                                            switch ($kk){
                                                case 2: echo _("Dienstag"); break;
                                                case 3: echo _("Mittwoch"); break;
                                                case 4: echo _("Donnerstag"); break;
                                                case 5: echo _("Freitag"); break;
                                                case 6: echo _("Samstag"); break;
                                                case 0: echo _("Sonntag"); break;
                                                case 1:
                                                default: echo _("Montag");
                                            }
                                            echo '</option>';
                                        }
                                        echo "</select>\n";
                                        $ss = (strlen($sem_create_data["term_turnus_start_stunde"][$i]))? sprintf('%02d', $sem_create_data["term_turnus_start_stunde"][$i]) : '';
                                        if (strlen($sem_create_data["term_turnus_start_minute"][$i])) {
                                            $sm = sprintf('%02d', $sem_create_data["term_turnus_start_minute"][$i]);
                                        } elseif (strlen($sem_create_data["term_turnus_start_stunde"][$i])) {
                                            $sm = '00';
                                        } else {
                                            $sm ='';
                                        }
                                        $es = (strlen($sem_create_data["term_turnus_end_stunde"][$i]))? sprintf('%02d', $sem_create_data["term_turnus_end_stunde"][$i]) : '';
                                        if (strlen($sem_create_data["term_turnus_end_minute"][$i])) {
                                            $em = sprintf('%02d', $sem_create_data["term_turnus_end_minute"][$i]);
                                        } elseif (strlen($sem_create_data["term_turnus_end_stunde"][$i])) {
                                            $em = '00';
                                        } else {
                                            $em = '';
                                        }
                                        echo '&nbsp; <input type="text" name="term_turnus_start_stunde['. $i. ']" size=2 maxlength=2 value="'. $ss. '"> : ';
                                        echo '<input type="text" name="term_turnus_start_minute['. $i. ']" size=2 maxlength=2 value="'. $sm. '">&nbsp;', _("Uhr bis");
                                        echo '&nbsp; <input type="text" name="term_turnus_end_stunde['. $i.']" size=2 maxlength=2 value="'. $es. '"> : ';
                                        echo '<input type="text" name="term_turnus_end_minute['. $i. ']" size=2 maxlength=2 value="'. $em. '">&nbsp;', _("Uhr"), "\n";

                                        if ($sem_create_data["turnus_count"]>1) {
                                            ?>
                                            &nbsp; <a href="<? echo URLHelper::getLink("?delete_turnus_field=".($i+1)) ?>"><img border=0 src="<?= $GLOBALS['ASSETS_URL'] ?>images/trash.gif" <? echo tooltip(_("Dieses Feld aus der Auswahl l�schen"), TRUE) ?> ></a>
                                            <?
                                        }
                                        echo  Termin_Eingabe_javascript(4, $i, 0, $ss,$sm,$es,$em);

                                        //Beschreibung
                                        echo "\n<br>&nbsp; " . _("Beschreibung:") . "&nbsp;";
                                        echo "\n<select name=\"term_turnus_desc_chooser[$i]\" ";
                                        echo "onChange=\"document.Formular.elements['term_turnus_desc[$i]'].value=document.Formular.elements['term_turnus_desc_chooser[$i]'].options[Formular.elements['term_turnus_desc_chooser[$i]'].selectedIndex].value;\" ";
                                        echo ">";
                                        echo "\n<option value=\"\">" . _("ausw&auml;hlen oder wie Eingabe") . " --></option>";
                                        foreach($TERMIN_TYP as $ttyp){
                                            if ($ttyp['sitzung']) {
                                                echo "\n<option ";
                                                if ($sem_create_data['term_turnus_desc'][$i] == $ttyp['name']) echo "selected";
                                                echo " value=\"" . htmlReady($ttyp['name']) . "\">" . htmlReady($ttyp['name']) . "</option>";
                                            }
                                        }
                                        echo "\n</select>";
                                        echo "&nbsp;";
                                        echo "\n<input type=\"text\" name=\"term_turnus_desc[$i]\" size=\"30\" value=\"".stripslashes(htmlReady($sem_create_data['term_turnus_desc'][$i]))."\">";

                                    }
                                    ?>
                                    <br>&nbsp; <input type="IMAGE" name="add_turnus_field" <?=makeButton("feldhinzufuegen", "src"); ?> border=0 value="Feld hinzuf&uuml;gen">&nbsp;
                                    <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                        <? echo tooltip(_("Wenn es sich um eine regelm��ige Veranstaltung handelt, so k�nnen Sie hier genau angeben, an welchen Tagen, zu welchen Zeiten und in welchem Raum die Veranstaltung stattfindet. Wenn Sie noch keine Zeiten wissen, dann klicken Sie auf �keine Zeiten speichern�."), TRUE, TRUE) ?>
                                    >
                                    <br>
                                </td>
                            </tr>
                        <?
                        }
                    else
                        {
                        ?>
                            <tr <? $cssSw->switchClass() ?>>
                                <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                                    &nbsp; <?=_("Daten &uuml;ber die Termine:"); ?>
                                </td>
                                <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                                    &nbsp; <b><font size=-1><?=_("Veranstaltung an unregelm&auml;&szlig;igen Terminen"); ?></font></b><br><br>
                                    &nbsp;  <font size=-1><?=_("Wenn Sie den Typ der Veranstaltung &auml;ndern m&ouml;chten, gehen Sie bitte auf die erste Seite zur&uuml;ck."); ?></font><br><br>
                                    &nbsp; <font size=-1><?=_("Die Veranstaltung findet an diesen Terminen statt:"); ?></font><br><br>
                                    <?
                                    if (empty($sem_create_data["term_count"]))
                                        $sem_create_data["term_count"]=1;
                                    for ($i=0; $i<$sem_create_data["term_count"]; $i++)
                                        {
                                        if ($i>0) echo "<br>";
                                        $ss = (strlen($sem_create_data["term_start_stunde"][$i]))? sprintf('%02d',$sem_create_data["term_start_stunde"][$i]) : '';
                                        if (strlen($sem_create_data["term_start_minute"][$i])) {
                                            $sm = sprintf('%02d', $sem_create_data["term_start_minute"][$i]);
                                        } elseif (strlen($sem_create_data["term_start_stunde"][$i])) {
                                            $sm = '00';
                                        } else {
                                            $sm = '';
                                        }
                                        $es = (strlen($sem_create_data["term_end_stunde"][$i]))? sprintf('%02d',$sem_create_data["term_end_stunde"][$i]):'';
                                        if (strlen($sem_create_data["term_end_minute"][$i])) {
                                            $em = sprintf('%02d', $sem_create_data["term_end_minute"][$i]);
                                        } elseif (strlen($sem_create_data["term_end_stunde"][$i])) {
                                            $em = '00';
                                        } else {
                                            $em = '';
                                        }
                                        echo '<font size=-1>&nbsp; ', _("Datum:"), ' <input type="text" name="term_tag[',$i,']" size=2 maxlength=2 value="';
                                        if ($sem_create_data["term_tag"][$i]) echo sprintf('%02d',$sem_create_data["term_tag"][$i]);
                                        echo '">.',"\n", '<input type="text" name="term_monat[',$i,']" size=2 maxlength=2 value="';
                                        if ($sem_create_data["term_monat"][$i]) echo sprintf('%02d',$sem_create_data["term_monat"][$i]);
                                        echo '">. <input type="text" name="term_jahr[',$i,']" size=4 maxlength=4 value="';
                                        if ($sem_create_data["term_jahr"][$i]) echo $sem_create_data["term_jahr"][$i];
                                        echo '"> &nbsp;'. _("um"). '<input type="text" name="term_start_stunde['.$i.']" size=2 maxlength=2 value="'. $ss. '"> : ';
                                        echo '<input type="text" name="term_start_minute['.$i.']" size=2 maxlength=2 value="'. $sm. '">&nbsp;'. _("Uhr bis");
                                        echo '<input type="text" name="term_end_stunde['.$i.']" size=2 maxlength=2 value="'. $es. '"> : ';
                                        echo '<input type="text" name="term_end_minute['.$i.']" size=2 maxlength=2 value="'. $em. '">&nbsp;'. _("Uhr"). '</font>'. "\n";

                                        if ($sem_create_data["term_count"]>1)
                                            {
                                            ?>
                                            &nbsp; <a href="<? echo URLHelper::getLink("?delete_term_field=".($i+1)) ?>"><img border=0 src="<?= $GLOBALS['ASSETS_URL'] ?>images/trash.gif" <? echo tooltip(_("Dieses Feld aus der Auswahl l�schen"), TRUE) ?> ></a>
                                            <?
                                            }
                                        echo  Termin_Eingabe_javascript (5, $i, 0, $ss, $sm, $es, $em);
                                        }
                                        ?>
                                        <br>&nbsp; <input type="IMAGE" name="add_term_field" <?=makeButton("feldhinzufuegen", "src"); ?> border=0 value="Feld hinzuf&uuml;gen">&nbsp;
                                        <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                            <? echo tooltip(_("In diesem Feldern k�nnen Sie alle Veranstaltungstermine eingeben. Wenn die Termine noch nicht feststehen, lassen Sie die Felder einfach frei."), TRUE, TRUE) ?>
                                        >
                                        <br>
                                </td>
                            </tr>
                        <?
                        }
                    if ($sem_create_data["modules_list"]["schedule"]) {
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Vorbesprechung:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                        <?
                            $ss = (($sem_create_data["sem_vor_termin"] <> -1)? date("H",$sem_create_data["sem_vor_termin"]):'');
                            $sm = (($sem_create_data["sem_vor_termin"] <> -1)? date("i",$sem_create_data["sem_vor_termin"]):'');
                            $es = (($sem_create_data["sem_vor_end_termin"] <> -1)? date("H",$sem_create_data["sem_vor_end_termin"]):'');
                            $em = (($sem_create_data["sem_vor_end_termin"] <> -1)? date("i",$sem_create_data["sem_vor_end_termin"]):'');
                            echo '<font size=-1>&nbsp; <font size=-1>', _("Wenn es eine Vorbesprechung gibt, tragen Sie diese bitte hier ein:"), '</font><br><br>&nbsp; ', _("Datum:"), '</font>', "\n";
                            echo '<font size=-1><input type="text" name="vor_tag" size=2 maxlength=2 value="', (($sem_create_data["sem_vor_termin"] <> -1)? date("d",$sem_create_data["sem_vor_termin"]):''), '">. ', "\n";
                            echo '<input type="text" name="vor_monat" size=2 maxlength=2 value="', (($sem_create_data["sem_vor_termin"] <> -1)?  date("m",$sem_create_data["sem_vor_termin"]):''), '">. ', "\n";
                            echo '<input type="text" name="vor_jahr" size=4 maxlength=4 value="', (($sem_create_data["sem_vor_termin"] <> -1)? date("Y",$sem_create_data["sem_vor_termin"]):''), '">&nbsp;', "\n";
                            echo _("um"), ' <input type="text" name="vor_stunde" size=2 maxlength=2 value="', $ss, '"> : ', "\n";
                            echo '<input type="text" name="vor_minute" size=2 maxlength=2 value="', $sm, '">&nbsp;', _("Uhr bis"), "\n";
                            echo '<input type="text" name="vor_end_stunde" size=2 maxlength=2 value="', $es, '"> : ', "\n";
                            echo '<input type="text" name="vor_end_minute" size=2 maxlength=2 value="', $em, '">&nbsp;', _("Uhr"), "\n";
                            echo '<img  src="'.$GLOBALS['ASSETS_URL'].'images/info.gif"';
                            echo tooltip(_("Dieses Feld m�ssen Sie nur ausf�llen, wenn es eine verbindliche Vorbesprechung zu der Veranstaltung gibt."), TRUE, TRUE);
                            echo '>';
                            echo  Termin_Eingabe_javascript (6, 0, 0, $ss, $sm, $es, $em);
                        ?>
                        </td>
                    </tr>
                    <?
                    }
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                </table>
            </form>
            </td>
        </tr>
    </table>
    <?
    }

//Level 4: Raumdaten
if ($level == 4) {
    if ($RESOURCES_ENABLE)
        $resList = new ResourcesUserRoomsList($user_id->id, TRUE, FALSE, TRUE);
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td class="blank" colspan=2>&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <tr>
            <td class="blank" valign="top">
                <blockquote>
                <b><?=_("Schritt 4: Raumangaben"); ?></b><br><br>
                <?
                if ($RESOURCES_ENABLE) {
                    if ($RESOURCES_ALLOW_ROOM_REQUESTS) {
                        if ($resList->roomsExist())
                            print _("Bitte geben Sie hier ein, welche Angaben zu R&auml;umen gemacht werden, buchen Sie konkrete R&auml;ume oder stellen sie Raumw&uuml;nsche an die zentrale Raumverwaltung.")."<br><br>";
                        else
                            print _("Bitte geben Sie hier ein, welche Angaben zu R&auml;umen gemacht werden oder stellen Sie Raumw&uuml;nsche an die zentrale Raumverwaltung.")."<br><br>";
                    } elseif ($resList->roomsExist())
                        print _("Bitte geben Sie hier ein, welche Angaben zu R&auml;umen gemacht werden oder buchen Sie konkrete R&auml;ume.")."<br><br>";
                } else
                    print _("Bitte geben Sie hier ein, welche Angaben zu R&auml;umen gemacht werden.")."<br><br>";
                ?>
            </td>
            <td class="blank" align="right" valign="top">
                <img src="<?= localePictureUrl('hands04.jpg') ?>" border="0">
            </td>
        </tr>
        <tr>
            <td class="blank" colspan=2>
            <form method="POST" name="form_4" action="<? echo URLHelper::getLink() ?>#anker">
            <input type="HIDDEN" name="form" value=4>
                <table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                    <?
                    if (($RESOURCES_ALLOW_ROOM_REQUESTS) && ($RESOURCES_ENABLE)) {
                    ?>

                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%">
                            <font size="-1"><b><?=_("Raumw&uuml;nsche"); ?></b><br><br>
                            <?
                            if (get_config('RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST')){
                                echo _("Es wird <u>kein</u> Raum ben&ouml;tigt") . "&nbsp;&nbsp;";
                                echo "<input type=\"checkbox\" name=\"skip_room_request\" style=\"vertical-align:middle\" value=\"1\" ";
                                if ($sem_create_data['skip_room_request']) echo " checked ";
                                echo "><br><br>";
                            }
                            print _("Sie haben die M&ouml;glichkeit, sich Raumeigenschaften sowie einen konkreten Raum zu w&uuml;nschen. Diese Raumw&uuml;nsche werden von der zentralen Raumverwaltung bearbeitet.");
                            print "<br>"._("<b>Achtung:</b> Um sp&auml;ter einen passenden Raum f&uuml;r Ihre Veranstaltung zu bekommen, geben Sie bitte <u>immer</u> die gew&uuml;nschten Eigenschaften mit an!");
                            ?>
                        <td>
                    </tr>
                    <?
                    if ($request_resource_id = $sem_create_data["resRequest"]->getResourceId()) {
                        $resObject = ResourceObject::Factory($request_resource_id);
                    ?>
                    <tr>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%">
                            <br><font size="-1"><b><?=("gew&uuml;nschter Raum:")?></b><br><br>
                            <?
                            print "<b>".htmlReady($resObject->getName())."</b>,&nbsp;"._("verantwortlich:")."&nbsp;<a href=\"".URLHelper::getLink($resObject->getOwnerLink())."\">".$resObject->getOwnerName()."</a>";  //getOwnerLink() does not contain the output of an URLHelper
                            print "&nbsp;&nbsp;<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" ".tooltip(_("den ausgew�hlten Raum l�schen"))." border=\"0\" name=\"reset_resource_id\" />";
                            ?>
                            </font>
                        </td>
                    </tr>
                    <?
                    }
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%">
                            <table border="0" width="100%" cellspaceing="2" cellpadding="0">
                                <tr>
                                    <td width="49%" valign="top">
                                        <font size="-1">
                                        <?
                                        //$sem_create_data["room_request_type"] = FALSE;
                                        print "<b>"._("Raumeigenschaften angeben:")."</b><br><br>";
                                        if (!$dont_anchor)
                                            print "<a name=\"anker\"></a>";
                                        $query = "SELECT * FROM resources_categories  WHERE is_room = '1' ORDER BY name";
                                        $db->query($query);

                                        if (($db->nf() == 1) || ($sem_create_data["resRequest"]->getCategoryId())) {
                                            $room_categories = $db->nf();
                                            if ($db->nf() == 1) {
                                                $db->next_record();
                                                $category_id = $db->f("category_id");
                                                $sem_create_data["resRequest"]->setCategoryId($category_id);
                                            } else
                                                $category_id = $sem_create_data["resRequest"]->getCategoryId();

                                            $query2 = sprintf("SELECT  b.*, c.name AS cat_name FROM resources_categories_properties a LEFT JOIN resources_properties b USING (property_id) LEFT JOIN resources_categories c ON (a.category_id = c.category_id) WHERE c.is_room = '1' AND a.requestable = '1' AND a.category_id = '%s' ORDER BY b.name", $category_id);
                                            $db2->query($query2);

                                            $i=0;
                                            while ($db2->next_record()) {
                                                if (!$i) {
                                                    if ($room_categories> 1) {
                                                        print ("Gew&auml;hlter Raumtyp:");
                                                        print "&nbsp;<select name=\"select_room_type\">";
                                                        while ($db->next_record()) {
                                                            printf ("<option value=\"%s\" %s>%s </option>", $db->f("category_id"), ($category_id == $db->f("category_id")) ? "selected" : "", htmlReady(my_substr($db->f("name"), 0, 30)));
                                                        }
                                                        print "</select>";
                                                        print "&nbsp;<input type=\"IMAGE\" value=\""._("Raumtyp ausw&auml;hlen")."\" name=\"send_room_type\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" border=\"0\" ".tooltip(_("Raumtyp ausw�hlen"))." />";
                                                        print "&nbsp;&nbsp;<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" ".tooltip(_("alle Angaben zur�cksetzen"))." border=\"0\" name=\"reset_room_type\" /><br><br>";
                                                    }

                                                    print _("Folgende Eigenschaften sind w&uuml;nschbar:")."<br><br>";
                                                    print "<table border=\"0\" width=\"100%\" cellspaceing=\"2\" cellpadding=\"0\">";
                                                }
                                                ?>
                                                <tr>
                                                    <td width="30%" valign="top">
                                                        <font size="-1"><?=htmlReady($db2->f("name"))?></font>
                                                    </td>
                                                    <td width="70%" align ="left" valign="top">
                                                    <?
                                                    switch ($db2->f("type")) {
                                                        case "bool":
                                                            printf ("<input type=\"CHECKBOX\" name=\"request_property_val[%s]\" %s /><font size=-1>&nbsp;%s</font>", $db2->f("property_id"), ($sem_create_data["resRequest"]->getPropertyState($db2->f("property_id"))) ? "checked": "", htmlReady($db2->f("options")));
                                                        break;
                                                        case "num":
                                                            if ($db2->f("system") == 2) {
                                                                printf ("<input type=\"TEXT\" name=\"request_property_val[%s]\" value=\"%s\" size=5 maxlength=10 />", $db2->f("property_id"), htmlReady($sem_create_data["resRequest"]->getPropertyState($db2->f("property_id"))));
                                                                if ($sem_create_data["sem_turnout"]) {
                                                                    print "<font size=\"-1\">"._("max. Teilnehmeranzahl &uuml;bernehmen")."</font>";
                                                                    printf ("<br><input type=\"CHECKBOX\" name=\"seats_are_admission_turnout\" %s />&nbsp;",  (($sem_create_data["resRequest"]->getPropertyState($db2->f("property_id")) == $sem_create_data["sem_turnout"]) && ($sem_create_data["sem_turnout"])>0) ? "checked" :"");
                                                                }

                                                            } else {
                                                                printf ("<input type=\"TEXT\" name=\"request_property_val[%s]\" value=\"%s\" size=30 maxlength=255 />", $db2->f("property_id"), htmlReady($sem_create_data["resRequest"]->getPropertyState($db2->f("property_id"))));
                                                            }
                                                        break;
                                                        case "text":
                                                            printf ("<textarea name=\"request_property_val[%s]\" cols=30 rows=2 >%s</textarea>", $db2->f("property_id"), htmlReady($sem_create_data["resRequest"]->getPropertyState($db2->f("property_id"))));
                                                        break;
                                                        case "select":
                                                            $options=explode (";",$db2->f("options"));
                                                            printf ("<select name=\"request_property_val[%s]\">", $db2->f("property_id"));
                                                            print   "<option value=\"\">--</option>";
                                                            foreach ($options as $a) {
                                                                printf ("<option %s value=\"%s\">%s</option>", ($sem_create_data["resRequest"]->getPropertyState($db2->f("property_id")) == $a) ? "selected":"", $a, htmlReady($a));
                                                            }
                                                            printf ("</select>");
                                                        break;
                                                    }
                                                    ?>
                                                    </td>
                                                </tr>
                                                <?
                                                $i++;
                                                if ($i == $db2->nf()) {
                                                    print "</table>";
                                                }
                                            }

                                        } elseif ($db->nf() > 0) {
                                            print _("Bitte geben Sie zun&auml;chst einen Raumtyp an, der f&uuml;r Sie am besten geeignet ist:")."<br><br>";
                                            print "<select name=\"select_room_type\">";
                                                while ($db->next_record()) {
                                                    printf ("<option value=\"%s\">%s </option>", $db->f("category_id"), htmlReady(my_substr($db->f("name"), 0, 30)));
                                                }
                                            print "</select></font>";
                                            print "&nbsp;<input type=\"IMAGE\" value=\""._("Raumtyp ausw&auml;hlen")."\" name=\"send_room_type\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" border=\"0\" ".tooltip(_("Raumtyp ausw�hlen"))." />";
                                        }
                                        ?>
                                        </font>
                                    </td>
                                    <td width="1px" align="center" valign="top" style="background-image: url('<?= $GLOBALS['ASSETS_URL'] ?>images/line2.gif');" nowrap>
                                        &nbsp;&nbsp;
                                    </td>
                                    <td width="50%" valign="top">
                                        <font size="-1">
                                        <?
                                        print "<b>"._("Raum suchen:")."</b><br>";
                                        if ((($search_exp_room) && ($search_room_x)) || ($search_properties_x)) {
                                            $result = $sem_create_data["resRequest"]->searchRoomsToRequest(remove_magic_quotes($search_exp_room), ($search_properties_x) ? TRUE : FALSE);
                                            if ($result) {
                                                printf ("<br><font size=-1><b>%s</b> ".((!$search_properties_x) ? _("R&auml;ume gefunden:") : _("passende R&auml;ume gefunden"))."<br><br>", sizeof($result));
                                                print "<select name=\"select_room\">";
                                                foreach ($result as $key => $val) {
                                                    printf ("<option value=\"%s\">%s </option>", $key, htmlReady(my_substr($val, 0, 30)));
                                                }
                                                print "</select></font>";
                                                print "&nbsp;<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" ".tooltip(_("Den Raum als Wunschraum ausw�hlen"))." border=\"0\" name=\"send_room\" />";
                                                print "&nbsp;&nbsp;<input type=\"IMAGE\" src=\"".$GLOBALS['ASSETS_URL']."images/rewind.gif\" ".tooltip(_("neue Suche starten"))." border=\"0\" name=\"reset_room_search\" />";
                                                if ($search_properties_x)
                                                    print "<br><br>"._("(Diese R&auml;ume erf&uuml;llen die Wunschkriterien, die Sie links angegeben haben.)");
                                            }
                                        }
                                        if (((!$search_exp_room) && (!$search_properties_x)) || (($search_exp_room) && (!$result)) || (($search_properties_x) && (!$result))) {
                                            ?>
                                            <font size=-1>
                                            <? print ((($search_exp_room) || ($search_properties_x)) && (!$result)) ? _("<b>Keinen</b> Raum gefunden.") : "";?>
                                            </font><br>
                                            <font size=-1><?=_("Geben Sie zur Suche den Raumnamen ganz oder teilweise ein:"); ?></font>
                                            <input type="TEXT" size="30" maxlength="255" name="search_exp_room" />&nbsp;
                                            <input type="IMAGE" src="<?= $GLOBALS['ASSETS_URL'] ?>images/suchen.gif" <? echo tooltip(_("Suche starten")) ?> border="0" name="search_room" /><br>
                                            <?
                                        }
                                        ?>
                                        </font>
                                    </td>
                                </tr>
                                <?
                                if ($category_id) {
                                ?>
                                <tr>
                                    <td colspan="2" align="right">
                                        <font size="-1"><?=("passende R&auml;ume suchen")?></font>
                                        <input type="IMAGE" src="<?= $GLOBALS['ASSETS_URL'] ?>images/move_right.gif" <? echo tooltip(_("passende R�ume suchen")) ?> border="0" name="search_properties" />
                                    </td>
                                    <td>
                                        &nbsp;
                                    </td>
                                </tr>
                                <?
                                }
                                ?>
                            </table>
                            </font>

                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%">
                            <font size="-1"><b><?=("Nachricht an den Raumadministrator:")?></b><br><br>
                                <?=_("Sie k&ouml;nnen hier eine Nachricht an den Raumadministrator verfassen, um weitere W&uuml;nsche oder Bermerkungen zur gew&uuml;nschten Raumbelegung anzugeben.")?> <br><br>
                                <textarea name="sem_room_comment" cols=58 rows=4><?=htmlReady($sem_create_data["resRequest"]->getComment()); ?></textarea>
                            </font>
                        </td>
                    </tr>
                    <?
                    }
                    if ($RESOURCES_ENABLE && $resList->roomsExist() &&
                        (((is_array($sem_create_data["metadata_termin"]["turnus_data"])) && ($sem_create_data["term_art"] == 0))
                        || (($sem_create_data["term_first_date"])) && ($sem_create_data["term_art"] == 1)
                        || ($sem_create_data["sem_vor_termin"] > -1))) {
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=3>
                            <font size="-1"><b><?=_("Raumbuchungen"); ?></b></font><br><br>
                            <table border="0" width="100%" cellspaceing="2" cellpadding="0">
                            <?
                                print "<font size=\"-1\">"._("Sie k&ouml;nnen zu jedem Termin einen Raum eintragen. Diese Eintragung wird beim Speichern der Veranstaltung in der Raumverwaltung gebucht.")."</font><br>";
                                if ($sem_create_data["term_art"] == 0) {
                                    if (is_array($sem_create_data["metadata_termin"]["turnus_data"])) {
                                        foreach ($sem_create_data["metadata_termin"]["turnus_data"] as $val) {
                                            print "<tr><td width=\"50%\"><font size=\"-1\">";
                                            switch ($val["day"]) {
                                                case 1: print _("Montag"); break;
                                                case 2: print _("Dienstag"); break;
                                                case 3: print _("Mittwoch"); break;
                                                case 4: print _("Donnerstag"); break;
                                                case 5: print _("Freitag"); break;
                                                case 6: print _("Samstag"); break;
                                                case 7: print _("Sonntag"); break;
                                            }
                                            printf (" "._("von %02d:%02d Uhr bis %02d:%02d Uhr"), $val["start_stunde"], $val["start_minute"],  $val["end_stunde"], $val["end_minute"]);
                                            print "</font></td><td width=\"50%\"><font size=\"-1\">";
                                            $resList->reset();
                                            if ($resList->numberOfRooms()) {
                                                print " &nbsp;<select name=\"term_turnus_resource_id[]\">";
                                                printf ("<option %s value=\"FALSE\">["._("bitte ausw&auml;hlen")."]</option>", (!$val["resource_id"]) ? "selected" : "");
                                                while ($res = $resList->next()) {
                                                    printf ("<option %s value=\"%s\">%s</option>", ($val["resource_id"] == $res["resource_id"]) ? "selected" :"", $res["resource_id"], htmlReady($res["name"]));                                                }
                                                print "</select><br>";
                                            }
                                            print "</font></td></tr>\n";
                                        }
                                    }
                                } elseif ($sem_create_data["term_art"] == 1) {
                                    for ($i=0; $i<$sem_create_data["term_count"]; $i++) {
                                        if (($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_jahr"][$i]) && ($sem_create_data["term_start_stunde"][$i] !== '') && ($sem_create_data["term_end_stunde"][$i] !== '')) {
                                            print "<tr><td width=\"50%\"><font size=\"-1\">";
                                            printf (_("am %02d.%02d.%s von %02d:%02d Uhr bis %02d:%02d Uhr"), $sem_create_data["term_tag"][$i], $sem_create_data["term_monat"][$i], $sem_create_data["term_jahr"][$i], $sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], $sem_create_data["term_end_stunde"][$i], $sem_create_data["term_end_minute"][$i]);
                                            print "</font></td><td width=\"50%\"><font size=\"-1\">";
                                            $resList->reset();
                                            if ($resList->numberOfRooms()) {
                                                printf (" &nbsp;<select name=\"term_resource_id[%s]\">", $i);
                                                printf ("<option %s value=\"FALSE\">["._("bitte ausw&auml;hlen")."]</option>", (!$sem_create_data["term_resource_id"][$i]) ? "selected" : "");
                                                while ($res = $resList->next()) {
                                                    printf ("<option %s value=\"%s\">%s</option>", ($sem_create_data["term_resource_id"][$i] == $res["resource_id"]) ? "selected" :"", $res["resource_id"], htmlReady($res["name"]));
                                                }
                                                print "</select><br>";
                                            }
                                            print "</font></td></tr>\n";
                                        }
                                    }
                                }
                                if ($sem_create_data["sem_vor_termin"] > -1) {
                                    print "<tr><td width=\"50%\"><font size=\"-1\">";
                                    printf (" "._("Vorbesprechung am %s von %s Uhr bis %s Uhr"), date("d.m.Y", $sem_create_data["sem_vor_termin"]), date("H:i", $sem_create_data["sem_vor_termin"]), date("H:i", $sem_create_data["sem_vor_end_termin"]));
                                    print "</font></td><td width=\"50%\"><font size=\"-1\">";
                                    $resList->reset();
                                    if ($resList->numberOfRooms()) {
                                        print " &nbsp;<select name=\"vor_resource_id\">";
                                        printf ("<option %s value=\"FALSE\">["._("bitte ausw&auml;hlen")."]</option>", (!$sem_create_data["sem_vor_resource_id"]) ? "selected" : "");
                                        while ($res = $resList->next()) {
                                            printf ("<option %s value=\"%s\">%s</option>", ($sem_create_data["sem_vor_resource_id"] == $res["resource_id"]) ? "selected" :"", $res["resource_id"], htmlReady($res["name"]));
                                        }
                                        print "</select><br>";
                                    }
                                    print "</font></td></tr>\n";
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                    <?
                    }
                    if (((is_array($sem_create_data["metadata_termin"]["turnus_data"])) && ($sem_create_data["term_art"] == 0))
                        || (($sem_create_data["term_first_date"]) && ($sem_create_data["term_art"] == 1))
                        || ($sem_create_data["sem_vor_termin"] > -1)) {
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=3>
                            <font size="-1"><b><?=_("freie Angaben zu R&auml;umen"); ?></b></font><br><br>
                            <table border="0" width="100%" cellspaceing="2" cellpadding="0">
                                <?
                                printf ("<font size=\"-1\">"._("%sSie k&ouml;nnen zu jedem Termin freie Angaben zu Raum bzw. Ort machen:")."</font><br>", (($RESOURCES_ENABLE && $resList->roomsExist()) ? "<i><u>"._("oder:")."</u></i>&nbsp;" : ""));
                                if ($sem_create_data["term_art"] == 0) {
                                    if (is_array($sem_create_data["metadata_termin"]["turnus_data"])) {
                                        foreach ($sem_create_data["metadata_termin"]["turnus_data"] as $val) {
                                            print "<tr><td width=\"50%\"><font size=\"-1\">";
                                            switch ($val["day"]) {
                                                case 1: print _("Montag"); break;
                                                case 2: print _("Dienstag"); break;
                                                case 3: print _("Mittwoch"); break;
                                                case 4: print _("Donnerstag"); break;
                                                case 5: print _("Freitag"); break;
                                                case 6: print _("Samstag"); break;
                                                case 7: print _("Sonntag"); break;
                                            }
                                            printf (" "._("von %02d:%02d Uhr bis %02d:%02d Uhr"), $val["start_stunde"], $val["start_minute"],  $val["end_stunde"], $val["end_minute"]);
                                            print "</font></td><td width=\"50%\"><font size=\"-1\">";
                                            printf ("&nbsp;<input type=\"text\" name=\"term_turnus_room[]\" size=\"30\" maxlength=\"255\" value=\"%s\" /><br>", htmlReady(stripslashes($val["room"])));
                                            print "</font></td></tr>\n";
                                        }
                                    }
                                } elseif ($sem_create_data["term_art"] == 1) {
                                    for ($i=0; $i<$sem_create_data["term_count"]; $i++) {
                                        if (($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_jahr"][$i]) && ($sem_create_data["term_start_stunde"][$i] !== '') && ($sem_create_data["term_end_stunde"][$i] !== '')) {
                                            print "<tr><td width=\"50%\"><font size=\"-1\">";
                                            printf (_("am %02d.%02d.%s von %02d:%02d Uhr bis %02d:%02d Uhr"), $sem_create_data["term_tag"][$i], $sem_create_data["term_monat"][$i], $sem_create_data["term_jahr"][$i], $sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], $sem_create_data["term_end_stunde"][$i], $sem_create_data["term_end_minute"][$i]);
                                            print "</font></td><td width=\"50%\"><font size=\"-1\">";
                                            printf ("&nbsp;<input type=\"text\" name=\"term_room[%s]\" size=\"30\" maxlength=\"255\" value=\"%s\" />", $i, htmlReady(stripslashes($sem_create_data["term_room"][$i])));
                                            print "</font></td></tr>\n";
                                        }
                                    }

                                }
                                if ($sem_create_data["sem_vor_termin"] > -1) {
                                    print "<tr><td width=\"50%\"><font size=\"-1\">";
                                    printf (" "._("Vorbesprechung am %s von %s Uhr bis %s Uhr"), date("d.m.Y", $sem_create_data["sem_vor_termin"]), date("H:i", $sem_create_data["sem_vor_termin"]), date("H:i", $sem_create_data["sem_vor_end_termin"]));
                                    print "</font></td><td width=\"50%\"><font size=\"-1\">";
                                    printf ("&nbsp;<input type=\"text\" name=\"vor_raum\" size=\"30\" maxlength=\"255\" value=\"%s\" /><br>", htmlReady(stripslashes($sem_create_data["sem_vor_raum"])));
                                    print "</font></td></tr>\n";
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                    <?
                    } else {
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">

                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%"  colspan=3>
                            <font size="-1">
                            <?=_("Sie k&ouml;nnen hier eine unspezifische Ortsangabe machen:")?><br>
                            <textarea name="sem_room" cols=58 rows="4"><? echo  htmlReady(stripslashes($sem_create_data["sem_room"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Sie k�nnen hier einen Ort eingeben, der nur angezeigt wird, wenn keine genaueren Angaben aus Zeiten oder Sitzungsterminen gemacht werden k�nnen oder Sitzungstermine bereits abgelaufen sind und aus diesem Grund nicht mehr angezeigt werden."), TRUE, TRUE) ?>
                            >
                            <br><?=_("<b>Achtung:</b> Diese Ortsangabe wird nur angezeigt, wenn keine genaueren Angaben aus Zeiten oder Sitzungsterminen gemacht werden k&ouml;nnen.");?>
                        </td>
                    </tr>
                    <?
                    }
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="4%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="96%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                </table>
            </form>
            </td>
        </tr>
    </table>
    <?
    }


//Level 5: Hier wird der Rest abgefragt
if ($level == 5)
    {


    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr >
            <td class="blank" colspan=2 >&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <tr>
            <td class="blank" valign="top">
                <blockquote>
                <b><?=_("Schritt 5: Sonstige Daten zu der Veranstaltung"); ?></b><br><br>
                <font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
                </blockquote>
            </td>
            <td class="blank" align="right" valign="top">
                <img src="<?= localePictureUrl('hands05.jpg') ?>" border="0">
            </td>
        </tr>
        <tr>
            <td class="blank" colspan=2>
            <form method="POST" name="form_5" action="<? echo URLHelper::getLink() ?>">
            <input type="HIDDEN" name="form" value=5>
                <table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?= _("Anmeldezeitraum:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            <font size=-1>&nbsp;
                                <? print _("Bitte geben Sie hier ein Datum an, ab wann und bis wann sich Teilnehmer f�r die Veranstaltung eintragen d&uuml;rfen."); ?>
                                <br>&nbsp;
                                <? print _("Wenn sich die Teilnehmer sofort nach erstellen dieser Veranstaltung eintragen d&uuml;rfen, lassen Sie das Datum einfach unver&auml;ndert."); ?>
                                <br>&nbsp;
                                <? print _("Wenn es kein Ende der Anmeldefrist geben soll, lassen Sie das Enddatum unver&auml;ndert."); ?>
                                <br><br>
                            </font>
                            <table align="right" width="98%" border="0" cellpadding="2" cellspacing="0">
                                <tr>
                                    <td class="<? echo $cssSw->getClass() ?>" valign="top" align="right" width="10%">
                                        <font size=-1><? echo _("Startdatum f&uuml;r Anmeldungen");?>:</font>
                                    </td>
                                    <td class="<? echo $cssSw->getClass() ?>" valign="top" width="40%">
                                        <font size=-1>&nbsp; <input type="text" name="adm_s_tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_start_date"]<>-1) echo date("d",$sem_create_data["sem_admission_start_date"]); else echo _("tt") ?>">.
                                        <input type="text" name="adm_s_monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_start_date"]<>-1) echo date("m",$sem_create_data["sem_admission_start_date"]); else echo _("mm") ?>">.
                                        <input type="text" name="adm_s_jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_admission_start_date"]<>-1) echo date("Y",$sem_create_data["sem_admission_start_date"]); else echo _("jjjj") ?>"><?=_("um");?>&nbsp;</font><br>
                                        <font size=-1>&nbsp; <input type="text" name="adm_s_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_start_date"]<>-1) echo date("H",$sem_create_data["sem_admission_start_date"]); else echo _("hh") ?>">:
                                        <input type="text" name="adm_s_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_start_date"]<>-1) echo date("i",$sem_create_data["sem_admission_start_date"]); else echo _("mm") ?>">&nbsp;<?=_("Uhr");?></font>&nbsp;
                                        <?=Termin_Eingabe_javascript(20,0,($sem_create_data["sem_admission_start_date"] != -1 ? $sem_create_data["sem_admission_start_date"] : 0),'','','','','&form_name=form_5');?>
                                        <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                            <? echo tooltip(_("Teilnehmer d�rfen sich erst ab diesem Datum in die Veranstaltung eintragen."), TRUE, TRUE) ?>

                                        >
                                    </td>
                                    <td class="<? echo $cssSw->getClass() ?>" valign="top" align="right" width="10%">
                                        <font size=-1><? echo _("Enddatum f&uuml;r Anmeldungen");?>:</font>
                                    </td>
                                    <td class="<? echo $cssSw->getClass() ?>" valign="top" width="40%">
                                        <font size=-1>&nbsp; <input type="text" name="adm_e_tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_end_date"]<>-1) echo date("d",$sem_create_data["sem_admission_end_date"]); else echo _("tt") ?>">.
                                        <input type="text" name="adm_e_monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_end_date"]<>-1) echo date("m",$sem_create_data["sem_admission_end_date"]); else echo _("mm") ?>">.
                                        <input type="text" name="adm_e_jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_admission_end_date"]<>-1) echo date("Y",$sem_create_data["sem_admission_end_date"]); else echo _("jjjj") ?>"><?=_("um");?>&nbsp;</font><br>
                                        <font size=-1>&nbsp; <input type="text" name="adm_e_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_end_date"]<>-1) echo date("H",$sem_create_data["sem_admission_end_date"]); else echo "23" ?>">:
                                        <input type="text" name="adm_e_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_end_date"]<>-1) echo date("i",$sem_create_data["sem_admission_end_date"]); else echo "59" ?>">&nbsp;<?=_("Uhr");?></font>&nbsp;
                                        <?=Termin_Eingabe_javascript(21,0,($sem_create_data["sem_admission_end_date"] != -1 ? $sem_create_data["sem_admission_end_date"] : 0),'','','','','&form_name=form_5');?>
                                        <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                            <? echo tooltip(_("Teilnehmer d�rfen sich nur bis zu diesem Datum in die Veranstaltung eintragen."), TRUE, TRUE) ?>
                                        >
                                    </td>
                                    <td class="<? echo $cssSw->getClass() ?>" >
                                    <?=makeButton('loeschen', 'input' , _("Start- und Enddatum zur�cksetzen"), 'reset_admission_time')?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?
                    if (($sem_create_data["sem_sec_lese"] ==2) || ($sem_create_data["sem_sec_schreib"] ==2)) {
                        ?>
                        <tr <? $cssSw->switchClass() ?>>
                            <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                                <?=_("Passwort f&uuml;r Freischaltung:"); ?>
                            </td>
                            <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>&nbsp;
                                <?
                                    if (($sem_create_data["sem_pw"]=="") || ($sem_create_data["sem_pw"] == md5("")))
                                        echo "<input type=\"password\" name=\"sem_passwd\" size=12 maxlength=31> <font size='+2' color='red'>*</font>&nbsp; "._("Passwort-Wiederholung:")."&nbsp; <input type=\"password\" name=\"sem_passwd2\" size=12 maxlength=31>";
                                    else
                                        echo "<input type=\"password\" name=\"sem_passwd\" size=12 maxlength=31 value=\"*******\"> <font size='+2' color='red'>*</font>&nbsp; "._("Passwort-Wiederholung:")."&nbsp; <input type=\"password\" name=\"sem_passwd2\" size=12 maxlength=31 value=\"*******\">";
                                ?>
                                <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                    <? echo tooltip(_("Bitte geben Sie hier ein Passwort f�r die Veranstaltung sowie dasselbe Passwort nochmal zur Best�tigung ein. Dieses wird sp�ter von den Teilnehmenden ben�tigt, um die Veranstaltung abonnieren zu k�nnen."), TRUE, TRUE) ?>
                                >
                                <font size="+2" color="red">*</font>
                            </td>
                        </tr>
                        <?
                    }
                    if ($sem_create_data["sem_admission"]) {
                        $num_all = $sem_create_data["sem_turnout"];
                        if (is_array($sem_create_data["sem_studg"]) && $sem_create_data["sem_turnout"]){
                            foreach ($sem_create_data["sem_studg"] as $key => $val){
                                if ($val["ratio"] && $key != 'all') {
                                    $num_stg[$key] = round($sem_create_data["sem_turnout"] * $val["ratio"] / 100);
                                    $num_all -= $num_stg[$key];
                                }
                            }
                            if ($num_all < 0) $num_all = 0;
                            $num_stg['all'] = $num_all;
                        }
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Anmeldeverfahren:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            <font size=-1>&nbsp;<?
                            if ($sem_create_data["sem_admission"] == 1)
                                print _("Sie haben vorher das Stud.IP Anmeldeverfahren nach dem Losverfahren aktiviert.");
                            else
                                print _("Sie haben vorher das Stud.IP Anmeldeverfahren nach der Reihenfolge der Anmeldungen aktiviert.");
                            ?><br>
                            &nbsp;<?
                            if ($sem_create_data["sem_admission"] == 1)
                                print _("Bitte geben Sie hier an, welche Studieng&auml;nge mit welchen Kontingenten zugelassen sind und wann das Losdatum ist:");
                            elseif (!$sem_create_data["admission_enable_quota"])
                                print _("Bitte geben Sie hier an, welche Studieng&auml;nge zugelassen sind:");
                            else
                                print _("Bitte geben Sie hier an, welche Studieng&auml;nge mit welchen Kontingenten zugelassen sind und wann das Enddatum der Kontingentierung ist:");
                            ?><br><br>
                                <table border="0" cellpadding="2" cellspacing="0">
                                    <tr>
                                    <td class="<? echo $cssSw->getClass() ?>" colspan="4" >
                                    <input style="vertical-align:middle;" type="checkbox" name="admission_enable_quota" <?=($sem_create_data["admission_enable_quota"] ? 'checked' : '')?> value="1">
                                    <font size=-1><?=_("Prozentuale Kontingentierung aktivieren.")."</font>"?>
                                    &nbsp;&nbsp;
                                    <?=makeButton('ok','input',_("Kontingentierung aktivieren/deaktivieren"), 'toggle_admission_quota')?>
                                    </td>
                                    </tr>
                                    <tr>
                                        <td class="<? echo $cssSw->getClass() ?>" valign="bottom" width="40%">
                                        <font size=-1>&nbsp;
                                        </font>
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" valign="bottom"  nowrap width="5%">
                                        &nbsp;
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" valign="top" align="right" width="25%">
                                            <font size=-1><? if ($sem_create_data["sem_admission"] == 1) echo _("Losdatum").':'; elseif($sem_create_data["admission_enable_quota"]) echo _("Enddatum der Kontingentierung").':';?>&nbsp;</font>
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" valign="top" width="45%">
                                        <?if($sem_create_data["sem_admission"] == 1 || $sem_create_data["admission_enable_quota"]){?>
                                            <font size=-1>&nbsp; <input type="text" name="adm_tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("d",$sem_create_data["sem_admission_date"]); else echo _("tt") ?>">.
                                            <input type="text" name="adm_monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("m",$sem_create_data["sem_admission_date"]); else echo _("mm") ?>">.
                                            <input type="text" name="adm_jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("Y",$sem_create_data["sem_admission_date"]); else echo _("jjjj") ?>"><?=_("um");?>&nbsp;</font><br>
                                            <font size=-1>&nbsp; <input type="text" name="adm_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("H",$sem_create_data["sem_admission_date"]); else echo"23" ?>">:
                                            <input type="text" name="adm_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("i",$sem_create_data["sem_admission_date"]); else echo"59" ?>">&nbsp;<?=_("Uhr");?></font>&nbsp;
                                            <?=Termin_Eingabe_javascript(22,0,($sem_create_data["sem_admission_date"] != -1 ? $sem_create_data["sem_admission_date"] : 0),'','','','','&form_name=form_5');?>
                                            <?
                                            if ($sem_create_data["sem_admission"] == 1) {
                                            ?>
                                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                                <? echo tooltip(_("Bitte geben Sie hier ein, wann die Anw�rter auf der Anmeldeliste in die Veranstaltung gelost werden. Freigebliebene Pl�tze werden nach diesem Termin per Warteliste an andere interessierte Personen vergeben."), TRUE, TRUE) ?>
                                            >
                                            <?
                                            } else {
                                            ?>
                                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                                <? echo tooltip(_("Bitte geben Sie hier ein, wann das Anmeldeverfahren die Kontingentierung aufheben soll. Ab diesem Zeitpunkt werden freie Pl�tze an interessierten Personen aus der Warteliste vergeben."), TRUE, TRUE) ?>
                                            >
                                            <?
                                            }
                                        } else echo '&nbsp;';
                                        ?>
                                        </td>
                                    </tr>
                                    <?
                                    if (count($sem_create_data["sem_studg"])) {
                                        foreach ($sem_create_data["sem_studg"] as $key=>$val) {
                                    ?>
                                    <tr>
                                        <td class="<? echo $cssSw->getClass() ?>" width="25%">
                                        <font size=-1>&nbsp;
                                        <?
                                        echo (htmlReady(my_substr($val["name"], 0, 40)));
                                        ?>
                                        </font>
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" nowrap width="5%">
                                        <input type="HIDDEN" name="sem_studg_id[]" value="<? echo $key ?>" />
                                        <input type="HIDDEN" name="sem_studg_name[]" value="<? echo $val["name"] ?>" />
                                        <?
                                        if($sem_create_data["admission_enable_quota"]){
                                            printf ("<input type=\"HIDDEN\" name=\"sem_studg_ratio_old[]\" value=\"%s\" />", $val["ratio"]);
                                            printf ("<input type=\"TEXT\" name=\"sem_studg_ratio[]\" size=5 maxlength=5 value=\"%s\" /><font size=-1> %% (%s Teilnehmer)</font>", $val["ratio"], $num_stg[$key]);
                                            printf ("&nbsp; <a href=\"%s\"><img border=0 src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("Den Studiengang aus der Liste l�schen"))." />", URLHelper::getLink("?sem_delete_studg=".$key));
                                        } else {
                                            printf ("&nbsp; <a href=\"%s\"><img border=0 src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("Den Studiengang aus der Liste l�schen"))." />", URLHelper::getLink("?sem_delete_studg=".$key));
                                        }
                                        ?>
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" width="70%" colspan=2>&nbsp;
                                        </td>
                                    </tr>
                                    <?
                                        }
                                    }
                                    $db->queryf("SELECT * FROM studiengaenge WHERE studiengang_id NOT IN ('%s') ORDER BY name", join("','", array_keys($sem_create_data["sem_studg"])));
                                    $stg = array();
                                    if(!isset($sem_create_data["sem_studg"]['all'])){
                                        $stg[] = array('name' => _("Alle Studieng�nge"), 'studiengang_id' => 'all');
                                    }
                                    while($db->next_record()){
                                        $stg[] = $db->Record;
                                    }
                                    if (count($stg)) {
                                    ?>
                                    <tr>
                                        <td class="<? echo $cssSw->getClass() ?>" width="25%">
                                        <font size=-1>&nbsp;
                                        <select name="sem_add_studg">
                                        <option value="">-- <?=_("bitte ausw&auml;hlen"); ?> --</option>
                                    <?
                                    foreach($stg as $s) {
                                    printf ("<option value=%s>%s</option>", $s["studiengang_id"], htmlReady(my_substr($s["name"], 0, 40)));
                                    }
                                    ?>
                                        </select>
                                        </font>
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" nowrap width="5%">
                                        <?if($sem_create_data["admission_enable_quota"]){?>
                                            <input type="TEXT" name="sem_add_ratio" size=5 maxlength=5 /><font size=-1> %</font>
                                        <?} else echo '&nbsp;';?>
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" width="25%">
                                            <input type="IMAGE" <?=makeButton("hinzufuegen", "src"); ?> name="add_studg" border=0 />&nbsp;
                                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                                <? echo tooltip(_("Bitte geben Sie hier ein, f�r welche Studieng�nge die Veranstaltung mit welchen Kontingenten beschr�nkt sein soll und bis wann eine Anmeldung �ber das Stud.IP Anmeldeverfahren m�glich ist."), TRUE, TRUE) ?>
                                            >
                                        </td>
                                        <td class="<? echo $cssSw->getClass() ?>" width="40%">&nbsp;
                                        </td>
                                    </tr>
                                    <?
                                    }
                                    ?>
                                </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            <?
                            printf (_("%sAchtung: %sWenn Sie ein Anmeldeverfahren starten, so kann dieser Schritt sp&auml;ter nicht r&uuml;ckg&auml;ngig gemacht werden.")." <br>&nbsp; "._("Sie k&ouml;nnen jedoch die Anzahl der Teilnehmer jederzeit unter <i>Grunddaten</i> anpassen.")."</font> ", "<font size=-1 color=\"red\">&nbsp; ", "</font><font size=-1>");
                            ?>
                        </td>
                    </tr>
                    <?
                    }

                    if ($sem_create_data["sem_payment"]=="1") { ?>
                    <tr<?$cssSw->switchClass()?>>
                        <td class ="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <? echo _("Hinweistext bei vorl&auml;ufigen Eintr&auml;gen:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" colspan=3>
                            &nbsp;&nbsp;<textarea name="sem_paytxt" cols=58 rows=4><? echo htmlReady(stripslashes($sem_create_data["sem_paytxt"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                            <? echo tooltip(_("Dieser Hinweistext erl�utert Ihren TeilnehmerInnen was sie tun m�ssen, um endg�ltig f�r die Veranstaltung zugelassen zu werden. Beschreiben Sie genau, wie Beitr�ge zu entrichten sind, Leistungen nachgewiesen werden m�ssen, etc."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>

                    <?
                    }

                    if (!$SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) {
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Teilnehmer- beschreibung:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            &nbsp; <textarea name="sem_teiln" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_teiln"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Bitte geben Sie hier ein, f�r welchen Teilnehmerkreis die Veranstaltung geeignet ist."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Voraussetzungen:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            &nbsp; <textarea name="sem_voraus" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_voraus"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Bitte geben Sie hier ein, welche Voraussetzungen f�r die Veranstaltung n�tig sind."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Lernorganisation:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            &nbsp; <textarea name="sem_orga" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_orga"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Bitte geben Sie hier ein, mit welcher Lernorganisation die Veranstaltung durchgef�hrt wird."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Leistungsnachweis:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            &nbsp; <textarea name="sem_leistnw" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_leistnw"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <?= tooltip(_("Bitte geben Sie hier ein, welche Leistungsnachweise erbracht werden m�ssen."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <?
                    }
                    //add the free adminstrable datafields
                    $dataFieldStructures = DataFieldStructure::getDataFieldStructures('sem', $sem_create_data['sem_class'], true);
                    foreach ($dataFieldStructures as $id=>$struct) {
                        if ($struct->accessAllowed($perm)) {
                            ?>
                            <tr <? $cssSw->switchClass() ?>>
                                <td class="<?= $cssSw->getClass() ?>" width="10%" align="right">
                                    <?=htmlReady($struct->getName()) ?>
                                </td>
                                <td class="<?= $cssSw->getClass() ?>" width="90%" colspan=3>
                                    <?
                                    if ($perm->have_perm($struct->getEditPerms())) {
                                        $entry = DataFieldEntry::createDataFieldEntry($struct, '', stripslashes($sem_create_data["sem_datafields"][$id]['value']));
                                        print "&nbsp;&nbsp;".$entry->getHTML("sem_datafields");
                                    } else {
                                    ?>
                                    &nbsp;<font size="-1"><?=_("Diese Daten werden von ihrem zust&auml;ndigen Administrator erfasst.")?></font>
                                    <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                        <? echo tooltip(_("Diese Felder werden zentral durch die zust�ndigen Administratoren erfasst."), TRUE, TRUE) ?>
                                    >
                                    <?
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?
                        }
                    }
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Sonstiges:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                            &nbsp; <textarea name="sem_sonst" cols=58 rows=<? if ($SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) echo "10"; else echo "4" ?>><? echo  htmlReady(stripslashes($sem_create_data["sem_sonst"])) ?></textarea>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Hier ist Platz f�r alle sonstigen Informationen zur Veranstaltung."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <?
                    if (($sem_create_data["term_start_woche"]==-1) && ($sem_create_data["term_art"] == 0))
                        {
                        ?>
                        <tr <? $cssSw->switchClass() ?>>
                            <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                                <?=_("erster Termin:"); ?>
                            </td>
                            <td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
                                <font size=-1>&nbsp; <font size=-1><?=_("Sie haben angegeben, an einem anderen Zeitpunkt mit der Veranstaltung zu beginnen. Bitte geben Sie hier den ersten Termin ein."); ?></font><br><br>&nbsp; <?=_("Datum:"); ?> </font>
                                <font size=-1><input type="text" name="tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("d",$sem_create_data["sem_start_termin"]); else echo _("tt") ?>">.
                                <input type="text" name="monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("m",$sem_create_data["sem_start_termin"]); else echo _("mm") ?>">.
                                <input type="text" name="jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("Y",$sem_create_data["sem_start_termin"]); else echo _("jjjj") ?>">&nbsp; </font>
                                <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                    <? echo tooltip(_("Bitte geben Sie hier ein, wann der erste Termin der Veranstaltung stattfindet."), TRUE, TRUE) ?>
                                >
                            </td>
                        </tr>
                        <?
                        }
                    ?>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            &nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                        </td>
                    </tr>
                </table>
            </form>
            </td>
        </tr>
    </table>
    <?
    }

//Level 6: Seminar anlegen
if ($level == 6)
    {
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td class="blank" colspan=2>&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <tr>
            <td class="blank" valign="top">
                <blockquote>
                <b><?=_("Schritt 6: Bereit zum Anlegen der Veranstaltung"); ?></b><br><br>
                <?=_("Sie haben nun alle n&ouml;tigen Daten zum Anlegen der Veranstaltung eingegeben. Wenn Sie auf &raquo;anlegen&laquo; klicken, wird die Veranstaltung in Stud.IP &uuml;bernommen. Wenn Sie sich nicht sicher sind, ob alle Daten korrekt sind, &uuml;berpr&uuml;fen Sie noch einmal Ihre Eingaben auf den vorhergehenden Seiten."); ?><br><br>
                <form method="POST" action="<? echo URLHelper::getLink() ?>">
                    <input type="HIDDEN" name="form" value=6>
                    <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?> >>" name="jump_back">&nbsp;<input type="IMAGE" <?=makeButton("anlegen", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                </form>
                </blockquote>
            </td>
            <td class="blank" align="right" valign="top">
                <img src="<?= localePictureUrl('hands06.jpg') ?>" border="0">
            </td>
        </tr>
    </table>
    <?
    }

//Level 6:Statusmeldungen nach dem Anlegen und weiter zum den Einzelheiten
if ($level == 7)
    {
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td class="blank" colspan=2>&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <?
        if (!$successful_entry)
            { ?>
            <tr>
                <td class="blank">
                    <blockquote>
                    <b><?=_("Die Veranstaltung konnte nicht angelegt werden."); ?></b><br><br>
                    <?=_("Bitte korrigieren Sie die Daten."); ?>
                    <form method="POST" action="<? echo URLHelper::getLink() ?>">
                        <input type="HIDDEN" name="form" value=7>
                        <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="jump_back">
                    </form>
                    </blockquote>
                </td>
                <td class="blank" align="right">
                    <img src="<?= localePictureUrl('hands06.jpg') ?>" border="0">
                </td>
            </tr> <?
            }
        elseif ($successful_entry==2)
            { ?>
            <tr>
                <td class="blank" valign="top">
                    <blockquote>
                    <?
                    print _("Sie haben die Veranstaltung bereits angelegt.");
                    if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["scm"])) {
                        if (($sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["scm"]))
                            print " "._("Sie k&ouml;nnen nun mit der Informationsseite und dem Termin-Assistenten fortfahren oder an diesem Punkt abbrechen.");
                        if (($sem_create_data["modules_list"]["schedule"]) && (!$sem_create_data["modules_list"]["scm"]))
                            print " "._("Sie k&ouml;nnen nun mit dem Termin-Assistenten fortfahren oder an diesem Punkt abbrechen.");
                        if ((!$sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["scm"]))
                            print " "._("Sie k&ouml;nnen nun mit der Informationsseite fortfahren oder an diesem Punkt abbrechen.");
                        print "<br><br><font size=-1>"._("Sie haben jederzeit die M&ouml;glichkeit, die bereits erfassten Daten zu &auml;ndern und diese Schritte sp&auml;ter nachzuholen.")."</font>";
                    }
                    ?>
                    <br><br>
                    <form method="POST" action="<? echo URLHelper::getLink() ?>">
                        <input type="HIDDEN" name="form" value=7>
                        <input type="IMAGE" <?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("abbrechen");?>" name="cancel">
                        <?
                        if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["scm"])) {
                            ?>
                            &nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                            <?
                        }
                        ?>
                    </form>
                    </blockquote>
                </td>
                <td class="blank" align="right">
                    <img src="<?= localePictureUrl('hands06.jpg') ?>" border="0">
                </td>
            </tr> <?
            }

        else
            { ?>
            <tr>
                <td class="blank" valign="top">
                    <blockquote>
                    <b><?=_("Die Daten der Veranstaltung wurden in das System &uuml;bernommen"); ?></b><br><br>
                    <?
                    print _("Die Veranstaltung ist jetzt eingerichtet.");
                    if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["scm"])) {
                        print " "._("Wenn Sie nun auf &raquo;weiter >>&laquo; klicken, k&ouml;nnen Sie weitere -optionale- Daten f&uuml;r die Veranstaltung eintragen.");
                        if (($sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["scm"]))
                            print " "._("Sie haben die M&ouml;glichkeit, eine Informationsseite anzulegen und k&ouml;nnen mit Hilfe des Termin-Assisten einen Ablaufplan erstellen.");
                        if (($sem_create_data["modules_list"]["schedule"]) && (!$sem_create_data["modules_list"]["scm"]))
                            print " "._("Sie haben die M&ouml;glichkeit, mit Hilfe des Termin-Assisten einen Ablaufplan zu erstellen.");
                        if ((!$sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["scm"]))
                            print " "._("Sie haben die M&ouml;glichkeit,  eine Informationsseite anzulegen.");
                        print "<br><br><font size=-1>"._("Sie haben jederzeit die M&ouml;glichkeit, die bereits erfassten Daten zu &auml;ndern und die n&auml;chsten Schritte sp&auml;ter nachzuholen.")."</font>";
                    }
                    ?><br><br>
                    <form method="POST" action="<?= URLHelper::getLink() ?>">
                        <input type="HIDDEN" name="form" value=7>
                        <?
                        if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["scm"])) {
                            ?>
                            <input type="IMAGE" <?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("abbrechen");?>" name="cancel">
                            &nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                            <?
                        }
                        ?>
                    </form>
                    </blockquote>
                </td>
                <td class="blank" align="right" valign="top">
                    <img src="<?= localePictureUrl('hands06.jpg') ?>" border="0">
                </td>
            </tr>
            <tr>
                <td class="blank" colspan=2>
                <br>
                <form method="POST" action="<? echo URLHelper::getLink() ?>">
                    <table width ="60%" cellspacing=1 cellpadding=1>
                        <tr>
                            <td width="10%" class="blank">&nbsp; </td>
                            <td width="90%" class="rahmen_steel">
                            <?
                            printf ("<br><br><ul><li>"._("Veranstaltung <b>%s</b> erfolgreich angelegt.")."<br><br>", htmlReady(stripslashes($sem_create_data["sem_name"])));
                            if ($count_bet_inst==1)
                                print "<li>"._("Veranstaltung f&uuml;r <b>1</b> beteiligte Einrichtung angelegt.")."<br><br>";
                            elseif ($count_bet_inst>1)
                                printf ("<li>"._("Veranstaltung f&uuml;r <b>%s</b> beteiligte Einrichtungen angelegt.")."<br><br>", $count_bet_inst);
                            printf("<li>"._("<b>%d</b> %s f&uuml;r die Veranstaltung eingetragen.")."<br><br>", $count_doz, get_title_for_status('dozent', $count_doz, $sem_create_data["sem_status"]));
                            printf("<li>"._("<b>%d</b> %s f&uuml;r die Veranstaltung eingetragen.")."<br><br>", $count_tut, get_title_for_status('tutor', $count_tut, $sem_create_data["sem_status"]));
                            if ($count_doms==1)
                                print "<li>"._("<b>1</b> Nutzerdom&auml;ne f&uuml;r die Veranstaltung eingetragen.")."<br><br>";
                            elseif ($count_doms>1)
                                printf ("<li>"._("<b>%s</b> Nutzerdom&auml;nen f&uuml;r die Veranstaltung eingetragen.")."<br><br>", $count_doms);
                            if ($count_bereich==1)
                                print "<li>"._("<b>1</b> Bereich f&uuml;r die Veranstaltung eingetragen.")."<br><br>";
                            elseif ($count_bereich)
                                printf ("<li>"._("<b>%s</b> Bereiche f&uuml;r die Veranstaltung eingetragen.")."<br><br>", $count_bereich);
                            //Show the result from the resources system
                            if ($RESOURCES_ENABLE) {
                                if (is_array($updateResult))
                                    foreach ($updateResult as $key=>$val) {
                                        if ($val["resource_id"]) {
                                            if ($val["overlap_assigns"] == TRUE)
                                                $resources_failed[$val["resource_id"]]=TRUE;
                                            else
                                                $resources_booked[$val["resource_id"]]=TRUE;
                                        }
                                    }
                                if ($resources_booked) {
                                    $i=0;
                                    $rooms='';
                                    foreach ($resources_booked as $key=>$val) {
                                        $resObj = ResourceObject::Factory($key);
                                        if ($i)
                                            $rooms.=", ";
                                        $rooms.= $resObj->getFormattedLink();
                                        $i++;
                                    }
                                    if (sizeof($resources_booked) == 1)
                                        printf ("<li>"._("Die Belegung des Raums %s wurde in die Ressourcenverwaltung eingetragen.")."<br><br>", $rooms);
                                    else
                                        printf ("<li>"._("Die Belegung der R&auml;ume %s wurde in die Ressourcenverwaltung eingetragen."). "<br><br>", $rooms);
                                }
                                if ($resources_failed) {
                                    $i=0;
                                    $rooms='';
                                    foreach ($resources_failed as $key=>$val) {
                                        $resObj = ResourceObject::Factory($key);
                                        if ($i)
                                            $rooms.=", ";
                                        $rooms.= $resObj->getFormattedLink();
                                        $i++;
                                    }
                                    if (sizeof($resources_failed) == 1)
                                        printf ("<li><font color=\"red\">"._("Eine oder mehrere Belegungen des Raumes %s konnte wegen &Uuml;berschneidungen nicht in die Ressourcenverwaltung eingetragen werden!")."<br>", $rooms);
                                    else
                                        printf ("<li><font color=\"red\">"._("Eine oder mehrere Belegungen der R&auml;ume %s konnten wegen &Uuml;berschneidungen nicht in die Ressourcenverwaltung eingetragen werden!")."<br>", $rooms);
                                    print _("Bitte &uuml;berpr&uuml;fen Sie manuell die Belegungen!")."</font><br><br>";
                                }
                            }

                            echo "</ul>";
                            ?>
                            </td>
                        </tr>
                    </table>
                    <br>
                    <br>
                </form>
                </td>
            </tr>
            <?
            }
            ?>
    </table>
    <?
    }

//Level 8: Erstellen des Simple-Content-Bereichs
if ($level == 8)
    {
    ?>
    <table width="100%" border=0 cellpadding=0 cellspacing=0>
        <tr>
            <td class="blank" colspan=2>&nbsp;
                <?
                if ($errormsg) parse_msg($errormsg);
                ?>
            </td>
        </tr>
        <tr>
            <td class="blank" valign="top">
                <blockquote>
                <b><?=_("Schritt 7: Erstellen einer Informationsseite"); ?></b><br><br>
                <? printf (_("Sie k&ouml;nnen nun eine frei gestaltbare Infomationsseite f&uuml;r die eben angelegte Veranstaltung <b>%s</b> eingeben."), $sem_create_data["sem_name"]);
                print "<br>"._("Sie k&ouml;nnen die Bezeichnug dieser Seite frei bestimmten. Nutzen sie Sie etwa, um ungeordnete Literaturlisten oder weitere Informationen anzugeben.");
                if ($sem_create_data["modules_list"]["schedule"])
                    print "<br> "._("Wenn Sie auf &raquo;weiter&laquo; klicken, haben Sie die M&ouml;glichkeit, mit dem Termin-Assistenten einen Ablaufplan f&uuml;r die Veranstaltung anzulegen.")
                ?>
                <br><br>
                </blockquote>
            </td>
            <td class="blank" align="right" valign="top">
                <img src="<?= localePictureUrl('hands07.jpg') ?>" border="0">
            </td>
        </tr>
        <tr>
            <td class="blank" colspan=2>
            <form method="POST" name="form_8" action="<? echo URLHelper::getLink() ?>">
            <input type="HIDDEN" name="form" value=8>
                <table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
                    <tr<? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            <input type="IMAGE"<?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("abbrechen");?>" name="cancel">
                            <?
                            if ($sem_create_data["modules_list"]["schedule"]) {
                                ?>
                                &nbsp;<input type="IMAGE"<?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                                <?
                            } else {
                                ?>
                                &nbsp;<input type="IMAGE"<?=makeButton("uebernehmen", "src"); ?> border=0 value="<?=_("uebernehmen");?>" name="jump_next">
                                <?
                            }
                            ?>
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Titel:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
                            &nbsp;
                            <select name="sem_scm_preset">
                                <? foreach ($SCM_PRESET as $key=>$val)
                                    printf ("<option value=\"%s\" %s>%s</option>\n", $key, ($sem_create_data["sem_scm_preset"] == $key) ? "selected": "", $val["name"]);
                                ?>
                            </select>&nbsp; <?=_("oder geben Sie einen beliebigen Titel ein:") ?>
                            <input type="TEXT" name="sem_scm_name" value="<?=$sem_create_data["sem_scm_name"]?>" maxlength="20" size="20" />
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("Sie k�nnen entweder einen vordefinierten Titel f�r die freie Kursseite ausw�hlen oder einen eigenen Titel frei w�hlen. Diese Titel erscheint im Reitersystem der Veranstaltung als Bezeichnug des der freien Informationsseite"), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr <? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
                            <?=_("Inhalt der Seite:"); ?>
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="50%"  colspan=2>
                            &nbsp; <textarea name="sem_scm_content" cols=58 rows=10><? echo $sem_create_data["sem_scm_content"] ?></textarea>

                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="40%" valign="top">
                            <?
                            print "<br><font size=\"-1\">"._("Sie k&ouml;nnen auf dieser Seite s&auml;mtliche Stud.IP Formatierungen verwenden. Sie k&ouml;nnen Links normal einegeben, diesen werden automatisch sp&auml;ter als Hyperlinks dargestellt.");

                            if (get_config("EXTERNAL_HELP")) {
                                $help_url=format_help_url("Basis.VerschiedenesFormat");
                            } else {
                                $help_url="help/index.php?help_page=ix_forum6.htm";
                            }
                            print "<br><br><a target=\"_blank\" href=\"".$help_url."\">"._("Hilfe zur Formatierung von Texten")."</a>";
                            print "<br><br>"._("Um eine geordnete Literaturliste zu erstellen, benutzen Sie bitte die Literaturverwaltung.")."</a></font>";
                            ?>
                            <br>
                            <img  src="<?= $GLOBALS['ASSETS_URL'] ?>images/info.gif"
                                <? echo tooltip(_("In dieses Feld k�nnen Sie beliebigen Text zur Anzeige auf der Informationsseite eingeben."), TRUE, TRUE) ?>
                            >
                        </td>
                    </tr>
                    <tr<? $cssSw->switchClass() ?>>
                        <td class="<? echo $cssSw->getClass() ?>" width="10%">
                            &nbsp;
                        </td>
                        <td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
                            <input type="IMAGE"<?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("abbrechen");?>" name="cancel">
                            <?
                            if ($sem_create_data["modules_list"]["schedule"]) {
                                ?>
                                &nbsp;<input type="IMAGE"<?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="jump_next">
                                <?
                            } else {
                                ?>
                                &nbsp;<input type="IMAGE"<?=makeButton("uebernehmen", "src"); ?> border=0 value="<?=_("uebernehmen");?>" name="jump_next">
                                <?
                            }
                            ?>

                        </td>
                    </tr>
                </table>
        </tr>
    </table>
    <?php
    }
//de-initialisations for room-requests
if (is_object($sem_create_data["resRequest"])) {
    $sem_create_data["resRequest"] = serialize ($sem_create_data["resRequest"]);
}
include ('lib/include/html_end.inc.php');
//save all the data back to database
page_close();
?>
