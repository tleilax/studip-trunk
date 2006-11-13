<?
/**
* config.inc.php
* 
* Configuration file for studip. In this file you can change the options of many
* Stud.IP Settings. Please note: to setup the system, set the basic settings in the
* local.inc of the phpLib package first.
* 
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		config.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, 
// Ralf Stockmann <rstockm@gwdg.de>, Andr� Noack Andr� Noack <andre.noack@gmx.net>
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

/*basic settings for Stud.IP
----------------------------------------------------------------
you find here the indivdual settings for your installation. 
please note the LOCAL.INC.PHP in the php-lib folder for the basic system settings!*/


//Some more basic data
//Note: The the clean-name of your master-faculty ($UNI_NAME_CLEAN) is stored in the local.inc
$UNI_URL = "http://www.studip.de";
$UNI_LOGIN_ADD='';
$UNI_LOGOUT_ADD=sprintf(_("Und hier geht's zur %sStud.IP Portalseite%s&nbsp;"), "<a href=\"http://www.studip.de\"><b>", "</b></a>");
$UNI_CONTACT = "<please insert your general contact mail-adress here>";
$UNI_INFO = "Stud.IP 1.1.5 - Studienbegleitender Internetsupport von Pr&auml;�senzlehre"; 


//Festlegen der zulaessigen Typen fuer Veranstaltungen
$SEM_TYPE_MISC_NAME=_("sonstige"); //dieser Name wird durch die allgemeine Bezeichnung (=Veranstaltung ersetzt)
$SEM_TYPE[1]=array("name"=>_("Vorlesung"), "class"=>1);
$SEM_TYPE[2]=array("name"=>_("Seminar"),  "class"=>1);
$SEM_TYPE[3]=array("name"=>_("�bung"),  "class"=>1);
$SEM_TYPE[4]=array("name"=>_("Praktikum"), "class"=>1);
$SEM_TYPE[5]=array("name"=>_("Colloquium"),  "class"=>1);
$SEM_TYPE[6]=array("name"=>_("Forschungsgruppe"), "class"=>1);
$SEM_TYPE[7]=array("name"=>_("sonstige"), "class"=>1); 
$SEM_TYPE[8]=array("name"=>_("Gremium"), "class"=>2); 
$SEM_TYPE[9]=array("name"=>_("Projektgruppe"), "class"=>2); 
$SEM_TYPE[10]=array("name"=>_("sonstige"), "class"=>2); 
$SEM_TYPE[11]=array("name"=>_("Kulturforum"), "class"=>3); 
$SEM_TYPE[12]=array("name"=>_("Veranstaltungsboard"), "class"=>3); 
$SEM_TYPE[13]=array("name"=>_("sonstige"), "class"=>3); 
//weitere Typen koennen hier angefuegt werden


//Festlegen der zulaessigen Klassen fuer Veranstaltungen. Jeder sem_type referenziert auf eine dieser Klassen
$SEM_CLASS[1]=array("name"=>_("Lehre"), 					//the name of the class
					"compact_mode"=>FALSE, 			//indicates, if all fields are used in the creation process or only the fields that are necessary for workgroups
					"workgroup_mode"=>FALSE, 		//indicates, if the workgroup mode is used (to use different declarations)
					"only_inst_user"=>TRUE,			//indicates, that olny staff from the Einrichtungen which own the Veranstaltung, are allowed for tutor and dozent
					"turnus_default"=>0	, 		//indicates, whether the turnus field is default set to "regulary" (0), "not regulary" (1) or "no dates" (-1) in the creation process
					"default_read_level"=>1, 		//the default read acces level. "without signed in" (0), "signed in" (1), "password" (2)
					"default_write_level" =>1, 		//the default write acces level. "without signed in" (0), "signed in" (1), "password" (2)
					"bereiche"=>TRUE,			//indicates, if bereiche should be used
					"show_browse"=>TRUE, 			//indicates, if the hierachy-system should be shown in the search-process
					"write_access_nobody"=>FALSE, 		//indicates, if write access level 0 is possible. If this is not possibly, don't set default_write_level to 0
					"topic_create_autor"=>FALSE,		//indicates, if global autor is allowed to create topic in the forums 
					"visible"=>TRUE,			//indicates, if the seminar is visible throughout the systems, if FALSE, it is hidden!
					//modules, select the active modules for this class
					"forum"=>TRUE,				//forum, this module is stud_ip core; always available
					"documents"=>TRUE,			//documents, this module is stud_ip core; always available
					"schedule"=>TRUE,			//schedule, this module is stud_ip core; always available
					"participants"=>TRUE,			//participants, this module is stud_ip core; always available
					"scm"=>FALSE,				//simple content module, this modul is stud_ip core; always available 
					"literature"=>TRUE,			//literature, this module is stud_ip core; always available
					"chat"=>TRUE,				//chat, only, if the module is global activated; see local.inc
					"ilias_connect"=>TRUE,			//Ilias-connect, only, if the module is global activated; see local.inc
					"wiki"=>TRUE,				//wikiwiki-web, this module is stud_ip core; always available
					"support"=>FALSE,			//support, only, if the module is global activated; see local.inc (this modul is not part of the main distribution)
					//descriptions
					"description"=>_("Hier finden Sie alle in Stud.IP registrierten Lehrveranstaltungen"), 						//the description
					"create_description"=>_("Verwenden Sie diese Kategorie, um normale Lehrveranstaltungen anzulegen"));		//the description in the creation process

