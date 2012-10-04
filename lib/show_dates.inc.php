<?
# Lifter001: DONE
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
 * show_dates.inc.php - Funktionen zum Anzeigen von Terminen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Andr� Noack <anoack@mcis.de>
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Stefan Suchi <suchi@gmx.de>
 * @author      Peter Thienel <thienel@data-quest.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     dates
 */

use Studip\Button, Studip\LinkButton;

require_once 'lib/visual.inc.php';
require_once 'lib/dates.inc.php';
require_once 'config.inc.php';
require_once 'lib/msg.inc.php';

if ($GLOBALS["CALENDAR_ENABLE"]) {
    require_once($RELATIVE_PATH_CALENDAR . "/lib/SingleCalendar.class.php");
    require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEventList.class.php");
}
/**
 * TODO: Bedarf eine kompletten �berarbeitung!!!!
 *
 * Es wird kein Seminar-Objekt instanziert
 * -> es werden hier noch keine neuen Termine angelegt, wenn ein neues Semester eingetragen wurden
 *
 * @param $date_start
 * @param $date_end
 * @param $open
 * @param $range_id
 * @param $show_not
 * @param $show_docs
 * @param $show_admin
 * @param $full_width
 * @param $show_as_window
 */
function show_dates($date_start, $date_end, $open, $range_id = "", $show_not = 0,
                    $show_docs = TRUE, $show_admin = FALSE, $full_width = TRUE,
                    $show_as_window = TRUE)
{
    global $TERMIN_TYP, $SessSemName, $user, $username, $rechte;

    // wenn man keinen Start und Endtag angibt, soll wohl alles angezeigt werden
    // "0" bedeutet jeweils "open end"

    $parameters = array();
    if (($date_start == 0) && ($date_end == 0)) {
        $show_whole_time = TRUE;
        $tmp_query = "";
    } else if ($date_start == 0) {
        $show_whole_time = TRUE;
        $tmp_query = " AND t.date <= :date_end ";
        $parameters[':date_end'] = $date_end;
    } else if ($date_end == 0) {
        $show_whole_time=TRUE;
        $tmp_query = " AND t.date >= :date_start ";
        $parameters[':date_start'] = $date_start;
    } else {
        $tmp_query = " AND (t.date >= $date_start AND t.date <= $date_end) ";
        $parameters[':date_start'] = $date_start;
        $parameters[':date_end']   = $date_end;
    }

    if ($show_admin) {
        if ($range_id == $user->id) {
            // F�r pers�nliche Termine Einsprung in Terminkalender
            $admin_link = '<a href="' . URLHelper::getLink('calendar.php', array('cmd' => 'edit')) . '">';
        } else {
            $admin_link = '<a href="' . URLHelper::getLink('raumzeit.php', array('cid' => $range_id)) . '">';
        }
    }

    $range_typ = ($range_id != $user->id) ? "sem" : "user";

    if ($show_not) {
        // wenn Seminartermine angezeigt werden und show_not =sem
        // zeigen wir nur als Sitzungen definierte Termine
        if ($show_not == "sem") {
            $date_types = array_filter($TERMIN_TYP, function ($type) { return $type['sitzung']; });
            if (count($date_types) > 0) {
                $show_query = " AND t.date_typ IN (:date_types) ";
                $parameters[':date_types'] = array_keys($date_types);
            }
        }

        //wenn Seminartermine angezeigt werden und show_not =other zeigen wir alles andere an
        if ($show_not == "other") {
            $date_types = array_filter($TERMIN_TYP, function ($type) { return !$type['sitzung']; });
            if (count($date_types) > 0) {
                $show_query = " AND t.date_typ IN (:date_types) ";
                $parameters[':date_types'] = array_keys($date_types);
            }
        }
    }

    if (is_array($range_id)) {
        $query = "SELECT t.*, th.issue_id, th.title AS Titel,
                         th.description AS Info, s.Name
                  FROM termine AS t
                  LEFT JOIN themen_termine USING (termin_id)
                  LEFT JOIN themen AS th USING (issue_id)
                  LEFT JOIN seminare AS s ON (range_id = Seminar_id)
                  WHERE Seminar_id IN (:range_id) {$show_query} {$tmp_query}
                  ORDER BY date";
        $parameters[':range_id'] = $range_id;
    } else if (strlen($range_id)) {
        $query = "SELECT t.*, th.issue_id, th.title AS Titel,
                         th.description as Info
                  FROM termine AS t
                  LEFT JOIN themen_termine USING (termin_id)
                  LEFT JOIN themen AS th USING (issue_id)
                  WHERE range_id = :range_id {$show_query} {$tmp_query}
                  ORDER BY date";
        $parameters[':range_id'] = $range_id;
    } else {
        $query = "SELECT t.*, th.issue_id, th.title as Titel,
                         th.description as Info, s.Name, su.*
                  FROM termine AS t
                  LEFT JOIN themen_termine USING (termin_id)
                  LEFT JOIN themen AS th USING (issue_id) 
                  LEFT JOIN seminare AS s ON (range_id = s.Seminar_id)
                  LEFT JOIN seminar_user su ON (s.Seminar_id = su.Seminar_id)
                  WHERE user_id = :user_id {$show_query} {$tmp_query}
                  ORDER BY date";
        $parameters[':user_id'] = $user->id;
    }

    $statement = DBManager::get()->prepare($query);
    $statement->execute($parameters);
    $dates = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (count($dates) > 0) {
        // set skip link
        SkipLinks::addIndex(_("Termine"), 'appointments_box');

        // Ausgabe der Kopfzeile
        $colspan = 1;
        echo "\n<table id=\"appointments_box\" role=\"article\" class=\"index_box\"".($full_width ? " style=\"width: 100%;\"" : '').">";
        if ($show_as_window) {
            if ($show_admin) {
                $colspan++;
                if (!$show_whole_time) {
                    printf("\n<tr><td class=\"table_header_bold\"><img src=\"".Assets::image_path('icons/16/white/schedule.png')."\" %s><b>", tooltip(_("Termine. Klicken Sie rechts auf die Zahnr�der, um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")));
                    printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $date_start), strftime("%d. %B %Y", $date_end));
                    printf( "</b></td>\n<td align = \"right\" class=\"table_header_bold\">%s<img src=\"".Assets::image_path('icons/16/white/admin.png')."\" %s></a></td></tr>", $admin_link, tooltip(_("Neuen Termin anlegen")));
                    }
                else {
                    printf("\n<tr><td class=\"table_header_bold\"><img src=\"".Assets::image_path('icons/16/white/schedule.png')."\" %s><b>", tooltip(_("Termine. Klicken Sie rechts auf die Zahnr�der, um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")));
                    printf(_("Termine"));
                    printf("</b></td>\n<td align = \"right\" class=\"table_header_bold\">%s<img src=\"".Assets::image_path('icons/16/white/admin.png')."\" %s ></a></td></tr>", $admin_link, tooltip(_("Neuen Termin anlegen")));
                    }
                }
            else
                if (!$show_whole_time) {
                    printf("\n<tr valign=\"baseline\"><td class=\"table_header_bold\"><img src=\"".Assets::image_path('icons/16/white/schedule.png')."\" %s><b>", tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")));
                    printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $date_start), strftime("%d. %B %Y", $date_end));
                    print("</b></td></tr>");
                } else {
                    printf("\n<tr valign=\"baseline\"><td class=\"table_header_bold\"><img src=\"".Assets::image_path('icons/16/white/schedule.png')."\" %s><b>", tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")));
                    printf(_("Termine"));
                    print("</b></td></tr>");
                }
            echo "\n";
        }

        // Ausgabe der Daten
        echo "\n<tr><td class=\"blank\" colspan=\"$colspan\">";


        //open/close all (show header to switch)
        if (!$show_as_window) {
            echo "\n<table id=\"appointments_box\" role=\"article\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\">"; //WTF?
            print "\n<tr>";
            print "\n<td width=\"5%\" class=\"table_row_odd\" align=\"left\"> ";
            if ($rechte)
                print '<a href="' . URLHelper::getLink('raumzeit.php?cmd=createNewSingleDate#newSingleDate') . '"><img class="middle" src="' . Assets::image_path('icons/16/blue/plus.png') . '" ' . tooltip(_("Einen neuen Termin anlegen")) . '></a></td>';
            print "\n<td class=\"table_row_odd\" align=\"center\">";
            if ($open == "all")
                print '<a href="' . URLHelper::getLink('?dclose=1') . '"><img style="vertical-align:middle;" src="' . Assets::image_path('close_all.png') . '" ' . tooltip(_("Alle schlie�en")) . '></a>';
            else
                print '<a href="' . URLHelper::getLink('?dopen=all') . '"><img style="vertical-align:middle;" src="' . Assets::image_path('open_all.png') . '" ' . tooltip(_("Alle �ffnen")) . 'border="0"></a>';
            print "\n</td></tr>\n<tr><td class=\"blank\" colspan=\"2\">";
        }

        if ($username)
            $add_to_link = "&username=$username";
        if ($show_not)
            $add_to_link .= "&show_not=$show_not";

        foreach ($dates as $date) {
            echo '<div role="article">';
            $zusatz = '';
            if (!$range_id || is_array($range_id)) {
                $zusatz .= "<a href=\"".URLHelper::getLink("seminar_main.php?auswahl=" . $date['range_id'])
                                . "\"><font size=\"-1\">" . htmlReady(mila($date['Name'], 22))
                                . "</font></a>";
                $current_seminar_id = $date['range_id'];
            }
            else {
                $termin = new SingleDate($date['termin_id']);
                if( $termin->hasRoom() ){
                    $zusatz .= _("Ort:") . " " . $termin->getRoom() . " ";
                }elseif( $freeroomtext = $termin->getFreeRoomText() ){
                    $zusatz .= " (" . htmlReady($freeroomtext) . ") ";
                }else{
                    $zusatz .= _("Ort:").' '._("k.A.") . " ";
                }
                $current_seminar_id = $range_id;
            }

            //Dokumente zaehlen
            $num_docs = 0;
            $folder_id = '';
            if ($show_docs) {
                $query = "SELECT folder_id, issue_id
                          FROM themen_termine
                          INNER JOIN folder ON (issue_id = range_id)
                          WHERE termin_id = ?
                          LIMIT 1";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array($date['termin_id']));
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                if ($row['folder_id']) {
                    $num_docs = doc_count($row['issue_id'], $current_seminar_id);
                    $folder_id = $row['folder_id'];
                }
            }

            $titel = '';

            if ($open == $date['termin_id']) {
                $titel.= "<a name=\"a\"> </a>";
            }

            $titel .= substr(strftime('%a', $date['date']),0,2);
            $titel .= date('. d.m.Y, H:i', $date['date']);
            if ($date['date'] < $date['end_time']) {
                $titel .= " - " . date("H:i", $date['end_time']);
            }
            if ($date['Titel']) {
                //Beschneiden des Titels
                $tmp_titel = htmlReady(mila($date['Titel'], 60 / (($full_width ? 100 : 70) / 100)));
                $titel .= ", " . $tmp_titel;
            }

            if ($date['chdate'] > max(object_get_visit($current_seminar_id, "schedule"), object_get_visit($current_seminar_id, "sem"))) {
                $new = false;
            } else {
                $new = FALSE;
            }

            if ($num_docs) {
                $zusatz .= '<a href="' . URLHelper::getLink('folder.php', array('cmd' => 'tree' , 'open' =>  $folder_id, 'cid' => $current_seminar_id));
                $zusatz .= '#anker"><img src="' . Assets::image_path('icons/16/blue/files.png') . '" ';
                $zusatz .= tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs));
                $zusatz .= '></a>';
            }

            //calendar jump
            $zusatz .= ' <a href="' . UrlHelper::getLink('calendar.php', array('cmd' =>'showweek', 'atime' => $date['date'], 'caluser' => 'self'));
            $zusatz .= '"><img style="vertical-align:bottom" src="' . Assets::image_path('popupcalendar.png') . '" ';
            $zusatz .= tooltip(sprintf(_("Zum %s in den pers�nlichen Terminkalender springen"), date("d.m.Y", $date['date'])));
            $zusatz .= '></a>';


            if ($open != $date['termin_id']) {
                $link=URLHelper::getLink("?dopen=".$date['termin_id'].$add_to_link."#a");
            } else {
                $link=URLHelper::getLink("?dclose=true".$add_to_link);
            }
            $icon = Assets::img('icons/16/grey/date.png', array('class' => 'text-bottom'));

            if ($link) {
                $titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";
            }
            echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";

            if (($open == $date['termin_id']) || ($open == "all") || ($new)) {
                printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $date['chdate']);
            } else {
                printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $date['chdate']);
            }
            echo '</tr></table> ';
            if (($open == $date['termin_id']) || ($open == "all") || ($new)) {
                $termin = new SingleDate($date['termin_id']);
                $content = '';
                if ($date['Info']) {
                    $content .= formatReady($date['Info'], TRUE, FALSE) . "<br><br>";
                } else {
                    $content .= _("Keine Beschreibung vorhanden") . "<br><br>";
                }
                $content .= '<b>' . _("Art des Termins:") . '</b> ' . $TERMIN_TYP[$date['date_typ']]["name"] . ', ';
                //$content.="<b>" . _("angelegt von:") . "</b> ".get_fullname($date['autor_id'],'full',true)."<br>";
                $content .= "<b>" . _("durchf�hrende Dozenten:") . "</b> ";
                foreach ($termin->getRelatedPersons() as $key => $dozent_id) {
                    $key < 1 || ($content .= ", ");
                    $content .= htmlReady(get_fullname($dozent_id));
                }
                $content .= "<br>";

                if ($show_admin)
                    $content .= "<br><div align=\"center\"> ". LinkButton::create(_('Bearbeiten'), URLHelper::getURL("raumzeit.php", array('cmd' => 'open','open_close_id' => $date['termin_id'] . '#' . $date['termin_id']))) . "</div>";
                echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
                printcontent(0,0, $content, $edit);
                echo "</tr></table> ";
                }
            echo '</div>';
        }
        echo "</td></tr></table>";
        return TRUE;
    }

    elseif (($show_admin) && ($show_as_window)) {   //no dates, but the possibility to create one (only, if show_dates is used in window-style)
        // set skip link
        SkipLinks::addIndex(_("Termine"), 'appointments_box');

        print("\n<table id=\"appointments_box\" role=\"article\" class=\"index_box\"".($full_width ? " style=\"width: 100%;\"" : '').">");
        printf("\n<tr><td class=\"table_header_bold\"><img src=\"".Assets::image_path('icons/16/white/schedule.png')."\"><b>  %s</b></td>",_("Termine"));
        printf("\n<td align =\"right\" class=\"table_header_bold\"> %s<img src=\"".Assets::image_path('icons/16/white/admin.png')."\" %s></a> </td></tr>", $admin_link, tooltip(_("Termine einstellen")));
        ?>
        <tr>
            <td class="table_row_even" colspan="2">
                <p class="info">
                    <?= _("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie rechts auf die Zahnr�der.") ?>
                </p>
            </td>
        </tr>
        </table>
        <?
        return TRUE;
    }

    elseif (!$show_as_window) {
        // set skip link
        SkipLinks::addIndex(_("Termine"), 'appointments_box');

        print("\n<table id=\"appointments_box\" role=\"article\" class=\"index_box\"".($full_width ? " style=\"width: 100%;\"" : '').">");
        print("\n<tr><td class=\"blank\" colspan=\"2\">");
        parse_msg ("info�"._("Es sind keine aktuellen Termine vorhanden."));
        print("\n</td></tr></table>\n");
        return TRUE;
    }

    else {
        return FALSE;
    }
}

