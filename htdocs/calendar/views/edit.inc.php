<?
/**
* edit.inc.php
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
// edit.inc.php
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

require("$ABSOLUTE_PATH_STUDIP/html_head.inc.php");
require("$ABSOLUTE_PATH_STUDIP/header.php");
require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/navigation.inc.php");


echo "<table width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
echo "<table class=\"blank\" width=\"99%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";

if (!empty($err)) {
	$error_sign = "<font color=\"#FF0000\" size=\"+1\"><b>&nbsp;*&nbsp;</b></font>";
	$error_message = sprintf(_("Bitte korrigieren Sie die mit %s gekennzeichneten Felder.%s"),
		$error_sign, $err_message);
	my_info($error_message, "blank", 2);
}

echo "<tr><td class=\"blank\" width=\"99%\" valign=\"top\">\n";
echo "<form action=\"$PHP_SELF?cmd=edit\" method=\"post\">";
echo "<table class=\"blank\" width=\"99%\" border=\"0\" cellspacing=\"0\" cellpadding=\"10\">\n";

if (isset($atermin) && get_class($atermin) == "seminarevent")
	// form is not editable
	$disabled = " style=\"color:#000000; background-color:#FFFFFF;\" disabled=\"disabled\"";
else
	$disabled = '';

echo "<tr><th width=\"100%\" align=\"left\">";
echo $edit_mode_out;
echo "\n</th></tr>\n";

$css_switcher = new cssClassSwitcher();
$css_switcher->switchClass();

########################################################################################

if (!$set_recur_x) {
	if (isset($atermin) && get_class($atermin) == "seminarevent") {
		echo "<tr>\n<td class=\"" . $css_switcher->getClass() . "\" width=\"100%\">\n";
		echo "<font size=\"-1\">Veranstaltung:&nbsp; " . htmlReady($atermin->getSemName());
		echo "</font></td>\n</tr>\n";
		$css_switcher->switchClass();
	}

	echo "<tr>\n<td class=\"" . $css_switcher->getClass() . "\" width=\"100%\">\n";
	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
	echo "<tr valign=\"center\">\n<td><font size=\"-1\">";
	echo _("Beginn:") . " </font></td>\n<td><font size=\"-1\"> &nbsp;";
	echo _("Tag");
	echo " <input type=\"text\" name=\"start_day\" size=\"2\" maxlength=\"2\" value=\"$start_day\"$disabled>\n";
	echo " . <input type=\"text\" name=\"start_month\" size=\"2\" maxlength=\"2\" value=\"$start_month\"\"$disabled>\n";
	echo " . <input type=\"text\" name=\"start_year\" size=\"4\" maxlength=\"4\" value=\"$start_year\"$disabled>\n";
	echo "&nbsp; &nbsp;";
	echo _("Uhrzeit");
	echo " <select name=\"start_h\" size=\"1\"$disabled>\n";
	
	for ($i = 0;$i < 24;$i++) {
		echo "<option";
		if ($i == $start_h)
			echo " selected";
		if ($i < 10)
			echo ">0$i";
		else
			echo ">$i";
	}
			
	echo "</select> : <select name=\"start_m\" size=\"1\"$disabled>\n";
	
	for ($i = 0;$i < 60;$i += 5) {
		echo "<option";
		if ($i == $start_m)
			echo " selected";
		if ($i < 10)
			echo ">0$i";
		else
			echo ">$i";
	}
	
	echo "</select>";
	echo ($err["start_time"] ? $error_sign : "");
	echo "&nbsp; &nbsp; <input type=\"checkbox\" name=\"wholeday\"";
	echo ($wholeday ? 'checked="checked"' : '') . "$disabled> &nbsp;";
	echo _("ganzt&auml;gig");
	$info = _("Als ganzt�gig markierte Termine beginnen um 00:00 Uhr am angegebenen Starttag und enden um 23.59 am angegeben Endtag.");
	echo "&nbsp;&nbsp;&nbsp;<img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/info.gif\"";
	echo tooltip($info, TRUE, TRUE) . ">\n";
	echo "</font></td>\n</tr>\n";
	echo "<tr><td colspan=\"2\"><font size=\"-1\">&nbsp;</font></td></tr>\n";
	echo "<tr valign=\"center\"><td><font size=\"-1\">";
	echo _("Ende:") . " </font></td>\n<td><font size=\"-1\"> &nbsp;";
	echo _("Tag");
	echo " <input type=\"text\" name=\"end_day\" size=\"2\" maxlength=\"2\" value=\"$end_day\"$disabled>\n";
	echo " . <input type=\"text\" name=\"end_month\" size=\"2\" maxlength=\"2\" value=\"$end_month\"$disabled>\n";
	echo " . <input type=\"text\" name=\"end_year\" size=\"4\" maxlength=\"4\" value=\"$end_year\"$disabled>\n";
	echo "&nbsp; &nbsp;";
	echo _("Uhrzeit");
	echo " <select name=\"end_h\" size=\"1\"$disabled>\n";
	
	for ($i = 0;$i < 24;$i++) {
		echo "<option";
		if ($i == $end_h)
			echo " selected";
		if ($i < 10)
			echo ">0$i";
		else
			echo ">$i";
	}
	
	echo "</select>&nbsp;:&nbsp;<select name=\"end_m\" size=\"1\"$disabled>\n";
	
	for ($i = 0;$i < 60;$i += 5) {
		echo "<option";
		if ($i == $end_m)
			echo " selected";
		if ($i < 10)
			echo ">0$i";
		else
			echo ">$i";
	}
	
	echo "</select>";
	echo ($err["end_time"] ? $error_sign : "");
	echo "</font></td>\n</tr>\n</table>\n</td>\n</tr>\n";
	
	$css_switcher->switchClass();
	echo "<tr><td class=\"" . $css_switcher->getClass() . "\">\n";
	echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	echo "<tr><td><font size=\"-1\">";
	echo _("Zusammenfassung:") . "&nbsp;&nbsp;</font></td>\n";
	echo "<td>";
	echo "<input type=\"text\" name=\"txt\" size=\"50\" maxlength=\"255\" value=\"$txt\"$disabled></input>";
	printf("%s</td>\n", ($err["titel"] ? $error_sign : ""));
	echo "</tr><tr>\n";
	echo "<tr><td colspan=\"2\"><font size=\"-1\">&nbsp;</font></td></tr>\n";
	echo "<td><font size=\"-1\">";
	echo _("Beschreibung:") . "&nbsp;&nbsp;<font></td>";
	echo "<td><textarea name=\"content\" cols=\"48\" rows=\"5\" wrap=\"virtual\"$disabled>";
	echo $content;
	echo "</textarea></td>\n";
	echo "</tr>\n</table>\n</td>\n</tr>\n";
	
	$css_switcher->switchClass();
	echo "<tr><td class=\"" . $css_switcher->getClass() . "\">\n";
	echo "<font size=\"-1\">";
	echo _("Kategorie:") . "&nbsp;&nbsp;</font>";
	echo "<select name=\"cat\" size=\"1\"$disabled>\n";
	
	if (isset($atermin) && get_class($atermin) == "seminarevent") {
		if (!isset($cat))
			$cat = 1;
		printf("<option value=\"%s\" selected>%s", $cat, htmlReady($TERMIN_TYP[$cat]["name"]));
		echo "</select>\n";
	}
	else {
		if (!isset($cat))
			$cat = 0;
		echo "<option value=\"0\"";
		if ($cat == 0)
			echo " selected";
		echo ">" . _("keine Auswahl");
		foreach ($PERS_TERMIN_KAT as $key => $value) {
			printf("<option value=\"%s\"", $key);
			if ($cat == $key)
				echo " selected";
			printf(">%s\n", htmlReady($value["name"]));
		}
		echo "</select>\n&nbsp; &nbsp;";
		echo "<input type=\"text\" name=\"cat_text\" size=\"30\" maxlength=\"255\" value=\"$cat_text\">\n";
		$info = _("Sie k�nnen beliebige Kategorien in das Freitextfeld eingeben. Trennen Sie einzelne Kategorien bitte durch ein Komma.");
		echo "&nbsp;&nbsp;&nbsp;<img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/info.gif\"";
		echo tooltip($info, TRUE, TRUE) . ">\n";
	}	
	echo "</td>\n</tr>\n";
	
	$css_switcher->switchClass();
	echo "<tr><td class=\"" . $css_switcher->getClass() . "\">\n";
	echo "<font size=\"-1\">";
	echo _("Raum:") . "&nbsp;&nbsp;";
	echo "<input type=\"text\" name=\"loc\" size=\"30\" maxlength=\"255\" value=\"$loc\"$disabled>";
	echo "</td>\n</tr>\n";
	
	if (get_class($atermin) != "seminarevent") {
		$css_switcher->switchClass();
		echo "<tr><td class=\"" . $css_switcher->getClass() . "\">\n";
		$info = _("Private und vertrauliche Termine sind nur f�r Sie sichtbar. �ffentliche Termine werden auf ihrer internen Homepage auch anderen Nutzern bekanntgegeben.");
		echo "<font size=\"-1\">";
		echo _("Sichtbarkeit:") . "&nbsp;&nbsp;\n";
		echo "<select name=\"via\" size=\"1\">\n";
		$via_names = array(
				"PUBLIC"       => _("&ouml;ffentlich"),
				"PRIVATE"      => _("privat"),
				"CONFIDENTIAL" => _("vertraulich"));
		foreach ($via_names as $key => $via_name) {
			echo "<option value=\"$key\"";
			if ($via == $key)
				echo " selected";
			echo " />$via_name\n";
		}
		echo "</select>&nbsp;&nbsp;&nbsp;";
		echo "<img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/info.gif\"";
		echo tooltip($info, TRUE, TRUE) . ">\n";
		
		echo "&nbsp;&nbsp;&nbsp;" . _("Priorit&auml;t:");
		echo "&nbsp;&nbsp;<select name=\"priority\" size=\"1\">\n";
		$priority_names = array(
				_("keine Angabe"),
				_("hoch"),
				_("mittel"),
				_("niedrig"));
		for ($i = 0; $i < 4; $i++) {
			echo "<option value=\"$i\"";
			if ($priority == $i)
				echo " selected";
			echo " />{$priority_names[$i]}\n";
		}
		echo "</select></font></td>\n</tr>\n";
	
		$css_switcher->switchClass();
		echo "<tr><td class=\"" . $css_switcher->getClass() . "\">";
		echo "<font size=\"-1\">";
		if ($atermin)
			echo htmlReady($atermin->toStringRecurrence());
		else
			echo _("Der Termin wird nicht wiederholt.");
		echo "&nbsp; &nbsp; &nbsp;";
		echo "<input style=\"vertical-align: middle;\" type=\"image\" " . makeButton("bearbeiten", "src"). " name=\"set_recur\" border=\"0\">\n";
		echo "</font></td>\n</tr>\n";
	}
}
######################################################################################
else{



	if (!isset($atermin) || get_class($atermin) != "seminarevent") {
		echo "<tr><td align=\"center\" class=\"" .  $css_switcher->getClass();
		echo "\" colspan=\"2\" nowrap=\"nowrap\">\n<font size=\"-1\">&nbsp;";

		if ($mod == "SINGLE")
			echo "<input type=\"image\" name=\"mod_s\" " . makeButton("keine2", "src") . " border=\"0\">\n";
		else
			echo "<input type=\"image\" name=\"mod_s\" " . makeButton("keine", "src") . " border=\"0\">\n";
		echo " ";
		if ($mod == "DAILY")
			echo "<input type=\"image\" name=\"mod_d\" " . makeButton("taeglich2", "src") . " border=\"0\">\n";
		else
			echo "<input type=\"image\" name=\"mod_d\" " . makeButton("taeglich", "src") . " border=\"0\">\n";
		echo " ";
		if ($mod == "WEEKLY")
			echo "<input type=\"image\" name=\"mod_w\" " . makeButton("woechentlich2", "src") . " border=\"0\">\n";
		else
			echo "<input type=\"image\" name=\"mod_w\" " . makeButton("woechentlich", "src") . " border=\"0\">\n";
		echo " ";
		if ($mod == "MONTHLY")
			echo "<input type=\"image\" name=\"mod_m\" " . makeButton("monatlich2", "src") . " border=\"0\">\n";
		else
			echo "<input type=\"image\" name=\"mod_m\" " . makeButton("monatlich", "src") . " border=\"0\">\n";
		echo " ";
		if($mod == "YEARLY")
			echo "<input type=\"image\" name=\"mod_y\" " . makeButton("jaehrlich2", "src") . " border=\"0\">\n";
		else
			echo "<input type=\"image\" name=\"mod_y\" " . makeButton("jaehrlich", "src") . " border=\"0\">\n";
		
		echo "</font></td></tr>\n";
	}
	
	if ($mod == "MONTHLY" || $mod == "YEARLY") {
		$form_week_arr = array(
				"1" => _("ersten"),
				"2" => _("zweiten"),
				"3" => _("dritten"),
				"4" => _("vierten"),
				"5" => _("letzten")
		);
		
		$form_day_arr = array(
				"1" => _("Montag"),
				"2" => _("Dienstag"),
				"3" => _("Mittwoch"),
				"4" => _("Donnerstag"),
				"5" => _("Freitag"),
				"6" => _("Samstag"),
				"7" => _("Sonntag")
		);
		
		$form_month_arr = array(
				"1" => _("Januar"),
				"2" => _("Februar"),
				"3" => _("M&auml;rz"),
				"4" => _("April"),
				"5" => _("Mai"),
				"6" => _("Juni"),
				"7" => _("Juli"),
				"8" => _("August"),
				"9" => _("September"),
				"10" => _("Oktober"),
				"11" => _("November"),
				"12" => _("Dezember")
		);
	}
	
	switch ($mod) {
		case "DAILY":
			$css_switcher->switchClass();
			echo "<tr>\n<td nowrap=\"nowrap\" class=\"" . $css_switcher->getClass() . "\">\n";
			echo "<font size=\"-1\">&nbsp; <input type=\"radio\" name=\"type_d\" value=\"daily\"";
			if ($type_d == "daily" || $type_d == "")
				echo " checked";
			echo ">&nbsp;" . _("Alle") . " &nbsp;";
			echo "<input type=\"text\" name=\"linterval_d\" size=\"3\" maxlength=\"3\" value=\"";
			echo ($linterval_d ? $linterval_d : "1");
			echo "\">&nbsp;" . _("Tage");
			echo ($err["linterval_d"] ? $error_sign : "");
			echo "&nbsp; &nbsp; &nbsp; ";
			echo "<input type=\"radio\" name=\"type_d\" value=\"wdaily\"";
			if ($type_d == "wdaily")
				echo " checked";
			echo ">&nbsp;" . _("Jeden Werktag") ."</font></td>";
			echo "</td></tr>\n";
			break;
			
		case "WEEKLY":
			if (!$wdays)
				$wdays = array();
			
			$css_switcher->switchClass();
			echo "<tr><td nowrap=\"nowrap\" class=\"" . $css_switcher->getClass() . "\">\n";
			echo "<font size=\"-1\">&nbsp; ";
			$out_1 = '<input type="text" name="linterval_w" size="3" maxlength="3" value="';
			$out_1 .= ($linterval_w ? $linterval_w : "1");
			$out_1 .= '">';
			printf(_("Alle %s Wochen %s am:"), $out_1, $err["linterval_w"] ? $error_sign : "");
			echo "</font><table width=\"60%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\n";
			echo "<tr><td width=\"8\" rowspan=\"2\">&nbsp;</td>\n<td width=\"23%\">";
			echo "<input type=\"checkbox\" name=\"wdays[]\" value=\"1\"";
			if(in_array(1, $wdays)) echo " checked=\"checked\"";
			echo "><font size=\"-1\">&nbsp;" . _("Montag") . "</font></td>\n";
			echo "<td width=\"23%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"2\"";
			if(in_array(2, $wdays)) echo " checked=\"checked\"";
			echo "><font size=\"-1\">&nbsp;" . _("Dienstag") . "</font></td>\n";
			echo "<td width=\"23%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"3\"";
			if(in_array(3, $wdays)) echo " checked=\"checked\"";
			echo "><font size=\"-1\">&nbsp;" . _("Mittwoch") . "</font></td>\n";
			echo "<td width=\"23%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"4\"";
			if(in_array(4, $wdays)) echo " checked=\"checked\"";
			echo "><font size=\"-1\">&nbsp;" . _("Donnerstag") . "</font></td>\n";
			echo "</tr><tr>\n";
			echo "<td><input type=\"checkbox\" name=\"wdays[]\" value=\"5\"";
			if(in_array(5, $wdays)) echo " checked=\"checked\"";
			echo "><font size=\"-1\">&nbsp;" . _("Freitag") . "</font></td>\n";
			echo "<td><input type=\"checkbox\" name=\"wdays[]\" value=\"6\"";
			if(in_array(6, $wdays)) echo " checked=\"checked\"";
			echo "><font size=\"-1\">&nbsp;" . _("Samstag") . "</font></td>\n";
			echo "<td colspan=\"2\"><input type=\"checkbox\" name=\"wdays[]\" value=\"7\"";
			if(in_array(7, $wdays)) echo " checked=\"checked\"";
			echo "><font size=\"-1\">&nbsp;" . _("Sonntag") . "</font></td>\n";
			echo "</tr>\n</table></td></tr>\n";
			break;
			
		case "MONTHLY":
			$css_switcher->switchClass();
			echo "<tr><td nowrap=\"nowrap\" class=\"" . $css_switcher->getClass() . "\">\n";
			echo "<font size=\"-1\">&nbsp; <input type=\"radio\" name=\"type_m\" value=\"day\"";
			if ($type_m == "day" || $type_m == "") echo " checked";
			echo ">&nbsp;";
			$out_1 = "&nbsp;";
			$out_1 .= "<input type=\"text\" name=\"day_m\" size=\"2\" maxlength=\"2\" value=\"";
			$out_1 .= ($day_m != '' ? "$day_m" : "$start_day");
			$out_1 .= "\">" . ($err['sinterval_m'] ? $error_sign : "") . "&nbsp;.&nbsp; ";
			$out_2 = "&nbsp;";
			$out_2 .= "<input type=\"text\" name=\"linterval_m1\" size=\"3\" maxlength=\"3\" value=\"";
			$out_2 .= ($linterval_m1 != '' ? "$linterval_m1" : "1");
			$out_2 .= "\">" . ($err["linterval_m1"] ? $error_sign : "") . "&nbsp;";
			printf(_("Wiederholt am %s alle %s Monate"), $out_1, $out_2);
			echo "<br><br>&nbsp; <input type=\"radio\" name=\"type_m\" value=\"wday\"";
			if ($type_m == "wday") echo " checked";
			echo ">&nbsp;" . _("Jeden") . "&nbsp;";
			echo "<select name=\"sinterval_m\" size=\"1\">\n";
			
			foreach ($form_week_arr as $key => $value) {
				echo "<option value=\"$key\"";
				if($sinterval_m == $key)
					echo " selected";
				echo ">$value\n";
			}
			
			echo "</select>\n";
			echo "<select name=\"wday_m\" size=\"1\">\n";
			
			foreach ($form_day_arr as $key => $value) {
				echo "<option value=\"$key\"";
				if($wday_m == $key)
					echo " selected";
				echo ">$value\n";
			}
			
			echo "</select>\n";
			echo "&nbsp;" . _("alle");
			echo " &nbsp;<input type=\"text\" name=\"linterval_m2\" size=\"3\" maxlength=\"3\" value=\"";
			echo ($linterval_m2 ? $linterval_m2 : "1");
			echo "\">" . ($err["linterval_m2"] ? $error_sign : "");
			echo "&nbsp;" . _("Monate") . "</font></td></tr>\n";
			break;
			
		case "YEARLY":
			if(!$month_y1)
				$month_y1 = $start_month;
			if(!$month_y2)
				$month_y2 = $start_month;
				
			$css_switcher->switchClass();
			echo "<tr><td nowrap=\"nowrap\" class=\"" . $css_switcher->getClass() . "\">\n";
			echo "<font size=\"-1\">&nbsp; <input type=\"radio\" name=\"type_y\" value=\"day\"";
			if ($type_y == "day" || $type_y == "") echo " checked";
			echo ">&nbsp;" . _("Jeden") . "&nbsp; ";
			echo "<input type=\"text\" name=\"day_y\" size=\"2\" maxlength=\"2\" value=\"";
			echo ($day_y ? $day_y : $start_day);
			echo "\">" . ($err["day_y"] ? $error_sign : "");
			echo "&nbsp;.&nbsp;\n";
			echo "<select name=\"month_y1\" size=\"1\">\n";
			
			foreach ($form_month_arr as $key => $value) {
				echo "<option value=\"$key\"";
				if($month_y1 == $key)
					echo " selected";
				echo ">$value\n";
			}
			
			echo "</select>\n";
			echo "<br><br>&nbsp; <input type=\"radio\" name=\"type_y\" value=\"wday\"";
			if ($type_y == "wday") echo " checked";
			echo ">&nbsp;";
			$out_1 = "&nbsp; ";
			$out_1 .= "<select name=\"sinterval_y\" size=\"1\">\n";
			
			reset($form_week_arr);
			foreach ($form_week_arr as $key => $value) {
				$out_1 .= "<option value=\"$key\"";
				if($sinterval_y == $key)
					$out_1 .= " selected";
				$out_1 .= ">$value\n";
			}
			
			$out_1 .= "</select>\n<select name=\"wday_y\" size=\"1\">\n";
			
			foreach ($form_day_arr as $key => $value) {
				$out_1 .= "<option value=\"$key\"";
				if ($wday_y == $key)
					$out_1 .= " selected";
				$out_1 .= ">$value\n";
			}
			
			$out_1 .= "</select>&nbsp;";
			printf(_("Jeden %s im"), $out_1);
			echo "&nbsp;<select name=\"month_y2\" size=\"1\">\n";
			
			foreach ($form_month_arr as $key => $value) {
				echo "<option value=\"$key\"";
				if ($month_y2 == $key)
					echo " selected";
				echo ">$value\n";
			}
			echo "</select></font></td></tr>\n";
			break;
	}	
	
	$css_switcher->switchClass();
	echo "<tr><td class=\"" . $css_switcher->getClass() . "\">";
	
	if ($mod != 'SINGLE') {
		// end of recurrence
		echo '<table border="0" cellspacing="0" cellpadding="0">';
		echo "\n<tr><td><font size=\"-1\">&nbsp; ";
		echo _("Wiederholung endet:") . '</font></td>';
		echo "<td><font size=\"-1\">&nbsp; ";
		echo "<input type=\"radio\" name=\"exp_c\" value=\"never\"";
		if ($exp_c == "never") echo " checked";
		echo ">" . _("nie");
		echo "<br>&nbsp; <input type=\"radio\" name=\"exp_c\" value=\"date\"";
		if ($exp_c == "date") echo " checked";
		echo ">" . _("am:");
		echo "&nbsp; <input type=\"text\" size=\"2\" maxlength=\"2\" name=\"exp_day\" value=\"";
		echo (($exp_day && $exp_c == "date") ? $exp_day : "TT");
		echo "\">&nbsp;.&nbsp;";
		echo "<input type=\"text\" size=\"2\" maxlength=\"2\" name=\"exp_month\" value=\"";
		echo (($exp_month && $exp_c == "date") ? $exp_month : "MM");
		echo "\">&nbsp;.&nbsp;";
		echo "<input type=\"text\" size=\"4\" maxlength=\"4\" name=\"exp_year\" value=\"";
		echo (($exp_year && $exp_c == "date") ? $exp_year : "JJJJ");
		echo "\">" . ($err["exp_time"] ? $error_sign : "");
		echo "<br>&nbsp; <input type=\"radio\" name=\"exp_c\" value=\"count\"";
		if ($exp_c == "count") echo " checked";
		echo ">" . sprintf(_("nach %s Wiederholungen"),
				'&nbsp; <input type="text" size="3" maxlength="3" name="exp_count" value="'
				. (($exp_count && $exp_c == "count") ? $exp_count : '1') . '">'
				. ($err['exp_count'] ? $error_sign : '') . ' &nbsp;');
		echo "</font></td></tr>\n</table>\n";
		echo "</td>\n</tr>\n";
		
		// exceptions
		$css_switcher->switchClass();
		echo "<tr><td class=\"" . $css_switcher->getClass() . "\">";
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "<tr><td valign=\"middle\">\n";
		echo "<font size=\"-1\"><br>&nbsp; ";
		echo _("Ausnahmen:") . '&nbsp; ';
		echo "<input type=\"text\" size=\"2\" maxlength=\"2\" name=\"exc_day\" value=\"TT\">";
		echo "&nbsp;.&nbsp;";
		echo "<input type=\"text\" size=\"2\" maxlength=\"2\" name=\"exc_month\" value=\"MM\">";
		echo "&nbsp;.&nbsp;";
		echo "<input type=\"text\" size=\"4\" maxlength=\"4\" name=\"exc_year\" value=\"JJJJ\">";
		echo ($err["exc_time"] ? $error_sign : "");
		echo '&nbsp;&nbsp;';
		echo "<input type=\"image\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/add_right.gif\"";
		echo " name=\"add_exc\">";
		echo "&nbsp; &nbsp;</font></td><td><font size=\"-1\">\n";
		echo "<select name=\"exc_delete[]\" size=\"4\" multiple=\"multiple\" style=\"width:170px; vertical-align:middle;\">\n";
		foreach ($exceptions as $exception) {
			echo "<option value=\"$exception\">" . strftime('%A, %x', $exception);
			echo "</option>\n";
		}
		echo "</select>\n</font></td></tr>\n";
		echo "<tr><td>&nbsp;</td>\n<td><input style=\"vertical-align:middle;\" type=\"image\" ";
		echo " src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/trash.gif\" name=\"del_exc\">\n";
		echo '<font size="-1">' . _("ausgew&auml;hlte l&ouml;schen") . '</font>';
		echo "</td></tr></table>\n</td>\n</tr>\n";
		
	}
	else {
		echo "<font size=\"-1\">&nbsp; ";
		echo _("Der Termin wird nicht wiederholt.");
		echo "</font></td>\n</tr>\n";
	}
}

#######################################################################################

	
if (isset($atermin) && get_class($atermin) == "seminarevent") {
	$db = new DB_Seminar();
	$query = "SELECT name FROM seminare WHERE Seminar_id='".$atermin->getSeminarId()."'";
	$db->query($query);
	$db->next_record();
	$link_to_seminar = "<a href=\"" . $CANONICAL_RELATIVE_PATH_STUDIP
										. "seminar_main.php?auswahl=" . $atermin->getSeminarId()
										. "\">" . htmlReady($db->f("name")) . "</a>";
	$permission = get_perm($atermin->getSeminarId());
	
	// create infobox entries
	$info_box['sem1'] = sprintf(_("Dieser Termin geh&ouml;rt zur Veranstaltung:<p>%s</p>Veranstaltungstermine k&ouml;nnen nicht im pers&ouml;nlichen Terminkalender bearbeitet werden.")
			, $link_to_seminar);
	$info_box['sem2'] = sprintf(_("<a href=\"%s?cmd=bind\">W&auml;hlen</a> Sie aus, welche Veranstaltungstermine in Ihrem Terminkalender angezeigt werden sollen.")
			, $PHP_SELF);
	if ($permission == "tutor" || $permission == "dozent") {
		$link_to_seminar = sprintf("<a href=\"%sadmin_dates.php?range_id=%s&show_id=%s\">"
				, $CANONICAL_RELATIVE_PATH_STUDIP, $atermin->getSeminarId(), $atermin->getId());
		$info_box['sem3'] = sprintf(_("Um diesen Termin zu bearbeiten, wechseln Sie bitte in die %sTerminverwaltung</a>.")
				, $link_to_seminar);
		
		$info_box['all'][0]['kategorie'] = _("Information:");
		$info_box['all'][0]['eintrag'][] = array("icon" => "pictures/ausruf_small.gif",
				"text" => $info_box['sem1']);
		$info_box['all'][1]['kategorie'] = _("Aktion:");
		$info_box['all'][1]['eintrag'][] = array("icon" => "pictures/meinesem.gif",
				"text" => $info_box['sem2']);
		$info_box['all'][1]['eintrag'][] = array("icon" => "pictures/admin.gif",
				"text" => $info_box['sem3']);
	}
	else {
		$info_box['all'][0]['kategorie'] = _("Information:");
		$info_box['all'][0]['eintrag'][] = array("icon" => "pictures/ausruf_small.gif",
				"text" => $info_box['sem1']);
		$info_box['all'][1]['kategorie'] = _("Aktion:");
		$info_box['all'][1]['eintrag'][] = array("icon" => "pictures/meinesem.gif",
				"text" => $info_box['sem2']);
	}
}
else {
	$css_switcher->switchClass();
	echo "<tr><td class=\"" . $css_switcher->getClass() . "\" align=\"center\" nowrap=\"nowrap\">\n";
	echo "<input type=\"hidden\" name=\"atime\" value=\"$atime\">\n";
	echo "<input type=\"hidden\" name=\"mod_err\" value=\"$mod_err\">\n";
	echo "<input type=\"hidden\" name=\"mod_prv\" value=\"$mod\">\n";
	echo "<input type=\"hidden\" name=\"mod\" value=\"$mod\">\n";
	if ($set_recur_x) {
		echo "<input type=\"image\" " . makeButton("zurueck", "src"). " name=\"back_recur\" border=\"0\">\n";
		echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ";
		echo "<input type=\"hidden\" name=\"set_recur_x\" value=\"1\">\n";
	}
	if ($atime && get_class($atermin) == 'calendarevent') {
		if ($count_events < $CALENDAR_MAX_EVENTS)
			echo "<input type=\"image\" " . makeButton("terminspeichern", "src"). " name=\"store\" border=\"0\">\n";
	}
	else {
		echo "<input type=\"hidden\" name=\"termin_id\" value=\"$termin_id\">\n";
		echo "<input type=\"image\" " . makeButton("terminaendern", "src"). " border=\"0\" name=\"change\">&nbsp; &nbsp;";
		echo "<input type=\"image\" " . makeButton("loeschen", "src"). " border=\"0\" name=\"del\">\n";
	}
	echo "<input type=\"image\" " . makeButton("abbrechen", "src"). " border=\"0\" name=\"cancel\">\n";

	// create infobox entries
	if ($count_events >= $CALENDAR_MAX_EVENTS) {
		$info_box['count'] = _("Sie k&ouml;nnen keine weiteren Termine mehr speichern!")
				. '<br><br>'
				. sprintf(_("L&ouml;schen Sie &auml;ltere Termine, oder w&auml;hlen Sie eine automatische L&ouml;schmethode in ihren %s Kalenderoptionen%s."),
				"<a href=\"$PHP_SELF?cmd=changeview&atime=$atime\">",
				"</a>");
	}
	else {
		$info_box['count'] = sprintf(_("Sie k&ouml;nnen noch %s Termine speichern."),
				$CALENDAR_MAX_EVENTS - $count_events);
	}
	$info_box['all'][0]['kategorie'] = _("Information:");
	$info_box['all'][0]['eintrag'][] = array("icon" => "pictures/ausruf_small.gif",
			"text" => $info_box['count']);
}


echo "</td></tr></table></form>\n</td>\n";
echo "<td class=\"blank\" align=\"center\" valign=\"top\" width=\"1%\">\n";
print_infobox($info_box['all'], "pictures/dates.jpg");
echo "</td></tr>\n";
echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";


echo "</table></td></tr></table><br />\n";
echo "</td></tr></table>\n";

?>
