<?
/**
* bind.inc.php
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>
* @version		$Id$
* @access		public
* @modulegroup	calendar
* @module		calendar
* @package	calendar
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// bind.inc.php
//
// Copyright (c) 2003 Peter Tienel <pthienel@web.de> 
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


echo "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"90%\">\n";
echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"1\" class=\"blank\">\n";

if (!empty($calendar_sess_control_data["view_prv"]))
	echo "<form action=\"$PHP_SELF?cmd={$calendar_sess_control_data['view_prv']}\" method=\"post\">";
else
	echo "<form action=\"$PHP_SELF?cmd=showweek\" method=\"post\">";
echo "\n<tr>\n";
echo "<th width=\"1%\" nowrap colspan=\"2\" align=\"center\">";
echo "&nbsp;<a href=\"gruppe.php\">";
$tooltip = tooltip(_("Gruppe �ndern"));
echo "<img src=\"pictures/gruppe.gif\"{$tooltip}border=\"0\">";
echo "</a></th>\n";
echo "<th width=\"64%\" align=\"left\">";
echo "<a href=\"$PHP_SELF?cmd=bind&sortby=Name&order=$order\">" . _("Name") . "</a></th>\n";
echo "<th width=\"7%\"><a href=\"$PHP_SELF?cmd=bind&sortby=count&order=$order\">";
echo _("Termine") . "</a></th>\n";
echo "<th width=\"13%\"><b>" . _("besucht") . "</b></th>\n";
echo "<th width=\"13%\"><a href=\"$PHP_SELF?cmd=bind&sortby=status&order=$order\">";
echo _("Status") . "</a></th>\n";
echo "<th width=\"2%\">&nbsp;</th>\n</tr>\n";

$css_switcher = new cssClassSwitcher();
echo $css_switcher->GetHoverJSFunction();
$css_switcher->enableHover();
$css_switcher->switchClass();

while($db->next_record()){
	$style = $css_switcher->getFullClass();
	echo "<tr" . $css_switcher->getHover() . "><td class=\"gruppe" . $db->f("gruppe") . "\">";
	echo "<img src=\"pictures/blank.gif\" alt=\"Gruppe\" border=\"0\" width=\"7\" height=\"12\"></td>\n";
	echo "<td$style>&nbsp; </td>";
	echo "<td$style><font size=\"-1\">";
	echo "<a href=\"" . $CANONICAL_RELATIVE_PATH_STUDIP;
	echo "seminar_main.php?auswahl=" . $db->f("Seminar_id") . "\">";
	echo htmlReady(mila($db->f("Name")));
	echo "</a></font></td>\n";
	echo "<td$style align=\"center\"><font size=\"-1\">";
	echo $db->f("count");
	echo "</font></td>\n";
	if ($loginfilenow[$db->f("Seminar_id")] == 0) {
		echo "<td$style align=\"center\"><font size=\"-1\">";
		echo _("nicht besucht") . "</font></td>\n";
	}
	else{
		echo "<td$style align=\"center\"><font size=\"-1\">";
		echo strftime("%x", $loginfilenow[$db->f("Seminar_id")]);
		echo "</font></td>";
	}
	echo "<td$style align=\"center\"><font size=\"-1\">";
	echo $db->f("status");
	echo "</font></td>\n";
	if($calendar_user_control_data["bind_seminare"][$db->f("Seminar_id")])
		$is_checked = " checked";
	else
		$is_checked = "";
	echo "<td$style>";
	echo "<input type=\"checkbox\" name=\"sem[" . $db->f("Seminar_id")
		. "]\" value=\"TRUE\"$is_checked></td></tr>\n",
	$css_switcher->switchClass();
}

echo "<tr><td class=\"blank\">&nbsp;</td></tr>\n";
echo "<tr><td class=\"blank\" colspan=\"6\" align=\"center\">";
echo "&nbsp;<input type=\"image\" " . makeButton("auswaehlen", "src") . " border=\"0\"></td></tr>\n";

// Dummy-Wert damit $sem auch ohne ausgewaehlte Seminare ausgewertet wird
echo "\n<input type=\"hidden\" name=\"sem[1]\" value=\"FALSE\">\n";
echo "<input type=\"hidden\" name=\"atime\" value=\"$atime\">";
echo "\n</form>\n";
echo "</table>";
echo "\n</td>\n";
echo "<td class=\"blank\" width=\"10%\" valign=\"top\">\n";
$info_content = array(array("kategorie" => _("Information:"),
											"eintrag" => array(	
												array("icon" => "pictures/ausruf_small.gif",
															"text" => _("Termine aus den ausgew&auml;hlten Veranstaltungen werden in Ihren Terminkalender &uuml;bernommen.")
											))));
										
print_infobox($info_content, "pictures/dates.jpg");
echo "</td></tr></table>\n";

echo "</tr><tr><td class=\"blank\">&nbsp;";
?>
