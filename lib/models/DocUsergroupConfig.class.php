<?php

class DocUsergroupConfig extends SimpleORMap
{
    public function __construct($id = null)
    {
        $this->db_table = 'doc_usergroup_config';
        parent::__construct($id);
    }
    
    static function getGroupConfig($usergroup)
    {
        $db= DBManager::get();
        $group = array_shift(DocUsergroupConfig::findBySQL('usergroup = '.$db->quote($usergroup)));
        if (!empty($group)) {
            $data['id'] = $group['id'];
            $data['name'] = $group['usergroup'];
            $data['upload'] = $group['upload_quota'];
            $data['upload_unit'] = $group['upload_unit'];
            $data['quota'] = $group['quota'];
            $data['quota_unit'] = $group['quota_unit'];
            $data['forbidden'] = $group['upload_forbidden'];
            $data['area_close']= $group['area_close'];
            $data['area_close_text']=$group['area_close_text'];
            $data['types'] = DocUsergroupConfig::getForbiddenTypesNameFromGroup($group['usergroup']);
        } else {
            return array();
        }
        return $data;
    }
    
    /*
     * Returns all configuration for Groups (user, autor etc.)
     */
    static function getGroupConfigAll()
    {
        $foo = DocUsergroupConfig::findBySQL('usergroup IS NOT NULL AND is_group_config = 1 ORDER BY usergroup');
        $data = array();
        foreach ($foo as $group) {
            $bar['id'] = $group['id'];
            $bar['name'] = $group['usergroup'];
            $bar['upload'] = $group['upload_quota'];
            $bar['upload_unit'] = $group['upload_unit'];
            $bar['quota'] = $group['quota'];
            $bar['quota_unit'] = $group['quota_unit'];
            $bar['forbidden'] = $group['upload_forbidden'];
            $bar['types'] = DocUsergroupConfig::getForbiddenTypesNameFromGroup($group['usergroup']);
            $data[] = $bar;
        }
        return $data;
    }
    
    public static function getForbiddenTypesNameFromGroup($groupname) 
    {
        $db = DBManager::get();
        $data = array();
        $typeID = DocFileTypeForbidden::findBySQL('usergroup = ' . $db->quote($groupname));
        foreach ($typeID as $id) {
            $data[] = array_pop(DocFiletype::findById($id['dateityp_id']));
        }
        return $data;
    }
    
     /**
     * return upload configuration for user for given user_id
     *       array('upload'=>in byte, 'upload_unit'=> kB, MB, GB or TB, 
     *              'quota'=>in byte, 'quota_unit'=> kB, MB, GB or TB,
     *               'forbidden'=> 1 or 0, 'types'=>array(forbidden datetypes)
     *               ,'area_close'=> 1=Dateibereich gesperrt 0=Dateibreich offen
     *               ,'area_close_text'=>Begruendung warm Dateibereich gesperrt) 
     * @param  $user_id a user_id
     * @return array()
     *  
     */
    public static function getUserConfig($user_id) 
    {
        $user = array_shift(User::findByUser_id($user_id));
        $config = DocUsergroupConfig::getGroupConfig($user_id);
        if (empty($config)) {
            $config = DocUsergroupConfig::getGroupConfig($user['perms']);
        }
        if (empty($config)) {
            $config = DocUsergroupConfig::getGroupConfig('default');
        }
        return $config;
    }
    
    public static function getUser($user_id)
    {
        $db = DBManager::get();
        $user = array_shift(User::findByUser_id($user_id));
        $userConfig = UserConfigEntry::findBySQL('field = '. $db->quote('CALENDAR_SETTINGS').' AND user_id = '. $db->quote($user_id));
        if(!empty($user) || !empty($userConfig)){
            $data['user'] = $user;
            $data['userConfig'] = $userConfig;            
            return $data;
        }else{
            return array();
        }       
    }
    /*
     * Method to store the individual-user or the group-settings
     */
    public function setConfig($data)
    {
        if (!empty($data)) {
            $config = array_pop(DocUsergroupConfig::findByUsergroup($data['usergroup']));
            if (empty($config)) {
                $config = new DocUsergroupConfig();
                $config->setData($data);
            } else {
                $config->setData(array('quota' => $data['quota'], 'upload_quota' => $data['upload_quota'],
                    'upload_forbidden' => $data['upload_forbidden'], 'area_close_text' =>  $data['area_close_text'],
                    'area_close' =>  $data['area_close'], 'upload_unit' => $data['upload_unit'], 'quota_unit' => $data['quota_unit']));
            }
            $config = $config->store();
            $db = DBManager::get();
            DocFileTypeForbidden::deleteBySQL('usergroup = ' . $db->quote($data['usergroup']));
            foreach ($data['datetype_id'] as $file) {
                $filetype = new DocFileTypeForbidden();
                $filetype->setData(array('usergroup' => $data['usergroup'], 'dateityp_id' => $file));
                $filetype->store();
            }
        }
        return $config;
    }
    
    public static function searchForUser($searchData) 
    {
        $db = DBManager::get();
        $stringCount = 0;
        $searchString = '';
        $searchDataCount = 0;
        foreach ($searchData as $name => $wert) {
            $searchDataCount++;
            if (strlen($wert) > 0) {
                if ($stringCount > 0 && $searchDataCount <= count($searchData)) {
                    $stringCount++;
                    $searchString = ' AND ' . ' ' . $name . ' LIKE ' . $db->quote('%'.$wert.'%') . ' ';
                } else {
                    $searchString = ' ' . $name . ' LIKE ' . $db->quote('%'.$wert.'%') . ' ';
                    $stringCount++;
                }
                $searchQuery .= $searchString;
            }
        }
        if (strlen($searchQuery) > 0) {
            $user = User::findBySQL($searchQuery . ' ORDER BY Nachname');
            if (empty($user)) {
                return array();
            } else {
                return $user;
            }
        } else {
            return array();
        }
    }
}