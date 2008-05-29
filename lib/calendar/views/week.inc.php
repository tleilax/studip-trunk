<?
# Lifter002: TODO
/**
* week.inc.php
*
* Shows the week calender
*
* @author		Peter Thienel <pthienel@web.de>
* @author 		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @version		$Id$
* @access		public
* @package		calendar
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// week.inc.php
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

/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);

//Imports
require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarWeek.class.php");

$aweek =& new DbCalendarWeek($atime, $calendar_user_control_data['type_week']);
$aweek->bindSeminarEvents($bind_seminare);
$tab = createWeekTable($aweek, $st, $et, $calendar_user_control_data['step_week'],
											FALSE, $calendar_user_control_data['link_edit']);
$rowspan = ceil(3600 / $calendar_user_control_data['step_week']);
$height = ' height="20"';

if($rowspan > 1){
	$colspan_1 = ' colspan="2"';
	$colspan_2 = $tab['max_columns'] + 4;
}
else{
	$colspan_1 = '';
	$colspan_2 = $tab['max_columns'] + 2;
}

if ($aweek->getType() == 7)
	$width = '1%';
else
	$width = '3%';

// Begin of output
//TODO: templates
include('lib/include/html_head.inc.php');

if ($forum["jshover"] == 1 AND $auth->auth["jscript"]) { // JS an und erwuenscht?
	echo "<script language=\"JavaScript\">";
	echo "var ol_textfont = \"Arial\"";
	echo "</script>";
	echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
	echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$GLOBALS['ASSETS_URL']."javascripts/overlib.js\"></SCRIPT>";
}

include('lib/include/header.php');
include('lib/include/links_sms.inc.php');

echo "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" align=\"center\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" align=\"center\"><br/>\n";
echo "<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" class=\"steelgroup0\">\n";
echo "<tr><td colspan=\"$colspan_2\">\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" align=\"center\" class=\"steelgroup0\">\n";
echo "<tr>\n";
echo "<td align=\"center\" width=\"15%\"><a href=\"$PHP_SELF?cmd=showweek&atime=";
echo $aweek->getStart() - 1 . "\">&nbsp;";
echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_previous.gif\"";
echo tooltip(_("eine Woche zur�ck")) . ">&nbsp;</a></td>\n";
echo "<td width=\"70%\" class=\"calhead\">";
printf(_("%s. Woche vom %s bis %s"), strftime("%V", $aweek->getStart()),
		strftime("%x", $aweek->getStart()), strftime("%x", $aweek->getEnd()));
echo "</td>\n";
echo "<td align=\"center\" width=\"15%\"><a href=\"$PHP_SELF?cmd=showweek&atime=";
echo $aweek->getEnd() + 259201 . "\">&nbsp;";
echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_next.gif\"";
echo tooltip(_("eine Woche vor")) . ">&nbsp;</a></td>\n";
echo "</tr></table>\n</td></tr>\n";

echo "<tr><td nowrap=\"nowrap\" align=\"center\" width=\"$width\"$colspan_1>";
if ($st > 0) {
	echo "<a href=\"calendar.php?cmd=showweek&atime=$atime&wtime=" . ($st - 1) . "\">";
	echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_up.gif\"";
	echo tooltip(_("zeig davor")) . "></a>";
}
else
	echo "&nbsp;&nbsp;&nbsp;";
// row with weekdays
echo "</td>" . $tab["table"][0];

echo "<td nowrap=\"nowrap\" align=\"center\" width=\"$width\"$colspan_1>";
if ($st > 0) {
	echo "<a href=\"$PHP_SELF?cmd=showweek&atime=$atime&wtime=" . ($st - 1) . "\">";
	echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_up.gif\"";
	echo tooltip(_("zeig davor")) . "></a>";
}
else
	echo "&nbsp;&nbsp;&nbsp;";
echo "</td></tr>\n";

// Zeile mit Tagesterminen ausgeben
echo "<tr><td class=\"precol1w\"$colspan_1 height=\"25\">" . _("Tag");
echo "</td>{$tab['table'][1]}<td class=\"precol1w\"$colspan_1>";
echo _("Tag") . "</td></tr>\n";
echo "<tr height=\"2\"><td colspan=\"" . (2 * $colspan_1 + $colspan_2) . "\"></tr>\n";

$j = $st;
for ($i = 2; $i < sizeof($tab["table"]); $i++) {
	echo "<tr>";

	if ($i % $rowspan == 0) {
		if ($rowspan == 1)
			echo "<td class=\"precol1w\"$height>$j</td>";
		else
			echo "<td class=\"precol1w\" rowspan=\"$rowspan\">$j</td>";
	}
	if ($rowspan > 1) {
		$minutes = (60 / $rowspan) * ($i % $rowspan);
		if ($minutes == 0)
			$minutes = "00";
		echo "<td class=\"precol2w\"$height>$minutes</td>\n";
	}

	echo $tab["table"][$i];

	if ($rowspan > 1)
		echo "<td class=\"precol2w\">$minutes</td>\n";
	if ($i % $rowspan == 0) {
		if($rowspan == 1)
			echo "<td class=\"precol1w\">$j</td>";
		else
			echo "<td class=\"precol1w\" rowspan=\"$rowspan\">$j</td>";
		$j = $j + ceil($calendar_user_control_data["step_week"] / 3600);
	}

	echo "</tr>\n";
}

echo "<tr><td$colspan_1 align=\"center\">";
if ($et < 23) {
	echo "<a href=\"$PHP_SELF?cmd=showweek&atime=$atime&wtime=" . ($et + 1) . "\">";
	echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_down.gif\"";
	echo tooltip(_("zeig danach")) . "></a>";
}
else
	echo "&nbsp";
echo "</td><td colspan=\"{$tab['max_columns']}\">&nbsp;</td>";
echo "<td$colspan_1 align=\"center\">";
if ($et < 23) {
	echo "<a href=\"$PHP_SELF?cmd=showweek&atime=$atime&wtime=" . ($et + 1) . "\">";
	echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_down.gif\"";
	echo tooltip(_("zeig danach")) . "></a>";
}
else
	echo "&nbsp;";
echo "</td></tr>\n</table>\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";
jumpTo($jmp_m, $jmp_d, $jmp_y);
echo "</table>\n";
?>