/**
 *
 * @param unknown_type $range_id
 * @param unknown_type $date_start
 * @param unknown_type $date_end
 * @param unknown_type $show_docs
 * @param unknown_type $show_admin
 * @param unknown_type $open
 */
function show_personal_dates ($range_id, $date_start, $date_end, $show_docs = FALSE, $show_admin = FALSE, $open)
{
    global $SessSemName, $user, $TERMIN_TYP;
    global $PERS_TERMIN_KAT, $username;

    if ($show_admin && $range_id == $user->id) {
        $admin_link = '<a href="'.URLHelper::getLink('calendar.php', array('cmd' => 'edit', 'source_page' => URLHelper::getURL())).'">';
    }

    if ($date_end <= $date_start) {
        // show seven days
        $date_end = $date_start + 7*24*60*60;
    }

    $list = new DbCalendarEventList(new SingleCalendar($range_id, Calendar::PERMISSION_READABLE), $date_start, $date_end, TRUE, null, ($GLOBALS['user']->id == $range_id ? array() : array('CLASS' => 'PUBLIC')));

    if ($list->existEvent()) {
        // set skip link
        SkipLinks::addIndex(_("Termine"), 'appointments_box');

        // Ausgabe der Kopfzeile
        $colspan = 1;
        echo "\n<table id=\"appointments_box\" role=\"article\" class=\"index_box\" style=\"width: 100%;\">";
        if ($show_admin) {
            $colspan++;
            echo "\n<tr><td class=\"table_header_bold\"> <img src=\"" . Assets::image_path('icons/16/white/schedule.png') . '" ' . tooltip(_("Termine. Klicken Sie rechts auf die Zahnr�der, um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen.")) . '> <b>';
            printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $list->getStart()), strftime("%d. %B %Y", $list->getEnd()));
            echo "</b></td>";
            echo "\n<td align=\"right\" class=\"table_header_bold\"> $admin_link<img src=\"" . Assets::image_path('icons/16/white/admin.png') . '" ' . tooltip(_("Neuen Termin anlegen")) . '></a></td></tr>';
        }
        else {
            echo "\n<tr><td class=\"table_header_bold\"> <img src=\"" . Assets::image_path('icons/16/white/schedule.png') . '" ' . tooltip(_("Termine. Klicken Sie auf den Pfeil, um eine Beschreibung des Termins anzuzeigen.")) . '><b>  ';
            printf(_("Termine f�r die Zeit vom %s bis zum %s"), strftime("%d. %B %Y", $list->getStart()), strftime("%d. %B %Y", $list->getEnd()));
            echo "</b></td></tr>";
        }
        echo "\n";

        // Ausgabe der Daten
        echo "\n<tr><td class=\"blank\" colspan=$colspan>";
        echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr><td class=\"blank\">";

        while ($termin = $list->nextEvent()) {
            echo '<div role="article">';
            $icon = Assets::img('icons/16/grey/date.png', array('class' => 'text-bottom'));

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
                } else {
                    $titel .= " - ".date("H:i", $termin->getEnd());
                }
            }

            if ($termin->getTitle()) {
                $tmp_titel = htmlReady(mila($termin->getTitle())); //Beschneiden des Titels
                $titel .= ", ".$tmp_titel;
            }
            $LastLogin = UserConfig::get($user->id)->_get('LastLogin');
            $new = ($termin->getChangeDate() > $LastLogin);

            // Zur Identifikation von auf- bzw. zugeklappten Terminen muss zusaetzlich
            // die Startzeit ueberprueft werden, da die Wiederholung eines Termins die
            // gleiche ID besitzt.
            $app_ident = $termin->getId() . $termin->getStart();
            if ($open != $app_ident) {
                $link = URLHelper::getLink('', ($username ? array('dopen' => $app_ident, 'username' => $username) : array('dopen' => $app_ident))) . '#a';
            } else {
                $link = URLHelper::getLink('', ($username ? array('dclose' => 'true', 'username' => $username) : array('dclose' => 'true')));
            }

            echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";

            if ($link) {
                $titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
            }

            if ($open == $app_ident) {
                // Ebenso muss hier als letzer Parameter eine Methode getMkdate o.ae. angefuegt werden
                printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $termin->getChangeDate());
            } else {
                // hier auch.....
                printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $termin->getChangeDate());
            }

            echo "</tr></table> ";

            if ($open == $app_ident) {
                echo "<a name=\"a\"></a>";

                $content = '';
                if ($termin->getDescription()) {
                    $content .= sprintf("%s<br><br>", formatReady($termin->getDescription(), TRUE, TRUE));
                } else {
                    $content .= _("Keine Beschreibung vorhanden") . "<br><br>";
                }

                if (sizeof($PERS_TERMIN_KAT) > 1) {
                    $content .= sprintf("<b>%s</b> %s", _("Kategorie:"),
                            htmlReady($termin->toStringCategories()));
                }

                $content .= '<br><b>' . _("Priorit&auml;t:") . ' </b>' . htmlReady($termin->toStringPriority());
                $content .= '&nbsp; &nbsp; &nbsp; &nbsp; ';
                $content .= '<b>' . _("Sichtbarkeit:") . ' </b>' . htmlReady($termin->toStringAccessibility());
                $content .= '<br>' . htmlReady($termin->toStringRecurrence());

                if ($show_admin) {
                    $content .= '<div align="center">' . LinkButton::create(_('Bearbeiten'), URLHelper::getURL('calendar.php', array('cmd' => 'edit', 'termin_id' => $termin->getId(), 'atime' => $termin->getStart(), 'source_page' => URLHelper::getURL('about.php'))))
                             . '</div>';
                }

                echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
                printcontent(0, 0, $content, $edit);
                echo "</tr></table> ";
                }
            echo '</div>';
        }
        echo "</td></tr></table></td></tr></table>";
        return TRUE;
    }
    // keine Termine da, aber die Moeglichkeit welche einzustellen
    else if ($show_admin) {
        // set skip link
        SkipLinks::addIndex(_("Termine"), 'appointments_box');

        echo "\n<table id=\"appointments_box\" role=\"article\" class=\"index_box\" style=\"width: 100%;\">";
        echo "\n<tr><td class=\"table_header_bold\"><img src=\"".Assets::image_path('icons/16/white/schedule.png')."\"> <b>" . _("Termine") . "</b></td>";
        echo "\n<td align =\"right\" class=\"table_header_bold\"> $admin_link<img src=\"".Assets::image_path('icons/16/white/admin.png')."\" " . tooltip(_("Termine einstellen")) . "></a> </td></tr>";
        ?>

        <tr>
            <td class="table_row_even" colspan="2">
                <p class="info">
                    <?= _("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie rechts auf die Zahnr�der.") ?>
                </p>
            </td>
        </tr>
        </table>
        <?
        return TRUE;
    }

    else {
        return FALSE;
    }
}

