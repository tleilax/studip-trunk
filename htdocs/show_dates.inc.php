<?
/*
show_dates.inc.php enth�lt Funktionen zum Anzeigen von Terminen
Copyright (C) 2000 Andr� Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>,
Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA	02111-1307, USA.
*/

require_once("visual.inc.php");
require_once("dates.inc.php");
require_once("config.inc.php");
	
function show_dates ($range_id, $date_start, $date_end, $show_not=0, $show_docs=false, $show_admin=FALSE, $open) { 
	Global $PHP_SELF, $loginfilelast, $SessSemName, $user, $TERMIN_TYP, $username;	
	
	// wenn man keinen Start und Endtag angibt, soll wohl alles angezeigt werden
	// "0" bedeutet jeweils "open end"
	
	if (($date_start == 0) && ($date_end == 0)) {
		$show_whole_time=TRUE;
		$tmp_query="";
	}
	else if ($date_start == 0) {
		$show_whole_time=TRUE;
		$tmp_query=" AND date <= $date_end ";
	}
	else if ($date_end == 0) {
		$show_whole_time=TRUE;
		$tmp_query=" AND date >= $date_start ";
	}
	else {
		$tmp_query=" AND (date>=$date_start AND date<=$date_end) ";
	}
	
	if ($show_admin) {
		if ($range_id == $user->id)
			// F�r pers�nliche Termine Einsprung in Terminkalender
			$admin_link="<a href=\"calendar.php?cmd=edit\">";
		else
			$admin_link="<a href=\"admin_dates.php?new_sem=TRUE&ebene=sem&range_id=".$range_id."\">";
		}
		
	$db = new DB_Seminar;
	$db2=new DB_Seminar;
	
	if ($show_not) {
		//wenn Seminartermine angezeigt werden und show_not =sem zeigen wir nur als Sitzungen definierte Termine
		if ($show_not=="sem") {
			$k=1;
			foreach ($TERMIN_TYP as $a) {
				if ($a["sitzung"]) {
					if (!$k2)
						$show_query=" AND date_typ IN (";
					elseif ($k2)
						$show_query.=", ";
					$show_query.="'$k'";
					$k2++;
					}
				$k++;
				}
			}
	
		//wenn Seminartermine angezeigt werden und show_not =other zeigen wir alles andere an
		if ($show_not=="other") {
			$k=1;
			foreach ($TERMIN_TYP as $a) {
				if (!$a["sitzung"]) {
					if (!$k2)
						$show_query=" AND date_typ IN (";
					elseif ($k2)
						$show_query.=", ";
					$show_query.="'$k'";
					$k2++;
					}
				$k++;
				}
			}
	
		if ($k2)
			$show_query.=") ";
		}
	
	$db->query("SELECT *	FROM termine WHERE (range_id='$range_id' $show_query $tmp_query ) ORDER BY date");
	
	if ($db->num_rows()) {
		
		// Ausgabe der Kopfzeile
		 $colspan=1;
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
		if ($show_admin) {
			$colspan++;
			if (!$show_whole_time) {
				printf("\n<tr><td class=\"topic\" width=\"99%%\">&nbsp;<img src=\"./pictures/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>&nbsp;&nbsp;", tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")));
				printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $date_start), strftime("%d. %B %Y", $date_end));
				printf("</b></td>\n<td align = \"right\" class=\"topic\">&nbsp;$admin_link<img src=\"./pictures/pfeillink.gif\" border=\"0\" %s ></a>&nbsp;</td></tr>", tooltip(_("Termine bearbeiten")));
				} 
			else {
				printf("\n<tr><td class=\"topic\" width=\"99%%\">&nbsp;<img src=\"./pictures/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>&nbsp;&nbsp;", tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")));
				printf(_("Termine"));
				printf("</b></td>\n<td align = \"right\" class=\"topic\">&nbsp;$admin_link<img src=\"./pictures/pfeillink.gif\" border=\"0\" %s ></a>&nbsp;</td></tr>", tooltip(_("Termine bearbeiten")));
				}
			}
		else
			if (!$show_whole_time) {
				printf("\n<tr valign=\"baseline\"><td class=\"topic\">&nbsp;<img src=\"./pictures/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>&nbsp;&nbsp;", tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")));
				printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $date_start), strftime("%d. %B %Y", $date_end));
				print("</b></td></tr>");
			} else {
				printf("\n<tr valign=\"baseline\"><td class=\"topic\">&nbsp;<img src=\"./pictures/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>&nbsp;&nbsp;", tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")));
				printf(_("Termine"));
				print("</b></td></tr>");
			}
		echo "\n";

		// Ausgabe der Daten
		echo "\n<tr><td class=\"blank\" colspan=$colspan>";
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr><td class=\"blank\">";

		if ($username) 
			$add_to_link="&username=$username";
		if ($show_not)
			$add_to_link.="&show_not=$show_not";
		
		while ($db->next_record()) {

			$zusatz='';
			if (getRoom($db->f("termin_id")))
				$zusatz.= _("Raum:") . " ".getRoom($db->f("termin_id"))."&nbsp;";
			
			//Dokumente zaehlen
			$num_docs='';
			if ($show_docs) {
				$num_docs=doc_count ($db->f("termin_id"));
			}

			
			$titel = substr(strftime("%a",$db->f("date")),0,2);
			$titel .= date (". d.m.Y, H:i", $db->f("date"));
			if ($db->f("date") <$db->f("end_time"))
				$titel .= " - ".date ("H:i", $db->f("end_time"));
			if ($db->f("content")) {
				$tmp_titel=htmlReady(mila($db->f("content"))); //Beschneiden des Titels			
				$titel .=", ".$tmp_titel;
				}
			
			if ($db->f("chdate") > $loginfilelast[$SessSemName[1]])
				$new = TRUE;
			else
				$new = FALSE;

			if ($num_docs) {
				$db2->query("SELECT folder_id FROM folder WHERE range_id ='".$db->f("termin_id")."' ");
				$db2->next_record();
				$zusatz .= "<a href=\"folder.php?cmd=tree&open=".$db2->f("folder_id")."#anker\"><img src=\"pictures/icon-disc.gif\" " .tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs)) . " border=\"0\" align=\"absmiddle\"></a>";
				if ($num_docs > 5)
					$tmp_num_docs = 5;
				else 
					$tmp_num_docs = $num_docs;
				for ($i = 1; $i < $tmp_num_docs; $i++) {
					$zusatz.= "<a href=\"folder.php?cmd=tree&open=".$db2->f("folder_id")."#anker\"><img src=\"pictures/file1b.gif\" " .tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs)) . " border=\"0\" align=\"absmiddle\"></a>";
				}			
			}
			
			
			
			if ($open != $db->f("termin_id"))
				$link=$PHP_SELF."?dopen=".$db->f("termin_id").$add_to_link;
			else
				$link=$PHP_SELF."?dclose=true".$add_to_link;
					
			$icon="&nbsp;<img src=\"./pictures/termin-icon.gif\" border=0>";
			
			if ($link)
				$titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";

			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
			
			if ($open == $db->f("termin_id"))
				printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $db->f("mkdate"));
			else
				printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $db->f("mkdate"));

			echo "</tr></table>	";
					
			if ($open == $db->f("termin_id")) {
				$content='';			
				if ($db->f("description"))
					$content.= htmlReady($db->f("description"), TRUE, TRUE)."<br /><br />";
				else
					$content.=_("Keine Beschreibung vorhanden") . "<br /><br />";

				$content.="<b>" . _("Art des Termins:") . "</b> ".$TERMIN_TYP[$db->f("date_typ")]["name"].",&nbsp;";

				$content.="<b>" . _("angelegt von:") . "</b> ".get_fullname($db->f("autor_id"))."<br />";

				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
				printcontent(0,0, $content, $edit);
				echo "</tr></table>	";
				}
		}
		echo "</td></tr></table></td></tr></table>";
		return TRUE;
	}
	
	else if ($show_admin) {	// keine Termine da, aber die Moeglichkeit welche einzustellen
		print("\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">");
		printf("\n<tr><td class=\"topic\" width=\"99%%\"><img src=\"./pictures/meinetermine.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;&nbsp;%s</b></td>",_("Termine"));
		printf("\n<td align =\"right\" class=\"topic\">&nbsp;$admin_link<img src=\"./pictures/pfeillink.gif\" border=\"0\" %s></a>&nbsp;</td></tr>", tooltip(_("Termine einstellen")));
		print("\n<tr><td class=\"steel1\" colspan=\"2\"><blockquote><br /><font size=-1>");
		print(_("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie auf die Doppelpfeile."));
		print("<br />&nbsp; </blockquote>\n</td></tr></table>");
		return TRUE;
	}
	
	else {
		return FALSE;
	}
}

