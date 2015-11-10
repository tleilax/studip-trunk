<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/*
* DataFieldEntry.class.php - <short-description>
*
* Copyright (C) 2005 - Martin Gieseking  <mgieseki@uos.de>
* Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of
* the License, or (at your option) any later version.
*/

/**
 * Enter description here...
 *
 */
abstract class DataFieldEntry
{
    protected $html_template = '%1$s';

    public $value;
    public $structure;
    public $rangeID;

    /**
     * Enter description here...
     *
     * @param unknown_type $structure
     * @param unknown_type $rangeID
     * @param unknown_type $value
     */
    public function __construct($structure = null, $rangeID = '', $value = null)
    {
        $this->structure = $structure;
        $this->rangeID   = $rangeID;
        $this->value     = $value;
    }

    public function getDescription()
    {
        return $this->structure->getDescription();
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $range_id
     * @param unknown_type $object_type
     * @param unknown_type $object_class_hint
     * @return unknown
     */
    public static function getDataFieldEntries($range_id, $object_type = '', $object_class_hint = '')
    {
        if(! $range_id)
            return false; // we necessarily need a range ID

        $parameters = array();
        if(is_array($range_id)) {
            // rangeID may be an array ("classic" rangeID and second rangeID used for user roles)
            $secRangeID = $range_id[1];
            $rangeID = $range_id[0]; // to keep compatible with following code
            if('usersemdata' !== $object_type && 'roleinstdata' !== $object_type) {
                $object_type = 'userinstrole';
            }
            $clause1 = "AND sec_range_id= :sec_range_id";
            $parameters[':sec_range_id'] = $secRangeID;
        } else {
            $rangeID = $range_id;
        }
        if (!$object_type) {
            $object_type = get_object_type($rangeID);
        }

        if($object_type) {
            switch ($object_type) {
                case 'sem':
                    if($object_class_hint) {
                        $object_class = SeminarCategories::GetByTypeId($object_class_hint);
                    } else {
                        $object_class = SeminarCategories::GetBySeminarId($rangeID);
                    }
                    $clause2 = "object_class = :object_class OR object_class IS NULL";
                    $parameters[':object_class'] = (int) $object_class;
                    break;
                case 'inst':
                case 'fak':
                    if($object_class_hint) {
                        $object_class = $object_class_hint;
                    } else {
                        $query = "SELECT type FROM Institute WHERE Institut_id = ?";
                        $statement = DBManager::get()->prepare($query);
                        $statement->execute(array($rangeID));
                        $object_class = $statement->fetchColumn();
                    }
                    $object_type = "inst";
                    $clause2 = "object_class = :object_class OR object_class IS NULL";
                    $parameters[':object_class'] = (int) $object_class;
                    break;
                case 'roleinstdata': //hmm tja, vermutlich so
                    $clause2 = '1';
                    break;
                case 'user':
                case 'userinstrole':
                case 'usersemdata':
                    $object_class = is_object($GLOBALS['perm']) ? DataFieldStructure::permMask($GLOBALS['perm']->get_perm($rangeID)) : 0;
                    $clause2 = "((object_class & :object_class) OR object_class IS NULL)";
                    $parameters[':object_class'] = (int) $object_class;
                    break;
            }
            $query = "SELECT a.*, content
                      FROM datafields AS a
                      LEFT JOIN datafields_entries AS b
                        ON (a.datafield_id = b.datafield_id AND range_id = :range_id {$clause1})
                      WHERE object_type = :object_type AND ({$clause2})
                      ORDER BY priority";
            $parameters[':range_id']    = $rangeID;
            $parameters[':object_type'] = $object_type;

            $rs = DBManager::get()->prepare($query);
            $rs->execute($parameters);

            $entries = array();
            while($data = $rs->fetch(PDO::FETCH_ASSOC)) {
                $struct = new DataFieldStructure($data);
                $entries[$data['datafield_id']] = DataFieldEntry::createDataFieldEntry($struct, $range_id, $data['content']);
            }
        }
        return $entries;
    }

    // @static
    //hmm wird das irgendwo gebraucht (und wenn ja wozu)?
    /*
    public static function getDataFieldEntriesBySecondRangeID ($secRangeID) {
        $db = new DB_Seminar;
        $query  = "SELECT *, a.datafield_id AS id ";
        $query .= "FROM datafields a JOIN datafields_entries b ON a.datafield_id=b.datafield_id ";
        $query .= "AND sec_range_id = '$secRangeID'";
        $db->query($query);
        while ($db->next_record()) {
            $data = array('datafield_id' => $db->f('id'), 'name' => $db->f('name'), 'type' => $db->f('type'),
            'typeparam' => $db->f('typeparam'), 'object_type' => $db->f('object_type'), 'object_class' => $db->f('object_class'),
            'edit_perms' => $db->f('edit_perms'), 'priority' => $db->f('priority'), 'view_perms' => $db->f('view_perms'));
            $struct = new DataFieldStructure($data);
            $entry = DataFieldEntry::createDataFieldEntry($struct, array($db->f('range_id'), $secRangeID), $db->f('content'));
            $entries[$db->f("id")] = $entry;
        }
        return $entries;
    }
    */

    /**
     * Enter description here...
     *
     * @return unknown
     */
    function store()
    {
        $st = DBManager::get()->prepare("SELECT content FROM datafields_entries "
            . "WHERE datafield_id = ? AND range_id = ? AND sec_range_id = ?");
        $ok = $st->execute(array($this->structure->getID(), (string)$this->getRangeID() , (string)$this->getSecondRangeID()));
        if ($ok) {
            $old_value = $st->fetchColumn();
        }

        $query = "INSERT INTO datafields_entries (content, datafield_id, range_id, sec_range_id, mkdate, chdate)
                     VALUES (?,?,?,?,UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                     ON DUPLICATE KEY UPDATE content=?, chdate=UNIX_TIMESTAMP()";
        $st = DBManager::get()->prepare($query);
        $ret = $st->execute(array($this->getValue() , $this->structure->getID() , $this->getRangeID() , $this->getSecondRangeID() , $this->getValue()));

        if ($ret) {
            NotificationCenter::postNotification('DatafieldDidUpdate', $this, array('changed' => $st->rowCount(), 'old_value' => $old_value));
        }

        return $ret;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $range_id
     * @return unknown
     */
    public static function removeAll($range_id)
    {
        if(is_array($range_id))
        {
            list ($rangeID, $secRangeID) = $range_id;
        }
        else
        {
            $rangeID = $range_id;
            $secRangeID = "";
        }
        if($rangeID && ! $secRangeID)
        {
            $where = "range_id = ?";
            $param = array($rangeID);
        }
        if($rangeID && $secRangeID)
        {
            $where = "range_id = ? AND sec_range_id = ?";
            $param = array($rangeID , $secRangeID);
        }
        if(! $rangeID && $secRangeID)
        {
            $where = "sec_range_id = ?";
            $param = array($secRangeID);
        }
        if($where)
        {
            $st = DBManager::get()->prepare("DELETE FROM datafields_entries WHERE $where");
            $ret = $st->execute($param);
            return $ret;
        }
    }

    /**
     * Enter description here...
     *
     * @return array() of supported types
     */
    public static function getSupportedTypes()
    {
        return array("bool" , "textline" , "textarea" , "selectbox" , "selectboxmultiple", "date" , "time" , "email" , "phone" , "radio" , "combo" , "link");
    }

    /**
     * "statische" Methode: liefert neues Datenfeldobjekt zu gegebenem Typ
     *
     * @param unknown_type $structure
     * @param unknown_type $rangeID
     * @param unknown_type $value
     * @return unknown
     */
    public static function createDataFieldEntry($structure, $rangeID = '', $value = '')
    {
        if(! is_object($structure))
            return false;
        $type = $structure->getType();
        if(in_array($type, DataFieldEntry::getSupportedTypes()))
        {
            $entry_class = 'DataField' . ucfirst($type) . 'Entry';
            return new $entry_class($structure, $rangeID, $value);
        }
        else
        {
            return false;
        }
    }

    /**
     * Enter description here...
     *
     * @return string type of entry
     */
    public function getType()
    {
        $class = strtolower(get_class($this));
        return substr($class, 9, strpos($class, 'entry') - 9);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $entities
     * @return unknown
     */
    public function getDisplayValue($entities = true)
    {
        if($entities)
            return htmlReady($this->getValue());
        return $this->getValue();
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Enter description here...
     *
     * @return string name
     */
    public function getName()
    {
        return $this->structure->getName();
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getId()
    {
        return $this->structure->getID();
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $name
     * @return unknown
     */
    function getHTML($name = '', $variables = array())
    {
        $variables = array_merge(array(
            'name'      => $name,
            'structure' => $this->structure,
            'value'     => $this->value,
        ), $variables);

        return $GLOBALS['template_factory']->render('datafields/' . $this->template, $variables);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $v
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $submitted_value
     */
    public function setValueFromSubmit($submitted_value)
    {
        $this->setValue($submitted_value);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $v
     */
    public function setRangeID($v)
    {
        $this->rangeID = $v;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $v
     */
    public function setSecondRangeID($v)
    {
        $this->rangeID = array(is_array($this->rangeID) ? $this->rangeID[0] : $this->rangeID , $v);
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    public function isValid()
    {
        if(!trim($this->getValue()) && $this->structure->getIsRequired())
           return false;
        else
           return true;
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    function numberOfHTMLFields()
    {
        return 1;
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    function getRangeID()
    {
        if(is_array($this->rangeID))
        {
            list ($rangeID, ) = $this->rangeID;
        }
        else
        {
            $rangeID = $this->rangeID;
        }
        return $rangeID;
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    function getSecondRangeID()
    {
        if(is_array($this->rangeID))
        {
            list (, $secRangeID) = $this->rangeID;
        }
        else
        {
            $secRangeID = "";
        }
        return $secRangeID;
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    function isVisible()
    {
        $users_own_range = ($this->getRangeID() == $GLOBALS['user']->id ? $GLOBALS['user']->id : '');
        return $this->structure->accessAllowed($GLOBALS['perm'], $GLOBALS['user']->id, $users_own_range);
    }

    /**
     * Enter description here...
     *
     * @return boolean
     */
    function isEditable()
    {
        return $this->structure->editAllowed($GLOBALS['perm']->get_perm());
    }
}
