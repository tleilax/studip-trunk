<?php
/**
 *  DataFieldStructure.class.php
 *
 * @author   Martin Gieseking <mgieseki@uos.de>
 * @author   Marcus Lunzenauer <mlunzena@uos.de>
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license  GPL2 or any later version
 */

class DataFieldStructure
{
    protected static $permission_masks = array(
        'user'   => 1,
        'autor'  => 2,
        'tutor'  => 4,
        'dozent' => 8,
        'admin'  => 16,
        'root'   => 32,
        'self'   => 64,
    );

    private $data;
    private $numEntries = null;

    public function __construct(array $data = array())
    {
        $this->data = $data ?: array();

        # we always need a valid unique ID
        if (!$this->data['datafield_id']) {
            $this->data['datafield_id'] = md5(uniqid('fdhdgg'));
        }
    }

    public function getID()
    {
        return $this->data['datafield_id'];
    }

    public function getName()
    {
        return $this->data['name'];
    }

    public function getType()
    {
        return $this->data['type'];
    }

    public function getTypeParam()
    {
        return $this->data['typeparam'];
    }

    public function getObjectClass()
    {
        return $this->data['object_class'];
    }

    public function getObjectType()
    {
        return $this->data['object_type'];
    }

    public function getPriority()
    {
        return $this->data['priority'];
    }

    public function getEditPerms()
    {
        return $this->data['edit_perms'];
    }

    public function getViewPerms()
    {
        return $this->data['view_perms'];
    }

    public function getSelfPerms()
    {
        return $this->data['self_perms'];
    }

    public function getIsRequired()
    {
        return (bool)$this->data['is_required'];
    }

    public function getIsUserfilter()
    {
        return (bool)$this->data['is_userfilter'];
    }

    public function getDescription()
    {
        return $this->data['description'];
    }

    public function setID($v)
    {
        $this->data['datafield_id'] = $v;
    }

    public function setName($v)
    {
        $this->data['name'] = $v;
    }

    public function setType($v)
    {
        $this->data['type'] = $v;
        if (!in_array($v, words('selectbox selectboxmultiple radio combo'))) {
            $this->setTypeParam('');
        }
    }

    public function setTypeParam($v)
    {
        $this->data['typeparam'] = $v;
    }

    public function setObjectClass($v)
    {
        $this->data['object_class'] = $v;
    }

    public function setObjectType($v)
    {
        $this->data['object_type'] = $v;
    }

    public function setPriority($v)
    {
        $this->data['priority'] = $v;
    }

    public function setEditPerms($v)
    {
        $this->data['edit_perms'] = $v;
    }

    public function setViewPerms($v)
    {
        $this->data['view_perms'] = $v;
    }

    public function setIsRequired($v)
    {
        $this->data['is_required'] = $v;
    }

    public function setIsUserfilter($v)
    {
        $this->data['is_userfilter'] = $v;
    }

    public function setDescription($v)
    {
        $this->data['description'] = $v;
    }

    public function getCachedNumEntries()
    {
        if (is_null($this->numEntries)) {
            $this->numEntries = $this->numberOfUsedEntries();
        }
        return $this->numEntries;
    }

    /**
     * Returns the count of entries for this datafield.
     *
     * @return integer  the count of entries for this datafield
     */
    public function numberOfUsedEntries()
    {
        $this->numEntries = DatafieldEntryModel::countBySQL('datafield_id = ?', array($this->getID()));
        return $this->numEntries;
    }

    /**
     * Returns a list of all datatype classes with an id as key and a name as
     * value.
     *
     * @return array() list of all datatype classes
     */
    public static function getDataClass()
    {
        return array(
            'sem'          => _('Veranstaltungen'),
            'inst'         => _('Einrichtungen'),
            'user'         => _('Benutzer'),
            'userinstrole' => _('Benutzerrollen in Einrichtungen'),
            'usersemdata'  => _('Benutzer-Zusatzangaben in VA'),
            'roleinstdata' => _('Rollen in Einrichtungen')
        );
    }

    /**
     * Return the mask for the given permission
     *
     * @param    string     the name of the permission
     * @return integer  the mask for the permission
     * @static
     */
    public static function permMask($perm)
    {
        return self::$permission_masks[$perm];
    }

    /**
     * liefert String zu gegebener user_class-Maske
     *
     * @param    integer    the user class mask
     * @return string       a string consisting of a comma separated list of
     *                      permissions
     * @static
     */
    public function getReadableUserClass($class)
    {
        $result = array();
        foreach (self::$permission_masks as $perm => $mask) {
            if ($class & $mask) {
                $result[] = $perm;
            }
        }
        return implode(', ', $result);
    }


    /**
     * Returns a collection of structures of datafields filtered by objectType,
     * objectClass and unassigned objectClasses.
     *
     * @param    type           <description>
     * @param    type           <description>
     * @param    boolean    <description>
     * @return array        <description>
     * @static
     */
    public function getDataFieldStructures($objectType = null, $objectClass = '', $includeNullClass = false)
    {
        $expr = $params = array();
        if (isset($objectType)) {
            $expr[] = "object_type = :object_type";
            $params[':object_type'] = $objectType;
        }

        if ($objectClass) {
            $expr[] = "(object_class & :object_class" .
                        ($includeNullClass ? ' OR object_class IS NULL)' : ')');
            $params[':object_class'] = $objectClass;
        }

        $expr = empty($expr) ? '' : 'WHERE ' . join(' AND ', $expr);

        $query = "SELECT *
                  FROM datafields
                  {$expr}
                  ORDER BY priority, name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute($params);

        $ret = array();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $ret[$row['datafield_id']] = new self($row);
        }

        return $ret;
    }


    # load structure information from database
    public function load()
    {
        if ($this->getID()) {
            $this->data = Datafield::find($this->getID())->toArray();
        }
    }

    public function store()
    {
        if (!in_array($this->data['type'], words('selectbox selectboxmultiple radio combo'))) {
            $this->data['typeparam'] = '';
        }

        $entry = Datafield::find($this->getId());
        $entry->name          = $this->data['name'];
        $entry->object_type   = $this->data['object_type'];
        $entry->object_class  = (int)$this->data['object_class'] ?: null;
        $entry->edit_perms    = $this->data['edit_perms'];
        $entry->priority      = (int)$this->data['priority'];
        $entry->view_perms    = $this->data['view_perms'];
        $entry->type          = (string)$this->data['type'];
        $entry->typeparam     = (string)$this->data['typeparam'];
        $entry->is_required   = (bool)$this->data['is_required'];
        $entry->is_userfilter = (bool)$this->data['is_userfilter'];
        $entry->description   = (string)$this->data['description'];        

        return $entry->store();
    }


    public function remove($id = '')
    {
        if (!$id) {
            $id = $this->getID();
        }
        return Datafield::find($id)->delete();
    }


    public function accessAllowed($perm, $watcher = '', $user = '')
    {
        # everybody may see the information
        if ($this->getViewPerms() === 'all') {
            return true;
        }

        # permission is sufficient
        if ($perm->have_perm($this->getViewPerms())) {
            return true;
        }

        # user may see his own data
        if ($watcher && $user && $user === $watcher) {
            return true;
        }

        # nothing matched...
        return false;
    }

    public function editAllowed($userPerms)
    {
        if (!$this->getEditPerms()) {
            $this->load();
        }

        $user_perms     = self::permMask($userPerms);
        $required_perms = self::permMask($this->getEditPerms());

        return $user_perms >= $required_perms;
    }
}
