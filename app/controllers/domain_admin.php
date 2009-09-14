<?php
# Lifter007: TODO
# Lifter003: TODO
/*
 * domain_admin.php - user domain admin controller
 *
 * Copyright (c) 2008  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/functions.php';
require_once 'lib/visual.inc.php';
require_once 'lib/classes/UserDomain.php';

class DomainAdminController extends Trails_Controller
{
    /**
     * common tasks for all actions
     */
    function before_filter (&$action, &$args)
    {
        global $perm, $template_factory, $CURRENT_PAGE, $HELP_KEYWORD;
        global $_language_path, $_language;

        # open session
        page_open(array('sess' => 'Seminar_Session',
                        'auth' => 'Seminar_Auth',
                        'perm' => 'Seminar_Perm',
                        'user' => 'Seminar_User'));

        # set up language prefs
        $_language_path = init_i18n($_language);

        # user must have root permission
        $perm->check('root');

        # set page title and navigation
        $layout = $template_factory->open('layouts/base');
        $layout->infobox = $this->infobox_content();
        $this->set_layout($layout);

        $CURRENT_PAGE = _('Nutzerdom�nen');
        $HELP_KEYWORD = 'Admins.Nutzerdomaenen';
        Navigation::activateItem('/admin/config/user_domains');

        # fetch user domain
        $this->domains = UserDomain::getUserDomains();
    }

    /**
     * common tasks for all actions
     */
    function after_filter ($action, $args)
    {
        page_close();
    }

    /**
     * Display the list of user domains.
     */
    function show_action ()
    {
    }

    /**
     * Create a new user domain.
     */
    function new_action ()
    {
        $this->render_action('edit');
    }

    /**
     * Edit an existing user domain.
     */
    function edit_action ($id)
    {
        $this->edit_id = $id;
    }

    /**
     * Save changes to a user domain.
     */
    function save_action ()
    {
        $id = remove_magic_quotes($_REQUEST['id']);
        $name = remove_magic_quotes($_REQUEST['name']);

        try {
            $domain = new UserDomain($id);
            $old_name = $domain->getName();

            if (isset($_REQUEST['new_domain']) && isset($old_name)) {
                throw new Exception(_('Diese ID wird bereits verwendet'));
            }

            $domain->setName($name);
            $domain->store();
        } catch (Exception $ex) {
            $this->error_msg = $ex->getMessage();
        }

        $this->domains = UserDomain::getUserDomains();
        $this->render_action('show');
    }

    /**
     * Delete an existing user domain.
     */
    function delete_action ($id)
    {
        $domain = new UserDomain($id);

        if (count($domain->getUsers()) == 0) {
            $domain->delete();
        } else {
            $this->error_msg = _('Dom�nen, denen noch Nutzer zugewiesen sind, k�nnen nicht gel�scht werden.');
        }

        $this->domains = UserDomain::getUserDomains();
        $this->render_action('show');
    }

    /**
     * Get contents of the info box for this action.
     */
    function infobox_content ()
    {
        $infobox_content = array(
            array(
                'kategorie' => _('Nutzerdom�nen verwalten'),
                'eintrag'   => array(array(
                    'icon' => 'add_sheet.gif',
                    'text' => '<a href="'.$this->url_for('domain_admin/new').'">'._('Neue Nutzerdom�ne anlegen').'</a>'
                ))
            ), array(
                'kategorie' => _('Informationen'),
                'eintrag'   => array(array(
                    'icon' => 'info.gif',
                    'text' => sprintf(_('In der Stud.IP-Hilfe finden Sie %sHinweise zur Verwendung von Nutzerdom�nen%s.'),
                                        '<a href="'.format_help_url('Admins.Nutzerdomaenen').'">', '</a>')
                ))
            )
        );

        return array('picture' => 'browse.jpg', 'content' => $infobox_content);
    }
}
?>
