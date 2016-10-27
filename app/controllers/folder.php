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
        if (Request::submitted('create_folder')) {
            
            $this->folderName = Request::get('folderName');
            $this->parentFolderId = Request::get('parentFolderId');            
            $this->rangeId = Request::option('rangeId');
            $currentUser = User::findCurrent();
            
            if($this->folderName && ($this->parentFolderId || $this->rangeId)) {
                //if $folderName and $this->parentFolderId or $this->rangeId are present
                //we have all required parameters to create a folder.
                
                $folderDescription = Request::get('description'); 
                
                //get parent folder:
                $parentFolder = null;
                if($this->parentFolderId) {
                    $parentFolder = Folder::find($this->parentFolderId);
                } else {
                    $parentFolder = Folder::findTopFolder($this->rangeId);
                }
                
                //check if parent folder doesn't exist:
                if(!$parentFolder) {
                    if($this->parentFolderId) {
                        //parent folder ID was given but parent folder was not found: that's an error!
                        PageLayout::postError(_('Unterordner kann nicht erstellt werden!'));
                        return;
                    } else {
                        //no parent folder ID was given: we can still get the parent folder
                        //by looking at the range-ID and get the top folder:
                        
                        $parentFolder = Folder::findTopFolder($this->rangeId);
                    }
                }
                
                
                //the current user is the only one who can create a folder in his personal file area.
                //Any other user is not allowed to add a folder to the personal file area.
                //If it is not a folder of the personal file area the user must be the owner
                //of the parent folder or he must be root to create a subfolder.
                if(($parentFolder->user_id == $currentUser->id) && 
                        (
                        ($parentFolder->range_type == 'user') ||
                        (($parentFolder->range_type != 'user') && (($parentFolder->user_id == $currentUser->id) || $perm->have_perm('root')))
                        )
                    ) {
                    //current user may create a new folder in the parent folder
                    
                    $folder = new Folder();
                    $folder->parent_id = $parentFolder->id;
                    $folder->range_id = $parentFolder->range_id;
                    $folder->user_id = $currentUser->id;
                    $folder->range_type = $parentFolder->range_type;
                    $folder->folder_type = $parentFolder->folder_type;
                    $folder->name = studip_utf8decode($folderName);
                    $folder->description = studip_utf8decode($folderDescription);
                    $folder->store();
                    
                    PageLayout::postSuccess('Ordner wurde erstellt!');
                } else {
                    PageLayout::postError(
                        _('Sie besitzen nicht die erforderlichen Berechtigungen zum Anlegen eines neuen Ordners!')
                    );
                    return;
                }
                
                if($folder->range_type == 'user') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/'.$parentFolder->id));
                } elseif($folder->range_type == 'course') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$parentFolder->id));
                } elseif($folder->range_type == 'inst') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/'.$parentFolder->id));
                }
                
                
            } else {
                if(!$this->folderName) {
                    PageLayout::postError(
                        _('Es wurde kein Name angegeben!')
                    );
                } else {
                    PageLayout::postError(
                        _('Das übergeordnete Verzeichnis kann nicht identifiziert werden!')
                    );
                }
            }
        } else {
            
            $this->rangeId = Request::option('rangeId');
            $this->context = Request::get('context');
           
            $this->parentFolderId = Request::get('parentFolderId');
            if (empty($this->parentFolderId)) {
                $this->parentFolderId = Folder::findTopFolder($this->rangeId)->id;
            }
        }
        
        if(Request::isDialog()) {
            $this->render_template('file/new_folder.php');
        } else {
            $this->render_template('file/new_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
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
        
        
        $this->render_template('file/edit.php', $GLOBALS['template_factory']->open('layouts/base'));
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
        
        $this->render_template('file/move.php', $GLOBALS['template_factory']->open('layouts/base'));
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
        //DEVELOPMENT STAGE ONLY:
        //return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$parentFolder->id));
        
        $this->render_template('file/delete.php', $GLOBALS['template_factory']->open('layouts/base'));
    }
}
