<?php
/**
 * folder.php - controller for one folder
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.0
 */
 
class FolderController extends AuthenticatedController
{
    
    public function new_action()
    {
        //get ID of course, institute, user etc.
        
        $courseId = Request::get('cid');
        $instituteId = Request::get('instituteId');
        $messageId = Request::get('messageId');
        $userId = Request::get('userId');
        
        $folderName = Request::get('folderName');
        $parentFolderId = Request::get('parentFolderId');
        
        if($folderName and $parentFolderId) {
            //if $folderName and $parentFolderId are present
            //we know that the form was submitted.
            
            $rangeId = null;
            if($courseId) {
                //The user wants to add a folder to a course's file area.
                $rangeId = $courseId;
            } elseif($instituteId) {
                //The user wants to add a folder to an institute's file area.
                $rangeId = $instituteId;
            } elseif($messageId) {
                //The user wants to add a folder to a message.
                $rangeId = $messageId;
            } elseif($userId) {
                //The user wants to add a folder to another user's file area.
                $rangeId = $userId;
            } else {
                //The user wants to add a folder to his own file area.
                $rangeId = User::findCurrent()->id;
            }
            
            //get parent folder:
            $parentFolder = null;
            if($parentFolderId) {
                
            } else {
                $parentFolder = Folder::findTopFolder($rangeId);
            }
            
            //display error message and return if the parent folder doesn't exist:
            if(!$parentFolder) {
                if($parentFolderId) {
                    PageLayout::postError(_('Unterordner kann nicht erstellt werden!'));
                } else {
                    PageLayout::postError(_('Fehler beim Erstellen eines Ordners!'));
                }
                return;
            }
            
            $folder = new Folder();
            $folder->parent_id = $parentFolder->id;
            $folder->range_id = $rangeId;
            $folder->user_id = User::findCurrent()->id;
            if($courseId) {
                $folder->range_type = 'course';
            } elseif($instituteId) {
                $folder->range_type = 'institute';
            } elseif($messageId) {
                $folder->range_type = 'message';
            } else {
                //in case of $userId or anything else
                $folder->range_type = 'user';
            }
            $folder->name = studip_utf8decode($folderName);
            $folder->description = studip_utf8decode($folderDescription);
            
            $folder->store();
            
            PageLayout::postSuccess('Ordner wurde erstellt!');
        }
    }
    
    
    public function edit_action()
    {
    
    }
    
    
    public function move_action()
    {
        //TODO
    }
    
    
    public function delete_action()
    {
        //TODO
    }
}
