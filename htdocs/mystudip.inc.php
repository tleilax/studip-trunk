<?
/**
* generates drop-down box for language selection
*
* This function generates a drop-down box for language selection.
* Language could be given as selected default.
*
* @access	public        
* @param		string	pre-selected language (in "de_DE" style)
*/
function select_language($selected_language = "") {  
	global $INSTALLED_LANGUAGES, $DEFAULT_LANGUAGE;
	
	if (!isset($selected_language)) {
		$selected_language = $DEFAULT_LANGUAGE;
	}

	echo "<select name=\"forced_language\" width=30>";
	foreach ($INSTALLED_LANGUAGES as $temp_language => $temp_language_settings) {
		if ($temp_language == $selected_language) {
			echo "<option selected value=\"$temp_language\">" . $temp_language_settings["name"] . "</option>";
		} else {
			echo "<option value=\"$temp_language\">" . $temp_language_settings["name"] . "</option>";
		}
	}

	echo "</select>";

	return;
}


//Anpassen der Ansicht
function change_general_view() {
	global $PHP_SELF, $_language;
		
	$db=new DB_Seminar;
	$cssSw=new cssClassSwitcher;		

	echo "<table width =\"100%\" cellspacing=0 cellpadding=0 border=0><tr>\n";
	echo "<td class=\"topic\" colspan=2><img src=\"pictures/einst.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;" . _("Allgemeine Stud.IP-Einstellungen anpassen") . "</b></td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=2>&nbsp;\n";
	echo "<blockquote><p>\n";
	echo _("Hier k&ouml;nnen Sie die Ansicht von Stud.IP nach Ihren Vorstellungen anpassen.<br>Sie k&ouml;nnen z.B. Ihre bevorzugte Sprache einstellen.");
	echo "<br></blockquote></td></tr>\n";	
	echo "<tr><td class=\"blank\" colspan=2>\n";
	?>
			<form method="POST" action="<? echo $PHP_SELF ?>?cmd=change_general">
			<table width ="99%" align="center" cellspacing=0 cellpadding=2 border=0>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b><? echo _("Sprache:") ?></b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%"> 
					<?	    
					select_language($_language);
					?>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%"><br>	&nbsp; 				
	<?
	echo "<font size=\"-1\"><input type=\"IMAGE\" " . makeButton("uebernehmen", "src") . " border=0 value=" . _("&Auml;nderungen &uuml;bernehmen") . "></font>&nbsp;"; 
	echo "<input type=\"HIDDEN\" name=\"view\" value=\"allgemein\">\n";
	echo "</td></tr></table></form>\n";	
}

?>
