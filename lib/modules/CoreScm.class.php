<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreScm implements StudipModule {
    
    function getIconNavigation($course_id, $last_visit, $user_id) {
        if (get_config('SCM_ENABLE')) {
            $navigation = new Navigation(_('Ablaufplan'), URLHelper::getURL("seminar_main.php", array('auswahl' => $course_id, 'redirect_to' => "dispatch.php/course/dates")));
            $navigation->setImage('icons/16/grey/schedule.png');
            return $navigation;
        } else {
            return null;
        }
    }
    
    function getTabNavigation($course_id) {
        if (get_config('SCM_ENABLE')) {
            $temp = StudipScmEntry::findByRange_id($course_id, 'ORDER BY position ASC');
            $scms = SimpleORMapCollection::createFromArray($temp);

            $navigation = new Navigation($scms->first()->tab_name ?: _('Informationen'));
            $navigation->setImage('icons/16/white/infopage.png');
            $navigation->setActiveImage('icons/16/black/infopage.png');

            foreach ($scms as $scm) {
                $scm_link = 'dispatch.php/course/scm/' . $scm->id;
                $nav = new Navigation($scm['tab_name'], $scm_link);
                $navigation->addSubNavigation($scm->id, $nav);
            }

            return array('scm' => $navigation);
        } else {
            return null;
        }
    }

    /** 
     * @see StudipModule::getMetadata()
     */ 
    function getMetadata()
    {
        return array(
            'summary' => _('Die Lehrenden bestimmen, wie Titel und Inhalt dieser Seite aussehen.'),
            'description' => _('Die Freie Informationsseite ist eine Seite, '.
                'die sich die Lehrenden nach ihren speziellen Anforderungen '.
                'einrichten k�nnen. So kann z.B. der Titel im Kartenreiter '.
                'selbst definiert werden. Ferner k�nnen beliebig viele '.
                'Eintr�ge im Untermen� vorgenommen werden. F�r jeden Eintrag '.
                '�ffnet sich eine Seite mit einem Text-Editor, in den '.
                'beliebiger Text eingegeben und formatiert werden kann. Oft '.
                'wird die Seite f�r die Angabe von Literatur genutzt als '.
                'Alternative zum Plugin Literatur. Sie kann aber auch f�r '.
                'andere beliebige Zusatzinformationen (Links, Protokolle '.
                'etc.) verwendet werden.'),
            'displayname' => _('Informationen'),
            'category' => _('Lehr- und Lernorganisation'),
            'keywords' => _('Raum f�r eigene Informationen;
                            Name des Reiters frei definierbar;
                            Beliebig erweiterbar durch zus�tzliche "neue Eintr�ge"'),
            'descriptionshort' => _('Freie Gestaltung von Reiternamen und Inhalten durch Lehrende.'),
            'descriptionlong' => _('Diese Seite kann von Lehrenden nach ihren speziellen Anforderungen eingerichtet werden. '.
                                    'So ist z.B. der Titel im Reiter frei definierbar. Ferner k�nnen beliebig viele neue '.
                                    'Eintragsseiten eingef�gt werden. F�r jeden Eintrag �ffnet sich eine Seite mit einem '.
                                    'Text-Editor, in den beliebiger Text eingef�ft, eingegeben und formatiert werden kann. '.
                                    'Oft wird die Seite f�r die Angabe von Literatur genutzt als Alternative zur Funktion '.
                                    'Literatur. Sie kann aber auch f�r andere beliebige Zusatzinformationen (Links, Protokolle '.
                                    'etc.) verwendet werden.'),
            'icon' => 'icons/16/black/infopage.png',
            'screenshots' => array(
                'path' => 'plus/screenshots/Freie_Informationsseite',
                'pictures' => array(
                    0 => array('source' => 'Zwei_Eintraege_mit_Inhalten_zur_Verfuegung_stellen.jpg', 'title' => _('Zwei Eintr�ge mit Inhalten zur Verf�gung stellen')),
                    1 => array( 'source' => 'Neue_Informationsseite_anlegen.jpg', 'title' => _('Neue Informationsseite anlegen'))
                )
            )       
        );
    }
}
