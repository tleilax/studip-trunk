<?php
/*
 * LoginNavigation.php - navigation for login page
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class LoginNavigation extends Navigation
{
    public function __construct()
    {
        parent::__construct(_('Login'));
    }

    public function initSubNavigation()
    {
        parent::initSubNavigation();

        $navigation = new Navigation(_('Login - f�r registrierte NutzerInnen'), 'index.php?again=yes');
        $this->addSubNavigation('login', $navigation);

        if (in_array('CAS', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
            $navigation = new Navigation(_('Login - f�r Single Sign On mit CAS'), 'index.php?again=yes&sso=cas');
            $this->addSubNavigation('login_cas', $navigation);
        }

        if (in_array('Shib', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
            $navigation = new Navigation(_('Shibboleth Login - f�r Single Sign On mit Shibboleth'), 'index.php?again=yes&sso=shib');
            $this->addSubNavigation('login_shib', $navigation);
        }

        if (get_config('ENABLE_SELF_REGISTRATION')) {
            $navigation = new Navigation(_('Registrieren - um NutzerIn zu werden'), 'register1.php');
            $this->addSubNavigation('register', $navigation);
        }

        if (get_config('ENABLE_FREE_ACCESS')) {
            $navigation = new Navigation(_('Freier Zugang - ohne Registrierung'), 'freie.php');
            $this->addSubNavigation('browse', $navigation);
        }

        if (get_config('EXTERNAL_HELP')) {
            $navigation = new Navigation(_('Hilfe - zu Bedienung und Funktionsumfang'), format_help_url('Basis.Allgemeines'));
            $this->addSubNavigation('help', $navigation);
        }
    }
}
