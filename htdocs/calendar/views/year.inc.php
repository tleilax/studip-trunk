<?
/**
* year.inc.php
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
// year.inc.php
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
echo "<tr><td class=\"blank\" width=\"100%\">\n";
echo "<table class=\"blank\" border=\"0\" width=\"98%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";
echo "<tr><td class=\"blank\">\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "<tr><th align=\"center\" width=\"10%\">\n";
echo "<a href=\"$PHP_SELF?cmd=showyear&atime=" . ($ayear->getStart() - 1) . "\">";
echo "<img border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/forumrotlinks.gif\" ";
echo tooltip(_("zur�ck")) . ">&nbsp;</a></th>\n";
echo "<th class=\"cal\" align=\"center\" width=\"80%\">\n";
echo "<font size=\"+2\"><b>" . $ayear->getYear() . "</b></font></th>\n";
echo "<th align=\"center\" width=\"10%\"><a href=\"$PHP_SELF?cmd=showyear&atime=";
echo ($ayear->getEnd() + 1) . "\">\n";
echo "<img border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/forumrot.gif\" ";
echo tooltip(_("vor")) . ">&nbsp;</a></th>\n";
echo "</tr></table>\n</td></tr>\n";
echo "<tr><td class=\"blank\"><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\">\n";
	
$days_per_month = array(31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);											
if (date("L", $ayear->getStart()))
	$days_per_month[2] = 29;
	
echo "<tr>";
for ($i = 1; $i < 13; $i++) {
	$ts_month += ($days_per_month[$i] - 1) * 86400;
	printf("<th width=\"8%%\"><a class=\"precol1\" href=\"%s?cmd=showmonth&atime=%s\">"
			,$PHP_SELF, ($ayear->getStart() + $ts_month));
	echo htmlentities(strftime("%B", $ts_month), ENT_QUOTES);
	echo "</a></th>\n";
}
echo "</tr>\n";

for ($i = 1; $i < 32; $i++) {
	echo "<tr>";
	for ($month = 1; $month < 13; $month++) {
		$aday = mktime(12, 0, 0, $month, $i, $ayear->getYear());
				
				if($i <= $days_per_month[$month]){
					$wday = date("w", $aday);
					if ($wday == 0 || $wday == 6)
						$weekend = " class=\"weekend\"";
					else
						$weekend = " class=\"weekday\"";
						
					if ($month == 1)
						echo "<td$weekend height=\"25\">";
					else
						echo "<td$weekend>";
					
					if($apps = $ayear->numberOfEvents($aday)) {
						echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr>";
						echo "<td$weekend>";
					}
					
					$weekday = "<font size=\"2\">" . wday($aday, "SHORT") . "</font>";
						
					// noch wird nicht nach Wichtigkeit bestimmter Feiertage unterschieden
					$hday = holiday($aday);
					switch ($hday["col"]) {
					
						case "1":
							if (date("w", $aday) == "0") {
								echo "<a class=\"sday\" href=\"$PHP_SELF?cmd=showday&atime=$aday\">";
								echo "<b>$i</b></a> " . $weekday;
								$count++;
								}
							else {
								echo "<a class=\"day\" href=\"$PHP_SELF?cmd=showday&atime=$aday\">";
								echo "<b>$i</b></a> " . $weekday;
							}
							break;
						case "2":
						case "3":
							if (date("w", $aday) == "0") {
								echo "<a class=\"sday\" href=\"$PHP_SELF?cmd=showday&atime=$aday\">";
								echo "<b>$i</b></a> " . $weekday;
								$count++;
							}
							else {
								echo "<a class=\"hday\" href=\"$PHP_SELF?cmd=showday&atime=$aday\">";
								echo "<b>$i</b></a> " . $weekday;
							}
							break;
						default:
							if (date("w", $aday) == "0") {
								echo "<a class=\"sday\" href=\"$PHP_SELF?cmd=showday&atime=$aday\">";
								echo "<b>$i</b></a> " . $weekday;
								$count++;
								}
							else {
								echo "<a class=\"day\" href=\"$PHP_SELF?cmd=showday&atime=$aday\">";
								echo "<b>$i</b></a> " . $weekday;
							}
					}
					
					if	($apps) {
						if	($apps > 1) {
							echo "</td><td$weekend align=\"right\">";
							echo "<img src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/icon-uhr.gif\" ";
							echo tooltip(sprintf(_("%s Termine"), $apps)) . " border=\"0\">";
							echo "</td></tr></table>\n";
						}
						else {
							echo "</td><td$weekend align=\"right\">";
							echo "<img src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/icon-uhr.gif\" ";
							echo tooltip(_("1 Termin")) . " border=\"0\">";
							echo "</td></tr></table>";
						}
					}
					echo "</td>";
				}
				else
					echo "<td class=\"weekday\">&nbsp;</td>";
			}
			echo "</tr>\n";
			
		}
		echo "<tr>";
		$ts_month = 0;
		for ($i = 1; $i < 13; $i++){ 
			$ts_month += ($days_per_month[$i] - 1) * 86400;
			echo "<th width=\"8%\"><a class=\"precol1\" href=\"$PHP_SELF?cmd=showmonth&atime=";
			echo ($ayear->getStart() + $ts_month) . "\">"
					. htmlentities(strftime("%B", $ts_month), ENT_QUOTES) . "</a></th>\n";
		}
		echo "</tr></table>\n</td></tr>\n";
		jumpTo($jmp_m, $jmp_d, $jmp_y);
		echo "\n</table>\n</td></tr>\n";
		echo "<tr><td class=\"blank\" width=\"100%\">&nbsp;";
?>
