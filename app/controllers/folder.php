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
        global $perm;
        
        //get ID of course, institute, user etc.
        
        $courseId = Request::get('cid');
        $instituteId = Request::get('instituteId');
        $messageId = Request::get('messageId');
        $userId = Request::get('userId');
        
        $folderName = Request::get('folderName');
        $parentFolderId = Request::get('parentFolderId');
        
        $currentUser = User::findCurrent();
        
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
                $rangeId = $currentUser->id;
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
            
            if(($parentFolder->user_id == $currentUser->id) or $perm->have_perm('admin')) {
                //current user may create a new folder in the parent folder
                
                $folder = new Folder();
                $folder->parent_id = $parentFolder->id;
                $folder->range_id = $rangeId;
                $folder->user_id = $currentUser->id;
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
    }
    
    
    public function edit_action($folderId)
    {
        global $perm;
        
        //we need the folder-ID of the folder that is to be edited:
        if(!$folderId) {
            PageLayout::postError(_('Ordner-ID nicht gefunden!'));
            return;
        }
        
        $this->folder = Folder::find($folderId);
        if(!$this->folder) {
            PageLayout::postError(_('Ordner nicht gefunden!'));
            return;
        }
        
        $currentUser = User::findCurrent();
        
        //permission check: is the current user the owner of the folder
        //or is the current user an admin?
        if(($folder->user_id == $currentUser->id) or $perm->have_perm('admin')) {
            //update edited fields (that should only be present when the form was sent)
            $folderName = Request::get('folderName');
            if($folderName) {
                $this->folder->name = $folderName;
            }
            
            if($folderDescription) {
                $this->folder->description = $folderDescription;
            }
        } else {
            //current user isn't permitted to change this folder:
            PageLayout::postError(_('Sie sind nicht dazu berechtigt, diesen Ordner zu bearbeiten!'));
        }
    }
    
    
    public function move_action($folderId)
    {
        global $perm;
        
        //we need the IDs of the folder and the target parent folder.
        //these should only be present when the form was sent.
        
        if(!$folderId) {
            PageLayout::postError(_('Ordner-ID nicht gefunden!'));
            return;
        }
        
        $targetFolderId = Request::get('targetFolderId');
        if(!$targetFolderId) {
            PageLayout::postError(_('Zielordner-ID nicht gefunden!'));
        }
        
        $this->folder = Folder::find($folderId);
        if(!$this->folder) {
            PageLayout::postError(_('Ordner nicht gefunden!'));
            return;
        }
        
        $this->targetFolder = Folder::find($targetFolderId);
        if(!$this->targetFolder) {
            PageLayout::postError(_('Zielordner nicht gefunden!'));
            return;
        }
        
        $currentUser = User::findCurrent();
        
        //ok, all data are present... now we have to check the permissions:
        
        if(($this->folder->user_id == $currentUser->id) or $perm->have_perm('admin')) {
            //ok, first step was successfull...
            if(($this->targetFolder->user_id == $currentUser->id) or $perm->have_perm('admin')) {
                //second step was successfull as well => we can move the folder
                
                $this->folder->parent_id = $this->targetFolder->id;
                $this->folder->store();
            } else {
                //not permitted to create subfolder in target folder:
                PageLayout::postError(_('Sie sind nicht dazu berechtigt, im Zielordner einen Ordner einzufügen!'));
            }
        } else {
                //not permitted to change folder:
                PageLayout::postError(_('Sie sind nicht dazu berechtigt, den Ordner zu verschieben!'));
        }
        
    }
    
    
    public function delete_action($folderId)
    {
        global $perm;
        
        //we need the ID of the folder:
        if(!$folderId) {
            PageLayout::postError(_('Ordner-ID nicht gefunden!'));
            return;
        }
        
        $this->folder = Folder::find($folderId);
        if(!$this->folder) {
            PageLayout::postError(_('Ordner nicht gefunden!'));
            return;
        }
        
        //ok, check permissions:
        
        $currentUser = User::findCurrent();
        
        if(($this->folder->user_id == $currentUser->id) or $perm->have_perm('admin')) {
            $this->folder->delete();
            PageLayout::postSuccess(_('Ordner wurde gelöscht!'));
        } else {
            //not permitted to delete the folder:
            PageLayout::postError(_('Sie sind nicht dazu berechtigt, diesen Ordner zu löschen!'));
        }
    }
}
