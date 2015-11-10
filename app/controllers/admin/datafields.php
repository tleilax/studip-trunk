<?php
# Lifter010: TODO
/**
 * datafields.php - controller class for the datafields
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       2.1
 */

class Admin_DatafieldsController extends AuthenticatedController
{
    public $user_status = array(
        'user'   =>  1,
        'autor'  =>  2,
        'tutor'  =>  4,
        'dozent' =>  8,
        'admin'  => 16,
        'root'   => 32,
    );

    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        // set navigation
        Navigation::activateItem('/admin/config/datafields');
        PageLayout::setTitle(_('Verwaltung von generischen Datenfeldern'));

        // Set variables used by (almost) all actions
        $this->allclasses   = DataFieldStructure::getDataClass();
        $this->class_filter = Request::option('class_filter', null);

        $this->createSidebar($action);
    }

    /**
     * Maintenance view for the datafield view
     *
     * @param $class static types for datafields
     */
    public function index_action($class = null)
    {
        if ($this->class_filter) {
            $this->datafields_list = array(
                $this->class_filter => DataFieldStructure::getDataFieldStructures($this->class_filter),
            );
        } else {
            $this->datafields_list = array(
                'sem'          => DataFieldStructure::getDataFieldStructures('sem'),
                'inst'         => DataFieldStructure::getDataFieldStructures('inst'),
                'user'         => DataFieldStructure::getDataFieldStructures('user'),
                'userinstrole' => DataFieldStructure::getDataFieldStructures('userinstrole'),
                'usersemdata'  => DataFieldStructure::getDataFieldStructures('usersemdata'),
                'roleinstdata' => DataFieldStructure::getDataFieldStructures('roleinstdata'),
            );
        }

        // set variables for view
        $this->current_class = $class;
        $this->allclass = array_keys($this->allclasses);
        $this->edit_id = Request::option('edit_id');
    }

    /**
     * Edit a datatyp
     *
     * @param md5 $datafield_id
     */
    public function edit_action($datafield_id)
    {
        if (Request::submitted('uebernehmen')) {
            $struct = new DataFieldStructure(compact('datafield_id'));
            $struct->load();
            if (Request::get('datafield_name')) {
                $struct->setName(Request::get('datafield_name'));
                $struct->setObjectClass(array_sum(Request::getArray('object_class')));
                $struct->setEditPerms(Request::get('edit_perms'));
                $struct->setViewPerms(Request::get('visibility_perms'));
                $struct->setPriority(Request::get('priority'));
                $struct->setType(Request::get('datafield_type'));
                $struct->setIsRequired(Request::get('is_required'));
                $struct->setDescription(Request::get('description'));
                $struct->setIsUserfilter(Request::int('is_userfilter'));
                $struct->store();

                PageLayout::postSuccess(_('Die Änderungen am generischen Datenfeld wurden übernommen.'));
                $this->redirect('admin/datafields/index/'.$struct->getObjectType().'#item_'.$datafield_id);
            } else {
                PageLayout::postError(_('Es wurde keine Bezeichnung eingetragen!'));
            }

        }

        // set variables for view
        $struct = new DataFieldStructure(compact('datafield_id'));
        $struct->load();
        $this->item = $struct;
        $this->datafield_id = $struct->getID();
        $this->type = $struct->getType();
    }

    /**
     * Create a new Datafield
     *
     * @param $type static types for datafields
     */
    public function new_action($type = null)
    {
        if (Request::submitted('anlegen')) {
            if (Request::get('datafield_name')) {
                $datafield_id = md5(uniqid(Request::get('datafield_name').time()));
                $struct = new DataFieldStructure(compact('datafield_id'));
                $struct->setName(Request::get('datafield_name'));
                $struct->setObjectType($type);
                $struct->setObjectClass(array_sum(Request::getArray('object_class')));
                $struct->setEditPerms(Request::get('edit_perms'));
                $struct->setViewPerms(Request::get('visibility_perms'));
                $struct->setPriority(Request::get('priority'));
                $struct->setType(Request::get('datafield_typ'));
                $struct->setIsUserfilter(Request::int('is_userfilter'));
                if ($type === 'sem') {
                    $struct->setDescription(Request::get('description'));
                    $struct->setIsRequired(Request::get('is_required'));
                }
                $struct->store();

                PageLayout::postSuccess(_('Das neue generische Datenfeld wurde angelegt.'));
                $this->redirect('admin/datafields/index/'.$struct->getObjectType().'#item_'.$struct->getID());
            } else {
                PageLayout::postError(_('Es wurde keine Bezeichnung eingetragen!'));
            }
        }

        $type = $type ?: Request::get('datafield_typ');

        $this->type_name  = $this->allclasses[$type];
        $this->object_typ = $type;

        if (!$this->object_typ) {
            $this->render_action('type_select');
        }
    }

    /**
     * Delete a datafield
     *
     * @param md5 $datafield_id
     * @param string $name
     */
    public function delete_action($datafield_id)
    {
        $struct = new DataFieldStructure(compact('datafield_id'));
        $struct->load();
        $type = $struct->getObjectType();
        $name = $struct->getName();
        if (Request::int('delete') == 1) {
            $struct->remove();

            PageLayout::postSuccess(_('Das Datenfeld wurde erfolgreich gelöscht!'));
        } elseif (!Request::get('back')) {
            $this->datafield_id = $datafield_id;
            $this->flash['delete'] = compact('datafield_id', 'name');
        }

        $this->redirect('admin/datafields/index/'.$type.'#'.$type);
    }

    /**
     * Configures a datafield
     *
     * @param String $datafield_id Datafield id
     */
    public function config_action($datafield_id)
    {
        $struct = new DataFieldStructure(compact('datafield_id'));
        $struct->load();

        if (Request::get('typeparam')) {
            $struct->setTypeParam(Request::get('typeparam'));
        }

        if (Request::isPost() && Request::submitted('store')) {
            $struct->store();

            PageLayout::postSuccess(_('Die Parameter wurden übernommen.'));

            $this->redirect('admin/datafields/index/'.$struct->getObjectType().'#item_'.$datafield_id);
        }

        $this->struct = $struct;

        if (Request::submitted('preview')) {
            $this->preview = DataFieldEntry::createDataFieldEntry($struct);
            $this->render_action('preview');
        }
    }

    /**
     * Creates the sidebar.
     *
     * @param String $action Currently called action
     */
    private function createSidebar($action)
    {
        $sidebar = Sidebar::Get();
        $sidebar->setImage('sidebar/admin-sidebar.png');
        $sidebar->setTitle(_('Datenfelder'));

        $actions = new ActionsWidget();
        $actions->addLink(_('Neues Datenfeld anlegen'),
                          $this->url_for('admin/datafields/new/' . $this->class_filter),
                          'icons/blue/add.svg')
                ->asDialog();
        $sidebar->addWidget($actions);

        $filter = new SelectWidget(_('Filter'), $this->url_for('admin/datafields'), 'class_filter');
        $filter->addElement(new SelectElement('', _('alle anzeigen')));
        $filter->setOptions($this->allclasses, $this->class_filter);
        $sidebar->addWidget($filter);
    }
}
