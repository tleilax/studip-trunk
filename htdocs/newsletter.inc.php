<?php
/*
newsletter.inc.php - Funktionen fuer den Newsletter
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

function CheckPersonInNewsletter ($username, $newsletter_id)    // Ist jemand aufgrund der SQL-Clause im Versand?
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT * ".
		"FROM auth_user_md5 ".
		$newsletter[$newsletter_id]["SQL"].
		" AND user_id = '$user_id'";
	$db->query($query); 
	if ($db->next_record()) {
		$status = "letter";
	} else {
		$status = FALSE;
	}
	return $status;
}


function CheckPersonNewsletter ($username, $newsletter_id)    // Ist jemand in der Ausnahmeliste?
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT status FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'";
	$db->query($query); 
	if ($db->next_record()) {
		if ($db->f("status") == 1) {
			$status = "added";
		} else {
			 $status = "removed";
		}
	} else {
		$status = FALSE;
	}
	return $status;
}

function CheckStatusPersonNewsletter ($username, $newsletter_id)   // was ist nun der Gesamtstatus?
{ global $newsletter;
	if (CheckPersonInNewsletter ($username, $newsletter_id)=="letter" AND CheckPersonNewsletter ($username, $newsletter_id) !="removed") {
		$status = "Eingetragen";
	} elseif (CheckPersonNewsletter ($username, $newsletter_id) =="added") {		
		$status = "Eingetragen";
	} else {
		$status = "Ausgetragen";
	}
	return $status;
}  

function AddPersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Positivliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "removed") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg�Der Nutzer $username wurde wieder in den Newsletter aufgenommen.�";
	} elseif (CheckPersonInNewsletter($username, $newsletter_id) != "letter" AND $status != "added")  {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '1', newsletter_id = '$newsletter_id'");
		$msg = "msg�Der Nutzer $username wurde in den Newsletter aufgenommen.�";
	}
	return $msg;
}

function RemovePersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Negativliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "added") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg�Der Nutzer $username wurde wieder aus dem Newsletter gel&ouml;scht.�";
	} elseif ($status != "removed") {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '0', newsletter_id = '$newsletter_id'");
		$msg = "msg�Der Nutzer $username wurde aus dem Newsletter gel&ouml;scht.�";
	}
	return $msg;
}

// Newsletter arrays

	// Standard

	$newsletter[0]["name"] = "Stud.IP Newsletter";
//	$newsletter[0]["SQL"] = "WHERE username = 'rstockm'";
	$newsletter[0]["SQL"] = "WHERE perms != 'user' AND perms != 'autor'";
	$newsletter[0]["return"] = "crew@studip.de";
	$newsletter[0]["text"] = 
"Stud.IP-Newsletter  #01  / 19.10.2002
----------------------------------------------------------------------

Inhalt

0. Vorwort
1. Release der 0.8.15 auf dem G�ttinger Hauptserver
2. Informationsveranstaltungen und Schulungen zum Semesterstart 
3. Neue Funktionen der letzten beiden Releases
4. Archivieren von Veranstaltungen des SS02
5. Impressum



**********************************************************************
** 0. Vorwort
**********************************************************************

Sehr geehrte Nutzer und Nutzerinnen von Stud.IP,

seit unser System im Rahmen der 'Notebook University' gef�rdert wird,
haben sich die Entwicklungszyklen beschleunigt. Viele neue
Funktionalit�ten sind dazugekommen, mit denen wir auf die vielf�ltigen
Anregungen der User eingehen.

Damit Sie als Nutzer nicht den �berblick verlieren und immer auf dem
aktuellen Stand bleiben k�nnen, habe wir diesen Newsletter entwickelt.
Zu jeder neuen Release informieren wir �ber alle �nderungen, die
Sie in Ihrer t�glichen Arbeit mit dem System betreffen k�nnten.

Adressaten dieses Newsletters sind alle im System registrierten
Nutzer ab dem Status 'Tutor'.

Wenn Sie sich aus dem Newsletter abmelden wollen, scrollen Sie an
das untere Ende und folgen Sie dort dem Link.

Eine informative Lekt�re w�nscht

Ihre Stud.IP Crew



**********************************************************************
** 1. Release der 0.8.15 auf dem G�ttinger Hauptserver
**********************************************************************

Am Sonntag den 13.10.2002 haben wir um 15 Uhr den G�ttinger Stud.IP
Server auf die neueste Version geupdatet (Version 0.8.15). Die neuen
Funktionalit�ten stehen allen angeschlossenen Instituten ab sofort
zur Verf�gung (siehe 3.).



**********************************************************************
** 2. Informationsveranstaltungen und Schulungen zum Semesterstart
**********************************************************************

Nicht nur durch die NBU-Verastaltungen begr��en wir immer mehr
Studierende im System. Die aktuelle Anzahl liegt bei 2740.
Die meisten Studierenden haben mit den selbsterkl�renden Funktionen
von Stud.IP wenig Probleme, aber die 'Medienkompetenz' ist bekanntlich
doch unterschiedlich ausgepr�gt.

Auf vielfachen Wunsch bieten wir daher zu mehreren Terminen im Semester
�ffentliche Schulungsveranstaltungen an, in denen Studierenden auf
einfache und unterhaltende Weise die Grundfunktionen des Systems 
erkl�rt werden.

Zielgruppe sind also ausschlie�lich Studierende, die ersten beiden
Termine sind:


Dienstag  29.10. 14.00 Uhr ZHG 010
Mittwoch, 30.10. 16.00 Uhr ZHG 010


Bitte teilen Sie diese Termine Ihren Studierenden mit, an beiden 
Terminen werden dieselben Grundlagen vermittelt.

Auch f�r TutorInnen und DozentInnen der beteiligten Einrichtungen
bieten wir im Semester gezielte Schulungen an. Diese werden �ber
die Zentrale Einrichtung Medien (ZEM) organisiert, ein Vertreter
macht die Einf�hrungen direkt bei Ihnen vor Ort.

Ansprechpartner f�r diese Schulungen ist:

Dirk Pfuhl
----------------------------------
Telefon: 0551-39 8351 
Email: dpfuhl@uni-goettingen.de 
Homepage: http://www.dirkpfuhl.de 
----------------------------------

In den n�chsten Wochen werden des weiteren Informationsflyer f�r
die verschiedenen Zielgruppen 'Autoren', 'Tutoren/Dozenten' und
'Admins' verf�gbar sein mit entsprechenden F.A.Q., Nutzungshinweisen
etc.



**********************************************************************
** 3. Neue Funktionen der letzten beiden Releases
**********************************************************************

Hinweis: zu allen neuen Funktionen finden Sie ausf�hrliche Hilfeseiten
direkt im System, wie gewohnt �ber das Fragezeichensymbol '?' zu
erreichen!


3.a. Funktionen- und Gruppenverwaltung

In vielen Situationen kann es hilfreich sein, 
die Teilnehmer einer Veranstaltung oder einer Einrichtung nach 
Funktionen oder Gruppen zu ordnen. 
In einer Veranstaltung k�nnten dies beispielsweise sein: 

- Unterteilung der Studierenden nach der Art des Scheinerwerbs 
  (kein Schein / Teilnahmeschein / Leistungsschein). 
  Sie sehen dann etwa auf einen Blick, welche Studierende 
  besondere Leistungen erbringen m�ssen... 

- Unterteilung der Studierenden nach Aufgabengebieten. 
  In einer Praxisveranstaltung etwa soll Teamarbeit gef�rdert werden, 
  dazu wird der Kurs aufgeteilt in 'Designer' und 'Programmierer'.

- Die Hausarbeiten der Veranstaltung werden in Gruppen angefertigt. 
  Diese k�nnen Sie so einfach verwalten und behalten den �berblick...


F�r jede Einrichtung sind Einteilungen fast unverzichtbar: 

- Um die Hierarchien einer Einrichtung Abzubilden 
  (Hochschullehrer, Mittelbau, Sekretariat etc.)
- Um Kompetenzbereiche oder Lehrst�hle abzubilden 
  (Ein Professor und sein 'Hofstaat')
- Um Themenschwerpunkte abzugrenzen etc.

Die entsprechenden Funktionen sind f�r Veranstaltungen am dem Status
'Tutor' verf�gbar, f�r Institute nehmen 'Admins' die entsprechenden
Einstellungen vor.


3.b. Meine aktuellen Termine auf der Startseite

Auf der pers�nlichen Startseite werden jetzt die Termine der n�chsten
sieben Tage angezeigt. Es werden genau die Termine angezeigt, die auch
im pers�nlichen Terminkalender eingebunden sind. Dort haben Sie wie
bisher auch die M�glichkeit, eigene veranstaltungsunabh�ngige Termine
anzulegen.


3.c. Verbessertes Arbeitsgruppenmanagement

F�r Forschungs- und Arbeitsgruppen gibt es eine Reihe Vereinfachungen
beim Anlegen.


3.d. Einrichtungen als Objekte im System

Einrichtungen wie Institute sind nun als eigenst�ndige Objekte im System
verankert. Sie k�nnen also f�r jedes Institut das Forensystem nutzen
(etwa f�r eine Studienberatung), den Dateibereich (Pr�fungsordnungen etc.)
und dergleichen mehr.
Sie erreichen den Einrichtungsbereich �ber die Veranstaltungs�bersichtsseite
mit dem neuen Reiter 'meine Einrichtungen'.


3.e. Zulassungsverfahren f�r teilnehmerbeschr�nkte Veranstaltungen

F�r teilnehmerbeschr�nkte Veranstaltungen stehen nun m�chtige 
Verwaltungsfunktionen zur Verf�gung: beim Anlegen k�nnen Sie die
maximale Teilnehmerzahl angeben. Es gibt bisher zwei Verfahrensweisen:

- Anmeldung nach Reihenfolge (wer zuerst kommt, malt zuerst):
  Die Studierenden k�nnen sich in die Veranstaltung eintragen bis diese 'voll'
  ist. Danach landet man auf einer Warteliste. Eingetragene Studierende 
  k�nnen ihren Platz aufgeben, es wird dann automatisch 'nachger�ckt'.

- Zuordnung durch Losverfahren:
  Alle Bewerber landen in einer Warteliste. An einem einstellbaren Termin
  werden die Teilnehmer ausgelost. Alle nicht gelosten landen in einer 
  Warteliste. Man hat zus�tzlich die M�glichkeit, Kontingente nach
  Studieng�ngen einzurichten, also etwa 50% Hauptf�chler, 40% Nebenf�chler,
  10% Sonstige etc.

Ab dem Status Tutor sehen Sie auf Ihrer TeilnehmerInnenseite den aktuellen
Stand der Zuweisungen. 
 


**********************************************************************
** 4. Archivieren von Veranstaltungen des SS02
**********************************************************************


Da das Wintersemester 2002 offiziell begonnen hat, empfiehlt es sich
alle abgelaufenen Veranstaltungen des vergangenen Sommersemesters zu
archivieren. Solange dies nicht geschieht, verbleiben die 
Veranstaltungen auf den �bersichtsseiten im System.

Archiviert werden k�nnen Veranstaltungen ausschlie�lich von den
Administratoren der Institute. Als Administrator sollten Sie daher
unter Ihren Lehrenden in Erfahrung bringen, welche Veranstaltungen
archiviert werden k�nnen. Das Archivieren geschieht sehr einfach �ber
den Bereich 'Verwaltung von Veranstaltungen'.

Sollten Veranstaltungen noch weiterlaufen, sollten diese entsprechend
nicht archiviert werden.

Auf alle archivierten Veranstaltungen haben Sie nach wie vor Zugriff
�ber die Option 'Suchen' auf der Startseite. Zugriff auf die
Veranstaltungen haben alle TeilnehmerInnen der Veranstaltungen.
Als Admin oder DozentIn k�nnen Sie auch nachtr�glich Nutzern Zugriff
erlauben.



**********************************************************************
** 5. Impressum
**********************************************************************

Redaktion des Newsletter:

  Stud.IP Entwicklercrew

Kontakt:

  Dipl.- Sozw. Ralf Stockmann
  ZiM - Humboldtallee 23
  +49 551 39 92 48
  rstockm@uni-goettingen.de
  www.studip.de
  www.zim.uni-goettingen.de";

	// weitere

	$newsletter[1]["name"] = "Stud.IP Admin-Newsletter";
	$newsletter[1]["SQL"] = "WHERE perms = 'admin'";
	$newsletter[1]["text"] = "Hallo dies ist ein noch ein Text";



?>