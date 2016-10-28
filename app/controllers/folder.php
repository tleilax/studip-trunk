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
        
        $this->parentFolderId = Request::get('parentFolderId');            
        $this->rangeId = Request::get('rangeId');
        
        //get parent folder:
        $parentFolder = Folder::find($this->parentFolderId);
        if(!$parentFolder) {
            if($this->parentFolderId) {
                //parent folder ID was given but parent folder was not found: that's an error!
                PageLayout::postError(_('�bergeordnetes Verzeichnis nicht gefunden!'));
                $this->render_text('');
                return;
            }
            Folder::findTopFolder($this->rangeId);
        }
        
        if(!$parentFolder) {
            PageLayout::postError(
                _('Das �bergeordnete Verzeichnis kann nicht identifiziert werden!')
            );
            $this->render_text('');
            return;
        }
        
        
        //get ID of course, institute, user etc.
        if (Request::get('submitted')) {
            
            $this->folderName = Request::get('folderName');
            $currentUser = User::findCurrent();
            
            if($this->folderName) {
                //if $this->folderName and $this->parentFolderId or $this->rangeId are present
                //we have all required parameters to create a folder.
                
                $this->folderDescription = Request::get('description'); 
                
                //the current user is the only one who can create a folder in his personal file area.
                //Any other user is not allowed to add a folder to the personal file area.
                //If it is not a folder of the personal file area the user must be the owner
                //of the parent folder or he must be root to create a subfolder.
                /*if(($parentFolder->user_id == $currentUser->id) && 
                        (
                        ($parentFolder->range_type == 'user') ||
                        (($parentFolder->range_type != 'user') && (($parentFolder->user_id == $currentUser->id) || $perm->have_perm('root')))
                        )
                    ) {*/
                if(true) { //DEVELOPMENT ONLY!
                    //current user may create a new folder in the parent folder
                    
                    $folder = new Folder();
                    $folder->name = studip_utf8decode($this->folderName);
                    $folder->description = studip_utf8decode($this->folderDescription);
                    
                    $errors = FileManager::createSubFolder($parentFolder, $currentUser, $folder);
                    if(!$errors) {
                        //FileManager::createSubFolder returned an empty array => no errors!
                        $this->render_text(MessageBox::success(_('Ordner wurde angelegt!')));
                    } else {
                        $this->render_text(MessageBox::error(_('Fehler beim Anlegen des Ordners'), $errors));
                    }
                    return;
                } else {
                    PageLayout::postError(
                        _('Sie besitzen nicht die erforderlichen Berechtigungen zum Anlegen eines neuen Ordners!')
                    );
                    $this->render_text('');
                    return;
                }
                                
                /*
                if($folder->range_type == 'user') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/'.$parentFolder->id));
                } elseif($folder->range_type == 'course') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$parentFolder->id));
                } elseif($folder->range_type == 'inst') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/'.$parentFolder->id));
                }
                */
                
            } else {
                PageLayout::postError(
                    _('Es wurde kein Name angegeben!')
                );
            }
        }
        
        
        if(Request::isDialog()) {
            $this->render_template('file/new_folder.php');
        } else {
            $this->render_template('file/new_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
        
    }
    
    
    public function edit_action($folder_id)
    {
        global $perm;
        
        //we need the folder-ID of the folder that is to be edited:
        if(!$folder_id) {
            $this->render_text(
                MessageBox::error(_('Ordner-ID nicht gefunden!'))
            );
            return;
        }
        
        $this->folder = Folder::find($folder_id);
        if(!$this->folder) {
            $this->render_text(
                MessageBox::error(_('Ordner nicht gefunden!'))
            );
            return;
        }
        
        $this->folder_id = $this->folder->id;
        
        $current_user = User::findCurrent();
        
        //permission check: is the current allowed to edit the folder?
        
        $folder_type = $this->folder->getTypedFolder();
        
        if($folder_type->isWritable($current_user->id)) {
            
            if(Request::get('form_sent')) {
                //update edited fields
                $this->name = Request::get('name');
                if($this->name) {
                    $this->folder->name = $this->name;
                } else {
                    //name is empty:
                    $this->render_text(
                        MessageBox::error(_('Ein neuer Name f�r den Ordner wurde nicht angegeben!'))
                    );
                }
                
                $this->description = Request::get('description');
                
                $this->folder->description = $this->description;
                
                $this->folder->store();
                
                $this->render_text(MessageBox::success(_('Ordner wurde bearbeitet!')));
                return;
            } else {
                //show current field values:
                
                $this->name = $this->folder->name;
                $this->description = $this->folder->description;
            }
        } else {
            //current user isn't permitted to change this folder:
            $this->render_text(
                MessageBox::error(_('Sie sind nicht dazu berechtigt, diesen Ordner zu bearbeiten!'))
            );
            return;
        }
        
        if(Request::isDialog()) {
            $this->render_template('file/edit_folder.php');
        } else {
            $this->render_template('file/edit_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
    }
    
    
    public function move_action($folder_id)
    {
        global $perm;
        
        //we need the IDs of the folder and the target parent folder.
        //these should only be present when the form was sent.
        
        if(!$folder_id) {
            $this->render_text(MessageBox::error(_('Ordner-ID nicht gefunden!')));
            return;
        }
        
        $targetFolderId = Request::get('targetFolderId');
        if(!$targetFolderId) {
            $this->render_text(MessageBox::error(_('Zielordner-ID nicht gefunden!')));
            return;
        }
        
        $this->folder = Folder::find($folder_id);
        if(!$this->folder) {
            $this->render_text(MessageBox::error(_('Ordner nicht gefunden!')));
            return;
        }
        
        $this->targetFolder = Folder::find($targetFolderId);
        if(!$this->targetFolder) {
            $this->render_text(MessageBox::error(_('Zielordner nicht gefunden!')));
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
                $this->render_text(MessageBox::error(_('Sie sind nicht dazu berechtigt, im Zielordner einen Ordner einzuf�gen!')));
                return;
            }
        } else {
                //not permitted to change folder:
                $this->render_text(MessageBox::error(_('Sie sind nicht dazu berechtigt, den Ordner zu verschieben!')));
                return;
        }
        
        if(Request::isDialog()) {
            $this->render_template('file/move.php');
        } else {
            $this->render_template('file/move.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
    }
    
    
    public function delete_action($folder_id)
    {
        global $perm;
        
        //we need the ID of the folder:
        if(!$folder_id) {
            $this->render_text(MessageBox::error(_('Ordner-ID nicht gefunden!')));
            return;
        }
        
        $this->folder = Folder::find($folder_id);
        if(!$this->folder) {
            $this->render_text(MessageBox::error(_('Ordner nicht gefunden!')));
            return;
        }
        
        //ok, check permissions:
        
        $currentUser = User::findCurrent();
        
        if(($this->folder->user_id == $currentUser->id) or $perm->have_perm('admin')) {
            $this->folder->delete();
            $this->render_text(MessageBox::success(_('Ordner wurde gel�scht!')));
            return;
        } else {
            //not permitted to delete the folder:
            $this->render_text(MessageBox::error(_('Sie sind nicht dazu berechtigt, diesen Ordner zu l�schen!')));
            return;
        }
        //DEVELOPMENT STAGE ONLY:
        //return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$parentFolder->id));
        
        if(Request::isDialog()) {
            $this->render_template('file/delete.php');
        } else {
            $this->render_template('file/delete.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
    }
}
