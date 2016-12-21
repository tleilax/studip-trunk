<?php
# Lifter001: DONE
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter005: TODO
# Lifter010: TODO
/*

datei.inc.php - basale Routinen zur Dateiverwaltung, dienen zum Aufbau des Ordnersystems
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, Cornelis Kater <ckater@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Softwareg
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

use Studip\Button, Studip\LinkButton;

require_once 'vendor/idna_convert/idna_convert.class.php';

/*
used in:
public/sendfile.php:230:@readfile_chunked($path_file, $start, $end);
*/
function readfile_chunked($filename, $start = null, $end = null) {
    if (isset($start) && $start < $end) {
        $chunksize = 1024 * 1024; // how many bytes per chunk
        $bytes = 0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        fseek($handle, $start);
        while (!feof($handle) && ($p = ftell($handle)) <= $end) {
            if ($p + $chunksize > $end) {
                $chunksize = $end - $p + 1;
            }
            $buffer = fread($handle, $chunksize);
            $bytes += mb_strlen($buffer);
            echo $buffer;
        }
        fclose($handle);
        return $bytes; // return num. bytes delivered like readfile() does.
    } else {
        return readfile($filename);
    }
}

/*
used in:
lib/datei.inc.php:192:        $parsed_link = parse_header($response);
*/
function parse_header($header){
    $ret = array();
    if (!is_array($header)){
        $header = explode("\n",trim($header));
    }
    if (preg_match("|^HTTP/[^\s]*\s(.*?)\s|", $header[0], $status)) {
        $ret['response_code'] = $status[1];
        $ret['response'] = trim($header[0]);
    } else {
        return $ret;
    }
    for($i = 0; $i < count($header); ++$i){
        $parts = null;
        if(trim($header[$i]) == "") break;
        $matches = preg_match('/^\S+:/', $header[$i], $parts);
        if ($matches){
            $key = trim(mb_substr($parts[0],0,-1));
            $value = trim(mb_substr($header[$i], mb_strlen($parts[0])));
            $ret[$key] = $value;
        } else {
            $ret[trim($header[$i])] = trim($header[$i]);
        }
    }
    return $ret;
}

/*
used in:
lib/models/OpenGraphURL.class.php:114:     * @todo The combination of parse_link() and the following request
lib/models/OpenGraphURL.class.php:124:        $response = parse_link($this['url']);
lib/datei.inc.php:220:            $parsed_link = parse_link($location_header, $level + 1);
lib/datei.inc.php:1369:        $link_data = parse_link(Request::get('the_link'));
app/routes/Files_old.php:434:        $link_data = parse_link($file->getValue('url'));
app/models/media_proxy.php:172:        $response = parse_link($url);
*/
function parse_link($link, $level=0) {
    global $name, $the_file_name, $the_link, $locationheader, $parsed_link, $link_update;
    if ($level > 3)
        return FALSE;
    if ($link == "***" && $link_update)
        $link = getLinkPath($link_update);

    $url_parts = @parse_url( $link );
    //filter out localhost and reserved or private IPs
    if (mb_stripos($url_parts["host"], 'localhost') !== false
        || mb_stripos($url_parts["host"], 'loopback') !== false
        || (filter_var($url_parts["host"], FILTER_VALIDATE_IP) !== false
            && (mb_strpos($url_parts["host"],'127') === 0
                || filter_var($url_parts["host"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)
            )
        ) {
        return array('response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400);
    }
    if (mb_substr($link,0,6) == "ftp://") {
        // Parsing an FTF-Adress
        $documentpath = $url_parts["path"];

        if (mb_strpos($url_parts["host"],"@")) {
            $url_parts["pass"] .= "@".mb_substr($url_parts["host"],0,mb_strpos($url_parts["host"],"@"));
            $url_parts["host"] = mb_substr(mb_strrchr($url_parts["host"],"@"),1);
        }

        if (preg_match('/[^a-z0-9_.-]/i',$url_parts['host'])){ // exists umlauts ?
            $IDN = new idna_convert();
            $out = $IDN->encode(utf8_encode($url_parts['host'])); // false by error
            $url_parts['host'] = ($out)? $out : $url_parts['host'];
        }

        $ftp = ftp_connect($url_parts["host"]);

        if (!$url_parts["user"]) $url_parts["user"] = "anonymous";
        if (!$url_parts["pass"]) {
            $mailclass = new StudipMail();
            $url_parts["pass"] = $mailclass->getSenderEmail();
        }
        if (!@ftp_login($ftp,$url_parts["user"],$url_parts["pass"])) {
            ftp_quit($ftp);
            return FALSE;
        }
        $parsed_link["Content-Length"] = ftp_size($ftp, $documentpath);
        ftp_quit($ftp);
        if ($parsed_link["Content-Length"] != "-1") {
            $parsed_link["HTTP/1.0 200 OK"] = "HTTP/1.0 200 OK";
            $parsed_link["response_code"] = 200;
        } else {
            $parsed_link = FALSE;
        }
        $url_parts["pass"] = preg_replace("!@!","%40",$url_parts["pass"]);
        $the_link = "ftp://".$url_parts["user"].":".$url_parts["pass"]."@".$url_parts["host"].$documentpath;
        return $parsed_link;

    } else {
        if (!empty( $url_parts["path"])){
            $documentpath = $url_parts["path"];
        } else {
            $documentpath = "/";
        }
        if ( !empty( $url_parts["query"] ) ) {
            $documentpath .= "?" . $url_parts["query"];
        }
        $host = $url_parts["host"];
        $port = $url_parts["port"];
        $scheme = mb_strtolower($url_parts['scheme']);
        if (!in_array($scheme , words('http https'))) {
            return array('response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400);
        }
        if ($scheme == "https") {
            $ssl = TRUE;
            if (empty($port)) $port = 443;
        } else {
            $ssl = FALSE;
        }
        if (empty( $port ) ) $port = "80";

        if (preg_match('/[^a-z0-9_.-]/i',$host)){ // exists umlauts ?
            $IDN = new idna_convert();
            $out = $IDN->encode(utf8_encode($host)); // false by error
            $host = ($out)? $out : $host;
            $pwtxt = ($url_parts['user'] && $url_parts['pass'])? $url_parts['user'].':'. $url_parts['pass'].'@':'';
            $the_link = $url_parts['scheme'].'://'.$pwtxt.$host.':'.$port.$documentpath;
        }
        $socket = @fsockopen( ($ssl? 'ssl://':'').$host, $port, $errno, $errstr, 10 );
        if (!$socket) {
            return array('response' => 'HTTP/1.0 400 Bad Request', 'response_code' => 400);
        } else {
            $urlString = "GET ".$documentpath." HTTP/1.0\r\nHost: $host\r\n";
            if ($url_parts["user"] && $url_parts["pass"]) {
                $pass = $url_parts["pass"];
                $user = $url_parts["user"];
                $urlString .= "Authorization: Basic ".base64_encode("$user:$pass")."\r\n";
            }
            $urlString .= sprintf("User-Agent: Stud.IP v%s File Crawler\r\n", $GLOBALS['SOFTWARE_VERSION']);
            $urlString .= "Connection: close\r\n\r\n";
            fputs($socket, $urlString);
            stream_set_timeout($socket, 5);
            $response = '';
            do {
                $response .= fgets($socket, 128);
                $info = stream_get_meta_data($socket);
            } while (!feof($socket) && !$info['timed_out'] && mb_strlen($response) < 1024);
            fclose($socket);
        }
        $parsed_link = parse_header($response);

        // Anderer Dateiname?
        $disposition_header = $parsed_link['Content-Disposition']
                           ?: $parsed_link['content-disposition'];
        if ($disposition_header) {
            $header_parts = explode(';', $disposition_header);
            foreach ($header_parts as $part) {
                $part = trim($part);
                list($key, $value) = explode('=', $part, 2);
                if (mb_strtolower($key) === 'filename') {
                    $the_file_name = trim($value, '"');
                }
            }
        } else {
            $the_file_name = basename($url_parts['path']) ?: $the_file_name;
        }
        // Weg �ber einen Locationheader:
        $location_header = $parsed_link["Location"]
                        ?: $parsed_link["location"];
        if (in_array($parsed_link["response_code"], array(300,301,302,303,305,307)) && $location_header) {
            if (mb_strpos($location_header, 'http') !== 0) {
                $location_header = $url_parts['scheme'] . '://' . $url_parts['host'] . '/' . $location_header;
            }
            $parsed_link = parse_link($location_header, $level + 1);
        }
        return $parsed_link;
    }
}


/**
 * creates a zip file from given ids in tmp directory
 *
 * @param array $file_ids array of document ids
 * @param bool $perm_check if true, files are checked for folder permissions
 * @param bool $size_check if true, number and size of files are checked against config values
 * @return string filename(id) of the created zip without path
 */
 /*
 TODO: REPLACE BY FileArchiveManager::createArchiveFromFileRefs
 used in:
 app/controllers/admin/user.php:1361:        $zip_file_id  = createSelectedZip($download_ids, false);
public/folder.php:113:        $zip_file_id = createSelectedZip($download_ids, true, true);
public/folder.php:126:        $zip_file_id = createSelectedZip($download_ids, true, true);
*/
function createSelectedZip ($file_ids, $perm_check = TRUE, $size_check = false) {
    global $TMP_PATH, $ZIP_PATH, $SessSemName;
    $zip_file_id = false;
    $max_files = Config::GetInstance()->getValue('ZIP_DOWNLOAD_MAX_FILES');
    $max_size = Config::GetInstance()->getValue('ZIP_DOWNLOAD_MAX_SIZE') * 1024 * 1024;
    if(!$max_files && !$max_size) $size_check = false;
    $db = DBManager::get();
    if ( is_array($file_ids) && !($size_check && count($file_ids) > $max_files)) {
        $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessSemName[1]));
        if ($perm_check) {
            $folders_cond = "AND seminar_id = " . $db->quote($SessSemName[1]);
            $allowed_folders = $folder_tree->getReadableFolders($GLOBALS['user']->id);
            if (is_array($allowed_folders) && count($allowed_folders)) {
                $folders_cond .= " AND range_id IN (" . $db->quote($allowed_folders) . ") ";
            } else {
                $folders_cond .= " AND 0 ";
            }
        }
        $query = $db->prepare("SELECT SUM(filesize) FROM dokumente WHERE url='' AND dokument_id IN(?) " . $folders_cond);
        $query->execute(array($file_ids));
        $zip_size = $query->fetch(PDO::FETCH_COLUMN, 0);
        if(!($size_check && $zip_size > $max_size)) {
            $zip_file_id = md5(uniqid("jabba",1));

            //create temporary Folder
            $tmp_full_path = "$TMP_PATH/$zip_file_id";
            mkdir($tmp_full_path,0700);
            $filelist = array();
            //create folder content
            $files = $db->prepare(
                "SELECT dokument_id, filename, author_name, filesize, name, description, FROM_UNIXTIME(chdate) as chdate " .
                "FROM dokumente " .
                "WHERE dokument_id IN (?) " .
                    $folders_cond .
                "ORDER BY chdate, name, filename ");
            $files->execute(array($file_ids));
            foreach ($files as $file) {
                if(check_protected_download($file['dokument_id'])) {
                    $filename = prepareFilename($file['filename'], FALSE, $tmp_full_path);
                    if (@copy(get_upload_file_path($file['dokument_id']), $tmp_full_path.'/'.$filename)) {
                        TrackAccess($file['dokument_id'],'dokument');
                        $filelist[] = array_merge($file, array('path' => mb_substr($tmp_full_path.'/'.$filename, mb_strlen("$TMP_PATH/$zip_file_id/"))));
                    }
                }
            }
            $caption = array('filename' => _("Dateiname"), 'filesize' => _("Gr��e"), 'author_name' => _("Ersteller"), 'chdate' => _("Datum"), 'name' =>  _("Name"), 'description' => _("Beschreibung"), 'path' => _("Pfad"));
            array_to_csv($filelist, $tmp_full_path . '/' . _("dateiliste.csv"), $caption);
            //zip stuff
            create_zip_from_directory($tmp_full_path, $tmp_full_path);
            rmdirr($tmp_full_path);
            @rename($tmp_full_path .".zip" , $tmp_full_path);
        }
    }
    return $zip_file_id;
}




/**
 * Returns the read- and executable subfolders to a given folder_id
 * @folder_id: id of the target folder
 * @return: array($subfolders, $numberofsubfolders)
 */
/*
used in:
lib/datei.inc.php:459:        $kids = getFolderChildren($parent_id);
lib/datei.inc.php:2524:        list($subfolders, $num_subfolders) = getFolderChildren($folder_id);
*/
function getFolderChildren($folder_id){
    global $SessionSeminar, $user;

    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    if (!$folder_tree->isReadable($folder_id, $user->id)
    || !$folder_tree->isExecutable($folder_id, $user->id)){
        return array(0,0);
    } else {
        $num_kids = $folder_tree->getNumKids($folder_id);
        $kids = array();
        if ($num_kids){
            foreach($folder_tree->getKids($folder_id) as $one){
                if($folder_tree->isExecutable($one, $user->id)) $kids[] = $one;
            }
        }
        return array($kids, count($kids));
    }
}

/*
used in:
lib/datei.inc.php:467:                getFolderId($kids[0][$i],true);
lib/datei.inc.php:614:            $folder = getFolderId($item_id);
lib/datei.inc.php:682:            $folder = getFolderId($item_id);
lib/datei.inc.php:788:        $folder = getFolderId($folder_id);
*/
function getFolderId($parent_id, $in_recursion = false){
    static $kidskids;
        if (!$kidskids || !$in_recursion){
            $kidskids = array();
        }
        $kids = getFolderChildren($parent_id);
        if ($kids[1]){
            $kidskids = array_merge((array)$kidskids,(array)$kids[0]);
            for ($i = 0; $i < $kids[1]; ++$i){
                getFolderId($kids[0][$i],true);
            }
        }
        return (!$in_recursion) ? $kidskids : null;
}

/**
 * Counts and returns the number files in the given folder and subfolders.
 * Files not visible to the current user are not counted
 *
 * @param $parent_id     a folder id
 * @param $range_id      the range id for the folder, course or institute id
 * @return integer
 */
/*
used in:
lib/dates.inc.php:365:        if (!doc_count($termin_id))
lib/datei.inc.php:326:    if(!($size_check && (doc_count($folder_id) > $max_files || doc_sum_filesize($folder_id) > $max_size))){
lib/datei.inc.php:488:function doc_count($parent_id, $range_id = null)
lib/datei.inc.php:650:                    return array(1, doc_count($item_id));
lib/datei.inc.php:1820:    $document_count = doc_count($folder_id);
lib/datei.inc.php:2190:    $document_count = doc_count($folder_id);
*/
function doc_count($parent_id, $range_id = null)
{
    global $SessionSeminar, $user;
    if ($range_id === null)  {
        $range_id = $SessionSeminar;
    }
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $range_id));

    $arr = $folder_tree->getReadableKidsKids($parent_id, $user->id);
    if ($folder_tree->isReadable($parent_id, $user->id) && $folder_tree->isExecutable($parent_id, $user->id)) {
        $arr[] = $parent_id;
    }

    if (!is_array($arr) || count($arr) === 0) {
        return 0;
    }

    $query = "SELECT COUNT(*) FROM dokumente WHERE range_id IN (?)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($arr));
    return $statement->fetchColumn();
}

/*
used in:
lib/datei.inc.php:326:    if(!($size_check && (doc_count($folder_id) > $max_files || doc_sum_filesize($folder_id) > $max_size))){
lib/datei.inc.php:491:lib/datei.inc.php:326:    if(!($size_check && (doc_count($folder_id) > $max_files || doc_sum_filesize($folder_id) > $max_size))){
*/
function doc_sum_filesize ($parent_id)
{
    global $SessionSeminar, $user;
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    $arr = $folder_tree->getReadableKidsKids($parent_id, $user->id);
    if($folder_tree->isReadable($parent_id, $user->id) && $folder_tree->isExecutable($parent_id, $user->id)) {
        $arr[] = $parent_id;
    }
    if (!is_array($arr) || count($arr) === 0) {
        return 0;
    }

    $query = "SELECT SUM(filesize)
              FROM dokumente
              WHERE url = '' AND range_id IN (?)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($arr));
    return $statement->fetchColumn();
}

function doc_newest ($parent_id)
{
    global $SessionSeminar, $user;
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    $arr = $folder_tree->getReadableKidsKids($parent_id, $user->id);
    if($folder_tree->isReadable($parent_id, $user->id) && $folder_tree->isExecutable($parent_id, $user->id)) {
        $arr[] = $parent_id;
    }
    if (!is_array($arr) || count($arr) === 0) {
        return 0;
    }

    $query = "SELECT GREATEST(IFNULL(MAX(mkdate), 0), IFNULL(MAX(chdate), 0))
              FROM dokumente
              WHERE range_id IN (?)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($arr));
    return $statement->fetchColumn();
}

function doc_challenge ($parent_id)
{
    global $SessionSeminar, $user;
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    $arr = $folder_tree->getReadableKidsKids($parent_id, $user->id);
    if($folder_tree->isReadable($parent_id, $user->id) && $folder_tree->isExecutable($parent_id ,$user->id)) {
        $arr[] = $parent_id;
    }
    if (!is_array($arr) || count($arr) === 0) {
        return 0;
    }

    $query = "SELECT dokument_id FROM dokumente WHERE range_id IN (?)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($arr));
    return $statement->fetchAll(PDO::FETCH_COLUMN);
}

