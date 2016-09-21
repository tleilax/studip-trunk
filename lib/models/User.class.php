<?php
/**
 * User.class.php
 * model class for combined auth_user_md5/user_info record
 * this class represents one user, the attributes from tables
 * auth_user_md5 and user_info were merged.
 *
 * @code
 * $a_user = User::find($id);
 * $another_users_email = User::findByUsername($username)->email;
 * $a_user->email = $another_users_email;
 * $a_user->store();
 * @endcode
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Andr� Noack <noack@data-quest.de>
 * @copyright   2011 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string user_id database column
 * @property string id alias column for user_id
 * @property string username database column
 * @property string password database column
 * @property string perms database column
 * @property string vorname database column
 * @property string nachname database column
 * @property string email database column
 * @property string validation_key database column
 * @property string auth_plugin database column
 * @property string locked database column
 * @property string lock_comment database column
 * @property string locked_by database column
 * @property string visible database column
 * @property string hobby computed column read/write
 * @property string lebenslauf computed column read/write
 * @property string publi computed column read/write
 * @property string schwerp computed column read/write
 * @property string home computed column read/write
 * @property string privatnr computed column read/write
 * @property string privatcell computed column read/write
 * @property string privadr computed column read/write
 * @property string score computed column read/write
 * @property string geschlecht computed column read/write
 * @property string mkdate computed column read/write
 * @property string chdate computed column read/write
 * @property string title_front computed column read/write
 * @property string title_rear computed column read/write
 * @property string preferred_language computed column read/write
 * @property string smsforward_copy computed column read/write
 * @property string smsforward_rec computed column read/write
 * @property string guestbook computed column read/write
 * @property string email_forward computed column read/write
 * @property string smiley_favorite computed column read/write
 * @property string motto computed column read/write
 * @property string lock_rule computed column read/write
 * @property SimpleORMapCollection course_memberships has_many CourseMember
 * @property SimpleORMapCollection institute_memberships has_many InstituteMember
 * @property SimpleORMapCollection admission_applications has_many AdmissionApplication
 * @property SimpleORMapCollection archived_course_memberships has_many ArchivedCourseMember
 * @property SimpleORMapCollection datafields has_many DatafieldEntryModel
 * @property SimpleORMapCollection studycourses has_many UserStudyCourse
 * @property SimpleORMapCollection contacts has_many Contact
 * @property UserInfo   info   has_one UserInfo
 * @property UserOnline online has_one UserOnline
 */
class User extends AuthUserMd5
{
    /**
     * Returns the currently authenticated user.
     *
     * @return User User
     */
    public static function findCurrent()
    {
        if (is_object($GLOBALS['user'])) {
            return $GLOBALS['user']->getAuthenticatedUser();
        }
    }

    /**
     * Returns user object including user_info
     *
     * @return User User
     */
    public static function findFull($id)
    {
        $sql = "SELECT * FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id = ?";
        $data = DbManager::get()->fetchOne($sql, array($id));
        if ($data) {
            $user = new User();
            $user->info = new UserInfo();
            $user->setData($data);
            $user->setNew(false);
            foreach (array_keys($user->db_fields) as $field) {
                $user->content_db[$field] = $user->content[$field];
            }
            $user->info = UserInfo::buildExisting($data);
            return $user;
        }
    }

    /**
     * return user object for given username
     *
     * @param string $username a username
     * @return User
     */
    public static function findByUsername($username)
    {
        $found = parent::findByUsername($username);
        return isset($found[0]) ? $found[0] : null;
    }

    /**
     * returns an array of User-objects that have the given value in the
     * given datafield.
     * @param string $datafield_id
     * @param array of User
     */
    public static function findByDatafield($datafield_id, $value)
    {
        $search = DBManager::get()->prepare(
            "SELECT range_id " .
            "FROM datafields_entries " .
            "WHERE datafield_id = :datafield_id " .
                "AND content = :value " .
        "");
        $search->execute(compact("datafield_id", "value"));
        $users = array();
        foreach ($search->fetchAll(PDO::FETCH_COLUMN, 0) as $user_id) {
            $users[] = new User($user_id);
        }
        return $users;
    }

    public static function findDozentenByTermin_id($termin_id)
    {
        $record = new User();
        $db = DBManager::get();
        $sql = "
            SELECT `" .  $record->db_table . "`.*
            FROM `" .  $record->db_table . "`
                INNER JOIN termin_related_persons USING (user_id)
            WHERE termin_related_persons.range_id = ?
            ORDER BY Nachname, Vorname ASC
        ";
        $st = $db->prepare($sql);
        $st->execute(array($termin_id));
        $ret = array();
        $c = 0;
        while($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $ret[$c] = new User();
            $ret[$c]->setData($row, true);
            $ret[$c]->setNew(false);
            ++$c;
        }
        return $ret;
    }

