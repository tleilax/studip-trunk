<?php
# Lifter002: TODO

/**

 * Creates a record of study and exports the data to pdf (html-outpu)

 *

 * @author      Christian Bauer <alfredhitchcock@gmx.net>

 * @version     $Id$

 * @copyright   2003 Stud.IP-Project

 * @access      public

 * @module      recordofstudy

 */

/**
 * displays the site title
 *
 * @access  private
 * @param   string $semester	the current semester (edit-mode) (optional)
 *
 */
function printSiteTitle($semester = NULL){
   	$html = "<table border=0 class=blank align=center cellspacing=0 cellpadding=0 width=\"100%\">\n"
    	  . "	<tr valign=top align=center>\n"
    	  . "    <td class=topic align=left colspan=\"2\">\n"
		  . "	  <img src=\"".$GLOBALS['ASSETS_URL']."images/meinesem.gif\" alt=\""._("Veranstaltungs�bersicht erstellen")."\" align=\"texttop\">\n"
		  . "	  &nbsp;<b>"._("Veranstaltungs�bersicht erstellen:")."</b>\n"
		  . "	  <font size=\"-1\">$semester</font>\n"
    	  . "    </td>\n"
    	  . "   </tr>\n"
    	  . "</table>\n";
   	echo $html;
}

/**
 * displays the semester selection page
 *
 * @access  private
 * @param   array $infobox		the infobox for this site
 * @param   array $semestersAR	the array with the semesters to select
 *
 */
function printSelectSemester($infobox,$semestersAR){
	global $record_of_study_templates;
	$html = "<table border=\"0\" class=\"blank\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n"
		  . " <tr valign=\"top\">\n"
		  . "  <td width=\"99%\" NOWRAP class=\"blank\">&nbsp;\n"
		  . "   <table align=\"center\" width=\"99%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=0>\n"
		  . "	 <tr>"
		  . "	  <td align=\"left\" valign=\"top\"><font size=\"-1\">\n"
		  . _("Bitte w�hlen sie ein Semster aus:")."\n"
		  . "	   <form action=\"".$_SERVER['PHP_SELF']."\" method=post>\n"
		  . "       &nbsp;<select name=\"semesterid\" style=\"vertical-align:middle;\">\n";
	// the semester
	foreach ($semestersAR as $semester){
		$html .= "        <option value=\"".$semester["id"]."\">".$semester["name"]."</option>\n";
	}
	$html .="       </select>\n"
		  . createButton("auswaehlen",_("Semester und Kriterium ausw�hlen."),"semester_selected")
		  . "       <br><br>&nbsp;<select name=\"onlyseminars\" style=\"vertical-align:middle;\">\n"
		  . "        <option value=\"1\" selected>"._("nur Lehrveranstaltungen")."</option>\n"
		  . "        <option value=\"0\">"._("alle Veranstaltungen")."</option>\n"
		  . "       </select>\n";
	if(sizeof($record_of_study_templates)>1){
		$html .="       <br><br>&nbsp;". _("Vorlage").": <select name=\"template\" style=\"vertical-align:middle;\">\n";
		for ($i=1;$i<=sizeof($record_of_study_templates);$i++){
			$html .="        <option value=\"".$i."\">".$record_of_study_templates[$i]["title"]."</option>\n";
		}
		$html .="       </select>\n";
	} else {
		$html .=" <input type=\"hidden\" name=\"template\" value=\"1\">\n";
	}
	$html .="      </form>\n"
		  . "	  </font></td>\n"
		  . "	  <td align=\"right\" width=\"250\" valign=\"top\">\n";
	echo $html;
	print_infobox($infobox, "folders.jpg");
	$html = "	  </td>\n"
		  . "	 </tr>\n"
		  . "	</table>\n"
		  . "  <br></td>\n"
		  . " </tr>\n"
		  . "</table>\n";
	echo $html;
}

/**
 * displays the edit page
 *
 * @access  private
 * @param   array  $infobox		the infobox for this site
 * @param   array  $basicdata	the basic data for the form
 * @param   array  $seminare	the seminars for the form
 * @param   string $notice		a notice for the user (optional)
 *
 */
