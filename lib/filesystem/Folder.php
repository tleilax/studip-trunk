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
 *
 *
 * @property string id database column
 * @property string user_id database column: owner of folder
 * @property string parent_id database column: parent folder
 * @property string range_id database column: user-ID, course-ID, institute-ID etc.
 * @property string range_type database column: 'course', 'inst', 'user', ...
 * @property string folder_type database column
 * @property string name database column: folder name
 * @property string data_content database column
 * @property string description database column
 * @property string mkdate database column
 * @property string chdate database column
 */
class Folder extends SimpleORMap
{
    /**
     * @param array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'folders';
        $config['belongs_to']['owner'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        );
        $config['has_many']['file_refs'] = array(
            'class_name'        => 'FileRef',
            'assoc_foreign_key' => 'folder_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        );
        $config['has_many']['subfolders'] = array(
            'class_name'        => 'Folder',
            'assoc_foreign_key' => 'parent_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        );
        $config['belongs_to']['parentfolder'] = array(
            'class_name'  => 'Folder',
            'foreign_key' => 'parent_id',
        );
        $config['belongs_to']['course'] = array(
            'class_name'  => 'Course',
            'foreign_key' => 'range_id',
        );
        $config['belongs_to']['institute'] = array(
            'class_name'  => 'Institute',
            'foreign_key' => 'range_id',
        );
        $config['belongs_to']['user'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'range_id',
        );
        $config['belongs_to']['message'] = array(
            'class_name'  => 'Message',
            'foreign_key' => 'range_id',
        );
        $config['serialized_fields']['data_content'] = 'JSONArrayObject';

        $config['notification_map']['after_create'] = 'FolderDidCreate';
        $config['notification_map']['after_store'] = 'FolderDidUpdate';
        $config['notification_map']['before_create'] = 'FolderWillCreate';
        $config['notification_map']['before_store'] = 'FolderWillUpdate';
        $config['notification_map']['after_delete'] = 'FolderDidDelete';
        $config['notification_map']['before_delete'] = 'FolderWillDelete';
        parent::configure($config);
    }

    /**
     * @param $range_id
     * @param $range_type
     * @return SimpleORMap
     */
    public static function createTopFolder($range_id, $range_type)
    {
        $data = [
            'parent_id'    => '',
            'range_id'     => $range_id,
            'range_type'   => $range_type,
            'description'  => 'virtual top folder',
            'name'         => '',
            'data_content' => '',
            'folder_type'  => 'StandardFolder'
        ];
        return self::create($data);
    }

    /**
     * @param $rangeId
     * @return bool|string
     */
    public static function findRangeTypeById($rangeId)
    {
        if (Course::exists($rangeId)) {
            return 'course';
        } elseif (Institute::exists($rangeId)) {
            return 'inst';
        } elseif (User::exists($rangeId)) {
            return 'user';
        } elseif (Message::exists($rangeId)) {
            return 'message';
        } else {
            return false;
        }
    }


    /**
     * Helper method: Checks if the file exists in a folder.
     *
     * @returns true, if a file was found, false otherwise
     **/
    public function fileExists($fileName)
    {

        //get files :
        $foundFiles = FileRef::countBySql(
            "INNER JOIN files ON file_refs.file_id = files.id "
            . "WHERE files.name = :fileName AND folder_id = :id",
            ['fileName' => $fileName,
             'id'       => $this->id]
        );
        $foundfolders = Folder::countBySql(
            "name = :fileName AND parent_id= :id",
            ['fileName' => $fileName,
             'id'       => $this->id]
        );

        return ($foundFiles + $foundfolders) > 0;
    }

    /**
     * @param $filename
     * @return string
     */
    public function getUniqueName($filename)
    {
        $c = 0;
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if ($ext) {
            $name = substr($filename, 0, strrpos($filename, $ext) - 1);
        } else {
            $name = $filename;
        }
        while ($this->fileExists($filename)) {
            $filename = $name . '[' . ++$c . ']' . ($ext ? '.' . $ext : '');
        }
        return $filename;
    }


    /**
     * Find the root folder of a course, institute, personal file area or a message.
     * If the root folder doesn't exist, it will be created.
     *
     * @returns Folder object on success or null, if no folder can be created
     **/
    public static function findTopFolder($rangeId)
    {
        $topFolder = self::findOneBySQL("range_id = ? AND parent_id=''", [$rangeId]);

        //topFolder may not exist!
        if (!$topFolder) {
            //topFolder doest not exist: create it

            //determine range type:
            $rangeType = self::findRangeTypeById($rangeId);
            if ($rangeType) {
                //range type determined: folder can be created!
                $topFolder = self::createTopFolder($rangeId, $rangeType);
            } else {
                //no range type means we can't create a folder!
                return null;
            }
        }

        return $topFolder;
    }


    /**
     * @return mixed
     * @throws InvalidValuesException
     */
    public function getTypedFolder()
    {
        if (class_exists($this->folder_type)) {
            return new $this->folder_type($this);
        }
        throw new InvalidValuesException('class: ' . $this->folder_type . ' not found');
    }

    /**
     * @param $file_or_id
     * @param string $description
     * @param string $license
     * @return FileRef
     */
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

    /**
     * @param $fileref_or_id
     * @return mixed
     */
    public function unlinkFileRef($fileref_or_id)
    {
        $fileref = File::toObject($fileref_or_id);
        return $fileref->delete();
    }

    /**
     * @return array
     */
    public function getParents()
    {
        $path = array();
        $current = $this;
        while ($current) {
            $path[] = $current;
            if (!$current->parent_id) {
                break;
            }
            $current = $current->parentfolder;
        }
        $path = array_reverse($path);
        return $path;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getPath($delimiter = '/')
    {
        return join($delimiter, SimpleCollection::createFromArray($this->getParents())->pluck('name'));
    }

}