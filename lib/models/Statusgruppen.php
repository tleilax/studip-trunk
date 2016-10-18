<?php

/**
 * Statusgruppen.php
 * model class for statusgroups.
 * The statusgrouphierarchy is represented by the attributes
 * children and parent
 * 
 * Statusgroupmembers are saved as in <code>$this->members</code>
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string statusgruppe_id database column
 * @property string id alias column for statusgruppe_id
 * @property string name database column
 * @property string range_id database column
 * @property string position database column
 * @property string size database column
 * @property string selfassign database column
 * @property string selfassign_start database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string calendar_group database column
 * @property string name_w database column
 * @property string name_m database column
 * @property string children computed column
 * @property SimpleORMapCollection members has_many StatusgruppeUser
 * @property Statusgruppen parent belongs_to Statusgruppen
 */
class Statusgruppen extends SimpleORMap
{
    public $keep_children = false;

    protected static function configure($config = array())
    {
        $config['db_table'] = 'statusgruppen';
        $config['has_many']['members'] = array(
            'class_name'        => 'StatusgruppeUser',
            'assoc_foreign_key' => 'statusgruppe_id',
            'on_delete'         => 'delete',
            'order_by'          => 'ORDER BY position ASC',
        );
        $config['has_and_belongs_to_many']['dates'] = array(
            'class_name' => 'CourseDate',
            'thru_table' => 'termin_related_groups',
            'order_by'   => 'ORDER BY date',
            'on_delete'  => 'delete', // TODO: This might cause trouble
            'on_store'   => 'store'
        );
        $config['belongs_to']['parent'] = array(
            'class_name'  => 'Statusgruppen',
            'foreign_key' => 'range_id',
        );
        $config['additional_fields']['children'] = true;
        parent::configure($config);
    }

    public static function findAllByRangeId($range_id, $as_collection = false)
    {
        $groups = self::findBySQL('range_id IN (?)', [$range_id]);
        if (count($groups) > 0) {
            $ids = array_map(function ($group) { return $group->id; }, $groups);
            $groups = array_merge($groups, self::findAllByRangeId($ids, false));
        }

        return $as_collection
             ? SimpleCollection::createFromArray($groups)
             : $groups;
    }

    public function getChildren()
    {
        $result = Statusgruppen::findBySQL('range_id = ? ORDER BY position', array($this->id));
        return $result ?: array();
    }

    public function getDatafields() 
    {
        return DataFieldEntry::getDataFieldEntries(array($this->range_id, $this->statusgruppe_id), 'roleinstdata');
    }

    public function setDatafields($data)
    {
        foreach ($this->getDatafields() as $field) {
            $field->setValueFromSubmit($data[$field->getId()]);
            $field->store();
        }
    }

    /**
     * Finds all statusgroups by a course id
     * 
     * @param string The course id
     * @return array Statusgroups
     */
    public static function findBySeminar_id($course_id)
    {
        return self::findByRange_id($course_id);
    }

    public static function findByTermin_id($termin_id)
    {
        return self::findBySQL('INNER JOIN termin_related_groups USING (statusgruppe_id) WHERE termin_id = ?', [$termin_id]);
    }

    public static function findContactGroups($user_id = null)
    {
        return self::findByRange_id($user_id ?: $GLOBALS['user']->id);
    }

    /**
     * Find all groups belonging to the given range_id that may be joined
     * by the given user.
     *
     * @param String $range_id range_id the groups shall belong to
     * @param String $user_id user to check
     * @return array
     */
    public static function findJoinableGroups($range_id, $user_id)
    {
        $groups = self::findByRange_id($range_id);
        return array_filter($groups, function ($g) use ($user_id) { return $g->userMayJoin($user_id); });
    }

    /**
     * Produces an array of all statusgroups a user is in
     * 
     * @param string $user_id The user_id
     * @param string $seperator The sign between the full paths
     * @param string $pre Preface of the outputted string (used for recursion)
     * @return array Stringarray of full gendered paths
     */
    public function getFullGenderedPaths($user_id, $seperator = " > ", $pre = "")
    {
        $result = array();
        $name = $pre
              ? $pre . $seperator . $this->getGenderedName($user_id)
              : $this->getGenderedName($user_id);
        if ($this->isMember($user_id)) {
            $result[] = $name;
        }
        if ($this->children) {
            foreach ($this->children as $child) {
                $result = array_merge($result, $child->getFullGenderedPaths($user_id, $seperator, $name));
            }
        }
        return $result;
    }

