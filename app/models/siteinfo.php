<?php
/*
 * siteinfo - display information about Stud.IP
 *
 * Copyright (c) 2008  Ansgar Bockstiegel
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/visual.inc.php');
require_once('lib/user_visible.inc.php');

class Siteinfo {
    function get_detail_content($id){
        global $perm, $rubrics_empty;
        if($id==0){
            if($perm->have_perm('root')){
                if ($rubrics_empty){
                    return _("Benutzen Sie den Link �neue Rubrik anlegen� in der Infobox, um eine Rubrik anzulegen.");
                }else{
        	        return _("Benutzen Sie den Link �neue Seite anlegen� in der Infobox, um eine Seite in dieser Rubrik anzulegen.");
                }
        	}else{
    	        return _("Der f�r diese Stud.IP-Installation verantwortliche Administrator muss hier noch Inhalte einf�gen.")."<br />".rootlist();
        	}
        }else{
            $db = DBManager::get();
            $sql = "SELECT content
                    FROM siteinfo_details
                    WHERE detail_id = ".$db->quote($id,PDO::PARAM_INT);
            $result = $db->query($sql);
            $rows = $result->fetchAll();
            return $rows[0][0];
        }
    }

    function get_detail_name($id){
        $db = DBManager::get();
        $sql = "SELECT name
                FROM siteinfo_details
                WHERE detail_id = ".$db->quote($id,PDO::PARAM_INT);
        $result = $db->query($sql);
        $rows = $result->fetchAll();
        return $rows[0][0];    
    }

    function get_detail_content_processed($id){
        $content = $this->get_detail_content($id);
        $output = siteinfoDirectives(formatReady(languageReady($content)));
        return $output;
    }

    function save($type, $input){
        $db = DBManager::get();
        switch ($type){
            case "update_detail":
                $db->exec("UPDATE siteinfo_details
                           SET rubric_id = ".$db->quote($input['rubric_id'],PDO::PARAM_INT).",
                               name = ".$db->quote($input['detail_name']?$input['detail_name']:"unbenannt").",
                               content = ".$db->quote($input['content'])."
                           WHERE detail_id=".$db->quote($input['detail_id'],PDO::PARAM_INT));
                $rubric = $input['rubric_id'];
                $detail = $input['detail_id'];
                break;
            case "insert_detail":
                $db->exec("INSERT 
                           INTO siteinfo_details 
                           (rubric_id,
                            name,
                            content)
                           VALUES (".$db->quote($input['rubric_id'],PDO::PARAM_INT).", 
                                   ".$db->quote($input['detail_name']?$input['detail_name']:"unbenannt").", 
                                   ".$db->quote($input['content']).");");
                $rubric = $input['rubric_id'];
                $detail = $db->lastInsertId();
                break;
            case "update_rubric":
                $db->exec("UPDATE siteinfo_rubrics
                           SET name = ".$db->quote($input['rubric_name']?$input['rubric_name']:"unbenannt")."
                           WHERE rubric_id = ".$db->quote($input['rubric_id'],PDO::PARAM_INT).";");
                $rubric = $input['rubric_id'];
                $detail = $this->first_detail_id($rubric);
                break;
            case "insert_rubric":
                $db->exec("INSERT 
                           INTO siteinfo_rubrics
                           (name)
                           VALUES (".$db->quote($input['rubric_name']?$input['rubric_name']:"unbenannt").");");
                $rubric = $db->lastInsertId();
                $detail = 0;
        }
        return array($rubric, $detail);
    }

    function delete($type,$id){
        $db = DBManager::get();
        if($type=="rubric"){
            $db->exec("DELETE FROM siteinfo_details WHERE rubric_id = ".$db->quote($id).";");
            $db->exec("DELETE FROM siteinfo_rubrics WHERE rubric_id = ".$db->quote($id).";");
        }else{
            $db->exec("DELETE FROM siteinfo_details WHERE detail_id = ".$db->quote($id).";");
        }
    }

    function first_detail_id($rubric=NULL){
        $db = DBManager::get();
        $rubric_id = $rubric ? $rubric : $this->first_rubric_id();
        $sql = "SELECT detail_id
                FROM siteinfo_details ";
        if($rubric_id){
            $sql .= "WHERE rubric_id = ".$db->quote($rubric_id,PDO::PARAM_INT);
        }
        $sql .= " ORDER BY position ASC
                 LIMIT 1";
        $result = $db->query($sql);
        $rows = $result->fetchAll();
        if (count($rows)>0){
            return $rows[0][0];
        }else{
            return 0;
        }
    }

    function first_rubric_id(){
        global $rubrics_empty;
        $sql = "SELECT rubric_id
                FROM siteinfo_rubrics
                ORDER BY position ASC
                LIMIT 1";
        $result = DBManager::get()->query($sql);
        $rows = $result->fetchAll();
        if (count($rows)>0){
            return $rows[0][0];
        }else{
            $rubrics_empty = TRUE;
            return NULL;
        }
    }

    function rubric_for_detail($id){
        $db = DBManager::get();
        $sql = "SELECT rubric_id
                FROM siteinfo_details
                WHERE detail_id = ".$db->quote($id,PDO::PARAM_INT);
        $result = $db->query($sql);
        $rows = $result->fetchAll();
        if ($type=="id"){
            return $rows[0][0];
        }else{
            return $rows[0][1];
        }
    }

    function rubric_name($id){
        $db = DBManager::get();
        $sql = "SELECT name
                FROM siteinfo_rubrics
                WHERE rubric_id = ".$db->quote($id,PDO::PARAM_INT);
        $result = $db->query($sql);
        $rows = $result->fetchAll();
        return $rows[0][0];
    }

    function get_all_rubrics(){
        $sql = "SELECT rubric_id, name
                FROM siteinfo_rubrics";
        $result = DBManager::get()->query($sql);
        $rows = $result->fetchAll();
        return $rows;
    }
}


//to preserve (parts?) of the old impressum.php-functionality
//here a modified copy of wiki-engine supports specialized markup
function siteinfoMarkup($pattern, $replace) {
       global $siteinfo_directives;
       $siteinfo_directives[]=array($pattern, $replace);
}
function siteinfoDirectives($str) {
       global $siteinfo_directives; // array of pattern-replace-arrays
       if (is_array($siteinfo_directives)){
               foreach ($siteinfo_directives as $direct) {
                    $str = preg_replace($direct[0],$direct[1],$str);
               }
       }
       return $str;
}


//*******************************
//** Starting to define Markup **
//*******************************
siteinfoMarkup("/\(:logofloater:\)/e",'logoFloater()');
function logoFloater(){
    global $ASSETS_URL;
    return '<div style="float: right;"><img border="0" src="'.$ASSETS_URL.'images/studipanim.gif"/></div>'."\n";
}

siteinfoMarkup("/\(:version:\)/e",'version()');
function version(){
    global $SOFTWARE_VERSION;
    return $SOFTWARE_VERSION;
}

siteinfoMarkup("/\(:versionfloater:\)/e",'versionFloater()');
function versionFloater(){
    global $SOFTWARE_VERSION;
    return '<div style="float:right;"><span style="font-weight:bold;">Version:</span> '.$SOFTWARE_VERSION.'</div>'."\n";
}

siteinfoMarkup("/\(:uniname:\)/e",'uniName()');
function uniname(){
    global $UNI_NAME;
    return $UNI_NAME;
}

siteinfoMarkup("/\(:unicontact:\)/e",'uniContact()');
function unicontact(){
    global $UNI_CONTACT;
    $out = '<a href="mailto:'.$UNI_CONTACT.'">'.$UNI_CONTACT.'</a>';
    return $out;
}

siteinfoMarkup("/\(:userinfo ([a-z_@\-]*):\)/e","userinfo('$1')");
function userinfo($input){
    $db = DBManager::get();
    $sql = "SELECT ".$GLOBALS['_fullname_sql']['full'] ." AS fullname,
                   Email, 
                   username 
            FROM auth_user_md5 
            LEFT JOIN user_info USING (user_id) 
            WHERE username=".$db->quote($input)."
            AND ".get_vis_query(); 
    $result = $db->query($sql);
    if ($result->rowCount()==1){
        $user = $result->fetchAll(PDO::FETCH_ASSOC);
        $out = '<a href="'.URLHelper::getLink('about.php', array('username' => $user[0]['username'])).'">';
        $out .= $user[0]['fullname'];
        $out .= '</a>';
        $out .= ', E-Mail:';
        $out .= formatReady($user[0]['Email']);
    }else{
        $out = _("Nutzer nicht gefunden.");
    }
    return $out;
}
siteinfoMarkup("/\(:rootlist:\)/e",'rootlist()');
function rootlist(){
    $sql = "SELECT ".$GLOBALS['_fullname_sql']['full'] ." AS fullname,
                   Email, 
                   username 
            FROM auth_user_md5 
            LEFT JOIN user_info USING (user_id) 
            WHERE perms='root' 
            AND ".get_vis_query()." 
            ORDER BY Nachname";
    $result = DBManager::get()->query($sql);
    if ($result->rowCount()>0){
        $rootlist = $result->fetchAll(PDO::FETCH_ASSOC);
        $out = "<ul>";
        foreach($rootlist as $listentry){
            $out .= '<li>';
            $out .= '<a href="'.URLHelper::getLink('about.php', array('username' => $listentry['username'])).'">';
            $out .= $listentry['fullname'];
            $out .= '</a>';
            $out .= ', E-Mail:';
            $out .= formatReady($listentry['Email']);
            $out .= '</li>';
        }
        $out .= "</ul>"."\n";
    }else{
        $out = _("keine. Na sowas. Das kann ja eigentlich gar nicht sein...");
    }
    return $out;
}

siteinfoMarkup("/\(:adminlist:\)/e",'adminlist()');
function adminList(){
    $db=DBManager::get();
    $sql = "SELECT Name,
                   Institute.Institut_id
            FROM user_inst
            LEFT JOIN Institute ON (user_inst.institut_id = Institute.Institut_id) 
            WHERE inst_perms='admin'
            GROUP BY Institute.Institut_id
            ORDER BY Name";
    $i_result = $db->query($sql);
    $institutes = $i_result->fetchAll();
    if ($i_result->rowCount()==0){
        return _("keine. Na sowas. Das kann ja eigentlich gar nicht sein...");
    }
    $out = "";
    foreach($institutes as $institute){
        $sql = "SELECT ".$GLOBALS['_fullname_sql']['full'] ." AS fullname,
                       Email, 
                       username
                FROM user_inst
                LEFT JOIN auth_user_md5 USING (user_id)
                LEFT JOIN user_info USING (user_id)
                WHERE inst_perms='admin' 
                AND institut_id = ".$db->quote($institute['Institut_id'],PDO::PARAM_INT)."
                AND ".get_vis_query()." 
                ORDER BY Nachname";


        $out .= '<h4 style="clear: both;margin-bottom: 0px;">';
        $out .= '<a href="'.URLHelper::getLink('institut_main.php', 
                                               array('auswahl' => $institute['Institut_id'])).'">';
        $out .= formatReady($institute['Name']);
        $out .= '</a>';
        $out .= '</h4>'."\n";
        $out .= '<div style="width: 49%; float:left;">'."\n".'<ul>';
        $u_result = $db->query($sql);
        $user = $u_result->fetchAll();
        $switch_to_next_column = $u_result->rowCount()/2;
        $admincount = 0;
        foreach($user as $suser){
            $out .= '<li>';
            $out .= '<a href="'.URLHelper::getLink('about.php', array('username' => $suser['username'])).'">';
            $out .= formatReady($suser['fullname']);
            $out .= '</a>';
            $out .= ', E-Mail:';
            $out .= formatReady($suser['Email']);
            $out .= '</li>';
            $admincount++;
            if ($admincount >= $switch_to_next_column){
                $out .= '</ul>'."\n".'</div>'."\n".'<div style="width: 49%; float: right;">'."\n".'<ul>';
                $admincount = 0;
            }
        }
        $out .= '</ul>'."\n".'</div>'."\n<br style='clear: both'>";
    }
    return $out;
}

siteinfoMarkup("/\(:coregroup:\)/e",'coregroup()');
function coregroup(){
    $cache = StudipCacheFactory::getCache();
    if (!($remotefile = $cache->read('coregroup'))){
        $remotefile = file ('http://www.studip.de/crew.php');
        $cache->write('coregroup', $remotefile);
    }
    $out = implode($remotefile,'');
    $out = substr($out, stripos($out, "<table"), strrpos($out, "</table>"));
    $out = str_replace(array('class="normal"','align="left"'), array("",""), $out);
    $out = $out;
    return $out;
}

siteinfoMarkup("/\(:toplist ([a-z]*):\)/ei","toplist('$1')");
function toplist($item){
    global $_fullname_sql;
    switch($item){
        case "mostparticipants":
            $heading = _("die meisten Teilnehmer");
            $sql = "SELECT seminar_user.seminar_id,
                           seminare.name AS display,
                           count(seminar_user.seminar_id) as count 
                    FROM seminar_user 
                    INNER JOIN seminare USING(seminar_id) 
                    WHERE seminare.visible = 1 
                    GROUP BY seminar_user.seminar_id 
                    ORDER BY count DESC 
                    LIMIT 10";
            $type = "seminar";
            break;
        case "recentlycreated":
            $heading = _("zuletzt angelegt");
            $sql = "SELECT seminare.seminar_id, 
                           seminare.name AS display, 
                           FROM_UNIXTIME(mkdate, '%d.%m.%Y %h:%i:%s') AS count 
                    FROM seminare 
                    WHERE visible = 1 
                    ORDER BY mkdate DESC 
                    LIMIT 10";
            $type = "seminar";
            break;
        case "mostdocuments":
            $heading = _("die meisten Materialien (Dokumente)");
            $sql = "SELECT a.seminar_id, 
                           b.name AS display, 
                           count(a.seminar_id) as count 
                    FROM seminare b  
                    INNER JOIN dokumente a USING(seminar_id) 
                    WHERE b.visible=1 
                    GROUP BY a.seminar_id  
                    ORDER BY count DESC 
                    LIMIT 10";
            $type = "seminar";
            break;
        case "mostpostings":
            $heading = _("die aktivsten Veranstaltungen (Postings der letzten zwei Wochen)");
            $sql = " SELECT a.seminar_id, 
                            b.name AS display, 
                            count( a.seminar_id ) AS count
                     FROM px_topics a
                     INNER JOIN seminare b USING ( seminar_id )
                     WHERE b.visible = 1
                     AND a.mkdate > UNIX_TIMESTAMP( NOW( ) - INTERVAL 2 WEEK )
                     GROUP BY a.seminar_id
                     ORDER BY count DESC
                     LIMIT 10 ";
            $type = "seminar";
            break;
        case "mostvisitedhomepages":
            $heading = _("die beliebtesten Homepages (Besucher)");
            $sql = "SELECT auth_user_md5.user_id, 
                           username, 
                           views as count, 
                         ".$_fullname_sql['full'] . " AS display
                    FROM object_views 
                    LEFT JOIN auth_user_md5 ON(object_id=auth_user_md5.user_id) 
                    LEFT JOIN user_info USING (user_id) 
                    WHERE auth_user_md5.user_id IS NOT NULL
                    ORDER BY count DESC 
                    LIMIT 10";
            $type = "user";
            break;
        default:
            $heading = _("die gew�hlte Option ist nicht verf�gbar");
    }
    if($sql){
        $result = DBManager::get()->query($sql);
	    if  ($result->rowCount() > 0) {
            $lines = $result->fetchAll(PDO::FETCH_ASSOC);
            $out = '<h4>'.$heading.'</h4>';
            $out .= '<ol>';
            foreach($lines as $line){
                $out .= '<li>';
                $out .= '<a href="';
                switch($type){
                    case "seminar":
                        $out .= URLHelper::getLink('details.php', array('sem_id' => $line["seminar_id"],
                                                                        'send_from_search' => 'true',
                                                                        'send_from_search_page' => $view));
                        break;
                    case "user":
                        $out .= URLHelper::getLink('about.php', array('username' => $line["username"]));
                        break;
                    default:
                        $out .= $view;
                }
                $out .= '">';
                $out .= htmlReady($line['display']);
                $out .= '</a>';
                $out .= ' ('.$line['count'].')';
            }
            $out .= '</ol>';
        }
    }else{
        $out = "<h3>".$heading."</h3>";
    }
    return $out;
}

siteinfoMarkup("/\(:indicator ([a-z_\-]*):\)/ei","indicator('$1')");
function indicator($key){
    $db = DBManager::get();
    $key = trim($key);
    $indicator['seminar_all'] = array("query" => "SELECT count(*) from seminare",
                                      "title" => _("Aktive Veranstaltungen"),
                                      "detail" => _("Alle Veranstaltungen, die nicht archiviert wurden."));
    $indicator['seminar_archived'] = array("query" => "SELECT count(*) from archiv",
                                           "title" => _("Archivierte Veranstaltungen"),
                                           "detail" => _("Alle Veranstaltungen, die archiviert wurden."));
    $indicator['institute_secondlevel_all'] = array("query" => "SELECT count(*) FROM Institute WHERE Institut_id != fakultaets_id",
                                                    "title" => _("beteiligte Einrichtungen"),
                                                    "detail" => _("alle Einrichtungen au�er den Fakult�ten"));
    $indicator['institute_firstlevel_all'] = array("query" => "SELECT count(*) FROM Institute WHERE Institut_id = fakultaets_id",
                                                   "title" => _("beteiligte Fakult�ten"),
                                                   "detail" => _("alle Fakult�ten"));
    $indicator['user_admin'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='admin'",
                            "title" => _("registrierte Administratoren"),
                            "detail" => "");
    $indicator['user_dozent'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='dozent'",
                            "title" => _("registrierte Dozenten"),
                            "detail" => "");
    $indicator['user_tutor'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='tutor'",
                            "title" => _("registrierte Tutoren"),
                            "detail" => "");
    $indicator['user_autor'] = array("query" => "SELECT count(*) from auth_user_md5 WHERE perms='autor'",
                            "title" => _("registrierte Autoren"),
                            "detail" => "");
    $indicator['posting'] = array("query" => "SELECT count(*) from px_topics",
                            "title" => _("Postings"),
                            "detail" => "");
    $indicator['document'] = array("query" => "SELECT count(*) from dokumente WHERE url = ''",
                            "title" => _("Dokumente"),
                            "detail" => "");
    $indicator['link'] = array("query" => "SELECT count(*) from dokumente WHERE url != ''",
                            "title" => _("verlinkte Dateien"),
                            "detail" => "");
    $indicator['litlist'] = array("query" => "SELECT count(*) from lit_list",
                            "title" => _("Literaturlisten"),
                            "detail" => "");
    $indicator['termin'] = array("query" => "SELECT count(*) from termine",
                            "title" => _("Termine"),
                            "detail" => "");
    $indicator['news'] = array("query" => "SELECT count(*) from news",
                            "title" => _("News"),
                            "detail" => "");
    $indicator['guestbook'] = array("query" => "SELECT count(*) from user_info WHERE guestbook='1'",
                            "title" => _("G�steb�cher"),
                            "detail" => "");
    $indicator['vote'] = array("query" => "SELECT count(*) from vote WHERE type='vote'",
                            "title" => _("Umfragen"),
                            "detail" => "",
                            "constraint" => $GLOBALS['VOTE_ENABLE']);
    $indicator['test'] = array("query" => "SELECT count(*) from vote WHERE type='test'",
                            "title" => _("Tests"),
                            "detail" => "",
                            "constraint" => $GLOBALS['VOTE_ENABLE']);
    $indicator['evaluation'] = array("query" => "SELECT count(*) from eval",
                            "title" => _("Evaluationen"),
                            "detail" => "",
                            "constraint" => $GLOBALS['VOTE_ENABLE']);
    $indicator['wiki_pages'] = array("query" => "SELECT COUNT(DISTINCT keyword) as count from wiki",
                            "title" => _("Wiki-Seiten"),
                            "detail" => "",
                            "constraint" => $GLOBALS['WIKI_ENABLE']);
    $indicator['lernmodul'] = array("query" => "SELECT COUNT(DISTINCT co_id) as count from seminar_lernmodul",
                            "title" => _("ILIAS-Lernmodule"),
                            "detail" => "",
                            "constraint" => $GLOBALS['ILIAS_CONNECT_ENABLE']);
    $indicator['resource'] = array("query" => "SELECT COUNT(*) from resources_objects",
                            "title" => _("Ressourcen-Objekte"),
                            "detail" => "von Stud.IP verwaltete Ressourcen wie R�ume oder Ger�te",
                            "constraint" => $RESOURCES_ENABLE);
    $out = "<ul>";
    if(in_array($key,array_keys($indicator))){
        if(!isset($indicator[$key]['constraint'])||$indicator[$key]['constraint']){
            $result = $db->query($indicator[$key]['query']);
            $rows = $result->fetchAll(PDO::FETCH_NUM);
            $out.="<li>".$indicator[$key]['title'];
            if($indicator[$key]['detail']){
                $out.=" (".$indicator[$key]['detail'].")";
            }
            $out.=": ".$rows[0][0]."(".$indicator[$key]['constraint'].")</li>";
        }
    }else{
        $out.="<li>".sprintf(_("Option %s nicht verf�gbar"),"&raquo;".$key."&laquo;")."</li>";
    }
    $out .= "</ul>";
    return $out;
}

siteinfoMarkup("/\(:history:\)/e",'history()');
function history(){
    $history = file($ABSOLUTE_PATH_STUDIP.'history.txt');
    $out =  formatReady(implode('',$history));
	return $out;
}

function languageReady($input){
    $pattern = "'\[lang=(\w*)\]\s*(.+)\s*\[/lang\]'eisU";
    $output = preg_replace($pattern,'stripforeignlanguage("$1", "$2")',$input);
    return $output;
}

function stripforeignlanguage($language, $text){
    global $_language;
    list($primary, $sub) = explode('_',$_language);
    if(($language==$primary)||($language==$_language)){
        return $text;
    }else{
        return '';
    }
}


?>
