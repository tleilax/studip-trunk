<?php
/**
 * UserFileArea.class.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2016 data-quest
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


/**
 * This class contains static methods especially designed for the user file area.
 */
class UserFileArea
{
    /**
     * Returns a FolderType object for the inbox folder of the given user.
     * 
     * @param User user The user whose inbox folder is requested.
     * 
     * @return Folder|null Returns the inbox folder on success, null on failure.
     */
    public static function getInboxFolder(User $user)
    {
        $top_folder = Folder::getTopFolder($user->id, 'user');
        if(!$top_folder) {
            return null;
        }
        
        $inbox_folder = Folder::findOneBySql(
            "(parent_id = :parent_id) AND (name = 'inbox')",
            ['parent_id' => $top_folder->id]
        );
        
        if(!$inbox_folder) {
            //inbox folder doesn't exist: create it!
            
            $inbox_folder = new Folder();
            $inbox_folder->name = 'INBOX';
            $inbox_folder->description = _('Ein Ordner für Dateianhänge eingegangener Nachrichten');
            
            $errors = self::createSubFolder($inbox_folder, $top_folder, $user);
            
            if(empty($errors)) {
                return $inbox_folder;
            } else {
                return null;
            }
        }
    }
    
    
    /**
     * Returns a FolderType object for the outbox folder of the given user.
     * 
     * @param User user The user whose outbox folder is requested.
     */
    public static function getOutboxFolder(User $user)
    {
        $top_folder = Folder::getTopFolder($user->id, 'user');
        if(!$top_folder) {
            return null;
        }
        
        $outbox_folder = Folder::findOneBySql(
            "(parent_id = :parent_id) AND (name = 'outbox')",
            ['parent_id' => $top_folder->id]
        );
        
        if(!$outbox_folder) {
            //inbox folder doesn't exist: create it!
            
            $outbox_folder = new Folder();
            $outbox_folder->name = 'OUTBOX';
            $outbox_folder->description = _('Ein Ordner für Dateianhänge gesendeter Nachrichten');
            
            $errors = self::createSubFolder($outbox_folder, $top_folder, $user);
            
            if(empty($errors)) {
                return $outbox_folder;
            } else {
                return null;
            }
        }
    }
    
}