$SEM_CLASS[2]=array("name"=>_("Organisation"), 
					"compact_mode"=>TRUE, 
					"workgroup_mode"=>TRUE, 
					"only_inst_user"=>TRUE,
					"turnus_default"=>-1, 
					"default_read_level"=>2, 
					"default_write_level" =>2, 
					"show_browse"=>TRUE,
					"visible"=>TRUE,
					"forum"=>TRUE,
					"documents"=>TRUE,
					"schedule"=>TRUE,
					"participants"=>TRUE,
					"scm"=>FALSE,
					"literature"=>TRUE,
					"chat"=>TRUE,
					"wiki"=>TRUE,					
					"description"=>_("Hier finden Sie virtuelle Veranstaltungen zum Thema Forschung an der Universit&auml;t"),
					"create_description"=>_("In dieser Kategorie k&ouml;nnen sie virtuelle Veranstaltungen f&uuml;r Forschungsprojekte anlegen."));

$SEM_CLASS[3]=array("name"=>_("Community"), 
					"compact_mode"=>TRUE, 
					"turnus_default"=>-1, 
					"default_read_level"=>1, 
					"default_write_level" =>1, 
					"show_browse"=>TRUE,
					"write_access_nobody"=>TRUE, 
					"visible"=>TRUE,
					"forum"=>TRUE,
					"documents"=>TRUE,
					"schedule"=>TRUE,
					"scm"=>FALSE,
					"participants"=>TRUE,
					"chat"=>TRUE,					
					"description"=>_("Hier finden Sie virtuelle Veranstaltungen zu verschiedenen Gremien an der Universit&auml;t"),
					"create_description"=>_("Um virtuelle Veranstaltungen f&uuml;r Uni-Gremien anzulegen, verwenden Sie diese Kategorie"));
//weitere Klassen koennen hier angefuegt werden. Bitte Struktur wie oben exakt uebernehmen.


