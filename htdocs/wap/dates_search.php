<?php
/**
* Outputs a list of the users dates
*
* The list is created by taking both private and event
* dates into consideration.<br/>
* Parameters received via stdin<br/>
* <code>
*	$session_id
*	$num_days
*	$dates_search_pc	(page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.1
* @access		public
* @modulegroup	wap_modules
* @module		dates_search.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// dates_search.php
// Output of user dates
// Copyright (c) 2003 Florian Hansen <f1701h@gmx.net>
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
	* Maximum of dates displayed per page
	* @const DATES_PER_PAGE
	*/
	define ("DATES_PER_PAGE", 5);

	/**
	* Maximum of results displayed
	* @const NUM_MAX_RESULTS
	*/
	define ("NUM_MAX_RESULTS", 50);

	include_once("Dummy.class.php");
	include_once("wap_adm.inc.php");
	include_once("wap_hlp.inc.php");
	include_once("wap_txt.inc.php");
	include_once("wap_buttons.inc.php");
	require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"]
	           . $GLOBALS["RELATIVE_PATH_CALENDAR"]
	           . "/lib/DbCalendarEventList.class.php");

	$session_user_id = wap_adm_start_card($session_id);
	if ($session_user_id)
	{
		$user     = new Dummy();
		$user->id = $session_user_id;
		wap_hlp_get_global_user_var($session_user_id, "calendar_user_control_data");

		if	(is_array($calendar_user_control_data["bind_seminare"]))
			$bind_seminare = array_keys($calendar_user_control_data["bind_seminare"], "TRUE");
		else
			$bind_seminare = "";

		$start_time = time();
		$end_time   = $start_time + ($num_days * 86400);

		$event_list = new AppList($session_user_id, TRUE, $start_time, $end_time);
		$event_list-> bindSeminarEvents($bind_seminare);

		$num_events = $event_list-> numberOfEvents();
		$num_pages  = ceil($num_events / DATES_PER_PAGE);

        if ($dates_search_pc)
        {
			$page_counter     = $dates_search_pc;
			$progress_counter = $page_counter * DATES_PER_PAGE;
			for ($i = 0; $i < $progress_counter; $i++)
			{
				$event_list-> nextEvent();
			}
		}
		else
		{
			$page_counter     = 0;
			$progress_counter = 0;
		}

		if (($num_events > 0) && ($num_events <= NUM_MAX_RESULTS))
		{
			$progress_limit = $progress_counter + DATES_PER_PAGE;
			if ($progress_limit > $num_events)
				$progress_limit = $num_events;

			while ($event_list-> existEvent() && ($event = $event_list-> nextEvent())
			                                  && ($progress_counter < $progress_limit))
			{
				$progress_counter ++;
				$event_id     = $event-> getId();
				$event_title  = $event-> getTitle();
				$event_sem_id = $event-> getSeminarId();

				if ($event_sem_id)
					$event_sem_name = $event-> getSemName();
				else
					$event_sem_name = FALSE;

				$short_title = wap_txt_shorten_text($event_title, WAP_TXT_LINK_LENGTH);
				echo "<p align=\"left\">";
				echo "<anchor>" . wap_txt_encode_to_wml($short_title);
				echo    "<go href=\"show_date.php\">";
				echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
				echo        "<postfield name=\"date_id\" value=\"$event_id\"/>";
				echo        "<postfield name=\"num_days\" value=\"$num_days\"/>";
				echo        "<postfield name=\"event_sem_name\" value=\"$event_sem_name\"/>";
				echo        "<postfield name=\"dates_search_pc\" value=\"$page_counter\"/>";
				echo    "</go>";
				echo "</anchor>";
				echo "</p>";

				if ($progress_counter == $progress_limit)
				{
                    echo "<p align=\"right\">";
                    if ($progress_counter < $num_events)
                    {
                        $page_counter_v = $page_counter + 1;
                        echo "<anchor>" . wap_buttons_forward_page($page_counter_v, $num_pages);
                        echo    "<go href=\"dates_search.php\">";
                        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                        echo        "<postfield name=\"num_days\" value=\"$num_days\"/>";
                        echo        "<postfield name=\"dates_search_pc\" value=\"$page_counter_v\"/>";
                        echo    "</go>";
                        echo "</anchor><br/>";
                    }
                    if ($page_counter > 0)
                    {
                        $page_counter_v = $page_counter - 1;
                        echo "<anchor>" . wap_buttons_back_page($page_counter_v, $num_pages);
                        echo    "<go href=\"dates_search.php\">";
                        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                        echo        "<postfield name=\"num_days\" value=\"$num_days\"/>";
                        echo        "<postfield name=\"dates_search_pc\" value=\"$page_counter_v\"/>";
                        echo    "</go>";
                        echo "</anchor><br/>";
                    }
                    echo "</p>";
                }
            }
        }
        elseif ($num_events > NUM_MAX_RESULTS)
        {
            echo "<p align=\"left\">";
            $t = sprintf(_("Mehr als %s Eintr�ge."), NUM_MAX_RESULTS);
            echo wap_txt_encode_to_wml($t) . "<br/>";
            $t = _("Bitte den Zeitraum einschr�nken.");
            echo wap_txt_encode_to_wml($t);
            echo "</p>";
        }
        else
        {
            echo "<p align=\"left\">";
            $t = _("Keine Termine vorhanden.");
            echo "? " . wap_txt_encode_to_wml($t) . " &#191;";
            echo "</p>";
        }

        echo "<p align=\"right\">";
        echo "<anchor>" . wap_buttons_time();
        echo    "<go href=\"dates.php\">";
        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
        echo    "</go>";
        echo "</anchor><br/>";

        wap_buttons_menu_link($session_id);
        echo "</p>";
	}
	wap_adm_end_card();
?>