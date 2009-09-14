<?php
/*
 * MessagingNavigation.php - navigation for messaging area
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/sms_functions.inc.php';

class MessagingNavigation extends Navigation
{
    public function __construct()
    {
        global $user;

        parent::__construct(_('Post'));

        $neum = count_messages_from_user('in', ' AND message_user.readed = 0 ');
        $altm = count_messages_from_user('in', ' AND message_user.readed = 1 ');
        $neux = count_x_messages_from_user('in', 'all',
            'AND mkdate > '.(int)$my_messaging_settings['last_box_visit'].' AND message_user.readed = 0 ');

        $icon = $neum ? 'header_nachricht2' : 'header_nachricht';

        if ($neux > 0) {
            $tip = sprintf(ngettext(_('Sie haben %d neue ungelesene Nachricht'),
                                    _('Sie haben %d neue ungelesene Nachrichten'), $neux), $neux);
        } else if ($neum > 1) {
            $tip = sprintf(ngettext(_('Sie haben %d ungelesene Nachricht'),
                                    _('Sie haben %d ungelesene Nachrichten'), $neum), $neum);
        } else if ($altm > 1) {
            $tip = sprintf(ngettext(_('Sie haben %d alte empfangene Nachricht'),
                                    _('Sie haben %d alte empfangene Nachrichten'), $altm), $altm);
        } else {
            $tip = _('Sie haben keine alten empfangenen Nachrichten');
        }

        $this->setURL('sms_box.php?sms_inout=in');
        $this->setImage($icon, array('title' => $tip));
    }

    public function initSubNavigation()
    {
        global $perm;

        parent::initSubNavigation();

        if (!$perm->have_perm('admin')) {
            if (get_config('CALENDAR_ENABLE')) {
                $navigation = new Navigation(_('Terminkalender'), 'calendar.php');
                $navigation->addSubNavigation('day', new Navigation(_('Tag'), 'calendar.php', array('cmd' => 'showday')));
                $navigation->addSubNavigation('week', new Navigation(_('Woche'), 'calendar.php', array('cmd' => 'showweek')));
                $navigation->addSubNavigation('month', new Navigation(_('Monat'), 'calendar.php', array('cmd' => 'showmonth')));
                $navigation->addSubNavigation('year', new Navigation(_('Jahr'), 'calendar.php', array('cmd' => 'showyear')));
                $navigation->addSubNavigation('edit', new Navigation(_('Termin anlegen/bearbeiten'), 'calendar.php', array('cmd' => 'edit')));
                $navigation->addSubNavigation('course', new Navigation(_('Veranstaltungstermine'), 'calendar.php', array('cmd' => 'bind')));
                $navigation->addSubNavigation('export', new Navigation(_('Export/Sync'), 'calendar.php', array('cmd' => 'export')));
                $navigation->addSubNavigation('settings', new Navigation(_('Ansicht anpassen'), 'calendar.php', array('cmd' => 'changeview')));
                $this->addSubNavigation('calendar', $navigation);
            }

            $navigation = new Navigation(_('Stundenplan'), 'mein_stundenplan.php');
            $this->addSubNavigation('schedule', $navigation);
        }

        $navigation = new Navigation(_('Adressbuch'));
        $navigation->addSubNavigation('alpha', new Navigation(_('Alphabetisch'), 'contact.php', array('view' => 'alpha')));
        $navigation->addSubNavigation('gruppen', new Navigation(_('Gruppenansicht'), 'contact.php', array('view' => 'gruppen')));
        $navigation->addSubNavigation('admin_groups', new Navigation(_('Gruppenverwaltung'), 'contact_statusgruppen.php'));
        $navigation->addSubNavigation('export', new Navigation(_('VCF-Export'), 'contact_export.php'));
        $this->addSubNavigation('address_book', $navigation);

        $navigation = new Navigation(_('Nachrichten'));
        $navigation->addSubNavigation('in', new Navigation(_('empfangene'), 'sms_box.php', array('sms_inout' => 'in')));
        $navigation->addSubNavigation('out', new Navigation(_('gesendete'), 'sms_box.php', array('sms_inout' => 'out')));
        $navigation->addSubNavigation('write', new Navigation(_('Neue Nachricht schreiben'), 'sms_send.php?cmd=new'));
        $navigation->addSubNavigation('settings', new Navigation(_('Messaging anpassen'), 'sms_box.php', array('change_view' => 'TRUE')));
        $this->addSubNavigation('message', $navigation);

        if (get_config('CHAT_ENABLE')) {
            $navigation = new Navigation(_('Chat'), 'chat_online.php');
            $this->addSubNavigation('chat', $navigation);
        }

        $navigation = new Navigation(_('Online'));
        $navigation->addSubNavigation('who', new Navigation(_('Wer ist online?'), 'online.php'));
        $navigation->addSubNavigation('settings', new Navigation(_('Messaging anpassen'), 'online.php', array('change_view' => 'TRUE')));
        $this->addSubNavigation('online', $navigation);
    }
}