function get_user_documents_in_folder($folder_id, $user_id)
{
    $query = "SELECT filename, filesize, chdate
              FROM dokumente
              WHERE range_id = ? AND user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($folder_id, $user_id));

    $ret = array();
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $ret[] = $row['filename']
               . ' (' . round($row['filesize'] / 1024) . 'kB, '
               . date('d.m.Y - H:i', $row['chdate']) . ')';
    }
    return $ret;
}

/*
used by:
lib/dates.inc.php:373:            move_item($folder_id, $sem_id, $sem_id);
public/folder.php:563:                $done = move_item($folder_system_data["move"], $new_range_id, $sid);
public/folder.php:587:        $done = move_item($folder_system_data["move"], $open_id);
*/
function move_item($item_id, $new_parent, $change_sem_to = false)
{
    global $SessionSeminar;
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    if ($change_sem_to && !$folder_tree->isFolder($item_id)) {
        $query = "SELECT folder_id FROM folder WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($change_sem_to));
        $new_folder_id = $statement->fetchColumn();

        if (!$new_folder_id) {
            return false;
        }
    }


    if ($item_id != $new_parent) {
        $doc = StudipDocument::find($item_id);
        if (isset($doc)) {
            if ($change_sem_to && $new_folder_id) {
                $doc['range_id'] = $new_folder_id;
                $doc['seminar_id'] = $change_sem_to;
            } else {
                $doc['range_id'] = $new_parent;
            }
            $doc['chdate'] = $doc['chdate'] + 1;
            $doc->store();

            return array(0,1);
        } else {
            //we want to move a folder, so we have first to check if we want to move a folder in a subordinated folder
            $folder = getFolderId($item_id);

            if (is_array($folder) && in_array($new_parent, $folder)) {
                $target_is_child = true;
            }

            if (!$target_is_child){
                $query = "UPDATE folder SET range_id = ?, seminar_id = ? WHERE folder_id = ?";
                $statement = DBManager::get()->prepare($query);
                $cid = $change_sem_to ?: $SessionSeminar;
                $statement->execute(array($new_parent, $cid, $item_id));

                if ($change_sem_to) {
                    if (is_array($folder) && count($folder)) {
                        $query = "UPDATE folder SET seminar_id = ? WHERE folder_id IN (?)";
                        $statement = DBManager::get()->prepare($query);
                        $statement->execute(array($cid, $folder));
                    }
                    $folder[] = $item_id;
                    // TODO (mlunzena): notify these documents
                    $query = "UPDATE dokumente SET seminar_id = ? WHERE range_id IN (?)";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute(array($change_sem_to, $folder));
                    $affected = $statement->rowCount();

                    $folder_tree->init();
                    return array(count($folder), $affected);
                } else {
                    $folder_tree->init();
                    return array(1, doc_count($item_id));
                }
            }
        }
    }
    return false;
}



/*
used by:
lib/datei.inc.php:1157:                edit_item(
public/folder.php:295:        edit_item($open_id, Request::int('type'), Request::get('change_name'), Request::get('change_description'), Request::int('change_protected', 0));
*/
function edit_item($item_id, $type, $name, $description, $protected = 0, $url = '', $filesize = '')
{
    global $SessionSeminar;

    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    if ($url != '') {
        $url_parts = parse_url($url);
        $the_file_name = basename($url_parts['path']);
    }

    if ($type) {
        $query = "UPDATE folder
                  SET description = ?, name = IFNULL(?, name)
                  WHERE folder_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array(
            $description,
            mb_strlen($name) ? $name : null,
            $item_id,
        ));

        if ($GLOBALS['perm']->have_studip_perm('tutor', $SessionSeminar)) {
            if ($folder_tree->permissions_activated) {
                foreach(array('r' => 'read', 'w' => 'write', 'x' => 'exec') as $p => $v){
                    if (Request::get('perm_' . $v)) {
                        $folder_tree->setPermission($item_id, $p);
                    } else {
                        $folder_tree->unsetPermission($item_id, $p);
                    }
                }
            }
            if (Request::get('perm_folder')) {
                $folder_tree->setPermission($item_id, 'f');
            } else {
                $folder_tree->unsetPermission($item_id, 'f');
            }
        }
        return $statement->rowCount() > 0;
    } else {

        $doc = new StudipDocument($item_id);
        $doc->setData(compact(words('name description protected')));

        if ($url != '') {
            $doc['url'] = $url;
            $doc['filename'] = $the_file_name;
        }

        return !!$doc->store();
    }
}

/**
 * Create a 'folder' in the files module of a course or institution.
 * Particularly interesting is the third parameter $parent_id mapping
 * to the 'range_id' field in the database table 'folder'. It is used
 * to create a tree structure of folders but is not the usual parent
 * key. Instead it is one of these:
 *  - $parent_id equals the course's ID, if this folder is the
 *    "Allgemeine Dateien" folder.
 *  - $parent_id equals the ID of an entry in table 'statusgruppen',
 *    if the folder is associated to that entry.
 *  - $parent_id equals the ID of an entry in table 'themen', if the
 *    folder is associated to that entry.
 *  - $parent_id equals `md5($cid . 'top_folder')`, if that folder is
 *    not the "Allgemeine Dateien" folder, but exists in the same
 *    depth of the tree as the mentioned folder. (blame StEP0008)
 *  - otherwise $parent_id equals the ID of the parent folder.
 *
 * @param string $name         the name of the folder
 * @param string $description  a description of the folder, may be the
 *                             empty string
 * @param string $parent_id    some kind of foreign key used to create
 *                             a tree structure of folders as
 *                             described above
 * @param int    $permission   bit-OR your permission:
 *                             0001 = visible,
 *                             0010 = writable,
 *                             0100 = readable,
 *                             1000 = extendable
 * @param string $seminar_id   an optional parameter used to associate
 *                             with a course or institute. $SessionSeminar
 *                             is used, if it is missing.
 * @return the ID of the folder if successful, otherwise NULL
 */
/*
used by:
lib/datei.inc.php:2625:        $dir_id = create_folder($name, "", $range_id);
app/controllers/institute/basicdata.php:278:                create_folder(
public/folder.php:170:    $create_folder_perm = $folder_tree->checkCreateFolder($open_id, $user->id);
public/folder.php:172:    $create_folder_perm = false;
public/folder.php:185:if ($rechte || $owner || $create_folder_perm) {
public/folder.php:188:        $change = create_folder(_("Neuer Ordner"), '', $open_id );
public/folder.php:228:        $open_id = create_folder($titel, $description, $open_id, $permission);
*/
function create_folder ($name, $description, $parent_id, $permission = 7, $seminar_id = null)
{
    global $user;

    if (!isset($seminar_id)) {
        $seminar_id = $GLOBALS['SessionSeminar'];
    }

    $id = md5(uniqid('salmonellen',1));
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $seminar_id));

    $query = "INSERT INTO folder (name, folder_id, description, range_id, seminar_id, user_id, permission, mkdate, chdate)
              VALUES (?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array(
        $name,
        $id,
        $description,
        $parent_id,
        $seminar_id,
        $user->id,
        $permission
    ));
    if ($statement->rowCount()) {
        $folder_tree->init();
        return $id;
    }
}

## Upload Funktionen ################################################################################

//Ausgabe des Formulars
/*
used by:
lib/datei.inc.php:1105:            echo form($refresh);
lib/datei.inc.php:1109:            return form($refresh);
lib/datei.inc.php:1676:        $content .= form($refresh);
*/
function form($refresh = FALSE)
{
    global $UPLOAD_TYPES,$range_id,$SessSemName,$user,$folder_system_data;

    $sem_status = $GLOBALS['perm']->get_studip_perm($SessSemName[1]);

    // add skip link (position in list is one before main content => 99)
    SkipLinks::addIndex(_("Datei hochladen"), 'upload_form', 99);

    //erlaubte Dateigroesse aus Regelliste der Config.inc.php auslesen
    if ($UPLOAD_TYPES[$SessSemName["art_num"]]) {
        $max_filesize=$UPLOAD_TYPES[$SessSemName["art_num"]]["file_sizes"][$sem_status];
    }   else {
        $max_filesize=$UPLOAD_TYPES["default"]["file_sizes"][$sem_status];
    }
    $c=1;
    $print = "";
    $print.= "\n<form enctype=\"multipart/form-data\" name=\"upload_form\" action=\"" . URLHelper::getLink('#anker') . "\" method=\"post\">";
    $print.= "\n<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"".(ini_get('upload_max_filesize')*1048576)."\" />";
    $print.= CSRFProtection::tokenTag();
    if ($folder_system_data['zipupload'])
        $print.="\n<br><br>" . _("Sie haben diesen Ordner zum Upload ausgew�hlt:")
            . '<br>' . _("Die Dateien und Ordner, die im hochzuladenden Ziparchiv enthalten sind, werden in diesen Ordner entpackt.") .  "<br><br><center><table width=\"90%\" style=\"border: 1px solid #000000;\" border=0 cellpadding=2 cellspacing=3 id=\"upload_form\">";
    else if (!$refresh)
        $print.="\n<br><br>" . _("Sie haben diesen Ordner zum Upload ausgew�hlt:") . "<br><br><center><table width=\"90%\" style=\"border: 1px solid #000000;\" border=0 cellpadding=2 cellspacing=3 id=\"upload_form\">";
    else
        $print .= "\n<br><br>" . _("Sie haben diese Datei zum Aktualisieren ausgew�hlt. Sie <b>�berschreiben</b> damit die vorhandene Datei durch eine neue Version!") . "<br><br><center><table width=\"90%\" style=\"border: 1px solid #000000;\" border=0 cellpadding=2 cellspacing=3 id=\"upload_form\">";
    $print.="\n";
    $print.="\n<tr><td class=\"table_row_even\" width=\"20%\"><font size=-1><b>";

    //erlaubte Upload-Typen aus Regelliste der Config.inc.php auslesen
    if (!$folder_system_data['zipupload']) {
        if ($UPLOAD_TYPES[$SessSemName["art_num"]]) {
            if ($UPLOAD_TYPES[$SessSemName["art_num"]]["type"] == "allow") {
                $i=1;
                $print.= _("Unzul�ssige Dateitypen:") . "</b><font></td><td class=\"table_row_even\" width=\"80%\"><font size=-1>";
                foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
                    if ($i !=1)
                        $print.= ", ";
                    $print.= mb_strtoupper ($ft);
                    $i++;
                    }
                }
            else {
                $i=1;
                $print.= _("Zul�ssige Dateitypen:") . "</b><font></td><td class=\"table_row_even\" width=\"80%\"><font size=-1>";
                foreach ($UPLOAD_TYPES[$SessSemName["art_num"]]["file_types"] as $ft) {
                    if ($i !=1)
                        $print.= ", ";
                    $print.= mb_strtoupper ($ft);
                    $i++;
                    }
                }
            }
        else {
            if ($UPLOAD_TYPES["default"]["type"] == "allow") {
                $i=1;
                $print.= _("Unzul�ssige Dateitypen:") . "</b><font></td><td class=\"table_row_even\" width=\"80%\"><font size=-1>";
                foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
                    if ($i !=1)
                        $print.= ", ";
                    $print.= mb_strtoupper ($ft);
                    $i++;
                    }
                }
            else {
                $i=1;
                $print.= _("Zul�ssige Dateitypen:") . "</b></td><font><td class=\"table_row_even\" width=\"80%\"><font size=-1>";
                foreach ($UPLOAD_TYPES["default"]["file_types"] as $ft) {
                    if ($i !=1)
                        $print.= ", ";
                    $print.= mb_strtoupper ($ft);
                    $i++;
                    }
                }
            }
    } else {
        $print.= _("Zul�ssige Dateitypen:") . "</b></td><font><td class=\"table_row_even\" width=\"80%\"><font size=-1>";
        $print .= 'ZIP';
    }
    $print.="</font></td></tr>";
    $print.="\n<tr><td class=\"table_row_even\" width=\"20%\"><font size=-1><b>" . _("Maximale Gr��e:") . "</b></font></td><td class=\"table_row_even\" width=\"80%\"><font size=-1><b>".($max_filesize / 1048576)." </b>" . _("Megabyte") . "</font></td></tr>";
    if ($folder_system_data['zipupload']) {
        $print.="\n<tr><td class=\"table_row_even\" width=\"20%\"><font size=-1><b>" . _("Maximaler Inhalt des Ziparchivs:")
            . "</b></font></td><td class=\"table_row_even\" width=\"80%\"><font size=-1>"
            . sprintf(_("<b>%d</b> Dateien und <b>%d</b> Ordner"),get_config('ZIP_UPLOAD_MAX_FILES'), get_config('ZIP_UPLOAD_MAX_DIRS'))
            . "</font></td></tr>";
    }
    $print.= "<tr><td class=\"content_seperator\" colspan=2><font size=-1>" . _("1. Klicken Sie auf <b>'Durchsuchen...'</b>, um eine Datei auszuw�hlen.") . " </font></td></tr>";
    $print.= "\n<tr>";
    $print.= "\n<td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;<label for=\"the_file\">" . _("Dateipfad:") . "</label>&nbsp;</font><br>";
    $print.= "&nbsp;<input name=\"the_file\" id=\"the_file\" aria-required=\"true\" type=\"file\"  style=\"width: 70%\" size=\"30\">&nbsp;</td></td>";
    $print.= "\n</tr>";
    if (!$refresh && !$folder_system_data['zipupload']) {
        $print.= "<tr><td class=\"content_seperator\" colspan=2><font size=-1>" . _("2. Schutz gem�� Urheberrecht.") . "</font></td></tr>";
        $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>";
        $print.= "\n&nbsp;<label><input type=\"RADIO\" name=\"protected\" value=\"0\"".(!$protect ? "checked" :"") .'>'._("Ja, dieses Dokument ist frei von Rechten Dritter") ;
        $print.= "</label>\n&nbsp;<label><input type=\"RADIO\" name=\"protected\" value=\"1\"".($protect ? "checked" :"") .'>'._("Nein, dieses Dokument ist <u>nicht</u> frei von Rechten Dritter");
        $print.= "</label></td></tr>";

        $print.= "<tr><td class=\"content_seperator\" colspan=2><font size=-1>" . _("3. Geben Sie eine kurze Beschreibung und einen Namen f�r die Datei ein.") . "</font></td></tr>";
        $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><label><font size=-1>&nbsp;" . _("Name:") . "&nbsp;</font><br>";
        $print.= "\n&nbsp;<input type=\"TEXT\" name=\"name\" style=\"width: 70%\" size=\"40\" maxlength\"255\"></label></td></tr>";
        $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><label><font size=-1>&nbsp;" . _("Beschreibung:") . "&nbsp;</font><br>";
        $print.= "\n&nbsp;<textarea name=\"description\" style=\"width: 70%\" COLS=40 ROWS=3 WRAP=PHYSICAL></textarea></label>&nbsp;</td></tr>";
        $print.= "\n<tr><td class=\"content_seperator\" colspan=2 ><font size=-1>" . _("4. Klicken Sie auf <b>'Absenden'</b>, um die Datei hochzuladen") . "</font></td></tr>";
    } else if ($folder_system_data['zipupload']) {
        $print.= "\n<tr><td class=\"content_seperator\" colspan=2 ><font size=-1>" . _("3. Klicken Sie auf <b>'Absenden'</b>, um das Ziparchiv hochzuladen und in diesem Ordner zu entpacken.") . "</font></td></tr>";
    } else {
        $print.= "\n<tr><td class=\"content_seperator\" colspan=2 ><font size=-1>" . _("3. Klicken Sie auf <b>'Absenden'</b>, um die Datei hochzuladen und damit die alte Version zu �berschreiben.") . "</font></td></tr>";
    }
    $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"center\" valign=\"center\">";

    $print .= '<div class="button-group">';
    $print .= Button::createAccept(_("Absenden"), "create", array('onClick' => 'return STUDIP.OldUpload.upload_start(jQuery(this).closest("form"));'));
    $print .= LinkButton::createCancel(_("Abbrechen"), URLHelper::getURL("?cancel_x=true#anker"));
    $print .= '</div>';

    $print.="</td></tr>";

    $print.= "\n<input type=\"hidden\" name=\"cmd\" value=\"upload\">";
    $print.= "\n<input type=\"hidden\" name=\"upload_seminar_id\" value=\"".$SessSemName[1]."\">";
    $print.= "\n</table></form><br></center>";

    return $print;
}

/**
 * kills forbidden characters in filenames,
 * shortens filename to 31 Characters if desired,
 * checks for unique filename in given folder and modifies
 * filename if needed
 *
 * @param string $filename original filename
 * @param bool $shorten if true, filename is shortened to 31 chars
 * @param bool $checkfolder if true, uniqueness of filename in this folder is guaranteed
 * @return string the modified filename
 */
