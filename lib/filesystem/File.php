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
     * @return mixed
     */
    public function getExtension()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    /**
     * @return null|string
     */
    function getPath()
    {
        if (!$this->id || $this->storage != 'disk') {
            return null;
        }
        return $GLOBALS['UPLOAD_PATH'] . '/' . substr($this->id, 0, 2) . '/' . $this->id;
    }

    /**
     * @return bool
     */
    public function deleteDataFile()
    {
        return @unlink($this->getPath());
    }

    /**
     * @param $path_to_file
     * @return bool
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