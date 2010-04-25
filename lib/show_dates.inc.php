<?
# Lifter001: DONE
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA   02111-1307, USA.
*/

require_once('lib/visual.inc.php');
require_once('lib/dates.inc.php');
require_once('config.inc.php');
require_once('lib/msg.inc.php');

if ($GLOBALS["CALENDAR_ENABLE"])
    require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEventList.class.php");

/*
 *TODO: Bedarf eine kompletten �berarbeitung!!!!
 *
 * Es wird kein Seminar-Objekt instanziert
 * -> es werden hier noch keine neuen Termine angelegt, wenn ein neues Semester eingetragen wurden
 */

function show_dates ($date_start, $date_end, $open, $range_id = "", $show_not = 0,
        $show_docs = TRUE, $show_admin = FALSE, $full_width = TRUE, $show_as_window = TRUE) {
    global $PHP_SELF, $TERMIN_TYP, $SessSemName, $user, $username, $rechte;

    // wenn man keinen Start und Endtag angibt, soll wohl alles angezeigt werden
    // "0" bedeutet jeweils "open end"

    if (($date_start == 0) && ($date_end == 0)) {
        $show_whole_time=TRUE;
        $tmp_query="";
    }
    else if ($date_start == 0) {
        $show_whole_time=TRUE;
        $tmp_query=" AND t.date <= $date_end ";
    }
    else if ($date_end == 0) {
        $show_whole_time=TRUE;
        $tmp_query=" AND t.date >= $date_start ";
    }
    else {
        $tmp_query=" AND (t.date >= $date_start AND t.date <= $date_end) ";
    }

    if ($show_admin) {
        if ($range_id == $user->id)
            // F�r pers�nliche Termine Einsprung in Terminkalender
            $admin_link="<a href=\"calendar.php?cmd=edit\">";
        else {
            $admin_link="<a href=\"".URLHelper::getLink("raumzeit.php?seminar_id=".$range_id)."\">";
        }
    }

    $range_typ = ($range_id != $user->id) ? "sem" : "user";

    $db = new DB_Seminar();
    $db2 = new DB_Seminar();

    if ($show_not) {
        $k = FALSE;
        // wenn Seminartermine angezeigt werden und show_not =sem
        // zeigen wir nur als Sitzungen definierte Termine
        if ($show_not == "sem") {
            foreach ($TERMIN_TYP as $key => $type) {
                if ($type["sitzung"]) {
                    if (!$k) {
                        $show_query = " AND t.date_typ IN (";
                        $k = TRUE;
                    }
                    elseif ($k)
                        $show_query .= ", ";
                    $show_query .= "'$key'";
                }
            }
        }

        //wenn Seminartermine angezeigt werden und show_not =other zeigen wir alles andere an
        if ($show_not == "other") {
            foreach ($TERMIN_TYP as $key => $type) {
                if (!$type["sitzung"]) {
                    if (!$k) {
                        $show_query = " AND t.date_typ IN (";
                        $k = TRUE;
                    }
                    elseif ($k2)
                        $show_query .= ", ";
                    $show_query .= "'$key'";
                }
            }
        }

        if ($k)
            $show_query .= ") ";
    }

    if (is_array($range_id)) {
        $query = "SELECT t.*, th.title as Titel, th.description as Info, s.Name FROM termine t LEFT JOIN themen_termine USING (termin_id) LEFT JOIN themen as th USING (issue_id) LEFT JOIN seminare s ON (range_id = Seminar_id) ";
        $query .= "WHERE (Seminar_id IN '" . implode(",", $range_id);
        $query .= "' $show_query $tmp_query ) ORDER BY date";
    }
    else if (strlen($range_id))
        $query = "SELECT t.*, th.title as Titel, th.description as Info FROM termine t LEFT JOIN themen_termine USING (termin_id) LEFT JOIN themen as th USING (issue_id) WHERE (range_id='$range_id' $show_query $tmp_query ) ORDER BY date";
    else {
        $query = "SELECT t.*, th.title as Titel, th.description as Info, s.Name, su.* FROM termine t ".
            "LEFT JOIN themen_termine USING (termin_id) ".
            "LEFT JOIN themen as th USING (issue_id) ".
            "LEFT JOIN seminare s ON (range_id = s.Seminar_id) ".
            "LEFT JOIN seminar_user su ON (s.Seminar_id = su.Seminar_id) ".
            "WHERE (user_id = '" . $user->id . "' $show_query $tmp_query ) ORDER BY date";
    }

    $db->query($query);

    if ($db->num_rows()) {

        // Ausgabe der Kopfzeile
        $colspan = 1;
        if (!$full_width) {
            echo "\n<table class=\"index_box\">";
            echo "\n<tr><td>";
        }
        echo "\n<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
        if ($show_as_window) {
            if ($show_admin) {
                $colspan++;
                if (!$show_whole_time) {
                    printf("\n<tr><td class=\"topic\"><img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>", tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")));
                    printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $date_start), strftime("%d. %B %Y", $date_end));
                    printf( "</b></td>\n<td align = \"right\" class=\"topic\">%s<img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" %s></a></td></tr>", $admin_link, tooltip(_("Neuen Termin anlegen")));
                    }
                else {
                    printf("\n<tr><td class=\"topic\"><img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>", tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")));
                    printf(_("Termine"));
                    printf("</b></td>\n<td align = \"right\" class=\"topic\">%s<img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" %s ></a></td></tr>", $admin_link, tooltip(_("Neuen Termin anlegen")));
                    }
                }
            else
                if (!$show_whole_time) {
                    printf("\n<tr valign=\"baseline\"><td class=\"topic\"><img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>", tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")));
                    printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $date_start), strftime("%d. %B %Y", $date_end));
                    print("</b></td></tr>");
                } else {
                    printf("\n<tr valign=\"baseline\"><td class=\"topic\"><img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" %s align=\"texttop\"><b>", tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")));
                    printf(_("Termine"));
                    print("</b></td></tr>");
                }
            echo "\n";
        }

        // Ausgabe der Daten
        echo "\n<tr><td class=\"blank\" colspan=\"$colspan\">";


        //open/close all (show header to switch)
        if (!$show_as_window) {
            echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">"; //WTF?
            print "\n<tr>";
            print "\n<td width=\"5%\" class=\"steelgraulight\" align=\"left\"> ";
            if ($rechte)
                print "<a href=\"".URLHelper::getLink("raumzeit.php?cmd=createNewSingleDate#newSingleDate")."\"><img style=\"vertical-align:middle;\" src=\"".$GLOBALS['ASSETS_URL']."images/add_sheet.gif\"".tooltip(_("Einen neuen Termin anlegen"))." border=0></a></td>";
            print "\n<td class=\"steelgraulight\" align=\"center\">";
            if ($open == "all")
                print "<a href=\"".URLHelper::getLink("?dclose=1")."\"><img style=\"vertical-align:middle;\" src=\"".$GLOBALS['ASSETS_URL']."images/close_all.gif\" ".tooltip(_("Alle schlie�en"))." border=\"0\"></a>";
            else
                print "<a href=\"".URLHelper::getLink("?dopen=all")."\"><img style=\"vertical-align:middle;\" src=\"".$GLOBALS['ASSETS_URL']."images/open_all.gif\" ".tooltip(_("Alle �ffnen"))."border=\"0\"></a>";
            print "\n</tr></td>\n<tr><td class=\"blank\" colspan=\"2\">";
        }

        if ($username)
            $add_to_link = "&username=$username";
        if ($show_not)
            $add_to_link .= "&show_not=$show_not";

        while ($db->next_record()) {

            $zusatz = '';
            if (!$range_id || is_array($range_id)) {
                $zusatz .= "<a href=\"".URLHelper::getLink("seminar_main.php?auswahl=" . $db->f("range_id"))
                                . "\"><font size=\"-1\">" . htmlReady(mila($db->f("Name"), 22))
                                . "</font></a>";
            }
            else {
                $termin = new SingleDate($db->f('termin_id'));
                if( $termin->hasRoom() ){
                    $zusatz .= _("Ort:") . " " . $termin->getRoom() . " ";
                }elseif( $freeroomtext = $termin->getFreeRoomText() ){
                    $zusatz .= " (" . htmlReady($freeroomtext) . ") ";
                }else{
                    $zusatz .= _("Ort:").' '._("k.A.") . " ";
                }
            }

            //Dokumente zaehlen
            $num_docs = '';
            if ($show_docs) {
                $num_docs = doc_count($db->f("termin_id"));
            }

            $titel = '';

            if ($open == $db->f("termin_id")) {
                $titel.= "<a name=\"a\"> </a>";
            }

            $titel .= substr(strftime("%a",$db->f("date")),0,2);
            $titel .= date(". d.m.Y, H:i", $db->f("date"));
            if ($db->f("date") < $db->f("end_time"))
                $titel .= " - " . date("H:i", $db->f("end_time"));
            if ($db->f("Titel")) {
                //Beschneiden des Titels
                $tmp_titel = htmlReady(mila($db->f("Titel"), 60 / (($full_width ? 100 : 70) / 100)));
                $titel .= ", " . $tmp_titel;
                }

            if ($db->f("chdate") > max(object_get_visit($SessSemName[1], "schedule"), object_get_visit($SessSemName[1], "sem")))
                $new = false;
            else
                $new = FALSE;

            if ($num_docs) {
                $db2->query("SELECT folder_id FROM folder WHERE range_id ='" . $db->f("termin_id")."' ");
                $db2->next_record();
                $zusatz .= "<a href=\"".URLHelper::getLink("folder.php?cmd=tree&open=" . $db2->f("folder_id"));
                $zusatz .= "#anker\"><img src=\"".$GLOBALS['ASSETS_URL']."images/icon-disc.gif\" ";
                $zusatz .= tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs));
                $zusatz .= " border=\"0\" align=\"absmiddle\"></a>";
                if ($num_docs > 5)
                    $tmp_num_docs = 5;
                else
                    $tmp_num_docs = $num_docs;
                for ($i = 1; $i < $tmp_num_docs; $i++) {
                    $zusatz .= "<a href=\"".URLHelper::getLink("folder.php?cmd=tree&open=" . $db2->f("folder_id"));
                    $zusatz .= "#anker\"><img src=\"".$GLOBALS['ASSETS_URL']."images/file1b.gif\" ";
                    $zusatz .= tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs));
                    $zusatz .= " border=\"0\" align=\"absmiddle\"></a>";
                }
            }

            //calendar jump
            $zusatz .= " <a href=\"calendar.php?cmd=showweek&atime=" . $db->f("date");
            $zusatz .= "\"><img style=\"vertical-align:bottom\" src=\"".$GLOBALS['ASSETS_URL']."images/popupkalender.gif\" ";
            $zusatz .= tooltip(sprintf(_("Zum %s in den pers�nlichen Terminkalender springen"), date("d.m.Y", $db->f("date"))));
            $zusatz .= " border=\"0\"></a>";


            if ($open != $db->f("termin_id"))
                $link=URLHelper::getLink("?dopen=".$db->f("termin_id").$add_to_link."#a");
            else
                $link=URLHelper::getLink("?dclose=true".$add_to_link);

            $icon=" <img src=\"".$GLOBALS['ASSETS_URL']."images/termin-icon.gif\" border=0>";

            if ($link)
                $titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";

            echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";

            if (($open == $db->f("termin_id")) || ($open == "all") || ($new))
                printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $db->f("chdate"));
            else
                printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $db->f("chdate"));

                echo "</tr></table> ";
            if (($open == $db->f("termin_id")) || ($open == "all") || ($new)) {
                $content='';
                if ($db->f("Info"))
                    $content.= formatReady($db->f("Info"), TRUE, FALSE)."<br><br>";
                else
                    $content.=_("Keine Beschreibung vorhanden") . "<br><br>";

                $content.="<b>" . _("Art des Termins:") . "</b> ".$TERMIN_TYP[$db->f("date_typ")]["name"].", ";
                $content.="<b>" . _("angelegt von:") . "</b> ".get_fullname($db->f("autor_id"),'full',true)."<br>";

                if ($show_admin)
                    $content .= "<br><div align=\"center\"><a href=\"".URLHelper::getLink("raumzeit.php?cmd=open&open_close_id=".$db->f("termin_id")."#".$db->f("termin_id"))."\">" . makeButton("bearbeiten", "img") . "</a></div>";

                echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
                printcontent(0,0, $content, $edit);
                echo "</tr></table> ";
                }
        }
        echo "</td></tr></table>";
        if (!$full_width)
            echo "</td></tr></table>\n";
        return TRUE;
    }

    elseif (($show_admin) && ($show_as_window)) {   //no dates, but the possibility to create one (only, if show_dates is used in window-style)
        if (!$full_width) {
            echo "\n<table class=\"index_box\">";
            echo "\n<tr><td>";
        }
        print("\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">");
        printf("\n<tr><td class=\"topic\"><img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" align=\"texttop\"><b>  %s</b></td>",_("Termine"));
        printf("\n<td align =\"right\" class=\"topic\"> %s<img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" %s></a> </td></tr>", $admin_link, tooltip(_("Termine einstellen")));
        print("\n<tr><td class=\"steel1\" colspan=\"2\"><blockquote><font size=-1>");
        print(_("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie auf die Doppelpfeile."));
        print("</font></blockquote>\n</td></tr></table>\n");
        if (!$full_width)
            echo "</td></tr></table>\n";
        return TRUE;
    }

    elseif (!$show_as_window) {
        if (!$full_width) {
            echo "\n<table class=\"index_box\">";
            echo "\n<tr><td>";
        }
        print("\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">");
        print("\n<tr><td class=\"blank\" colspan=\"2\">");
        parse_msg ("info�"._("Es sind keine aktuellen Termine vorhanden."));
        print("\n</td></tr></table>\n");
        if (!$full_width)
            echo "</td></tr></table>\n";
        return TRUE;
    }

    else {
        return FALSE;
    }
}

function show_personal_dates ($range_id, $date_start, $date_end, $show_docs=FALSE, $show_admin=FALSE, $open){
    global $PHP_SELF, $SessSemName, $user, $TERMIN_TYP;
    global $PERS_TERMIN_KAT, $username, $LastLogin;

    if ($show_admin && $range_id == $user->id) {
        $admin_link = sprintf("<a href=\"./calendar.php?cmd=edit&source_page=%s\">", rawurlencode($PHP_SELF));
    }

    $list = new DbCalendarEventList($range_id, $date_start, $date_end, TRUE);

    if ($list->existEvent()) {

        // Ausgabe der Kopfzeile
        $colspan = 1;
        echo "\n<table class=\"index_box\" style=\"width: 100%;\">";
        if ($show_admin) {
            $colspan++;
            echo "\n<tr><td class=\"topic\"> <img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" " . tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")) . " align='absmiddle'><b>  ";
            printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $list->getStart()), strftime("%d. %B %Y", $list->getEnd()));
            echo "</b></td>";
            echo "\n<td align=\"right\" class=\"topic\"> $admin_link<img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" " . tooltip(_("Neuen Termin anlegen")) . "></a> </td></tr>";
        }
        else {
            echo "\n<tr><td class=\"topic\"> <img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" " . tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")) . " align=\"absmiddle\"><b>  ";
            printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $list->getStart()), strftime("%d. %B %Y", $list->getEnd()));
            echo "</b></td></tr>";
        }
        echo "\n";

        // Ausgabe der Daten
        echo "\n<tr><td class=\"blank\" colspan=$colspan>";
        echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr><td class=\"blank\">";

        if ($username)
            $add_to_link = "&username=$username";

        while ($termin = $list->nextEvent()) {
            $icon = " <img src=\"".$GLOBALS['ASSETS_URL']."images/termin-icon.gif\" border=\"0\" " . tooltip(_("Termin")) . ">";

            $zusatz = '';
            if ($termin->getLocation()) {
                $zusatz .= '<font size="-1">' . _("Raum:") . ' ';
                $zusatz .= htmlReady($termin->getLocation()) . ' </font>';
            }

            $titel = "";
            if (date("Ymd", $termin->getStart()) == date("Ymd", time()))
                $titel .= _("Heute") . date(", H:i", $termin->getStart());
            else {
                $titel = substr(strftime("%a", $termin->getStart()),0,2);
                $titel .= date(". d.m.Y, H:i", $termin->getStart());
            }

            if ($termin->getStart() < $termin->getEnd()) {
                if (date("Ymd", $termin->getStart()) < date("Ymd", $termin->getEnd())) {
                    $titel .= " - ".substr(strftime("%a", $termin->getEnd()),0,2);
                    $titel .= date(". d.m.Y, H:i", $termin->getEnd());
                }
                else
                    $titel .= " - ".date("H:i", $termin->getEnd());
            }

            if ($termin->getTitle()) {
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
                $link = $PHP_SELF . "?dopen=$app_ident$add_to_link#a";
            else
                $link = $PHP_SELF . "?dclose=true$add_to_link";

            echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";

            if ($link)
                $titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";

            if ($open == $app_ident)
                // Ebenso muss hier als letzer Parameter eine Methode getMkdate o.ae. angefuegt werden
                printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $termin->getChangeDate());
            else
                // hier auch.....
                printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $termin->getChangeDate());

            echo "</tr></table> ";

            if ($open == $app_ident) {
                echo "<a name=\"a\"></a>";

                $content = '';
                if ($termin->getDescription())
                    $content .= sprintf("%s<br><br>", formatReady($termin->getDescription(), TRUE, TRUE));
                else
                    $content .= _("Keine Beschreibung vorhanden") . "<br><br>";

                if (sizeof($PERS_TERMIN_KAT) > 1) {
                    $content .= sprintf("<b>%s</b> %s", _("Kategorie:"),
                            htmlReady($termin->toStringCategories()));
                }

                $content .= '<br><b>' . _("Priorit&auml;t:") . ' </b>'
                        . htmlReady($termin->toStringPriority());
                $content .= '&nbsp; &nbsp; &nbsp; &nbsp; ';
                $content .= '<b>' . _("Sichtbarkeit:") . ' </b>'
                        . htmlReady($termin->toStringAccessibility());
                $content .= '<br>' . htmlReady($termin->toStringRecurrence());

                if ($show_admin)
                    $content .= sprintf("<div align=\"center\"><a href=\"./calendar.php?cmd=edit&termin_id=%s&atime=%s&source_page=%s\">"
                                        . makeButton("bearbeiten", "img")
                                        . "</a></div>", $termin->getId(), $termin->getStart(), rawurlencode($PHP_SELF));

                echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
                printcontent(0,0, $content, $edit);
                echo "</tr></table> ";
                }
        }
        echo "</td></tr></table></td></tr></table>";
        return TRUE;
    }
    // keine Termine da, aber die Moeglichkeit welche einzustellen
    else if ($show_admin) {
        echo "\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
        echo "\n<tr><td class=\"topic\"><img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" align=\"texttop\"><b>  " . _("Termine") . "</b></td>";
        echo "\n<td align =\"right\" class=\"topic\"> $admin_link<img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" " . tooltip(_("Termine einstellen")) . "></a> </td></tr>";
        echo "\n<tr><td class=\"steel1\" colspan=\"2\"><blockquote><font size=-1>" . _("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie auf die Doppelpfeile.") . "</font></blockquote>";
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

    $admin_link = sprintf("<a href=\"./calendar.php?cmd=edit&source_page=%s\">", rawurlencode($PHP_SELF));

    if (is_array($calendar_user_control_data["bind_seminare"]))
        $bind_seminare = array_keys($calendar_user_control_data["bind_seminare"], "TRUE");
    else
        $bind_seminare = "";

    $list = new DbCalendarEventList($user->id, $date_start, $date_end, TRUE);
    $list->bindSeminarEvents($bind_seminare);

    if ($list->existEvent()) {

        echo "\n<table class=\"index_box\">";
        echo "\n<tr><td>\n";
        // Ausgabe der Kopfzeile
        echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
        echo "\n<tr><td class=\"topic\" align=\"left\">\n";
        echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" ";
        echo tooltip(_("Termine. Klicken Sie auf die Pfeile (rechts), um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen."));
        echo " align=\"absmiddle\"><b>  ";
        echo _("Meine aktuellen Termine");
        echo "</b></td>";
        echo "\n<td align=\"right\" class=\"topic\"> $admin_link<img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" " . tooltip(_("Neuen Termin anlegen")) . "></a> </td></tr>\n";

        // Ausgabe der Daten
        echo "<tr><td class=\"blank\" colspan=\"2\">";

        while ($termin = $list->nextEvent()) {
            $icon = ' <img src="'.$GLOBALS['ASSETS_URL'].'images/termin-icon.gif" border="0" alt="Termin">';
            $have_write_permission = ((strtolower(get_class($termin)) == 'seminarevent' && $termin->haveWritePermission())
                    || (strtolower(get_class($termin)) != 'seminarevent'));

            $zusatz = "";
            if(strtolower(get_class($termin)) == 'seminarevent')
                $zusatz .= "<a href=\"".URLHelper::getLink("seminar_main.php?auswahl=" . $termin->getSeminarId())
                                . "\"><font size=\"-1\">".htmlReady(mila($termin->getSemName(), 22))
                                . " </font></a>";

            $titel = "";
            $length = 70;
            if (date("Ymd", $termin->getStart()) == date("Ymd", time()))
                $titel .= _("Heute") . date(", H:i", $termin->getStart());
            else {
                $titel .= substr(strftime("%a,", $termin->getStart()),0,2);
                $titel .= date(". d.m.Y, H:i", $termin->getStart());
                $length = 55;
            }

            if (date("Ymd", $termin->getStart()) != date("Ymd", $termin->getEnd())) {
                $titel .= " - ".substr(strftime("%a,",$termin->getEnd()),0,2);
                $titel .= date(". d.m.Y, H:i", $termin->getEnd());
                $length = 55;
            }
            else
                $titel .= " - ".date("H:i", $termin->getEnd());

            if (strtolower(get_class($termin)) == 'seminarevent')
                //Beschneiden des Titels
                $titel .= ", " . htmlReady(mila($termin->getTitle(), $length - 10));
            else
                //Beschneiden des Titels
                $titel .= ", " . htmlReady(mila($termin->getTitle(), $length));

            //Dokumente zaehlen
            $num_docs = 0;
            if ($show_docs && strtolower(get_class($termin)) == 'seminarevent') {
                $num_docs = doc_count($termin->getId());

                if ($num_docs) {
                    $db = new DB_Seminar();
                    $db->query("SELECT folder_id FROM folder WHERE range_id ='" . $termin->getId() . "' ");
                    $db->next_record();
                    $zusatz .= "<a href=\"seminar_main.php?auswahl=" . $termin->getSeminarId()
                                    . "&redirect_to=folder.php&cmd=tree&open=" . $db->f("folder_id")
                                    . "#anker\"><img src=\"".$GLOBALS['ASSETS_URL']."images/icon-disc.gif\" ";
                    $zusatz .= tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs));
                    $zusatz .= " border=\"0\" align=absmiddle>";
                    if ($num_docs > 5)
                        $tmp_num_docs = 5;
                    else
                        $tmp_num_docs = $num_docs;
                    for ($i = 1; $i < $tmp_num_docs; $i++)
                        $zusatz .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/file1b.gif\" alt=\"\" border=\"0\" align=\"absmiddle\">";

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
                $link = URLHelper::getLink("?dopen=".$app_ident."#a");
            else
                $link = URLHelper::getLink("?dclose=true");

            if ($link)
                $titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";

            echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";

            if ($open == $app_ident)
                printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $termin->getChangeDate());
            else
                printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $termin->getChangeDate());

            echo "</tr></table> ";

            if ($open == $app_ident) {
                echo "<a name=\"a\"></a>";

                $content = "";
                if($termin->getDescription())
                    $content .= sprintf("%s<br><br>", formatReady($termin->getDescription(), TRUE, TRUE));
                else
                    $content .= _("Keine Beschreibung vorhanden") . "<br><br>";

                $have_category = FALSE;
                if (sizeof($PERS_TERMIN_KAT) > 1 && strtolower(get_class($termin)) != 'seminarevent') {
                    $content .= "<b>" . _("Kategorie:") . "</b> " . htmlReady($termin->toStringCategories());
                    $have_category = TRUE;
                }
                elseif (sizeof($TERMIN_TYP) > 1 && strtolower(get_class($termin)) == 'seminarevent') {
                    $content .= "<b>" . _("Art des Termins:") . "</b> " . htmlReady($termin->toStringCategories());
                    $have_category = TRUE;
                }

                if ($termin->getLocation()) {
                    if ($have_category)
                        $content .= "&nbsp; &nbsp; &nbsp; &nbsp; ";
                    if (strtolower(get_class($termin)) == 'seminartermin')
                        $content .= "<b>" . _("Raum:") . " </b>";
                    else
                        $content .= "<b>" . _("Ort:") . " </b>";
                    $content .= htmlReady(mila($termin->getLocation(), 25));
                }

                if (strtolower(get_class($termin)) != 'seminarevent') {
                    $content .= '<br><b>' . _("Priorit&auml;t:") . ' </b>'
                            . htmlReady($termin->toStringPriority());
                    $content .= '&nbsp; &nbsp; &nbsp; &nbsp; ';
                    $content .= '<b>' . _("Sichtbarkeit:") . ' </b>'
                            . htmlReady($termin->toStringAccessibility());
                    $content .= '<br>' . htmlReady($termin->toStringRecurrence());
                }

                $edit = FALSE;
                if ($have_write_permission) {
                    // Seminar appointment
                    if ($termin->getType() == 1) {
                        $edit = sprintf("<a href=\"./raumzeit.php?seminar_id=%s&cmd=open&open_close_id=%s#%s\">"
                                    . makeButton("bearbeiten", "img")
                                    . "</a>", $termin->getSeminarId(), $termin->getId(), $termin->getId());
                    }
                    else {
                        // Personal appointment
                        $edit = sprintf("<a href=\"./calendar.php?cmd=edit&termin_id=%s"
                                    . "&atime=%s&source_page=%s\">"
                                    . makeButton("bearbeiten", "img") . "</a>"
                                    , $termin->getId(), $termin->getStart(), rawurlencode($PHP_SELF));
                    }
                }
                else
                    $content .= "<br>";

                echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
                printcontent(0, FALSE, $content, $edit);
                echo "</tr></table> ";
            }
        }
        echo "\n</td></tr>\n</table>";
        echo "\n</td></tr>\n</table>";
        return TRUE;
    }
    // keine Termine da, aber die Moeglichkeit welche einzustellen
    else if($show_admin) {
        echo "\n<table class=\"index_box\">";
        echo "\n<tr><td>\n";
        echo "\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\" width=\"100%\" align=\"center\">";
        echo "\n<tr><td class=\"topic\"><img src=\"".$GLOBALS['ASSETS_URL']."images/meinetermine.gif\" border=\"0\" align=\"texttop\"><b>  " . _("Termine") . "</b></td>";
        echo "\n<td align=\"right\" class=\"topic\"> $admin_link<img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" " . tooltip(_("Termine einstellen")) . "></a> </td></tr>";
        echo "\n<tr><td class=\"steel1\" colspan=\"2\"><blockquote><font size=-1>";
        echo _("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie auf die Doppelpfeile.");
        echo "</font></blockquote>";
        echo "\n</td></tr></table>";
        echo "\n</tr></td>\n</table>";
        return TRUE;
    }

    else {
        return FALSE;
    }
}
?>
