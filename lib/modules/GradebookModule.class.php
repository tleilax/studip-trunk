<?php

use Grading\Definition;
use Grading\Instance;

/**
 * GradebookModule.class.php - Gradebook API for Stud.IP.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      <mlunzena@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class GradebookModule extends StudIPPlugin implements SystemPlugin, StandardPlugin
{
    public function __construct()
    {
        parent::__construct();

        NotificationCenter::on('UserDidDelete', function ($event, $user) {
            Instance::deleteBySQL('user_id = ?', [$user->id]);
        });
        NotificationCenter::on('CourseDidDelete', function ($event, $course) {
            Definition::deleteBySQL('course_id = ?', [$course->id]);
        });
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getInfoTemplate($courseId)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIconNavigation($courseId, $lastVisit, $userId)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function getTabNavigation($cid)
    {
        if ('nobody' === $GLOBALS['user']->id) {
            return [];
        }

        $gradebook = new Navigation('Gradebook');
        $gradebook->addSubNavigation('index', new Navigation(_('Übersicht'), 'dispatch.php/course/gradebook/overview'));

        if ($GLOBALS['perm']->have_studip_perm('dozent', $cid)) {
            $this->addTabNavigationOfLecturers($gradebook, $cid);
        } elseif ($GLOBALS['perm']->have_studip_perm('student', $cid)) {
            $this->addTabNavigationOfStudents($gradebook, $cid);
        }

        return compact('gradebook');
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function addTabNavigationOfLecturers(\Navigation $navigation, $cid)
    {
        $navigation->addSubNavigation(
            'export',
            new Navigation(_('Export'), 'dispatch.php/course/gradebook/lecturers/export')
        );
        $navigation->addSubNavigation(
            'weights',
            new Navigation(_('Gewichtungen'), 'dispatch.php/course/gradebook/lecturers/weights')
        );
        $navigation->addSubNavigation(
            'custom_definitions',
            new Navigation(_('Noten manuell erfassen'), 'dispatch.php/course/gradebook/lecturers/custom_definitions')
        );
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function addTabNavigationOfStudents(\Navigation $navigation, $cid)
    {
        $navigation->addSubNavigation(
            'export',
            new Navigation(_('Export'), 'dispatch.php/course/gradebook/students/export')
        );
    }

    /**
     * Provides metadata like a descriptional text for this module that
     * is shown on the course "+" page to inform users about what the
     * module acutally does. Additionally, a URL can be specified.
     *
     * @return array metadata containg description and/or url
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Noten- und Fortschrittserfassung (Gradebook)'),
            'description' => _('Dieses Modul ermöglicht die manuelle und automatische Erfassung von Noten und Leistungen.'),
            'category' => _('Lehr- und Lernorganisation'),
            'keywords' => _('automatische und manuelle Erfassung von gewichteten Leistungen;Export von Leistungen;persönliche Fortschrittskontrolle'),
            'icon' => Icon::create('assessment', 'info'),
            'screenshots' => [
                'path' => '../../assets/images/plus/screenshots/Gradebook',
                'pictures' => [
                    [
                        'source' => 'Lehrendensicht.png',
                        'title' => 'Beispiel für das Gradebook aus der Sicht der Lehrenden',
                    ],
                    [
                        'source' => 'Studierendensicht.png',
                        'title' => 'Beispiel für das Gradebook aus der Sicht der Studierenden',
                    ],
                ],
            ],
        ];
    }
}
