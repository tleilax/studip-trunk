<?php
/**
* Shows general information about a user
*
* Parameters received via stdin<br/>
* <code>
*   $session_id
*   $first_name
*   $last_name
*   $user_id
*   $directory_search_pc	(page counter)
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.12	18.09.2003	11:30:46
* @access		public
* @modulegroup	wap_modules
* @module		dates_search.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_user.php
// User information
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
    * workaround for PHPDoc
    *
    * Use this if module contains no elements to document!
    * @const PHPDOC_DUMMY
    */
    define("PHPDOC_DUMMY", TRUE);

	include_once("wap_adm.inc.php");
	include_once("wap_txt.inc.php");
	include_once("wap_buttons.inc.php");

	wap_adm_start_card();

        $db = new DB_Seminar;
        $q_string  = "SELECT " . $_fullname_sql['full'] . " ";
        $q_string .= "AS komplett_name, Email ";
        $q_string .= "FROM auth_user_md5 ";
        $q_string .= "LEFT JOIN user_info USING (user_id) ";
        $q_string .= "WHERE auth_user_md5.user_id=\"$user_id\"";
        $db-> query("$q_string");
        $db-> next_record();

        $complete_name = $db-> f("komplett_name");
        $e_mail        = $db-> f("Email");

        $q_string  = "SELECT privatnr, privadr ";
        $q_string .= "FROM user_info ";
        $q_string .= "WHERE user_id = \"$user_id\"";
        $db-> query("$q_string");
        $db-> next_record();

        $private_nr  = $db-> f("privatnr");
        $private_adr = $db-> f("privadr");

        echo "<p align=\"left\">\n";
        echo wap_txt_encode_to_wml($complete_name) . "<br/>\n";
        echo wap_txt_encode_to_wml($e_mail) . "<br/>\n";
        echo "</p>\n";

        $q_string  = "SELECT Institute.Name, Institute.Institut_id ";
        $q_string .= "FROM user_inst, Institute ";
        $q_string .= "WHERE user_inst.user_id = '$user_id'";
        $q_string .= "AND user_inst.Institut_id = Institute.Institut_id ";
        $q_string .= "AND user_inst.inst_perms != 'user' ";
        $q_string .= "ORDER BY Institute.Name";
        $db-> query("$q_string");

        if ($private_nr || $private_adr)
        {
            echo "<p align=\"left\">\n";
            echo "<anchor>" . wap_txt_encode_to_wml(_("Privat")) . "\n";
            echo "    <go method=\"post\" href=\"show_private.php\">\n";
            echo "        <postfield name=\"session_id\" value=\"$session_id\"/>\n";
            echo "        <postfield name=\"first_name\" value=\"$first_name\"/>\n";
            echo "        <postfield name=\"last_name\" value=\"$last_name\"/>\n";
            echo "        <postfield name=\"user_id\" value=\"$user_id\"/>\n";
            echo "        <postfield name=\"directory_search_pc\" value=\"$directory_search_pc\"/>\n";
            echo "    </go>\n";
            echo "</anchor>\n";
            echo "</p>\n";
        }

        while ($db-> next_record())
        {
            $inst_name = $db-> f("Name");
            $inst_id   = $db-> f("Institut_id");

            $short_name = wap_txt_shorten_text($inst_name, WAP_TXT_LINK_LENGTH * 2);
            echo "<p align=\"left\">\n";
            echo "<anchor>" . wap_txt_encode_to_wml($short_name) . "\n";
            echo "    <go method=\"post\" href=\"show_institute.php\">\n";
            echo "        <postfield name=\"session_id\" value=\"$session_id\"/>\n";
            echo "        <postfield name=\"first_name\" value=\"$first_name\"/>\n";
            echo "        <postfield name=\"last_name\" value=\"$last_name\"/>\n";
            echo "        <postfield name=\"user_id\" value=\"$user_id\"/>\n";
            echo "        <postfield name=\"directory_search_pc\" value=\"$directory_search_pc\"/>\n";
            echo "        <postfield name=\"inst_id\" value=\"$inst_id\"/>\n";
            echo "    </go>\n";
            echo "</anchor>\n";
            echo "</p>\n";
        }

        echo "<p align=\"right\">\n";
        echo "<anchor>" . wap_buttons_back() . "\n";
        echo "    <go method=\"post\" href=\"directory_search.php\">\n";
        echo "        <postfield name=\"session_id\" value=\"$session_id\"/>\n";
        echo "        <postfield name=\"first_name\" value=\"$first_name\"/>\n";
        echo "        <postfield name=\"last_name\" value=\"$last_name\"/>\n";
        echo "        <postfield name=\"directory_search_pc\" value=\"$directory_search_pc\"/>\n";
        echo "    </go>\n";
        echo "</anchor><br/>\n";

        echo "<anchor>" . wap_buttons_new_search() . "\n";
        echo "    <go method=\"post\" href=\"directory.php\">\n";
        echo "        <postfield name=\"session_id\" value=\"$session_id\"/>\n";
        echo "    </go>\n";
        echo "</anchor><br/>\n";

        wap_buttons_menu_link ($session_id);
        echo "</p>\n";

    wap_adm_end_card();
?>