function printRecordOfStudies($infobox, $basicdata, $seminare, $notice = NULL){
	global $semesterid;
	$html = "<table border=\"0\" class=\"blank\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n"
		. " <form action=\"{$_SERVER['PHP_SELF']}\" method=post>\n"
		. " <input type=\"hidden\" name=\"semesterid\" value=\"".$semesterid."\">\n"
		. " <tr valign=\"top\">\n"
		. "  <td width=\"99%\" NOWRAP class=\"blank\">&nbsp;\n"
		. "   <table align=\"center\" width=\"99%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=0>\n"
		. "	 <tr>"
		. "	  <td valign=\"top\">"
		. "	   <table align=\"center\" width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=0>\n";

	// displays some infos for the user
	if ($notice){
		$html .="		<tr>\n"
			  . "		 <td colspan=\"4\">\n"
			  . "		   <table border=0 cellspacing=0 cellpadding=2>\n"
			  . "			<tr>\n"
			  . "			 <td align=\"center\" width=50 valign=\"middle\">"
			  . "			  <img src=\"".$GLOBALS['ASSETS_URL']."images/ausruf.gif\" alt=\"ausruf\" style=\"vertical-align:middle;\">\n"
			  . "			 </td>\n"
			  . "			 <td align=\"left\" valign=\"middle\">\n";
		if ($notice == "empty")
			$html .="		  <font size=\"-1\"><b>"._("Keine Veranstaltungen zum Anzeigen vorhanden.")."</b><br>"._("Bitte f�gen sie Veranstaltungen mit Hilfe des Buttons \"hinzuf�gen\" ein oder �ndern Sie ihre Auswahl.")."\n";
		elseif ($notice == "above_limit")
			$html .="		  <font size=\"-1\"><b>"._("Sie haben mehr als 10 Veranstaltungen in diesem Semester ausgew�hlt.")."</b><font size=\"-1\" color=\"yello\"><br>"._("Es werden automatisch mehrere Veranstaltungs�bersichtseiten erstellt.")."</font>\n";
		$html .="			  </font>\n"
			  . "			 </td>\n"
			  . "			</tr>\n"
			  . "		   </table>\n"
			  . "		 <br></td>\n"
			  . "		</tr>\n";
	}


	$html .=createInputBox(_("Hochschule: "), $basicdata["university"],	"university", "steelgraulight", 	"60")
		  . createInputBox(_("Studienfach: "), $basicdata["fieldofstudy"],	 "fieldofstudy",  "steelgraulight", 	"60")
		  . createInputBox(_("Name (Vor- und Zuname): "), $basicdata["studentname"],	 "studentname",	  "steelgraulight", 	"60")
		  . createInputBox(_("Semester: "), $basicdata["semester"],		 "semester",	  "steel1kante", 		"30")
		  . createInputBox(_("Fachsemester: "), $basicdata["semesternumber"],"semesternumber","steel1", 			"2", "tes Fachsemester");

	$html .="	    <tr>\n"
		  . "		 <td colspan=\"4\"><font size=\"-1\"><b><br>\n"
		  . _("Veranstaltungen:")."\n"
		  . "		 </b></font></td>\n"
		  . "		</tr>\n"
		  . "		<tr>\n"
		  . createSeminarHeadTD(_("Kenn.-Nr"))
		  . createSeminarHeadTD(_("Name des Dozenten"))
		  . createSeminarHeadTD(_("Wochenstundenzahl"), "center")
		  . createSeminarHeadTD(_("l�schen"), "center")
		  . "		</tr>\n";

  if (!empty($seminare)){
	for($i=0;$i+1<=sizeof($seminare);$i++){
	  	if (($i % 2) == 0)	$displayclass = "steel1";
	  	else				$displayclass = "steelgraulight";
	$html .="		<tr>\n"
		  . "		 <td class=\"$displayclass\" height=\"40\"><font size=\"-1\">\n"
		  . "	   	  &nbsp;<input name=\"seminarnumber$i\" type=\"text\" size=\"6\" maxlength=\"6\" value=\"".$seminare[$i]["seminarnumber"]."\">\n"
		  . "		 </td>\n"
		  . "		 <td class=\"$displayclass\"><font size=\"-1\">\n"
		  . "	   	  &nbsp;<input name=\"tutor$i\" type=\"text\" size=\"70\" maxlength=\"70\" value=\"".$seminare[$i]["tutor"]."\">\n"
		  . "		  \n"
		  . "		 </td>\n"
		  . "		 <td class=\"$displayclass\" align=\"center\"><font size=\"-1\">\n"
		  . "	   	  &nbsp;<input name=\"sws$i\" type=\"text\" size=\"2\" maxlength=\"2\" value=\"".$seminare[$i]["sws"]."\">"._("SWS")."\n"
		  . "		 </td>\n"
		  . "		 <td class=\"$displayclass\" rowspan=\"2\" align=\"center\">\n"
		  . "		  &nbsp;<input type=\"checkbox\" name=\"delete$i\" value=\"1\">\n"
		  . "		 </td>\n"
		  . "		</tr>\n"
		  . "		<tr>\n"
		  . "		 <td class=\"$displayclass\" colspan=\"3\"><font size=\"-1\" align=\"top\">\n"
		  . "		  &nbsp;<b>"._("Genaue Bezeichnung:")."</b><br>&nbsp;<textarea name=\"description$i\" cols=\"60\" rows=\"2\">".$seminare[$i]["description"]."</textarea>\n"
		  . "		 &nbsp;<br><br></td>\n"
		  . "		</tr>\n";
	}
	// delivers the seminar_max
	$seminare_max = $i;
	$html.="		 <input type=\"hidden\" name=\"seminare_max\" value=\"".$seminare_max."\">\n";

	}

	$html .="		<tr>\n"
		  . "		 <td colspan=\"4\"><font size=\"-1\"><br><table width=\"100%\"><tr><td align=\"left\">\n"
		  . createButton("hinzufuegen",_("Neue Veranstaltung hinzuf�gen."),"add_seminars")
		  . "		  <select style=\"vertical-align:middle;\" name=\"newseminarfields\" size=1>\n";
	for( $i=1; $i<=10; $i++ )
		$html .= "		  <option value=\"$i\">$i</option>\n";
	$html .="		  </select>\n"
		  . "		 </font></td>\n"
		  . "		 <td align=right><font size=\"-1\" style=\"vertical-align:middle;\">\n";

	// only show delete-button if there are any seminars
	if(!empty($seminare))
		$html .= _("Markierte Veranstaltung(en) l�schen")."\n" . createButton("loeschen",_("Markierte Veranstaltung(en) l�schen."),"delete_seminars");
	$html .="	     </font></td></tr></table>\n"
		  . "	    </tr>\n"
		  . "	   </table>\n"
		  . "	  </td>\n";

	// the right site of the page
	$html .="	  <td class=\"blank\" width=\"256\" valign=\"top\" align=\"center\"><font size=\"-1\">\n";
	echo $html;
	print_infobox($infobox, "folders.jpg");
	$html = "	   <br>\n"
		  . createButton("zurueck",_("Abbrechen und ein anderes Semester ausw�hlen."),"select_new_semester")
		  . createButton("weiter",_("Weiter zum Download ihrer Veranstaltungs�bersicht."),"create_pdf")
		  . "	  <br><br></td>\n"
		  . "	 </tr>\n"
		  . "	</table>\n"
		  . "  </td>\n"
		  . " </tr>\n"
		  . " </form>\n"
		  . "</table>\n";
	echo $html;
}