function show_personal_dates ($range_id, $date_start, $date_end, $show_docs=FALSE, $show_admin=FALSE, $open){
	global $PHP_SELF, $RELATIVE_PATH_CALENDAR, $SessSemName, $user, $TERMIN_TYP;
	global $PERS_TERMIN_KAT, $username, $CALENDAR_DRIVER, $LastLogin;
	
	require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEventList.class.php");
	
	
	//wenn persoenliche Termine angezeigt werden und nicht ich selber draufschau, dann die privaten ausblenden
	if($username && $range_id != $user->id)
		$show_private = FALSE;
		
	if($show_admin && $range_id == $user->id){
		$show_private = TRUE;
		$admin_link = sprintf("<a href=\"./calendar.php?cmd=edit&source_page=%s\">", rawurlencode($PHP_SELF));
	}

	$list = new AppList($range_id, $show_private, $date_start, $date_end, TRUE);
	
	if($list->existEvent()){
		
		// Ausgabe der Kopfzeile
		$colspan = 1;
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
		if($show_admin){
			$colspan++;
			echo "\n<tr><td class=\"topic\" width=\"99%\">&nbsp;<img src=\"./pictures/meinetermine.gif\" border=\"0\" " . tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")) . " align='absmiddle'><b>&nbsp;&nbsp;";
			printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $list->getStart()), strftime("%d. %B %Y", $list->getEnd()));
			echo "</b></td>";
			echo "\n<td align=\"right\" class=\"topic\">&nbsp;$admin_link<img src=\"./pictures/pfeillink.gif\" border=\"0\" " . tooltip(_("Termine bearbeiten")) . "></a>&nbsp;</td></tr>";
		}
		else {
			echo "\n<tr><td class=\"topic\">&nbsp;<img src=\"./pictures/meinetermine.gif\" border=\"0\" " . tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")) . " align=\"absmiddle\"><b>&nbsp;&nbsp;";
			printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $list->getStart()), strftime("%d. %B %Y", $list->getEnd()));
			echo "</b></td></tr>";
		}
		echo "\n";

		// Ausgabe der Daten
		echo "\n<tr><td class=\"blank\" colspan=$colspan>";
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr><td class=\"blank\">";

		if($username) 
			$add_to_link="&username=$username";
		
		while($termin = $list->nextEvent()){
			$icon = "&nbsp;<img src=\"./pictures/termin-icon.gif\" border=\"0\" " . tooltip(_("Termin")) . ">";
			
			$zusatz = "";
			if($termin->getLocation())
				$zusatz.= "<font size=\"-1\">Raum: ".htmlReady($termin->getLocation())."&nbsp;</font>";
				
			$titel = "";
			$titel = substr(strftime("%a", $termin->getStart()),0,2);
			$titel .= date(". d.m.Y, H:i", $termin->getStart());
			
			if($termin->getStart() < $termin->getEnd()){
				if (date("Ymd", $termin->getStart()) < date("Ymd", $termin->getEnd())) {
					$titel .= " - ".substr(strftime("%a", $termin->getEnd()),0,2);
					$titel .= date(". d.m.Y, H:i", $termin->getEnd());
				}
				else
					$titel .= " - ".date("H:i", $termin->getEnd());
			}
			
			if($termin->getTitle()){
				$tmp_titel = htmlReady(mila($termin->getTitle())); //Beschneiden des Titels			
				$titel .= ", ".$tmp_titel;
			}

			if ($termin->getChangeDate() > $LastLogin)
				$new=TRUE;
			else
				$new=FALSE;

			
			// Zur Identifikation von auf- bzw. zugeklappten Terminen muss zusaetzlich
			// die Startzeit ueberprueft werden, da die Wiederholung eines Termins die
			// gleiche ID besitzt.
			$app_ident = $termin->getId() . $termin->getStart();
			if ($open != $app_ident)
				$link = $PHP_SELF."?dopen=".$app_ident.$add_to_link;
			else
				$link = $PHP_SELF."?dclose=true".$add_to_link;
			
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";

			if ($link)
				$titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";
			
			if ($open == $app_ident)
				printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $termin->getChangeDate()); //Ebenso muss hier als letzer Parameter eine Methode getMkdate o.ae. angefuegt werden
			else
				printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $termin->getChangeDate()); //hier auch.....

			echo "</tr></table>	";
			
			if($open == $app_ident) {
				$content = '';			
				if($termin->getDescription())
					$content .= sprintf("%s<br /><br />", htmlReady($termin->getDescription(), TRUE, TRUE));
				else
					$content .= _("Keine Beschreibung vorhanden") . "<br /><br />";
				
				if(sizeof($PERS_TERMIN_KAT) > 1)
					$content .= sprintf("<b>%s</b> %s<br /><br />", _("Art des Termins:"), $termin->getCategoryName());
				
				if($show_admin)
					$content .= sprintf("<div align=\"center\"><a href=\"./calendar.php?cmd=edit&termin_id=%s&atime=%s&source_page=%s\">"
										. makeButton("terminaendern", "img")
										. "</a></div>", $termin->getId(), $termin->getStart(), rawurlencode($PHP_SELF));

				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
				printcontent(0,0, $content, $edit);
				echo "</tr></table>	";
				}
		}
		echo "</td></tr></table></td></tr></table>";
		return TRUE;
	}
	
	else if($show_admin){	// keine Termine da, aber die Moeglichkeit welche einzustellen
		echo "\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
		echo "\n<tr><td class=\"topic\" width=\"99%\"><img src=\"./pictures/meinetermine.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;&nbsp;" . _("Termine") . "</b></td>";
		echo "\n<td align =\"right\" class=\"topic\">&nbsp;$admin_link<img src=\"./pictures/pfeillink.gif\" border=\"0\" " . tooltip(_("Termine einstellen")) . "></a>&nbsp;</td></tr>";
		echo "\n<tr><td class=\"steel1\" colspan=\"2\"><blockquote><br /><font size=-1>" . _("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie auf die Doppelpfeile.") . "<br />&nbsp; </blockquote>";
		echo "\n</td></tr></table>";
		return TRUE;
	}
	
	else {
		return FALSE;
	}
}