/*
used by:
lib/datei.inc.php:290:                    $filename = prepareFilename($file['filename'], FALSE, $tmp_full_path);
lib/datei.inc.php:840:function prepareFilename($filename, $shorten = FALSE, $checkfolder = false) {
lib/datei.inc.php:2219:            $link[] =  rawurlencode(prepareFilename($file_id));
lib/datei.inc.php:2221:        $link[] = '/' . rawurlencode(prepareFilename($file_name));
lib/datei.inc.php:2237:            $link[] = '&file_id=' . rawurlencode(prepareFilename($file_id));
lib/datei.inc.php:2239:        $link[] = '&file_name=' . rawurlencode(prepareFilename($file_name ));
app/controllers/admin/user.php:1364:        $filename = prepareFilename($user->username . '-' . _("Dokumente") . '.zip');
app/controllers/course/dates.php:352:        $filename = prepareFilename($course['name'] . '-' . _("Ablaufplan")) . '.doc';
public/sendfile.php:100:$file_name = prepareFilename(basename(Request::get('file_name')));
public/folder.php:82:        $zip_name = prepareFilename(_('Dateiordner') . '_' . $name . '.zip');
public/folder.php:115:            $zip_name = prepareFilename($SessSemName[0].'-'._("Neue Dokumente").'.zip');
public/folder.php:128:            $zip_name = prepareFilename($SessSemName[0].'-'._("Dokumente").'.zip');
*/
function prepareFilename($filename, $shorten = FALSE, $checkfolder = false) {
    $bad_characters = array (":", chr(92), "/", "\"", ">", "<", "*", "|", "?", " ", "(", ")", "&", "[", "]", "#", chr(36), "'", "*", ";", "^", "`", "{", "}", "|", "~", chr(255));
    $replacements = array ("", "", "", "", "", "", "", "", "", "_", "", "", "+", "", "", "", "", "", "", "-", "", "", "", "", "-", "", "");

    //delete all ASCII control characters
    for($i = 0; $i < 0x20; $i++) {
        $bad_characters[] = chr($i);
        $replacements[] = "";
    }

    $filename=str_replace($bad_characters, $replacements, $filename);

    if ($filename{0} == ".")
        $filename = mb_substr($filename, 1, mb_strlen($filename));

    if ($shorten) {
        $ext = getFileExtension ($filename);
        $filename = mb_substr(mb_substr($filename, 0, mb_strrpos($filename,$ext)-1), 0, (30 - mb_strlen($ext))).".".$ext;
    }
    if ($checkfolder !== false) {
        $c = 0;
        $ext = getFileExtension($filename);
        if ($ext) {
          $name = mb_substr($filename, 0, mb_strrpos($filename,$ext)-1);
        } else {
            $name = $filename;
}
        while (file_exists($checkfolder . '/' . $filename)) {
            $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
        }
    }
    return $filename;
}

//Diese Funktion dient zur Abfrage der Dateierweiterung
/*
used by:
db/migrations/207_moadb.php:303:            $ext = getFileExtension($filename);
db/migrations/207_moadb.php:356:            $ext = getFileExtension($filename);
lib/calendar/CalendarImportFile.class.php:131:    public function _getFileExtension()
lib/datei.inc.php:871:        $ext = getFileExtension ($filename);
lib/datei.inc.php:876:        $ext = getFileExtension($filename);
lib/datei.inc.php:890:function getFileExtension($str) {
lib/datei.inc.php:944:        $pext = mb_strtolower(getFileExtension($real_file_name ? $real_file_name : $the_file_name));
lib/datei.inc.php:1395:            $fext = getFileExtension(mb_strtolower($datei['filename']));
lib/datei.inc.php:1514:        print GetFileIcon(getFileExtension($datei['filename']))->asImg();
lib/datei.inc.php:1523:            print "<a href=\"".GetDownloadLink( $datei["dokument_id"], $datei["filename"], $type, "normal")."\" class=\"extern\">".GetFileIcon(getFileExtension($datei['filename']))->asImg()."</a>";
lib/datei.inc.php:2192:    $extension = mb_strtolower(getFileExtension($filename));
lib/datei.inc.php:2514:    $ext = mb_strtolower(getFileExtension($_FILES['the_file']['name']));
app/views/admin/user/list_files.php:18:                        GetFileIcon(getFileExtension($file->filename), true));
*/
function getFileExtension($str) {
    $i = mb_strrpos($str,".");
    if (!$i) { return ""; }

    $l = mb_strlen($str) - $i;
    $ext = mb_substr($str,$i+1,$l);

    return $ext;
}

/**
 * Checks whether a given file upload is valid and allowed.
 *
 * @param $the_file file to upload to Stud.IP
 * @param $real_file_name an optional real file name for handling files
 *   inside a ZIP (otherwise, the filename of the ZIP itself would always be
 *   used)
 *
 * @return Can the given file be uploaded to Stud.IP?
 */
/*
used by:
lib/datei.inc.php:1040:    if (!validate_upload($the_file)) {
lib/datei.inc.php:2657:        if (validate_upload(array('name' => $file, 'size' => filesize($file)))) {
app/routes/Files_old.php:102:                validate_upload($file);
app/routes/Files_old.php:169:                validate_upload($file);
app/models/WysiwygDocument.php:162:        $GLOBALS['msg'] = ''; // validate_upload will store messages here
app/models/WysiwygDocument.php:163:        if (! \validate_upload($file)) { // upload is forbidden
app/controllers/messages.php:718:        if (!validate_upload($file)) {
public/plugins_packages/core/Blubber/controllers/streams.php:527:            validate_upload($file);
*/
function validate_upload($the_file, $real_file_name='') {
    global $UPLOAD_TYPES, $SessSemName;

    $the_file_size = $the_file['size'];
    $the_file_name = $the_file['name'];
    $result = true;
    if (!$the_file) { # haben wir eine Datei?
        PageLayout::postError(_("Sie haben keine Datei zum Hochladen ausgew�hlt!"));
    } else { # pruefen, ob der Typ stimmt
        if (match_route("dispatch.php/messages/upload_attachment")) {
            if (!$GLOBALS["ENABLE_EMAIL_ATTACHMENTS"] == true) {
                PageLayout::postError(_("Dateianh�nge f�r Nachrichten sind in dieser Installation nicht erlaubt!"));
                $result = false;
            }
            $active_upload_type = "attachments";
            $sem_status = $GLOBALS['perm']->get_perm();
        } else {
            if (Request::option('cid')) {
                $sem_status = $GLOBALS['perm']->get_studip_perm($SessSemName[1]);
                $active_upload_type = $SessSemName["art_num"];
            } else {
                $sem_status = $GLOBALS['perm']->get_perm();
                $active_upload_type = "personalfiles";
            }
            if (!isset($UPLOAD_TYPES[$active_upload_type])) {
                $sem_status = $GLOBALS['perm']->get_perm();
                $active_upload_type = 'default';
            }
        }

        //erlaubte Dateigroesse aus Regelliste der Config.inc.php auslesen
        $max_filesize = $UPLOAD_TYPES[$active_upload_type]["file_sizes"][$sem_status];

        //Die Dateierweiterung von dem Original erfragen
        $pext = mb_strtolower(getFileExtension($real_file_name ? $real_file_name : $the_file_name));
        if ($pext == "doc")
            $doc=TRUE;

        //Erweiterung mit Regelliste in config.inc.php vergleichen
        $exts = '';
        $errors = [];
        if ($UPLOAD_TYPES[$active_upload_type]["type"] == "allow") {
            $t=TRUE;
            $i=1;
            foreach ($UPLOAD_TYPES[$active_upload_type]["file_types"] as $ft) {
                if ($pext == $ft)
                    $t=FALSE;
                if ($i !=1)
                    $exts.=",";
                $exts.=" ".mb_strtoupper($ft);
                $i++;
                }
            if (!$t) {
                if ($i==2)
                    $errors[] = sprintf(_("Die Datei konnte nicht �bertragen werden: Sie d�rfen den Dateityp %s nicht hochladen!"), trim($exts));
                else
                    $errors[] = sprintf(_("Die Datei konnte nicht �bertragen werden: Sie d�rfen die Dateitypen %s nicht hochladen!"), trim($exts));
                if ($doc) {
                    $help_url = format_help_url("Basis.DateienUpload");
                    $errors[] = sprintf(_("%sHier%s bekommen Sie Hilfe zum Upload von Word-Dokumenten."), "<a target=\"_blank\" href=\"".$help_url."\">", "</a>");
                }
                }
        } else {
            $t=FALSE;
            $i=1;

            foreach ($UPLOAD_TYPES[$active_upload_type]["file_types"] as $ft) {
                if ($pext == $ft)
                    $t=TRUE;
                if ($i !=1)
                    $exts.=",";
                $exts.=" ".mb_strtoupper($ft);
                $i++;
                }
            if (!$t) {
                if ($i==2)
                    $errors[] =  sprintf(_("Die Datei konnte nicht �bertragen werden: Sie d�rfen nur den Dateityp %s hochladen!"), trim($exts));
                else
                    $errors[] =  sprintf(_("Die Datei konnte nicht �bertragen werden: Sie d�rfen nur die Dateitypen %s hochladen!"), trim($exts));
                if ($doc) {
                    $help_url = format_help_url("Basis.DateienUpload");
                    $errors[] =  sprintf(_("%sHier%s bekommen Sie Hilfe zum Upload von Word-Dokumenten."), "<a target=\"_blank\" href=\"".$help_url."\">", "</a>");
                }
            }
        }

        if(!empty($errors)) {
            $result = false;
            PageLayout::postError(_('Bitte beachten Sie;'), $errors);
        }

        //pruefen ob die Groesse stimmt.
        if ($the_file['error'] ===  UPLOAD_ERR_INI_SIZE || $the_file_size > $max_filesize) {
            $result = false;
            PageLayout::postError(sprintf(_("Die Datei konnte nicht �bertragen werden: Die maximale Gr��e f�r einen Upload (%s Megabyte) wurde �berschritten!"), $max_filesize / 1048576));
        } elseif ($the_file_size == 0) {
            $result = false;
            PageLayout::postError(_("Sie haben eine leere Datei zum Hochladen ausgew�hlt!"));
        }

    }

    if (!$result) {
        return false;
    } else {
        return true;
    }
}

//der eigentliche Upload
/*
used by:
lib/datei.inc.php:1155:        upload($the_file, $refresh, $range_id);
*/
function upload($the_file, $refresh, $range_id)
{
    global $dokument_id;

    if (!validate_upload($the_file)) {
        return FALSE;
    }

    $data = getUploadMetadata($range_id, $refresh);

    $doc = StudipDocument::createWithFile($the_file['tmp_name'], $data);
    if ($doc === null) {
        PageLayout::postError(_("Datei�bertragung gescheitert!"));
        return false;
    }

    // wird noch in folder.php gebraucht
    $dokument_id = $doc->getId();

    PageLayout::postSuccess(_("Die Datei wurde erfolgreich auf den Server �bertragen!"));
    return TRUE;
}


//Erzeugen des Datenbankeintrags zur Datei
/*
used by:
lib/datei.inc.php:1089:    $data = getUploadMetadata($range_id, $refresh);
*/
function getUploadMetadata($range_id, $refresh = FALSE) {
    global $user;
    $upload_seminar_id = Request::option('upload_seminar_id');
    $description = trim(Request::get('description'));
    $name = trim(Request::get('name'));
    $protected = Request::int('protected');
    $the_file_name = basename($_FILES['the_file']['name']);
    $the_file_size = $_FILES['the_file']['size'];

    $name || ($name = $the_file_name);

    $result = array(
        'filename'    => $the_file_name,
        'filesize'    => $the_file_size,
        'autor_host'  => $_SERVER['REMOTE_ADDR'],
        'user_id'     => $user->id,
        'author_name' => get_fullname()
    );

    if (!$refresh) {
        $result['range_id']     = trim($range_id);
        $result['seminar_id']   = $upload_seminar_id;
        $result['description']  = $description;
        $result['name']         = $name;
        $result['protected']    = (int) $protected;
    } else {
        $result['dokument_id'] = $refresh;
        $result['chdate'] = time();
    }

    return $result;
}



//Steuerungsfunktion
/*
used by:
lib/datei.inc.php:1380:        $content.=upload_item ($upload,FALSE,FALSE,$refresh);
*/
function upload_item ($range_id, $create = FALSE, $echo = FALSE, $refresh = FALSE) {
    $the_file = $_FILES["the_file"];

    if ($create) {
        upload($the_file, $refresh, $range_id);
        return;
    }
    else {
        if ($echo) {
            echo form($refresh);
            return;
        }
        else
            return form($refresh);
    }
}


/*
used by:
lib/datei.inc.php:1403:        $content .= link_item($datei["dokument_id"],FALSE,FALSE,$datei["dokument_id"]);
lib/datei.inc.php:1664:        $content .= link_item($folder_id);
public/folder.php:506:        if (link_item ($folder_system_data["link"], TRUE, FALSE, $folder_system_data["refresh"],FALSE)) {
public/folder.php:520:        if (link_item ($range_id, TRUE, FALSE, FALSE, Request::option('link_update'))) {
*/
function link_item ($range_id, $create = FALSE, $echo = FALSE, $refresh = FALSE, $link_update = FALSE) {
    global $filesize;

    if ($create) {
        $link_data = parse_link(Request::get('the_link'));
        if ($link_data["HTTP/1.0 200 OK"] || $link_data["HTTP/1.1 200 OK"] || $link_data["HTTP/1.1 302 Found"] || $link_data["HTTP/1.0 302 Found"]) {
            if (!$link_update) {
                if (insert_link_db($range_id, $link_data["Content-Length"], $refresh)) {
                    if ($refresh) {
                        delete_link($refresh, TRUE);
                    }
                }
                $tmp = TRUE;
            } else {
                $filesize = $link_data["Content-Length"];
                edit_item(
                    $link_update,
                    FALSE,
                    Request::get('name'),
                    Request::get('description'),
                    Request::int('protect'),
                    Request::get('the_link'),
                    $filesize
                );
                $tmp = TRUE;
            }
        } else {
            $tmp = FALSE;

        }
        return $tmp;

    } else {
        if ($echo) {
            echo link_form($refresh,$link_update);
            return;
        } else {
            return link_form($refresh,$link_update);
        }
    }
}


/*
used by:
lib/datei.inc.php:1177:            echo link_form($refresh,$link_update);
lib/datei.inc.php:1180:            return link_form($refresh,$link_update);
*/
function link_form ($range_id, $updating=FALSE)
{
    global $SessSemName, $the_link, $protect, $description, $name, $folder_system_data, $user;
    if ($folder_system_data['update_link']) {
        $updating = TRUE;
    }
    if ($protect == 'on') {
        $protect = 'checked';
    }
    $print = '';
    $hiddenurl = FALSE;
    if ($updating == TRUE) {
        $query = "SELECT name, description, url, protected, user_id
                  FROM dokumente
                  WHERE dokument_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($range_id));
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $the_link = $row['url'];
            $protect  = $row['protected'];
            if ($protect == 1) {
                $protect = 'checked';
            }
            $name = $row['name'];
            $description = $row['description'];
            if ($row['user_id'] != $user->id) { // check if URL can be seen
                $url_parts = @parse_url( $the_link );
                if ($url_parts['user'] && $url_parts['user'] != 'anonymous') {
                    $hiddenurl = TRUE;
                }

            }
        }
    }
    if ($folder_system_data["linkerror"]==TRUE) {
        $print.="<hr>".  Icon::create('accept', 'attention')->asImg(['class' => 'text-top']) . "<font color=\"red\">";
        $print.=_("&nbsp;FEHLER: unter der angegebenen Adresse wurde keine Datei gefunden.<br>&nbsp;Bitte kontrollieren Sie die Pfadangabe!");
        $print.="</font><hr>";
    }
    // Check if URL can be seen



    $print.="\n<br><br>" . _("Sie haben diesen Ordner zum Upload ausgew�hlt:") . "<br><br><center><table width=\"90%\" style=\"border: 1px solid #000000;\" border=0 cellpadding=2 cellspacing=3>";

    $print.="</font></td></tr>";
    $print.= "\n<form enctype=\"multipart/form-data\" name=\"link_form\" action=\"" . URLHelper::getLink('#anker') . "\" method=\"post\">";
    $print.= CSRFProtection::tokenTag();
    $print.= "<tr><td class=\"content_seperator\" colspan=2><font size=-1>" . _("1. Geben Sie hier den <b>vollst�ndigen Pfad</b> zu der Datei an, die Sie verlinken wollen.") . " </font></td></tr>";
    $print.= "\n<tr>";
    $print.= "\n<td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><label><font size=-1>&nbsp;" . _("Dateipfad:") . "&nbsp;</font><br>";
    if ($hiddenurl)
        $print.= "&nbsp;<input name=\"the_link\" type=\"text\"  style=\"width: 70%\" size=\"30\" value=\"***\"></label>&nbsp;</td></td>";
    else
        $print.= '&nbsp;<input name="the_link" type="text"  style="width: 70%" size="30" value="'.$the_link.'"></label>&nbsp;</td></td>';
    $print.= "\n</tr>";
    if (!$refresh) {

        $print.= "<tr><td class=\"content_seperator\" colspan=2><font size=-1>" . _("2. Schutz gem�� Urheberrecht.") . "</font></td></tr>";
        $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><font size=-1>&nbsp;" . _("Dieses Dokument ist frei von Rechten Dritter:") . "&nbsp;";
        $print.= "\n&nbsp;<label><input type=\"RADIO\" name=\"protect\" value=\"0\"".(!$protect ? "checked" :"") .'>'._("Ja");
        $print.= "</label>\n&nbsp;<label><input type=\"RADIO\" name=\"protect\" value=\"1\"".($protect ? "checked" :"") .'>'._("Nein");

        $print.= "</label><tr><td class=\"content_seperator\" colspan=2><font size=-1>" . _("3. Geben Sie eine kurze Beschreibung und einen Namen f�r die Datei ein.") . "</font></td></tr>";
        $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><label><font size=-1>&nbsp;" . _("Name:") . "&nbsp;</font><br>";
        $print.= "\n".'&nbsp;<input type="text" name="name" id="name" style="width: 70%" size="40" maxlength"255" value="'.$name.'"></label></td></tr>';

        $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"left\" valign=\"center\"><label><font size=-1>&nbsp;" . _("Beschreibung:") . "&nbsp;</font><br>";
        $print.= "\n&nbsp;<textarea name=\"description\" id=\"description\" style=\"width: 70%\" COLS=40 ROWS=3 WRAP=PHYSICAL>$description</textarea></label>&nbsp;</td></tr>";
        $print.= "\n<tr><td class=\"content_seperator\"colspan=2 ><font size=-1>" . _("4. Klicken Sie auf <b>'Absenden'</b>, um die Datei zu verlinken") . "</font></td></tr>";
    } else
        $print.= "\n<tr><td class=\"content_seperator\"colspan=2 ><font size=-1>" . _("2. Klicken Sie auf <b>'Absenden'</b>, um die Datei hochzuladen und damit die alte Version zu �berschreiben.") . "</font></td></tr>";
    $print.= "\n<tr><td class=\"table_row_even\" colspan=2 align=\"center\" valign=\"center\">";

    $print .= '<div class="button-group">';
    $print .= Button::createAccept(_("Absenden"), "create");
    $print .= LinkButton::createCancel(_("Abbrechen"), URLHelper::getURL("?cancel_x=true#anker"));
    $print .= '</div>';

    $print .="</td></tr>";
    $print.= "\n<input type=\"hidden\" name=\"upload_seminar_id\" value=\"".$SessSemName[1]."\">";
    if ($updating == TRUE) {
        $print.= "\n<input type=\"hidden\" name=\"cmd\" value=\"link_update\">";
        $print.= "\n<input type=\"hidden\" name=\"link_update\" value=\"$range_id\">";
    } else {
        $print.= "\n<input type=\"hidden\" name=\"cmd\" value=\"link\">";
    }
    $print.= "\n</form></table><br></center>";

    return $print;

}

