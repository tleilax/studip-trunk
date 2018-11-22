<?php

/**
 * StatusgruppeUser.php
 * model class for statusgroupusers.
 *
 * This model should be joined to an user object if nessecary
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
 * @property string user_id database column
 * @property string position database column
 * @property string visible database column
 * @property string inherit database column
 * @property string id computed column read/write
 * @property Statusgruppen group belongs_to Statusgruppen
 * @property User user belongs_to User
 */
class StatusgruppeUser extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'statusgruppe_user';
        $config['belongs_to']['group'] = array(
            'class_name' => 'Statusgruppen',
            'foreign_key' => 'statusgruppe_id',
        );
        $config['belongs_to']['user'] = array(
            'class_name' => 'User',
            'foreign_key' => 'user_id',
        );
        $config['has_many']['datafields'] = array(
            'class_name' => 'DatafieldEntryModel',
            'foreign_key' => function($group_member) {
                return [$group_member];
            },
            'assoc_foreign_key' => function($model, $params) {
                $model->setValue('range_id', $params[0]->user_id);
                $model->setValue('sec_range_id', $params[0]->statusgruppe_id);
            },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store'  => 'store',
        );

        $config['additional_fields']['vorname']     = ['user', 'vorname'];
        $config['additional_fields']['nachname']    = ['user', 'nachname'];
        $config['additional_fields']['username']    = ['user', 'username'];
        $config['additional_fields']['email']       = ['user', 'email'];
        $config['additional_fields']['title_front'] = ['user', 'title_front'];
        $config['additional_fields']['title_rear']  = ['user', 'title_rear'];

        parent::configure($config);
    }

    /**
     * Prevents invisible users from being displayed
     *
     * @return string Fullname if visible else string for invisible user
     */
    public function name($format = 'full_rev')
    {
        return $this->user->getFullname($format);
    }

    public function getUserFullname($format = "full")
    {
        return User::build(array_merge(array('motto' => ''), $this->toArray('vorname nachname username title_front title_rear')))->getFullname($format);
    }

    /**
     * Prevents the avatar of invisible users from being displayed
     *
     * @return mixed Useravatar if visible else dummyavatar
     */
    public function avatar()
    {
        return Avatar::getAvatar($this->user_id, $this->user->username)->getImageTag(Avatar::SMALL, array('title' => htmlReady($this->name())));
    }

    /**
     * {@inheritdoc }
     */
    public function store()
    {
        if ($this->isNew()) {
            $sql = "SELECT MAX(position)+1 FROM statusgruppe_user WHERE statusgruppe_id = ?";
            $stmt = DBManager::get()->prepare($sql);
            $stmt->execute(array($this->statusgruppe_id));
            $this->position = $stmt->fetchColumn() ?: 0;

            StudipLog::log(
                "STATUSGROUP_ADD_USER",
                $this['user_id'],
                $this['statusgruppe_id'],
                "Statusgruppe ".$this->group->name
            );
        }
        return parent::store();
    }

    /**
     * {@inheritdoc }
     */
    public function delete()
    {
        // Resort members
        $query = "UPDATE statusgruppe_user SET position = position - 1 WHERE statusgruppe_id = ? AND position > ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->statusgruppe_id, $this->position));

        StudipLog::log(
            "STATUSGROUP_REMOVE_USER",
            $this['user_id'],
            $this['statusgruppe_id'],
            "Statusgruppe ".$this->group->name
        );

        return parent::delete();
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @param User $user User object to acquire data for
     * @return array of StoredUserData objects
     */
    public static function getUserdata(User $user)
    {
        $storage = new StoredUserData($user);
        $sorm = self::findBySQL("user_id = ?", [$user->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData('statusgruppe_user', $field_data, $user);
            }
        }
        return [_('StatusgruppeUser') => $storage];
    }

}
