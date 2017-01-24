<?php
/**
 * MvvPerm.php
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class MvvPerm {

    /**
     * Permission to read the value of the designated field.
     */
    const PERM_READ = 1;

    /**
     * Permission to read and write the value of the designated field.
     */
    const PERM_WRITE = 3;

    /**
     * Permission to read, write/create and delete the value
     * of the dsignated field.
     */
    const PERM_CREATE = 7;

    private static $privileges;

    private static $roles;

    private static $user_role_institutes;

    private $mvv_object;

    private $variant;

    public function __construct(ModuleManagementModel $mvv_object)
    {
        $this->mvv_object = $mvv_object;
    }

    public static function get($mvv_object)
    {
        static $perm_objects;

        if (is_object($mvv_object)) {
            $id = $mvv_object->getId();
            $index = get_class($mvv_object) . (is_array($id) ? join('', $id) : $id);
        } else {
            $index = $mvv_object;
        }
        if (!$perm_objects[$index]) {
            if (!is_object($mvv_object)) {
                $mvv_class_name = (string) $mvv_object;
                $mvv_object = new $mvv_class_name;
            }

            if (!$mvv_object instanceof ModuleManagementModel) {
                throw new InvalidArgumentException('Wrong object type. Only MVV '
                        . 'objects of type ModuleManagementModel are allowed.');
            }

            $perm_objects[$index] = new MvvPerm($mvv_object);
        }
        return $perm_objects[$index];
    }

    public static function __callStatic($name, $arguments)
    {
        $name = mb_strtolower($name);

        if (mb_strpos($name, 'haveperm') === 0) {
            $perm = 'PERM_' . mb_strtoupper(mb_substr($name, 8));
            if (defined('self::' . $perm)) {
                return self::get($arguments[0])->havePerm(constant('self::' . $perm),
                        $arguments[1], $arguments[2], $arguments[3]);
            } else {
                throw new InvalidArgumentException('Undefined Permission.');
            }
        }

        if (mb_strpos($name, 'getfieldperm') === 0) {
            $field = mb_strtolower(mb_substr($name, 12));
            return self::get($arguments[0])->getFieldPerm($field,
                    $arguments[1], $arguments[2]);
        }

        if (mb_strpos($name, 'havefieldperm') === 0) {
            $field = mb_strtolower(mb_substr($name, 13));
            return self::get($arguments[0])->haveFieldPerm($field, $arguments[1],
                    $arguments[2], $arguments[3]);
        }

        throw new BadMethodCallException('Method '
                . get_called_class() . "::$name not found");
    }

    public function __call($name, $arguments)
    {
        $name = mb_strtolower($name);

        if (mb_strpos($name, 'haveperm') === 0) {
            $perm = 'PERM_' . mb_strtoupper(mb_substr($name, 8));
            if (defined('self::' . $perm)) {
                return $this->havePerm(constant('self::' . $perm),
                        $arguments[1], $arguments[2], $arguments[3]);
            } else {
                throw new InvalidArgumentException('Undefined Permission.');
            }
        }

        if (mb_strpos($name, 'getfieldperm') === 0) {
            $field = mb_strtolower(mb_substr($name, 12));
            return $this->getFieldPerm($field,
                    $arguments[1], $arguments[2]);
        }

        if (mb_strpos($name, 'havefieldperm') === 0) {
            $field = mb_strtolower(mb_substr($name, 13));
            return $this->haveFieldPerm($field, $arguments[0],
                    $arguments[1], $arguments[2]);
        }

        throw new BadMethodCallException('Method '
                . get_called_class() . "::$name not found");
    }

    public function setVariant($variant)
    {
        $this->variant = $variant;
        return $this;
    }

    /**
     * Retrieves the permission for the table or object.
     * Returns the (max) perm the given or current user has globally.
     * If the institute is set, checks for the perm in the context of this institute.
     *
     * @param object|string $object An mvv object or its class name.
     * @param string $status Constrain the permission to this status of the object.
     * @param string $user_id The ID of the user. If not set the ID of the
     * current user.
     * @param string|array $institut_id The ID(s) of institute(s)
     * if the user role is defined for institutes.
     * @param string $variant The optional variant of the object table (e.g. to
     * support different languages)
     * @return int The perm defined in class MVVPlugin.
     * @throws InvalidArgumentException If the object is not of type
     * ApplicationSimpleOrMap.
     */
    public final static function getPerm($object, $status = null,
            $user_id = null, $institut_id = null, $variant = null)
    {
        if (is_object($object)) {
            $mvv_object = $object;
        } else {
            $mvv_object = new $object;
        }
        if (!$mvv_object instanceof ModuleManagementModel) {
            throw new InvalidArgumentException('Wrong object type. Only MVV objects of type ModuleManagementModel are allowed.');
        }
        $user_id = is_null($user_id) ? $GLOBALS['user']->id : $user_id;
        if ($GLOBALS['perm']->get_perm($user_id) == 'root' ) {
            return self::PERM_CREATE;
        }
        if (is_null($institut_id)) {
            $inst_ids = array();
            foreach ($mvv_object->getResponsibleInstitutes() as $inst) {
                if ($inst) {
                    $inst_ids[] = $inst->getId();
                    // the user have permission if he is assigned to the faculty
                    $inst_ids[] = $inst->fakultaets_id;
                }
            }
        } else {
            $inst_ids = array();
            foreach (Institute::findMany((array) $institut_id) as $inst) {
                $inst_ids[] = $inst->getId();
                // the user have permission if he is assigned to the faculty
                $inst_ids[] = $inst->fakultaets_id;
            }
        }
        $table_meta = $mvv_object->getTableMetadata();
        $institut_ids = array_unique($inst_ids);
        $status = $status ?: $mvv_object->getStatus();
        $roles = self::getRoles($user_id);
        $variant = $variant ? '_' . $variant : '';
        $mvv_table = $table_meta['table'] . $variant;
        self::getPrivileges($mvv_table);
        $perm = 0;
        foreach ($roles as $role) {
            if ($role->rolename === 'MVVAdmin') {
                return self::PERM_CREATE;
            }
            if (count($institut_ids)) {
                $institutes_assigned_role = RolePersistence::getAssignedRoleInstitutes($user_id, $role->roleid);
                // count($institutes_assigned_role) === 1 means global role...
                if (count($institutes_assigned_role) === 1
                        || count(array_intersect($institut_ids, $institutes_assigned_role))) {
                    if (!$status) {
                        $priv = intval(self::$privileges[$mvv_table]['default_table'][$role->rolename]);
                    } else {
                        $priv = intval(self::$privileges[$mvv_table]['table'][$status][$role->rolename]);
                    }
                } else {
                    $priv = 0;
                }
            } else {
                if (!$status) {
                    $priv = intval(self::$privileges[$mvv_table]['default_table'][$role->rolename]);
                } else {
                    $priv = intval(self::$privileges[$mvv_table]['table'][$status][$role->rolename]);
                }
            }

            if ($priv > $perm) {
                $perm = $priv;
            }
        }
        return $perm;
    }

    public final function havePerm($perm, $status = null,
            $user_id = null, $institut_id = null)
    {
        $user_perm = self::getPerm($this->mvv_object, $status, $user_id,
                $institut_id, $this->variant);
        return ($user_perm >= $perm);
    }

    public function haveObjectPerm($perm, $user_id = null)
    {
        $institute_ids = SimpleORMapCollection::createFromArray(
                $this->mvv_object->getResponsibleInstitutes())->pluck('id');
        return $this->havePerm($perm, $this->mvv_object->getStatus(), $user_id,
                $institute_ids, $this->variant);
    }

    public function haveDfEntryPerm($df_entry, $perm)
    {
        $field = 'datafields';
        $df_id = reset($df_entry->getId());
        $field_perm = $this->getFieldPerm(array($field, $df_id));
        return $field_perm >= $perm;
    }

    /**
     * Retrieves the permission for the table or object.
     * Returns the (max) perm the given or current user have globally.
     * If the institute is set, checks for the perm in the context of this institute.
     *
     * @param string|array Name of field or an array with name of field and
     * related entry (usefull in the case of relations to datafields).
     * @param string $user_id The ID of the user. If not set the ID of the
     * current user.
     * @param string $institut_id The ID of an institute
     * if the user role is defined for institutes.
     * @return int The perm defined in class MVVPlugin.
     */
    public final function getFieldPerm($field, $user_id = null, $institut_id = null)
    {
        $user_id = is_null($user_id) ? $GLOBALS['user']->id : $user_id;
        if ($GLOBALS['perm']->get_perm($user_id) == 'root' ) {
            return self::PERM_CREATE;
        }
        $roles = self::getRoles($user_id);
        $table_meta = $this->mvv_object->getTableMetadata();
        $mvv_table = $table_meta['table']
                . ($this->variant ? '_' . $this->variant : '');
        if (is_null($institut_id)) {
            $institut_ids = array_map(function ($ins) {
                return $ins ? $ins->getId() : null;
            }, $this->mvv_object->getResponsibleInstitutes());
        } else {
            $institut_ids = is_array($institut_id) ? $institut_id : array($institut_id);
        }
        $status = $this->mvv_object->getStatus();
        self::getPrivileges($mvv_table);

        $perm = 0;
        foreach ($roles as $role) {
            if ($role->rolename === 'MVVAdmin') {
                return self::PERM_CREATE;
            }
            if (count($institut_ids)) {
                $institutes_assigned_role = RolePersistence::getAssignedRoleInstitutes($user_id, $role->roleid);
                // count($institutes_assigned_role) === 1 means global role...
                if (count($institutes_assigned_role) === 1
                        || count(array_intersect($institut_ids, $institutes_assigned_role))) {
                    $priv = is_array($field)
                            ? self::$privileges[$mvv_table]['fields'][$field[0]][$field[1]][$status][$role->rolename]
                            : self::$privileges[$mvv_table]['fields'][$field][$status][$role->rolename];
                    if (is_null($priv)) {
                        $priv = is_array($field)
                                ? self::$privileges[$mvv_table]['fields'][$field[0]]['default'][$status][$role->rolename]
                                : self::$privileges[$mvv_table]['fields'][$field]['default'][$role->rolename];
                    }
                    if (is_null($priv)) {
                        $priv = self::$privileges[$mvv_table]['default_fields'][$role->rolename];
                    }
                } else {
                    $priv = 0;
                }
            } else {
                $priv = is_array($field)
                        ? self::$privileges[$mvv_table]['fields'][$field[0]][$field[1]][$status][$role->rolename]
                        : self::$privileges[$mvv_table]['fields'][$field][$status][$role->rolename];
                if (is_null($priv)) {
                    $priv = is_array($field)
                            ? self::$privileges[$mvv_table]['fields'][$field[0]]['default'][$status][$role->rolename]
                            : self::$privileges[$mvv_table]['fields'][$field]['default'][$role->rolename];
                }
                if (is_null($priv)) {
                    $priv = self::$privileges[$mvv_table]['default_fields'][$role->rolename];
                }
            }
            $priv = intval($priv);
            if ($priv > $perm) {
                $perm = $priv;
            }
        }
        return $perm;
    }

    public final function haveFieldPerm($field, $perm = null, $user_id = null, $institut_id = null)
    {
        $perm = intval($perm) ?: MvvPerm::PERM_WRITE;
        $user_perm = $this->getFieldPerm($field, $user_id, $institut_id);
        return ($user_perm >= $perm);
    }

    public function disable($field, $perm = null, $user_id = null, $institut_id = null)
    {
        $perm = intval($perm) ?: MvvPerm::PERM_WRITE;
        return $this->haveFieldPerm($field, $perm, $user_id, $institut_id)
                ? '' : ' readonly ';
    }

    public static function getRoles($user_id)
    {
        if (!self::$roles[$user_id]) {
            self::$roles[$user_id] = array_uintersect(
                    RolePersistence::getAssignedPluginRoles(
                        PluginEngine::getPlugin('MVVPlugin')->getPluginId()),
                    RolePersistence::getAssignedRoles($user_id),
                    function ($a, $b) {
                        return ($a->roleid === $b->roleid ? 0 : (
                                $a->roleid < $b->roleid ? -1 : 1));
                    }
                );
        }
        return self::$roles[$user_id];
    }

    /**
     * Retrieves all privileges from config files.
     */
    private static function getPrivileges($mvv_table)
    {
        if (self::$privileges === null) {
            $cache = StudipCacheFactory::getCache();
            self::$privileges = unserialize($cache->read('MvvPlugin/privileges'));
        }

        if (self::$privileges[$mvv_table] === null) {
            $plugin_info = PluginManager::getInstance()->getPluginInfo('MvvPlugin');
            $config_dir = realpath($GLOBALS['PLUGINS_PATH'] . '/'
                    . $plugin_info['path'] . '/config/');
            if ($config_dir) {
                $config_file = $config_dir . '/' . $mvv_table . '.php';
                if (filetype($config_file) === 'file') {
                    include $config_file;
                    self::$privileges[$mvv_table] = $privileges;
                }
                $cache = StudipCacheFactory::getCache();
                $cache->write('MvvPlugin/privileges', serialize(self::$privileges));
            }
        }
        return isset(self::$privileges[$mvv_table]);
    }

    public static function refreshPrivileges($mvv_table){
        self::$privileges[$mvv_table] = null;
        return self::getPrivileges($mvv_table);
    }

    /**
     * Returns all ids of institutes the user is assigned with at least one role.
     * Have the user at least one role globally an empty array is returned.
     * If the user has no role at any institute false is returned.
     *
     * @param string $user_id Optional. The actual user if not set.
     * @param array $mvv_roles Optional. An array of roles. All MvvPlugin-Roles if not set.
     * @return array An array of institute ids or an empty array.
     */
    public static function getOwnInstitutes($user_id = null, $mvv_roles = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        if (is_null($mvv_roles)) {
            $roles = self::getRoles($user_id);
        } else {
            $roles = array();
            foreach (self::getRoles($user_id) as $role) {
                if (in_array($role->rolename, (array) $mvv_roles)) {
                    $roles[] = $role;
                }
            }
        }

        if (self::$user_role_institutes[$user_id] === null) {
            $institutes = array();
            foreach ($roles as $role) {
                if ($role->rolename === 'MVVAdmin' || $GLOBALS['perm']->have_perm('root')) {
                    $institutes = array();
                    break;
                }
                $institutes_assigned_role = RolePersistence::getAssignedRoleInstitutes($user_id, $role->roleid);
                if (count($institutes_assigned_role) === 1) {
                    // this role is globally defined for this user
                    $institutes = array();
                    break;
                }
                $institutes = array_merge((array) $institutes, array_filter(
                    $institutes_assigned_role, function ($inst) {
                        return $inst != '';
                    }));
            }

            if (count($institutes)) {
                $stmt = DBManager::get()->prepare('SELECT DISTINCT(Institut_id) '
                        . 'FROM Institute WHERE Institut_id IN (:inst_ids) '
                        . 'OR fakultaets_id IN (:inst_ids)');
                $stmt->execute(array('inst_ids' => $institutes));
                self::$user_role_institutes[$user_id] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                self::$user_role_institutes[$user_id] = array();
            }
        }
        return self::$user_role_institutes[$user_id];
    }

}