## Ende Upload Funktionen ################################################################################

/**
 * Displays the body of a file containing the decription, downloadbuttons and change-forms
 *
 */
/*
used by:
lib/datei.inc.php:1550:        display_file_body($datei, $folder_id, $open, $change, $move, $upload, $all, $refresh, $filelink);
public/folder.php:681:            display_file_body($datei, null, $folder_system_data["open"], null, $folder_system_data["move"], $folder_system_data["upload"], $all, $folder_system_data["refresh"], $folder_system_data["link"]);
*/
function display_file_body($datei, $folder_id, $open, $change, $move, $upload, $all, $refresh=FALSE, $filelink="") {
    global $rechte, $user, $SessionSeminar;
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    $type = $datei['url'] != '' ? 6 : 0;

    $content='';

    if ($change == $datei["dokument_id"]) {     //Aenderungsmodus, Formular aufbauen
        if ($datei["protected"]==1)
            $protect = "checked";
        $content.= "\n&nbsp;<input type=\"CHECKBOX\" name=\"change_protected\" value=\"1\" $protect>&nbsp;"._("gesch�tzter Inhalt")."</br>";
        $content.= "<br><textarea name=\"change_description\" aria-label=\"Beschreibung des Ordners eingeben\" rows=\"3\" cols=\"40\">".htmlReady($datei["description"])."</textarea><br>";

        $content .= '<div class="button-group">';
        $content .= Button::createAccept(_("�bernehmen"));
        $content .= Button::createCancel(_("Abbrechen"), "cancel");
        $content .= '</div>';

        $content.= "<input type=\"hidden\" name=\"open\" value=\"".htmlReady($datei["dokument_id"])."_sc_\">";
        $content.= "<input type=\"hidden\" name=\"type\" value=\"0\">";
    } else {
        $content = '';
        $media_url = GetDownloadLink($datei['dokument_id'], $datei['filename'], $type);
        $media_type = get_mime_type($datei['filename']);
        if ($media_type == 'video/x-flv') {
            $cfg = Config::GetInstance();
            $DOCUMENTS_EMBEDD_FLASH_MOVIES = $cfg->getValue('DOCUMENTS_EMBEDD_FLASH_MOVIES');
            if (trim($DOCUMENTS_EMBEDD_FLASH_MOVIES) != 'deny') {
                $flash_player = get_flash_player($datei['dokument_id'], $datei['filename'], $type);
                $content = "<div style=\"margin-bottom: 10px; height: {$flash_player['height']}; width: {$flash_player['width']};\">" . $flash_player['player'] . '</div>';
            }
        } else if (mb_strpos($media_type, 'video/') === 0 || $media_type == 'application/ogg') {
            $content = '<div class="preview">' . formatReady('[video]' . $media_url) . '<div>';
        } else if (mb_strpos($media_type, 'audio/') === 0) {
            $content = '<div class="preview">' . formatReady('[audio]' . $media_url) . '<div>';
        } else if (mb_strpos($media_type, 'image/') === 0) {
            $content = '<div class="preview">' . formatReady('[img]' . $media_url) . '<div>';
        }
        if ($datei["description"]) {
            $content .= htmlReady($datei["description"], TRUE, TRUE);
        } else {
            $content .= _("Keine Beschreibung vorhanden");
        }
        $content .=  "<br><br>" . sprintf(_("<b>Dateigr��e:</b> %s kB"), round ($datei["filesize"] / 1024));
        $content .=  "&nbsp; " . sprintf(_("<b>Dateiname:</b> %s "),htmlReady($datei['filename']));
        if ($all) {
            $content .= "<br>" . sprintf("<b>%s</b> <a class=\"link-intern\" title=\"%s\" href=\"%s\">%s</a>",
                    _("Ordner:"),
                    _("Diesen Ordner in der Ordneransicht �ffnen"),
                    URLHelper::getLink('folder.php#anker', array('open' => $datei['range_id'], 'data' => null, 'cmd' => 'tree')),
                    htmlReady($folder_tree->getShortPath($datei['range_id'], null, '/', 1)));
        }
    }

    if ($move == $datei["dokument_id"])
        $content.="<br>" . sprintf(_("Diese Datei wurde zum Verschieben / Kopieren markiert. Bitte w�hlen Sie das Einf�gen-Symbol %s, um diese Datei in den gew�nschten Ordner zu verschieben / kopieren. Wenn Sie diese Datei in eine andere Veranstaltung verschieben / kopieren m�chten, w�hlen Sie die gew�nschte Veranstaltung oben auf der Seite aus (sofern Sie Dozent oder Tutor in einer anderen Veranstaltung sind)."),
                                   Icon::create('arr_2right', 'sort', ['title' => _("Klicken Sie dieses Symbol, um diese Datei in einen anderen Ordner einzuf�gen")])->asImg());

    $content.= "\n";

    if ($upload == $datei["dokument_id"])
        $content.=upload_item ($upload,FALSE,FALSE,$refresh);

    //Editbereich ertstellen
    $edit='';

    if (($change != $datei["dokument_id"]) && ($upload != $datei["dokument_id"]) && $filelink != $datei["dokument_id"]) {

        $edit .= '<div class="button-group">';

        # Kn�pfe: herunterladen/ZIP
        if (check_protected_download($datei['dokument_id'])) {

            $edit .= LinkButton::createExtern(_("Herunterladen"), GetDownloadLink( $datei['dokument_id'], $datei['filename'], $type, 'force'));

            $fext = getFileExtension(mb_strtolower($datei['filename']));
            if (($type != '6') && ($fext != 'zip') && ($fext != 'tgz') && ($fext != 'gz') && ($fext != 'bz2')) {
                $edit .= LinkButton::createExtern(_("Als ZIP-Archiv"), GetDownloadLink( $datei['dokument_id'], $datei['filename'], $type, 'zip'));
            }
        }

        if (($rechte) || ($datei["user_id"] == $user->id && $folder_tree->isWritable($datei["range_id"], $user->id))) {
            # Kn�pfe: bearbeiten/aktualisieren
            if ($type!=6) {
                $edit .= LinkButton::create(_("Bearbeiten"),
                                            URLHelper::getURL("?open=".$datei["dokument_id"]."_c_#anker"));
                $edit .= LinkButton::create(_("Aktualisieren"),
                                            URLHelper::getURL("?open=".$datei["dokument_id"]."_rfu_#anker"));
            } else {
                //wenn Datei ein Link ist:
                $edit .= LinkButton::create(_("Bearbeiten"),
                                            URLHelper::getURL("?open=".$datei["dokument_id"]."_led_#anker"));
            }

            # Kn�pfe: verschieben/kopieren
            if (!$all){
                $edit .= LinkButton::create(_("Verschieben"),
                                            URLHelper::getURL("?open=".$datei["dokument_id"]."_m_#anker"));
                $edit .= LinkButton::create(_("Kopieren"),
                                            URLHelper::getURL("?open=".$datei["dokument_id"]."_co_#anker"));
            }

            # Knopf: l�schen
            $edit .= LinkButton::create(_("L�schen"),
                                        URLHelper::getURL("?open=".$datei["dokument_id"]."_fd_#anker"));
        }
        $edit .= '</div>';
    }

    //Dokument_Body ausgeben; dies ist auch der Bereich, der �ber Ajax abgerufen werden wird
    print "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0>";
    if ($datei["protected"]) {
        if(check_protected_download($datei["dokument_id"])){
            $detail .=_("Sie darf nur im Rahmen dieser Veranstaltung verwendet werden, jede weitere Verbreitung ist unzul�ssig!");
        } else {
            $detail .= _("Sie k�nnen diese Datei nicht herunterladen, so lange diese Veranstaltung einen offenen Teilnehmerkreis aufweist.");
        }
        $content .= MessageBox::info(_("Diese Datei ist urheberrechtlich gesch�tzt."), array($detail));
    }
    if ($filelink == $datei["dokument_id"])
        $content .= link_item($datei["dokument_id"],FALSE,FALSE,$datei["dokument_id"]);
    printcontent ("100%",TRUE, $content, $edit);
    print "</table>";
}

//$countfiles is important, so that each file_line has its own unique id and can be found by javascript.
$countfiles = 0;
/**
 * Displays one file/document with all of its information and options.
 *
 */
/*
lib/datei.inc.php:1822:                display_file_line($datei, $folder_id, $open, $change, $move, $upload, FALSE, $refresh, $filelink, $anchor_id, $file_pos);
public/folder.php:1209:                    display_file_line($datei,
*/
function display_file_line ($datei, $folder_id, $open, $change, $move, $upload, $all, $refresh=FALSE, $filelink="", $anchor_id, $position = "middle") {
    global $_fullname_sql,$SessionSeminar,$SessSemName, $rechte, $anfang,
        $user, $SemSecLevelWrite, $SemUserStatus, $check_all, $countfiles;
    //Einbinden einer Klasse, die Informationen �ber den ganzen Baum enth�lt
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));
    $javascriptok = true;
    print "\n\t<div class=\"".($rechte ? "draggable" : "")."\" id=\"file_".$folder_id."_$countfiles\">";
    print "<div style=\"display:none\" id=\"getmd5_fi".$folder_id."_$countfiles\">".$datei['dokument_id']."</div>";
    print "<table cellpadding=0 border=0 cellspacing=0 width=\"100%\"><tr class=\"handle\">";
    if (!$all) {
        print "<td class=\"tree-elbow-end\">" . Assets::img("datatree_2.gif") . "</td>";
    }

    //Farbe des Pfeils bestimmen:
    $chdate = (($datei["chdate"]) ? $datei["chdate"] : $datei["mkdate"]);
    if (object_get_visit($SessSemName[1], "documents") < $chdate)
        $timecolor = "#FF0000";
    else {
        $timediff = (int) log((time() - doc_newest($folder_id)) / 86400 + 1) * 15;
        if ($timediff >= 68)
            $timediff = 68;
        $red = dechex(255 - $timediff);
        $other = dechex(119 + $timediff);
        $timecolor= "#" . $red . $other . $other;
    }

    if ($open[$datei["dokument_id"]]) {
        print "<td id=\"file_".$datei["dokument_id"]."_arrow_td\" nowrap valign=\"top\"" .
            "align=\"left\" width=1% bgcolor=\"$timecolor\" class=\"printhead3\" valign=\"bottom\"><a href=\"";
        print URLHelper::getLink("?close=".$datei["dokument_id"]."#anker");
        print "\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefilebody('".
            $datei["dokument_id"]."', '".$SessionSeminar."')\">".
            Assets::img('forumgraurunt2.png', tooltip2(_('Objekt zuklappen')) + array('id' => 'file_'. $datei["dokument_id"] . '_arrow_img')).
            "</a></td>";
    } else {
        print "<td id=\"file_".$datei["dokument_id"]."_arrow_td\" nowrap valign=\"top\" align=\"left\" width=1% bgcolor=\"$timecolor\" class=\"printhead2\" valign=\"bottom\"><a href=\"";
        print URLHelper::getLink("?open=".$datei["dokument_id"]."#anker");
        print "\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefilebody('".
            $datei["dokument_id"]."', '".$SessionSeminar."')\">".
            Assets::img('forumgrau2.png', tooltip2(_('Objekt aufklappen')) + array('id' => 'file_'. $datei["dokument_id"] . '_arrow_img')).
            "</a></td>";
    }

    // -> Pfeile zum Verschieben (bzw. die Ziehfl�che)
    if ((!$all) && ($rechte)) {
        $countfiles++;
        $bewegeflaeche = "<span class=\"updown_marker\" id=\"pfeile_".$datei["dokument_id"]."\">";
        if (($position == "middle") || ($position == "bottom")) {
            $bewegeflaeche .= "<a href=\"".URLHelper::getLink('?open='.$datei['dokument_id'])."_mfu_\" title=\""._("Datei nach oben schieben").
                "\">" . Icon::create('arr_2up', 'sort')->asImg(['class' => 'text-top']) . "</a>";
        }
        if (($position == "middle") || ($position == "top")) {
            $bewegeflaeche .= "<a href=\"".URLHelper::getLink('?open='.
                    $datei['dokument_id'])."_mfd_\" title=\""._("Datei nach unten schieben").
                    "\">". Icon::create('arr_2down', 'sort')->asImg(['class' => 'text-top']) . "</a>";
        }
        $bewegeflaeche .= "</span>";
    }

    print "<td class=\"printhead\" valign=\"bottom\">";
    if ($change == $datei["dokument_id"]) {
        print "<span id=\"file_".$datei["dokument_id"]."_header\" style=\"font-weight: bold\"><a href=\"".URLHelper::getLink("?close=".$datei["dokument_id"]."#anker")."\" class=\"tree\"";
        print ' name="anker"></a>';
        print GetFileIcon(getFileExtension($datei['filename']))->asImg();
        print "<input style=\"font-size: 8pt; width: 50%;\" type=\"text\" size=\"20\" maxlength=\"255\" name=\"change_name\" aria-label=\"Ordnername eingeben\" value=\"".htmlReady($datei["name"])."\"></b>";
    } else {
        if (($move == $datei["dokument_id"]) ||  ($upload == $datei["dokument_id"]) || ($anchor_id == $datei["dokument_id"])) {
            print "<a name=\"anker\"></a>";
        }
        $type = ($datei['url'] != '')? 6 : 0;
        // LUH Spezerei:
        if (check_protected_download($datei["dokument_id"])) {
            print "<a href=\"".GetDownloadLink( $datei["dokument_id"], $datei["filename"], $type, "normal")."\" class=\"extern\">".GetFileIcon(getFileExtension($datei['filename']))->asImg()."</a>";
        } else {
            print Icon::create('info-circle', 'inactive')->asImg();
        }
        //Jetzt folgt der Link zum Aufklappen
        if ($open[$datei["dokument_id"]]) {
      print "<a href=\"".URLHelper::getLink("?close=".$datei["dokument_id"]."#anker")."\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefilebody('".$datei["dokument_id"]."')\">";
            print "&nbsp;<span id=\"file_".$datei["dokument_id"]."_header\" style=\"font-weight: bold\">";
        } else {
            print "<a href=\"".URLHelper::getLink("?open=".$datei["dokument_id"]."#anker")."\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefilebody('".$datei["dokument_id"]."')\">";
            print "&nbsp;<span id=\"file_".$datei["dokument_id"]."_header\" style=\"font-weight: normal\">";
        }
        print htmlReady($datei['t_name']);

        print "</span>";
    }

    //add the size
    print '&nbsp;&nbsp;(' . relsize($datei['filesize'], $datei['filesize'] < 1024);

    //add number of downloads
    print " / ".(($datei["downloads"] == 1) ? $datei["downloads"]." "._("Download") : $datei["downloads"]." "._("Downloads")).")";


    //So und jetzt die rechtsb�ndigen Sachen:
    print "</a></td><td align=\"right\" class=\"printhead\" valign=\"bottom\">";
    if ($datei['username']) {
        print "<a href=\"".URLHelper::getLink('dispatch.php/profile?username='.$datei['username'])."\">".htmlReady($datei['fullname'])."</a> ";
    } else {
        print htmlReady($datei['author_name']);
    }
    print $bewegeflaeche." ";

    //Workaround for older data from previous versions (chdate is 0)
    print " ".date("d.m.Y - H:i", (($datei["chdate"]) ? $datei["chdate"] : $datei["mkdate"]));

    if ($all) {
      if ((!$upload) && ($datei["url"]=="") && check_protected_download($datei["dokument_id"])) {
        $checked = ($check_all || in_array($datei["dokument_id"], Request::getArray('download_ids'))) ? 'checked' : '';
        $box = sprintf ("<input type=\"CHECKBOX\" %s name=\"download_ids[]\" value=\"%s\">",$checked , $datei["dokument_id"]);
        print $box;
      } else {
        echo Icon::create('decline', 'inactive', ['title' => _("Diese Datei kann nicht als ZIP-Archiv heruntergeladen werden."), 'style' => 'padding-left:5px;'])->asImg();
    }
    }
    print "</td></tr>";

    //Ab jetzt kommt der Bereich zum Runterladen und Bearbeiten:
    if (isset($open[$datei["dokument_id"]])) {
        //Dokument-Content ausgeben
        print "<tr id=\"file_".$datei["dokument_id"]."_body_row\">".(($all) ? "" : "<td></td>")."<td colspan=3><div id=\"file_".$datei["dokument_id"]."_body\">";
        //Der eigentliche Teil ist outsourced in die folgende Funktion,
        //damit der K�rper auch �ber Ajax abgerufen werden kann.
        display_file_body($datei, $folder_id, $open, $change, $move, $upload, $all, $refresh, $filelink);
    } else {
        print "<tr id=\"file_".$datei["dokument_id"]."_body_row\">".(($all) ? "" : "<td></td>")."<td colspan=3><div id=\"file_".$datei["dokument_id"]."_body\" style=\"display:none\">";
    }
    print "</div></td></tr></table>\n\t</div>";
}

