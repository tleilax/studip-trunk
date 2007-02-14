<?php

/*
 * seminar_webservice.php - Provides webservices for infos about
 * 	Seminars
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/webservices/api/studip_seminar.php');

class SeminarService extends Studip_Ws_Service
{

	function SeminarService()
	{
    $this->add_api_method('get_participants',
                          array('string', 'string'),
                          array('string'),
                          'gets participants for seminar');
    $this->add_api_method('get_users_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all users for seminar');
    $this->add_api_method('get_authors_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all authors for seminar');
    $this->add_api_method('get_tutors_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all tutors for seminar');
    $this->add_api_method('get_lecturers_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all lecturers for seminar');
    $this->add_api_method('get_admins_for_seminar',
                          array('string', 'string'),
                          array('string'),
                          'gets all admins for seminar');
    $this->add_api_method('get_seminar_groups',
                          array('string', 'string'),
                          array('string'),
                          'gets all groups for seminar');
    $this->add_api_method('get_seminar_group_members',
                          array('string', 'string', 'string'),
                          array('string'),
													'gets all group members for seminar');

    $this->add_api_method('validate_seminar_permission',
                          array('string', 'string', 'string', 'string'),
                          array('string'),
													'validates permissions in seminar');

  #  $this->add_api_method('validate_institute_permission',
  #                        array('string', 'string', 'string', 'string'),
  #                        array('string'),
	#												'validates permissions in institute');
	}
  function before_filter($name, &$args) 
	{

    # get api_key
    $api_key = current($args);
    
    if ($api_key != $GLOBALS['STUDIP_API_KEY'])
      return new Studip_Ws_Fault('Could not authenticate client.');
	}

	function validate_seminar_permission_action($api_key, $ticket, $seminar_id, $permission)
	{
		$seminar =& new StudipSeminarHelper();
		return $seminar->validate_seminar_permission($ticket, $seminar_id, $permission);
	}

	function get_participants_action($api_key, $seminar_id)
	{
		$seminar =& new StudipSeminarHelper();
		return $seminar->get_participants($seminar_id);
	}

	function get_users_for_seminar_action($api_key, $seminar_id)
	{
		return StudipSeminarHelper::get_participants($seminar_id, 'user');
	}

	function get_authors_for_seminar_action($api_key, $seminar_id)
	{
		return StudipSeminarHelper::get_participants($seminar_id, 'autor');
	}

	function get_tutors_for_seminar_action($api_key, $seminar_id)
	{
		return StudipSeminarHelper::get_participants($seminar_id, 'tutor');
	}

	function get_lecturers_for_seminar_action($api_key, $seminar_id)
	{
		$lecturers = StudipSeminarHelper::get_participants($seminar_id, 'dozent');
		return $lecturers;
	}

	function get_admins_for_seminar_action($api_key, $seminar_id)
	{
		$authorized_users = StudipSeminarHelper::get_admins_for_seminar($seminar_id);
		return $authorized_users;
	}

	function get_seminar_groups_action($api_key, $seminar_id)
	{
		return StudipSeminarHelper::get_seminar_groups($seminar_id);
	}

	function get_seminar_group_members_action($api_key, $seminar_id, $group_name)
	{
		return StudipSeminarHelper::get_seminar_group_members($seminar_id, $group_name);
	}

}
