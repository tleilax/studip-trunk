<?php
/**
* Outputs a list of new short messages.
*
* Only new messages since last login to the web-interface are displayed.
*
* Parameters received via stdin<br/>
* <code>
*	$session_id
*	$sms_pc		(page counter)
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
// sms.php
// List of new short messages
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
	* Maximum of short messages displayed per page
	* @const SMS_PER_PAGE
	*/
	define ("SMS_PER_PAGE", 5);

	include_once("wap_adm.inc.php");
	include_once("wap_txt.inc.php");
	include_once("wap_hlp.inc.php");
	include_once("wap_buttons.inc.php");

	$session_user_id = wap_adm_start_card($session_id);
	if ($session_user_id)
    {
        echo "<p align=\"center\">";
        echo "<b>" . wap_txt_encode_to_wml(_("Kurznachrichten")) . "</b>";
        echo "</p>";

        if ($sms_pc)
        {
            $page_counter     = $sms_pc;
            $progress_counter = $page_counter * SMS_PER_PAGE;
        }
        else
        {
            $page_counter     = 0;
            $progress_counter = 0;
        }

        wap_hlp_get_global_user_var($session_user_id, "CurrentLogin");
        $user_name = wap_adm_get_user_name($session_user_id);

        $db = new DB_Seminar();
        $q_string  = "SELECT COUNT(message_id) AS num_sms ";
        $q_string .= "FROM globalmessages ";
        $q_string .= "WHERE user_id_rec = \"$user_name\" ";
        $q_string .= "AND mkdate > $CurrentLogin";

        $db-> query("$q_string");
        $db-> next_record();
        $num_sms   = $db-> f("num_sms");
        $num_pages = ceil($num_sms / SMS_PER_PAGE);

        if ($num_sms > 0)
        {
            $q_string  = "SELECT user_id_snd, message_id FROM globalmessages ";
            $q_string .= "WHERE user_id_rec = \"$user_name\" ";
            $q_string .= "AND mkdate > $CurrentLogin ";
            $q_string .= "ORDER BY mkdate DESC ";
            $q_string .= "LIMIT $progress_counter, " . SMS_PER_PAGE;

            $db-> query("$q_string");
            $num_entries = $db-> nf();
            $progress_limit = $progress_counter + $num_entries;

            if (!isset($sms_pc))
            {
                echo "<p align=\"center\">";
                $t = sprintf(_("%s neue Nachricht(en)."), $num_sms);
                echo wap_txt_encode_to_wml($t);
                echo "</p>";
            }

            while ($db-> next_record() && $progress_counter < $progress_limit)
            {
                $progress_counter ++;
                $entry_sender = $db-> f("user_id_snd");
                $entry_id     = $db-> f("message_id");

                $short_sender = wap_txt_shorten_text($entry_sender, WAP_TXT_LINK_LENGTH - 3);
                echo "<p align=\"left\">";
                echo "<anchor>" . sprintf ("%02d ", $progress_counter);
                echo wap_txt_encode_to_wml($short_sender);
                echo    "<go href=\"show_sms.php\">";
                echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                echo        "<postfield name=\"sms_id\" value=\"$entry_id\"/>";
                echo        "<postfield name=\"sms_pc\" value=\"$page_counter\"/>";
                echo    "</go>";
                echo "</anchor>";
                echo "</p>";

                if ($progress_counter == $progress_limit)
                {
                    echo "<p align=\"right\">";
                    if ($progress_counter < $num_sms)
                    {
                        $page_counter_v = $page_counter + 1;
                        echo "<anchor>" . wap_buttons_forward_page($page_counter_v, $num_pages);
                        echo    "<go href=\"sms.php\">";
                        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                        echo        "<postfield name=\"sms_pc\" value=\"$page_counter_v\"/>";
                        echo    "</go>";
                        echo "</anchor><br/>";
                    }
                    if ($page_counter > 0)
                    {
                        $page_counter_v = $page_counter - 1;
                        echo "<anchor>" . wap_buttons_back_page($page_counter_v, $num_pages);
                        echo    "<go href=\"sms.php\">";
                        echo        "<postfield name=\"session_id\" value=\"$session_id\"/>";
                        echo        "<postfield name=\"sms_pc\" value=\"$page_counter_v\"/>";
                        echo    "</go>";
                        echo "</anchor><br/>";
                    }
                    echo "</p>";
                }
            }
        }
        else
        {
            echo "<p align=\"left\">";
            echo "? ";
            $t = _("Keine neuen Kurznachrichten seit letztem Web-Besuch.");
            echo wap_txt_encode_to_wml($t) . " &#191;";
            echo "</p>";
        }

        echo "<p align=\"right\">";
        wap_buttons_menu_link($session_id);
        echo "</p>";
    }
	wap_adm_end_card();
?>