/**
 * Displays the body of a folder including the description, changeform, subfolder and files
 *
 */
/*
used by:
lib/datei.inc.php:2059:        display_folder_body($folder_id, $open, $change, $move, $upload, $refresh, $filelink, $anchor_id, $depth - 3);
public/folder.php:688:            display_folder_body(Request::quoted("getfolderbody"), $folder_system_data["open"], null, $folder_system_data["move"], null, null, null, null);
*/
function display_folder_body($folder_id, $open, $change, $move, $upload, $refresh=FALSE, $filelink="", $anchor_id, $level = 0) {
    global $_fullname_sql, $SessionSeminar, $SemUserStatus, $SessSemName, $user, $perm, $rechte, $countfolder;
    $db = DBManager::get();
    //Einbinden einer Klasse, die Informationen �ber den ganzen Baum enth�lt
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));
    //Hole alle Informationen, die es �ber $folder_id gibt
    $query = "SELECT ". $_fullname_sql['full'] ." AS fullname , username, folder_id, a.range_id, a.user_id, name, a.description, a.mkdate, a.chdate FROM folder a LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE a.folder_id = '$folder_id' ORDER BY a.name, a.chdate";
    $result = $db->query($query)->fetch();
    $document_count = doc_count($folder_id);
    $super_folder = $folder_tree->getNextSuperFolder($folder_id);
    $is_issue_folder = ((count($folder_tree->getParents($folder_id)) > 1) && IssueDB::isIssue($result["range_id"]));
    if ($is_issue_folder) {
        $dates_for_issue = IssueDB::getDatesforIssue($result['range_id']);
    }
    print "<table cellpadding=0 border=0 cellspacing=0 width=\"100%\">";

    //Ausgabe der Optionen zu dem Ordner mit Beschreibung, Kn�pfen und PiPaPo
    print "<tr>";

    if ((($document_count > 0) || ($folder_tree->hasKids($folder_id))) && ($folder_tree->isReadable($folder_id))) {
        print "<td style=\"background-image: url(".Assets::image_path('datatree_grau.gif') . "); background-repeat: repeat-y;\">";
        print Assets::img('datatree_grau.gif');
        print "</td>";
    } else {
        print "<td class=\"printcontent\">&nbsp;</td>";
    }
    print "<td width=100% class=\"printcontent\" style=\"font-align: center\">";

    $content='';
    if ($super_folder){
        $content .=  Icon::create('lock-locked', 'inactive')->asImg(['class' => 'texttop']) . '&nbsp;'
            . sprintf(_("Dieser Ordner ist nicht zug�nglich, da der �bergeordnete Ordner \"%s\" nicht lesbar oder nicht sichtbar ist!"), htmlReady($folder_tree->getValue($super_folder,'name')))
            . '<hr>';
    }
    if ($folder_tree->isExerciseFolder($folder_id)){
        $content .=  Icon::create('edit', 'inactive')->asImg(['class' => 'texttop']) . '&nbsp;'
                . _("Dieser Ordner ist ein Hausaufgabenordner. Es k�nnen nur Dateien eingestellt werden.")
                . (!$rechte ? _("Sie selbst haben folgende Dateien in diesen Ordner eingestellt:")
                . '<br><b>' . htmlReady(join('; ', get_user_documents_in_folder($folder_id, $GLOBALS['user']->id))).'</b>' : '')
                . '<hr>';
    }
    if ($is_issue_folder) {
        $dates = array();
        foreach ($dates_for_issue as $date) {
            $dates[] = strftime("%x", $date['date']);
        }
        $content .= _("Dieser Ordner ist ein themenbezogener Dateiordner.");
        if(count($dates)){
            $content .= '&nbsp;' ._("Folgende Termine sind diesem Thema zugeordnet:")
            . '<br><b>' . htmlReady(join('; ', $dates)).'</b>';
        }
        $content .=  '<hr>';
    }
    $is_group_folder = $folder_tree->isGroupFolder($folder_id);
    if ($is_group_folder){
        $content .=  sprintf(
            _('Dieser Ordner geh�rt der Gruppe <b>%s</b>. Nur Mitglieder dieser Gruppe k�nnen diesen Ordner sehen.'
                . ' Dieser Ordner kann nicht verschoben oder kopiert werden.'),
            htmlReady(Statusgruppen::find($result['range_id'])->name)
        ) . '<hr>';
    }
    //Contentbereich erstellen
    if ($change == $folder_id) { //Aenderungsmodus, zweiter Teil
        $content .= '<textarea name="change_description"'
            . ' style="width:98%" class="add_toolbar wysiwyg"'
            . ' aria-label="Beschreibung des Ordners eingeben"'
            . ' rows="3">'
            . htmlReady($result["description"])
            . '</textarea>';

        if($rechte){
            $content .= '<div>';

            if ($folder_tree->permissions_activated){
                $content.= "\n<label><input style=\"vertical-align:middle\" type=\"checkbox\" value=\"1\" ".($folder_tree->isReadable($folder_id) ? "CHECKED" : "" ) . " name=\"perm_read\">&nbsp;";
                $content.= '<b>r</b> - ' . _("Lesen (Dateien k�nnen heruntergeladen werden)");
                $content.= "</label>\n<br><label><input style=\"vertical-align:middle\" type=\"checkbox\" value=\"1\" ".($folder_tree->isWritable($folder_id) ? "CHECKED" : "" ) . " name=\"perm_write\">&nbsp;";
                $content.= '<b>w</b> - ' . _("Schreiben (Dateien k�nnen heraufgeladen werden)");
                $content.= "</label>\n<br><label><input style=\"vertical-align:middle\" type=\"checkbox\" value=\"1\" ".($folder_tree->isExecutable($folder_id) ? "CHECKED" : "" ) . " name=\"perm_exec\">&nbsp;";
                $content.= '<b>x</b> - ' . _("Sichtbarkeit (Ordner wird angezeigt)") . '</label>';
            }
            if($level == 0 && $folder_tree->entity_type == 'sem'){
                $content .= "\n<br><label><input style=\"vertical-align:middle\" type=\"checkbox\" value=\"1\" ".($folder_tree->checkCreateFolder($folder_id) ? "CHECKED" : "" ) . " name=\"perm_folder\">&nbsp;";
                $content .= '<b>f</b> - ' . _("Ordner erstellen (Alle Nutzer k�nnen Ordner erstellen)") . '</label>';
            }

            $content .= '</div>';
        }

        $content .= '<div class="button-group">';
        $content .= Button::createAccept(_("�bernehmen"));
        $content .= Button::createCancel(_("Abbrechen"), "cancel");
        $content .= '</div>';

        $content.= "\n<input type=\"hidden\" name=\"open\" value=\"".$folder_id."_sc_\">";
        $content.="\n<input type=\"hidden\" name=\"type\" value=\"1\">";
    }
    elseif ($result["description"])
        $content .= formatReady($result["description"]);
    else
        $content .= _("Keine Beschreibung vorhanden");
    if ($move == $result["folder_id"]){
        $content .= "<br>"
                  . sprintf(_("Dieser Ordner wurde zum Verschieben / Kopieren markiert. Bitte w�hlen Sie das Einf�gen-Symbol %s, um ihn in den gew�nschten Ordner zu verschieben."),
                            Icon::create('arr_2right', 'sort', ['title' => _("Klicken Sie auf dieses Symbol, um diesen Ordner in einen anderen Ordner einzuf�gen.")])->asImg());
        if ($rechte) {
            $content .= _("Wenn Sie den Ordner in eine andere Veranstaltung verschieben / kopieren m�chten, w�hlen Sie die gew�nschte Veranstaltung oben auf der Seite aus.");
        }
    }
    if ($upload == $folder_id) {
        $content .= form($refresh);
    }
    // Abfrage ob Dateilink eingeleitet
    if ($filelink == $folder_id) {
        $content .= link_item($folder_id);
    }
    $content.= "\n";
    $edit='';
    //Editbereich erstellen
    if (($change != $folder_id) && ($upload != $folder_id) && ($filelink != $folder_id)) {
        if ($perm->have_studip_perm('autor', $SessionSeminar) && $folder_tree->isWritable($folder_id, $user->id))
            # Knopf: hochladen
            $edit .= LinkButton::create(_("Hochladen"), URLHelper::getURL("?open=".$folder_id."_u_&rand=".rand()."#anker"));

            # Knopf: Datei verlinken
            if ($rechte) {
                $edit .= LinkButton::create(_("Datei verlinken"), URLHelper::getURL("?open=".$folder_id."_l_&rand=".rand()."#anker"));
            }

            # Knopf: Ordner als ZIP
            if ($document_count && $folder_tree->isReadable($folder_id, $user->id)) {
                $edit .= LinkButton::create(_("Ordner als ZIP"), URLHelper::getURL("?folderzip=".$folder_id));
            }

            if ($perm->have_studip_perm('autor', $SessionSeminar) && $folder_tree->checkCreateFolder($folder_id, $user->id)) {
                if ($folder_tree->isWritable($folder_id, $user->id) && !$folder_tree->isExerciseFolder($folder_id, $user->id)) {

                    # Knopf: neuer Ordner
                    $edit .= LinkButton::create(_("Neuer Ordner"), URLHelper::getURL("?open=".$folder_id."_n_#anker"));

                    # Knopf: ZIP hochladen
                    if ($rechte && get_config('ZIP_UPLOAD_ENABLE')) {
                        $edit .= LinkButton::create(_("ZIP hochladen"), URLHelper::getURL("?open=".$folder_id."_z_&rand=".rand()."#anker"));
                    }
                }

                # Knopf: l�schen
                if ($rechte
                    ||
                    (
                        !$document_count
                        && $level !=0
                        && (
                            $folder_tree->isWritable($folder_id, $user->id)
                            && $folder_tree->isWritable($folder_tree->getValue($folder_id, 'parent_id'), $user->id)
                            && !$folder_tree->isExerciseFolder($folder_id, $user->id)
                        )
                    )
                ) {
                    $edit .= LinkButton::create(_("L�schen"), URLHelper::getURL("?open=".$folder_id."_d_"));
                }

                # Knopf: bearbeiten
                if ($folder_tree->isWritable($folder_id, $user->id) && !$folder_tree->isExerciseFolder($folder_id, $user->id)) {
                    $edit .= LinkButton::create(_("Bearbeiten"), URLHelper::getURL("?open=".$folder_id."_c_#anker"));
                }

                # verschieben
                if (
                    ($rechte && $result['range_id'] != $SessSemName[1])
                    ||
                    (
                        $level !=0
                        && (
                            $folder_tree->isWritable($folder_id, $user->id)
                            && $folder_tree->isWritable($folder_tree->getValue($folder_id, 'parent_id'), $user->id)
                            && !$folder_tree->isExerciseFolder($folder_id, $user->id)
                        )
                    )
                ) {
                    if (!$is_issue_folder && !$is_group_folder) {
                        $edit.= LinkButton::create(_("Verschieben"), URLHelper::getURL("?open=".$folder_id."_m_#anker"));
                    }
                }

                # Knopf: kopieren
                if ($rechte
                    ||
                    (
                        $level !=0
                        && !$folder_tree->isExerciseFolder($folder_id, $user->id)
                    )
                ) {
                    if (!$is_issue_folder && !$is_group_folder) {
                        $edit.= LinkButton::create(_("Kopieren"), URLHelper::getURL("?open=".$folder_id."_co_#anker"));
                    }
                }
            }

            # Knopf: sortieren
            if ($rechte) {
                $edit .= LinkButton::create(_("Sortieren"), URLHelper::getURL("?open=".$folder_id."_az_#anker"));
            }
    }

    if (!$edit) $edit = '&nbsp;';
    print "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr>";
    //Ordner-Content ausgeben
    printcontent ("99%", TRUE, $content, $edit);
    print "</tr></table>";

    print "</td></tr>";

    //Ein paar �berpr�fungen, was eigentlich angezeigt werden soll: Dateien und Unterordner
    $folders_kids = $folder_tree->getKids($folder_id);

    if ( ((count($folders_kids)) || ($document_count > 0))
            && ($folder_tree->isReadable($folder_id, $user->id)) ) {
        print "<tr>";
        //Der Navigationsast nach unten
        print "<td class=\"tree-elbow-line\">" . Assets::img("datatree_3.gif") . "</td>";
        //Mehrere Zeilen, die wiederum Dateien mit eventuellen Optionen sind.
        print "<td colspan=3>";

        print "<div class=\"folder_container".($rechte ? " sortable" : "")."\" id=\"folder_subfolders_".$folder_id."\">";
        //Unterordner darstellen:
        is_array($folders_kids) || $folders_kids = array();
        $subfolders = array();
        foreach ($folders_kids as $key => $unterordner) {
            if (($folder_tree->isExecutable($unterordner, $user->id))) { //bin ich Dozent oder Tutor?
                $subfolders[] = $unterordner;
            }
        }
        if ($subfolders) {
            foreach ($subfolders as $key => $subfolder) {
                $folder_pos = ((count($subfolders) > 1) ? (($key == 0) ? "top" : (($key == count($subfolders)-1) ? "bottom" : "middle")) : "alone");
                display_folder($subfolder, $open, $change, $move, $upload, $refresh, $filelink, $anchor_id, $folder_pos, false);
            }
        }
        print "</div>";

        //Dateien darstellen:
        $countfolder++;
        print "<div class=\"folder_container".($rechte ? " sortable" : "")."\" id=\"folder_".$folder_id."\">";
        if (($rechte) || ($folder_tree->isReadable($folder_id, $user->id))) {
            $query = "SELECT a.*,". $_fullname_sql['full'] ." AS fullname, " .
                            "username, " .
                            "IF(IFNULL(a.name,'')='', a.filename,a.name) AS t_name " .
                    "FROM dokumente a " .
                            "LEFT JOIN auth_user_md5 USING (user_id) " .
                            "LEFT JOIN user_info USING (user_id) " .
                    "WHERE range_id = '".$result["folder_id"]."' " .
                    "ORDER BY a.priority ASC, t_name ASC, a.chdate DESC ";
            $result2 = $db->query($query)->fetchAll();
            foreach ($result2 as $key => $datei) {
                $file_pos = ((count($result2) > 1) ? (($key == 0) ? "top" : (($key == count($result2)-1) ? "bottom" : "middle")) : "alone");
                display_file_line($datei, $folder_id, $open, $change, $move, $upload, FALSE, $refresh, $filelink, $anchor_id, $file_pos);
            }
        }
        print "</div>";
        print "</td></tr>";

    }
    print "</table>";   //Ende der zweiten Tabelle
}

$countfolder = 0;
$droppable_folder = 0;
/**
 * Displays the folder and all of its documents and recursively subfolders.
 * This function is not dependent on the recursive-level so it looks as if it all starts from here.
 *
 */
