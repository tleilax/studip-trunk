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
        PageLayout::setTitle(_("Suche im Veranstaltungsarchiv"));
        if(Request::get('searchPerfomed')) {
            /* 
                A search form was sent here:
                We have to make lookups in the database.
            */
            
            
            if(Request::get('courseName')) {
                //courseName is presend and not empty
                $this->courseName = Request::get('courseName');
            }
            
        }
    }
    
    public function dump_action()
    {
    
    }
    
    public function forum_action()
    {
    
    }
    
    public function wiki_action()
    {
    
    }
}