<?php
# Lifter007: TODO
/*
 * event_log.php - event logging admin controller
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'app/models/event_log.php';

class EventLogController extends Trails_Controller
{
    private $event_log;

    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory, $_language_path, $_language;

        // open session
        page_open(array('sess' => 'Seminar_Session',
                        'auth' => 'Seminar_Auth',
                        'perm' => 'Seminar_Perm',
                        'user' => 'Seminar_User'));

        // set up language prefs
        $_language_path = init_i18n($_language);

        // user must have root permission
        $perm->check('root');

        // set page title and navigation
        $layout = $template_factory->open('layouts/base_without_infobox');
        $layout->tabs = 'links_admin';
        $this->set_layout($layout);

        if ($action === 'show') {
            $GLOBALS['CURRENT_PAGE'] = _('Anzeige der Log-Events');
            $this->layout->reiter_view = 'show_log';
        } else {
            $GLOBALS['CURRENT_PAGE'] = _('Konfiguration der Logging-Funktionen');
            $this->layout->reiter_view = 'admin_log';
        }

        $this->event_log = new EventLog();
    }

    /**
     * common tasks for all actions
     */
    function after_filter ($action, $args)
    {
        page_close();
    }

    /**
     * show and search log events
     */
    function show_action ()
    {
        $this->action_id = $_REQUEST['action_id'];
        $this->object_id = $_REQUEST['object_id'];
        $this->log_actions = $this->event_log->get_used_log_actions();
        $this->types = $this->event_log->get_object_types();

        // restrict log events to object scope
        if (isset($_REQUEST['search']) && $_REQUEST['search'] != '') {
            $this->type = remove_magic_quotes($_REQUEST['type']);
            $this->search = remove_magic_quotes($_REQUEST['search']);
            $objects = $this->event_log->find_objects($this->type, $this->search);

            if (count($objects) > 0) {
                $this->objects = $objects;
            } else {
                $this->error_msg = _('Kein passendes Objekt gefunden.');
            }
        }

        // find all matching log events
        if ($_REQUEST['search'] === '' || isset($this->object_id)) {
            $this->start = (int) $_REQUEST['start'];
            $this->format = $_REQUEST['format'];
            $this->num_entries =
                $this->event_log->count_log_events($this->action_id, $this->object_id);

            if (isset($_REQUEST['back']) || $_REQUEST['back_x']) {
                $this->start = max(0, $this->start - 50);
            } else if (isset($_REQUEST['forward']) || $_REQUEST['forward_x']){
                $this->start = min($this->num_entries, $this->start + 50);
            }

            $this->log_events =
                $this->event_log->get_log_events($this->action_id, $this->object_id, $this->start);
        }
    }

    /**
     * configure log action
     */
    function admin_action ()
    {
        $this->log_actions = $this->event_log->get_log_actions();
    }

    /**
     * edit an existing log action
     */
    function edit_action ($action_id)
    {
        $this->edit_id = $action_id;
        $this->log_actions = $this->event_log->get_log_actions();
        $this->render_action('admin');
    }

    /**
     * save changes to a log action
     */
    function save_action ($action_id)
    {
        $description = remove_magic_quotes($_REQUEST['description']);
        $info_template = remove_magic_quotes($_REQUEST['info_template']);
        $active = $_REQUEST['active'] ? 1 : 0;
        $expires = (int) $_REQUEST['expires'] * 86400;

        try {
            $this->event_log->update_log_action($action_id, $description,
                                                $info_template, $active, $expires);
        } catch (InvalidArgumentException $ex) {
            $this->error_msg = $ex->getMessage();
        }

        $this->log_actions = $this->event_log->get_log_actions();
        $this->render_action('admin');
    }
}
?>
