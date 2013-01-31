<?php
/**
 * ForumLike.php - Manage the likes for postings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

class ForumLike {
    
    /**
     * Set the posting denoted by the passed topic_id as liked for the
     * currently logged in user
     * 
     * @param string $topic_id
     */
    static function like($topic_id) {
        $stmt = DBManager::get()->prepare("REPLACE INTO
            forum_likes (topic_id, user_id)
            VALUES (?, ?)");
        $stmt->execute(array($topic_id, $GLOBALS['user']->id));
    }
    
    /**
     * Revoke the liking of the posting denoted by the passed topic_id for the
     * currently logged in user
     * 
     * @param string $topic_id
     */
    static function dislike($topic_id) {
        $stmt = DBManager::get()->prepare("DELETE FROM forum_likes
            WHERE topic_id = ? AND user_id = ?");
        $stmt->execute(array($topic_id, $GLOBALS['user']->id));        
    }
    
    /**
     * Get the user_id for all likers of the topic denoted by the passed id
     * 
     * @param string $topic_id
     * @return array  an array of user_id's
     */
    static function getLikes($topic_id) {
        $stmt = DBManager::get()->prepare("SELECT 
            auth_user_md5.user_id FROM forum_likes
            LEFT JOIN auth_user_md5 USING (user_id)
            LEFT JOIN user_info USING (user_id)
            WHERE topic_id = ?");
        $stmt->execute(array($topic_id));
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}