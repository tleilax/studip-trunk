<?
/**
* day.inc.php
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
// day.inc.php
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
echo "<tr><td class=\"blank\" width=\"50%\">\n";
echo "<table width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"100%\">\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr>\n";
echo "<th width=\"10%\" height=\"40\"><a href=\"$PHP_SELF?cmd=showday&atime=";
echo $atime - 86400 . "\">\n";
$tooltip = tooltip(_("zur�ck"));
echo "<img border=\"0\" src=\"./pictures/forumrotlinks.gif\"$tooltip></a></th>\n";
echo "<th width=\"80%\" class=\"cal\"><b>\n";

echo $aday->toString("LONG") . ", " . $aday->getDate();
// event. Feiertagsnamen ausgeben
if ($hday = holiday($atime))
	echo "<br>" . $hday["name"];

echo "</b></th>\n";
echo "<th width=\"10%\"><a href=\"$PHP_SELF?cmd=showday&atime=";
echo $atime + 86400 . "\">\n";
$tooltip = tooltip(_("vor"));
echo "<img border=\"0\" src=\"./pictures/forumrot.gif\"$tooltip></a></th>\n";
echo "</tr>\n";

if ($st > 0) {
	echo "<tr><th colspan=\"3\"><a href=\"$PHP_SELF?cmd=showday&atime=";
	echo ($atime - ($at - $st + 1) * 3600) . "\">";
	$tooltip = tooltip(_("zeig davor"));
	echo "<img border=\"0\" src=\"./pictures/forumgraurauf.gif\"$tooltip></a></th></tr>\n";
}
echo "</table>\n</td></tr>\n<tr><td class=\"blank\">\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\">";

echo $tab["table"];

if ($et < 23) {
	echo "<tr><th colspan=\"" . $tab["max_columns"] . "\">";
	echo "<a href=\"$PHP_SELF?cmd=showday&atime=";
	echo ($atime + ($et - $at + 1) * 3600) . "\">";
	$tooltip = tooltip(_("zeig danach"));
	echo "<img border=\"0\" src=\"./pictures/forumgraurunt.gif\"$tooltip></a></th></tr>\n";
}
else
	echo "<tr><th colspan=\"" . $tab["max_columns"] . "\">&nbsp;</th></tr>\n";

echo "</table>\n</td></tr>\n</table>\n<td width=\"50%\" valign=\"top\" class=\"blank\">\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr><td>\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
jumpTo($jmp_m, $jmp_d, $jmp_y);
echo "</table></td></tr>\n";
$link = "$PHP_SELF?cmd=showday&atime=";
echo "<tr><td align=\"center\">".includeMonth($atime, $link)."</td></tr>\n";
echo "<tr><td>&nbsp;</td></tr>\n";
echo "</table>\n";
echo "</td></tr><tr><td class=\"blank\" width=\"100%\" colspan=\"2\">&nbsp;";
?>