/*
used by:
lib/datei.inc.php:1572:lib/datei.inc.php:2059:        display_folder_body($folder_id, $open, $change, $move, $upload, $refresh, $filelink, $anchor_id, $depth - 3);
lib/datei.inc.php:1573:public/folder.php:688:            display_folder_body(Request::quoted("getfolderbody"), $folder_system_data["open"], null, $folder_system_data["move"], null, null, null, null);
lib/datei.inc.php:1575:function display_folder_body($folder_id, $open, $change, $move, $upload, $refresh=FALSE, $filelink="", $anchor_id, $level = 0) {
lib/datei.inc.php:1811:                display_folder($subfolder, $open, $change, $move, $upload, $refresh, $filelink, $anchor_id, $folder_pos, false);
lib/datei.inc.php:2064:        display_folder_body($folder_id, $open, $change, $move, $upload, $refresh, $filelink, $anchor_id, $depth - 3);
public/folder.php:688:            display_folder_body(Request::quoted("getfolderbody"), $folder_system_data["open"], null, $folder_system_data["move"], null, null, null, null);
public/folder.php:989:                display_folder($general_folder["folder_id"],
public/folder.php:1009:                display_folder($general_folder["folder_id"],
public/folder.php:1036:                  display_folder($row['folder_id'],
public/folder.php:1082:                        display_folder($folder["folder_id"],
*/
function display_folder ($folder_id, $open, $change, $move, $upload, $refresh=FALSE, $filelink="", $anchor_id, $position="middle", $isissuefolder = false) {
    global $_fullname_sql,$SessionSeminar,$SessSemName, $rechte, $anfang,
        $user, $SemSecLevelWrite, $SemUserStatus, $check_all, $countfolder, $droppable_folder;
    $option = true;
    $countfolder++;
    $more = true;
    $db = DBManager::get();
    $droppable_folder++;
    $javascriptok = true;
    //Einbinden einer Klasse, die Informationen �ber den ganzen Baum enth�lt
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $SessionSeminar));

    //Hole alle Informationen, die es �ber $folder_id gibt
    $query = "SELECT ". $_fullname_sql['full'] ." AS fullname , username, folder_id, a.range_id, a.user_id, name, a.description, a.mkdate, a.chdate FROM folder a LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE a.folder_id = '$folder_id' ORDER BY a.name, a.chdate";
    $result = $db->query($query)->fetch();

    $depth = count($folder_tree->getParents($folder_id));
    print "<div id=\"folder_".($depth > 2 ? $result['range_id'] : "root")."_".$countfolder."\"".($rechte ? " class=\"draggable_folder\"" : "").">";
    print "<div style=\"display:none\" id=\"getmd5_fo".$result['range_id']."_".$countfolder."\">".$folder_id."</div>";
    print "<table cellpadding=0 border=0 cellspacing=0 width=\"100%\"><tr>";

    //Abzweigung, wenn Ordner ein Unterordner ist
    if ($depth > 2) // root > folder > subfolder
        print "<td class=\"tree-elbow-end\">" . Assets::img("datatree_2.gif") . "</td>";
    else
        print "<td></td>";
    print "<td valign=\"bottom\">";

    //Farbe des Pfeils bestimmen:
    $chdate = (($result["chdate"]) ? $result["chdate"] : $result["mkdate"]);
    if (object_get_visit($SessSemName[1], "documents") < $chdate)
        $neuer_ordner = TRUE;
    else
        $neuer_ordner = FALSE;
    if ($neuer_ordner == TRUE)
        $timecolor = "#FF0000";
    else {
        $timediff = (int) log((time() - doc_newest($folder_id)) / 86400 + 1) * 15;
        if ($timediff >= 68)
            $timediff = 68;
        $red = dechex(255 - $timediff);
        $other = dechex(119 + $timediff);
        $timecolor= "#" . $red . $other . $other;
    }

    //Jetzt f�ngt eine zweite Tabelle an mit den Zeilen: Titel, Beschreibung und Kn�pfe, Unterdateien und Unterordner
    if ($rechte) {
        print "<div class=\"droppable handle\" id=\"dropfolder_$folder_id\">";
    }
    print "<table cellpadding=0 border=0 cellspacing=0 width=\"100%\" id=\"droppable_folder_$droppable_folder\"><tr>";

    // -> Pfeile zum Verschieben (bzw. die Ziehfl�che)
    if (($rechte) && ($depth > 2)) {
        $bewegeflaeche = "<span class=\"updown_marker\" id=\"pfeile_".$folder_id."\">";
        if (($position == "middle") || ($position == "bottom")) {
            $bewegeflaeche .= "<a href=\"".URLHelper::getLink('?open='.$folder_id)."_mfou_\" title=\""._("Nach oben verschieben").
                    "\">" . Icon::create('arr_2up', 'sort')->asImg(['class' => 'text-top']) . "</a>";
        }
        if (($position == "middle") || ($position == "top")) {
            $bewegeflaeche .= "<a href=\"".URLHelper::getLink('?open='.
                    $folder_id)."_mfod_\" title=\""._("Nach unten verschieben").
                    "\">" . Icon::create('arr_2down', 'sort')->asImg(['class' => 'text-top']) . "</a>";
        }
        $bewegeflaeche .= "</span>";
    }

    //Jetzt folgt der Link zum Aufklappen
    if ($open[$folder_id]) {
        //print "<td width=1px class=\"printhead\">&nbsp;</td>";
        print "<td id=\"folder_".$folder_id."_arrow_td\" nowrap valign=\"top\" align=\"left\" width=1% bgcolor=\"$timecolor\" class=\"printhead3\" valign=\"bottom\">";
        print "<a href=\"".URLHelper::getLink("?close=".$folder_id."#anker");
        print "\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefolderbody('".$folder_id."')\">";
        print Assets::img('forumgraurunt2.png', tooltip2(_('Objekt zuklappen')) + array('id' => 'folder_' . $folder_id . '_arrow_img'));
        print "</a>";
        print "</td>";
        //print ($javascriptok ? "<td class=\"printhead\"><a href=\"Javascript: changefolderbody('".$folder_id."')\" class=\"tree\"><span id=\"folder_".$folder_id."_header\" style=\"font-weight: bold\">" :
        print "<td class=\"printhead\" valign=\"bottom\">";
        if ($move && ($move != $folder_id) && $folder_tree->isWritable($folder_id, $user->id) && (!$folder_tree->isFolder($move) || ($folder_tree->checkCreateFolder($folder_id, $user->id) && !$folder_tree->isExerciseFolder($folder_id, $user->id)))){
                print "<a href=\"".URLHelper::getLink("?open=".$folder_id."_md_")."\">";
                print Icon::create('arr_2right', 'sort')->asImg();
                print "</a>&nbsp;";
        }
        if (($anchor_id == $folder_id) || (($move == $folder_id))) {
            print "<a name=\"anker\"></a>";
        }
        print "<a href=\"".URLHelper::getLink("?close=".$folder_id."#anker")."\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefolderbody('".$folder_id."')\"><span id=\"folder_".$folder_id."_header\" style=\"font-weight: bold\">";
    } else {
        //print "<td width=1px class=\"printhead\">&nbsp;</td>";
        print "<td id=\"folder_".$folder_id."_arrow_td\" nowrap valign=\"top\" align=\"left\" width=1% bgcolor=\"$timecolor\" class=\"printhead2\" valign=\"bottom\">";
        print "<a href=\"";
        print URLHelper::getLink("?open=".$folder_id."#anker");
        print "\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefolderbody('".$folder_id."')\">";
        print Assets::img('forumgrau2.png', tooltip2(_('Objekt aufklappen')) + array('id' => 'folder_' . $folder_id . '_arrow_img'));
        print "</a></td>";
        print "<td class=\"printhead\" valign=\"bottom\">";
        if ($move && ($move != $folder_id) && $folder_tree->isWritable($folder_id, $user->id) && (!$folder_tree->isFolder($move) || ($folder_tree->checkCreateFolder($folder_id, $user->id) && !$folder_tree->isExerciseFolder($folder_id, $user->id)))){
            print "&nbsp;<a href=\"".URLHelper::getLink("?open=".$folder_id."_md_")."\">";
            print Icon::create('arr_2right', 'sort')->asImg();
            print "</a>&nbsp";
        }
        print "<a href=\"".URLHelper::getLink("?open=".$folder_id."#anker")."\" class=\"tree\" " .
                "onClick=\"return STUDIP.Filesystem.changefolderbody('".$folder_id."')\"><span id=\"folder_".$folder_id."_header\" " .
                "style=\"font-weight: normal\">";
    }

    $document_count = doc_count($folder_id);

    if ($document_count > 0) {
        print Icon::create('folder-full', 'clickable')->asImg() . '&nbsp;';
    } else {
        print Icon::create('folder-empty', 'clickable')->asImg() . '&nbsp;';
    }

    //Pfeile, wenn Datei bewegt werden soll
    if ($move && ($folder_id != $move) && $folder_tree->isWritable($folder_id, $user->id) && (!$folder_tree->isFolder($move) || ($folder_tree->checkCreateFolder($folder_id, $user->id) && !$folder_tree->isExerciseFolder($folder_id, $user->id)))){
        print "</a><span class=\"move_arrows\"><a href=\"".URLHelper::getLink("?open=".$folder_id."_md_")."\">";
        print Icon::create('arr_2right', 'sort')->asImg();
        print "</a></span>";
        if ($open[$folder_id])
            print "<a href=\"".URLHelper::getLink("?close=".$folder_id."#anker")."\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefolderbody('".$folder_id."')\">";
        else
            print "<a href=\"".URLHelper::getLink("?open=".$folder_id."#anker")."\" class=\"tree\" onClick=\"return STUDIP.Filesystem.changefolderbody('".$folder_id."')\">";
    }

    //Dateiname, Rechte und Dokumente anzeigen
    $tmp_titel = htmlReady(mila($result['name']));
    if ($isissuefolder) {
        $issue_id = $db->query("SELECT range_id FROM folder WHERE folder_id = ".$db->quote($folder_id))->fetch();
        $dates_for_issue = IssueDB::getDatesforIssue($issue_id['range_id']);
        $dates_title = array();
        foreach ($dates_for_issue as $date) {
            $dates_title[] .= date('d.m.y, H:i', $date['date']).' - '.date('H:i', $date['end_time']);
        }

        if (!empty($dates_title)) {
            $tmp_titel = sprintf(_("Sitzung am: %s"), implode(', ', $dates_title)) .
                 ", " . ($tmp_titel ? $tmp_titel : _("Ohne Titel"));
        } else {
            $tmp_titel = $tmp_titel ? $tmp_titel : _("Ohne Titel");
        }
    }

    if (($change == $folder_id)
            && (!$isissuefolder)
            && ((count($folder_tree->getParents($folder_id)) > 1)
             || $result['range_id'] == md5($SessSemName[1] . 'top_folder')
             || $folder_tree->isGroupFolder($result['folder_id'])
             )
            ) { //Aenderungsmodus, Anker + Formular machen, Font tag direkt ausgeben (muss ausserhalb einer td stehen!
        $titel= "</a><input style=\"font-size:8 pt; width: 400px;\" type=\"text\" size=\"20\" maxlength=\"255\" aria-label=\"Ordnername eingeben\" name=\"change_name\" value=\"".htmlReady($result['name'])."\" >";
        if ($rechte && $folder_tree->permissions_activated)
            $titel .= '&nbsp;['.$folder_tree->getPermissionString($result["folder_id"]).']';
    }   else {
        //create a link onto the titel, too
        if ($rechte && $folder_tree->permissions_activated ) {
            $tmp_titel .= '&nbsp;';
            $tmp_titel .= '['.$folder_tree->getPermissionString($result["folder_id"]).']';
        }
        if ($document_count > 1)
            $titel= $tmp_titel."</span>&nbsp;&nbsp;" . sprintf(_("(%s Dokumente)"), $document_count);
        elseif ($document_count)
            $titel= $tmp_titel."</span>&nbsp;&nbsp;" . _("(1 Dokument)");
        else
            $titel= $tmp_titel;
    }
    print $titel;

    if ($isissuefolder) {
        $dates_title = array();
        foreach ($dates_for_issue as $date) {
            $dates_title[] .= date('d.m.y, H:i', $date['date']).' - '.date('H:i', $date['end_time']);
        }
        if (sizeof($dates_title) > 0) {
            $title_name = sprintf(_("Sitzung am: %s"), implode(', ', $dates_title));
            if (!$result['name']) {
                $title_name .= ', '._("Ohne Titel");
            } else {
                $title_name .= ', '.htmlReady($result['name']);
            }
        }
    }

    print "</a>&nbsp;";

    // Schloss, wenn Folder gelockt
    if ($folder_tree->isLockedFolder($folder_id)) {
        print Icon::create('lock-locked', 'inactive', ['title' => _('Dieser Ordner ist gesperrt.')])->asImg(['class' => 'text-bottom']);
    }
    //Wenn verdeckt durch gesperrten �bergeordneten Ordner
    else if ( ($super_folder = $folder_tree->getNextSuperFolder($folder_id)) ) {
        print Icon::create('lock-locked', 'inactive', ['title' => _('Dieser Ordner ist nicht zug�nglich, da ein �bergeordneter Ordner gesperrt ist.')])->asImg(['class' => 'text-bottom']);
    }
    // Wenn es ein Hausaufgabenordner ist
    if ($folder_tree->isExerciseFolder($folder_id)) {
        print Icon::create('edit', 'inactive', ['title' => _('Dieser Ordner ist ein Hausaufgabenordner. Es k�nnen nur Dateien eingestellt werden.')])->asImg(['class' => 'text-bottom']);
    }

    print "</td>";

    //So und jetzt die rechtsb�ndigen Sachen:
    print "</td><td align=right class=\"printhead\" valign=\"bottom\">";

    print "<a href=\"".URLHelper::getLink('dispatch.php/profile?username='.$result['username'])."\">".htmlReady($result['fullname'])."</a> ";

    print $bewegeflaeche." ";

    //Workaround for older data from previous versions (chdate is 0)
    print date("d.m.Y - H:i", (($result["chdate"]) ? $result["chdate"] : $result["mkdate"]));

    print "</td></tr></table>"; //Ende des Titels, Beschreibung und Kn�pfen
    if ($rechte)
        print "</div>";  //End des Droppable-Divs

    if ($open[$folder_id]) {
        print "<div id=\"folder_".$folder_id."_body\">";
        //Der ganze Teil des Unterbaus wurde in die folgende Funktion outsourced:
        display_folder_body($folder_id, $open, $change, $move, $upload, $refresh, $filelink, $anchor_id, $depth - 3);
    } else {
        print "<div id=\"folder_".$folder_id."_body\" style=\"display: none\">";
    }
    print "</div></td></tr></table>";
    print "</div>";
}


/*
used by:
lib/datei.inc.php:104:        $link = getLinkPath($link_update);
public/folder.php:265:        if (getLinkPath($open_id)) {
*/
function getLinkPath($file_id)
{
    $query = "SELECT url FROM dokumente WHERE dokument_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($file_id));
    return $statement->fetchColumn() ?: false;
}


/*
used by:
lib/evaluation/evaluation_admin_overview.lib.php:909:                $link->addHTMLContent(GetFileIcon('csv')->asImg());
lib/datei.inc.php:900:lib/datei.inc.php:1514:        print GetFileIcon(getFileExtension($datei['filename']))->asImg();
lib/datei.inc.php:901:lib/datei.inc.php:1523:            print "<a href=\"".GetDownloadLink( $datei["dokument_id"], $datei["filename"], $type, "normal")."\" class=\"extern\">".GetFileIcon(getFileExtension($datei['filename']))->asImg()."</a>";
lib/datei.inc.php:904:app/views/admin/user/list_files.php:18:                        GetFileIcon(getFileExtension($file->filename), true));
lib/datei.inc.php:1497:        print GetFileIcon(getFileExtension($datei['filename']))->asImg();
lib/datei.inc.php:1506:            print "<a href=\"".GetDownloadLink( $datei["dokument_id"], $datei["filename"], $type, "normal")."\" class=\"extern\">".GetFileIcon(getFileExtension($datei['filename']))->asImg()."</a>";
app/views/admin/user/list_files.php:18:                        GetFileIcon(getFileExtension($file->filename), true));
app/controllers/messages.php:348:                                        'icon' => GetFileIcon(
app/controllers/messages.php:442:                        'icon' => GetFileIcon(
app/controllers/messages.php:763:        $output['icon'] = GetFileIcon($file_ref->file->getExtension())->asImg(['class' => "text-bottom"]);
*/
function GetFileIcon($ext){
    //Icon auswaehlen
    switch (mb_strtolower($ext)){
        case 'rtf':
        case 'doc':
        case 'docx':
        case 'odt':
            $icon = 'file-text';
        break;
        case 'xls':
        case 'xlsx':
        case 'ods':
        case 'csv':
        case 'ppt':
        case 'pptx':
        case 'odp':
            $icon = 'file-office';
        break;
        case 'zip':
        case 'tgz':
        case 'gz':
        case 'bz2':
            $icon = 'file-archive';
        break;
        case 'pdf':
            $icon = 'file-pdf';
        break;
        case 'gif':
        case 'jpg':
        case 'jpe':
        case 'jpeg':
        case 'png':
        case 'bmp':
            $icon = 'file-pic';
        break;
        default:
            $icon = 'file-generic';
        break;
    }
    return Icon::create($icon, 'clickable');
}

/**
 * Determines an appropriate MIME type for a file based on the
 * extension of the file name.
 *
 * @param string $filename      file name to check
 */
