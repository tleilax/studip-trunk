<?php
/**
 * @author  André Noack <noack@data-quest.de>
 * @license GPL2 or any later version
 *
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
        $firsttime = !$_SESSION['MOADB_MIGRATION_ALREADY_EXECUTED'];
        if ($firsttime) {
            $db->exec("RENAME TABLE files TO _files");
            $db->exec("RENAME TABLE file_refs TO _file_refs");
            $_SESSION['MOADB_MIGRATION_ALREADY_EXECUTED'] = true;
        } else {
            $db->exec("TRUNCATE table folders");
            $db->exec("TRUNCATE table files");
            $db->exec("TRUNCATE table file_refs");
            $db->exec("TRUNCATE table file_urls");
        }


        //delete configuration variables designed for the old file area:
        $db->exec(
            "DELETE FROM `config`
            WHERE
            (field = 'PERSONALDOCUMENT_OPEN_ACCESS')
            OR
            (field = 'PERSONALDOCUMENT_OPEN_ACCESS_ROOT_PRIVILEDGED');"
        );


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
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_refs` (
                 `id` varchar(32) NOT NULL,
                 `file_id` varchar(32) NOT NULL,
                 `folder_id` varchar(32) NOT NULL,
                 `downloads` int(10) unsigned NOT NULL DEFAULT 0,
                 `description` text NOT NULL,
                 `license` varchar(255) NOT NULL,
                 `content_terms_of_use_id` varchar(32) NOT NULL,
                 `user_id` varchar(32) NOT NULL DEFAULT '',
                 `name` varchar(255) NOT NULL DEFAULT '',
                 `mkdate` int(10) UNSIGNED NOT NULL DEFAULT 0,
                 `chdate` int(10) UNSIGNED NOT NULL DEFAULT 0,
                 PRIMARY KEY (`id`),
                 KEY `file_id` (`file_id`),
                 KEY `folder_id` (`folder_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_urls` (
                 `file_id` varchar(32)  NOT NULL,
                 `url` varchar(4096)  NOT NULL,
                 PRIMARY KEY (`file_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
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
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        //table for SORM class ContentTermsOfUse:
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `content_terms_of_use_entries` (
            `id` VARCHAR(32) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `internal_name` VARCHAR(16) UNIQUE NOT NULL,
            `description` TEXT NOT NULL,
            `download_condition` TINYINT(2) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC;"
        );

        //default terms of use entries:
        $db->exec(
            "INSERT INTO content_terms_of_use_entries (`id`, `name`, `internal_name`, `description`, `download_condition`)
            VALUES ('e3ce00626924b34cb945f8b9207e43fe', 'Dokument ist frei von Rechten Dritter', '3RD_PARTY_FALSE', '', '0'),
            ('93739b2a33dc5d8602434067fbc7d4ac', 'Dokument ist nicht frei von Rechten Dritter', '3RD_PARTY_TRUE', '', '1'),
            ('87f3194d604723e6ac529e7d7069f907', 'Selbst verfasstes, nicht publiziertes Werk', 'SELFMADE_NONPUB', '', '0'),
            ('38c706dbb45afcb5ad73b54c07d04662', 'Werk mit freier Lizenz', 'FREE_LICENSE', '', '0'),
            ('ce7801c11c6eeed2e8b5253e46a22b01', 'Nutzungserlaubnis oder Lizenz liegt vor', 'WITH_LICENSE', '', '1'),
            ('f66fe78c95f721bdfc54c3002bb33bef', 'Abbildungen, Fotos, Filme, Musikstücke, Partituren', 'NON_TEXTUAL', '', '1'),
            ('1de7bd86120ddb26abb89e44d5103008', 'Publizierte Texte ohne erworbene Lizenz oder gesonderte Erlaubnis', 'PUB_NO_LICENSE', '', '2'),
            ('2093c5f3733697f297d2f530320b91f8', 'Ungeklärte Lizenz', 'UNDEF_LICENSE', '', '2')");



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

        $insert_folder->execute(array($folder['folder_id'], $folder['user_id'], $folder['range_id'], $range_id, $range_type, isset($folder['permission']) && $folder['permission'] != 7 ? 'PermissionEnabledFolder' : $folder_type, $folder['name'], isset($folder['permission']) && $folder['permission'] != 7 ? json_encode(['permission' => $folder['permission']]): '', (string)$folder['description'], $folder['mkdate'], $folder['chdate']));
        $subfolders = $db->fetchAll("SELECT * FROM folder WHERE range_id = ?", array($folder['folder_id']));
        foreach ($subfolders as $one) {
            $this->migrateFolder($one, $range_id, $range_type, $folder_type);
        }
        $this->migrateFiles($db->fetchAll("SELECT * FROM dokumente WHERE range_id = ?", array($folder['folder_id'])), $folder['folder_id']);



    }

    public function migrateFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `license`, `user_id`, `name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
            $insert_file_ref->execute(array($one['dokument_id'], $one['dokument_id'], $folder_id, $one['downloads'], $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string)$one['description'], $one['protected'] ? 'RestrictedLicense' : 'UnknownLicense', $one['user_id'], $filename, $one['mkdate'], $one['chdate']));
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