<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreDocuments implements StudipModule
{

    function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $navigation = new Navigation(_('Dateibereich'), "seminar_main.php?auswahl=$course_id&redirect_to=folder.php");
        $navigation->setImage(Icon::create('files', 'inactive'));
        return $navigation;
    }

    function getTabNavigation($course_id)
    {
        $range_type = get_object_type($course_id, ['sem', 'inst']) == 'sem' ? 'course' : 'institute';
        $newFilesNavigation = new Navigation(_('Dateien'), 'dispatch.php/' . $range_type . '/files');
        $newFilesNavigation->setImage(Icon::create('files', 'info_alt'));
        $newFilesNavigation->setActiveImage(Icon::create('files', 'info'));
        return array('files' => $newFilesNavigation);
    }

    /**
     * @see StudipModule::getMetadata()
     */
    function getMetadata()
    {
        return array(
            'summary'          => _('Austausch von Dateien'),
            'description'      => _('Im Dateibereich k�nnen Dateien sowohl von ' .
                'Lehrenden als auch von Studierenden hoch- bzw. ' .
                'heruntergeladen werden. Es k�nnen Ordner angelegt und ' .
                'individuell benannt werden (nur Lehrende). Die Dateien ' .
                'k�nnen somit strukturiert zur Verf�gung gestellt werden. ' .
                'Multimediadateien wie Grafiken, Audio- und Videodateien ' .
                'k�nnen sofort angezeigt bzw. abgespielt werden. �ber das ' .
                'PlugIn "Dateiordnerberechtigung" k�nnen Im Dateibereich ' .
                'bestimmte Rechte f�r Studierende, wie z.B. das Leserecht, ' .
                'festgelegt werden.'),
            'displayname'      => _('Dateien'),
            'category'         => _('Lehr- und Lernorganisation'),
            'keywords'         => _('Hoch- und Herunterladen von Dateien;
                            Anlegen von Ordnern und Unterordnern;
                            Verschieben einer Datei/eines Ordners per drag and drop innerhalb einer Veranstaltung;
                            Verschieben einer Datei/eines Ordners in eine andere Veranstaltung;
                            Kopieren einer Datei/eines Ordners in eine andere oder mehrere Veranstaltungen;
                            Verlinkung auf abgelegte Dateien m�glich;
                            Erstellung Hausaufgabenordner durch Aktivierung der Funktion "Dateiordnerberechtigung"'),
            'descriptionshort' => _('Austausch von Dateien'),
            'descriptionlong'  => _('Dateien k�nnen sowohl von Lehrenden als auch von Studierenden hoch- bzw. ' .
                'heruntergeladen werden. Ordner k�nnen angelegt und individuell benannt werden ' .
                '(Standard: nur Lehrende), so dass Dateien strukuriert zur Verf�gung gestellt ' .
                'werden k�nnen. Multimediadateien wie Grafiken, Audio- und Videodateien werden ' .
                'sofort angezeigt bzw. abspielbar dargestellt. �ber das PlugIn "Dateiordnerberechtigungen" ' .
                'k�nnen Im Dateibereich bestimmte Rechte (r, w, x, f) f�r Studierende, wie z.B. das ' .
                'Leserecht (r), festgelegt werden.'),
            'icon'             => Icon::create('files', 'info'),
            'screenshots'      => array(
                'path'     => 'plus/screenshots/Dateibereich_-_Dateiordnerberechtigung',
                'pictures' => array(
                    0 => array('source' => 'Ordneransicht_mit_geoeffnetem_Ordner.jpg', 'title' => _('Ordneransicht mit ge�ffnetem Ordner')),
                    1 => array('source' => 'Ordneransicht_mit_Dateiinformationen.jpg', 'title' => _('Ordneransicht mit Dateiinformationen')),
                    2 => array('source' => 'Neuen_Ordner_erstellen.jpg', 'title' => _('Neuen Ordner erstellen')),
                    3 => array('source' => 'Ordner_zum_Hausaufgabenordner_umwandeln.jpg', 'title' => _('Ordner zum Hausaufgabenordner umwandeln')),
                    4 => array('source' => 'Ansicht_alle_Dateien.jpg', 'title' => _('Ansicht alle Dateien'))
                )
            )
        );
    }
}