/**
 * displays the site in which the user can download the pdf
 *
 * @access  private
 * @param   array  $infobox		the infobox for this site
 * @param   array  $seminars	the seminars to export
 *
 */
function printPdfAssortment($infobox,$seminars){
	global $record_of_study_templates, $template;
	$html = "<table border=\"0\" class=\"blank\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n"
		  . " <tr valign=\"top\">\n"
		  . "  <td width=\"99%\" NOWRAP class=\"blank\">&nbsp;\n"
		  . "   <table align=\"center\" width=\"99%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=0>\n"
		  . "	 <tr>\n"
		  . "	  <td align=\"left\" valign=\"top\"><font size=\"-1\">\n"
		  . sprintf(_("Sie haben %s Eintr�ge f�r ihre Veranstaltungs�bersicht ausgew�hlt. "),$seminars["numberofseminars"]);
	$html .= ($seminars["numberofpages"]>1)
		  ? sprintf(_("Deshalb werden ihre Eintr�ge auf %s Seiten verteilt."),$seminars["numberofpages"])."\n"
		  : sprintf(_("Ihre Eintr�ge k�nnen auf einer Seite untergebracht werden."),$seminars["numberofseminars"])."\n";
	$html .="	  <br><br>\n"
		  . _("Ihre Studiendaten:")."<br>\n"
		  . "&nbsp;" . _("Hochschule: ") . $seminars["university"] . "<br>\n"
		  . "&nbsp;" . _("Studienfach: ") . $seminars["fieldofstudy"] . "<br>\n"
		  . "&nbsp;" . _("Name (Vor- und Zuname): ") . $seminars["studentname"] . "<br>\n"
		  . "&nbsp;" . _("Semester: ") . $seminars["semester"] . "<br>\n"
		  . "&nbsp;" . _("Fachsemester: ") . $seminars["semesternumber"] . "<br>\n"
		  . "<br>\n"
		  . _("Vorlage:") ." ". $record_of_study_templates[$template]["title"] . "\n"
		  . "<br><br>\n";

	$html .= ($seminars["numberofpages"]>1)
		  ? sprintf(_("Klicken sie nun auf die einzelnen Links, um ihre Veranstaltungs�bersicht zu erstellen."),$seminars["numberofpages"])."\n"
		  : sprintf(_("Klicken sie nun auf den Link, um ihre Veranstaltungs�bersicht zu erstellen."),$seminars["numberofseminars"])."\n";

	$html .="	  <br>\n";
	if ($seminars["numberofpages"]>1)
		$html .= _("Veranstaltungs�bersicht: ");
	for($i=1;$i<=$seminars["numberofpages"];$i++){
		$html .="	  <a href=\"recordofstudy.php?create_pdf=1&page=$i\" target=\"_blank\">\n";
		$html .= ($seminars["numberofpages"]>1)
			  ? sprintf(_("Seite %s"),$i)
			  : _("Veranstaltungs�bersicht");
		$html .=" </a>";
	}

	$html .="	  </font></td>\n"
		  . "	  <td align=\"right\" width=\"250\" valign=\"top\">\n";
	echo $html;
	print_infobox($infobox, "folders.jpg");
//	$html = "	  <form action=\"$PHP_SELF\" method=post>"
//		  . "	  <center>\n"
//		  . "		<a href=\"recordofstudy.php\">\n"
//		  . "		 "._("Zur�ck zur Semesterauswahl")."\n"
//		  . "		</a>\n"
// 		  . createButton("speichern",_("Erstellt sie mit diesem Button ein PDF, wenn sie die ben�tigten Daten eingegeben haben."),"create_pdf")
//		  . createButton("zurueck",_("Abbrechen und eine Studienbuchseite f�r ein anderes Semester erstellen."),"select_new_semester")
//		  . "	  <br><br></center></form></td>\n"

	$html = "	  </td>\n"
		  . "	 </tr>\n"
		  . "	</table>\n"
		  . "  <br></td>\n"
		  . " </tr>\n"
		  . "</table>\n";
	echo $html;
}

