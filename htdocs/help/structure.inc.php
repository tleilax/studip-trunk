<?php
/*
help/structure.inc.php - die Struktur der Hilfeseiten von Stud.IP
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>

modifiziert 2002 von:
Cornelis Kater <ckater@gwdg.de>,
Marco Bohnsack <Silencer@www.funcity.de>


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

$pages = array	(	
array	(	"name" => "Allgemeines",
				"text" => "Einige generelle Informationen zu Stud.IP ",
				"perm" =>	"",
				"kategorien" => array	(	
																array	(	"name" => "Einleitung",
																				"text" => "Das Ziel von Stud.IP",
																				"page" => "help1.html"
																			),
																array	(	"name" => "Hilfe zur Hilfe",
																				"text" => "Was Sie �ber diese Hilfefunktion wissen sollten",
																				"page" => "help_help.html"
																			),
																array	(	"name" => "Nutzungsbedingungen",
																				"text" => "Die rechtlichen Grundlagen",
																				"page" => "nutzung.html"
																			)
															)
			),
			
array	(	"name" => "Die Anmeldung",
				"text" => "Alles, was Sie �ber die Anmeldung wissen m�ssen",
				"perm" =>	"",
				"kategorien" => array	(	
																array	(	"name" => "Zugang zum System",
																				"text" => "Wie komme ich in Stud.IP?",
																				"page" => "ii_zugang.htm"
																			),
																array	(	"name" => "Vorteile der Anmeldung",
																				"text" => "Warum soll ich mich anmelden?",
																				"page" => "ii_vorteile_anmeldung.htm"
																			),
																array	(	"name" => "Die Registrierung",
																				"text" => "Was muss ich tun um mich anzumelden?",
																				"page" => "ii_anmeldeformular.htm"
																			),
																array	(	"name" => "Die Best�tigungsmail",
																				"text" => "nur noch ein kleiner Schritt...",
																				"page" => "ii_bestaetigungsmail.htm"
																			),
																array	(	"name" => "Die Login-Seite",
																				"text" => "Der erste Login",
																				"page" => "ii_login.htm"
																			),
																array	(	"name" => "Passwort vergessen?",
																				"text" => "Nur keine Panik...",
																				"page" => "ii_passwort.htm"
																			)
															)
			),



array	(	"name" => "Erste Schritte",
				"text" => "Eine Kurzeinweisung speziell f�r Neulinge",
				"perm" =>	"",
				"kategorien" => array	(	
																array	(	"name" => "Die Startseite",
																				"text" => "Ihre �bersichtsseite nach jedem Login",
																				"page" => "startseite.html"
																			),
																
																array	(	"name" => "Schnelleinstieg",
																				"text" => "Das Wichtigste in K�rze",
																				"page" => "schnelleinstieg.htm"
																			),
																array	(	"name" => "Die eigene Homepage",
																				"text" => "Erz�hlen Sie der Welt von sich...",
																				"page" => "iii_homepage.htm"
																			)
																
															)
			),


array	(	"name" => "Die eigene Homepage",
				"text" => "Ihre private Ecke in Stud.IP",
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => "Pers�nliche Homepage",
																				"text" => "In 5 Minuten eingerichtet!",
																				"page" => "iii_homepage.htm"
																			),
																array	(	"name" => "Eigenes Bild",
																				"text" => "Bleiben Sie nicht im Dunkeln",
																				"page" => "iii_homepagea.htm"
																			),
																array	(	"name" => "Pers�nlichen Daten",
																				"text" => "Was mu�, was kann?",
																				"page" => "iii_homepageb.htm"
																			),
																array	(	"name" => "Karriere",
																				"text" => "Was tun Sie so an der Uni?",
																				"page" => "iii_homepagec.htm"
																			),
																array	(	"name" => "Lebenslauf",
																				"text" => "Und was machen Sie sonst noch? ",
																				"page" => "iii_homepaged.htm"
																			),
																array	(	"name" => "Sonstiges",
																				"text" => "Eigene Kategorien anlegen",
																				"page" => "iii_homepagee.htm"
																			),
																			
																
																			
															)
			),
			
			

array	(	"name" => "Interaktion",
				"text" => "Wie man mit anderen Nutzern des Systems interagieren kann ",
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => "Die Homepages der anderen",
																				"text" => "Wie Sie die Homepages anderer Nutzer finden k�nnen",
																				"page" => "iv_interaktion.htm"
																			),
																array	(	"name" => "Wer ist online?",
																				"text" => "Wie Sie herausfinden, wer ausser Ihnen gerade im System ist",
																				"page" => "iv_online.htm"
																			),
																array	(	"name" => "Systeminterne SMS",
																				"text" => "Wie Sie Nachrichten an andere Nutzer schicken k�nnen",
																				"page" => "iv_sms.htm"
																			),
																array	(	"name" => "Der Chatbereich",
																				"text" => "Wo und wie Sie in Stud.IP chatten k�nnen",
																				"page" => "iv_chat.htm"
																			)
															)
			),
array	(	"name" => "Meine Veranstaltungen",
				"text" => "Meine Veranstaltungen - hinzuf�gen, l�schen, verwalten",
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => "Veranstaltungen abonnieren",
																				"text" => "Wie Sie Veranstaltungen zu 'Meine Veranstaltungen' hinzuf�gen",
																				"page" => "v_abonnieren.htm"
																			),
																array	(	"name" => "Der Veranstaltungs-Browser",
																				"text" => "Die Veranstaltungssuchmaschine",
																				"page" => "v_sembrowse.htm"
																			),
																array	(	"name" => "Was ist neu?",
																				"text" => "Alle Neuigkeiten im Blick",
																				"page" => "v_neu.htm"
																			),
																array	(	"name" => "Veranstaltungen ordnen",
																				"text" => "Ordnung in die Veranstaltungs�bersicht bringen",
																				"page" => "v_ordnen.htm"
																			),
																array	(	"name" => "Abonnements k�ndigen",
																				"text" => "Wie Sie Veranstaltungen aus 'Meine Veranstaltungen' entfernen",
																				"page" => "v_kuendigen.htm"
																			)																		
															)
			),
array	(	"name" => "In der Veranstaltung: grundlegende Funktionen",
				"text" => "Wie Sie sich im Veranstaltungsbereich zurechtfinden",
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => "Navigation",
																				"text" => "Die Bedienlogik des Veranstaltungsbereichs",
																				"page" => "vi_navi.htm"
																			),
																array	(	"name" => "Kurzinfo",
																				"text" => "Die Startseite im Veranstaltungsbereich",
																				"page" => "vi_kurz.htm"
																			),
																array	(	"name" => "Detailansicht",
																				"text" => "Erweiterte Informationen",
																				"page" => "vi_detail.htm"
																			),
																array	(	"name" => "Druckansicht",
																				"text" => "alles auf einen Blick",
																				"page" => "vi_druckansicht.htm"
																			),
																array	(	"name" => "Teilnehmer",
																				"text" => "Personen in der Veranstaltung",
																				"page" => "vi_teilnehmer.htm"
																			),
																array	(	"name" => "Funktionen / Gruppen",
																				"text" => "Nutzer in Gruppen organisieren",
																				"page" => "vi_statusgruppen_show.htm"
																			),

																array	(	"name" => "Ablaufplan",
																				"text" => "Termine finden",
																				"page" => "vi_ablauf.htm"
																			),
																array	(	"name" => "Literatur & Links",
																				"text" => "Materialien f�r die Veranstaltung",
																				"page" => "vi_literatur.htm"
																			)																		
															)
			),
			
			
			
array	(	"name" => "In der Veranstaltung: das Forum",
				"text" => "Diskutieren & streiten",
				"perm" =>	"user",
				"kategorien" => array	(	
																
																			
																array	(	"name" => "Funktionen des Forums",
																				"text" => "Bedienlogik, Ansichten, Postings verfassen",
																				"page" => "ix_forum1.htm"
																			),
																array	(	"name" => "Einstellungen des Forums",
																				"text" => "Schonen Sie Ihr Modem ",
																				"page" => "iii_homepagef2.htm"
																			),
																array	(	"name" => "Neue Beitr�ge",
																				"text" => "Was gibt�s Neues?",
																				"page" => "ix_forum2.htm"
																			),
																			array	(	"name" => "Letzte 5 Beitr�ge",
																				"text" => "Was als letztes los war",
																				"page" => "ix_forum3.htm"
																			),
																array	(	"name" => "Suchen",
																				"text" => "Finden eines bestimmten Postings",
																				"page" => "ix_forum4.htm"
																			)
																
																			
																			
																			
																			
															)
			),
array	(	"name" => "In der Veranstaltung: die Dateiverwaltung",
				"text" => "Wie Sie Dateien in das System einstellen und herunterladen",
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => "Dateien herunterladen",
																				"text" => "Die Dateiverwaltung und wie Sie Dateien herunterladen",
																				"page" => "vii_download.htm"
																			),
																array	(	"name" => "Dateien einstellen",
																				"text" => "Wie Sie neue Dateien ins System einstellen",
																				"page" => "vii_upload.htm"
																			),
																array	(	"name" => "Dateien bearbeiten",
																				"text" => "Verschieben, l�schen oder die Beschreibung �ndern",
																				"page" => "vii_move.htm"
																			)
																
																)
			),
			
			
			
			
			array	(	"name" => "Der Veranstaltungs-Assistent",
				"text" => "Veranstaltungen anlegen -ganz einfach!",
				"perm" =>	"tutor",
				"kategorien" => array	(	
																array	(	"name" => "Grunddaten",
																				"text" => "Name, Beschreibung, Raum u.a.",
																				"page" => "va_assi1.htm"
																			),
																array	(	"name" => "Personendaten, Typ und Sicherheit",
																				"text" => "DozentInnen, TutorInnen und Passw�rter",
																				"page" => "va_assi2.htm"
																			),
																array	(	"name" => "Termindaten",
																				"text" => "Wann finden Sitzungen statt?",
																				"page" => "va_assi3.htm"
																			),
																array	(	"name" => "Sonstiges",
																				"text" => "Voraussetzungen, Lernorganisation, Leistungsnachweis, Sonstiges",
																				"page" => "va_assi4.htm"
																			),
																array	(	"name" => "Bereit zum anlegen",
																				"text" => "Fast fertig!",
																				"page" => "va_assi5.htm"
																			), 
																array	(	"name" => "Literatur- und Linkliste",
																				"text" => "B�cher und Webquellen anlegen",
																				"page" => "va_assi6.htm"
																			), 
																array	(	"name" => "Ablaufplan und Termine",
																				"text" => "Manuell oder automatisch anlegen",
																				"page" => "va_assi7.htm"
																			) 
															)
			),
			
array	(	"name" => "Veranstaltungen verwalten",
				"text" => "Wie Sie Ihre Veranstaltungen anlegen und aktuell halten",
				"perm" =>	"tutor",
				"kategorien" => array	(	
																array	(	"name" => "Die Administrierungsseite",
																				"text" => "Zugang zur Veranstaltungsverwaltung",
																				"page" => "x_adminarea.htm"
																			),
																
																array	(	"name" => "Basisdaten �ndern",
																				"text" => "Wie Sie die Basisdaten einer Veranstaltung �ndern k�nnen",
																				"page" => "x_aendern.htm"
																			),
																array	(	"name" => "Literatur / Links",
																				"text" => "Wie Sie die Literatur und Linklisten Ihrer Veranstaltungen anpassen",
																				"page" => "x_literatur.htm"
																			),
																array	(	"name" => "Ablaufplan eingeben / �ndern",
																				"text" => "Wie Sie den Ablaufplan einer Veranstaltung verwalten k�nnen",
																				"page" => "x_ablauf.htm"
																			),
																array	(	"name" => "Zeiten �ndern",
																				"text" => "Wie Sie die Veranstaltungszeiten �ndern k�nnen",
																				"page" => "x_metadates.htm"
																			),
																array	(	"name" => "Zugangsberechtigungen �ndern",
																				"text" => "Wie Sie die Zugangsberechtigungen f�r Veranstaltungen �ndern k�nnen",
																				"page" => "x_admission.htm"
																			),
																array	(	"name" => "Themen anlegen",
																				"text" => "Wie Sie Debattenthemen f�r das Forum vorgeben k�nnen",
																				"page" => "x_themen.htm"
																			), 
																array	(	"name" => "Teilnehmer verwalten",
																				"text" => "Wie Sie Teilnehmer verwalten und Tutoren ernennen k�nnen",
																				"page" => "x_teil.htm"
																			), 
																array	(	"name" => "Funktionen / Gruppen verwalten",
																				"text" => "Wie Sie Teilnehmer mit Funktionen oder Gruppen organisieren",
																				"page" => "x_statusgruppen_admin.htm"
																			), 

																array	(	"name" => "Dateiordner verwalten",
																				"text" => "Wie Sie Dateiordner verwalten k�nnen",
																				"page" => "x_datei.htm"
																			), 
																array	(	"name" => "News anlegen und verwalten",
																				"text" => "Wie Sie die neuesten Neuigkeiten unters Volk bringen",
																				"page" => "x_admin_news.htm"
																			) 
															)
			),
			
			array	(	"name" => "Terminkalender und Stundenplan",
				"text" => "Ihr Timeplaner im Netz",
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => "Der Stundenplan",
																				"text" => "Praktisch und einfach",
																				"page" => "stupla.htm"
																			),
																array	(	"name" => "Der Terminkalender",
																				"text" => "Bedienung und Ansichten",
																				"page" => "termin1.htm"
																			),
																array	(	"name" => "Termine bearbeiten",
																				"text" => "Anlegen und �ndern von Terminen",
																				"page" => "termin2.htm"
																			),
																array	(	"name" => "Termine einbinden",
																				"text" => "Veranstaltungstermine im Terminkalender anzeigen",
																				"page" => "termin3.htm"
																			),
																array	(	"name" => "Ansicht anpassen",
																				"text" => "Optionen des Terminkalenders",
																				"page" => "iii_homepagef3.htm"
																			)
																
															)
			),
			
			
			array	(	"name" => "MyStud.IP",
				"text" => "Stud.IP anpassen",
				"perm" =>	"user",
				"kategorien" => array	(	
																
																			
																array	(	"name" => "Autlogin",
																				"text" => "Wenn es bequem sein soll",
																				"page" => "iii_homepagef1.htm"
																			),
																array	(	"name" => "Einstellungen des Forums",
																				"text" => "Schonen Sie Ihr Modem ",
																				"page" => "iii_homepagef2.htm"
																			),
																array	(	"name" => "Einstellungen des Terminkalenders",
																				"text" => "Die Zeit l�uft -aber wie schnell?",
																				"page" => "iii_homepagef3.htm"
																			),
																			array	(	"name" => "Einstellungen des Stundenplans",
																				"text" => "Haben Sie heute frei? ",
																				"page" => "iii_homepagef4.htm"
																			),
																array	(	"name" => "Einstellungen des Messaging",
																				"text" => "So bleiben Sie in Kontakt",
																				"page" => "iii_homepagef5.htm"
																			)
																			
															)
			),
			
			
			
			
			
			array	(	"name" => "Suchen",
				"text" => "Was m�chten Sie finden?",
				"perm" =>	"user",
				"kategorien" => array	(	
																
																			
																array	(	"name" => "Die Suchauswahl",
																				"text" => "Was genau suchen Sie?",
																				"page" => "xii_suchen1.htm"
																			),
																array	(	"name" => "Suchen nach Personen",
																				"text" => "DozentInnen und KommilitonInnen",
																				"page" => "personensuche.htm"
																			),
																array	(	"name" => "Suchen nach Veranstaltungen",
																				"text" => "Veranstaltungen des aktuellen und kommenden Semesters",
																				"page" => "v_abonnieren.htm"
																			),
																			array	(	"name" => "Suchen im Archiv",
																				"text" => "Veranstaltungen vergangener Semester",
																				"page" => "xii_suchen3.htm"
																			)
																
																			
															)
			),
			array	(	"name" => "Verschiedenes",
				"text" => "Textformatierungen, FAQ u.a.",
				"perm" =>	"user",
				"kategorien" => array	(	
																
																			
																array	(	"name" => "Formatierungen von Text",
																				"text" => "Fett, kursiv, Aufz�hlungen und mehr",
																				"page" => "ix_forum6.htm"
																			),
																array	(	"name" => "Smilies",
																				"text" => "Zeigen Sie Gef�hl",
																				"page" => "ix_forum7.htm"
																			),
																array	(	"name" => "Score-Liste",
																				"text" => "Die Stud.IP-Rangliste",
																				"page" => "score.htm"
																			),
																array	(	"name" => "Glossar",
																				"text" => "Kurze Erkl�rungen",
																				"page" => "glossar.htm"
																			),
																array	(	"name" => "FAQ",
																				"text" => "Oft gestellte Fragen",
																				"page" => "faq.htm"
																			)
																			
															)
			)
);
//show help for resources management, if available
if ($GLOBALS["RESOURCES_ENABLE"]) {
	$pages[] = array	(	"name" => "Ressourcenverwaltung",
				"text" => "Verwaltung unterschiedlicher Ressourcen in Stud.IP",
				"perm" =>	"user",
				"kategorien" => array	(	
																
																			
																array	(	"name" => "Einf�hrung",
																				"text" => "Was fange ich mit der Ressourcenverwaltung an?",
																				"page" => "resources_intro.htm"
																			)
															)
			);
	
}

//show help for resources management, if available
if ($GLOBALS["ILIAS_CONNECT_ENABLE"]) {
	$pages[] = array	(	"name" => "ILIAS Lernmodule in Stud.IP",
				"text" => "Einrichten und Nutzen von Lernmodulen aus ILIAS Open Source",
				"perm" =>	"user",
				"kategorien" => array	(	
																
																			
																array	(	"name" => "Was ist ILIAS",
																				"text" => "Was ist ILIAS und was sind ILIAS Lernmodule",
																				"page" => "what_is_ilias.php"
																			)
															)
			);
	
}
?>