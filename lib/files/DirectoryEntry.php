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

class DirectoryEntry extends SimpleORMap
{
    /**
     * Initialize a new directory entry object for the given id.
     *
     * @param string $id  directory entry id
     *
     * @return DirectoryEntry  DirectoryEntry object
     */
    public function __construct($id = NULL)
    {
        $this->db_table = 'file_refs';
        
        $this->additional_fields['file'] = array('get' => function ($record, $field) {
            return File::get($record->file_id);
        });
        $this->additional_fields['direcory'] = array('get' => function ($record, $field) {
            return File::get($record->parent_id);
        });

        $this->notification_map['before_create'] = 'FileWillCreate';
        $this->notification_map['after_create']  = 'FileDidCreate';
        $this->notification_map['before_update'] = 'FileWillChange';
        $this->notification_map['after_update']  = 'FileDidChange';
        $this->notification_map['before_delete'] = 'FileWillDelete';
        $this->notification_map['after_delete']  = 'FileDidDelete';

        parent::__construct($id);
        
        if ($id !== null && $this->isNew()) {
            throw new InvalidArgumentException('directory entry not found');
        }
    }

    /**
     * Set the new parent_id.
     *
     * @param String $parent_id Directory id of the new parent
     * @todo  Prevent impossible situations (move a folder inside itself)
     */
    public function move($parent_id)
    {
        $entries = DirectoryEntry::findByFile_id($this->file_id);
        if(count($entries) > 0) {
            $entries[0]->parent_id = $parent_id;
            $entries[0]->store();
        }
    }

    /**
    * Returns the Parent from an Entry.
    *
    * @return DirectoryEntry Parent entry
    * @throws Exception if no valid parent is found
    */
    public function getParent()
    {
        $entries = DirectoryEntry::findByFile_id($this->parent_id);
        if (count($entries) === 0) {
            throw new Exception('No parent found');
        }
        return $entries[0];
    }
}
