<?php
/**
* Form for user login.
*
* Parameters received via stdin<br/>
* <code>
*	$user_name
* </code>
*
* @author		Florian Hansen <f1701h@gmx.net>
* @version		0.1
* @access		public
* @modulegroup	wap_modules
* @module		login_form.php
* @package		WAP
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// login_form.php
// Form for user login
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

	include_once("wap_buttons.inc.php");
	include_once("wap_txt.inc.php");
	include_once("wap_adm.inc.php");

	wap_adm_start_card();

		echo "<p align=\"center\">";
		echo "<b>" . _("Login") . "</b>";
		echo "</p>";

		echo "<p align=\"left\">";

		echo wap_txt_encode_to_wml(_("Username:")) . "<br/>";
		echo "<input type=\"text\" name=\"user_name\" ";
		echo "emptyok=\"false\" value=\"" . stripslashes($user_name);
		echo "\"/><br/>";

		echo wap_txt_encode_to_wml(_("Passwort:")) . "<br/>";
		echo "<input type=\"password\" name=\"user_pass\" ";
		echo " emptyok=\"false\"/><br/>";
		echo "</p>";

		echo "<p align=\"right\">";
        echo "<anchor>" . wap_buttons_login();
        echo 	"<go href=\"login_index.php\" method=\"post\">";
        echo		"<postfield name=\"user_name\" value=\"\$(user_name)\"/>";
        echo		"<postfield name=\"user_pass\" value=\"\$(user_pass)\"/>";
        echo	"</go>";
        echo "</anchor><br/>";

        wap_buttons_menu_link(FALSE);
		echo "</p>";

	wap_adm_end_card();
?>