    /**
     * Produces string of all statusgroups a user is in (upwards from the
     * current group)
     * 
     * @param string $user_id The user_id
     * @param string $seperator The sign between the full paths
     * @return array String of full gendered paths separated by given separator
     */
    public function getFullGenderedName($user_id, $seperator = ' > ')
    {
        $result = [$this->getGenderedName($user_id)];

        $item = $this;
        while ($item = $item->parent) {
            array_unshift($result, $item->getGenderedName($user_id));
        }

        return implode($seperator, $result);
    }

    /**
     * Returns the gendered name of a statusgroup
     * 
     * @param string $user_id The user_id
     * @return string The gendered name
     */
    public function getGenderedName($user_id)
    {
        // We have to have at least 1 name gendered
        if ($this->name_m || $this->name_w) {
            $userinfo = new UserInfo($user_id);
            switch ($userinfo->geschlecht) {
                case UserInfo::GENDER_FEMALE:
                    return $this->name_w ?: $this->name;
                case UserInfo::GENDER_MALE:
                    return $this->name_m ?: $this->name;
            }
        }
        return $this->name;
    }

    /**
     * Puts out an array of all gendered userroles for a user in a certain
     * context
     * 
     * @param string $context The context
     * @param string $user The user id
     * @return array All roles
     */
    public static function getUserRoles($context, $user)
    {
        $roles = array();
        $groups = self::findByRange_id($context);
        foreach ($groups as $group) {
            $roles = array_merge($roles, $group->getFullGenderedPaths($user));
        }
        return $roles;
    }

    /**
     * Checks if a statusgroup has a folder.
     * 
     * @return boolean <b>true</> if the statusgroup has a folder, else 
     * <b>false</b>
     */
    public function hasFolder()
    {
        $query = "SELECT folder_id FROM folder WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->id));
        return (bool) $statement->fetchColumn();
    }

    /**
     * Gets the folder assigned to this statusgroup.
     *
     * @return DocumentFolder|null
     */
    public function getFolder()
    {
        return DocumentFolder::findOneByRange_id($this->id);
    }

    /**
     * Delete or create a folder
     * 
     * @param boolean $set <b>true</b> Create a folder
     * <b>false</b> Delete the folder
     */
    public function updateFolder($set)
    {
        if ($this->hasFolder() && !$set) {
            delete_folder($this->hasFolder(), true);
        }
        if (!$this->hasFolder() && $set) {
            create_folder(
                _('Dateiordner der Gruppe:') . ' ' . $this->name,
                _('Ablage f�r Ordner und Dokumente dieser Gruppe'),
                $this->id,
                15
            );
        }
    }

    /**
     * Finds CourseTopics assigned to this group via course dates.
     * @return array
     */
    public function findTopics()
    {
        $topics = array();
        foreach ($this->dates as $d) {
            foreach ($d->topics as $t) {
                // Assign topics with ID as key so we get unique entries.
                $topics[$t->id] = $t;
            }
        }
        return $topics;
    }

    /**
     * Finds Lecturers assigned to this group via course dates.
     * @return array
     */
    public function findLecturers()
    {
        $lecturers = array();
        foreach ($this->dates as $d) {
            foreach ($d->dozenten as $d) {
                // Assign topics with ID as key so we get unique entries.
                $lecturers[$d->id] = $d;
            }
        }
        return $lecturers;
    }

