<?php
/**
 * File.php
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/datei.inc.php';       // get_upload_file_path()

/**
 * Class to represent files and directories in the database.
 * Should probably use SimpleORMap. Does this work for factory
 * classes like this?.
 */
class File // extends SimpleORMap
{
    public $id;
    public $user_id;
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

        $stmt = $db->prepare('SELECT * FROM files WHERE id = ?');
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
        $file->mime_type = $result['mime_type'];
        $file->size = $result['size'];
        $file->restricted = $result['restricted'];
        $file->storage = $result['storage'];
        $file->storage_id = $result['storage_id'];
        $file->mkdate = strtotime($result['mkdate']);
        $file->chdate = strtotime($result['chdate']);

        if ($file->storage_id) {
            $file->storage_object = new $file->storage($file->storage_id);
        }

        return $file;
    }

    /**
     * Initialize a new file object for the given id.
     *
     * @param string $id  file id
     *
     * @return File  File object
     */
    public function __construct($id)
    {
        $this->id = $id;
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
        $stmt->execute(array($this->id));

        $stmt = $db->prepare('DELETE FROM files WHERE id = ?');
        $stmt->execute(array($this->id));
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
        return $this->id;
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
     * Set the file's mime type.
     *
     * @param string $mime_type  mime type
     */
    public function setMimeType($mime_type)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE files SET mime_type = ? WHERE id = ?');
        $stmt->execute(array($mime_type, $this->id));

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

        $stmt = $db->prepare('UPDATE files SET user_id = ? WHERE id = ?');
        $stmt->execute(array($user_id, $this->id));

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

        $stmt = $db->prepare('UPDATE files SET restricted = ? WHERE id = ?');
        $stmt->execute(array($restricted, $this->id));

        $this->restricted = $restricted;
    }

    /**
     * Update this file's metadata if the content has changed.
     * Note: This needs to be called after each update of the file.
     */
    public function update()
    {
        $db = DBManager::get();

        $this->mkdate = $storage_object->getCreationTime();
        $this->mime_type = $storage_object->getMimeType();
        $this->chdate = $storage_object->getModificationTime();
        $this->size = $storage_object->getSize();

        $stmt = $db->prepare('UPDATE files SET mkdate = FROM_UNIXTIME(?), mime_type = ?, chdate = FROM_UNIXTIME(?), size = ? WHERE id = ?');
        $stmt->execute(array($this->mkdate, $this->mime_type, $this->chdate, $this->size, $this->id));
    }
}

/**
 * Class to represent directories in the database. Every
 * directory is also a file, so this a subclass of File.
 */
class StudipDirectory extends File
{
    /**
     * Get a root directory object for the given context id.
     * Root directories are not represented in the database.
     * TODO Is this function really needed?
     *
     * @param string $context_id  context (course id etc.)
     *
     * @return StudipDirectory  directory object
     */
    public static function getRootDirectory($context_id)
    {
        return File::get($context_id);
    }

    /**
     * Create a new empty file in this directory under the
     * given name and returns the directory entry.
     *
     * @param string $name  file name
     *
     * @return DirectoryEntry  created DirectoryEntry object
     */
    public function create($name)
    {
        $db = DBManager::get();

        $file_id = md5(uniqid(__CLASS__, true));
        $user_id = $GLOBALS['user']->id;
        $mime_type = 'text/plain';
        $storage_object = new $this->storage();

        $stmt = $db->prepare('INSERT INTO files (id, user_id, mime_type, size, restricted, storage, storage_id, mkdate, chdate)
                                    VALUES(?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?))');
        $stmt->execute(array($file_id, $user_id, $mime_type, 0, 0, $this->storage, $storage_object->getId(), time(), time()));

        return $this->link(File::get($file_id), $name);
    }

    /**
     * Create a new file by copying the contents of an existing
     * file into this directory under the given name. Unlike the
     * link() method, this creates a separate file. Returns the
     * directory entry.
     *
     * @param File $source  source file to copy
     * @param string $name  destination file name
     *
     * @return DirectoryEntry  created DirectoryEntry object
     */
    public function copy(File $source, $name)
    {
        $new_entry = $this->create($name);
        $new_file = $new_entry->getFile();
        $source_fp = $source->open('rb');
        $dest_fp = $new_file->open('wb');

        while (!feof($source_fp)) {
            $buffer = fread($source_fp, 65536);
            fwrite($dest_fp, $buffer);
        }

        fclose($dest_fp);
        fclose($source_fp);

        // copy some attributes
        $new_file->setMimeType($source->getMimeType());
        $new_file->setRestricted($source->isRestricted());

        return $new_entry;
    }

