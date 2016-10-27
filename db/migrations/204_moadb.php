<?php
/**
 * @author  André Noack <noack@data-quest.de>
 * @license GPL2 or any later version
 *
*/
/*
truncate table folders;
truncate table files;
truncate table file_refs;
truncate table file_urls;
*/
class Moadb extends Migration
{
    public function description()
    {
        return 'migrates documents to moadb';
    }

    public function up()
    {
        $db = DBManager::get();
        //$db->exec("RENAME TABLE files TO _files");
        //$db->exec("RENAME TABLE file_refs TO _file_refs");
        $db->exec("truncate table folders");
        $db->exec("truncate table files");
        $db->exec("truncate table file_refs");
        $db->exec("truncate table file_urls");
        $db->exec("CREATE TABLE IF NOT EXISTS `files` (
                 `id` varchar(32) NOT NULL,
                 `user_id` varchar(32) NOT NULL,
                 `mime_type` varchar(255) NOT NULL DEFAULT '',
                 `name` varchar(255)  NOT NULL,
                 `size` int(10) unsigned NOT NULL,
                 `storage` enum('disk','url')  NOT NULL DEFAULT 'disk',
                 `author_name` varchar(100) NOT NULL DEFAULT '',
                 `mkdate` int(10) unsigned NOT NULL,
                 `chdate` int(10) unsigned NOT NULL,
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_refs` (
                 `id` varchar(32) NOT NULL,
                 `file_id` varchar(32) NOT NULL,
                 `folder_id` varchar(32) NOT NULL,
                 `downloads` int(10) unsigned NOT NULL DEFAULT 0,
                 `description` text NOT NULL,
                 `license` varchar(255) NOT NULL,
                 PRIMARY KEY (`id`),
                 KEY `file_id` (`file_id`),
                 KEY `folder_id` (`folder_id`)
                ) ENGINE=InnoDB");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_urls` (
                 `file_id` varchar(32)  NOT NULL,
                 `url` varchar(4096)  NOT NULL,
                 PRIMARY KEY (`file_id`)
                ) ENGINE=InnoDB");
        $db->exec("CREATE TABLE IF NOT EXISTS `folders` (
                 `id` varchar(32)  NOT NULL,
                 `user_id` varchar(32)  NOT NULL,
                 `parent_id` varchar(32)  NOT NULL,
                 `range_id` varchar(32)  NOT NULL,
                 `range_type` varchar(32)  NOT NULL,
                 `folder_type` varchar(255)  NOT NULL,
                 `name` varchar(255)  NOT NULL,
                 `data_content` text  NOT NULL,
                 `description` text  NOT NULL,
                 `mkdate` int(10) unsigned NOT NULL,
                 `chdate` int(10) unsigned NOT NULL,
                 PRIMARY KEY (`id`),
                 KEY `range_id` (`range_id`),
                 KEY `parent_id` (`parent_id`)
                ) ENGINE=InnoDB");


        //top folder courses
        $institute_folders = array();
        foreach ($db->query("SELECT i.institut_id as new_range_id,i.name FROM `folder` f INNER JOIN `Institute` i ON i.institut_id = f.range_id OR MD5(CONCAT(i.institut_id, 'top_folder')) = f.range_id group by i.institut_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = 'virtual top folder';
            $folder['mkdate'] = $folder['chdate'] = time();
            $this->migrateFolder($folder, $folder['new_range_id'], 'institute', 'StandardFolder');
            $institute_folders[$folder['new_range_id']] = $folder['folder_id'];
        }
        //aka Allgemeiner Dateiordner
        foreach ($db->query("SELECT f.*, i.institut_id as seminar_id FROM `folder` f INNER JOIN `Institute` i ON i.institut_id = f.range_id") as $folder) {
            $folder['range_id'] = $institute_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'institute', 'StandardFolder');
        }
        //other top folders
        foreach ($db->query("SELECT f.*, i.institut_id as seminar_id FROM `folder` f INNER JOIN `Institute` i ON MD5(CONCAT(i.institut_id, 'top_folder')) = f.range_id") as $folder) {
            $folder['range_id'] = $institute_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'institute', 'StandardFolder');
        }
        unset($institute_folders);
        //Blubber folders
        foreach ($db->query("SELECT f.*, a.user_id AS seminar_id, CONCAT_WS(' ', vorname,nachname) as name
                            FROM `folder` f
                            INNER JOIN `auth_user_md5` a ON a.user_id = f.range_id") as $folder) {
            $folder['range_id'] = '';
            $folder['description'] = 'virtual top folder';
            $folder['name'] = 'virtual top folder';
            $this->migrateFolder($folder, $folder['seminar_id'], 'user', 'StandardFolder');
        }
        $seminar_folders = array();
        foreach ($db->query("SELECT s.seminar_id as new_range_id,s.name FROM `folder` f INNER JOIN `seminare` s ON s.Seminar_id = f.range_id OR MD5(CONCAT(s.Seminar_id, 'top_folder')) = f.range_id group by s.Seminar_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = 'virtual top folder';
            $folder['mkdate'] = $folder['chdate'] = time();
            $this->migrateFolder($folder, $folder['new_range_id'], 'course', 'StandardFolder');
            $seminar_folders[$folder['new_range_id']] = $folder['folder_id'];
        }
        //aka Allgemeiner Dateiordner
        foreach ($db->query("SELECT f.*, s.Seminar_id as seminar_id FROM `folder` f INNER JOIN `seminare` s ON s.Seminar_id = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'StandardFolder');
        }
        //other top folders
        foreach ($db->query("SELECT f.*, s.Seminar_id as seminar_id FROM `folder` f INNER JOIN `seminare` s ON MD5(CONCAT(s.Seminar_id, 'top_folder')) = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            if (!$folder['range_id'] ) throw new Exception($folder['seminar_id']);
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'StandardFolder');
        }

        //group folder
        foreach ($db->query("SELECT f.*, s.range_id AS seminar_id FROM `folder` f INNER JOIN `statusgruppen` s ON s.statusgruppe_id = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'coursegroup', 'CourseGroupFolder');
        }
        //issue folder
        foreach ($db->query("SELECT f.*, t.seminar_id AS seminar_id FROM `folder` f INNER JOIN `themen` t ON t.issue_id = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'coursetopic', 'CourseTopicFolder');
        }

    }

    public function migrateFolder($folder, $range_id, $range_type, $folder_type)
    {
        $db = DBManager::get();
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $insert_folder->execute(array($folder['folder_id'], $folder['user_id'], $folder['range_id'], $range_id, $range_type, $folder_type, $folder['name'], isset($folder['permission']) && $folder['permission'] != 7 ? json_encode(['permission' => $folder['permission']]): '', (string)$folder['description'], $folder['mkdate'], $folder['chdate']));
        $subfolders = $db->fetchAll("SELECT * FROM folder WHERE range_id = ?", array($folder['folder_id']));
        foreach ($subfolders as $one) {
            $this->migrateFolder($one, $range_id, $range_type, $folder_type);
        }
        $this->migrateFiles($db->fetchAll("SELECT * FROM dokumente WHERE range_id = ?", array($folder['folder_id'])), $folder['folder_id']);



    }

    public function migrateFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `license`) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_file = $db->prepare("INSERT INTO `files` (`id`, `user_id`, `mime_type`, `name`, `size`, `storage`, `author_name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file_url = $db->prepare("INSERT INTO `file_urls` (`file_id`, `url`) VALUES (?, ?)");
        $filenames = array();
        foreach ($files as $one) {
            $c = 0;
            $filename = $one['filename'];
            $ext = getFileExtension($filename);
            if ($ext) {
                $name = substr($filename, 0, strrpos($filename, $ext) - 1);
            } else {
                $name = $filename;
            }
            while (in_array($filename, $filenames)) {
                $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
            }
            $filenames[] = $filename;
            $insert_file_ref->execute(array($one['dokument_id'], $one['dokument_id'], $folder_id, $one['downloads'], $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string)$one['description'], $one['protected'] ? 'RestrictedLicense' : 'UnknownLicense'));
            $insert_file->execute(array($one['dokument_id'], $one['user_id'], get_mime_type($one['filename']), $filename, $one['filesize'], $one['url'] ? 'url' : 'disk', $one['author_name'], $one['mkdate'], $one['chdate']));
            if ($one['url']) {
                $insert_file_url->execute(array($one['dokument_id'], $one['url']));
            }
        }
    }

    public function down()
    {
    }
}