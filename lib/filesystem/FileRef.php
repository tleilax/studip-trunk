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
 *
 *
 * @property string id database column
 * @property string file_id database column
 * @property string folder_id database column
 * @property string downloads database column
 * @property string description database column
 * @property string license database column
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

    public function getDownloadURL($dltype = 'normal')
    {
        $mode = Config::get()->SENDFILE_LINK_MODE ?: 'normal';
        $link = array();
        $type = '0';
        $file_name = $this->file->name;
        $file_id = $this->id;
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
                $link[] = '/' . $file_name;
                break;

            case 'normal':
            default:
                $link[] = 'sendfile.php?';
                if ($dltype == 'zip'){
                    $link[] = 'zip=1&';
                } elseif ($dltype == 'force_download' || $dltype == 'force') {
                    $link[] = 'force_download=1&';
                }
                $link[] = 'type='.$type;
                $link[] = '&file_id=' . $file_id;
                $link[] = '&file_name=' . $file_name;
        }
        return URLHelper::getScriptURL(implode('', $link));
    }
}