function show_all_dates ($date_start, $date_end, $show_docs=FALSE, $show_admin=TRUE, $open){
	global $PHP_SELF, $RELATIVE_PATH_CALENDAR, $SessSemName, $user, $TERMIN_TYP;
	global $PERS_TERMIN_KAT, $username, $CALENDAR_DRIVER, $LastLogin, $calendar_user_control_data;
		
	require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEventList.class.php");
	
	
	$show_private = TRUE;
	$admin_link = sprintf("<a href=\"./calendar.php?cmd=edit&source_page=%s\">", rawurlencode($PHP_SELF));
	
	if	(is_array($calendar_user_control_data["bind_seminare"]))
		$bind_seminare = array_keys($calendar_user_control_data["bind_seminare"], "TRUE");
	else
		$bind_seminare = "";
	
	$list = new AppList($user->id, $show_private, $date_start, $date_end, TRUE);
	$list->bindSeminarEvents($bind_seminare);
	
	if($list->existEvent()){
	
		echo "\n\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"blank\" width=\"70%\">";
		echo "\n<tr><td>\n";
		// Ausgabe der Kopfzeile
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
		echo "\n<tr><td class=\"topic\" align=\"left\">\n";
		echo "<img src=\"./pictures/meinetermine.gif\" border=\"0\" ";
		echo tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen."));
		echo " align=\"absmiddle\"><b>&nbsp;&nbsp;";
		echo _("Meine aktuellen Termine");
		echo "</b></td>";
		echo "\n<td align=\"right\" class=\"topic\">&nbsp;$admin_link<img src=\"./pictures/pfeillink.gif\" border=\"0\" " . tooltip(_("Termine bearbeiten")) . "></a>&nbsp;</td></tr>";
		echo "\n";

		// Ausgabe der Daten
		echo "\n<tr><td class=\"blank\" colspan=\"2\">";

		while($termin = $list->nextEvent()){
			$icon = '&nbsp;<img src="./pictures/termin-icon.gif" border="0" alt="Termin">';
			$have_write_permission = (($termin->getType() == 1 && $termin->haveWritePermission())
					|| ($termin->getType() != 1));
					
			$zusatz = "";
			if($termin->getType() == 1)
				$zusatz .= "<a href=\"seminar_main.php?auswahl=" . $termin->getSeminarId()
								. "\"><font size=\"-1\">".htmlReady(mila($termin->getSemName(), 22))
								. "&nbsp;</font></a>";
			
			$titel = "";
			$length = 70;
			if(date("Ymd", $termin->getStart()) == date("Ymd", time()))
				$titel .= _("Heute") . date(", H:i", $termin->getStart());
			else{
				$titel .= substr(strftime("%a,", $termin->getStart()),0,2);
				$titel .= date(". d.m.Y, H:i", $termin->getStart());
				$length = 55;
			}
			
			if(date("Ymd", $termin->getStart()) != date("Ymd", $termin->getEnd())){
				$titel .= " - ".substr(strftime("%a,",$termin->getEnd()),0,2);
				$titel .= date(". d.m.Y, H:i", $termin->getEnd());
				$length = 55;
			}
			else
				$titel .= " - ".date("H:i", $termin->getEnd());
			
			if($termin->getType() == 1)
				$titel .= ", " . htmlReady(mila($termin->getTitle(), $length - 10)); //Beschneiden des Titels
			else
				$titel .= ", " . htmlReady(mila($termin->getTitle(), $length)); //Beschneiden des Titels
			
			//Dokumente zaehlen
			$num_docs = 0;
			if($show_docs && $termin->getType() == 1) {
				$num_docs = doc_count($termin->getId());
				
				if($num_docs){
					$db = new DB_Seminar();
					$db->query("SELECT folder_id FROM folder WHERE range_id ='" . $termin->getId() . "' ");
					$db->next_record();
					$zusatz .= "<a href=\"seminar_main.php?auswahl=" . $termin->getSeminarId()
									. "&redirect_to=folder.php&cmd=tree&open=" . $db->f("folder_id")
									. "#anker\"><img src=\"pictures/icon-disc.gif\" ";
					$zusatz .= tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs));
					$zusatz	.= " border=\"0\" align=absmiddle>";
					if ($num_docs > 5)
						$tmp_num_docs = 5;
					else 
						$tmp_num_docs = $num_docs;
					for($i = 1; $i < $tmp_num_docs; $i++)
						$zusatz .= "<img src=\"pictures/file1b.gif\" alt=\"\" border=\"0\" align=\"absmiddle\">";
						
					$zusatz .= "</a>";
				}
			}
			
			if ($termin->getChangeDate() > $LastLogin)
				$new = TRUE;
			else
				$new = FALSE;
			
			// Zur Identifikation von auf- bzw. zugeklappten Terminen muss zus�tzlich
			// die Startzeit �berpr�ft werden, da die Wiederholung eines Termins die
			// gleiche ID besitzt.
			$app_ident = $termin->getId() . $termin->getStart();
			if ($open != $app_ident)
				$link = $PHP_SELF."?dopen=".$app_ident;
			else
				$link = $PHP_SELF."?dclose=true";
			
			if ($link)
				$titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";
			
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
			
			if($open == $app_ident)
				printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $termin->getChangeDate());
			else
				printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $termin->getChangeDate());

			echo "</tr></table>	";
					
			if($open == $app_ident) {
				$content = "";
				if($termin->getDescription())
					$content .= sprintf("%s<br /><br />", htmlReady($termin->getDescription(), TRUE, TRUE));
				else
					$content .= _("Keine Beschreibung vorhanden") . "<br /><br />";
					
				$have_category = (sizeof($TERMIN_TYP) > 1 && $termin->getType() == 1)
						|| (sizeof($PERS_TERMIN_KAT) > 1 && $termin->getType() != 1);
				
				if($have_category)
					$content .= "<b>" . _("Kategorie:") . "</b> " . htmlReady($termin->getCategoryName());
				
				if($termin->getLocation()){
					if($have_category)
						$content .= "&nbsp; &nbsp; &nbsp; &nbsp; ";
					$content .= "<b>" . _("Raum:") . " </b>"
										. htmlReady(mila($termin->getLocation(), 25));
				}
								
				$edit = FALSE;
				if($have_write_permission)
					$edit = sprintf("<a href=\"./calendar.php?cmd=edit&termin_id=%s&atime=%s&source_page=%s\">"
								. makeButton("terminaendern", "img")
								. "</a>", $termin->getId(), $termin->getStart(), rawurlencode($PHP_SELF));
				else
					$content .= "<br />";

				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
				printcontent(0, FALSE, $content, $edit);
				echo "</tr></table>	";
				}
		}
		echo "\n</td></tr>\n</table>";
		return TRUE;
	}
	
	else if($show_admin){	// keine Termine da, aber die Moeglichkeit welche einzustellen
		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"blank\" width=\"70%\">";
		echo "\n<tr><td>\n";
		echo "\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
		echo "\n<tr><td class=\"topic\" width=\"99%\"><img src=\"./pictures/meinetermine.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;&nbsp;" . _("Termine") . "</b></td>";
		echo "\n<td align=\"right\" class=\"topic\">&nbsp;$admin_link<img src=\"./pictures/pfeillink.gif\" border=\"0\" " . tooltip(_("Termine einstellen")) . "></a>&nbsp;</td></tr>";
		echo "\n<tr><td class=\"steel1\" colspan=\"2\"><blockquote><br /><font size=-1>";
		echo _("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie auf die Doppelpfeile.");
		echo "<br />&nbsp; </blockquote>";
		echo "\n</td></tr></table>";
		echo "\n</tr></td>\n</table>";
		return TRUE;
	}
	
	else {
		return FALSE;
	}
}
?>