/**
 * creates a complete <tr> with a label and an input-box
 *
 * @access  private
 * @param   string $text	the label
 * @param   string $value	the input box value
 * @param   string $name	the input box name
 * @param   string $class	the <td> class
 * @param   string $size	the $size of the input box
 * @param   string $additionaltext	an additonal text (optional)
 * @returns string         	the button
 */
function createInputBox($text, $value, $name, $class, $size, $additionaltext = NULL){
	$html = "	 <tr>\n"
		  . "	  <td class=\"".$class."\" colspan=\"4\" width=\"99%\"><font size=\"-1\">\n"
		  . "	   &nbsp;".$text."<br><input name=\"".$name."\" type=\"text\" size=\"".$size."\" maxlength=\"".$size."\" value=\"".$value."\">".$additionaltext."\n"
		  . "	  </font></td>\n"
		  . "	 </tr>\n";

	return $html;
}

/**
 * creates a <td> with a label
 *
 * @access  private
 * @param   string $text	the label
 * @param   string $align	the align (optional)
 * @returns string          the <td> head
 */
function createSeminarHeadTD($text, $align = "left"){
	$html = "		 <td class=\"steel\" height=\"26\" align=\"".$align."\" style=\"vertical-align:bottom;\" ><font size=\"-1\"><b>\n"
		  . "		  &nbsp;".$text."\n"
		  . "		 </font></b></td>\n";
	return $html;
}

/**
 * creates an image-button
 *
 *
 * @access  private
 * @param   string $button	the button name (send to makeButton())
 * @param   string $title	the label
 * @param   string $button	the button name (optional)
 * @param   string $align	the button value (optional)
 * @returns string         	the button
 */
function createButton($button, $title, $name = NULL, $value = NULL){
	$html = "      <input type=\"image\" name=\"".$name."\" value=\"".$value."\" style=\"vertical-align:middle;\""
		  . 	   makeButton($button,"src") ." alt=\"".$title."\" title=\"".$title."\" border=0>\n";
	return $html;
}
?>
