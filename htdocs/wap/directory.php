<?php
/**
* Form for directory search
*
* Lets the user enter the first and last name of the wanted person.<br/>
* Parameters received via stdin<br/>
* <code>
*	$session_id
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.1
* @access		public
* @modulegroup	wap_modules
* @module		directory.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// directory.php
// Form for directory search
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
	include_once("wap_buttons.inc.php");

    $session_user_id = wap_adm_start_card($session_id);
    if (!$session_expired)
    {
        echo "<p align=\"center\">";
        echo "<b>" . _("Suche") . "</b>";
        echo "</p>";

        echo "<p align=\"left\">";
        $t = _("Nachname:");
        echo wap_txt_encode_to_wml($t) . "<br/>";
        echo "<input type=\"text\" name=\"last_name\" emptyok=\"false\"/><br/>";

        $t = _("Vorname:");
        echo wap_txt_encode_to_wml($t) . "<br/>";
        echo "<input type=\"text\" name=\"first_name\" emptyok=\"false\"/><br/>";
        echo "</p>";

        echo "<p align=\"right\">";
        echo "<anchor>" . wap_buttons_search();
        echo "<go method=\"post\" href=\"directory_search.php\">";
        echo    "<postfield name=\"last_name\" value=\"\$(last_name)\"/>";
        echo    "<postfield name=\"first_name\" value=\"\$(first_name)\"/>";
        echo    "<postfield name=\"session_id\" value=\"$session_id\"/>";
        echo "</go>";
        echo "</anchor><br/>";

        wap_buttons_menu_link ($session_id);
        echo "</p>";
    }
    wap_adm_end_card();
?>