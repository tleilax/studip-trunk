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

class File // extends SimpleORMap
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

    /**
     * Get a file object for the given id. May be file or directory.
     * If the file does not exist, a new (virtual) RootDirectory is
     * created for this id. TODO Is this a good idea?
     * TODO Maybe use a singleton (with cache) here?
     *
     * @param string $id  file id
     *
     * @return File  File object
     */
    public static function get($id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT * FROM files WHERE file_id = ?');
        $stmt->execute(array($id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            return new RootDirectory($id);
        }

        if ($result['storage_id']) {
            $file = new File($id);
        } else {
            $file = new StudipDirectory($id);
        }

        $file->user_id = $result['user_id'];
        $file->filename = $result['filename'];
        $file->mime_type = $result['mime_type'];
        $file->size = $result['size'];
        $file->restricted = $result['restricted'];
        $file->storage = $result['storage'];
        $file->storage_id = $result['storage_id'];
        $file->mkdate = $result['mkdate'];
        $file->chdate = $result['chdate'];

        if ($file->storage_id) {
            $file->storage_object = new $file->storage($file->storage_id);
        }
        return $file;
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

        $stmt = $db->prepare('DELETE FROM files WHERE file_id = ?');
        $stmt->execute(array($this->file_id));
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
     * Return the file's entry type.
     *
     * @return string
     */

    public function getEntryType()
    {
         if (empty($this->storage_id))
             return "Ordner";
         else
             return "Datei";
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
        $stmt->execute(array($this->id));

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
    public function setFilename($filename)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE files SET filename = ? WHERE file_id = ?');        
        $stmt->execute(array($filename, $this->file_id));
        
        $this->filename = $filename;
    }

    /**
     * Set the file's mime type.
     *
     * @param string $mime_type  mime type
     */
    public function setMimeType($mime_type)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE files SET mime_type = ? WHERE file_id = ?');
        $stmt->execute(array($mime_type, $this->file_id));

        $this->mime_type = $mime_type;
    }

    /**
     * Set the file's owner.
     *
     * @param string $user_id  user id
     */
    public function setOwner($user_id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE files SET user_id = ? WHERE file_id = ?');
        $stmt->execute(array($user_id, $this->file_id));

        $this->user_id = $user_id;
    }

    /**
     * Set the file's download restriction. Restricted files may
     * only be downloaded in closed courses.
     *
     * @param boolean $restricted  TRUE or FALSE
     */
    public function setRestricted($restricted)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE files SET restricted = ? WHERE file_id = ?');
        $stmt->execute(array($restricted, $this->file_id));

        $this->restricted = $restricted;
    }

    /**
     * Update this file's metadata if the content has changed.
     * Note: This needs to be called after each update of the file.
     */
    public function update()
    {
        $db = DBManager::get();
        $this->mkdate = $this->getCreationTime();
        $this->mime_type = $this->getMimeType();
        $this->chdate = $this->getModificationTime();
        $this->size = $this->getSize();

        $stmt = $db->prepare('UPDATE files SET mkdate = ?, mime_type = ?, chdate = ?, size = ? WHERE file_id = ?');
        $stmt->execute(array($this->mkdate, $this->mime_type, $this->chdate, $this->size, $this->file_id));
    }
}
