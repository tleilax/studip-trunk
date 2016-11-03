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
 * @property string user_id database column
 * @property string name database column
 * @property string downloads database column
 * @property string description database column
 * @property string license database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMap file belongs_to File
 * @property SimpleORMap folder belongs_to Folder
 * @property SimpleORMap owner belongs_to User
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
        $config['belongs_to']['owner'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
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
            File::deleteBySQL("id = ?", array($this->file_id));
        }
    }


    /**
        Renames the file associated with this file reference.

        If the parameter forceRename is set to true and the current user
        is the owner of the file, the file will be renamed
        even if there are other references linked with it.

        @param newName the new name of the file
        @param forceRename if set to true, renaming will be forced. Defaults to false.

        @returns true on success, false on failure
    **/
    public function rename($newName = '', $forceRename = false)
    {
        if(!$newName) {
            //you can't rename a file to (empty string)...
            return false;
        }

        if(!$this->folder->fileExists()) {
            //there is no file with that name. We can rename the file.

            //check if there are other file refercences:
            $numReferences = FileRef::countBySql(
                '(file_id = :fileId) AND (id <> :referenceId)',
                ['fileId' => $this->file_id, 'referenceId' => $this->id]
            );

            //check if the current user is the owner of the file:

            $currentUserId = User::findCurrent()->id;

            if($this->file->user_id = $currentUserId) {
                //yes, the current user owns this file. We can rename it,
                //if that is forced:

                if($numReferences > 0) {
                    //there is at least one other file reference:
                    if($forceRename) {
                        $this->file->name = $newName;
                        $this->file->store();
                    } else {
                        //do not force rename: we can't rename when there
                        //are more than one file references:
                        return false;
                    }

                } else {
                    // no other references
                }
            } else {
                //user is not the owner of the file:
                return false;
            }
        }
    }


    /**
        Copies a file to the destination folder.

        In case the current user is not the owner of the file
        the file will be cloned (including the data file).
    **/
    public function copy(Folder $destination)
    {
        //STUB
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

    public function incrementDownloadCounter()
    {
        $this->downloads++;
        return $this->store();
    }

    public function getLicenseObject()
    {
        if (class_exists($this->license)) {
            return new $this->license();
        }
        throw new InvalidValuesException('class: ' . $this->license . ' not found');
    }

    public function isDownloadable($user_id)
    {
        return true;
    }

    public function isEditable($user_id)
    {
        return true;
    }

    public function isDeletable($user_id)
    {
        return true;
    }
}