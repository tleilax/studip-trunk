<?php

/*
 * archive.php - contains Course_ArchiveController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5.alpha-svn
 */

//rewrite of /public/archiv_assi.php

require_once('lib/archiv.inc.php'); //needed in archive_action


/**
    Course_ArchiveController is a controller that allows users
    who have the required permissions to archive a course.
    
    It replaces the old script at /public/archiv_assi.php.
*/
class Course_ArchiveController extends AuthenticatedController
{
    /**
        This method checks if the current user has the required
        permissions to archive a course.
    */
    private function userHasPermission($courseId = null)
    {
        global $perm;
        //check permissions: user has to be an administrator of the course:
        $requiredPermission = 'admin';
        if(get_config('ALLOW_DOZENT_ARCHIV')) {
            //members of the "dozent" role may also archive the course:
            $requiredPermission = 'dozent';
        }
        return $perm->have_studip_perm($requiredPermission, $courseId);
    }
    
    
    /**
        A helper method that creates an HTML table out of the course's data.
        This method exists for compatibility reasons with public/archiv.php
        (which displays the output created here).
    */
    private function createArchivedCourseHTMLTable($course = null)
    {
        $table = '<table class="default">'
               . '<caption>' . $course->name . '</caption>'
               . '<tbody>'
               . '<tr><th>' . _("Untertitel") . ':</th><td>' . $course->untertitel . '</td></tr>'
               //. '<tr><th>' . _("Zeit") . ':</th><td>' . INSERT_ZEIT_HERE . '</td></tr>'
               . '<tr><th>' . _("Semester") . ':</th><td>' . $course->start_semester . '</td></tr>' //TODO: check if start_semester is right
               . '<tr><th>' . _("Erster Temin") . ':</th><td>' . $course->untertitel . '</td></tr>'
               //. '<tr><th>' . _("Vorbesprechung") . ':</th><td>' . INSERT_VORBESPRECHUNG_HERE . '</td></tr>'
               . '<tr><th>' . _("Ort") . ':</th><td>' . $course->ort . '</td></tr>'
               . '<tr><th>' . _("Semester") . ':</th><td>' . $course->start_semester . '</td></tr>'
               . '<tr><th>' . _("Typ der Veranstaltung") . ':</th><td>'
                    . $course->start_semester . '</td></tr>';
        
        $table .= '</tbody></table>';
        return $table;
    }
    
    
    /**
        This action collects all required data about the course.
    */
    public function confirm_action()
    {
        //TODO: check in the preceding controller (admin/courses) if at least 
        //      one course is selected!
        //TODO: make it possible to archive more than one course at a time!
        
        global $perm;
        
        /*
            NOTE: confirm_action will be called from admin/courses
            with an array in HTTP POST that is called archiv_sem, 
            having the following form:
            [ "_id_courseID", "_id_courseID", "on", "_id_courseID", ...]
            
            Every courseID followed by "on" is an ID of a course
            that was selected for archiving.
        */
        
        //check the archiv_sem array and extract the relevant course IDs:
        $courseIds = array();
        
        $archiv_sem = Request::getArray('archiv_sem');
        
        for($i = 0; $i < count($archiv_sem); $i++) {
            if(($i > 0) && $archiv_sem[$i] == 'on') {
                //the previous array item is a relevant course ID:
                $id = explode('_', $archiv_sem[$i-1])[2];
                $courseIds[] = $id;
            }
        }
        //check if the user has the required permission
        //to archive the selected course:
        if (!$this->userHasPermission($courseId)) {
            //no permission: access denied!
            throw new AccessDeniedException();
        }
        
        //TODO: enable navigation items, depending whether the user
        // is in the admin role or not.
        
        
        //get the course object: TODO: resolve multiple course-IDs (to archive more than one course)
        
        
        $this->courses = Course::findMany($courseIds);
        
        //should be handled by the previous controller (admin/courses for example):
        /*if ($this->courses == false) {
            //courses not found!
            throw new Exception(_('Veranstaltungen nicht gefunden!'));
        }*/
        
        
        
        //activate navigation elements if they exist:
        if ($perm->have_perm('admin')) {
            if (Navigation::hasItem('/browse/my_courses/list')) {
                Navigation::activateItem('/browse/my_courses/list');
            }
        } else {
            if (Navigation::hasItem('/course/admin/main/archive')) {
                Navigation::activateItem('/course/admin/main/archive');
            }
        }
        
        //set the page title with the area of Stud.IP:
        PageLayout::setTitle(_('Archivieren von Veranstaltungen'));
        
        //get the list of "dozenten" for each course:
        $this->dozenten = array();
        
        foreach ($this->courses as $course) {
            /*$course->dozenten = $course->members->filter(
                                function ($member) {
                                    return $member['status'] === "dozent"; 
                                }
                            );
            */
            //cannot add attributes to course directly. TODO: resolve that problem!
            $this->dozenten[$course->id] = $course->members->filter(
                                function ($member) {
                                    return $member['status'] === "dozent"; 
                                }
                            );
        }
    }
    
    
    /**
        This action does the actual archiving of a course.
    */
    public function archive_action()
    {
        global $perm;
        
        //now pick the courses IDs:
        $courseIds = Request::getArray('courseIds');
        
        //check if the user has the required permission
        //to archive all selected courses:
        
        $this->deletedCourses = array();
        foreach ($courseIds as $courseId) {
            if (!$this->userHasPermission($courseId)) {
                //no permission for one of the selected courses: access denied!
                throw new AccessDeniedException();
            }
            
            // to be replaced when archive.inc.php is replaced:
            in_archiv($courseId);
            
            
            $course = Course::find($courseId);
            if($course != null) {
                $course->delete();
                $this->deletedCourses[] = $course;
            } else {
                throw new Exception(_("Veranstaltung nicht in Datenbank gefunden!"));
            }
            
        }
        
        
        /*
        // enable the following code when archive.inc.php is replaced
        
        //get all courses:
        $courses = Course::findMany($courseIds);
        
        //now create ArchivedCourse objects out of the Course objects:
        
        foreach ($courses as $course) {
            in_archiv($course->id);
        }
            $archivedCourse = new ArchivedCourse();
            $archivedCourse->id = $course->id;
            $archivedCourse->name = $course->name;
            $archivedCourse->untertitel = $course->untertitel;
            $archivedCourse->beschreibung = $course->beschreibung;
            $archivedCourse->start_time = $course->start_time;
            $archivedCourse->semester = $course->end_semester; //TODO: maybe start_semester is better
            $archivedCourse->heimat_inst_id = $course->home_institut->id;
            $archivedCourse->institute = $course->institutes;
            
            //get "dozenten":
            $archivedCourse->dozenten = $course->members->filter(
                                function ($member) {
                                    return $member['status'] === "dozent"; 
                                }
                            );
            
            $archivedCourse->fakultaet = $course->home_institut->faculty;
            

            //dump is an HTML table with the seminar data
            $archivedCourse->dump = $this->createArchivedCourseHTMLTable($course);

            //TODO:
            //$archivedCourse->archiv_file_id = 
            //$archivedCourse->archiv_protected_file_id = 
            $archivedCourse->mkdate = time();
            //$archivedCourse->forumdump = 
            //$archivedCourse->wikidump = 
            $archivedCourse->studienbereiche = $course->study_areas;
            $archivedCourse->veranstaltungsnummer = $course->veranstaltungsnummer;
            $archivedCourse->members = $course->members;
            $archivedCourse->home_institut = $course->home_institut;
            
            
            
        }
        */
    }
}
