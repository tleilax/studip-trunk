<?
/**
* language functions
* 
* helper functions for handling I18N system
* 
*
* @author		Stefan Suchi <suchi@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	library
* @module		language.inc
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// language.inc.php
// helper functions for handling I18N system
// Copyright (c) 2003 Stefan Suchi <suchi@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


/**
* This function tries to find the preferred language  
*
* This function tries to find the preferred language.
* It returns the first accepted language from browser settings, which is installed.
* 
* @access	public        
* @return		string	preferred user language, given in "en_GB"-style	
*
*/
function get_accepted_languages() {
	global $INSTALLED_LANGUAGES;

	$accepted_languages = explode(",", getenv("HTTP_ACCEPT_LANGUAGE"));
	if (is_array($accepted_languages) && count($accepted_languages)) {
		foreach ($accepted_languages as $temp_accepted_language) {
			foreach ($INSTALLED_LANGUAGES as $temp_language => $temp_language_settings) {
				if (substr(trim($temp_accepted_language), 0, 2) == substr($temp_language, 0, 2)) {
					$_language = $temp_language;
					break 2;
				}
			}
		}
	}
	return $_language;
}


/**
* This function starts output via i18n system in the given language  
*
* This function starts output via i18n system in the given language.
* It returns the path to the choosen language.
* 
* @access	public        
* @param		string	the language to use for output, given in "en_GB"-style
* @return		string	the path to the language file, given in "en"-style	
*
*/
function init_i18n($_language) {
	global $_language_domain, $INSTALLED_LANGUAGES, $ABSOLUTE_PATH_STUDIP;

	if (isset($_language_domain) && isset($_language)) {
		$_language_path = $INSTALLED_LANGUAGES[$_language]["path"];
		putenv("LANG=$_language");
		setlocale(LC_ALL, "");
		if ($_language != "de_DE") { // German is the original language, so we need no I18N
			bindtextdomain($_language_domain, "$ABSOLUTE_PATH_STUDIP/locale");
			textdomain($_language_domain);
		}
	}
	return $_language_path;
}


/**
* create the img tag for graphic buttons
*
* This function creates the html text for a button.
* Decides, which button (folder)
* is used for international buttons.
*
* @access	public        
* @param		string	the (german) button name
* @param		string	if mode = img, the functions return the full tag, if mode = src, it return only the src-part (for graphic submits)
* @return		string	html output of the button
*/
function makeButton ($name, $mode = "img") {
	global $_language_path, $CANONICAL_RELATIVE_PATH_STUDIP;
	$path = "{$CANONICAL_RELATIVE_PATH_STUDIP}locale/$_language_path/LC_BUTTONS";
	if ($mode == "img")
		$tag = sprintf ("<img src=\"%s/%s-button.gif\" border=\"0\" align=\"absmiddle\" alt=\"%s\"/>", $path, $name, $name);
	else
		$tag = sprintf ("src=\"%s/%s-button.gif\"", $path, $name);

	return $tag;
}


/**
* retrieves path to preferred language of user from database
*
* Can be used for sending language specific mails to other users.
*
* @access	public        
* @param		string	the user_id of the recipient (function will try to get preferred language from database)
* @return		string	the path to the language files, given in "en"-style
*/
function getUserLanguagePath($uid) {
	global $INSTALLED_LANGUAGES, $DEFAULT_LANGUAGE;
	// try to get preferred language from user
	$db=new DB_Seminar;
	$db->query("SELECT preferred_language FROM user_info WHERE user_id='$uid'");
	if ($db->next_record()) {
		if ($db->f("preferred_language") != NULL && $db->f("preferred_language") != "") {
			// we found a stored setting for preferred language
			$temp_language = $db->f("preferred_language");
		} else {
			// no preferred language, use system default
			$temp_language = $DEFAULT_LANGUAGE;
		}
	} else {
		// no preferred language, use system default
		$temp_language = $DEFAULT_LANGUAGE;
	}
	return $INSTALLED_LANGUAGES[$temp_language]["path"];
}

/**
* switch i18n to different language
*
* This function switches i18n system to a different language.
* Should be called before writing strings to other users into database.
* Use restoreLanguage() to switch back.
*
* @access	public        
* @param		string	the user_id of the recipient (function will try to get preferred language from database)
* @param		string	explicit temporary language (set $uid to FALSE to switch to this language)
*/
function setTempLanguage ($uid = FALSE, $temp_language = "") {
	global $_language_domain, $DEFAULT_LANGUAGE, $ABSOLUTE_PATH_STUDIP;
	
	if ($uid) {
		// try to get preferred language from user
		$db=new DB_Seminar;
		$db->query("SELECT preferred_language FROM user_info WHERE user_id='$uid'");
		if ($db->next_record()) {
			if ($db->f("preferred_language") != NULL && $db->f("preferred_language") != "") {
				// we found a stored setting for preferred language
				$temp_language = $db->f("preferred_language");
			} else {
				// no preferred language, use system default
				$temp_language = $DEFAULT_LANGUAGE;
			}
		} else {
			// should never be reached, best we can do is to set system default
			$temp_language = $DEFAULT_LANGUAGE;
		}
	}
	
	if ($temp_language == "") {
		// we got no arguments, best we can do is to set system default
		$temp_language = $DEFAULT_LANGUAGE;
	}

	putenv("LANG=$temp_language");
	setlocale(LC_ALL, "");
	bindtextdomain($_language_domain, "$ABSOLUTE_PATH_STUDIP/locale");
	textdomain($_language_domain);
}


/**
* switch i18n back to original language
*
* This function switches i18n system back to the original language.
* Should be called after writing strings to other users via setTempLanguage().
*
* @access	public        
*/
function restoreLanguage() {
	global $_language_domain, $_language, $ABSOLUTE_PATH_STUDIP;

	putenv("LANG=$_language");
	setlocale(LC_ALL, "");
	bindtextdomain($_language_domain, "$ABSOLUTE_PATH_STUDIP/locale");
	textdomain($_language_domain);
}


?>
