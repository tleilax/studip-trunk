<?php
/**
 * Folder.php
 * model class for table folders
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class Folder extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'folders';
        $config['belongs_to']['owner'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        );
        $config['has_many']['file_refs'] = array(
            'class_name'  => 'FileRef',
            'assoc_foreign_key' => 'folder_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['has_many']['subfolders'] = array(
            'class_name'  => 'Folder',
            'assoc_foreign_key' => 'parent_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['belongs_to']['parentfolder'] = array(
            'class_name' => 'Folder',
            'foreign_key' => 'parent_id',
        );
        $config['belongs_to']['course'] = array(
            'class_name' => 'Course',
            'foreign_key' => 'range_id',
        );
        $config['belongs_to']['institute'] = array(
            'class_name' => 'Institute',
            'foreign_key' => 'range_id',
        );
        $config['belongs_to']['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'range_id',
        );
        $config['belongs_to']['message'] = array(
            'class_name' => 'Message',
            'foreign_key' => 'range_id',
        );
        $config['notification_map']['after_create'] = 'FolderDidCreate';
        $config['notification_map']['after_store'] = 'FolderDidUpdate';
        $config['notification_map']['before_create'] = 'FolderWillCreate';
        $config['notification_map']['before_store'] = 'FolderWillUpdate';
        $config['notification_map']['after_delete'] = 'FolderDidDelete';
        $config['notification_map']['before_delete'] = 'FolderWillDelete';
        parent::configure($config);
    }


    public function getTypedFolder()
    {

    }

    public function linkFile($file_or_id, $description = '', $license = 'UnknownLicense')
    {

        $file = File::toObject($file_or_id);
        $ref = new FileRef();
        $ref->file_id = $file->id;
        $ref->folder_id = $this->id;
        $ref->description = $description;
        $ref->license = $license;
        if ($ref->store()) {
            return $ref;
        }
    }

    public function unlinkFileRef($fileref_or_id)
    {
        $fileref = File::toObject($fileref_or_id);
        return $fileref->delete();
    }



}