/**
 *
 * @param unknown_type $date_start
 * @param unknown_type $date_end
 * @param unknown_type $show_docs
 * @param unknown_type $show_admin
 * @param unknown_type $open
 */
function show_all_dates($date_start, $date_end, $show_docs=FALSE, $show_admin=TRUE, $open)
{
    global $RELATIVE_PATH_CALENDAR, $SessSemName, $user, $TERMIN_TYP;
    global $PERS_TERMIN_KAT, $username, $CALENDAR_DRIVER, $calendar_user_control_data;

    $admin_link = '<a href="'.URLHelper::getLink('calendar.php', array('cmd' => 'edit', 'source_page' => URLHelper::getURL())).'">';

    if (is_array($calendar_user_control_data["bind_seminare"]))
        $bind_seminare = array_keys($calendar_user_control_data["bind_seminare"], "TRUE");
    else
        $bind_seminare = "";

    $list = new DbCalendarEventList(new SingleCalendar($user->id, Calendar::PERMISSION_OWN), $date_start, $date_end, TRUE, Calendar::getBindSeminare());

    if ($list->existEvent()) {
        // set skip link
        SkipLinks::addIndex(_("Termine"), 'appointments_box');

        // Ausgabe der Kopfzeile
        echo "<table id=\"appointments_box\" role=\"article\" class=\"index_box\">";
        echo "\n<tr><td class=\"table_header_bold\" align=\"left\">\n";
        echo '<img src="' . Assets::image_path('icons/16/white/schedule.png') . '" ';
        echo tooltip(_("Termine. Klicken Sie rechts auf die Zahnr�der, um Termine in diesen Bereich zu bearbeiten. Klicken Sie auf den einfachen Pfeil, um die Terminbeschreibung zu lesen."));
        echo "> <b>";
        echo _("Meine aktuellen Termine");
        echo "</b></td>";
        echo "\n<td align=\"right\" class=\"table_header_bold\"> $admin_link<img src=\"" . Assets::image_path('icons/16/white/admin.png') . '" ' . tooltip(_("Neuen Termin anlegen")) . "></a> </td></tr>\n";

        // Ausgabe der Daten
        echo "<tr><td class=\"blank\" colspan=\"2\">";

        while ($termin = $list->nextEvent()) {
            echo '<div role="article">';
            $icon = Assets::img('icons/16/grey/date.png', array('class' => 'text-bottom'));
            $have_write_permission = true;

            $zusatz = '';
            if(strtolower(get_class($termin)) == 'seminarevent') {
                $have_write_permission = $GLOBALS['perm']->have_studip_perm('tutor', $termin->getSeminarId());
                $singledate = new SingleDate($termin->id);
                $issues = array_map(array('IssueDB', 'restoreIssue'), (array)$singledate->getIssueIDs());
                $issue_titles = join('; ', array_map(create_function('$a', 'return $a["title"];'), $issues));
                if (!$issue_titles) {
                    $issue_titles = _("Ohne Titel");
                }
                $issue_descriptions = join("\n\n", array_map(create_function('$a', 'return $a["description"];'), $issues));
                $zusatz .= '<a href="'.URLHelper::getLink("seminar_main.php?auswahl=" . $termin->getSeminarId())
                                . "\"><font size=\"-1\">".htmlReady(mila($termin->getSemName(), 22))
                                . ' </font></a>';
            }

            $titel = '';
            $length = 70;
            if (date('Ymd', $termin->getStart()) == date('Ymd', time())) {
                $titel .= _("Heute") . date(", H:i", $termin->getStart());
            } else {
                $titel .= substr(strftime('%a,', $termin->getStart()),0,2);
                $titel .= date('. d.m.Y, H:i', $termin->getStart());
                $length = 55;
            }

            if (date('Ymd', $termin->getStart()) != date('Ymd', $termin->getEnd())) {
                $titel .= ' - ' . substr(strftime('%a,', $termin->getEnd()), 0, 2);
                $titel .= date('. d.m.Y, H:i', $termin->getEnd());
                $length = 55;
            } else {
                $titel .= ' - '.date('H:i', $termin->getEnd());
            }

            if (strtolower(get_class($termin)) == 'seminarevent') {
                //Beschneiden des Titels
                $titel .= ', ' . htmlReady(mila($issue_titles, $length));
            } else {
                //Beschneiden des Titels
                $titel .= ', ' . htmlReady(mila($termin->getTitle(), $length));
            }

            //Dokumente zaehlen
            $num_docs = 0;
            if ($show_docs && strtolower(get_class($termin)) == 'seminarevent') {
                $query = "SELECT folder_id, issue_id
                          FROM themen_termine
                          INNER JOIN folder ON (issue_id = range_id)
                          WHERE termin_id = ?
                          LIMIT 1";
                $statement = DBManager::get()->prepare($query);
                $statement->execute(array(
                    $termin->getId()
                ));
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                if ($row['folder_id']) {
                    $num_docs = doc_count($row['issue_id'],  $termin->getSeminarId());
                    if ($num_docs) {
                        $zusatz .= '<a href="' .URLHelper::getLink('seminar_main.php', array('auswahl' => $termin->getSeminarId(), 'redirect_to' => 'folder.php', 'cmd' => 'tree', 'open' => $row['folder_id']))
                        . '#anker"><img src="' . Assets::image_path('icons/16/blue/files.png') . '" ';
                        $zusatz .= tooltip(sprintf(_("%s Dokument(e) vorhanden"), $num_docs));
                        $zusatz .= ">";
                    }
                }
            }
            $LastLogin = UserConfig::get($user->id)->_get('LastLogin');
            $new = ($termin->getChangeDate() > $LastLogin);

            // Zur Identifikation von auf- bzw. zugeklappten Terminen muss zus�tzlich
            // die Startzeit �berpr�ft werden, da die Wiederholung eines Termins die
            // gleiche ID besitzt.
            $app_ident = $termin->getId() . $termin->getStart();
            if ($open != $app_ident) {
                $link = URLHelper::getLink("?dopen=".$app_ident."#a");
            } else {
                $link = URLHelper::getLink("?dclose=true");
            }

            if ($link) {
                $titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";
            }

            echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";

            if ($open == $app_ident) {
                printhead(0, 0, $link, "open", $new, $icon, $titel, $zusatz, $termin->getChangeDate());
            } else {
                printhead(0, 0, $link, "close", $new, $icon, $titel, $zusatz, $termin->getChangeDate());
            }

            echo "</tr></table> ";

            if ($open == $app_ident) {
                echo "<a name=\"a\"></a>";

                $content = "";
                if (strtolower(get_class($termin)) == 'seminarevent') {
                    $description = $issue_descriptions;
                } else {
                    $description = $termin->getDescription();
                }
                if($description) {
                    $content .= sprintf("%s<br><br>", formatReady($description, TRUE, TRUE));
                } else {
                    $content .= _("Keine Beschreibung vorhanden") . "<br><br>";
                }

                if (strtolower(get_class($termin)) == 'seminarcalendarevent') {
                    $content .= '<b>' . _("Seminar:") . '</b>' . htmlReady($termin->getSemName()) . '<br>';
                }

                $have_category = FALSE;
                if (sizeof($PERS_TERMIN_KAT) > 1 && strtolower(get_class($termin)) != 'seminarevent') {
                    $content .= "<b>" . _("Kategorie:") . "</b> " . htmlReady($termin->toStringCategories());
                } elseif (sizeof($TERMIN_TYP) > 1 && strtolower(get_class($termin)) == 'seminarevent') {
                    $content .= "<b>" . _("Art des Termins:") . "</b> " . htmlReady($singledate->getTypeName());
                }

                if (strtolower(get_class($termin)) == 'seminarevent') {
                    if ($singledate->getRoom()) {
                        $content .= "&nbsp; &nbsp; &nbsp; &nbsp; ";
                        $content .= "<b>" . _("Raum:") . " </b>";
                        $content .= htmlReady(mila($singledate->getRoom(), 25));
                    } else if ($singledate->getFreeRoomText()) {
                        $content .= "&nbsp; &nbsp; &nbsp; &nbsp; ";
                        $content .= "<b>" . _("Ort:") . " </b>";
                        $content .= htmlReady(mila($singledate->getFreeRoomText(), 25));
                    }
                } else {
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
                    if (strtolower(get_class($termin)) == 'seminarevent') {
                        $edit = LinkButton::create(_('Bearbeiten'), URLHelper::getURL('raumzeit.php', array('cid' => $termin->getSeminarId(), 'cmd' => 'open', 'open_close_id' => $termin->getId())) . '#' . $termin->getId());
                    } else {
                        // Personal appointment
                        $edit = LinkButton::create(_('Bearbeiten'), URLHelper::getURL('calendar.php', array('cmd' => 'edit', 'termin_id' => $termin->getId(), 'atime' => $termin->getStart(), 'source_page' => URLHelper::getURL())));                                    
                    }
                } else {
                    $content .= "<br>";
                }

                echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
                printcontent(0, FALSE, $content, $edit);
                echo "</tr></table> ";
            }
            echo '</div>';
        }
        echo "\n</td></tr>\n</table>";
        return TRUE;
    }
    // keine Termine da, aber die Moeglichkeit welche einzustellen
    else if($show_admin) {
        // set skip link
        SkipLinks::addIndex(_("Termine"), 'appointments_box');

        echo "\n<table id=\"appointments_box\" role=\"article\" class=\"index_box\">";
        echo "\n<tr><td class=\"table_header_bold\">" . Assets::img('icons/16/white/schedule.png', array('class' => 'text-top', 'title' =>_('Termine'))) . '<b>  ' . _("Termine") . '</b></td>';
        echo "\n<td align=\"right\" class=\"table_header_bold\"> $admin_link<img src=\"" . Assets::image_path('icons/16/white/admin.png') . '" ' . tooltip(_("Termine einstellen")) . '></a> </td></tr>';
        ?>
        <tr>
            <td class="table_row_even" colspan="2">
                <p class="info">
                    <?= _("Es sind keine aktuellen Termine vorhanden. Um neue Termine zu erstellen, klicken Sie rechts auf die Zahnr�der.") ?>
                </p>
            </td>
        </tr>
        </table>
        <?
        return TRUE;
    }

    else {
        return FALSE;
    }
}