/*
db/migrations/207_moadb.php:325:            $insert_file->execute(array($one['dokument_id'], $one['user_id'], get_mime_type($one['filename']), $filename, $one['filesize'], $one['url'] ? 'url' : 'disk', $one['author_name'], $one['mkdate'], $one['chdate']));
lib/classes/restapi/RouteMap.php:858:            $this->contentType(get_mime_type($path));
lib/filesystem/FileManager.php:65:                $filetype = $uploaded_files['type'][$key] ?: get_mime_type($filename);
lib/filesystem/FileManager.php:1090:            $parsed_link['Content-Type'] = get_mime_type($parsed_link['filename']);
lib/datei.inc.php:1322:        $media_type = get_mime_type($datei['filename']);
app/routes/Files_old.php:441:            "Content-Type"        => get_mime_type($filename),
app/controllers/course/dates.php:354:        $this->set_content_type(get_mime_type($filename));
app/controllers/file.php:210:                        'type' => get_mime_type($file),
app/controllers/file.php:888:                            $file->mime_type = get_mime_type($file->name);
app/controllers/file.php:904:                        $file->mime_type = $meta['Content-Type'] ? strstr($meta['Content-Type'], ';', true) : get_mime_type($file->name);
public/sendfile.php:106:        $content_type = get_mime_type($file_name);
public/sendfile.php:110:        $content_type = get_mime_type($file_name);
public/sendfile.php:114:        $content_type = $file_ref->mime_type ?: get_mime_type($file_name);
public/sendfile.php:141:    $content_type = $link_data['Content-Type'] ? strstr($link_data['Content-Type'], ';', true) : get_mime_type($file_name);
public/plugins_packages/core/Blubber/controllers/streams.php:579:                                'type' => [null], //let the get_mime_type guess the file type
*/
function get_mime_type($filename)
{
    static $mime_types = array(
        // archive types
        'gz'   => 'application/x-gzip',
        'tgz'  => 'application/x-gzip',
        'bz2'  => 'application/x-bzip2',
        'zip'  => 'application/zip',
        // document types
        'txt'  => 'text/plain',
        'css'  => 'text/css',
        'csv'  => 'text/csv',
        'rtf'  => 'application/rtf',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'xls'  => 'application/ms-excel',
        'ppt'  => 'application/ms-powerpoint',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'swf'  => 'application/x-shockwave-flash',
        'odp'  => 'application/vnd.oasis.opendocument.presentation',
        'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt'  => 'application/vnd.oasis.opendocument.text',
        // image types
        'gif'  => 'image/gif',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'png'  => 'image/png',
        'bmp'  => 'image/x-ms-bmp',
        // audio types
        'mp3'  => 'audio/mp3',
        'oga'  => 'audio/ogg',
        'wav'  => 'audio/wave',
        'ra'   => 'application/x-pn-realaudio',
        'ram'  => 'application/x-pn-realaudio',
        // video types
        'mpeg' => 'video/mpeg',
        'mpg'  => 'video/mpeg',
        'mpe'  => 'video/mpeg',
        'qt'   => 'video/quicktime',
        'mov'  => 'video/quicktime',
        'avi'  => 'video/x-msvideo',
        'flv'  => 'video/x-flv',
        'ogg'  => 'application/ogg',
        'ogv'  => 'video/ogg',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
    );

    $extension = mb_strtolower(getFileExtension($filename));

    if (isset($mime_types[$extension])) {
        return $mime_types[$extension];
    } else {
        return 'application/octet-stream';
    }
}

/**
* Erzeugt einen Downloadlink abhaengig von der Konfiguration des Systems
* (Config::get()->SENDFILE_LINK_MODE = 'normal'|'old'|'rewrite')
*
* @param    string  $file_id
* @param    string  $file_name
* @param    integer $type   sendfile type 1,2,3,4,5 or 6
* @param    string  $dltype 'normal', 'zip' or 'force' (or 'force_download')
* @return   string  downloadlink
*/
/*
used by:
lib/datei.inc.php:2758:    $movie_url = GetDownloadLink($document_id, $filename, $type, 'force');
lib/extern/modules/ExternModuleDownload.class.php:220:                $download_link = GetDownloadLink($row['dokument_id'], $row['filename']);
lib/extern/modules/ExternModuleTemplateDownload.class.php:250:                $download_link = GetDownloadLink($row['dokument_id'], $row['filename']);
app/views/admin/user/list_files.php:16:                $actionMenu->addLink(GetDownloadLink($file->id, $file->filename, $type),
app/views/admin/user/list_files.php:20:                    $actionMenu->addLink(GetDownloadLink($file->id, $file->filename, $type, 'zip'),
app/views/search/archive/index.php:82:                <a href="<?= URLHelper::getLink(GetDownloadLink($course->archiv_file_id, $filename, 1)) ?>">
app/views/search/archive/index.php:88:                <a href="<?= URLHelper::getLink(GetDownloadLink($course->archiv_protected_file_id, $filename, 1)) ?>">
app/views/my_courses/archive.php:50:                            <a href="<?= URLHelper::getLink(GetDownloadLink($row['archiv_file_id'], $filename, 1)) ?>">
app/views/my_courses/archive.php:56:                            <a href="<?= URLHelper::getLink(GetDownloadLink($row['archiv_protected_file_id'], $filename, 1)) ?>">
app/models/WysiwygDocument.php:62:        return \GetDownloadLink($this->studipDocument->getId(),
app/controllers/admin/user.php:157:                    $this->redirect(GetDownloadLink($tmpname, 'nutzer-export.csv', 4));
app/controllers/admin/courses.php:621:            $this->redirect(GetDownloadLink($tmpname, 'Veranstaltungen_Export.csv', 4, 'force'));
app/controllers/admission/restricted_courses.php:103:                $this->redirect(GetDownloadLink($tmpname, 'teilnahmebeschraenkteVeranstaltungen.csv', 4, 'force'));
app/controllers/admission/courseset.php:431:                $this->redirect(GetDownloadLink($tmpname, 'Veranstaltungen_' . $courseset->getName() . '.csv', 4, 'force'));
app/controllers/admission/courseset.php:454:                        $this->redirect(GetDownloadLink($tmpname, 'Gesamtteilnehmerliste_' . $courseset->getName() . '.csv', 4, 'force'));
app/controllers/admission/courseset.php:479:                        $this->redirect(GetDownloadLink($tmpname, 'Mehrfachanmeldungen_' . $courseset->getName() . '.csv', 4, 'force'));
app/controllers/admission/courseset.php:573:                $this->redirect(GetDownloadLink($tmpname, 'Anmeldungen_' . $courseset->getName() . '.csv', 4, 'force'));
app/controllers/course/members.php:1274:            $this->redirect(GetDownloadLink($tmp_name, _('Zusatzangaben') . '.csv', 4, 'force'));
public/eval_summary.php:321:        $txt .= '<IMG SRC="' . GetDownloadLink('evalsum'.$parent_id.$auth->auth['uid'].'.'.Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT, 'evalsum'.$parent_id.$auth->auth['uid'].'.'.Config::get()->EVAL_AUSWERTUNG_GRAPH_FORMAT, 2) .'">'."\n";
templates/mail/text.php:13:        <?= GetDownloadLink($one['dokument_id'], $one['filename'], 7, 'force') ?>
templates/mail/html.php:21:            <a href="<?=GetDownloadLink($one['dokument_id'], $one['filename'], 7, 'force')?>"><?= htmlReady($one['filename'] . ' (' . relsize($one['filesize'], false) . ')') ?></a> 
*/
function GetDownloadLink($file_id, $file_name, $type = 0, $dltype = 'normal', $range_id = '', $list_id = ''){
    $mode = Config::get()->SENDFILE_LINK_MODE ?: 'normal';
    $link[] = $GLOBALS['ABSOLUTE_URI_STUDIP'];
    $wa = '';
    switch($mode) {
    case 'rewrite':
        $link[] = 'download/';
        switch ($dltype) {
        case 'zip':
            $link[] = 'zip/';
            break;
        case 'force':
        case 'force_download':
            $link[] = 'force_download/';
            break;
        case 'normal':
        default:
            $link[] = 'normal/';
        }
        $link[] = $type . '/';
        if ($type == 5) {
            $link[] = rawurlencode($range_id) . '/' . rawurlencode($list_id);
        } else {
            $link[] =  rawurlencode(prepareFilename($file_id));
        }
        $link[] = '/' . rawurlencode(prepareFilename($file_name));
        break;
    case 'old':  // workaround for old browser (IE)
        $wa = '/';
    case 'normal':
    default:
        $link[] = 'sendfile.php?' . $wa;
        if ($dltype == 'zip'){
            $link[] = 'zip=1&';
        } elseif ($dltype == 'force_download' || $dltype == 'force') {
            $link[] = 'force_download=1&';
        }
        $link[] = 'type='.$type;
        if ($type == 5) {
            $link[] = '&range_id=' . rawurlencode($range_id) . '&list_id=' . rawurlencode($list_id);
        } else {
            $link[] = '&file_id=' . rawurlencode(prepareFilename($file_id));
        }
        $link[] = '&file_name=' . rawurlencode(prepareFilename($file_name ));
    }
    return implode('', $link);
}


/*
Die function delete_document l�scht ein hochgeladenes Dokument.
Der erste Parameter ist die dokument_id des zu l�schenden Dokuments.
Der R�ckgabewert der Funktion ist bei Erfolg TRUE.
FALSE bedeutet einen Fehler beim Loeschen des Dokumentes.
Ausgabe wird keine produziert.
Es erfolgt keine �berpr�fung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/
/*
used by:
lib/classes/UserManagement.class.php:774:    function deleteUser($delete_documents = true)
lib/classes/UserManagement.class.php:879:        if ($delete_documents) {
lib/messaging.inc.php:83:            array_map('delete_document', $document_ids);
lib/datei.inc.php:2391:            if (delete_document($dokument_id)) {
lib/datei.inc.php:2443:            if (delete_document($file_id)) {
public/folder.php:274:        if (delete_document($open_id))
public/folder.php:652:        $deleted += delete_document($one);
*/
function delete_document($dokument_id, $delete_only_file = FALSE)
{
    $query = "SELECT url FROM dokumente WHERE dokument_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($dokument_id));
    $url = $statement->fetchColumn();

    if ($url !== false) {
        if (!$url) {   //Bei verlinkten Datein nicht nachsehen ob es Datei gibt!
            @unlink(get_upload_file_path($dokument_id));
            if ($delete_only_file){
                return TRUE;
            }
        }
    }

    // eintrag aus der Datenbank werfen
    $doc = new StudipDocument($dokument_id);
    return $doc->delete();
}


/*
used by:
lib/datei.inc.php:1154:                        delete_link($refresh, TRUE);
public/folder.php:282:        if (delete_link($open_id))
*/
function delete_link($dokument_id) {
    // eintrag aus der Datenbank werfen
    $doc = new StudipDocument($dokument_id);
    return $doc->delete();
}

/*
Die function delete_folder l�scht einen kompletten Dateiordner.
Der Parameter ist die folder_id des zu l�schenden Ordners.
Der R�ckgabewert der Funktion ist bei Erfolg TRUE.
FALSE bedeutet einen Fehler beim Loeschen des Dokumentes.
Ausgabe wird keine produziert.
Es erfolgt keine �berpr�fung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/
/*
used by:
lib/models/Statusgruppen.php:249:            delete_folder($this->hasFolder(), true);
lib/datei.inc.php:2388:                delete_folder($one_folder, true);
public/folder.php:240:            delete_folder($open_id, true);
public/folder.php:249:        delete_folder($open_id, true);
*/
function delete_folder($folder_id, $delete_subfolders = false)
{
    if ($delete_subfolders){
        list($subfolders, $num_subfolders) = getFolderChildren($folder_id);
        if ($num_subfolders){
            foreach ($subfolders as $one_folder){
                delete_folder($one_folder, true);
            }
        }
    }

    $query = "SELECT folder_id, name FROM folder WHERE folder_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($folder_id));
    $temp = $statement->fetch(PDO::FETCH_ASSOC);

    if ($temp) {
        $foldername = $temp['name'];

        $query = "SELECT dokument_id FROM dokumente WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($folder_id));
        while ($dokument_id = $statement->fetchColumn()) {
            if (delete_document($dokument_id)) {
                $deleted++;
            }
        }

        $query = "DELETE FROM folder WHERE folder_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($folder_id));
        if ($statement->rowCount()) {
            if ($deleted) {
                PageLayout::postSuccess(sprintf(_("Der Dateiordner <b>%s</b> und %s Dokument(e) wurden gel�scht"), htmlReady($foldername), $deleted));
            } else {
                PageLayout::postSuccess(sprintf(_("Der Dateiordner <b>%s</b> wurde gel�scht"),htmlReady($foldername)));
                return TRUE;
            }
        } else {
            if ($deleted){
                PageLayout::postWarning(sprintf(_("Probleme beim L�schen des Ordners <b>%s</b>. %u Dokument(e) wurden gel�scht"),htmlReady($foldername), $deleted));
            }else{
                PageLayout::postError(sprintf(_("Probleme beim L�schen des Ordners <b>%s</b>"),htmlReady($foldername)));
            }
            return FALSE;
        }
    }
}

//Rekursive Loeschfunktion, loescht erst jeweils enthaltene Dokumente und dann den entsprechenden Ordner
/*
used by:
lib/dates.inc.php:362:        recursiv_folder_delete ($termin_id);
lib/dates.inc.php:366:            recursiv_folder_delete($termin_id);
lib/datei.inc.php:2457:        $doc_count += recursiv_folder_delete($folder_id);
lib/datei.inc.php:2481:            $count += recursiv_folder_delete($folder_id);
*/
function recursiv_folder_delete($parent_id)
{
    // Prepare files statement
    $query = "SELECT dokument_id FROM dokumente WHERE range_id = ?";
    $files_statement = DBManager::get()->prepare($query);

    // Prepare delete statement
    $query = "DELETE FROM folder WHERE folder_id = ?";
    $delete_statement = DBManager::get()->prepare($query);

    $doc_count = 0;

    $query = "SELECT folder_id FROM folder WHERE range_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($parent_id));
    $folder_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

    foreach ($folder_ids as $folder_id) {
        $doc_count += recursiv_folder_delete($folder_id);

        $files_statement->execute(array($folder_id));
        $file_ids = $files_statement->fetchAll(PDO::FETCH_COLUMN);
        $files_statement->closeCursor();

        foreach ($file_ids as $file_id) {
            if (delete_document($file_id)) {
                $doc_count++;
            }
        }

        $delete_statement->execute(array($folder_id));
    }
    return $doc_count;
}


/*
used by:
lib/classes/Seminar.class.php:1923:        if (($db_ar = delete_all_documents($s_id)) > 0) {
app/controllers/institute/basicdata.php:443:            $db_ar = delete_all_documents($i_id);
*/
function delete_all_documents($range_id){
    if (!$range_id){
        return false;
    }
    $folder_tree = TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $range_id));
    if($folder_tree->getNumKids('root')){
        foreach($folder_tree->getKids('root') as $folder_id){
            $count += recursiv_folder_delete($folder_id);
        }
    }
    return $count;
}
/**
* Delete a file, or a folder and its contents
*
* @author      Aidan Lister <aidan@php.net>
* @version     1.0
* @param       string   $dirname    The directory to delete
* @return      bool     Returns true on success, false on failure
*/
/*
used by:
db/migrations/101_step00246_blubber.php:95:                @rmdirr($GLOBALS['PLUGINS_PATH']."/".$old_blubber['pluginpath']);
db/migrations/199_step_00302_modulverwaltung.php:21:                @rmdirr($GLOBALS['PLUGINS_PATH'] . '/' . $old_mvv['pluginpath']);
lib/archiv.inc.php:739:        rmdirr($archive_tmp_dir_path);
lib/archiv.inc.php:783:            rmdirr($archive_protected_files_zip_path);
lib/datei.inc.php:301:            rmdirr($tmp_full_path);
lib/datei.inc.php:2525:            rmdirr("$dirname/$entry");
lib/datei.inc.php:2625:            @rmdirr($tmpdirname);
lib/datei.inc.php:2646:            @rmdirr($tmpdirname);
lib/datei.inc.php:2651:    @rmdirr($tmpdirname);
app/models/plugin_administration.php:42:            rmdirr($packagedir);
app/models/plugin_administration.php:68:            rmdirr($packagedir);
app/models/plugin_administration.php:81:            rmdirr($packagedir);
app/models/plugin_administration.php:98:            rmdirr($plugindir);
app/models/plugin_administration.php:103:                rmdirr($plugindir_old);
app/models/plugin_administration.php:122:            rmdirr($plugindir);
app/models/plugin_administration.php:134:        rmdirr($packagedir);
app/models/plugin_administration.php:199:        rmdirr($plugindir);
app/controllers/file.php:164:                rmdirr($tmp_folder);
*/
function rmdirr($dirname){
    // Simple delete for a file
    if (is_file($dirname)) {
        return @unlink($dirname);
    } else if (!is_dir($dirname)) {
        return false;
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== ($entry = $dir->read())) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep delete directories
        if (is_dir("$dirname/$entry") && !is_link("$dirname/$entry")) {
            rmdirr("$dirname/$entry");
        } else {
            @unlink("$dirname/$entry");
        }
    }
    // Clean up
    $dir->close();
    return @rmdir($dirname);
}