    /**
     * Returns all available auth_plugins
     * @return array
     */
    public static function getAvailableAuthPlugins()
    {
        $query = "SELECT DISTINCT IFNULL(auth_plugin, 'standard') as auth_plugin FROM auth_user_md5 ORDER BY auth_plugin='standard',auth_plugin";
        return DBManager::get()->query($query)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Wraps a search parameter in %..% if the parameter itself does not
     * contain % or _.
     *
     * @param String $needle Search parameter
     * @return String containing the wrapped needle if neccessary
     */
    private static function searchParam($needle)
    {
        if (preg_match('/[%_]/S', $needle)) {
            return $needle;
        }

        return '%' . $needle . '%';
    }

    /**
     * Temporary migrate to User.class.php
     *
     * @param $attributes
     * @return array
     */
    public static function search($attributes)
    {
        $params = [];

        $query = "SELECT au.* "
                 ."FROM auth_user_md5 au "
                 ."LEFT JOIN datafields_entries de ON de.range_id=au.user_id "
                 ."LEFT JOIN user_online uo ON au.user_id = uo.user_id "
                 ."LEFT JOIN user_info ui ON (au.user_id = ui.user_id) "
                 ."LEFT JOIN user_userdomains uud ON (au.user_id = uud.user_id) "
                 ."LEFT JOIN userdomains uds USING (userdomain_id) "
                 ."LEFT JOIN user_studiengang us ON us.user_id = au.user_id "
                 ."LEFT JOIN user_inst uis ON uis.user_id = au.user_id "
                 ."WHERE 1 ";
        if ($attributes['username']) {
            $query .= "AND au.username like :username ";
            $params[':username'] = self::searchParam($attributes['username']);
        }

        if ($attributes['vorname']) {
            $query .= "AND au.vorname like :vorname ";
            $params[':vorname'] = self::searchParam($attributes['vorname']);
        }

        if ($attributes['nachname']) {
            $query .= "AND au.nachname like :nachname ";
            $params[':nachname'] = self::searchParam($attributes['nachname']);
        }

        if ($attributes['email']) {
            $query .= "AND au.Email like :email ";
            $params[':email'] = self::searchParam($attributes['email']);
        }

        //permissions
        if (!is_null($attributes['perms']) && $attributes['perms'] != 'alle') {
            $query .= "AND au.perms =  :perms ";
            $params[':perms'] = $attributes['perms'];
        }

        //locked user
        if ((int)$attributes['locked'] == 1) {
            $query .= "AND au.locked = 1 ";
        }

        //inactivity
        if (!is_null($attributes['inaktiv']) && $attributes['inaktiv'][0] != 'nie') {
            $comp = in_array(trim($attributes['inaktiv'][0]), ['=', '>', '<=']) ? $attributes['inaktiv'][0] : '=';
            $days = (int)$attributes['  inaktiv'][1];
            $query .= "AND uo.last_lifesign {$comp} UNIX_TIMESTAMP(TIMESTAMPADD(DAY, -{$days}, NOW())) ";
        } elseif (!is_null($attributes['inaktiv'])) {
            $query .= "AND uo.last_lifesign IS NULL ";
        }

        //datafields
        if (!is_null($attributes['datafields']) && count($attributes['datafields']) > 0) {
            foreach ($attributes['datafields'] as $id => $entry) {
                $query .= "AND de.datafield_id = :df_id_". $id ." AND de.content = :df_content_". $id ." ";
                $params[':df_id_' . $id] = $id;
                $params[':df_content_' . $id] = $entry;
            }
        }

        if ($attributes['auth_plugins']) {
            $query .= "AND IFNULL(auth_plugin, 'preliminary') = :auth_plugins ";
            $params[':auth_plugins'] = $attributes['auth_plugins'];
        }

        if ($attributes['userdomains']) {
            if ($attributes['userdomains'] === 'null-domain') {
                $query .= "AND userdomain_id IS NULL ";
            } else {
                $query .= "AND userdomain_id = :userdomains ";
                $params[':userdomains'] = $attributes['userdomains'];
            }
        }

        if($attributes['degree']) {
            $query .= "AND us.abschluss_id = :degree ";
            $params[':degree'] = $attributes['degree'];
        }

        if($attributes['studycourse']) {
            $query .= "AND us.fach_id = :studycourse ";
            $params[':studycourse'] = $attributes['studycourse'];
        }

        if($attributes['institute']) {
            $query .= "AND uis.Institut_id = :institute ";
            $params[':institute'] = $attributes['institute'];
        }

        $query .= " GROUP BY au.user_id ";
        //sortieren
        switch ($attributes['sort']) {
            case "perms":
                $query .= "ORDER BY au.perms {$attributes['order']}, au.username";
                break;
            case "Vorname":
                $query .= "ORDER BY au.Vorname {$attributes['order']}, au.Nachname";
                break;
            case "Nachname":
                $query .= "ORDER BY au.Nachname {$attributes['order']}, au.Vorname";
                break;
            case "Email":
                $query .= "ORDER BY au.Email {$attributes['order']}, au.username";
                break;
            case "changed":
                $query .= "ORDER BY uo.last_lifesign {$attributes['order']}, au.username";
                break;
            case "mkdate":
                $query .= "ORDER BY ui.mkdate {$attributes['order']}, au.username";
                break;
            case "auth_plugin":
                $query .= "ORDER BY auth_plugin {$attributes['order']}, au.username";
                break;
            default:
                $query .= " ORDER BY au.username {$attributes['order']}";
        }

        return DBManager::get()->fetchAll($query, $params, __CLASS__ . '::buildExisting');
    }


    /**
     *
     */
    protected static function configure($config = array())
    {
        $config['has_many']['course_memberships'] = array(
            'class_name' => 'CourseMember',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['has_many']['institute_memberships'] = array(
            'class_name' => 'InstituteMember',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['has_many']['admission_applications'] = array(
            'class_name' => 'AdmissionApplication',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['has_many']['archived_course_memberships'] = array(
            'class_name' => 'ArchivedCourseMember',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['has_many']['datafields'] = array(
            'class_name' => 'DatafieldEntryModel',
            'assoc_foreign_key' =>
                function($model, $params) {
                    $model->setValue('range_id', $params[0]->id);
                },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
                function($user) {
                    return array($user);
                }
        );
        $config['has_many']['studycourses'] = array(
            'class_name' => 'UserStudyCourse',
            'assoc_func' => 'findByUser',
            'on_delete' => 'delete',
            'on_store' => 'store',
        );
        $config['has_and_belongs_to_many']['contacts'] = array(
            'class_name' => 'User',
            'thru_table' => 'contact',
            'thru_key' => 'owner_id',
            'thru_assoc_key' => 'user_id',
            'order_by' => 'ORDER BY Nachname, Vorname',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_many']['contactgroups'] = array(
            'class_name' => 'Statusgruppen',
            'assoc_foreign_key' => 'range_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_one']['info'] = array(
            'class_name' => 'UserInfo',
            'assoc_func' => 'find',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_one']['online'] = array(
            'class_name' => 'UserOnline',
            'assoc_func' => 'find',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );

        $info = new UserInfo();
        $info_meta = $info->getTableMetadata();
        foreach ( $info_meta ['fields'] as $field => $meta ) {
            if ($field !== $info_meta ['pk'] [0]) {
                $config ['additional_fields'] [$field] = array (
                        'get' => '_getAdditionalValueFromRelation',
                        'set' => '_setAdditionalValueFromRelation',
                        'relation' => 'info',
                        'relation_field' => $field
                );
            }
        }

        $config['notification_map']['after_create'] = 'UserDidCreate';
        $config['notification_map']['after_store'] = 'UserDidUpdate';
        $config['notification_map']['after_delete'] = 'UserDidDelete';
        $config['notification_map']['before_create'] = 'UserWillCreate';
        $config['notification_map']['before_store'] = 'UserWillUpdate';
        $config['notification_map']['before_delete'] = 'UserWillDelete';
        parent::configure($config);
    }

    /**
     * @see SimpleORMap::store()
     */
    function store()
    {
        if ($this->isDirty() && !$this->info->isFieldDirty('chdate')) {
            $this->info->setValue('chdate', time());
        }
        return parent::store();
    }

    /**
     * @see SimpleORMap::triggerChdate()
     */
    function triggerChdate()
    {
       return $this->info->triggerChdate();
    }

    /**
     * returns the name in specified format
     * (formats defined in $GLOBALS['_fullname_sql'])
     *
     * @param string one of full,full_rev,no_title,no_title_rev,no_title_short,no_title_motto,full_rev_username
     * @return string guess what - the fullname
     */
    function getFullName($format = "full")
    {
        static $concat,$left,$if,$quote;

        $sql = $GLOBALS['_fullname_sql'][$format];
        if (!$sql || $format == 'no_title') {
            return $this->vorname . ' ' . $this->nachname;
        }
        if ($format == 'no_title_rev') {
            return $this->nachname . ', ' . $this->vorname;
        }
        if ($concat === null) {
            $concat = function() {return join('', func_get_args());};
            $left = function($str, $c = 0) {return mb_substr($str,0,$c);};
            $if = function($ok,$yes,$no) {return $ok ? $yes : $no;};
            $quote = function($str) {return "'" . addcslashes($str, "\\'\0") . "'";};
        }

        $data = array_map($quote, $this->toArray('vorname nachname username title_front title_rear motto perms'));
        $replace_func['CONCAT'] = '$concat';
        $replace_func['LEFT'] = '$left';
        $replace_func['UCASE'] = 'mb_strtoupper';
        $replace_func['IF'] = '$if';
        $eval = strtr($sql, $replace_func);
        $eval = strtr(mb_strtolower($eval), $data);
        return eval('return ' . $eval . ';');
    }

    function toArrayRecursive($only_these_fields = null)
    {
        $ret = parent::toArrayRecursive($only_these_fields);
        unset($ret['info']);
        return  $ret;
    }

    /**
     * Returns whether the user was assigned a certain role.
     *
     * @param string $role         The role to check
     * @param string $institute_id An optional institute_id
     * @return bool True if the user was assigned this role, false otherwise
     */
    public function hasRole($role, $institute_id = '')
    {
        return RolePersistence::isAssignedRole($this->user_id, $role, $institute_id);
    }

    /**
     * Returns the roles that were assigned to the user.
     *
     * @param boolean $with_implicit
     * @return array
     */
    public function getRoles($with_implicit = false)
    {
        return RolePersistence::getAssignedRoles($this->user_id, $withimplicit);
    }

    /**
     * Returns whether the given user is stored in contacts.
     *
     * @param User $another_user
     * @return bool
     */
    public function isFriendOf($another_user)
    {
        return (bool)DBManager::get()->fetchColumn("SELECT 1 FROM contact WHERE owner_id=? AND user_id=?", array($this->user_id, $another_user->user_id));
    }

    /**
     * checks if at least one field was modified since last restore
     *
     * @return boolean
     */
    public function isDirty()
    {
        return parent::isDirty() || $this->info->isDirty();
    }

    /**
     * checks if given field was modified since last restore
     *
     * @param string $field
     * @return boolean
     */
    public function isFieldDirty($field)
    {
        $field = mb_strtolower($field);
        return (array_key_exists($field, $this->content_db) ? parent::isFieldDirty($field) : $this->info->isFieldDirty($field));
    }

    /**
     * reverts value of given field to last restored value
     *
     * @param string $field
     * @return mixed the restored value
     */
    public function revertValue($field)
    {
        $field = mb_strtolower($field);
        return (array_key_exists($field, $this->content_db) ? parent::revertValue($field) : $this->info->revertValue($field));
    }

    /**
     * returns unmodified value of given field
     *
     * @param string $field
     * @throws InvalidArgumentException
     * @return mixed
     */
    public function getPristineValue($field)
    {
        $field = mb_strtolower($field);
        return (array_key_exists($field, $this->content_db) ? parent::getPristineValue($field) : $this->info->getPristineValue($field));
    }

    /**
     * Returns data of table row as assoc array with raw contents like
     * they are in the database.
     * Pass array of fieldnames or ws separated string to limit
     * fields.
     *
     * @param mixed $only_these_fields
     * @return array
     */
    public function toRawArray($only_these_fields = null)
    {
        return array_merge($this->info->toRawArray($only_these_fields), parent::toRawArray($only_these_fields));
    }

    /**
     * @param string $relation
     */
    public function initRelation($relation)
    {
        parent::initRelation($relation);
        if ($relation == 'info' && is_null($this->relations['info'])) {
            $options = $this->getRelationOptions($relation);
            $result = new $options['class_name'];
            $foreign_key_value = call_user_func($options['assoc_func_params_func'], $this);
            call_user_func($options['assoc_foreign_key_setter'], $result, $foreign_key_value);
            $this->relations[$relation] = $result;
        }
    }

    /**
     * This function returns the perms allowed for an institute for the current user
     *
     * @return array list of perms
     */
    public function getInstitutePerms()
    {
        if($this->perms === 'admin') {
            return ['admin'];
        }
        $allowed_status = [];
        $possible_status = words('autor tutor dozent');

        $pos = array_search($this->perms, $possible_status);
        if($pos) {
            $allowed_status = array_slice($possible_status, 0, $pos+1);
        }
        return $allowed_status;
    }

    /**
     * Builds an array containing all available elements that are part of a
     * user's homepage together with their visibility. It isn't sufficient to
     * just load the visibility settings from database, because if the user
     * has added some data (e.g. CV) but not yet assigned a special visibility
     * to that field, it wouldn't show up.
     *
     * @return array An array containing all available homepage elements
     * together with their visibility settings in the form
     * $name => $visibility.
     */
    public function getHomepageElements()
    {
        $homepage_visibility = get_local_visibility_by_id($this->id, 'homepage');
        if (is_array(json_decode($homepage_visibility, true))) {
            $homepage_visibility = json_decode($homepage_visibility, true);
        } else {
            $homepage_visibility = array();
        }

        // News
        $news = StudipNews::GetNewsByRange($this->id, true);

        // Non-private dates.
        if (Config::get()->CALENDAR_ENABLE) {
            $dates = CalendarEvent::countBySql('range_id = ?', [$this->id]);
        }

        // Votes
        if (Config::get()->VOTE_ENABLE) {
            $activeVotes  = Questionnaire::countBySQL("user_id = ? AND visible = '1'", [$this->id]);
            $stoppedVotes = Questionnaire::countBySQL("user_id = ? AND visible = '0'", [$this->id]);
        }
        // Evaluations
        $evalDB = new EvaluationDB();
        $activeEvals = $evalDB->getEvaluationIDs($this->id, EVAL_STATE_ACTIVE);
        // Literature
        $lit_list = StudipLitList::GetListsByRange($this->id);
        // Free datafields
        $data_fields = DataFieldEntry::getDataFieldEntries($this->id, 'user');
        $homepageplugins = [];

        // Now join all available elements with visibility settings.
        $homepage_elements = [];

        if (Avatar::getAvatar($this->id)->is_customized() && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['picture']) {
            $homepage_elements['picture'] = ['name'         => _('Eigenes Bild'),
                                              'visibility'  => $homepage_visibility['picture'] ?: get_default_homepage_visibility($this->id),
                                              'extern'      => true,
                                              'category'    => 'Allgemeine Daten'];
        }

        if ($this->info->motto && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['motto']) {
            $homepage_elements['motto'] = ['name'       => _('Motto'),
                                           'visibility' => $homepage_visibility['motto'] ?: get_default_homepage_visibility($this->id),
                                           'category'   => 'Private Daten'];
        }
        if (Config::get()->ENABLE_SKYPE_INFO) {
            if ($GLOBALS['user']->cfg->getValue('SKYPE_NAME') && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['skype_name']) {
                $homepage_elements['skype_name'] = ['name'              => _('Skype Name'),
                                                         'visibility'   => $homepage_visibility['skype_name'] ?: get_default_homepage_visibility($this->id),
                                                         'category'     => 'Private Daten'];
            }
        }
        if ($this->info->privatnr && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['Private Daten_phone']) {
            $homepage_elements['private_phone'] = ['name'       => _('Private Telefonnummer'),
                                                   'visibility' => $homepage_visibility['private_phone'] ?: get_default_homepage_visibility($this->id),
                                                   'category'   => 'Private Daten'];
        }
        if ($this->info->privatcell && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['private_cell']) {
            $homepage_elements['private_cell'] = ['name'        => _('Private Handynummer'),
                                                  'visibility'  => $homepage_visibility['private_cell'] ?: get_default_homepage_visibility($this->id),
                                                  'category'    => 'Private Daten'];
        }
        if ($this->info->privadr && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['privadr']) {
            $homepage_elements['privadr'] = ['name'         => _('Private Adresse'),
                                             'visibility'   => $homepage_visibility['privadr'] ?: get_default_homepage_visibility($this->id),
                                             'category'     => 'Private Daten'];
        }
        if ($this->info->home && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['homepage']) {
            $homepage_elements['homepage'] = ['name'        => _('Homepage-Adresse'),
                                              'visibility'  => $homepage_visibility['homepage'] ?: get_default_homepage_visibility($this->id),
                                              'extern'      => true,
                                              'category'    => 'Private Daten'];
        }
        if ($news && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['news']) {
            $homepage_elements['news'] = ['name'        => _('Ank�ndigungen'),
                                          'visibility'  => $homepage_visibility['news'] ?: get_default_homepage_visibility($this->id),
                                          'extern'      => true,
                                          'category'    => 'Allgemeine Daten'];
        }
        if (Config::get()->CALENDAR_ENABLE && $dates && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['dates']) {
            $homepage_elements['termine'] = ['name'        => _('Termine'),
                                             'visibility'  => $homepage_visibility['termine'] ?: get_default_homepage_visibility($this->id),
                                             'extern'      => true,
                                             'category'    => 'Allgemeine Daten'];
        }
        if (Config::get()->VOTE_ENABLE && ($activeVotes || $stoppedVotes || $activeEvals) && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['votes']) {
            $homepage_elements['votes'] = ['name'       => _('Frageb�gen'),
                                           'visibility' => $homepage_visibility['votes'] ?: get_default_homepage_visibility($this->id),
                                           'category'   => 'Allgemeine Daten'];
        }

        $query = "SELECT 1
                  FROM user_inst
                  LEFT JOIN Institute USING (Institut_id)
                  WHERE user_id = ? AND inst_perms = 'user'";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);
        if ($statement->fetchColumn() && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['studying']) {
            $homepage_elements['studying'] = ['name'        => _('Wo ich studiere'),
                                              'visibility'  => $homepage_visibility['studying'] ?: get_default_homepage_visibility($this->id),
                                              'category'    => 'Studien-/Einrichtungsdaten'];
        }
        if ($lit_list && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['literature']) {
            $homepage_elements["literature"] = ['name'          => _('Literaturlisten'),
                                                'visibility'    => $homepage_visibility['literature'] ?: get_default_homepage_visibility($this->id),
                                                'category'      => 'Allgemeine Daten'];
        }
        if ($this->info->lebenslauf && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['lebenslauf']) {
            $homepage_elements['lebenslauf'] = ['name'          => _('Lebenslauf'),
                                                'visibility'    => $homepage_visibility['lebenslauf'] ?: get_default_homepage_visibility($this->id),
                                                'extern'        => true,
                                                'category'      => 'Private Daten'];
        }
        if ($this->info->hobby && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['hobby']) {
            $homepage_elements['hobby'] = ['name'            => _('Hobbies'),
                                           'visibility'      => $homepage_visibility['hobby'] ?: get_default_homepage_visibility($this->id),
                                           'category'        => 'Private Daten'];
        }
        if ($this->info->publi && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['publi']) {
            $homepage_elements['publi'] = ['name'           => _('Publikationen'),
                                           'visibility'     => $homepage_visibility['publi'] ?: get_default_homepage_visibility($this->id),
                                           'extern'         => true,
                                           'category'       => 'Private Daten'];
        }
        if ($this->info->schwerp && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms]['schwerp']) {
            $homepage_elements['schwerp'] = ['name'         => _('Arbeitsschwerpunkte'),
                                             'visibility'   => $homepage_visibility['schwerp'] ?: get_default_homepage_visibility($this->id),
                                             'extern'       => true,
                                             'category'     => 'Private Daten'];
        }

        if ($data_fields) {
            foreach ($data_fields as $key => $field) {
                if ($field->getValue() && $field->isEditable($this->perms) && !$GLOBALS['NOT_HIDEABLE_FIELDS'][$this->perms][$key]) {
                    $homepage_elements[$key] = [
                        'name'       => $field->getName(),
                        'visibility' => $homepage_visibility[$key]
                            ?: get_default_homepage_visibility($this->id),
                        'extern'     => true,
                        'category'   => 'Zus�tzliche Datenfelder'
                    ];
                }
            }
        }

        $query = "SELECT kategorie_id, name
                  FROM kategorien
                  WHERE range_id = ?
                  ORDER BY priority";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);
        while ($category = $statement->fetch(PDO::FETCH_ASSOC)) {
            $homepage_elements['kat_'.$category['kategorie_id']] = ['name'          => $category['name'],
                                                                    'visibility'    => $homepage_visibility['kat_'.$category['kategorie_id']] ?: get_default_homepage_visibility($this->id),
                                                                    'extern'        => true,
                                                                    'category'      => 'Eigene Kategorien'];
        }

        if ($homepageplugins) {
            foreach ($homepageplugins as $plugin) {
                $homepage_elements['plugin_'.$plugin->getPluginId()] = ['name'          => $plugin->getPluginName(),
                                                                        'visibility'    => $homepage_visibility['plugin_'.$plugin->getPluginId()] ?: get_default_homepage_visibility($this->id),
                                                                        'category'      => 'Plugins'];
            }
        }
        return $homepage_elements;
    }


    /**
     * @param $user
     * @param $email
     * @param bool $force
     * @return bool
     */
    public function changeEmail($email, $force = false)
    {
        if ($this->email == $email && !$force) {
            return true;
        }

        if (!$GLOBALS['ALLOW_CHANGE_EMAIL']) {
            return false;
        }

        if (StudipAuthAbstract::CheckField('auth_user_md5.Email', $this->auth_plugin) || LockRules::check($this->user_id, 'email')) {
            return false;
        }

        $validator          = new email_validation_class; ## Klasse zum Ueberpruefen der Eingaben
        $validator->timeout = 10;
        $REMOTE_ADDR        = $_SERVER['REMOTE_ADDR'];
        $Zeit               = date('H:i:s, d.m.Y', time());

        // accept only registered domains if set
        $email_restriction = trim(Config::get()->EMAIL_DOMAIN_RESTRICTION);
        if (!$validator->ValidateEmailAddress($email, $email_restriction)) {
            if ($email_restriction) {
                $email_restriction_msg_part = '';
                $email_restriction_parts    = explode(',', $email_restriction);
                for ($email_restriction_count = 0; $email_restriction_count < count($email_restriction_parts); $email_restriction_count++) {
                    if ($email_restriction_count == count($email_restriction_parts) - 1) {
                        $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . '<br>';
                    } else if (($email_restriction_count + 1) % 3) {
                        $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ', ';
                    } else {
                        $email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ',<br>';
                    }
                }
                PageLayout::postError(sprintf(_('Die E-Mail-Adresse fehlt, ist falsch geschrieben oder geh�rt nicht zu folgenden Domains:%s'),
                    '<br>' . $email_restriction_msg_part));
            } else {
                PageLayout::postError(_('Die E-Mail-Adresse fehlt oder ist falsch geschrieben!'));
            }
            return false;
        }

        if (!$validator->ValidateEmailHost($email)) {     // Mailserver nicht erreichbar, ablehnen
            PageLayout::postError(_('Der Mailserver ist nicht erreichbar. Bitte �berpr�fen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken k�nnen!'));
            return false;
        } else {       // Server ereichbar
            if (!$validator->ValidateEmailBox($email)) {    // aber user unbekannt. Mail an abuse!
                StudipMail::sendAbuseMessage("edit_about", "Emailbox unbekannt\n\nUser: " . $this->username . "\nEmail: ".$email ."\n\nIP: " . $REMOTE_ADDR ." \nZeit: " . $Zeit . "\n");
                PageLayout::postError(_('Die angegebene E-Mail-Adresse ist nicht erreichbar. Bitte �berpr�fen Sie Ihre Angaben!'));
                return false;
            }
        }

        if (self::countBySql('email = ? AND user_id != ?', [$email, $this->user_id])) {
            PageLayout::postError(sprintf(_('Die angegebene E-Mail-Adresse wird bereits von einem anderen Benutzer (%s) verwendet. Bitte geben Sie eine andere E-Mail-Adresse an.'),
                htmlReady($this->getFullName())));
            return false;
        }

        if (StudipAuthAbstract::CheckField('auth_user_md5.validation_key', $this->auth_plugin)) {
            PageLayout::postSuccess(_('Ihre E-Mail-Adresse wurde ge�ndert!'));
        } else {
            // auth_plugin does not map validation_key (what if...?)

            // generate 10 char activation key
            $key = '';
            mt_srand((double)microtime() * 1000000);
            for ($i = 1; $i <= 10; $i++) {
                $temp = mt_rand() % 36;
                if ($temp < 10)
                    $temp += 48;   // 0 = chr(48), 9 = chr(57)
                else
                    $temp += 87;   // a = chr(97), z = chr(122)
                $key .= chr($temp);
            }
            $this->validation_key = $key;

            $activatation_url = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'activate_email.php?uid=' . $this->user_id . '&key=' . $this->validation_key;
            // include language-specific subject and mailbody with fallback to german
            $lang = $GLOBALS['_language_path']; // workaround
            if($lang == '') {
                $lang = 'de';
            }
            include_once("locale/$lang/LC_MAILS/change_self_mail.inc.php");

            $mail = StudipMail::sendMessage($email, $subject, $mailbody);

            if (!$mail) {
                return true;
            }

            $this->store();

            PageLayout::postInfo(sprintf(_('An Ihre neue E-Mail-Adresse <b>%s</b> wurde ein Aktivierungslink geschickt, dem Sie folgen m�ssen bevor Sie sich das n�chste mal einloggen k�nnen.'), $email));
            StudipLog::log("USER_NEWPWD", $this->user_id);
        }
        return true;
    }

    /**
     * Merge an user ($old_id) to another user ($new_id).  This is a part of the
     * old numit-plugin.
     *
     * @param string $old_user
     * @param string $new_user
     * @param boolean $identity merge identity (if true)
     *
     * @return array() messages to display after migration
     * @deprecated
     */
    public static function convert($old_id, $new_id, $identity = false)
    {
        NotificationCenter::postNotification('UserWillMigrate', $old_id, $new_id);

        $messages = array();

        //Identit�tsrelevante Daten migrieren
        if ($identity) {
            // Veranstaltungseintragungen
            self::removeDoubles('seminar_user', 'Seminar_id', $new_id, $old_id);
            $query = "UPDATE IGNORE seminar_user SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            self::removeDoubles('admission_seminar_user', 'seminar_id', $new_id, $old_id);
            $query = "UPDATE IGNORE admission_seminar_user SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Pers�nliche Infos
            $query = "DELETE FROM user_info WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id));

            $query = "UPDATE IGNORE user_info SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Studieng�nge
            self::removeDoubles('user_studiengang', 'fach_id', $new_id, $old_id);
            $query = "UPDATE IGNORE user_studiengang SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Eigene Kategorien
            $query = "UPDATE IGNORE kategorien SET range_id = ? WHERE range_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Institute
            self::removeDoubles('user_inst', 'Institut_id', $new_id, $old_id);
            $query = "UPDATE IGNORE user_inst SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Generische Datenfelder zusammenf�hren (bestehende Eintr�ge des
            // "neuen" Nutzers werden dabei nicht �berschrieben)
            $old_user = User::find($old_id);

            $query = "INSERT INTO datafields_entries
                        (datafield_id, range_id, sec_range_id, content, mkdate, chdate)
                      VALUES (:datafield_id, :range_id, :sec_range_id, :content,
                              UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                      ON DUPLICATE KEY
                        UPDATE content = IF(content IN ('', 'default_value'), VALUES(content), content),
                               chdate = UNIX_TIMESTAMP()";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':range_id', $new_id);

            $old_user->datafields->each(function ($field) use ($new_id, $statement) {
                $statement->bindValue(':datafield_id', $field->datafield_id);
                $statement->bindValue(':sec_range_id', $field->sec_range_id);
                $statement->bindValue(':content', $field->content);
                $statement->execute();
            });

            # Datenfelder des alten Nutzers leeren
            $old_user->datafields = array();
            $old_user->store();

            //Buddys
            $query = "UPDATE IGNORE contact SET owner_id = ? WHERE owner_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Avatar
            $old_avatar = Avatar::getAvatar($old_id);
            $new_avatar = Avatar::getAvatar($new_id);
            if ($old_avatar->is_customized()) {
                if (!$new_avatar->is_customized()) {
                    $avatar_file = $old_avatar->getFilename(AVATAR::ORIGINAL);
                    if (!file_exists($avatar_file)) {
                        $avatar_file = $old_avatar->getFilename(AVATAR::NORMAL);
                    }
                    $new_avatar->createFrom($avatar_file);
                }
                $old_avatar->reset();
            }

            $messages[] = _('Identit�tsrelevante Daten wurden migriert.');
        }

        // Restliche Daten �bertragen

        // ForumsModule migrieren
        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $plugin->migrateUser($old_id, $new_id);
        }

        // Dateieintragungen und Ordner
        // TODO (mlunzena) should post a notification
        $query = "UPDATE IGNORE dokumente SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE folder SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Kalender
        $query = "UPDATE IGNORE calendar_event SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE calendar_user SET owner_id = ? WHERE owner_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE calendar_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE event_data SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE event_data SET editor_id = ? WHERE editor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Archiv
        self::removeDoubles('archiv_user', 'seminar_id', $new_id, $old_id);
        $query = "UPDATE IGNORE archiv_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Evaluationen
        $query = "UPDATE IGNORE eval SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('eval_user', 'eval_id', $new_id, $old_id);
        $query = "UPDATE IGNORE eval_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE evalanswer_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Kategorien
        $query = "UPDATE IGNORE kategorien SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Literatur
        $query = "UPDATE IGNORE lit_catalog SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE lit_list SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Nachrichten (Interne)
        $query = "UPDATE IGNORE message SET autor_id = ? WHERE autor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('message_user', 'message_id', $new_id, $old_id);
        $query = "UPDATE IGNORE message_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // News
        $query = "UPDATE IGNORE news SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE news_range SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Informationsseiten
        $query = "UPDATE IGNORE scm SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Statusgruppeneintr�ge
        self::removeDoubles('statusgruppe_user', 'statusgruppe_id', $new_id, $old_id);
        $query = "UPDATE IGNORE statusgruppe_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Termine
        $query = "UPDATE IGNORE termine SET autor_id = ? WHERE autor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Votings
        $query = "UPDATE IGNORE vote SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE vote SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('vote_user', 'vote_id', $new_id, $old_id);
        $query = "UPDATE IGNORE vote_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('voteanswers_user', 'answer_id', $new_id, $old_id);
        $query = "UPDATE IGNORE voteanswers_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Wiki
        $query = "UPDATE IGNORE wiki SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE wiki_locks SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Adressbucheintr�ge
        $query = "UPDATE IGNORE contact SET owner_id = ? WHERE owner_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Blubber
        $query = "UPDATE IGNORE blubber SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_follower SET studip_user_id = ? WHERE studip_user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_mentions SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_reshares SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_streams SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        NotificationCenter::postNotification('UserDidMigrate', $old_id, $new_id);

        $messages[] = _('Dateien, Termine, Adressbuch, Nachrichten und weitere Daten wurden migriert.');
        return $messages;
    }


    /**
     * Delete double entries of the old and new user. This is a part of the old
     * numit-plugin.
     *
     * @param string $table
     * @param string $field
     * @param md5 $new_id
     * @param md5 $old_id
     * @deprecated
     */
    private static function removeDoubles($table, $field, $new_id, $old_id)
    {
        $items = array();

        $query = "SELECT a.{$field} AS field_item
                  FROM {$table} AS a, {$table} AS b
                  WHERE a.user_id = ? AND b.user_id = ? AND a.{$field} = b.{$field}";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $value) {
            array_push($items, $value['field_item']);
        }

        if (!empty($items)) {
            $query = "DELETE FROM `{$table}`
                  WHERE user_id = :user_id AND `{$field}` IN (:items)";

            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $new_id);
            $statement->bindValue(':items', $items, StudipPDO::PARAM_ARRAY);
            $statement->execute();
        }
    }
}
