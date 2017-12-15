<?php
# Lifter010: TODO
/**
 * ObjectConfig.class.php
 * provides access to object preferences
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class ObjectConfig extends Config
{
    /**
     * cache of created ObjectConfig instances
     * @var array
     */
    private static $instances;

    /**
     * range_id
     * @var string
     */
    private $range_id;

    /**
     * range type ('user' or 'course')
     * @var string
     */
    protected $range_type;

    /**
     * returns cached instance for given range_id
     * creates new objects if needed
     * @param string $range_id
     * @return ObjectConfig
     */
    public static function get()
    {
        $range_id = func_get_arg(0);
        if (self::$instances[$range_id] === null) {
            $config = new static($range_id);
            self::$instances[$range_id] = $config;
        }
        return self::$instances[$range_id];
    }

    /**
     * set cached instance for given range_id
     * use for testing or to unset cached instance by passing
     * null as second param
     * @param string $range_id
     * @param ObjectConfig $my_instance
     */
    public static function set()
    {
        list ($range_id, $my_instance) = func_get_args();
        self::$instances[$range_id] = $my_instance;
    }

    /**
     * passing null as first param is for compatibility and
     * should be considered deprecated.
     * passing data array as second param only for testing
     * @param string $range_id
     * @param array $data
     */
    function __construct($range_id = null, $data = null)
    {
        $this->range_id = $range_id;
        if ($range_id !== null || $data !== null) {
            $this->fetchData($data);
        }
    }

    /**
     * @see lib/classes/Config::fetchData()
     */
    protected function fetchData($data = null)
    {
        if ($data !== null) {
            $this->data = $data;
        } else {
            $this->data = array();
            foreach(Config::get()->getFields($this->range_type) as $field) {
                $this->data[$field] = Config::get()->$field;
                $metadata[$field] = Config::get()->getMetadata($field);
            }
            $db = DbManager::get();
            $rs = $db->query("SELECT field, value FROM config_values WHERE range_id = " . $db->quote($this->range_id));
            while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
                switch ($metadata[$row['field']]['type']) {
                    case 'integer':
                        $value = (int)$row['value'];
                        break;
                    case 'boolean':
                        $value = (bool)$row['value'];
                        break;
                    case 'array':
                        $value = (array)json_decode($row['value'], true);
                        break;
                    default:
                        $value = $row['value'];
                }
                $this->data[$row['field']] = $value ;
            }
        }
    }

    /**
     * returns the range id
     *
     * @return string
     */
    function getRangeId()
    {
        return $this->range_id;
    }

    /** old style usage with $range_id, $key as params
     * still works but is deprecated
     *
     * @see lib/classes/Config::getValue()
     */
    function getValue($field)
    {
        $args = func_get_args();
        if(count($args) > 1) {
            list($range_id, $key) = $args;
            if($range_id !== null && $key !== null) {
                $ret = self::get($range_id)->$key;
            }
            if($range_id === null) {
                $ret = parent::getValue($key);
            }
            trigger_error('deprecated use of ' . __METHOD__, E_USER_NOTICE);
            return $ret;
        }
        if (array_key_exists($field, $this->data)) {
            return $this->data[$field];
        }
        return null;
    }

    /** old style usage with $value, $range_id, $key as params
     * still works but is deprecated
     *
     * @see lib/classes/Config::setValue()
     */
    function setValue($field, $value)
    {
        $args = func_get_args();
        if(count($args) > 2) {
            list($value, $range_id, $key) = $args;
            if($range_id !== null && $key !== null) {
                $ret = self::get($range_id)->store($key, $value);
            }
            if($range_id === null && $key !== null) {
                $ret = $this->store($key, $value);
            }
            trigger_error('deprecated use of ' . __METHOD__, E_USER_NOTICE);
            return $ret;
        }
        return parent::setValue($field, $value);
    }

    /**
     * old style usage with $range_id, $key as params
     * still works but is deprecated
     * @param string $field
     * @return bool
     */
    function unsetValue($field)
    {
        $args = func_get_args();
        if(count($args) > 1) {
            list($range_id, $key) = $args;
            if($range_id !== null && $key !== null) {
                $ret = self::get($range_id)->delete($key);
            }
            if($range_id === null) {
                $ret = $this->delete($key);
            }
            trigger_error('deprecated use of ' . __METHOD__, E_USER_NOTICE);
            return $ret;
        }
        return $this->delete($field);
    }

    /**
     * @see lib/classes/Config::store()
     */
    function store($field, $value)
    {
        $entry = new ConfigValue(array($field, $this->range_id));
        $this->data[$field] = $value;

        // Check if entry is default and if so, delete it
        if (Config::get()->getValue($field) == $value) {
            $entry->delete();
            return 1;
        }

        // Otherwise convert it to an appropriate format and store it
        $metadata = Config::get()->getMetadata($field);
        switch ($metadata['type']) {
            case 'integer':
            case 'boolean':
                $value = (int)$value;
            break;
            case 'array' :
                $value = json_encode($value);
            break;
            default:
                $value = (string)$value;
        }
        $entry->value = $value;
        $ret = $entry->store();
        if ($ret) {
            $this->fetchData();
        }
        return $ret;

    }

    /**
     * @see lib/classes/Config::delete()
     */
    function delete($field)
    {
        $entry = ConfigValue::find(array($field, $this->range_id));
        if($entry !== null) {
            if($ret = $entry->delete()) {
                $this->data[$field] = Config::get()->$field;
            }
            return $ret;
        } else {
            return null;
        }
    }

}
