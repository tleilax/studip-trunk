<?php
/*
 * StartNavigation.php - navigation for start page
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class StartNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        global $user;

        parent::__construct(_('Start'), 'index.php');

        $db = DBManager::get();

        if (is_object($user) && $user->id != 'nobody') {
            $result = $db->query("SELECT COUNT(IF(chdate > IFNULL(b.visitdate, 0) AND nw.user_id !='{$user->id}', nw.news_id, NULL)) AS neue
                                    FROM news_range a LEFT JOIN news nw ON (a.news_id = nw.news_id AND UNIX_TIMESTAMP() BETWEEN date AND date + expire)
                                    LEFT JOIN object_user_visits b ON (b.object_id = nw.news_id AND b.user_id = '{$user->id}' AND b.type ='news')
                                    WHERE a.range_id = 'studip' GROUP BY a.range_id");
            $news = $result->fetchColumn();

            if (get_config('VOTE_ENABLE')) {
                $result = $db->query("SELECT COUNT(IF(chdate > IFNULL(b.visitdate, 0) AND a.author_id !='{$user->id}' AND a.state != 'stopvis', vote_id, NULL)) AS neue
                                        FROM vote a LEFT JOIN object_user_visits b ON (b.object_id = vote_id AND b.user_id = '{$user->id}' AND b.type='vote')
                                        WHERE a.range_id = 'studip' AND a.state IN('active', 'stopvis') GROUP BY a.range_id");
                $vote = $result->fetchColumn();

                $result = $db->query("SELECT COUNT(IF(chdate > IFNULL(b.visitdate, 0) AND d.author_id !='{$user->id}', a.eval_id, NULL)) AS neue
                                        FROM eval_range a INNER JOIN eval d ON (a.eval_id = d.eval_id AND d.startdate < UNIX_TIMESTAMP() AND
                                            (d.stopdate > UNIX_TIMESTAMP() OR d.startdate + d.timespan > UNIX_TIMESTAMP() OR (d.stopdate IS NULL AND d.timespan IS NULL)))
                                        LEFT JOIN object_user_visits b ON (b.object_id = d.eval_id AND b.user_id = '{$user->id}' AND b.type='eval')
                                        WHERE a.range_id = 'studip' GROUP BY a.range_id");
                $vote += $result->fetchColumn();
            }
        }

        $homeinfo = _('Zur Startseite');
        $homeinfo .= $news ? ' - ' . sprintf(_('%s neue News'), $news) : '';
        $homeinfo .= $vote ? ' - ' . sprintf(_('%s neue Umfrage(n)'), $vote) : '';
        $homeimage = $vote + $news ? 'header_home_red' : 'header_home';

        $this->setImage($homeimage, array('title' => $homeinfo));
    }

    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $perm;

        parent::initSubNavigation();

        // my courses
	if ($perm->have_perm('root')) {
            $navigation = new Navigation(_('Veranstaltungs�bersicht'), 'sem_portal.php');
	} else if ($perm->have_perm('admin')) {
            $navigation = new Navigation(_('Veranstaltungen an meinen Einrichtungen'), 'meine_seminare.php');
        } else {
            $navigation = new Navigation(_('Meine Veranstaltungen'), 'meine_seminare.php');

            if (!$perm->have_perm('dozent')) {
                $navigation->addSubNavigation('browse', new Navigation(_('Veranstaltung hinzuf�gen'), 'sem_portal.php'));

                if ($perm->have_perm('autor') && get_config('STUDYGROUPS_ENABLE')) {
                    $navigation->addSubNavigation('new_studygroup', new Navigation(_('Studiengruppe anlegen'), 'dispatch.php/course/studygroup/new'));
                }
            }
        }

        $this->addSubNavigation('my_courses', $navigation);

        // course administration
        if ($perm->have_perm('dozent')) {
            $navigation = new Navigation(_('Verwaltung von Veranstaltungen'), 'adminarea_start.php?list=TRUE');

            if ($perm->have_perm(get_config('SEM_CREATE_PERM'))) {
                $navigation->addSubNavigation('new_course', new Navigation(_('neue Veranstaltung anlegen'), 'admin_seminare_assi.php?new_session=TRUE'));
            }

            if (get_config('STUDYGROUPS_ENABLE')) {
                $navigation->addSubNavigation('new_studygroup', new Navigation(_('Studiengruppe anlegen'), 'dispatch.php/course/studygroup/new'));
            }

            $this->addSubNavigation('admin_course', $navigation);
        }

        // insitute administration
	if ($perm->have_perm('admin')) {
            $navigation = new Navigation(_('Verwaltung von Einrichtungen'), 'admin_institut.php?list=TRUE');
            $this->addSubNavigation('admin_inst', $navigation);
        }

        // user administration
	if ($perm->have_perm('root')) {
            $navigation = new Navigation(_('Verwaltung globaler Einstellungen'), 'new_user_md5.php');
            $this->addSubNavigation('admin_user', $navigation);
	} else if ($perm->have_perm('admin') && !get_config('RESTRICTED_USER_MANAGEMENT')) {
            $navigation = new Navigation(_('globale Benutzerverwaltung'), 'new_user_md5.php');
            $this->addSubNavigation('admin_user', $navigation);
	}

        // calendar / home page
        if (!$perm->have_perm('admin')) {
            $navigation = new Navigation(_('Mein Planer'));
            $navigation->addSubNavigation('calendar', new Navigation(_('Terminkalender'), 'calendar.php'));
            $navigation->addSubNavigation('address_book', new Navigation(_('Adressbuch'), 'contact.php'));
            $navigation->addSubNavigation('schedule', new Navigation(_('Stundenplan'), 'mein_stundenplan.php'));
            $this->addSubNavigation('messaging', $navigation);

            $navigation = new Navigation(_('pers�nliche Homepage'), 'about.php');

            if ($perm->have_perm('autor')) {
                $navigation->addSubNavigation('settings', new Navigation(_('individuelle Einstellungen'), 'edit_about.php?view=allgemein'));
            }

            $this->addSubNavigation('homepage', $navigation);
        }

        // module administration
        if ($perm->have_perm('dozent') && get_config('STM_ENABLE')) {
            $navigation = new Navigation(_('Studienmodule'), 'auswahl_module.php');
            $this->addSubNavigation('admin_modules', $navigation);
        }

        // load additional plugins
        PluginEngine::getPlugins('SystemPlugin');

        if ($perm->have_perm('admin')) {
            PluginEngine::getPlugins('AdministrationPlugin');
        }

        // global search
        $navigation = new Navigation(_('Suchen'), 'auswahl_suche.php');
        $navigation->addSubNavigation('user', new Navigation(_('Personensuche'), 'browse.php'));
        $navigation->addSubNavigation('course', new Navigation(_('Veranstaltungssuche'), 'sem_portal.php'));
        $this->addSubNavigation('search', $navigation);

        // external help
	if (get_config('EXTERNAL_HELP')) {
            $navigation = new Navigation(_('Hilfe'), format_help_url('Basis.Allgemeines'));
            $navigation->addSubNavigation('intro', new Navigation(_('Schnelleinstieg'), format_help_url('Basis.SchnellEinstiegKomplett')));
            $this->addSubNavigation('help', $navigation);
	}
    }
}
