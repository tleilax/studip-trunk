<?php
/**
 * FileRef.php
 * model class for table file_refs
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
class FileRef extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'file_refs';
        $config['belongs_to']['file'] = array(
            'class_name'  => 'File',
            'foreign_key' => 'file_id',
        );
        $config['belongs_to']['folder'] = array(
            'class_name'  => 'Folder',
            'foreign_key' => 'folder_id',
        );
        $config['registered_callbacks']['after_delete'][] = 'cbRemoveFileIfOrphaned';
        $config['notification_map']['after_create'] = 'FileRefDidCreate';
        $config['notification_map']['after_store'] = 'FileRefDidUpdate';
        $config['notification_map']['before_create'] = 'FileRefWillCreate';
        $config['notification_map']['before_store'] = 'FileRefWillUpdate';
        $config['notification_map']['after_delete'] = 'FileRefDidDelete';
        $config['notification_map']['before_delete'] = 'FileRefWillDelete';

        parent::configure($config);
    }

    public function cbRemoveFileIfOrphaned()
    {
        if (!self::countBySql("file_id = ?", array($this->file_id))) {
            File::deleteBySQL("id = ?", $this->file_id);
        }
    }
}