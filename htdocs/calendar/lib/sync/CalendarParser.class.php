<?
/**
* CalendarParser.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	calendar_modules
* @module		calendar_import
* @package	Calendar
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CalendarParser.class.php
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


global $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_CALENDAR, $CALENDAR_DRIVER;

require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/CalendarEvent.class.php");
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/driver/$CALENDAR_DRIVER/CalendarDriver.class.php");

class CalendarParser {

	var $events = array();
	var $components;
	var $type;
	var $number_of_events;
	var $errors;
	var $fatal_error;
	
	function numberOfEvents () {
	
	}
	
	function parseIntoDatabase ($data, $ignore) {
		
		$database =& new CalendarDriver();
		if ($this->parse($data, $ignore))
			$database->writeIntoDatabase($this->components, 'INSERT_IGNORE');
	
	}
	
	function parseIntoObjects ($data, $ignore) {
		
		if ($this->parse($data, $ignore)) {
			foreach ($this->components as $properties)
				$this->events[] =& new CalendarEvent($properties);
		}
	}
	
	function getType () {
		
		return $this->type;
	}
	
	function &getObjects () {
		
		return $objects =& $this->events;
	}
	
}

?>