/*
used by:
public/sendfile.php:124:    if (create_zip_from_file( $tmp_file_name, "$zip_path_file.zip") === false) {
*/
function create_zip_from_file($file_name, $zip_file_name){
    if ($GLOBALS['ZIP_USE_INTERNAL']){
        $archive = Studip\ZipArchive::create($zip_file_name);
        $localfilename = $archive->addFile($file_name);
        $archive->close();
        return [$localfilename];
    } else if (@file_exists($GLOBALS['ZIP_PATH']) || ini_get('safe_mode')){
        if (mb_strtolower(mb_substr($zip_file_name, -3)) != 'zip' ) {
            $zip_file_name = $zip_file_name . '.zip';
        }

        exec($GLOBALS['ZIP_PATH'] . ' -q ' . $GLOBALS['ZIP_OPTIONS'] . " -j {$zip_file_name} $file_name", $output, $ret);
        return $ret;
    }

    // return false, if nothing worked
    return false;
}

/*
used by:
lib/archiv.inc.php:735:        create_zip_from_directory($archive_tmp_dir_path, $archive_tmp_dir_path);
lib/archiv.inc.php:771:            create_zip_from_directory(
lib/datei.inc.php:300:            create_zip_from_directory($tmp_full_path, $tmp_full_path);
app/controllers/admin/plugin.php:306:        create_zip_from_directory($pluginpath, $filepath);
app/controllers/admission/ruleadministration.php:212:        create_zip_from_directory($dirname, $filepath);
*/
function create_zip_from_directory($fullpath, $zip_file_name) {
    if ($GLOBALS['ZIP_USE_INTERNAL']) {
        $archive = Studip\ZipArchive::create($zip_file_name);
        $added = $archive->addFromPath($fullpath);
        $archive->close();
        return $added;
    } else if (@file_exists($GLOBALS['ZIP_PATH']) || ini_get('safe_mode')){
        if (mb_strtolower(mb_substr($zip_file_name, -3)) != 'zip' ) {
            $zip_file_name = $zip_file_name . '.zip';
        }

        //zip stuff
        $zippara = (ini_get('safe_mode')) ? ' -R ':' -r ';
        if (@chdir($fullpath)) {
            exec ($GLOBALS['ZIP_PATH'] . ' -q -D ' . $GLOBALS['ZIP_OPTIONS'] . ' ' . $zippara . $zip_file_name . ' *',$output, $ret);
            @chdir($GLOBALS['ABSOLUTE_PATH_STUDIP']);
        }
        return $ret;
    }
}

/*
used by:
lib/datei.inc.php:2630:    return !extract_zip($file_name, $dir_name, $testonly);
lib/datei.inc.php:2649:        if(!extract_zip($GLOBALS['TMP_PATH'] . '/' . $tmpname, false, true)) {
lib/datei.inc.php:2656:        if (!extract_zip($GLOBALS['TMP_PATH'] . '/' . $tmpname , $tmpdirname)){
app/models/plugin_administration.php:41:        if (!extract_zip($filename, $packagedir)) {
app/controllers/file.php:156:                extract_zip($this->file_ref->file->getPath(), $tmp_folder);
*/
function extract_zip($file_name, $dir_name = '', $testonly = false) {
    $ret = false;
    if ($GLOBALS['ZIP_USE_INTERNAL']) {
        if ($testonly) {
            return Studip\ZipArchive::test($file_name);
        }

        return Studip\ZipArchive::extractToPath($file_name, $dir_name);
    } else if (@file_exists($GLOBALS['UNZIP_PATH']) || ini_get('safe_mode')){
        if ($testonly){
            exec($GLOBALS['UNZIP_PATH'] . " -t -qq $file_name ", $output, $ret);
        } else {
            exec($GLOBALS['UNZIP_PATH'] . " -qq $file_name " . ($dir_name ? "-d $dir_name" : ""), $output, $ret);
        }
        $ret = $ret === 0;
    }
    return $ret;
}



/**
 * Laedt eine bestehende Verzeichnisstruktur in das System.
 * Die ganze Struktur wird samt Dateien und Unterverzeichnissen rekursiv
 * eingefuegt: 1. Den aktuellen Ordner erstellen. -- 2. Die Dateien in
 * alphabetischer Reihenfolge einfuegen. -- 3. Die Verzeichnisstruktur jedes
 * Unterordners einfuegen (Rekursion).
 * Nach Einfuegen einer Datei / eines Verzeichnisses wird die Datei oder das
 * Verzeichnis geloescht.
 *
 * @param range_id Die ID des Ordners unter dem die Verzeichnisstruktur
 * @param dir
 * @return (no return value)
 */
 /*
used in:
lib/datei.inc.php:2713:        $ret = upload_recursively($GLOBALS['folder_system_data']['upload'], $tmpdirname);
lib/datei.inc.php:2836:        upload_recursively($dir_id, $subdir);
lib/datei.inc.php:2733:        upload_recursively($dir_id, $subdir);
*/
function upload_recursively($range_id, $dir) {
    static $count = array(
        'files'       => 0,
        'files_max'   => false,
        'subdirs'     => 0,
        'subdirs_max' => false,
    );

    $max_files = get_config('ZIP_UPLOAD_MAX_FILES');
    $max_dirs = get_config('ZIP_UPLOAD_MAX_DIRS');

    $files = array ();
    $subdirs = array ();

    if ($count['files'] >= $max_files) {
        $count['files_max'] = true;
        return;
    }
    if ($count['subdirs'] >= $max_dirs) {
        $count['subdirs_max'] = true;
        return;
    }

    // Versuchen, das Verzeichnis zu oeffnen
    if ($handle = @opendir($dir)) {

        // Alle Eintraege des Verzeichnisses durchlaufen
        while (false !== ($file = readdir($handle))) {

            // Verzeichnisverweise . und .. ignorieren
            if ($file != "." && $file != "..") {
                // Namen vervollstaendigen
                $file = $dir."/".$file;

                if (is_link($file)) {
                    continue;
                }

                if (is_file($file)) {
                    // Datei in Dateiliste einfuegen
                    $files[] = $file;
                }
                elseif (is_dir($file)) {
                    // Verzeichnis in Verzeichnisliste einfuegen
                    $subdirs[] = $file;
                }
            }
        }
        closedir($handle);
    }

    // Listen der Dateien und Unterverzeichnisse sortieren.
    sort($files);
    sort($subdirs);

    // Alle Dateien hinzufuegen.
    while (list ($nr, $file) = each($files)) {
        if ($count['files'] >= $max_files) {
            $count['files_max'] = true;
            break;
        }
        if (validate_upload(array('name' => $file, 'size' => filesize($file)))) {
            $count['files'] += upload_zip_file($range_id, $file);
        }
    }

    // Alle Unterverzeichnisse hinzufuegen.
    while (list ($nr, $subdir) = each($subdirs)) {
        if ($count['subdirs'] >= $max_dirs) {
            $count['subdirs_max'] = true;
            break;
        }
        // Verzeichnis erstellen
        $pos = mb_strrpos($subdir, "/");
        $name = mb_substr($subdir, $pos + 1, mb_strlen($subdir) - $pos);
        $dir_id = create_folder($name, "", $range_id);
        $count['subdirs']++;
        // Verzeichnis hochladen.
        upload_recursively($dir_id, $subdir);
    }
    return $count;
}


/**
 * Eine einzelne Datei in das Verzeichnis mit der dir_id einfuegen.
 */
 /*
used in:
lib/datei.inc.php:2718:            $count['files'] += upload_zip_file($range_id, $file);
lib/datei.inc.php:2745: lib/datei.inc.php:2816:            $count['files'] += upload_zip_file($range_id, $file);
*/
function upload_zip_file($dir_id, $file) {

    global $user;

    $file_size = filesize($file);
    if (!$file_size) {
        return false;
    }

    $file_name = basename($file);

    $data = array(
        'filename'    => $file_name,
        'name'        => $file_name,
        'filesize'    => $file_size,
        'autor_host'  => $_SERVER['REMOTE_ADDR'],
        'user_id'     => $user->id,
        'range_id'    => $dir_id,
        'seminar_id'  => Request::option('upload_seminar_id'),
        'description' => '',
        'author_name' => get_fullname()
    );
    $ret = StudipDocument::createWithFile($file, $data);
    return (int)$ret;
}

/*
used in:
lib/datei.inc.php:1327:                $flash_player = get_flash_player($datei['dokument_id'], $datei['filename'], $type);
*/
function get_flash_player ($document_id, $filename, $type) {
    global $auth;
    // width of image in pixels
    if (is_object($auth) && $auth->auth['xres']) {
        // 50% of x-resolution maximal
        $max_width = floor($auth->auth['xres'] / 4);
    } else {
        $max_width = 400;
    }
    $width = $max_width;
    $height = round($width * 0.75);
    if ($width > 200) {
        $flash_config = $GLOBALS['FLASHPLAYER_DEFAULT_CONFIG_MAX'];
    } else {
        $flash_config = $GLOBALS['FLASHPLAYER_DEFAULT_CONFIG_MIN'];
    }
    $cfg = Config::GetInstance();
    $DOCUMENTS_EMBEDD_FLASH_MOVIES = $cfg->getValue('DOCUMENTS_EMBEDD_FLASH_MOVIES');
    if ($DOCUMENTS_EMBEDD_FLASH_MOVIES == 'autoplay') {
        $flash_config .= '&amp;autoplay=1&amp;autoload=1';
    } else if ($DOCUMENTS_EMBEDD_FLASH_MOVIES == 'autoload') {
        $flash_config .= '&amp;autoload=1';
    }
    // we need the absolute url if the player is delivered from a different base
    $movie_url = GetDownloadLink($document_id, $filename, $type, 'force');
    $flash_object  = "\n<object type=\"application/x-shockwave-flash\" id=\"FlashPlayer\" data=\"".Assets::url()."flash/player_flv.swf\" width=\"$width\" height=\"$height\">\n";
    $flash_object .= "<param name=\"movie\" value=\"".Assets::url()."flash/player_flv.swf\">\n";
    $flash_object .= '<param name="allowFullScreen" value="true">' . "\n";
    $flash_object .= "<param name=\"FlashVars\" value=\"flv=" . urlencode($movie_url) . $flash_config . "\">\n";
    $flash_object .= "<embed src=\"".Assets::url()."flash/player_flv.swf\" movie=\"{$movie_url}\" type=\"application/x-shockwave-flash\" FlashVars=\"flv=".urlencode($movie_url).$flash_config."\">\n";
    $flash_object .= "</object>\n";

    return array('player' => $flash_object, 'width' => $width, 'height' => $height);
}

/**
 * Return the absolute path of an uploaded file. The uploaded files
 * are organized in sub-folders of UPLOAD_PATH to avoid performance
 * problems with large directories.
 * If the document_id is empty, NULL is returned.
 *
 * @param string MD5 id of the uploaded file
 */
/*
used in:
lib/classes/StudipMail.class.php:287:            $this->addFileAttachment(get_upload_file_path($doc->getId()), $doc->getValue('filename'));
lib/classes/exportdocument/ExportPDF.class.php:178:            $path = get_upload_file_path($doc->getId());
lib/classes/exportdocument/ExportPDF.class.php:307:                        $convurl = get_upload_file_path($matches[1]);
lib/models/StudipDocument.class.php:241:        $newpath = get_upload_file_path($this->getId());
lib/models/StudipDocument.class.php:267:            @unlink(get_upload_file_path($this->getId()));
lib/datei.inc.php:267:                    if (@copy(get_upload_file_path($file['dokument_id']), $tmp_full_path.'/'.$filename)) {
lib/datei.inc.php:362:            if (copy(get_upload_file_path($row['dokument_id']), $tmp_full_path . '/' . $filename)) {
lib/datei.inc.php:699:        return (bool) StudipDocument::createWithFile(get_upload_file_path($doc_id), $new);
lib/datei.inc.php:2461:            @unlink(get_upload_file_path($dokument_id));
app/routes/Files_old.php:70:            if (!file_exists($real_file = get_upload_file_path($file_id))) {
app/controllers/messages.php:366:                        $new_attachment = StudipDocument::createWithFile(get_upload_file_path($attachment->getId()), $new_attachment);
app/controllers/messages.php:756:        $data_stored = move_uploaded_file($file['tmp_name'], get_upload_file_path($file_ref->file_id));
*/
function get_upload_file_path ($document_id)
{
    global $UPLOAD_PATH;

    if ($document_id == '') {
        return NULL;
    }

    $directory = $UPLOAD_PATH.'/'.mb_substr($document_id, 0, 2);

    if (!file_exists($directory)) {
        mkdir($directory);
    }

    return $directory.'/'.$document_id;
}

/**
 *
 * checks if the 'protected' flag of a file is set and if
 * the course access is closed
 *
 * @param string MD5 id of the file
 * @return bool
 */
 /*
used in:
lib/datei.inc.php:265:                if(check_protected_download($file['dokument_id'])) {
lib/datei.inc.php:360:        } else if(check_protected_download($row['dokument_id'])) {
lib/datei.inc.php:1573:        if (check_protected_download($datei['dokument_id'])) {
lib/datei.inc.php:1614:        if(check_protected_download($datei["dokument_id"])){
lib/datei.inc.php:1704:        if (check_protected_download($datei["dokument_id"])) {
lib/datei.inc.php:1742:      if ((!$upload) && ($datei["url"]=="") && check_protected_download($datei["dokument_id"])) {
public/folder.php:106:            && check_protected_download($dl_id['dokument_id']) && $dl_id['url'] == "") {
*/
function check_protected_download($document_id) {
    $ok = true;
    if (Config::GetInstance()->getValue('ENABLE_PROTECTED_DOWNLOAD_RESTRICTION')) {
        $doc = new StudipDocument($document_id);
        if ($doc->getValue('protected')) {
            $ok = false;
            $range_id = $doc->getValue('seminar_id');

            if (get_object_type($range_id) == 'sem') {
                $seminar = Seminar::GetInstance($range_id);
                $timed_admission = $seminar->getAdmissionTimeFrame();

                if ($seminar->isPasswordProtected() ||
                        $seminar->isAdmissionLocked()
                        || ($timed_admission['end_time'] > 0 && $timed_admission['end_time'] < time())) {
                    $ok = true;
                } else if (StudygroupModel::isStudygroup($range_id)) {
                    $studygroup = Seminar::GetInstance($range_id);
                    if ($studygroup->admission_prelim == 1) {
                        $ok = true;
                    }
                }
            }
        }
    }

    return $ok;
}

/**
 * converts a given array to a csv format
 *
 * @param array $data the data to convert, each row should be an array
 * @param string $filename full path to a file to write to, if omitted the csv content is returned
 * @param array $caption assoc array with captions, is written to the first line, $data is filtered by keys
 * @param string $delimiter sets the field delimiter (one character only)
 * @param string $enclosure sets the field enclosure (one character only)
 * @param string $eol sets the end of line format
 * @return mixed if $filename is given the number of written bytes, else the csv content as string
 */
/*
used in:
lib/cronjobs/check_admission.class.php:96:                          if (array_to_csv($data, $applicants_file, $captions)) {
lib/datei.inc.php:298:            array_to_csv($filelist, $tmp_full_path . '/' . _("dateiliste.csv"), $caption);
app/controllers/admin/user.php:156:                if (array_to_csv(array_map($mapper, $this->users), $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
app/controllers/admin/courses.php:620:        if (array_to_csv($data, $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
app/controllers/admission/restricted_courses.php:102:            if (array_to_csv($data, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
app/controllers/admission/courseset.php:430:            if (array_to_csv($data, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
app/controllers/admission/courseset.php:453:                    if (array_to_csv($liste, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
app/controllers/admission/courseset.php:478:                    if (array_to_csv($liste, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
app/controllers/admission/courseset.php:572:            if (array_to_csv($data, $GLOBALS['TMP_PATH'].'/'.$tmpname, $captions)) {
app/controllers/questionnaire.php:424:        $this->render_text(array_to_csv($csv));
app/controllers/course/members.php:1273:            array_to_csv($aux['rows'], $GLOBALS['TMP_PATH'] . '/' . $tmp_name, $aux['head']);
app/controllers/course/members.php:1594:        $csv = array_to_csv($data);
*/
function array_to_csv($data, $filename = null, $caption = null, $delimiter = ';' , $enclosure = '"', $eol = "\r\n" )
{
    $fp = fopen('php://temp', 'r+');
    $fp2 = fopen('php://temp', 'r+');
    if (is_array($caption)) {
        fputcsv($fp, array_values($caption), $delimiter, $enclosure);
        rewind($fp);
        $csv = stream_get_contents($fp);
        if ($eol != PHP_EOL) {
            $csv = trim($csv);
            $csv .= $eol;
        }
        fwrite($fp2, $csv);
        ftruncate($fp, 0);
        rewind($fp);
    }
    foreach ($data as $row) {
        if (is_array($caption)) {
            $fields = array();
            foreach(array_keys($caption) as $fieldname) {
                $fields[] = $row[$fieldname];
            }
        } else {
            $fields = $row;
        }
        fputcsv($fp, $fields, $delimiter, $enclosure);
        rewind($fp);
        $csv = stream_get_contents($fp);
        if ($eol != PHP_EOL) {
            $csv = trim($csv);
            $csv .= $eol;
        }
        fwrite($fp2, $csv);
        ftruncate($fp, 0);
        rewind($fp);
    }
    fclose($fp);
    rewind($fp2);
    if ($filename === null) {
        return stream_get_contents($fp2);
    } else {
        return file_put_contents($filename, $fp2);
    }
}
