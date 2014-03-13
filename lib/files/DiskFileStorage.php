<?php
/**
 * File.php
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * Class to represent files on disk (the only supported backend for now).
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/datei.inc.php';       // get_upload_file_path()

class DiskFileStorage implements FileStorage
{
    protected $storage_id;      // backend id
    protected $file_path;       // path on disk

    /**
     * Initialize a new DiskFileStorage object for the given id.
     * A new (empty) file is created of storage_id is NULL.
     *
     * @param string $storage_id  file id or NULL
     */
    public function __construct($storage_id = NULL)
    {
        if (isset($storage_id)) {
            $this->storage_id = $storage_id;
            global $USER_DOC_PATH;
            $path = $USER_DOC_PATH.'/'.$GLOBALS['user']->id.'/'.$this->storage_id;
            $this->file_path = $path;//get_upload_file_path($storage_id);

            /* TODO Should a DiskFileStorage exist without backing file?
            if (!file_exists($this->file_path)) {
                throw new InvalidArgumentException('file not found');
            }
            */
        } else {
            $this->storage_id = md5(uniqid(__CLASS__, true));
        }
    }

    /**
     * Delete this file from disk.
     */
    public function delete()
    {
        unlink($this->file_path);
    }

    public function getPath()
    {
        return $this->file_path;
    }
    /**
     * Check whether the file exists on disk.
     *
     * @return boolean  TRUE or FALSE
     */
    public function exists()
    {
        return file_exists($this->file_path);
    }

    /**
     * Return the file creation time.
     *
     * @return int  timestamp
     */
    public function getCreationTime()
    {
        return filectime($this->file_path);
    }

    /**
     * Return the backend id of this file.
     *
     * @return string  backend id
     */
    public function getId()
    {
        return $this->storage_id;
    }

    /**
     * Return the file's mime type, if known.
     *
     * @return string  mime type (NULL if unknown)
     */
    public function getMimeType()
    {
        return NULL;
    }

    /**
     * Return the file modification time.
     *
     * @return int  timestamp
     */
    public function getModificationTime()
    {
        return filemtime($this->file_path);
    }

    /**
     * Return the file's size in bytes.
     *
     * @return int  file size
     */
    public function getSize()
    {
        return filesize($this->file_path);
    }

    /**
     * Check if this backend allows reading of files.
     *
     * @return boolean  TRUE
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * Check if this backend allows writing of files.
     *
     * @return boolean  TRUE
     */
    public function isWritable()
    {
        return true;
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
        return fopen($this->file_path, $mode);
    }
    
    public static function getQuotaUsage($user_id)
    {
        $db = DBManager::get();
        $stmt = $db->prepare('SELECT SUM(size) FROM files WHERE user_id = :user_id');
        $stmt->execute(array('user_id' => $user_id));
        $result = $stmt->fetchColumn();
        if($result == 'NULL'){
            return  0;
        }else{
            return $result;
        }
    }
}
