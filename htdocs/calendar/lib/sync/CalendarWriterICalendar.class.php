<?
/**
* CalendarWriteriCalendar.class.php
*
*
* Based on the iCalendar export functions from The Horde Project
* www.horde.org
* horde/lib/iCalendar.php,v 1.19
* Copyright 2003 Mike Cochrane <mike@graftonhall.co.nz>
*
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	calendar_modules
* @module		calendar_export
* @package	Calendar
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CalendarWriteriCalendar.class.php
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

global $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_CALENDAR;

require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarWriter.class.php");

class CalendarWriteriCalendar extends CalendarWriter {
	
	var $newline = "\r\n";
	
	function CalendarWriteriCalendar () {
		
		$this->default_filename_suffix = "ics";
		$this->format = "iCalendar";
	}
	
	function writeHeader () {
	
		// Default values
		$header = "BEGIN:VCALENDAR" . $this->newline;
		$header .= "VERSION:2.0" . $this->newline;
		$header .= "PRODID:-//Stud.IP//Stud.IP_iCalendar Library, Stud.IP 1.0-cvs //EN" . $this->newline;
		$header .= "METHOD:PUBLISH" . $this->newline;
		
		return $header;
	}
	
	function writeFooter () {
	
		return "END:VCALENDAR" . $this->newline;
	}
	
	/**
	 * Export this component as iCalendar format
	 *
	 * @param array $event The event to export.
	 * @return String iCalendar format data
	 */
	function write ($properties) {

		$result	= "BEGIN:VEVENT" . $this->newline;

		foreach ($properties as $name => $attribute) {
			$name = $name;
			$params = array();
			$params_str = '';

			$value = $attribute;
			if ($value === "")
				continue;
			
			$match_pattern = array('\\', '\n', ';', ',');
			$replace_pattern = array('\\\\', '\\n', '\;', '\,');
			
			switch ($name) {
				// text fields
				case 'SUMMARY':
				case 'DESCRIPTION':
				case 'CATEGORIES':
				case 'LOCATION':
					$value = str_replace($match_pattern, $replace_pattern, $value);
					break;
				// Date fields with DST
				case 'DTSTAMP':
				case 'LAST-MODIFIED':
					$value = $this->_exportDateTime($value, TRUE);
					break;
				// Date fields without DST
				case 'CREATED':
				case 'COMPLETED':
					$value = $this->_exportDateTime($value);
					break;

				case 'DTEND':
				case 'DTSTART':
				case 'DUE':
				case 'RECURRENCE-ID':
					if (array_key_exists('VALUE', $params)) {
						if ($params['VALUE'] == 'DATE') {
							$value = $this->_exportDate($value);
						}
						else {
							$value = $this->_exportDateTime($value);
						}
					}
					else {
						$value = $this->_exportDateTime($value);
					}
					break;

				case 'RDATE':
					if (array_key_exists('VALUE', $params)) {
						if ($params['VALUE'] == 'DATE') {
							$value = $this->_exportDate($value);
						}
						else if ($params['VALUE'] == 'PERIOD') {
							$value = $this->_exportPeriod($value);
						}
						else {
							$value = $this->_exportDateTime($value);
						}
					}
					else {
						$value = $this->_exportDateTime($value);
					}
					break;

				case 'TRIGGER':
					if (array_key_exists('VALUE', $params)) {
						if ($params['VALUE'] == 'DATE-TIME') {
							$value = $this->_exportDateTime($value);
						}
						else if ($params['VALUE'] == 'DURATION') {
							$value = $this->_exportDuration($value);
						}
					}
					else {
						$value = $this->_exportDuration($value);
					}
					break;

				// Duration fields
				case 'DURATION':
					$value = $this->_exportDuration($value);
					break;

				// Period of time fields
				case 'FREEBUSY':
					$value_str = '';
					foreach ($value as $period) {
						$value_str .= empty($value_str) ? '' : ',';
						$value_str .= $this->_exportPeriod($period);
					}
					$value = $value_str;
					break;


				// UTC offset fields
				case 'TZOFFSETFROM':
				case 'TZOFFSETTO':
					$value = $this->_exportUtcOffset($value);
					break;

				// Integer fields
				case 'PERCENT-COMPLETE':
				case 'REPEAT':
				case 'SEQUENCE':
					$value = "$value";
					break;
				
				case 'PRIORITY':
					switch ($value) {
						case 0:
							$value = '0';
							break;
						case 1:
							$value = '1';
							break;
						case 2:
							$value = '5';
							break;
						case 3:
							$value = '9';
							break;
						default:
							$value = '1';
					}
					break;
				
				// Geo fields
				case 'GEO':
					$value = $value['latitude'] . ',' . $value['longitude'];
					break;

				// Recursion fields
				case 'EXRULE':
				case 'RRULE':
					$value = $this->_exportRecurrence($value);
					break;
				
				case "UID":
					$value = "$value";

			}

			$attr_string = "$name$params_str:$value";
			$result .= $this->_foldLine($attr_string) . $this->newline;
		}

		$result .= "END:VEVENT" . $this->newline;

		return utf8_encode($result);
	}
	
		/**
	 * Export a UTC Offset field
	 *
	 * @param array $value 
	 * @return String UTC offset field iCalendar formatted
	 */
	function _exportUtcOffset ($value) {
		$offset = $value['ahead'] ? '+' : '-';
		$offset .= sprintf('%02d%02d',
					$value['hour'], $value['minute']);
		if (array_key_exists('second', $value)) {
			$offset .= sprintf('%02d', $value['second']);
		}

		return $offset;
	}

	/**
	 * Export a Time Period field
	 *
	 * @param array $value
	 * @return String Period field iCalendar formatted
	 */
	function _exportPeriod ($value) {
		$period = $this->_exportDateTime($value['start']);
		$period .= '/';
		if (array_key_exists('duration', $value)) {
			$period .= $this->_exportDuration($value['duration']);
		}
		else {
			$period .= $this->_exportDateTime($value['end']);
		}
		return $period;
	}

	/**
	 * Export a DateTime field
	 *
	 * @param int $value Unix timestamp
	 * @return String Date and time (UTC) iCalendar formatted
	 */
	function _exportDateTime ($value, $dst = TRUE) {
		
		if ($dst) {
			$TZOffset  = 3600 * substr(date('O', $value), 0, 3);
			$TZOffset += 60 * substr(date('O', $value), 3, 2);
		}
		else
			$TZOffset  = 3600;// * substr(date('O'), 0, 3);
	//	$TZOffset += 60 * substr(date('O'), 3, 2);
		$value -= $TZOffset;

		return $this->_exportDate($value) . 'T' . $this->_exportTime($value);
	}

	/**
	 * Export a Time field
	 *
	 * @param int $value Unix timestamp
	 * @return String Time (UTC) iCalendar formatted
	 */
	function _exportTime ($value) {
		$time = date ("His", $value);
		$time .= 'Z';
		
		return $time;
	}

	/**
	 * Export a Date field
	 */
	function _exportDate ($value) {
		return date("Ymd", $value);
	}

	/**
	 * Export a duration value
	 */
	function _exportDuration ($value) {
		$duration = '';
		if ($value < 0) {
			$value *= -1;
			$duration .= '-';
		}
		$duration .= 'P';

		$weeks = floor($value / (7 * 86400));
		$value = $value % (7 * 86400);
		if ($weeks) {
			$duration .= $weeks . 'W';
		}

		$days = floor($value / (86400));
		$value = $value % (86400);
		if ($days) {
			$duration .= $days . 'D';
		}

		if ($value) {
			$duration .= 'T';

			$hours = floor($value / 3600);
			$value = $value % 3600;
			if ($hours) {
				$duration .= $hours . 'H';
			}

			$mins = floor($value / 60);
			$value = $value % 60;
			if ($mins) {
				$duration .= $mins . 'M';
			}

			if ($value) {
				$duration .= $value . 'S';
			}
		}

		return $duration;
	}
	
	/**
	*Export a recurrence rule
	*/
	function _exportRecurrence ($value) {
		$rrule = array();
		// the last day of week in a MONTHLY or YEARLY recurrence in the
		// Stud.IP calendar is 5, in iCalendar it is -1
		if ($value['sinterval'] == 5)
			$value['sinterval'] = -1;
		
		foreach ($value as $r_param => $r_value) {
			if ($r_value) {
				switch ($r_param) {
					case 'rtype':
						$rrule[] = 'FREQ=' . $r_value;
						break;
					case 'expire':
						// end of unix epoche (this is also the end of Stud.IP epoche ;-) )
						if ($r_value != 2114377200)
							$rrule[] = 'UNTIL=' . $this->_exportDateTime($r_value);
						break;
					case 'linterval':
						$rrule[] = 'INTERVAL=' . $r_value;
						break;
					case 'wdays':
						switch ($value['rtype']) {
							case 'WEEKLY':
								$rrule[] = 'BYDAY=' . $this->_exportWdays($r_value);
								break;
							// Some CUAs (e.g. Outlook) don't understand the nWDAY syntax
							// (where n is the nth ocurrence of the day in a given period of
							// time and WDAY is the day of week) the RRULE uses the BYSETPOS
							// rule.
							case 'MONTHLY':
							case 'YEARLY':
								// $rrule[] = 'BYDAY=' . $r_value['sinterval'] . $this->_exportWdays($r_value);
								$rrule[] = 'BYDAY=' . $this->_exportWdays($r_value);
								// The Stud.IP calendar don't support multiple values in a
								// comma separated list.
								if ($r_value['sinterval'])
									$rrule[] = 'BYSETPOS=' . $r_value['sinterval'];
								break;
						}
						break;
					case 'day':
						$rrule[] = 'BYMONTHDAY=' . $r_value;
						break;
					case 'month':
						$rrule[] = 'BYMONTH=' . $r_value;
						break;
				}
			}
		}
				
		return implode(";", $rrule);
	}
	
	/**
	* Return the Stud.IP calendar wdays attribute of a event recurrence
	*/
	function _exportWdays ($value) {
		$wdays_map = array("1" => "MO", "2" => "TU", "3" => "WE", "4" => "TH", "5" => "FR",
				"6" => "SA", "7" => "SU");
		$wdays = array();
		preg_match_all('/(\d)/', $value, $matches);
		foreach ($matches[1] as $match) {
			$wdays[] = $wdays_map[$match];
		}
		
		return implode(",", $wdays);
	}
	
	/**
	* Return the folded version of a line
	*/
	function _foldLine ($line) {
		$line = preg_replace ('/(\r\n|\n|\r)/', '\n', $line);
		if (strlen($line) > 75) {
			$foldedline = '';
			while (!empty($line)) {
				$maxLine = substr($line, 0, 75);
				$cutPoint = max(60, max(strrpos($maxLine, ';'), strrpos($maxLine, ':')) + 1);

				$foldedline .= (empty($foldedline)) ?
									substr($line, 0, $cutPoint) :
									$this->newline . ' ' . substr($line, 0, $cutPoint);

				$line = (strlen($line) <= $cutPoint) ? '' : substr($line, $cutPoint);
			}
			return $foldedline;
		}
		return $line;

	}

}

?>
