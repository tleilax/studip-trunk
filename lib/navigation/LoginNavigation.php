<?php
/*
 * LoginNavigation.php - navigation for login page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class LoginNavigation extends Navigation
{
    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        $navigation = new Navigation(_('Login'), 'index.php?again=yes');
        $navigation->setDescription(_('f�r registrierte NutzerInnen'));
        $this->addSubNavigation('login', $navigation);

        if (in_array('CAS', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
            $navigation = new Navigation(_('Login'), 'index.php?again=yes&sso=cas');
            $navigation->setDescription(_('f�r Single Sign On mit CAS'));
            $this->addSubNavigation('login_cas', $navigation);
        }

        if (in_array('Shib', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
            $navigation = new Navigation(_('Shibboleth Login'), 'index.php?again=yes&sso=shib');
            $navigation->setDescription(_('f�r Single Sign On mit Shibboleth'));
            $this->addSubNavigation('login_shib', $navigation);
        }

        if (get_config('ENABLE_SELF_REGISTRATION')) {
            $navigation = new Navigation(_('Registrieren'), 'register1.php');
            $navigation->setDescription(_('um NutzerIn zu werden'));
            $this->addSubNavigation('register', $navigation);
        }

        if (get_config('ENABLE_FREE_ACCESS')) {
            $navigation = new Navigation(_('Freier Zugang'), 'freie.php');
            $navigation->setDescription(_('ohne Registrierung'));
            $this->addSubNavigation('browse', $navigation);
        }

        if (get_config('EXTERNAL_HELP')) {
            $navigation = new Navigation(_('Hilfe'), format_help_url('Basis.Allgemeines'));
            $navigation->setDescription(_('zu Bedienung und Funktionsumfang'));
            $this->addSubNavigation('help', $navigation);
        }
    }
}
