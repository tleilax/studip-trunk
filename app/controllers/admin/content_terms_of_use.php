<?php

/**
 * content_terms_of_use.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   data-quest Suchi & Berg GmbH
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       4.0
 */

/**
 * Controller for adding, editing and deleting content terms of use entries.
 */
class Admin_ContentTermsOfUseController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        //Do the permission check here since all actions
        //are only accessible for root users:
        global $perm;
        parent::before_filter($action, $args);
        
        if(!$perm->have_perm('root')) {
            throw new AccessDeniedException();
        }
        
        //activate navigation item:
        if(Navigation::hasItem('/admin/locations/content_terms_of_use')) {
            Navigation::activateItem('/admin/locations/content_terms_of_use');
        }
    }
    
    
    /**
     * This action displays a list of all content terms of use entries.
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Inhalts-Nutzungsbedingungen'));
        
        //build sidebar:
        $sidebar = Sidebar::get();
        
        $actions = new ActionsWidget(_('Aktionen'));
        
        $actions->addLink(
            _('Eintrag hinzufügen'),
            URLHelper::getUrl('dispatch.php/admin/content_terms_of_use/add'),
            Icon::create('add', 'clickable'),
            [
                'data-dialog' => '1'
            ]
        );
        
        $sidebar->addWidget($actions);
        
        
        //load all ContentTermsOfUse entries:
        $this->content_terms_of_use_entries = ContentTermsOfUse::findBySql('TRUE ORDER BY position ASC, id ASC');
        
    }
    
    
    /**
     * This action lets a root user add a new content terms of use entry.
     */
    public function add_action()
    {
        PageLayout::setTitle(_('Eintrag hinzufügen'));
        $this->add_action = true;
        
        $this->entry_id = Request::get('entry_id');
        $this->entry_name = Request::get('entry_name', '');
        $this->entry_download_condition = Request::get('entry_download_condition', '0');
        $this->entry_icon = Request::get('entry_icon', 'license');
        $this->entry_position = Request::get('entry_position', '0');
        $this->entry_description = Request::get('entry_description', '');
        
        
        if($this->entry_id && $this->entry_name) {
            //check if the ID already exists and display an error message
            //if it exists:
            if(ContentTermsOfUse::exists($this->entry_id)) {
                PageLayout::postError(
                    _('Die gewünschte ID ist bereits vergeben!')
                );
                
                //error in form data: display add_edit_form
                if(Request::isDialog()) {
                    $this->render_template(
                        'admin/content_terms_of_use/add_edit_form.php'
                    );
                } else {
                    $this->render_template(
                        'admin/content_terms_of_use/add_edit_form.php',
                        $GLOBALS['template_factory']->open('layouts/base')
                    );
                }
                return;
            }
            
            //all required data are set or set to default values
            $new_entry = new ContentTermsOfUse();
            $new_entry->id = $this->entry_id;
            $new_entry->name = $this->entry_name;
            $new_entry->download_condition = $this->entry_download_condition;
            $new_entry->icon = $this->entry_icon;
            $new_entry->position = $this->entry_position;
            $new_entry->description = $this->entry_description;
            
            if($new_entry->store()) {
                PageLayout::postSuccess(
                    _('Neuer Eintrag für Nutzungsbedingungen wurde hinzugefügt!')
                );
                if(!Request::isDialog()) {
                    $this->redirect(
                        'admin/content_terms_of_use/index'
                    );
                }
                return;
            } else {
                PageLayout::postError(
                    _('Fehler beim Speichern des neuen Eintrags für Nutzungsbedingungen')
                );
            }
        }
        
        //no form submitted or error in form data: display add_edit_form
        if(Request::isDialog()) {
            $this->render_template(
                'admin/content_terms_of_use/add_edit_form.php'
            );
        } else {
            $this->render_template(
                'admin/content_terms_of_use/add_edit_form.php',
                $GLOBALS['template_factory']->open('layouts/base')
            );
        }
    }
    
    
    /**
     * This action lets a root user edit a new content terms of use entry.
     */
    public function edit_action()
    {
        PageLayout::setTitle(_('Eintrag bearbeiten'));
        $this->add_action = false;
        
        //if old_entry_id is not set, try to set the value of 
        //the parameter entry_id:
        $this->entry_id = Request::get('entry_id');
        
        //load entry by looking at the entry_id:
        
        if(!$this->entry_id) {
            PageLayout::postError(
                _('Es wurde keine ID angegeben!')
            );
            
            $this->redirect(
                'admin/content_terms_of_use/index'
            );
            return;
        }
        
        $entry = ContentTermsOfUse::find($this->entry_id);
        
        if(!$entry) {
            PageLayout::postError(
                sprintf(
                    _('Eintrag für Nutzungsbedingungen mit ID %s wurde nicht in der Datenbank gefunden!'),
                $this->entry_id
                )
            );
            $this->redirect(
                'admin/content_terms_of_use/index'
            );
            return;
        } else {
            $this->entry_name = Request::get('entry_name', $entry->name);
            $this->entry_download_condition = Request::get(
                'entry_download_condition',
                $entry->download_condition
            );
            $this->entry_icon = Request::get('entry_icon', $entry->icon);
            $this->entry_position = Request::get(
                'entry_position',
                $entry->position
            );
            $this->entry_description = Request::get(
                'entry_description',
                $entry->description
            );
        }
            
        if(Request::submitted('save')) {
        
            if($this->entry_name) {
                
                //all required data are set:
                $entry->name = $this->entry_name;
                $entry->download_condition = $this->entry_download_condition;
                $entry->icon = $this->entry_icon;
                $entry->position = $this->entry_position;
                $entry->description = $this->entry_description;
                
                if($entry->store()) {
                    PageLayout::postSuccess(
                        _('Eintrag für Nutzungsbedingungen wurde bearbeitet!')
                    );
                    if(!Request::isDialog()) {
                        $this->redirect(
                            'admin/content_terms_of_use/index'
                        );
                    }
                    return;
                } else {
                    PageLayout::postError(
                        _('Fehler beim Speichern des Eintrags für Nutzungsbedingungen')
                    );
                }
            } else {
                PageLayout::postError(
                    _('Es wurde kein Name für den Eintrag gesetzt!')
                );
            }
        }
        
        //no form submitted or error in form data: display add_edit_form
        if(Request::isDialog()) {
            $this->render_template(
                'admin/content_terms_of_use/add_edit_form.php'
            );
        } else {
            $this->render_template(
                'admin/content_terms_of_use/add_edit_form.php',
                $GLOBALS['template_factory']->open('layouts/base')
            );
        }
    }
    
    
    /**
     * This action lets a root user delete a new content terms of use entry.
     */
    public function delete_action()
    {
        PageLayout::setTitle(_('Eintrag löschen'));
        
        $this->entry_id = Request::get('entry_id');
        
        //load entry by looking at the entry_id:
        
        $entry = ContentTermsOfUse::find($this->entry_id);
        
        if(!$entry) {
            //entry not found: return to index page
            PageLayout::postError(
                sprintf(
                    _('Eintrag für Nutzungsbedingungen mit ID %s wurde nicht in der Datenbank gefunden!'),
                $this->entry_id
                )
            );
            $this->redirect(
                'admin/content_terms_of_use/index'
            );
            return;
        }
        
        $this->dependent_files_count = FileRef::countBySql(
            'content_terms_of_use_id = :entry_id',
            [
                'entry_id' => $this->entry_id
            ]
        );
        
        
        if(Request::submitted('confirm')) {
            //delete was confirmed
            $this->other_entry_id = Request::get('other_entry_id');
            
            if($this->dependent_files_count > 0) {
                //files depend on the entry! We must give them
                //a new terms of use entry before we can delete
                //the entry!
                
                if(!$this->other_entry_id) {
                    //Files depend on the old entry, but no new entry
                    //was selected. That's an error!
                    PageLayout::postError(
                        sprintf(
                            _('Fehler beim Löschen von Eintrag mit ID %s: Es wurde für betroffene Dateien kein neuer Nutzungsbedingungen-Eintrag ausgewählt!'),
                            $this->entry_id
                        )
                    );
                    $this->redirect(
                        'admin/content_terms_of_use/index'
                    );
                    return;
                }
                
                
                //Change the content_terms_of_use_id for all file_refs
                //that have the ID of the entry which is going to be removed.
                
                $db = DBmanager::get();
                
                $result = $db->exec('UPDATE file_refs SET content_terms_of_use_id = '
                . $db->quote($this->other_entry_id)
                . 'WHERE content_terms_of_use_id = '
                . $db->quote($this->entry_id)
                );
                
                if($result === false) {
                    PageLayout::postError(
                        sprintf(
                            _('Fehler beim Zuordnen von Dateien zu Eintrag mit  ID %s! Eintrag mit ID %s wurde nicht gelöscht!'),
                        $this->other_entry_id,
                        $this->entry_id
                        )
                    );
                    
                    $this->redirect(
                        'admin/content_terms_of_use/index'
                    );
                    return;
                }
            }
            
            //delete the entry:
            if($entry->delete()) {
                if($this->dependent_files_count > 0) {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Eintrag mit ID "%s" wurde gelöscht. Alle Dateien, welche diesen Eintrag verwendeten, nutzen nun den Eintrag mit ID "%s"!'),
                            $this->entry_id,
                            $this->other_entry_id
                        )
                    );
                } else {
                    PageLayout::postSuccess(
                        sprintf(
                            _('Eintrag mit ID "%s" wurde gelöscht!'),
                            $this->entry_id
                        )
                    );
                }
            } else {
                if($this->dependent_files_count > 0) {
                    PageLayout::postError(
                        sprintf(
                            _('Fehler beim Löschen von Eintrag mit ID "%s"! Alle Dateien, welche diesen Eintrag verwendeten, nutzen nun den Eintrag mit ID "%s"!'),
                            $this->entry_id,
                            $this->other_entry_id
                        )
                    );
                } else {
                    PageLayout::postError(
                        sprintf(
                            _('Fehler beim Löschen von Eintrag mit ID "%s"!'),
                            $this->entry_id
                        )
                    );
                }
            }
            
            $this->redirect(
                'admin/content_terms_of_use/index'
            );
        } else {
            //form not submitted: If files depend on it,
            //another entry must be selected for these files.
            
            if($this->dependent_files_count > 0) {
                $this->other_entries = ContentTermsOfUse::findBySql(
                    'id <> :entry_id ORDER BY position ASC, id ASC',
                    [
                        'entry_id' => $this->entry_id
                    ]
                );
            }
        }
    }
}
