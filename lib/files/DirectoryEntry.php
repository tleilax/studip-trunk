<?php
/**
 * File.php
 *
 * Class to represent file and directory entries inside a directory.
 * This should probably use SimpleORMap.
 *
 * Copyright (c) 2013  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class DirectoryEntry // extends SimpleORMap
{
    public $id;
    public $file_id;
    public $parent_id;
    public $name;        //public $title;
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
            throw new InvalidArgumentException('directory entry not found');
        }

        $this->id = $id;
        $this->file_id = $result['file_id'];
        $this->parent_id = $result['parent_id'];
        $this->name = $result['name'];               //$this->title = $result['title'];
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

    /**
     * Set the new parent_id.
     *
     * @param String $parent_id Directory id of the new parent
     *
    */
    public function move($parent_id)
    {
        $db = DBManager::get();
        $stmt = $db->prepare('UPDATE file_refs SET parent_id = :newParent_id WHERE file_id = :file_id');
        $stmt->execute(array('newParent_id' => $parent_id, 'file_id' => $this->file_id));
    }

    /**
    * Returns the Parent from an Entry.
    *
    *
    */
    public function getParent()
    {
        $db = DBManager::get();
        $stmt = $db->prepare('SELECT id FROM file_refs WHERE file_id = ?');
        $stmt->execute(array($this->parent_id));
        $result = $stmt->fetchColumn();

        if (!$result) {
            throw new Exception('No parent found');
        }
        return new DirectoryEntry($result);
    }
}
