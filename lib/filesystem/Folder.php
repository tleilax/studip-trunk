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
        $config['serialized_fields']['data_content'] = 'JSONArrayObject';

        $config['notification_map']['after_create'] = 'FolderDidCreate';
        $config['notification_map']['after_store'] = 'FolderDidUpdate';
        $config['notification_map']['before_create'] = 'FolderWillCreate';
        $config['notification_map']['before_store'] = 'FolderWillUpdate';
        $config['notification_map']['after_delete'] = 'FolderDidDelete';
        $config['notification_map']['before_delete'] = 'FolderWillDelete';
        parent::configure($config);
    }

    public static function createTopFolder($range_id, $range_type)
    {
        $data = [
            'parent_id' => '',
            'range_id' => $range_id,
            'range_type' => $range_type,
            'description' => 'virtual top folder',
            'name' => '',
            'data_content' => '',
            'folder_type' => 'StandardFolder'
        ];
        return self::create($data);
    }
    
    /**
        Helper method to find the range type from a range-ID.
        
        @returns 'course', 'inst', 'user', 'message' on success, false on error.
    **/
    public static function findRangeTypeById($rangeId)
    {
        if(Course::exists($rangeId)) {
            return 'course';
        } elseif(Institute::exists($rangeId)) {
            return 'inst';
        } elseif(User::exists($rangeId)) {
            return 'user';
        } elseif(Message::exists($rangeId)) {
            return 'message';
        } else {
            return false;
        }
    }
    
    
    /**
        Helper method: Checks if the file exists in a folder.
        
        @returns true, if a file was found, false otherwise
    **/
    public function fileExists($fileName = '')
    {
        if(!$fileName) {
            //you can't search for a file with no name...
            return false;
        }
        
        //get files :
        $foundFiles = FileRef::countBySql(
              "INNER JOIN files ON file_refs.file_id = files.id "
            . "WHERE files.name = :fileName",
            ['fileName' => $fileName]
        );
        
        return ($foundFiles > 0);
    }
    
    
    
    /**
        Find the root folder of a course, institute, personal file area or a message.
        If the root folder doesn't exist, it will be created.
        
        @returns Folder object on success or null, if no folder can be created
    **/
    public static function findTopFolder($rangeId)
    {
        $topFolder = self::findOneBySQL("range_id = ? AND parent_id=''", [$rangeId]);
        
        //topFolder may not exist!
        if(!$topFolder) {
            //topFolder doest not exist: create it
            
            //determine range type:
            $rangeType = self::findRangeTypeById($rangeId);
            if($rangeType) {
                //range type determined: folder can be created!
                $topFolder = self::createTopFolder($rangeId, $rangeType);
            } else {
                //no range type means we can't create a folder!
                return null;
            }
        }
        
        return $topFolder;
    }
    
    
    public function getTypedFolder()
    {
        if (class_exists($this->folder_type)) {
            return new $this->folder_type($this);
        }
        throw new InvalidValuesException('class: ' . $this->folder_type . ' not found');
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

    public function getParents() {
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

    public function getPath($delimiter = '/')
    {
        return join($delimiter, SimpleCollection::createFromArray($this->getParents())->pluck('name'));
    }

}