<?php
# Lifter002: TODO

/*
 * session_webservice.php - Provides webservices for infos about
 * 	authorization
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/webservices/api/studip_session.php');

class SessionService extends Studip_Ws_Service
{
	function SessionService()
	{
    $this->add_api_method('is_session_valid',
                          array('string', 'string'),
                          bool,
                          'checks if session-id is valid');

    $this->add_api_method('get_session_username',
                          array('string', 'string'),
                          'string',
                          'returns username for session-id');

    $this->add_api_method('get_prefixed_session_username',
                          array('string', 'string'),
                          'string',
                          'returns prefixed username for session-id');
	}

  function before_filter($name, &$args) {
    # get api_key
    $api_key = current($args);
    
		$db = new DB_Seminar();
    if ($api_key != $GLOBALS['STUDIP_API_KEY'])
      return new Studip_Ws_Fault('Could not authenticate client.');
		}

	function is_session_valid_action($api_key, $session_id)
	{
		return StudipSessionHelper::is_session_valid($session_id);
	}

	function get_session_username_action($api_key, $session_id)
	{
		return StudipSessionHelper::get_session_username($session_id);
	}

	function get_prefixed_session_username_action($api_key, $session_id)
	{
		if ($GLOBALS['STUDIP_INSTALLATION_ID'])
		{
			$prefix = $GLOBALS['STUDIP_INSTALLATION_ID'];
		} else
		{
			$prefix = $GLOBALS['HTTP_SERVER']['HTTP_HOST'];
		}
			return $prefix."#".StudipSessionHelper::get_session_username($session_id);
	}
}
