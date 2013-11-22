<?php
/**
 * StudipDirectory.php
 *
 * Class to represent directories in the database. Every
 * directory is also a file, so this a subclass of File.
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
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
