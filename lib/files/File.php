<?php
/**
 * File.php
 *
 * Class to represent files and directories in the database.
 * Should probably use SimpleORMap. Does this work for factory
 * classes like this?.
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class File extends SimpleORMap
{
    public $file_id;
    public $user_id;
    public $filename;
    public $mime_type;
    public $size;
    public $restricted;
    public $storage;            // backend name
    public $storage_id;         // backend id (NULL for directories)
    public $mkdate;
    public $chdate;

    protected $storage_object;  // backend object

    protected static $object_cache = array();

    /**
     * Get a file object for the given id. May be file or directory.
     * If the file does not exist, a new (virtual) RootDirectory is
     * created for this id. TODO Is this a good idea?
     *
     * @param string $id  file id
     *
     * @return File  File object
     */
    public static function get($id)
    {
        if (!isset(self::$object_cache[$id])) {
            $entry = self::find($id)->content;
            if (empty($entry)) {
                $file = new RootDirectory($id);
            } else {
                if ($entry['storage_id']) {
                    $file = new File($id);
                } else {
                    $file = new StudipDirectory($id);
                }

                $file->user_id = $entry['user_id'];
                $file->filename = $entry['filename'];
                $file->mime_type = $entry['mime_type'];
                $file->size = $entry['size'];
                $file->restricted = $entry['restricted'];
                $file->storage = $entry['storage'];
                $file->storage_id = $entry['storage_id'];
                $file->mkdate = $entry['mkdate'];
                $file->chdate = $entry['chdate'];

                if ($file->storage_id) {
                    $file->storage_object = new $file->storage($file->storage_id);
                }
            }
            self::$object_cache[$id] = $file;
        }
        return self::$object_cache[$id];
    }

    /**
     * Initialize a new file object for the given id.
     *
     * @param string $id  file_id
     *
     * @return File  File object
     */
    public function __construct($id)
    {
        $this->file_id = $id;
        $this->storage = 'DiskFileStorage'; // TODO: Hardcoded storage type
        $this->db_table = 'files';
        parent::__construct($id);
    }

    /**
     * Delete all the links to this file and the file itself.
     */
    public function delete()
    {
        $db = DBManager::get();

        if (isset($this->storage_object)) {
            $this->storage_object->delete();
        }

        $stmt = $db->prepare('DELETE FROM file_refs WHERE file_id = ?');
        $stmt->execute(array($this->file_id));

        $this->deleteBySQL('file_id = :file_id', array('file_id' => $this->file_id));
    }

    /**
     * Return the file creation time.
     *
     * @return int  timestamp
     */
    public function getCreationTime()
    {
        return $this->mkdate;
    }

    /**
     * Return the file id of this file.
     *
     * @return string  file id
     */
    public function getId()
    {
        return $this->file_id;
    }

    /**
     * Return the links to this file (directory entries). Each file can
     * be linked into mutiple directories, like on a POSIX file system.
     * The file is deleted when the link count drops to zero.
     *
     * @return array  array of DirectoryEntry objects
     */
    public function getLinks()
    {
        $db = DBManager::get();
        $result = array();

        $stmt = $db->prepare('SELECT id FROM file_refs WHERE parent_id = ?');
        $stmt->execute(array($this->file_id));

        foreach ($stmt as $row) {
            $result[] = new DirectoryEntry($row[0]);
        }

        return $result;
    }

    /**
     * Return the file's name.
     *
     * @return string file name
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Return the file's mime type, if known.
     *
     * @return string  mime type (NULL if unknown)
     */
    public function getMimeType()
    {
        return $this->mime_type;
    }

    /**
     * Return the file modification time.
     *
     * @return int  timestamp
     */
    public function getModificationTime()
    {
        return $this->chdate;
    }

    /**
     * Return the file's storage path.
     *
     * @return string storage path
     */

    public function getStoragePath()
    {
        $path = $this->storage_object->getPath();
        return  $path;
    }

    /**
     * Return the Storage Opject from File.
     *
     * @return Storage Object
     */
    public function getStorageObject(){
        return $this->storage_object;
    }

    /**
     * Return the file owner's user id.
     *
     * @return string  user id
     */
    public function getOwner()
    {
        return $this->user_id;
    }

    /**
     * Return the file's size in bytes.
     *
     * @return int  file size
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Check if the file's backend allows reading of files.
     *
     * @return boolean  TRUE or FALSE
     */
    public function isReadable()
    {
        return $this->storage_object->isReadable();
    }

    /**
     * Check if the file may be downloaded in open courses.
     * This was formerly called 'protected'.
     *
     * @return boolean  TRUE or FALSE
     */
    public function isRestricted()
    {
        return $this->restricted;
    }

    /**
     * Check if the file's backend allows writing of files.
     *
     * @return boolean  TRUE or FALSE
     */
    public function isWritable()
    {
        return $this->storage_object->isWritable();
    }

    /**
     * Open a PHP stream resource for this file.
     * Access mode parameter works just like fopen.
     *
     * @param string $mode  access mode (see fopen)
     *
     * @return resource  file handle
     */
    public function open($mode)
    {
        return $this->storage_object->open($mode);
    }

    /**
     * Set the file's name.
     *
     * @param string $filename file name
     */
    public function setNewFilename($filename)
    {
        $this->filename = $filename;
        $this->setData(array('filename' => $filename));
        $this->store();
        
    }
    

    /**
     * Set the file's mime type.
     *
     * @param string $mime_type  mime type
     */
    public function setNewMimeType($mime_type)
    {
        $this->mime_type = $mime_type;
        $this->setData(array('mime_type' => $mime_type));
        $this->store();
    }
    

    /**
     * Set the file's owner.
     *
     * @param string $user_id  user id
     */
    public function setNewOwner($user_id)
    {
        $this->user_id = $user_id;
        $this->setData(array('user_id' => $user_id));
        $this->store();
    }
    
    /**
     * Set the file's download restriction. Restricted files may
     * only be downloaded in closed courses.
     *
     * @param boolean $restricted  TRUE or FALSE
     */
    public function setNewRestricted($restricted)
    {
        $this->setData(array('restricted' => $restricted));
        $this->store();
        $this->restricted = $restricted;
    }
    
    /**
     * Update this file's metadata if the content has changed.
     * Note: This needs to be called after each update of the file.
     */
    public function update() 
    {
    
        $this->mkdate = $this->getCreationTime();
        $this->mime_type = $this->getMimeType();
        $this->chdate = $this->getModificationTime();
        $this->size = $this->getSize();
        $this->setData(array('mkdate' => $this->mkdate,
                          'mime_type' => $this->mime_type,
                          'size' => $this->size,
                          'chdate' => $this->chdate));
        $this->store();
    }
}