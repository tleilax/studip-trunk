<?php

/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

class PersonalNotifications extends SimpleORMap {
    
    
    static public function add($user_ids, $url, $text, $html_id = null, $avatar = null) {
        if (!is_array($user_ids)) {
            $user_ids = array($user_ids);
        }
        if (!count($user_ids)) {
            return false;
        }
        $notification = new PersonalNotifications();
        $notification['html_id'] = $html_id;
        $notification['url'] = $url;
        $notification['text'] = $text;
        $notification['avatar'] = $avatar;
        $notification->store();
        
        foreach ($user_ids as $user_id) {
            if (self::isActivated($user_id)) {
                $db = DBManager::get();
                $insert_statement = $db->prepare(
                    "INSERT INTO personal_notifications_user " .
                    "SET user_id = :user_id, " .
                        "personal_notification_id = :id, " .
                        "seen = '0' " .
                "");
                $insert_statement->execute(array(
                    'id' => $notification->getId(), 
                    'user_id' => $user_id
                ));
            }
        }
    }
    
    static public function getMyNotifications($only_unread = true, $user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        $db = DBManager::get();
        $statement = $db->prepare(
            "SELECT pn.* " .
            "FROM personal_notifications AS pn " .
                "INNER JOIN personal_notifications_user AS u ON (u.personal_notification_id = pn.personal_notification_id) " .
            "WHERE u.user_id = :user_id " .
                ($only_unread ? "AND u.seen = '0' " : "") .
        "");
        $statement->execute(array('user_id' => $user_id));
        $notifications = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $notification = new PersonalNotifications();
            $notification->setData($data);
            $notifications[] = $notification;
        }
        return $notifications;
    }
    
    static public function markAsRead($notification_id, $user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        $pn = new PersonalNotifications($notification_id);
        $statement = DBManager::get()->prepare(
            "UPDATE personal_notifications_user AS pnu " .
                "INNER JOIN personal_notifications AS pn ON (pn.personal_notification_id = pnu.personal_notification_id) " .
            "SET pnu.seen = '1' " .
            "WHERE pnu.user_id = :user_id " .
                "AND pn.url = :url " .
        "");
        return $statement->execute(array(
            'user_id' => $user_id,
            'url' => $pn['url']
        ));
    }
    
    static public function activate($user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_DEACTIVATED", "0");
    }
    
    static public function deactivate($user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_DEACTIVATED", "1");
    }
    
    static public function activateAudioFeedback($user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_AUDIO_DEACTIVATED", "0");
    }
    
    static public function deactivateAudioFeedback($user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        UserConfig::get($user_id)->store("PERSONAL_NOTIFICATIONS_AUDIO_DEACTIVATED", "1");
    }
    
    static public function isGloballyActivated()
    {
        $config = Config::GetInstance();
        return !empty($config['PERSONAL_NOTIFICATIONS_ACTIVATED']);
    }
    
    static public function isActivated($user_id = null) {
        if (!PersonalNotifications::isGloballyActivated()) {
            return false;
        }
        
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        return UserConfig::get($user_id)->getValue("PERSONAL_NOTIFICATIONS_DEACTIVATED") ? false : true;
    }
    
    static public function isAudioActivated($user_id = null) {
        if (!PersonalNotifications::isGloballyActivated()) {
            return false;
        }
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        return UserConfig::get($user_id)->getValue("PERSONAL_NOTIFICATIONS_AUDIO_DEACTIVATED") ? false : true;
    }
    
    public function getLiElement() {
        return $GLOBALS['template_factory']
                ->open("personal_notifications/notification.php")
                ->render(array('notification' => $this));
    }
    
    function __construct($id = null)
    {
        $this->db_table = "personal_notifications";
        $this->default_values['text'] = '';
        parent::__construct($id);
    }
}