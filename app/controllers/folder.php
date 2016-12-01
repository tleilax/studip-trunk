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

/**
 * This controller contains actions related to single folders.
 */
class FolderController extends AuthenticatedController
{

    protected $utf8decode_xhr = true;

    /**
     * This is a helper method that decides where a redirect shall be made
     * in case of error or success after an action was executed.
     */
    private function redirectToFolder(FolderType $folder, $message = null)
    {
        if($message instanceof MessageBox) {
            if(Request::isDialog()) {
                $this->render_text($message);
            } else {
                PageLayout::postMessage($message);
            }
        }

        if(!Request::isDialog()) {
            //we only need to redirect when we're not in a dialog!

            $dest_range = $folder->range_id;

            switch ($folder->range_type) {
                case 'course':
                case 'institute':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $folder->getId() . '?cid=' . $dest_range));
                case 'user':
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/' . $folder->getId()));
                default:
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/' . $folder->getId()));
            }
        }
    }


    /**
     * Helper action for views: If the abort button in a view is clicked this action is called
     * with the ID of a folder. It then loads the folder object and decides to which URL
     * the user shall be redirected.
     */
    public function goto_action($folder_id)
    {
        $folder = Folder::find($folder_id);
        if($folder) {
            $this->redirectToFolder($folder);
        }
    }

    /**
     * Action for creating a new folder.
     */
    public function new_action()
    {

        if (!$parent_folder_object = Folder::find(Request::option('parent_folder_id'))) {
            throw new Trails_Exception(404, 'parent folder not found');
        }
        
        $parent_folder = $parent_folder_object->getTypedFolder();
        
        /*
        TEMPORARY DISABLED!
        if (!$parent_folder->isSubfolderAllowed($GLOBALS['user']->id)) {
            throw new AccessDeniedException();
        }
        */

        $this->parent_folder_id = $parent_folder->getId();
        $this->range_id = $parent_folder->range_id;



        $folder_types = FileManager::getFolderTypes($parent_folder->range_type);

        $this->current_folder_type = 'StandardFolder';
        $this->folder_types = [];

        foreach($folder_types as $folder_type) {
            $folder_type_instance = new $folder_type(new Folder());
            $this->folder_types[] = [
                'class' => $folder_type,
                'name' => $folder_type::getTypeName(),
                'icon' => $folder_type_instance->getIcon('clickable')
            ];
        }

        //get ID of course, institute, user etc.
        if (Request::get('form_sent')) {

            $this->name = Request::get('name');
            $current_user = User::findCurrent();

            if($this->name) {
                //If $this->name is present we have all required parameters
                //to create a folder.

                $this->description = Request::get('description');
                $this->current_folder_type = Request::get('folder_type', 'StandardFolder');


                if(!is_subclass_of($this->current_folder_type, FolderType)) {
                    if(Request::isDialog()) {
                        $this->render_text(MessageBox::error(_('Unbekannter Ordnertyp!'), $errors));
                    } else {
                        PageLayout::postError(_('Unbekannter Ordnertyp!'), $errors);
                        $this->render_template('file/new_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
                    }
                    return;
                }


                if($parent_folder->isWritable($current_user->id)) {
                    //current user may create a new folder in the parent folder

                    $result = FileManager::createSubFolder(
                        $parent_folder,
                        $current_user,
                        $this->current_folder_type,
                        $this->name,
                        $this->description
                    );
                    

                    if(!is_array($result)) {
                        $folder_type = $result;
                        //FileManager::createSubFolder returned an empty array => no errors!

                        if(Request::get('js')) {
                            //special return value for JavaScript dialogs
                            $this->folder = $folder_type; //FolderType is the interface for all folders!
                            $this->marked_element_ids = [];
                            
                            $result_json = [];
                            $result_json['tr'] = $this->render_template_as_string('files/_folder_tr');
                            $result_json['folder_id'] = $folder_type->getId();
                            // (unsused code) $payload = array("func" => "STUDIP.Folders.updateFolderListEntry", 'payload' => $payload);
                        
                            $this->render_json($result_json);
                        } else {
                            $this->redirectToFolder($parent_folder, MessageBox::success(_('Ordner wurde angelegt!')));
                            return;
                        }
                    } else {
                        if(Request::isDialog()) {
                            $this->render_text(MessageBox::error(_('Fehler beim Anlegen des Ordners'), $result));
                        } else {
                            PageLayout::postError(_('Fehler beim Anlegen des Ordners'), $result);
                            $this->render_template('file/new_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
                        }
                    }
                    return;
                } else {
                    if(Request::isDialog()) {
                        $this->render_text(
                            MessageBox::error(
                                _('Sie besitzen nicht die erforderlichen Berechtigungen zum Anlegen eines neuen Ordners!')
                            )
                        );
                    } else {
                        PageLayout::postError(
                            _('Sie besitzen nicht die erforderlichen Berechtigungen zum Anlegen eines neuen Ordners!')
                        );
                        $this->render_template('file/new_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
                    }
                    return;
                }

                /*
                if($folder->range_type == 'user') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/files/index/'.$parent_folder->id));
                } elseif($folder->range_type == 'course') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/course/files/index/'.$parent_folder->id));
                } elseif($folder->range_type == 'inst') {
                    return $this->redirect(URLHelper::getUrl('dispatch.php/institute/files/index/'.$parent_folder->id));
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


    /**
     * Action for editing an existing folder, referenced by its ID.
     *
     * @param string folder_id The ID of the folder that shall be edited.
     */
    public function edit_action($folder_id)
    {
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
        
        $this->folder = $this->folder->getTypedFolder();
        if(!$this->folder) {
            $this->render_text(
                MessageBox::error(_('Ordnertyp des Ordners konnte nicht ermittelt werden!'))
            );
            return;
        }
        
        
        $this->folder_id = $this->folder->getId();
        $this->parent_folder_id = $this->folder->parent_id;

        $current_user = User::findCurrent();

        //permission check: is the current allowed to edit the folder?

        if($this->folder->isWritable($current_user->id)) {

            if(Request::get('form_sent')) {
                //update edited fields
                $this->name = Request::get('name');
                $this->description = Request::get('description');

                $result = FileManager::editFolder($this->folder, $current_user, $this->name, $this->description);

                if($result instanceof FolderType) {
                    $this->redirectToFolder($this->folder, MessageBox::success(_('Ordner wurde bearbeitet!')));
                } else {
                    $this->redirectToFolder($this->folder, MessageBox::error(_('Fehler beim Bearbeiten des Ordners!'), $result));
                }
                return;
            } else {
                //show current field values:

                $this->name = $this->folder->name;
                $this->description = $this->folder->description;
            }
        } else {
            //current user isn't permitted to change this folder:
            $error_message = MessageBox::error(_('Sie sind nicht dazu berechtigt, diesen Ordner zu bearbeiten!'));

            $this->redirectToFolder($this->folder, $error_message);

            return;
        }

        if(Request::isDialog()) {
            $this->render_template('file/edit_folder.php');
        } else {
            $this->render_template('file/edit_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
    }


    /**
     * Common method for copying or moving folders.
     *
     * Since the process of copying or moving folders is very similar it is
     * unified into one method that distincts via a parameter, if it is in
     * copy or move mode.
     *
     * @param string folder_id The ID of the folder that shall be copied/moved.
     * @param bool copy True, if the folder shall be copied, otherwise false. Defaults to true.
     */
    public function copyOrMove($folder_id, $copy = true)
    {
        //we need the IDs of the folder and the target parent folder.
        //these should only be present when the form was sent.

        if(!$folder_id) {
            $this->render_text(MessageBox::error(_('Ordner-ID nicht gefunden!')));
            return;
        }

        $this->folder = Folder::find($folder_id);
        if(!$this->folder) {
            $this->render_text(MessageBox::error(_('Ordner nicht gefunden!')));
            return;
        }

        $this->folder_id = $folder_id;
        $this->parent_folder_id = $this->folder->parent_id;

        $current_user = User::findCurrent();

        $folder_type = $this->folder->getTypedFolder();

        if($copy && !$folder_type->isReadable($current_user->id)) {
            //not permitted to copy the folder:
            $this->render_text(MessageBox::error(_('Sie sind nicht dazu berechtigt, den Ordner zu kopieren!')));
            return;
        }

        if(!$copy && !$folder_type->isWritable($current_user->id)) {
            //not permitted to move the folder:
            $this->render_text(MessageBox::error(_('Sie sind nicht dazu berechtigt, den Ordner zu verschieben!')));
            return;
        }


        //check if form was sent:

        if(Request::submitted('form_sent')) {
            /*$target_folder_id = Request::get('course_dest_folder');
            
            if(!$target_folder_id) {
                $target_folder_id = Request::get('inst_dest_folder');
            }
            
            if(!$target_folder_id) {
                $target_folder_id = Request::get('user_dest_folder');
            }*/
            $target_folder_id = Request::get('dest_folder');
            
            if(!$target_folder_id) {
                $this->render_text(MessageBox::error(_('Zielordner-ID nicht gefunden!')));
                return;
            }

            $this->target_folder = Folder::find($target_folder_id);
            if(!$this->target_folder) {
                $this->render_text(MessageBox::error(_('Zielordner nicht gefunden!')));
                return;
            }

            //ok, all data are present... now we have to check the permissions:

            $target_folder_type = $this->target_folder->getTypedFolder();

            if($copy) {
                $result = FileManager::copyFolder($folder_type, $target_folder_type, $current_user);

                if($result instanceof FolderType) {
                    $this->redirectToFolder($target_folder_type, MessageBox::success(_('Ordner erfolgreich kopiert!')));
                } else {
                    $this->redirectToFolder($target_folder_type, MessageBox::error(_('Fehler beim Kopieren des Ordners!'), $result));
                }
            } else {
                //ok, we can move the folder!
                $result = FileManager::moveFolder($folder_type, $target_folder_type, $current_user);

                if($result instanceof FolderType) {
                    $this->redirectToFolder($target_folder_type, MessageBox::success(_('Ordner erfolgreich verschoben!')));
                } else {
                    $this->redirectToFolder(MessageBox::error(_('Fehler beim Verschieben des Ordners!'), $result));
                }
            }
            return;
        }


        if ($GLOBALS['perm']->have_perm('root')) {
            $parameters = array(
                'semtypes' => studygroup_sem_types() ?: array(),
                'exclude' => array()
            );
        } else if ($GLOBALS['perm']->have_perm('admin')) {
            $parameters = array(
                'semtypes' => studygroup_sem_types() ?: array(),
                'institutes' => array_map(function ($i) {
                return $i['Institut_id'];
                }, Institute::getMyInstitutes()),
                'exclude' => array()
                );

        } else {
            $parameters = array(
                'userid' => $current_user->id,
                'semtypes' => studygroup_sem_types() ?: array(),
                'exclude' => array()
            );
        }

        $course_search = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);
        $this->search = QuickSearch::get('course_id', $course_search)
            ->setInputStyle('width:100%')
            ->fireJSFunctionOnSelect('function(){STUDIP.Files.getFolders();}')
            ->withButton()
            ->setAttributes(['name' => 'course_dest_folder'])
            ->render();

        $institute_sql =  "SELECT DISTINCT Institute.Institut_id, Institute.Name " .
                    "FROM Institute " .
                    "LEFT JOIN range_tree ON (range_tree.item_id = Institute.Institut_id) " .
                    "LEFT JOIN user_inst ON (user_inst.Institut_id = Institute.Institut_id)" .
                    "WHERE user_inst.user_id = '" . $current_user->id . "' " .
                    "AND Institute.Name LIKE :input " .
                    "OR Institute.Strasse LIKE :input " .
                    "OR Institute.email LIKE :input " .
                    "OR range_tree.name LIKE :input " .
                    "ORDER BY Institute.Name";

        $institute_search = SQLSearch::get($institute_sql, _("Einrichtung suchen"), 'Institut_id');
        $this->inst_search = QuickSearch::get('Institut_id', $institute_search)
            ->setInputStyle('width:100%')
            ->fireJSFunctionOnSelect('function(){STUDIP.Files.getFolders();}')
            ->withButton()
            ->setAttributes(['name' => 'inst_dest_folder'])
            ->render();

        $this->copy_mode = $copy; //for the view: copy and move both use the file/move_folder view


        if(Request::isDialog()) {
            $this->render_template('file/move_folder.php');
        } else {
            $this->render_template('file/move_folder.php', $GLOBALS['template_factory']->open('layouts/base'));
        }
    }


    /**
     * Action for copying a folder, referenced by its ID.
     *
     * This action does nothing but calling the copyOrMove method.
     */
    public function copy_action($folder_id)
    {
        $this->copyOrMove($folder_id, true);
    }


    /**
     * Action for moving a folder, referenced by its ID.
     *
     * This action does nothing but calling the copyOrMove method.
     */
    public function move_action($folder_id)
    {
        $this->copyOrMove($folder_id, false);
    }


    /**
     * Action for deleting a folder, referenced by its ID.
     */
    public function delete_action($folder_id)
    {
        //we need the folder's FolderType object, but first we check for the ID,
        //the folder object itself and then for the folder type:
        if(!$folder_id) {
            $this->render_text(MessageBox::error(_('Ordner-ID nicht gefunden!')));
            return;
        }

        $this->folder = Folder::find($folder_id);
        if(!$this->folder) {
            $this->render_text(MessageBox::error(_('Ordner nicht gefunden!')));
            return;
        }
        
        $this->folder = $this->folder->getTypedFolder();
        if(!$this->folder) {
            $this->render_text(MessageBox::error(_('Ordnertyp konnte nicht ermittelt werden!')));
            return;
        }

        //ok, we can try to delete the folder:
        
        $result = FileManager::deleteFolder($this->folder, User::findCurrent());
        
        if($result instanceof FolderType) {
            $this->render_text(MessageBox::success(_('Ordner wurde gelöscht!')));
        } else {
            $this->render_text(MessageBox::error(_('Ordner konnte nicht gelöscht werden!', $result)));
        }
    }
}
