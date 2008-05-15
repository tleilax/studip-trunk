<?php

/*
 * PmWikiConnectedLink.class.php - Provides links to PmWiki Modules
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once("ConnectedLink.class.php");

global $ABSOLUTE_PATH_STUDIP;
global $RELATIVE_PATH_RESOURCES;

require_once("lib/webservices/api/studip_seminar.php");

/**
*
* This class contains methods to generate links to PmWiki-Farm
*
* @author	Marco Diedrich <mdiedric@uos.de>
* @access	public
* @modulegroup	elearning_interface_modules
* @module		PmWikiConnectedLink
* @package	ELearning-Interface
*/

class PmWikiConnectedLink extends ConnectedLink
{
	function PmWikiConnectedLink($cms)
	{
		parent::ConnectedLink($cms);
		$this->cms_link = "pmwiki_referrer.php";
	}

	/**
	* get user module links
	*
	* returns content module links for user
	* @access public
	* @return string html-code
	*/
	
	function getUserModuleLinks()
	{
		$range_id = $GLOBALS['_SESSION']['SessSemName'][1];
		$username = get_username($GLOBALS['auth']->auth['uid']);

		global $connected_cms, $view, $search_key, $cms_select, $current_module;

		// hier muss die Authentifizierung mit �bergeben werden...
		//
		if ($GLOBALS['_SESSION']['SessSemName']['class'] == 'sem')
		{
			$context = 'seminar';

			$status = StudipSeminarHelper::get_user_status($username, $range_id);

		} else if ($GLOBALS['_SESSION']['SessSemName']['class'] == 'inst')
		{
			$context = 'institute';

			$status = StudipInstituteHelper::get_user_status($username, $range_id);
		}

		$token = new Token($GLOBALS['auth']->auth['uid']);

		ob_start(); ?>
		<form method='post' target='_blank' 
					action='<?=$connected_cms[$this->cms_type]->content_module[$current_module]->link?>' >

			<input type='hidden' 	name='authid' 			value='<?= $GLOBALS['auth']->auth['uname'] ?>' />
			<input type='hidden' 	name='authpw' 			value='<?= $token->get_string() ?>' />
			<input type='hidden' 	name='_permission' 	value='<?= $status ?>' />
			<input type='hidden' 	name='_range_id' 		value='<?= $range_id ?>' />
			<input type='hidden' 	name='_server' 			value='<?= $GLOBALS['STUDIP_INSTALLATION_ID'] ?>' />
			<input type='hidden' 	name='_context' 		value='<?= $context ?>' />
			<input type='image' 	alt='starten' 						 <?= makeButton("starten", "src")?> />

		</form>

		<?php 

		$output = ob_get_contents();
		ob_end_clean();
		return $output; 
	}

	/**
	* get admin module links
	*
	* returns links add or remove a module from course
	* @access public
	* @return string returns html-code
	*/

	function getAdminModuleLinks()
	{
		global $connected_cms, $view, $search_key, $cms_select, $current_module;

		ob_start(); ?>

		<form method=\"post\" action="<?= $GLOBALS["PHP_SELF"] ?>"/>
			<input type="hidden" 	name="view" 							value="<?= $view ?>"/>
			<input type="hidden" 	name="search_key" 				value="<?= $search_key ?>"/>
			<input type="hidden" 	name="cms_select" 				value="<?= $cms_select ?>"/>
			<input type="hidden" 	name="module_type" 				value="wiki" />
			<input type="hidden" 	name="module_id" 					value="<?= $current_module ?>" />
			<input type="hidden" 	name="module_system_type" value="<?= $this->cms_type ?>"/>

			<?php if ($connected_cms[$this->cms_type]->content_module[$current_module]->isConnected()) : ?>
			
				&nbsp;<input type="image" <?= makeButton("entfernen", "src") ?> border=0 
									value="<?= _("Entfernen") ?>" name="remove"/>

			<?php else :?>

				&nbsp;<input type="image" <?= makeButton("hinzufuegen", "src") ?> border=0 
									value="<?= _("Hinzuf&uuml;gen") ?>" name="add"/><br/>

			<?php endif ; ?>

		</form>
		<?php 

		$output = ob_get_contents();

		ob_end_clean();

		return $output;
	}

}