    /**
     * Return the entry with the given name in this directory,
     * if one exists (returns NULL otherwise).
     *
     * @param string $name  file name
     *
     * @return DirectoryEntry  DirectoryEntry object or NULL
     */
    public function getEntry($name)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT id FROM file_refs WHERE parent_id = ? AND name = ?');
        $stmt->execute(array($this->id, $name));
        $id = $stmt->fetchColumn();

        return $id ? new DirectoryEntry($id) : NULL; // should this throw an error on failure?
    }

    /**
     * Get access permissions for this directory. Access
     * permissions are not implemented at this time.
     */
    public function getPermissions()
    {
        // TODO not yet implemented
        return NULL;
    }

    /**
     * Check whether this directory is empty.
     *
     * @return boolean  TRUE or FALSE
     */
    public function isEmpty()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT COUNT(id) FROM file_refs WHERE parent_id = ?');
        $stmt->execute(array($this->id));
        $count = $stmt->fetchColumn();

        return $count == 0;
    }

    /**
     * Check whether this directory is a root directory,
     * i.e. an instance of the RootDirectory class.
     *
     * @return boolean  TRUE or FALSE
     */
    public function isRootDirectory()
    {
        return $this instanceof RootDirectory;
    }

    /**
     * Create a new entry in this directory for the given file
     * under the given name. This will increase the link count
     * of the file by one.
     *
     * @param File $file    file to link
     * @param string $name  new file name
     *
     * @return DirectoryEntry  created DirectoryEntry object
     */
    public function link(File $file, $name)
    {
        $db = DBManager::get();

        if ($this->getEntry($name)) {
            throw new Exception('file "' . $name . '" already exists');
        }

        $entry_id = md5(uniqid(__CLASS__, true));

        $stmt = $db->prepare('INSERT INTO file_refs (id, file_id, parent_id, name) VALUES(?, ?, ?, ?)');
        $stmt->execute(array($entry_id, $file->getId(), $this->id, $name));

        return new DirectoryEntry($entry_id);
    }

    /**
     * Return a list of all entries in this directory.
     * Each entry is returned as a DirectoryEntry object.
     *
     * @return array  array of DirectoryEntry objects
     */
    public function listFiles()
    {
        $db = DBManager::get();
        $result = array();

        $stmt = $db->prepare('SELECT id FROM file_refs WHERE parent_id = ?');
        $stmt->execute(array($this->id));

        foreach($stmt as $row) {
            $result[] = new DirectoryEntry($row[0]);
        }

        return $result;
    }

    /**
     * Create a new sub directory with the given name in this
     * directory. It inherits the backend storage of its parent.
     *
     * @param string $name  directory name
     */
    public function mkdir($name)
    {
        $db = DBManager::get();

        $file_id = md5(uniqid(__CLASS__, true));
        $user_id = $GLOBALS['user']->id;
        $mime_type = '';

        $stmt = $db->prepare('INSERT INTO files (id, user_id, mime_type, size, restricted, storage, storage_id, mkdate, chdate)
                                    VALUES(?, ?, ?, ?, ?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?))');
        $stmt->execute(array($file_id, $user_id, $mime_type, 0, 0, $this->storage, '', time(), time()));

        $dir = File::get($file_id);
        return $this->link($dir, $name);
    }

    /**
     * Delete the contents of this directory (recursively).
     */
    public function delete()
    {
        foreach ($this->listFiles() as $entry) {
            $entry->getFile()->delete();
        }

        parent::delete();
    }

    /**
     * Check if the directory's backend allows reading of files.
     *
     * @return boolean  TRUE or FALSE
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * Check if the directory's backend allows writing of files.
     *
     * @return boolean  TRUE or FALSE
     */
    public function isWritable()
    {
        return true;
    }

    /**
     * Opening a directory is not allowed.
     */
    public function open($mode)
    {
        throw new Exception('cannot open directory');
    }

    /**
     * Search this directory (recursively) for the given text.
     * Searching is not implemented at this time.
     */
    public function search($text)
    {
        // TODO not yet implemented
    }

    /**
     * Set access permissions for this directory. Access
     * permissions are not implemented at this time.
     */
    public function setPermissions($permissions)
    {
        // TODO not yet implemented
    }

    /**
     * Remove the entry with the given name from this directory.
     * If the file's link count drops to zero, it is deleted.
     *
     * @param string $name  file name
     */
    public function unlink($name)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT file_id FROM file_refs WHERE name = ? AND parent_id = ?');
        $stmt->execute(array($name, $this->id));
        $file_id = $stmt->fetchColumn();

        if ($file_id) {
            $stmt = $db->prepare('DELETE FROM file_refs WHERE file_id = ? AND parent_id = ?');
            $stmt->execute(array($file_id, $this->id));

            // count links and delete storage if link count == 0
            $stmt = $db->prepare('SELECT COUNT(id) FROM file_refs WHERE file_id = ?');
            $stmt->execute(array($file_id));
            $count = $stmt->fetchColumn();

            if ($count == 0) {
                $file = File::get($file_id);
                $file->delete();
            }
        }
    }

    /**
     * Updating a directory is not allowed (and not needed).
     */
    public function update()
    {
        throw new Exception('cannot update directory');
    }
}