    /**
     * Checks if a user is a member of this group
     * 
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user is a member of this group
     */
    public function isMember($user_id = null)
    {
        if ($user_id == null) {
            $user_id = $GLOBALS['user']->id;
        }
        foreach ($this->members as $member) {
            if ($member->user_id == $user_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Displayfunction to show the places left in this group
     * 
     * @return string displaystring
     */
    public function getPlaces()
    {
        return $this->size ? "( " . min(count($this->members), $this->size) . " / {$this->size} )" : "";
    }

    /**
     * Remove all users of this group
     */
    public function removeAllUsers()
    {
        StatusgruppeUser::deleteBySQL('statusgruppe_id = ?', array($this->id));
    }

    /**
     * Remove one user from this group
     * 
     * @param string $user_id The user id
     * @param bool   $deep    Remove user from children as well?
     * @return bool
     */
    public function removeUser($user_id, $deep = false)
    {
        // Delete user from statusgruppe
        $member = StatusgruppeUser::find([$this->id, $user_id]);
        $result = $member !== null && $member->delete();

        if ($deep) {
            foreach ($this->children as $child) {
                $child->removeUser($user_id, true);
            }
        }

        return $result;
    }

    /**
     * Adds a user to a group
     * 
     * @param string $user_id The user id
     * @param boolean $check if <b>true</b> checks if there is space left in
     * this group
     * @return boolean <b>true</b> if user was added
     */
    public function addUser($user_id, $check = false)
    {
        if ($check && !$this->userMayJoin($user_id)) {
            return false;
        }
        $user = new StatusgruppeUser(array($this->id, $user_id));
        return $user->store();
    }

    /**
     * Checks if a user could join this group
     * 
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user is allowed to join
     */
    public function userMayJoin($user_id)
    {
        return !$this->isMember($user_id)
            && $this->hasSpace()
            && ($this->selfAssign != 2 || !$this->userHasExclusiveGroup($user_id));
    }

    /**
     * Checks if the user is already in an exclusive group of this range
     * 
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user has already an exclusive group
     */
    public function userHasExclusiveGroup($user_id)
    {
        $sql = "SELECT 1 FROM statusgruppe_user JOIN statusgruppen USING (statusgruppe_id) WHERE range_id = ? AND user_id = ?";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute(array($this->range_id, $user_id));
        return $stmt->fetchColumn();
    }
    
    /**
     * Sorts the member of a group alphabetic
     */
    public function sortMembersAlphabetic()
    {
        foreach ($this->members as $member) {
            $assoc[$member->id] = $member->user->nachname."_".$member->user->vorname;
        }
        asort($assoc);

        $i = 0;
        foreach ($assoc as $key => $value) {
            $statusgruppenuser = new StatusgruppeUser(explode('_', $key));
            $statusgruppenuser->position = $i++;
            $statusgruppenuser->store();
        }
    }

    /**
     * Checks if there is free space in this group
     * 
     * @return <b>true</b> if there is free space
     */
    public function hasSpace()
    {
        return $this->selfassign &&
            ($this->selfassign_start <= time()) &&
            ($this->selfassign_end == 0 || $this->selfassign_end >= time()) &&
            ($this->size == 0 || count($this->members) < $this->size);
    }

    /**
     * Move a user to a position of a group
     * 
     * @param string $user 
     * @param type $pos
     */
    public function moveUser($user_id, $pos)
    {
        $statususer = new StatusgruppeUser(array($this->id, $user_id));
        if ($pos > $statususer->position) {
            $sql = "UPDATE statusgruppe_user SET position = position - 1 WHERE statusgruppe_id = ? AND position > ? AND position <= ?";
        } else {
            $sql = "UPDATE statusgruppe_user SET position = position + 1 WHERE statusgruppe_id = ? AND position < ? AND position >= ?";
        }
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->id, $statususer->position, $pos));

        $sql2 = "UPDATE statusgruppe_user SET position = ? WHERE statusgruppe_id = ? AND user_id = ?";
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute(array($pos, $this->id, $statususer->user_id));
    }

    public function store()
    {
        if ($this->position === null) {
            $sql = "SELECT MAX(position) + 1 FROM statusgruppen WHERE range_id = ?";
            $stmt = DBManager::get()->prepare($sql);
            $stmt->execute(array($this->range_id));
            $this->position = $stmt->fetchColumn();
        }
        parent::store();
    }

    /**
     * Deletes a status group. Any associated child group will move upwards
     * in the tree.
     */
    public function remove()
    {
        // get all child-statusgroups and put them as a child of the father, so they don't hang around without a parent
        $children = $this->children->pluck('statusgruppe_id');
        if (!empty($children)) {
            $query = "UPDATE statusgruppen
                      SET range_id = ?
                      WHERE statusgruppe_id IN (?)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->range_id, $children]);
        }

        $old = $this->keep_children;
        $this->keep_children = true;

        $result = $this->delete();

        $this->keep_children = $old;

        return $result;
    }

    /**
     * Deletes a status group and all it's child groups.
     *
     * @return int number of deleted groups
     */
    public function delete()
    {
        $result = 0;
        if (!$this->keep_children) {
            foreach($this->children as $child) {
                $result += $child->delete();
            }
            
        }

        // Resort groups
        $query = "UPDATE statusgruppen
                  SET position = position - 1
                  WHERE range_id = ? AND position > ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->range_id, $this->position]);

        // Remove datafields
        $query = "DELETE FROM datafields_entries
                  WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);

        $result += parent::delete();

        return $result;
    }
}
