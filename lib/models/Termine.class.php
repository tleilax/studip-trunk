<?php
# Lifter010: TODO
/**
 * Termine.class.php
 * model class for table termine
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string termin_id   database column
 * @property string id          alias column for termin_id
 * @property string range_id    database column
 * @property string author_id   database column
 * @property string content     database column
 * @property string description database column
 * @property string date        database column
 * @property string end_time    database column
 * @property string mkdate      database column
 * @property string chdate      database column
 * @property string date_typ    database column
 * @property string topic_id    database column
 * @property string raum        database column
 * @property string metadate_id database column
 * @property User   author      belongs_to User
 */
class Termine extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'termine';

        $config['belongs_to']['author'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'author_id'
        );
        parent::configure($config);
    }

    /**
     * Search related persons for a termin
     * @return array
     * @throws Exception
     */
    public function getRelatedPersons()
    {
        $stm = DBManager::get()->prepare('SELECT user_id FROM termin_related_persons WHERE range_id = ?');
        $stm->execute(array($this->id));
        $users = $stm->fetchAll(PDO::FETCH_COLUMN);
        $users = User::findMany($users);
        if(count($users)) {
            $users =  new SimpleCollection($users);
            $users =  $users->toGroupedArray('user_id', 'username vorname nachname email');
            foreach($users as $user_id => $user){
                $users[$user_id]['fullname'] = get_fullname($user_id);
            }
            return $users;
        }
        return null;
    }


    public function addRelatedPerson($user_id) {
        $query = "INSERT IGNORE INTO termin_related_persons (range_id, user_id) VALUES (?, ?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($this->id, $user_id));
        return $statement->rowCount();
    }
}
