<?
/**
* ErrorHandler.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	calendar_modules
* @module		Calendar
* @package	calendar_export
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ErrorHandler.class.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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

require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/Error.class.php");

define('ERROR_NORMAL', 1);
define('ERROR_MESSAGE', 2);
define('ERROR_WARNING', 4);
define('ERROR_CRITICAL', 8);
define('ERROR_FATAL', 16); 


function init_error_handler ($handler_name) {
	global $$handler_name;
	
	static $instantiated = array();
	
	if (!isset($instantiated[$handler_name])) {
		$$handler_name = new ErrorHandler();
		$instantiated[$handler_name] = TRUE;
	}
}
	

class ErrorHandler {

	var $errors;
	var $status;
	
	function ErrorHandler () {
		
		$this->errors = array();
		$this->status = ERROR_NORMAL;
		$this->_is_instantiated = TRUE;
	}
	
	function getStatus ($status = NULL) {
		
		if ($status === NULL)
			return $this->status;
		else
			return $status & $this->status;
	}
	
	function getErrors () {
	
		return $this->errors;
	}
	
	function throwError ($status, $message, $file = '', $line = '') {
		
		$this->errors[] =& new Error($status, $message, $file, $line);
		$this->status |= $status;
	}
	
}
	
		
