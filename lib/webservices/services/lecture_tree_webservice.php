<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/*
 * lecture_tree_webservice.php - Provides webservices for infos about
 *  lecture tree
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/webservices/api/studip_lecture_tree.php');
require_once('lib/webservices/api/studip_user.php');
require_once('lib/webservices/api/studip_seminar_info.php');
require_once('lib/dates.inc.php');

class LectureTreeService extends Studip_Ws_Service
{
    function LectureTreeService()
    {
        $this->add_api_method('get_seminars_by_sem_tree_id',
                                                        array('string', 
                                                                    'string', 
                                                                    'string'),
                                                        array('Studip_Seminar_Info'));
    }

  function before_filter($name, &$args) 
    {

    # get api_key
    $api_key = current($args);
    
    if ($api_key != $GLOBALS['STUDIP_API_KEY'])
      return new Studip_Ws_Fault('Could not authenticate client.');
    }

    function get_seminars_by_sem_tree_id_action($api_key, $sem_tree_id, $term_id)
    {
        $seminar_infos = array();

        $seminar_ids = StudipLectureTreeHelper::get_seminars_by_sem_tree_id($sem_tree_id, $term_id);

          foreach($seminar_ids as $seminar_id)
        {
            $sem_obj = new Seminar($seminar_id['seminar_id']);

            $lecturers = StudipSeminarHelper::get_participants($seminar_id['seminar_id'], 'dozent');

            foreach($lecturers as $lecturer)
            {
                $lecturers [] = Studip_User::find_by_user_name($lecturer);
            }

            $seminar_info = new Studip_Seminar_Info();
            $seminar_info->title = $sem_obj->getName();
            $seminar_info->lecturers = $lecturers;
            $seminar_info->turnus = $sem_obj->getDatesTemplate('dates/seminar_export', array('semester_id' => $term_id));
            $seminar_info->lecture_number = $sem_obj->seminar_number;

            $seminar_infos [] = $seminar_info;
        }
        return $seminar_infos;
    }

    

}
