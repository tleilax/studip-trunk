<?php
/*
 * ProfilNavigation.php - navigation for user profile page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

require_once 'lib/edit_about.inc.php';

/**
 * This is the new profile page instead of the old homepage
 * It includes all informations and functions for a user
 *
 */
class ProfileNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        global $user, $auth, $homepage_cache_own, $LastLogin;

        parent::__construct(_('Profil'));

        $db = DBManager::get();
        $time = $homepage_cache_own ? $homepage_cache_own : $LastLogin;

        $result = $db->query("SELECT COUNT(post_id) AS count FROM guestbook
                                WHERE range_id = '".$user->id."'
                                AND user_id != '".$user->id."'
                                AND mkdate > '".$time."'");

        $count = $result->fetchColumn();

        if ($count > 0) {
            $hp_txt = _('Zu Ihrer Profilseite') . ', ' .
                sprintf(ngettext('Sie haben %d neuen Eintrag im G�stebuch.',
                                 'Sie haben %d neue Eintr�ge im G�stebuch.', $count), $count);
            $picture = 'icons/32/blue/new/profile.png';
            $hp_link = 'about.php?guestbook=open#guest';
        } else {
            $hp_txt = _('Zu Ihrer Profilseite');
            $picture = 'icons/32/blue/profile.png';
            $hp_link = 'about.php';
        }

        $hp_txt .= sprintf(' (%s, %s)', $auth->auth['uname'], $auth->auth['perm']);
        $this->setURL($hp_link);
        $this->setImage($picture, array('title' => $hp_txt));
    }

    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        $active = parent::isActive();

        if ($active) {
            URLHelper::addLinkParam('username', Request::get('username'));
        }

        return $active;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $auth;

        parent::initSubNavigation();

        $username = Request::get('username', $auth->auth['uname']);

        // this really should not be here
        $username = preg_replace('/[^\w@.-]/', '', $username);

        $my_about = new about($username, NULL);
        $my_about->get_user_details();

        // main profile
        $this->addSubNavigation('view', new Navigation(_('Profil'), 'about.php'));

        // avatar
        $this->addSubNavigation('avatar', new Navigation(_('Bild'), 'edit_about.php', array('view' => 'Bild')));

        // profile data
        $navigation = new Navigation(_('Nutzerdaten'));
        $navigation->addSubNavigation('profile', new Navigation(_('Allgemein'), 'edit_about.php', array('view' => 'Daten')));
        $navigation->addSubNavigation('private', new Navigation(_('Privat'), 'edit_about.php', array('view' => 'Lebenslauf')));

        if ($my_about->auth_user['perms'] != 'admin' && $my_about->auth_user['perms'] != 'root') {
            $navigation->addSubNavigation('study_data', new Navigation(_('Studiendaten'), 'edit_about.php', array('view' => 'Studium')));
        }

        if ($my_about->auth_user['perms'] != 'root') {
            if (count(UserDomain::getUserDomains())) {
                $navigation->addSubNavigation('user_domains', new Navigation(_('Nutzerdom�nen'), 'edit_about.php', array('view' => 'userdomains')));
            }

            if ($my_about->special_user) {
                $navigation->addSubNavigation('inst_data', new Navigation(_('Einrichtungsdaten'), 'edit_about.php', array('view' => 'Karriere')));
            }
        }
        $this->addSubNavigation('edit', $navigation);

        // user defined sections
        $this->addSubNavigation('sections', new Navigation(_('Kategorien'), 'edit_about.php', array('view' => 'Sonstiges')));
    }
}
