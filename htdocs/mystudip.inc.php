<?
/**
* personal settings
* 
* helper functions for handling personal settings
* 
*
* @author		Stefan Suchi <suchi@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	library
* @module		mystudip.inc
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// mystudip.inc.php
// helper functions for handling personal settings
// Copyright (c) 2003 Stefan Suchi <suchi@data-quest.de>
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


/**
* generates first page of personal settings
*
* This function generates the first page of personal settings.
*
* @access	public        
*/
function change_general_view() {
	global $PHP_SELF, $_language, $auth, $forum, $user, $my_studip_settings;
		
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
					<td class="<? echo $cssSw->getClass() ?>" width="5%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<br><b><? echo _("Sprache:") ?></b><br><br>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="75%" colspan="2"> 
					<?	    
					select_language($_language);
					?>
					</td>
				</tr>
				
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="5%">&nbsp;
					</td>
				<td class="<? echo $cssSw->getClass() ?>" width="20%"><b><?print _("Java-Script Hovereffekte");?></b></td><td  width="20%" class="<? echo $cssSw->getClass() ?>">
				<?
				IF ($auth->auth["jscript"]) {
					echo "<input type=CHECKBOX name='jshover' value=1";
					IF($forum["jshover"]==1) 
					    echo " checked";
					echo ">";
				} else
					echo _("Sie m�ssen in Ihrem Browser Javascript aktivieren um dieses Feature nutzen zu k�nnen.");
				?>
				</td><td   width="55%" class="<? echo $cssSw->getClass() ?>"><br><font size="2"><?print _("Mit dieser Funktion k&ouml;nnen sie durch reines &Uuml;berfahren bestimmter Icons mit dem Mauszeiger (z.B. in den Foren oder im Addresbuch) die entsprechenden Eintr&auml;ge anzeigen lassen. Sie k&ouml;nnen sich so sehr schnell und effizient auch durch gr&ouml;&szlig;ere Informationsmengen arbeiten. Da jedoch die Ladezeiten der Seiten erheblich ansteigen, empfehlen wir diese Einstellung nur f�r NutzerInnen die mindestens &uuml;ber eine ISDN Verbindung verf&uuml;gen.");?></font><br><br>
				</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="5%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<br><b><? echo _("pers&ouml;nliche Startseite:") ?></b><br><br>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="25%">
						<select name="personal_startpage">
							<?	    
							printf ("<option %s value=\"\">"._("keine")."</option>", (!$my_studip_settings["startpage_redirect"]) ? "selected" : "");
							printf ("<option %s value=\"1\">"._("Meine Veranstaltungen")."</option>", ($my_studip_settings["startpage_redirect"] ==  1) ? "selected" : "");
							printf ("<option %s value=\"2\">"._("Meine Einrichtungen")."</option>", ($my_studip_settings["startpage_redirect"] == 2) ? "selected" : "");
							printf ("<option %s value=\"3\">"._("Mein Stundenplan")."</option>", ($my_studip_settings["startpage_redirect"] == 3) ? "selected" : "");
							printf ("<option %s value=\"4\">"._("Mein Adressbuch")."</option>", ($my_studip_settings["startpage_redirect"] == 4) ? "selected" : "");
							printf ("<option %s value=\"5\">"._("Mein Planer")."</option>", ($my_studip_settings["startpage_redirect"] == 5) ? "selected" : "");
							?>
						</select>
					</td>
					<td  width="55%" class="<? echo $cssSw->getClass() ?>">
					<br><font size="2"><?print _("Sie k&ouml;nnen hier einstellen, welcher Systembereich automatisch nach dem Login oder Autologin aufgerufen wird. Wenn Sie zum Beispiel regelm&auml;&szlig;ig die Seite &raquo;Meine Veranstaltungen&laquo;. nach dem Login aufrufen, so k&ouml;nnen Sie dies hier direkt einstellen.");?></font><br><br>
				</tr>
				
				
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="5%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="75%" colspan="2"><br>	&nbsp; 				
	<?
	echo "<font size=\"-1\"><input type=\"IMAGE\" " . makeButton("uebernehmen", "src") . " border=0 value=" . _("&Auml;nderungen &uuml;bernehmen") . "></font>&nbsp;"; 
	echo "<input type=\"HIDDEN\" name=\"view\" value=\"allgemein\">\n";
	echo "</td></tr></table></form>\n";	
}

?>