/**
 * Class to represent (virtual) root directory.
 * Root directories are not represented in the database.
 */
class RootDirectory extends StudipDirectory
{
    /**
     * Initialize a new root directory object for the given id.
     *
     * @param string $id  context id
     *
     * @return RootDirectory  directory object
     */
    public function __construct($id)
    {
        parent::__construct($id);

        // default is to use DiskFileStorage
        $this->storage = 'DiskFileStorage';
    }

    /**
     * Delete the contents of this directory (recursively).
     * The root directory itself cannot (and need not) be deleted.
     */
    public function delete()
    {
        foreach ($this->listFiles() as $entry) {
            $entry->getFile()->delete();
        }
    }
}

/**
 * Class to represent file and directory entries inside a directory.
 * This should probably use SimpleORMap.
 */
class DirectoryEntry // extends SimpleORMap
{
    public $id;
    public $file_id;
    public $parent_id;
    public $name;
    public $description;
    public $downloads;

    /**
     * Initialize a new directory entry object for the given id.
     *
     * @param string $id  directory entry id
     *
     * @return DirectoryEntry  DirectoryEntry object
     */
    public function __construct($id)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('SELECT * FROM file_refs WHERE id = ?');
        $stmt->execute(array($id));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new IllegalArgumentException('directory entry not found');
        }

        $this->id = $id;
        $this->file_id = $result['file_id'];
        $this->parent_id = $result['parent_id'];
        $this->name = $result['name'];
        $this->description = $result['description'];
        $this->downloads = $result['downloads'];
    }

    /**
     * Return the description for the entry.
     *
     * @return string  description text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Return the directory of the entry.
     *
     * @return Directory  directory object
     */
    public function getDirectory()
    {
        return File::get($this->parent_id);
    }

    /**
     * Return the download count of the entry.
     *
     * @return int  download count
     */
    public function getDownloadCount()
    {
        return $this->downloads;
    }

    /**
     * Return the file of the entry.
     *
     * @return File  file object
     */
    public function getFile()
    {
        return File::get($this->file_id);
    }

    /**
     * Return the name of the entry.
     *
     * @return string  file name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Rename the file inside the same directory.
     *
     * @param string  new file name
     */
    public function rename($name)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE file_refs SET name = ? WHERE id = ?');
        $stmt->execute(array($name, $this->id));

        $this->name = $name;
    }

    /**
     * Set the description for the entry.
     *
     * @param string $text  description text
     */
    public function setDescription($text)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE file_refs SET description = ? WHERE id = ?');
        $stmt->execute(array($text, $this->id));

        $this->description = $text;
    }

    /**
     * Set the download count of the entry.
     *
     * @param int $count  download count
     */
    public function setDownloadCount($count)
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE file_refs SET downloads = ? WHERE id = ?');
        $stmt->execute(array($count, $this->id));

        $this->downloads = $count;
    }

    /**
     * Set the position of the entry in the directory.
     *
     * @param int $position  position
     */
    public function setPosition($position)
    {
        // TODO not implemented yet (do we really need this?)
    }
}

/**
 * Common interface for all backends to store files. At the
 * moment, there is only one backend: DiskFileStorage.
 */
interface FileStorage
{
    /**
     * Delete this file from disk.
     */
    public function delete();

    /**
     * Check whether the file exists on disk.
     *
     * @return boolean  TRUE or FALSE
     */
    public function exists();

    /**
     * Return the file creation time.
     *
     * @return int  timestamp
     */
    public function getCreationTime();

    /**
     * Return the backend id of this file.
     *
     * @return string  backend id
     */
    public function getId();

    /**
     * Return the file's mime type, if known.
     *
     * @return string  mime type (NULL if unknown)
     */
    public function getMimeType();

    /**
     * Return the file modification time.
     *
     * @return int  timestamp
     */
    public function getModificationTime();

    /**
     * Return the file's size in bytes.
     *
     * @return int  file size
     */
    public function getSize();

    /**
     * Check if this backend allows reading of files.
     *
     * @return boolean  TRUE
     */
    public function isReadable();

    /**
     * Check if this backend allows writing of files.
     *
     * @return boolean  TRUE
     */
    public function isWritable();

    /**
     * Open a PHP stream resource for this file.
     * Access mode parameter works just like fopen.
     *
     * @param string $mode  access mode (see fopen)
     *
     * @return resource  file handle
     */
    public function open($mode);
}

/**
 * Class to represent files on disk (the only supported backend for now).
 */
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
            $this->file_path = get_upload_file_path($storage_id);

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
}
