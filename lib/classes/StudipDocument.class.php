<?php
# Lifter007: TODO
# Lifter003: TEST
/**
* StudipDocument.class.php
*
*
*
*
* @author   Andr� Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @access   public
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2005 Andr� Noack <noack@data-quest>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

require_once 'lib/classes/SimpleORMap.class.php';

/**
 * Small class derived from SimpleORMap to give access to the table dokumente
 * @author Andr� Noack
 *
 */
class StudipDocument extends SimpleORMap {

    /**
     * returns new instance of StudipDocument for given id or null if id does 
     * not exist
     * @param id string primary key of table 'dokumente' in db
     * @return object of type StudipDocument that matches id or null if nothing matches id
     */
    static function find($id)
    {
        return SimpleORMap::find(__CLASS__,$id);
    }

    /**
     * returns array of instances of StudipDocument filtered by given sql-where-clause
     * @param string: sql-where clause to use on the right side of WHERE to 
     * filter all StudipDocuments in an array
     * @return array of StudipDocument filtered by where (sql-qhere-clause) or 
     * empty array if no matches were found
     */
    static function findBySql($where)
    {
        return SimpleORMap::findBySql(__CLASS__,$where);
    }

    /**
     * returns array of StudipDocument-objects of given course id
     * @param string cid: course_id in the db (Seminar_id) with which all 
     * StudipDocuments should be filtered
     * @return array of all StudipDocument from the course with the given course_id
     */
    static function findByCourseId($cid)
    {
        return self::findBySql("seminar_id = " . DBManager::get()->quote($cid));
    }

    /**
     * returns array of document-objects of given folder with id folder_id
     * @param string folder_id: id of a folder whose documents we want to catch
     * @return array of StudipDocument objects of the given folder_id's folder 
     * or empty if that folder contains no documents.
     */
    static function findByFolderId($folder_id)
    {
         return self::findBySql("folder_id = " . DBManager::get()->quote($folder_id));
    }

    /**
     * deletes table rows which matches the given sql-where clause and returns 
     * the number of deleted rows. 
     * @param string sql clause to use on the right side of WHERE to delete 
     * all rows matching this clause
     * @return int: number of rows deleted by the given sql-where-clause. 
     */
    static function deleteBySql($where)
    {
        return SimpleORMap::deleteBySql(__CLASS__, $where);
    }

    /**
     * constructor
     * @param string id: primary key of table dokumente
     * @return null
     */
    function __construct($id = null)
    {
        $this->db_table = 'dokumente';
        parent::__construct($id);
    }
}
