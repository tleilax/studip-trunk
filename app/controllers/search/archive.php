<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Moritz Strohm <strohm@data-quest.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.5.alpha-svn
 */

class Search_ArchiveController extends AuthenticatedController
{
    
    public function index_action()
    {
        PageLayout::setTitle(_('Suche im Veranstaltungsarchiv'));
        
        if(Request::get('searchRequested')) {
            /* 
                A search form was sent here:
                We have to make lookups in the database.
            */
            
            $this->searchRequested = true;
            
            //read parameters from HTTP POST, if they exist:
            $this->onlyMyCourses = Request::get('onlyMyCourses', false);
            $this->searchField = trim(Request::get('searchField', '')); //strip whitespaces here
            echo 'strlen($this->searchField) = ' . strlen($this->searchField);
            echo 'empty($this->searchField) = ' . empty($this->searchField);
            
            //mb_strlen is used for unicode compatibility
            if(mb_strlen($this->searchField) < 4) {
                //search keyword too short
                $this->errorMessage = sprintf(_('Suchbegriff muss mindestens %s Zeichen lang sein!'), 4);
            }
            else
            {
                
                if($this->onlyMyCourses) {
                    /*
                        If the user wants to see only his courses 
                        we have to filter the courses:
                    */
                    
                    
                    
                    $user = User::findCurrent();
                    
                    $allUserEntries = ArchivedCourseMember::findBySQL(
                        "user_id = :userId", array('userId' => $user->id));
                    
                    $this->foundCourses = array();
                    
                    foreach($allUserEntries as $userEntry) {
                        
                        $this->foundCourses[] = $userEntry->course;
                    }
                    
                }
                else
                {
                    $queryParameters = array('criteria' => $this->searchField);
                    //get courses where at least one field matches the search criteria
                    
                    $sql = "name LIKE CONCAT('%', :criteria, '%') "
                        . "OR untertitel LIKE CONCAT('%', :criteria, '%') "
                        . "OR beschreibung LIKE CONCAT('%', :criteria, '%') "
                        . "OR dozenten LIKE CONCAT('%', :criteria, '%') "
                        . "OR institute LIKE CONCAT('%', :criteria, '%') "
                        . "OR semester LIKE CONCAT('%', :criteria, '%') "
                        
                        //order:
                        . "ORDER BY start_time DESC, name DESC ";
                    
                    $this->foundCourses = ArchivedCourse::findBySQL($sql, $queryParameters);
                }
            }
        }
    }
}
