<?php
/**
 * File.php
 * model class for table files
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
 *
 *
 * @property string id database column
 * @property string user_id database column
 * @property string mime_type database column
 * @property string name database column
 * @property string size database column
 * @property string storage enum('disk', 'url') database column
 * @property string author_name database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMap owner belongs_to User
 * @property SimpleORMap url has_one FileURL
 * @property SimpleORMapCollection refs has_many FileReference
 */
class File extends SimpleORMap
{
    /**
     * @param array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'files';
        $config['belongs_to']['owner'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        );
        $config['has_many']['refs'] = array(
            'class_name'  => 'FileReference',
            'foreign_key' => 'file_id',
        );
        $config['has_one']['url'] = array(
            'class_name'  => 'FileURL',
            'foreign_key' => 'file_id',
        );
        $config['additional_fields']['extension'] = true;
        $config['additional_fields']['path'] = true;

        $config['registered_callbacks']['after_delete'][] = 'deleteDataFile';

        $config['notification_map']['after_create'] = 'FileDidCreate';
        $config['notification_map']['after_store'] = 'FileDidUpdate';
        $config['notification_map']['before_create'] = 'FileWillCreate';
        $config['notification_map']['before_store'] = 'FileWillUpdate';
        $config['notification_map']['after_delete'] = 'FileDidDelete';
        $config['notification_map']['before_delete'] = 'FileWillDelete';
        parent::configure($config);
    }

    /**
     * Returns the file extension of a file.
     * 
     * @return string A string with the file extension.
     */
    public function getExtension()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * Returns the path to the file in the operating system's file system.
     * 
     * @return null|string Returns the operating system's file system path of the file or null on failure.
     */
    function getPath()
    {
        if (!$this->id || $this->storage != 'disk') {
            return null;
        }
        return $GLOBALS['UPLOAD_PATH'] . '/' . substr($this->id, 0, 2) . '/' . $this->id;
    }

    /**
     * Deletes the data file associated with the File object.
     * 
     * @return bool Returns true on success and false on failure.
     */
    public function deleteDataFile()
    {
        return @unlink($this->getPath());
    }

    /**
     * Connects the File object to a physical file that is stored in the operating system's file system.
     * 
     * @param string $path_to_file The path to the physical file.
     * @return bool Returns true on success and false on failure.
     */
    public function connectWithDataFile($path_to_file)
    {
        $newpath = $this->getPath();

        if (!is_dir(pathinfo($newpath, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($newpath, PATHINFO_DIRNAME));
        }
        if (is_uploaded_file($path_to_file)) {
            if (!move_uploaded_file($path_to_file, $newpath)) {
                return false;
            }
        } else if (!copy($path_to_file, $newpath)) {
            return false;
        }
        return true;

    }



}