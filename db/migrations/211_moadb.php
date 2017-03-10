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
    
    
    private function insert52aTermsOfUse($db)
    {
        // Copied and modified from cli script add_52a_tables from Stud.IP 3.5:
        $db->exec("INSERT IGNORE INTO `content_terms_of_use_entries`
        (`id`, `name`, `download_condition`, `description`, `position`, `icon`)
        VALUES
        ('3RD_PARTY_TRUE', 'Dieses Dokument ist frei von Rechten Dritter', 0, '', '0', 'decline-circle'),
        ('3RD_PARTY_FALSE', 'Dieses Dokument ist **nicht** frei von Rechten Dritter', 1, '', '1', 'check-circle'),
        ('UNDEF_LICENSE', 'Ungeklärte Lizenz', 2, 'Diese Datei enthält Material mit einer ungeklärten Lizenz.', '2', 'question-circle'),
        ('SELFMADE_NONPUB', 'Selbst verfasstes, nicht publiziertes  Werk', 0, 'Das Dokument wird von den Autor/-innen zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.\n\nFür darüber hinaus gehende Erlaubnisse (Weitergabe, Veränderung) wenden Sie sich an die Autor/-innen oder beachten Sie die Hinweise im Dokument.', '3', 'own-license'),
        ('FREE_LICENSE', 'Werk mit freier Lizenz', 0, 'Das Dokument unterliegt einer freien Lizenz. Sie dürfen es weitergeben und unter Beachtung der Details der Lizenz (s. Angaben im Dokument) verändern und in eigene Werke übernehmen.', '4', 'cc'),
        ('WITH_LICENSE', 'Nutzungserlaubnis oder Lizenz liegt vor', 1, 'Das Dokument wird zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.', '5', 'license'),
        ('NON_TEXTUAL', 'Abbildungen, Fotos, Filme, Musikstücke, Partituren', 1, 'Das Dokument wird zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.', '6', '52a'),
        ('TEXT_NO_LICENSE', 'Publizierte Texte ohne erworbene Lizenz oder gesonderte Erlaubnis', 2, 'Das Dokument kann nicht heruntergeladen werden, da es urheberrechtlich geschützt ist und keine Erlaubnis für die Weitergabe vorliegt.', '7', '52a-stopp2')
        ");

    }
    
    
    private function updateLicenseIds($db)
    {
        //We must convert the old IDs from the document_licenses table
        //to the new IDs from the content_terms_of_use_entries table:
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = '3RD_PARTY_TRUE' WHERE content_terms_of_use_id = '0';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = '3RD_PARTY_FALSE' WHERE content_terms_of_use_id = '1';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'UNDEF_LICENSE' WHERE content_terms_of_use_id = '2';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'SELFMADE_NONPUB' WHERE content_terms_of_use_id = '3';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'FREE_LICENSE' WHERE content_terms_of_use_id = '4';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'WITH_LICENSE' WHERE content_terms_of_use_id = '5';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'NON_TEXTUAL' WHERE content_terms_of_use_id = '6';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'TEXT_NO_LICENSE' WHERE content_terms_of_use_id = '7';");
    }
    
    

    public function up()
    {
        $db = DBManager::get();

        try {
            $db->exec("RENAME TABLE files TO _files");
            $db->exec("RENAME TABLE file_refs TO _file_refs");
        } catch (PDOException $e)
        {

        }




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
                 `access_type` enum('proxy','redirect') NOT NULL DEFAULT 'proxy',
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
            `position` int(10) unsigned NOT NULL,
            `description` TEXT NOT NULL,
            `download_condition` TINYINT(2) NOT NULL,
            `icon` VARCHAR(128) NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC"
        );


        //default terms of use entries:
        $this->insert52aTermsOfUse($db);
        
        //map old 52 license IDs to new terms of use entries:
        $this->updateLicenseIds($db);



        //top folder courses
        $institute_folders = array();
        foreach ($db->query("SELECT i.institut_id as new_range_id,i.name FROM `folder` f INNER JOIN `Institute` i ON i.institut_id = f.range_id OR MD5(CONCAT(i.institut_id, 'top_folder')) = f.range_id group by i.institut_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = '';
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
            $folder['description'] = '';
            $folder['name'] = '';
            $this->migrateFolder($folder, $folder['seminar_id'], 'user', 'StandardFolder');
        }
        $seminar_folders = array();
        foreach ($db->query("SELECT s.seminar_id as new_range_id,s.name FROM `folder` f INNER JOIN `seminare` s ON s.Seminar_id = f.range_id OR MD5(CONCAT(s.Seminar_id, 'top_folder')) = f.range_id group by s.Seminar_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = '';
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
            $data_content = json_encode(['group_id' => $folder['range_id']]);
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'CourseGroupFolder', $data_content);
        }
        //issue folder
        foreach ($db->query("SELECT f.*, t.seminar_id AS seminar_id FROM `folder` f INNER JOIN `themen` t ON t.issue_id = f.range_id") as $folder) {
            $data_content = json_encode(['issue_id' => $folder['range_id']]);
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'CourseTopicFolder', $data_content);
        }

        //personal documents
        foreach ($db->fetchFirst("SELECT distinct parent_id FROM `_file_refs` inner join auth_user_md5 where parent_id=user_id") as $user_id) {
            $top_folder_id = $db->fetchColumn("SELECT id FROM folders WHERE range_type = 'user' AND range_id=?", [$user_id]);
            if (!$top_folder_id) {
                $folder = [
                    'folder_id' => md5(uniqid($user_id)),
                    'range_id' => '',
                    'range_type' => 'user',
                    'parent_id' => '',
                    'user_id' => $user_id,
                    'description' => '',
                    'name' => '',
                    'mkdate' => time(),
                    'chdate' => time()
                ];
                $this->migratePersonalFolder($folder, $user_id);
                $top_folder_id = $folder['folder_id'];
            }
            $this->migratePersonalFiles($db->fetchAll("SELECT file_id,id,storage_id,mime_type,user_id,filename,description,mkdate,chdate,downloads,size FROM `_files` inner join _file_refs using(file_id) WHERE parent_id = ? and storage_id<>''", array($user_id)), $top_folder_id);
            $subfolders = $db->fetchAll("SELECT file_id as folder_id,user_id,'{$top_folder_id}' as range_id,name,description,mkdate,chdate FROM _file_refs INNER JOIN _files USING(file_id) WHERE storage_id='' AND parent_id = ?", array($user_id));
            foreach ($subfolders as $one) {
                $this->migratePersonalFolder($one, $user_id);
            }
        }


        //migrate message attachments:
        $this->migrateMessageAttachments();





        //delete configuration variables designed for the old file area:
        $db->exec(
            "DELETE FROM `config`
            WHERE
            (field = 'PERSONALDOCUMENT_OPEN_ACCESS')
            OR
            (field = 'PERSONALDOCUMENT_OPEN_ACCESS_ROOT_PRIVILEDGED');"
        );


    }




    public function migrateMessageAttachments()
    {
        //First we wipe out all documents with range-ID = 'provisional'.
        //Such documents were meant to be attached to mails but were left
        //unattached... to remain lonely in the database...
        //So it's time to end this misery and delete them!
        /*
        $unattached_documents = StudipDocument::deleteBySql(
            "range_id = 'provisional'"
        );
        */

        $db = DBManager::get();

        //then we retrieve all message-IDs:
        $message_rows = $db->query("SELECT DISTINCT message_id,autor_id,subject,message.mkdate FROM message INNER JOIN dokumente ON range_id = message_id");

        foreach($message_rows as $message_row) {
            //now we loop through each message ID and check if there are
            //files in the dokumente table with that range-ID:
            $message_id = $message_row['message_id'];

                //we found at least one attachment: create a top folder for this message:
                $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $folder_id = md5($message_id . '_attachments');

                $insert_folder->execute([
                    $folder_id,
                    $message_row['autor_id'],
                    '',
                    $message_id,
                    'message',
                    'MessageFolder',
                    $message_row['subject'],
                    '',
                    '',
                    $message_row['mkdate'],
                    $message_row['mkdate']
                ]);

                $this->migrateFiles($db->fetchAll(
                    "SELECT * FROM dokumente WHERE range_id = :range_id",
                    [
                        'range_id' => $message_id
                    ]
                ), $folder_id);

        }
    }


    public function migrateFolder($folder, $range_id, $range_type, $folder_type, $data_content = '')
    {
        $db = DBManager::get();
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $folder_type = $folder_type == 'StandardFolder' && isset($folder['permission']) && $folder['permission'] != 7 ? 'PermissionEnabledFolder' : $folder_type;
        $data_content = $data_content = '' && isset($folder['permission']) && $folder['permission'] != 7 ? json_encode(['permission' => $folder['permission']]): '';
        $insert_folder->execute(array($folder['folder_id'], $folder['user_id'], $folder['range_id'], $range_id, $range_type, $folder_type, $folder['name'], $data_content, (string)$folder['description'], $folder['mkdate'], $folder['chdate']));
        $subfolders = $db->fetchAll("SELECT * FROM folder WHERE range_id = ?", array($folder['folder_id']));
        foreach ($subfolders as $one) {
            $this->migrateFolder($one, $range_id, $range_type, 'StandardFolder');
        }
        $this->migrateFiles($db->fetchAll("SELECT * FROM dokumente WHERE range_id = ?", array($folder['folder_id'])), $folder['folder_id']);



    }

    public function migrateFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `content_terms_of_use_id`, `user_id`, `name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file = $db->prepare("INSERT INTO `files` (`id`, `user_id`, `mime_type`, `name`, `size`, `storage`, `author_name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file_url = $db->prepare("INSERT INTO `file_urls` (`file_id`, `url`) VALUES (?, ?)");
        $filenames = array();
        foreach ($files as $one) {
            $c = 0;
            $filename = $one['filename'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $name = substr($filename, 0, strrpos($filename, $ext) - 1);
            } else {
                $name = $filename;
            }
            while (in_array($filename, $filenames)) {
                $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
            }
            $filenames[] = $filename;
            $insert_file_ref->execute(array(
                $one['dokument_id'],
                $one['dokument_id'],
                $folder_id,
                $one['downloads'],
                $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string) $one['description'],
                $one['protected'],
                $one['user_id'],
                $filename,
                $one['mkdate'],
                $one['chdate']
            ));
            $insert_file->execute(array($one['dokument_id'], $one['user_id'], get_mime_type($one['filename']), $filename, $one['filesize'], $one['url'] ? 'url' : 'disk', $one['author_name'], $one['mkdate'], $one['chdate']));
            if ($one['url']) {
                $insert_file_url->execute(array($one['dokument_id'], $one['url']));
            }
        }
    }

    public function migratePersonalFolder($folder, $range_id)
    {
        $db = DBManager::get();
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, 'user', 'StandardFolder', ?, '', ?, ?, ?)");

        $insert_folder->execute(array($folder['folder_id'], $folder['user_id'], $folder['range_id'], $range_id, $folder['name'], (string)$folder['description'], $folder['mkdate'], $folder['chdate']));
        $subfolders = $db->fetchAll("SELECT file_id as folder_id,user_id,parent_id as range_id,name,description,mkdate,chdate FROM _file_refs INNER JOIN _files USING(file_id) WHERE storage_id='' AND parent_id = ?", array($folder['folder_id']));
        foreach ($subfolders as $one) {
            $this->migratePersonalFolder($one, $range_id);
        }
        $this->migratePersonalFiles($db->fetchAll("SELECT file_id,id,storage_id,mime_type,user_id,filename,description,mkdate,chdate,downloads,size FROM `_files` inner join _file_refs using(file_id) WHERE parent_id = ? and storage_id<>''", array($folder['folder_id'])), $folder['folder_id']);


    }

    public function migratePersonalFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `content_terms_of_use_id`, `user_id`, `name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file = $db->prepare("INSERT INTO `files` (`id`, `user_id`, `mime_type`, `name`, `size`, `storage`, `author_name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $filenames = array();
        foreach ($files as $one) {
            $c = 0;
            $filename = $one['filename'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $name = substr($filename, 0, strrpos($filename, $ext) - 1);
            } else {
                $name = $filename;
            }
            while (in_array($filename, $filenames)) {
                $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
            }
            $filenames[] = $filename;
            $insert_file_ref->execute(array(
                $one['id'],
                $one['storage_id'],
                $folder_id,
                $one['downloads'],
                $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string) $one['description'],
                0,
                $one['user_id'],
                $filename,
                $one['mkdate'],
                $one['chdate']
            ));
            $insert_file->execute(array($one['storage_id'], $one['user_id'], $one['mime_type'], $filename, $one['size'], 'disk', '', $one['mkdate'], $one['chdate']));
            $new_path = $GLOBALS['UPLOAD_PATH'] . '/' . substr($one['storage_id'], 0, 2) . '/' . $one['storage_id'];
            $old_path = $GLOBALS['USER_DOC_PATH'] . '/' . substr($one['storage_id'], 0, 2) . '/' . $one['storage_id'];
            @rename($old_path, $new_path);
        }
    }

    public function down()
    {
    }
}