//Festlegen der erlaubten oder verbotenen Dateitypen
$UPLOAD_TYPES=array( 	"default" =>												//Name bezeichnet den zugehoerigen SEM_TYPE, name "1" waere entsprechend die Definition der Dateiendungen fuer SEM_TYPE[1]; default wird verwendet, wenn es keine spezielle Definition fuer einen SEM_TYPE gibt
						array(	"type"=>"deny", 									//Type bezeichnet den grundsetzlichen Typ der Deklaration: deny verbietet alles ausser den angegebenen file_types, allow erlaubt alle ausser den angegebenen file_types
								"file_types" => array ("rtf", "xls", "ppt", "zip", "pdf", "txt", "tgz", "gz", "bz2"),	//verbotene bzw. erlaubte Dateitypen
								"file_sizes" => array (	"root" => 7 * 1048576,			//Erlaubte Groesse je nach Rechtestufe
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,
													"tutor" => 7 * 1048576,
													"autor" => 1.38 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"8" =>													 
						array(	"type"=>"allow", 									
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,
													"tutor" => 7 * 1048576,													
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"9" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							),
						"10" =>									
						array(	"type"=>"allow",
								"file_types" => array ("exe"),
								"file_sizes" => array (	"root" => 7 * 1048576,			
													"admin" => 7 * 1048576,
													"dozent" => 7 * 1048576,													
													"tutor" => 7 * 1048576,
													"autor" => 7 * 1048576,
													"nobody" => 1.38 * 1048576													
												)
							)
					);
//weitere Definitionen fuer spezielle Veranstaltungstypen koennen hier angefuegt werden. Bitte Struktur wie oben exakt uebernehmen.


//Festlegen von zulaessigen Bezeichnungen fuer Einrichtungen (=Institute)
$INST_TYPE[1]=array("name"=>_("Einrichtung"));
$INST_TYPE[2]=array("name"=>_("Zentrum"));
$INST_TYPE[3]=array("name"=>_("Lehrstuhl"));
$INST_TYPE[4]=array("name"=>_("Abteilung"));
$INST_TYPE[5]=array("name"=>_("Fachbereich"));
$INST_TYPE[6]=array("name"=>_("Seminar"));
$INST_TYPE[7]=array("name"=>_("Fakult�t"));
$INST_TYPE[8]=array("name"=>_("Arbeitsgruppe"));
//weitere Typen koennen hier angefuegt werden


//define the presets of statusgroups for Veranstaltungen (refers to the key of the $SEM_CLASS array)
$SEM_STATUS_GROUPS["default"] = array ("DozentInnen", "TutorInnen", "AutorInnen", "LeserInnen", "sonstige"); 	//the default. Don't delete this entry!
$SEM_STATUS_GROUPS["2"] = array ("Organisatoren", "Mitglieder", "Ausschu�mitglieder", "sonstige");
$SEM_STATUS_GROUPS["3"] = array ("Moderatoren des Forums","Mitglieder", "sonstige");
//you can add more specific presets for the different classes 


//define the presets of statusgroups for Einrichtungen (refers to the key of the $INST_TYPE array)
$INST_STATUS_GROUPS["default"] = array ("DirektorIn", "HochschullehrerIn", "Lehrbeauftragte", "Zweitmitglied", "wiss. Hilfskraft","wiss. MitarbeiterIn",
									"stud. Hilfskraft", "Frauenbeauftragte", "Internetbeauftragte(r)", "StudentIn", "techn. MitarbeiterIn", "Sekretariat / Verwaltung", 
									"stud. VertreterIn");
//you can add more specific presets for the different types 


//define the used modules for instiutes
$INST_MODULES["default"] = array(
			"forum"=>TRUE,				//forum, this module is stud_ip core; always available
			"documents"=>TRUE,			//documents, this module is stud_ip core; always available
			"personal"=>TRUE,			//personal, this module is stud_ip core; always available 
			"literature"=>TRUE,			//literature, this module is stud_ip core; always available 
			"scm"=>FALSE,				//simple content module, this modul is stud_ip core; always available 
			"ilias_connect"=>FALSE,			//Ilias-connect, only, if the modul is global activated; see local.inc
			"chat"=>TRUE,				//chat, only, if the module is global activated; see local.inc
			"support"=>FALSE,			//support, only, if the module is global activated; see local.inc (this modul is not part of the main distribution)
			"wiki"=>FALSE,				//wikiwiki-web, this module is stud_ip core; always available 
			);
//you can add more specific presets for the different types 			


//Festlegen der Veranstaltungs Termin Typen
$TERMIN_TYP[1]=array("name"=>_("Sitzung"), "sitzung"=>1, "color"=>"#2D2C64"); 		//dieser Termin Typ wird immer als Seminarsitzung verwendet und im Ablaufplan entsprechend markiert. Der Titel kann veraendert werden, Eintraege aus dem Seminar Assistenten und Terminverwaltung fuer Seminar-Sitzungsterrmine bekommen jedoch immer diesen Typ
$TERMIN_TYP[2]=array("name"=>_("Vorbesprechung"), "sitzung"=>0, "color"=>"#5C2D64"); 	//dieser Termin Typ wird immer als Vorbesprechung verwendet. Der Titel kann veraendert werden, Eintraege aus dem Seminar Assistenten fuer Vorbesprechungen bekommen jedoch immer diesen Typ
$TERMIN_TYP[3]=array("name"=>_("Klausur"), "sitzung"=>0, "color"=>"#526416");
$TERMIN_TYP[4]=array("name"=>_("Exkursion"), "sitzung"=>0, "color"=>"#505064");
$TERMIN_TYP[5]=array("name"=>_("anderer Termin"), "sitzung"=>0, "color"=>"#41643F");
$TERMIN_TYP[6]=array("name"=>_("Sondersitzung"), "sitzung"=>0, "color"=>"#64372C");
$TERMIN_TYP[7]=array("name"=>_("Vorlesung"), "sitzung"=>1, "color"=>"#627C95");
//weitere Typen koennen hier angefuegt werden


// Festlegen der Kategorien f�r pers�nlichen Terminkalender
$PERS_TERMIN_KAT[1]=array("name"=>_("Sonstiges"), "color"=>"#41643F");
$PERS_TERMIN_KAT[2]=array("name"=>_("Sitzung"), "color"=>"#2D2C64");
$PERS_TERMIN_KAT[3]=array("name"=>_("Vorbesprechung"), "color"=>"#5C2D64");
$PERS_TERMIN_KAT[4]=array("name"=>_("Klausur"), "color"=>"#526416");
$PERS_TERMIN_KAT[5]=array("name"=>_("Exkursion"), "color"=>"#505064");
$PERS_TERMIN_KAT[6]=array("name"=>_("Sondersitzung"), "color"=>"#64372C");
$PERS_TERMIN_KAT[7]=array("name"=>_("Pr�fung"), "color"=>"#64541E");
$PERS_TERMIN_KAT[8]=array("name"=>_("Telefonat"), "color"=>"#48642B");
$PERS_TERMIN_KAT[9]=array("name"=>_("Besprechung"), "color"=>"#957C29");
$PERS_TERMIN_KAT[10]=array("name"=>_("Verabredung"), "color"=>"#956D42");
$PERS_TERMIN_KAT[11]=array("name"=>_("Geburtstag"), "color"=>"#66954F");
$PERS_TERMIN_KAT[12]=array("name"=>_("Familie"), "color"=>"#2C5964");
$PERS_TERMIN_KAT[13]=array("name"=>_("Urlaub"), "color"=>"#951408");
$PERS_TERMIN_KAT[14]=array("name"=>_("Reise"), "color"=>"#18645C");
$PERS_TERMIN_KAT[15]=array("name"=>_("Vorlesung"), "color"=>"#627C95");
// weitere Kategorien k�nnen hier angef�gt werden


//standardtimes for date-begin and date-end 
$TIME_PRESETS = array ( //starthour, startminute, endhour, endminute
		array ('07','45','09','15'), // 07:45 - 09:15
		array ('09','30','11','00'), // 09:30 - 11:00
		array ('11','15','12','45'), // 11:15 - 12:45
		array ('13','30','15','00'), // 13:30 - 15:00
		array ('15','15','16','45'), // 15:15 - 16:45
		array ('17','00','18','30'), // 17:00 - 18:30
		array ('18','45','20','15')  // 18:45 - 20:15
		);
//$TIME_PRESETS = false;


//number of personal events each user can store in his calendar
$CALENDAR_MAX_EVENTS = 1000;

//preset for titles, insert your own titles in the array
$TITLE_FRONT_TEMPLATE = array("","Prof.","Prof. Dr.","Dr.","PD Dr.","Dr. des.","Dr. med.","Dr. rer. nat.","Dr. forest.",
							"Dr. sc. agr.","Dipl.-Biol.","Dipl.-Chem.","Dipl.-Ing.","Dipl.-Sozw.","Dipl.-Geogr.",
							"Dipl.-Geol.","Dipl.-Geophys.","Dipl.-Ing. agr.","Dipl.-Kfm.","Dipl.-Math.","Dipl.-Phys.",
							"Dipl.-Psych.","M. Sc","B. Sc");
$TITLE_REAR_TEMPLATE = array("","M.A.","B.A.","M.S.","MBA","Ph.D.","Dipl.-Biol.","Dipl.-Chem.","Dipl.-Ing.","Dipl.-Sozw.","Dipl.-Geogr.",
							"Dipl.-Geol.","Dipl.-Geophys.","Dipl.-Ing. agr.","Dipl.-Kfm.","Dipl.-Math.","Dipl.-Phys.",
							"Dipl.-Psych.","M. Sc","B. Sc");

$NAME_FORMAT_DESC['full'] = _("Titel1 Vorname Nachname Titel2");
$NAME_FORMAT_DESC['full_rev'] = _("Nachname, Vorname, Titel1, Titel2");
$NAME_FORMAT_DESC['no_title'] = _("Vorname Nachname");
$NAME_FORMAT_DESC['no_title_rev'] = _("Nachname, Vorname");
$NAME_FORMAT_DESC['no_title_short'] = _("Nachname, V.");
$NAME_FORMAT_DESC['no_title_motto'] = _("Vorname Nachname, Motto");

//preset names for scm (simple content module)
$SCM_PRESET[1] = array("name"=>_("Informationen"));		//the first entry is the default label for scms, it'll be used if the user give no information for another label
$SCM_PRESET[2] = array("name"=>_("Literatur"));
$SCM_PRESET[3] = array("name"=>_("Links"));
$SCM_PRESET[4] = array("name"=>_("Verschiedenes"));
//you can add more presets here

//preset template for formatting of literature list entries
$LIT_LIST_FORMAT_TEMPLATE = "**{dc_creator}** |({dc_contributor})||\n"
						. "{dc_title}||\n"
						. "{dc_identifier}||\n"
						. "%%{published}%%||\n"
						. "{note}||\n"
						. "[{lit_plugin}]{external_link}|\n";

//Shorts for Smiley
$SMILE_SHORT = array( //diese Kuerzel fuegen das angegebene Smiley ein (Dateiname + ".gif")
	":)"=>"smile" , 
	":-)"=>"asmile" , 
	":#:"=>"zwinker" , 
	":("=>"frown" , 
	":o"=>"redface" , 
	":D"=>"biggrin", 
	";-)"=>"wink");
	
//Shorts for symbols
$SYMBOL_SHORT = array( //use this shorts to insert an symbols (filename + ".gif") 
	"=)"=>"symbol03" , 
	"(="=>"symbol04" , 
	"(c)"=>"symbol05" , 
	"(r)"=>"symbol06" , 
	" tm "=>"symbol08");


/*configuration for additional modules
----------------------------------------------------------------
this options are only needed, if you are using the addional Stud.IP modules (please see in local.inc
which modules are activated). It's a good idea to leave this settings untouched...*/


// <<-- EXPORT-EINSTELLUNGEN
// Ausgabemodi f�r den Export
$export_o_modes = array("start","file","choose", "direct","processor","passthrough");
// Exportierbare Datenarten
$export_ex_types = array("veranstaltung", "person", "forschung");

$skip_page_3 = true;
// Name der erzeugten XML-Datei
$xml_filename = "data.xml";
// Name der erzeugten Ausgabe-Datei
$xslt_filename = "studip";

// Vorhandene Ausgabeformate
$output_formats = array(
	"html"		=>		"Hypertext (HTML)", 
	"rtf"		=>		"Rich Text Format (RTF)", 
	"txt"		=>		"Text (TXT)", 
	"fo"		=>		"Adobe Postscript (PDF)", 
	"xml"		=>		"Extensible Markup Language (XML)"
);

// Icons f�r die Ausgabeformate
$export_icon["xml"] = "xls-icon.gif";
$export_icon["xslt"] = "xls-icon.gif";
$export_icon["xsl"] = "xls-icon.gif";
$export_icon["rtf"] = "rtf-icon.gif";
$export_icon["fo"] = "pdf-icon.gif";
$export_icon["pdf"] = "pdf-icon.gif";
$export_icon["html"] = "txt-icon.gif";
$export_icon["htm"] = "txt-icon.gif";
$export_icon["txt"] = "txt-icon.gif";
// weitere Icons und Formate k�nnen hier angef�gt werden

// PDF-Vorlagen f�r den Veranstaltungsexport (Index von 1 bis X)
// title = Beschreibung der Vorlage
// template = PDF-Vorlage in '/export'
$record_of_study_templates[1] = array("title" => "Allgemeine Druckvorlage", "template" =>"general_template.pdf");
$record_of_study_templates[2] = array("title" => "Studienbuch", "template" => "recordofstudy_template.pdf");
// EXPORT -->>



// <<-- LERNMODULE
// Zeichenkette, die vor Ilias-Usernamen gesetzt wird:
// IM LAUFENDEN BETRIEB NICHT MEHR �NDERN!!!
$username_prefix = "studip_";

// Zuordnung von Stud.IP-Status zu ILIAS-Status
// DEFAULT: 1 = Gast, 2 = Superuser, 3 = StudentIn, 4 = MitarbeiterIn
$ilias_status = array(
"user" => "1",
"autor" => "3",
"tutor" => "3",
"dozent" => "4",
"admin" => "2",
"root" => "2",
);

// Zuordnung von Stud.IP-Status zu ILIAS-System-Gruppe
// DEFAULT: 1 = AdministratorIn, 2 = AutorIn, 3 = LernerIn, 4 = Gast
$ilias_systemgroup = array(
"user" => "4",
"autor" => "2",
"tutor" => "2",
"dozent" => "2",
"admin" => "1",
"root" => "1",
);
// LERNMODULE -->>
?>
