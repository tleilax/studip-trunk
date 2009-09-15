<?php
/*basic settings for Stud.IP
----------------------------------------------------------------
you find here the basic system settings. You shouldn't have to touch much of them...
please note the CONFIG.INC.PHP in the system folder for the indivual settings of your installation!*/

$UNI_NAME_CLEAN = "Entwicklungssystem Thinkpad";							//the clean-name of your master-faculty (e.g. University of G�ttingen), without html-entities (used for mail-system)
$STUDIP_INSTALLATION_ID='unios';      //unique identifier for installation
$AUTH_LIFETIME = 0;									//the length of a session in minutes, zero means unlimited lifetime


/*settings for database access
----------------------------------------------------------------
please fill in your database connection settings.
please note: Stud.IP uses the class DB_Seminar,
DB_Ilias is used to connect to an Ilias Database*/

// default Stud.IP database (DB_Seminar)
$DB_STUDIP_HOST = "localhost";
$DB_STUDIP_USER = "root";
$DB_STUDIP_PASSWORD = "enigma";
$DB_STUDIP_DATABASE = "uos_19";

//additional class for Ilias connection (DB_Ilias)
$DB_ILIAS_HOST = "localhost";
$DB_ILIAS_USER = "<username>";
$DB_ILIAS_PASSWORD = "<password>";
$DB_ILIAS_DATABASE = "ilias";


#####    ##   ##### #    #  ####
#    #  #  #    #   #    # #
#    # #    #   #   ######  ####
#####  ######   #   #    #      #
#      #    #   #   #    # #    #
#      #    #   #   #    #  ####


// ABSOLUTE_PATH_STUDIP should end with a '/'
$ABSOLUTE_PATH_STUDIP = $STUDIP_BASE_PATH . '/public/';

$CANONICAL_RELATIVE_PATH_STUDIP = dirname($_SERVER['PHP_SELF']);

// CANONICAL_RELATIVE_PATH_STUDIP should end with a '/'
if (substr($CANONICAL_RELATIVE_PATH_STUDIP,-1) != "/"){
	$CANONICAL_RELATIVE_PATH_STUDIP .= "/";
}

// ABSOLUTE_URI_STUDIP: insert the absolute URL to your Stud.IP installation; it should end with a '/'
$ABSOLUTE_URI_STUDIP = "http://localhost/workspace/uos_19/public/";
// optional automagically computing ABSOLUTE_URI_STUDIP
//   Warning: this can only be enabled AFTER executing the migration scripts for Stud.IP 1.6
//   customize if required
//   change this, if MAIL_NOTIFICATION activated
/* $ABSOLUTE_URI_STUDIP = sprintf('http%s://%s%s%s',
     $_SERVER['SERVER_PORT'] == 443 || $_SERVER['HTTPS'] == 'on' ? 's' : '',
     $_SERVER['SERVER_NAME'],
     in_array($_SERVER['SERVER_PORT'], array(80, 443)) ? '' : ':' . $_SERVER['SERVER_PORT'],
     $CANONICAL_RELATIVE_PATH_STUDIP);
*/


// default ASSETS_URL, customize if required
$ASSETS_URL = $ABSOLUTE_URI_STUDIP . 'assets/';


// absolute filesystem path to the plugin packages
$PLUGINS_PATH = $ABSOLUTE_PATH_STUDIP . 'plugins_packages';


// path to uploaded documents (wwwrun needs write-perm there)
$UPLOAD_PATH = $STUDIP_BASE_PATH . "/data/upload_doc";
// path to Stud.IP archive (wwwrun needs write-perm there)
$ARCHIV_PATH = $STUDIP_BASE_PATH . "/data/archiv";
//path to store configs (wwwrun needs write-perm there)
$EXTERN_CONFIG_FILE_PATH =  $STUDIP_BASE_PATH . "/data/extern_config/";


// path and url for dynamically generated static content like smilies..
$DYNAMIC_CONTENT_PATH = $ABSOLUTE_PATH_STUDIP . "pictures";
$DYNAMIC_CONTENT_URL  = $ABSOLUTE_URI_STUDIP . "pictures";


//path to the temporary folder
$TMP_PATH ="/tmp";									//the system temp path

$SESSION_PATH = $STUDIP_BASE_PATH . '/data/session';
//paths to the command line tools, used in Stud.IP
$ZIP_USE_INTERNAL = false;								//set to true, if command-line zip/unzip is not available
$ZIP_PATH = "/usr/bin/zip";								//zip tool
$ZIP_OPTIONS = "";									//command line options for zip, e.g. when using SuSE try "-K" to correct long filenames for windows
$UNZIP_PATH = "/usr/bin/unzip";

//latexrender settings
$LATEX_PATH = "/usr/bin/latex";
$DVIPS_PATH = "/usr/bin/dvips";

//ImageMagick, used by latexrender modul and for resizing of pictures uploaded by users
//if the convert utility is not found, an attempt to use gd (must be > 2.01) is made
$CONVERT_PATH = "/usr/bin/convert";
$IDENTIFY_PATH = "/usr/bin/identify";

//path to Stud.IP modules (this folders only have to exist, if the corresponcing module is active)
$RELATIVE_PATH_RESOURCES = "lib/resources";							//Stud.IP module: resourge management
$RELATIVE_PATH_CALENDAR = "lib/calendar";							//Stud.IP module: calendar
$RELATIVE_PATH_CHAT = "lib/chat"; 								//Stud.IP module: chat
$RELATIVE_PATH_ADMIN_MODULES = "lib/admin";				 			//Stud.IP module: admin tools
$RELATIVE_PATH_EXTERN = "lib/extern"; 							//Stud.IP module: SRI-System for including Stud.IP data in other websites
$RELATIVE_PATH_LEARNINGMODULES = "lib/lernmodule";						//Stud.IP module: Ilias 2 lerningmodules-connection (deprecated)
$RELATIVE_PATH_ELEARNING_INTERFACE = "lib/elearning";					//Stud.IP module: Ilias 3 lerningmodules-connection / general E-Learning-interface
$RELATIVE_PATH_SOAP = "lib/soap";
$RELATIVE_PATH_SUPPORT = "lib/support";

$PATH_EXPORT = "lib/export";								//Stud.IP module: export

$LOG_PATH = '/tmp';

$FAVICON = "http://www.studip.de/favicon.ico";						//the place where the favicon is stored.

/* PATHS to RSS FEED
 *
 * This FEEDS are shown in the loginform
 * see below for example usage
 *
 */

$NEWS_FEEDS = array(
    array('name' => 'Neues aus Stud.IP',
        'url' => 'http://studip.serv.uni-osnabrueck.de/rss.php?id=70cefd1e80398bb20ff599636546cdff'),
    // array('name' => 'The Onion',
        // 'url' => 'http://feeds.theonion.com/theonion/daily'),
    array('name' => 'H�ufige Fragen',
        'url' => 'http://www.blogs.uni-osnabrueck.de/vikursma/feed/')
);

/*Stud.IP modules
----------------------------------------------------------------
enable or disable the Stud.IP internal modules, set and basic settings*/

$CALENDAR_ENABLE = TRUE;								//Stud.IP module: calendar
$CALENDAR_DRIVER = "MySQL"; 								//calendar driver: database to use (MySQL in default installation)*/

$CHAT_ENABLE = TRUE;									//Stud.IP module: chat
$CHAT_SERVER_NAME = "ChatPDOServer";

$EXPORT_ENABLE = TRUE;									//Stud.IP module: export
$XSLT_ENABLE = TRUE;
$FOP_ENABLE = TRUE;
$FOP_SH_CALL = "/usr/bin/fop";    					//path to fop
$JAVA_ENV_CALL = "/etc/profile.d/alljava.sh";     					//used to set environment for JRE

$ILIAS_CONNECT_ENABLE = FALSE;								//Stud.IP module: ilias 2 connect
$ABSOLUTE_PATH_ILIAS = "http://<your.server.name/ilias/>";

$EXTERN_ENABLE = TRUE;									//Stud.IP module: "external pages" and SRI-System
$EXTERN_SERVER_NAME = "";								//define name, if you use special setup
$EXTERN_SRI_ENABLE = TRUE;								//allow the usage of SRI-interface (Stud.IP Remote Include)
$EXTERN_SRI_ENABLE_BY_ROOT = FALSE;							//only root allows the usage of SRI-interface for specific institutes
$EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG = FALSE;						//free access to external pages (without the need of a configuration), independent of SRI settings above

$SUPPORT_ENABLE = FALSE;								//Stud.IP module: SupportDB (not part of the main distribution)

$VOTE_ENABLE = TRUE;									//Stud.IP module: Votingsystem

$ELEARNING_INTERFACE_ENABLE = TRUE;						//Stud.IP module: elearning interface
$SOAP_ENABLE = TRUE;

$WEBSERVICES_ENABLE = TRUE;							//Stud.IP module: webservices

$STUDIP_API_KEY=''; // webservice api key is set in config table

$WAP_ENABLE = FALSE;							//Stud.IP module: WAP

$STM_ENABLE = FALSE;			//Studienmodule (experimental)

$USER_VISIBILITY_CHECK = TRUE;

$SEMINAR_LOCK_ENABLE = TRUE;

$ELEARNING_INTERFACE_MODULES["pmwiki-farm-zentrum"] =	array(
		"name" => "Wikifarm Zentrum virtUOS",
		"ABSOLUTE_PATH_ELEARNINGMODULES" => "http://zentrum.virtuos.uos.de/wikifarm/fields/",
			// url to farm soap service
		"WEBSERVICE_CLASS" => "xml_rpc_webserviceclient",
		"ABSOLUTE_PATH_SOAP" => "http://zentrum.virtuos.uos.de/wikifarm/pmwiki.php",
		"URL_PARAMS" => "action=xmlrpc",
		"CLASS_PREFIX" => "PmWiki",
		"auth_necessary" => false,
		"field_script" => "field.php",
		"logo_file" => $ASSETS_URL."/images/pmwiki-32.gif",
		"soap_data" => array(
		"api-key" => "ClatupCul",
		"username" => "studip-webservice-client", 
				), 
		"types" => 	array(
			"wiki" => array("name" => "PmWiki-Lerneinheit", "icon" => 		
				$ASSETS_URL."/images/icon-lern.gif"),
			)
		);

$ELEARNING_INTERFACE_MODULES["lernmodul-wikis-zentrum"] = array(
		"name" => "Lernmodul-Wikis",
  		"ABSOLUTE_PATH_ELEARNINGMODULES" => "https://zentrum.virtuos.uos.de/wiki/",
  		"WEBSERVICE_CLASS" => "xml_rpc_webserviceclient",
  		"ABSOLUTE_PATH_SOAP" => "http://zentrum.virtuos.uos.de/wiki/admin_field/",
  		"URL_PARAMS" => "action=xmlrpc",
  		"CLASS_PREFIX" => "PmWiki",
  		"auth_necessary" => false,
  		"field_script" => "field.php",
  		"logo_file" => $ASSETS_URL."/images/pmwiki-32.gif",
  		"soap_data" => array(
   		   "api-key" => "DocNagdon2"
  		   ),
  		"types" =>  array(
    		  "wiki" => array("name" => "Lernmodul-Wiki",
    		  "icon" => $ASSETS_URL."/images/icon-lern.gif")
  		   )
		); 

$ELEARNING_INTERFACE_MODULES["ilias-impuls"] = array (
     		"name" => "ILIAS-IMPULS",
     		"ABSOLUTE_PATH_ELEARNINGMODULES" => "http://ilias.serv.uni-osnabrueck.de/",
     		"ABSOLUTE_PATH_SOAP" => "http://ilias.serv.uni-osnabrueck.de/webservice/soap/server.php?wsdl",
     		"CLASS_PREFIX" => "Ilias3",
     		"auth_necessary" => true,
     		"USER_PREFIX" => "${STUDIP_INSTALLATION_ID}_",
     		"target_file" => "studip_referrer.php",
     		"logo_file" => "assets/images/ilias_logo.png",
     		"soap_data" => array(
       		"username" => "studip_admin",   //this credentials are used to communicate with your Ilias 3 installation over SOAP
       		"password" => "...................",
       		"client" => "UNI-OS-IMPULS"),
     		"types" =>      array(
       		   "htlm" => array("name" => "HTML-Lerneinheit", 
		      "icon" => "assets/images/icon-lern.gif"),
       		   "sahs" => array("name" => "SCORM/AICC-Lerneinheit", 
		      "icon" => "assets/images/icon-lern.gif"),
       		   "lm" => array("name" => "ILIAS-Lerneinheit", 
		      "icon" => "assets/images/icon-lern.gif"),
       		   "tst" => array("name" => "ILIAS-Test", 
		      "icon" => "assets/images/icon-lern.gif")
     		   ),
     		"global_roles" => array(4,5,14), // put here the ilias role-ids for User, Guest and Anonymous
     		"roles" =>      array(
       		   "autor" => "4",
       		   "tutor" => "4",
        	   "dozent" => "4",
       		   "admin" => "4",
       	 	   "root" => "2"
     		   ),
     		"crs_roles" =>  array(
       		   "autor" => "member",
       		   "tutor" => "tutor",
       		   "dozent" => "admin",
       		   "admin" => "admin",
       		   "root" => "admin"
     		   )
   		);

$ELEARNING_INTERFACE_MODULES["ilias-hannover"] = array (
    		"name" => "ILIAS Universit�t Hannover",
    		"ABSOLUTE_PATH_ELEARNINGMODULES" => "http://www.ilias.uni-hannover.de/",
    		"ABSOLUTE_PATH_SOAP" => "http://www.ilias.uni-hannover.de/webservice/soap/server.php?wsdl",
    		"CLASS_PREFIX" => "Ilias3",
    		"auth_necessary" => true,
    		"USER_PREFIX" => "unios_",
    		"target_file" => "studip_referrer.php",
    		"logo_file" => "assets/images/ilias_logo.png",
    		"soap_data" => array(
      		"username" => "studip_admin_uos", 
      		"password" => "...............",
      		"client" => "client01"),
    		"types" =>      array(
      		"htlm" => array("name" => "HTML-Lerneinheit", "icon" => "assets/images/icon-lern.gif"),
      		"sahs" => array("name" => "SCORM/AICC-Lerneinheit", "icon" => "assets/images/icon-lern.gif"),
      		"lm" => array("name" => "ILIAS-Lerneinheit", "icon" => "assets/images/icon-lern.gif"),
      		"tst" => array("name" => "ILIAS-Test", "icon" => "assets/images/icon-lern.gif")
    		),
    		"global_roles" => array(4,5,14), 
    		"roles" =>      array(
      		    "autor" => "4",
      		    "tutor" => "4",
      		    "dozent" => "4",
      		    "admin" => "4",
      		    "root" => "2"
    		    ),
    		"crs_roles" =>  array(
      		    "autor" => "member",
      		    "tutor" => "tutor",
      		    "dozent" => "admin",
      		    "admin" => "admin",
      		    "root" => "admin"
    		    )
  		); 


$PLUGINS_ENABLE = TRUE;
$PLUGINS_UPLOAD_ENABLE = TRUE; 					//Upload of Plugins is enabled
			     								//if disabled for security reasons, uploads have to go into $NEW_PLUGINS_PATH
$NEW_PLUGINS_PATH = $ABSOLUTE_PATH_STUDIP . 'plugins_packages'; 							//The place from which new plugins should be loaded
$PLUGINS_CACHING = TRUE;  						//enable caching
$PLUGINS_CACHE_TIME = 300; 						//Time in seconds, the cache is valid


/*system functions
----------------------------------------------------------------
activate or deactivate some basic system-functions here*/

$LATEXRENDER_ENABLE = TRUE;								//enable to use the LaTexrenderer (Please note the further LaTeX template-settings below)
$WIKI_ENABLE = TRUE;									//enable WikiWiki-Webs
$SCM_ENABLE = TRUE;									//enable Simple-Content functionality
$LOG_ENABLE = TRUE;									//enable event logging for some admin actions on courses, users and institutes
$SMILEYADMIN_ENABLE = TRUE;								//enable Smiley-administration
$SMILEY_COUNTER = FALSE;								//enable Smiley-counter


/*domain name and path translation
----------------------------------------------------------------
to translate internal links (within Stud.IP) to the different
domain names. To activate this feature uncomment these lines
and add all used domain names. Below, some examples are given.
*/

//server-root is stud.ip root dir, or virtual server for stud.ip
//$STUDIP_DOMAINS[1] = "<your.server.name>";
//$STUDIP_DOMAINS[2] = "<your.server.ip>";
//$STUDIP_DOMAINS[3] = "<your.virtual.server.name>";
//
// or
//
//stud.ip root is a normal directory
//$STUDIP_DOMAINS[1] = "<your.server.name/studip>";
//$STUDIP_DOMAINS[2] = "<your.server.ip/studip>";

$STUDIP_DOMAINS[1] = 'studip.rz.uos.de';
$STUDIP_DOMAINS[2] = 'studip.rz.uni-osnabrueck.de';
$STUDIP_DOMAINS[3] = 'studip.serv.uos.de';
$STUDIP_DOMAINS[4] = 'studip.serv.uni-osnabrueck.de';


/*mail settings
----------------------------------------------------------------
leave blank if localhost is also the mailserver*/

$MAIL_LOCALHOST = "";									//name of the mail sending machine (the web server) defaults to SERVER_NAME
$MAIL_HOST_NAME = "";									//which mailserver should we use? (must allow mail-relaying from $MAIL_LOCALHOST, defaults to SERVER_NAME)
$MAIL_CHARSET = "";									//character set of mail body, defaults to ISO-8859-1
$MAIL_ENV_FROM = "";									//sender mail adress, defaults to wwwrun @ $MAIL_LOCAHOST
$MAIL_FROM = "";									//name of sender, defaults to "Stud.IP"
$MAIL_ABUSE = "";									//mail adress to reply to in case of abuse, defaults to abuse @  $MAIL_LOCAHOST

$MAIL_VALIDATE_HOST = TRUE;								//check for valid mail host when user enters email adress
$MAIL_VALIDATE_BOX = TRUE;								//check for valid mail account when user enters email adress; set to false if the webserver got no valid MX record

$MESSAGING_FORWARD_AS_EMAIL = TRUE;							//enable to forward every internal message to the user-mail (the user is able to deactivate this function in his personal settings)
$MESSAGING_FORWARD_DEFAULT = 2;								//the default setting: if 1, the user has to switch it on; if 2, every message will be forwarded; if 3 every message will be forwarded on request of the sender

$ENABLE_EMAIL_TO_STATUSGROUP = TRUE;								// enable to send messages to whole status groups

/*advanced system settings
----------------------------------------------------------------
this are some settings to activate some special features, special
behaviour of some features and other advanced options. Change on your
own risk :) */

$ALLOW_GROUPING_SEMINARS = TRUE;							//if true, administrators can group seminars - students
											//will only be able to register for one of the grouped seminars

$ALLOW_SELFASSIGN_STUDYCOURSE = FALSE ; 							//if true, students are allowed to set or change
$ALLOW_SELFASSIGN_INSTITUTE = TRUE ; 							//if true, students are allowed to set or change
											//their studycourse (studiengang)

$SHOW_TERMS_ON_FIRST_LOGIN = FALSE;							//if true, the user has to accept the terms on his first login
											//(this feature makes only sense, if you use disable $ENABLE_SELF_REGISTRATION).

$BANNER_ADS_ENABLE = TRUE; 								//enable the Banner ads functions (config as root in "global settings")
											//you'll need an additional folder in the pictures folder named banner. The Webserver
											//needs write accees for this folder.

$CONVERT_IDNA_URL = TRUE;								//if true, urls with german "umlauts" are converted

/*language settings
----------------------------------------------------------------*/

$INSTALLED_LANGUAGES["de_DE"] = array ("path"=>"de", "picture"=>"lang_de.gif", "name"=>"Deutsch");
$INSTALLED_LANGUAGES["en_GB"] =	array ("path"=>"en", "picture"=>"lang_en.gif", "name"=>"English");

$DEFAULT_LANGUAGE = "de_DE";  // which language should we use if we can gather no information from user?

$_language_domain = "studip";  // the name of the language file. Should not be changed except in cases of individual translations or special terms.

/*literature search plugins
----------------------------------------------------------------
If you write your own plugin put it in studip-htdocs/lib/classes/lit_search_plugins
and enable it here. The name of the plugin is the classname excluding "StudipLitSearchPlugin".
If the catalog your plugin is designed for offers the possibility to create a link to an entry, you
could provide the link here. Place templates for the needed attributes in curly braces. (see examples below)*/

//standard plugin, searches in Stud.IP Database (table lit_catalog), you should leave this one enabled !
$_lit_search_plugins[] = array('name' => "Studip",'display_name' =>'Katalog der Stud.IP Datenbank', 'link' => '');

//Plugins derived from Z3950Abstract, used for querying Z39.50 Servers
//only activate these plugins, if your Version of PHP supports the YAZ extension!

/* Gemeinsamer Verbundkatalog - GVK */
//$_lit_search_plugins[] = array('name' => "Gvk",'display_name' =>'Gemeinsamer Verbundkatalog', 'link' => 'http://gso.gbv.de/DB=2.1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Nieders�chsische Staats- und Universit�tsbibliothek G�ttingen, OPAC */
//$_lit_search_plugins[] = array('name' => "SUBGoeOpac",'display_name' => "Opac der SUB G�ttingen" , 'link' => 'http://goopc4.sub.uni-goettingen.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* G�ttinger Gesamtkatalog (Regionalkatalog G�ttingen) */
//$_lit_search_plugins[] = array('name' => 'Rkgoe', 'link' => 'http://gso.gbv.de/DB=2.90/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliotheken der Wirtschafts- und Sozialwiss. Fakult�ten Goettingen" */
//$_lit_search_plugins[] = array('name' => 'WisoFak', 'link' => 'http://goopc4.sub.uni-goettingen.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Technische Informationsbibliothek / Universit�tsbibliothek Hannover, OPAC */
//$_lit_search_plugins[] = array('name' => 'TIBUBOpac', 'link' => 'http://opc4.tib.uni-hannover.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}', 'display_name' => "UB Katalog");

/* Hannover Gesamtkatalog (Regionalkatalog Hannover) */
//$_lit_search_plugins[] = array('name' => 'Rkhan', 'link' => 'http://gso.gbv.de/DB=2.92/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}', 'display_name' => "Gesamtkatalog Hannover");

/* Bibliotheken der Fachhochschule Hildesheim/Holzminden/G�ttingen */
//$_lit_search_plugins[] = array('name' => 'FHHIOpac', 'link' => 'http://hidbs2.bib.uni-hildesheim.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Th�ringer Universit�ts- und Landesbibliothek Jena */
//$_lit_search_plugins[] = array('name' => 'ThULB_Jena', 'link' => 'http://jenopc4.thulb.uni-jena.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Jena */
//$_lit_search_plugins[] = array('name' => 'FH_Jena', 'link' => 'http://jenopc4.thulb.uni-jena.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universit�tsbibliothek der Bauhaus-Universit�t Weimar */
//$_lit_search_plugins[] = array('name' => 'UB_Weimar', 'link' => 'http://weias.ub.uni-weimar.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Herzogin Anna Amalia Bibliothek Weimar */
//$_lit_search_plugins[] = array('name' => 'HAAB_Weimar', 'link' => 'http://weias.ub.uni-weimar.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Hochschule f�r Musik Franz Liszt Weimar */
//$_lit_search_plugins[] = array('name' => 'HSfMFL_Weimar', 'link' => 'http://weias.ub.uni-weimar.de:8080/DB=3/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universit�tsbibliothek Erfurt */
//$_lit_search_plugins[] = array('name' => 'UB_Erfurt', 'link' => 'http://opac.uni-erfurt.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Erfurt */
//$_lit_search_plugins[] = array('name' => 'FH_Erfurt', 'link' => 'http://opac.uni-erfurt.de:8080/DB=4/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Nordhausen */
//$_lit_search_plugins[] = array('name' => 'FH_Nordhausen', 'link' => 'http://opac.uni-erfurt.de:8080/DB=5/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universit�tsbibliothek Ilmenau */
//$_lit_search_plugins[] = array('name' => 'UB_Ilmenau', 'link' => 'http://ilmopc4.bibliothek.tu-ilmenau.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Bibliothek der Fachhochschule Schmalkalden */
//$_lit_search_plugins[] = array('name' => 'FH_Schmalkalden', 'link' => 'http://ilmopc4.bibliothek.tu-ilmenau.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universit�ts- und Landesbibliothek Sachsen-Anhalt Halle */
//$_lit_search_plugins[] = array('name' => "Ulb", 'link' => 'http://haweb1.bibliothek.uni-halle.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* FB Technik ULB Halle und FH Merseburg  */
//$_lit_search_plugins[] = array('name' => "FBTechnik", 'link' => 'http://haweb1.bibliothek.uni-halle.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Deutsche Akademie der Naturforscher Leopoldina */
//$_lit_search_plugins[] = array('name' => "Leopoldina", 'link' => 'http://haweb1.bibliothek.uni-halle.de:8080/DB=4/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/* Universit�tsbibliothek Trier */
//$_lit_search_plugins[] = array('name' => 'UB_Trier', 'display_name' =>'Universit�tsbibliothek Trier', 'link' => 'https://ub-aleph.uni-trier.de/F/?func=find-c&local_base=tri01&ccl_term={accession_number}');

/* IWF Campusmedien */
//$_lit_search_plugins[] = array('name' => "IWFdigiClips", 'display_name' => "IWF Campusmedien", 'link' => 'http://gso.gbv.de/DB=1.65/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

$_lit_search_plugins = array(
	array('name' => "Studip", 'link' => ''),
       	array('name' => "SubGoeOpac", 'link' => 'http://goopc4.sub.uni-goettingen.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}'),
        array('name' => "UniOsnabrueck", 'link' => 'http://osopc4.ub.uni-osnabrueck.de:8080/DB=1/LNG=DU/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}'),
        array('name' => "Rkgoe", 'link' => 'http://gso.gbv.de/DB=2.90/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}'),
        array('name' => "Gvk", 'link' => 'http://gso.gbv.de/DB=2.1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}'));

/*authentication plugins
----------------------------------------------------------------
the following plugins are available:
Standard 		authentication using the local Stud.IP database
StandardExtern  	authentication using an alternative Stud.IP database, e.g. another installation
Ldap  			authentication using an LDAP server, this plugin uses anonymous bind against LDAP to retrieve the user dn,
			then it uses the submitted password to authenticate with this user dn
LdapReader		authentication using an LDAP server, this plugin binds to the server using a given dn and a password,
			this account must have read access to gather the attributes for the user who tries to authenticate.
			Using this plugin allows to keep the md5 challenge-response mechanism during the login process, provided a md5
			hash of the user password is available in LDAP

If you write your own plugin put it in studip-htdocs/lib/classes/auth_plugins
and enable it here. The name of the plugin is the classname excluding "StudipAuth".

You could also place your configuration here, name it $STUDIP_AUTH_CONFIG_<plugin name>,
all uppercase each item of the configuration array will become a member of your plugin class.*/

//$STUDIP_AUTH_PLUGIN[] = "LdapReader";
//$STUDIP_AUTH_PLUGIN[] = "Ldap";
//$STUDIP_AUTH_PLUGIN[] = "StandardExtern";
$STUDIP_AUTH_PLUGIN[] = "Standard";
// $STUDIP_AUTH_PLUGIN[] = "LdapReadAndBind";
//$STUDIP_AUTH_PLUGIN[] = "LdapOS";
$STUDIP_AUTH_PLUGIN[] = "CAS";
//$STUDIP_AUTH_PLUGIN[] = "Shib";

$STUDIP_AUTH_CONFIG_LDAPREADANDBIND = array(
    "plugin_name" => "ldapos",
    "host" => "ldap.uni-osnabrueck.de",
    "base_dn" => "cn=people,dc=uni-osnabrueck,dc=de",
    "protocol_version" => 3,
    "start_tls" => false,
    "decode_utf8_values" => true,
    "username_attribute" => "uid",
    "reader_dn" => "cn=studip,cn=adm",
    "reader_password" => "z6Puo4eM",
    "user_data_mapping" => array(
        "auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
        "auth_user_md5.password" => array("callback" => "dummy", "map_args" => ""),
        "auth_user_md5.Nachname" => array("callback" => "doLdapMap", "map_args" => "sn"),
        "auth_user_md5.Vorname" => array("callback" => "doLdapMap", "map_args" => "givenname"),
//      "datafields_entries.6da14fb9d1c48d163a1530b1e5ca6eb4" => array("callback" => "doLdapMap", "map_args" => "uniosmatrikelnumber")
    )
);

/*
// create a config for your own user data mapping class
$CASAbstractUserDataMapping_CONFIG = array();
$STUDIP_AUTH_CONFIG_CAS = array("host" => "cas.studip.de",
										"port" => 8443,
										"uri"  => "cas",
										"user_data_mapping_class" => "CASAbstractUserDataMapping",
										"user_data_mapping" => // map_args are dependent on your own data mapping class
												array(  "auth_user_md5.username" => array("callback" => "getUserData", "map_args" => "username"),
						                                "auth_user_md5.Vorname" => array("callback" => "getUserData", "map_args" => "givenname"),
						                                "auth_user_md5.Nachname" => array("callback" => "getUserData", "map_args" => "surname"),
						                                "auth_user_md5.Email" => array("callback" => "getUserData", "map_args" => "email"),
						                                "auth_user_md5.perms" => array("callback" => "getUserData", "map_args" => "status")));

$STUDIP_AUTH_CONFIG_LDAPREADER = array(	"host" => "localhost",
										"base_dn" => "dc=studip,dc=de",
										"protocol_version" => 3,
										"start_tls" => false,
										"send_utf8_credentials" => false,
										"decode_utf8_values" => false,
										"bad_char_regex" => '/[^0-9_a-zA-Z]/',
										"username_case_insensitiv" => false,
										"username_attribute" => "uid",
										"user_password_attribute" => "userpassword",
										"reader_dn" => "uid=reader,dc=studip,dc=de",
										"reader_password" => "<password>",
										"error_head" =>	"LDAP reader plugin",
										"user_data_mapping" =>
										array(	"auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
												"auth_user_md5.password" => array("callback" => "dummy", "map_args" => "")));

$STUDIP_AUTH_CONFIG_LDAPREADANDBIND = array("host" => "localhost",
										"base_dn" => "dc=studip,dc=de",
										"protocol_version" => 3,
										"start_tls" => false,
										"send_utf8_credentials" => false,
										"decode_utf8_values" => false,
										"bad_char_regex" => '/[^0-9_a-zA-Z]/',
										"username_case_insensitiv" => false,
										"username_attribute" => "uid",
										"user_password_attribute" => "userpassword",
										"reader_dn" => "uid=reader,dc=studip,dc=de",
										"reader_password" => "<password>",
										"error_head" =>	"LDAP read-and-bind plugin",
										"user_data_mapping" =>
										array(	"auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
												"auth_user_md5.password" => array("callback" => "dummy", "map_args" => "")));

$STUDIP_AUTH_CONFIG_LDAP = array(		"host" => "localhost",
										"base_dn" => "dc=data-quest,dc=de",
										"protocol_version" => 3,
										"start_tls" => false,
										"send_utf8_credentials" => false,
										"decode_utf8_values" => false,
										"bad_char_regex" => '/[^0-9_a-zA-Z]/',
										"username_case_insensitiv" => false,
										"username_attribute" => "uid",
										"anonymous_bind" => true,
										"error_head" =>	"LDAP plugin",
										"user_data_mapping" =>
										array(	"auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
												"auth_user_md5.password" => array("callback" => "dummy", "map_args" => "")));

$STUDIP_AUTH_CONFIG_STANDARDEXTERN = array(	"db_host" => "localhost",
										"db_username" => "extern",
										"db_name" => "extern_studip",
										"db_password" => "<password>",
										"error_head" =>	"Stud.IP extern plugin",
										"user_data_mapping" =>
										array(	"auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
												"auth_user_md5.password" => array("callback" => "dummy", "map_args" => ""),
												"auth_user_md5.Email" => array("callback" => "doExternMap", "map_args" => "Email"),
												"auth_user_md5.Nachname" => array("callback" => "doExternMap", "map_args" => "Nachname"),
												"auth_user_md5.Vorname" => array("callback" => "doExternMap", "map_args" => "Vorname"),
												"auth_user_md5.perms" => array("callback" => "doExternMapPerms", "map_args" => "perms"),
												"user_info.privatnr" => array("callback" => "doExternMap", "map_args" => "privatnr"),
												"user_info.privadr" => array("callback" => "doExternMap", "map_args" => "privadr"),
												"user_info.geschlecht" => array("callback" => "doExternMap", "map_args" => "geschlecht"),
												"user_info.hobby" => array("callback" => "doExternMap", "map_args" => "hobby"),
												"user_info.lebenslauf" => array("callback" => "doExternMap", "map_args" => "lebenslauf"),
												"user_info.title_front" => array("callback" => "doExternMap", "map_args" => "title_front"),
												"user_info.title_rear" => array("callback" => "doExternMap", "map_args" => "title_rear"),
												"user_info.publi" => array("callback" => "doExternMap", "map_args" => "publi"),
												"user_info.schwerp" => array("callback" => "doExternMap", "map_args" => "schwerp"),
												"user_info.Home" => array("callback" => "doExternMap", "map_args" => "Home")));
*/
$STUDIP_AUTH_CONFIG_CAS = array(
	"plugin_name" => "ldapos",
	"host" => "cas.serv.uni-osnabrueck.de",
	"port" => 443,
	"uri" => "/cas",
	"user_data_mapping_class" => "CASUserDataMappingOS",
	"user_data_mapping" => array(
		"auth_user_md5.Nachname" => array("callback" => "getUserData", "map_args" => "sn"),
		"auth_user_md5.Vorname" => array("callback" => "getUserData", "map_args" => "givenname"),
//		"datafields_entries.6da14fb9d1c48d163a1530b1e5ca6eb4" => array("callback" => "getUserData", "map_args" => "uniosmatrikelnumber")
	)
);

$STUDIP_AUTH_CONFIG_SHIB = array(
        'plugin_name' => 'ldapos',
        // SessionInitator URL for lazy session
        'session_initiator' =>
                'https://sp.serv.uni-osnabrueck.de/proxy/studip-sp.php',
        // validation URL for remote SP
        'validate_url' =>
                'https://sp.serv.uni-osnabrueck.de/secure/studip-sp.php',
        // strip local domain name
        'local_domain' => 'uni-osnabrueck.de',
        // standard user data mapping
        'user_data_mapping' => array(
                'auth_user_md5.Vorname' =>
                        array('callback' => 'getUserData', 'map_args' => 'inetorgperson_givenname'),
                'auth_user_md5.Nachname' =>
                        array('callback' => 'getUserData', 'map_args' => 'inetorgperson_surname'),
                'auth_user_md5.Email' =>
                        array('callback' => 'getUserData', 'map_args' => 'inetorgperson_mail')
        )
);

$STUDIP_AUTH_CONFIG_LDAPOS = array(		
		"host" => "ldap.uni-osnabrueck.de",
		"base_dn" => "cn=people,dc=uni-osnabrueck,dc=de",
		"protocol_version" => 3,
                "start_tls" => false,
                "decode_utf8_values" => true,
                "bad_char_regex" => "/[^\\w-]/",
		"username_attribute" => "uid",
		"reader_dn" => "cn=studip,cn=adm",
		"reader_password" => "z6Puo4eM",
		"user_data_mapping" => array(
			"auth_user_md5.username" => array("callback" => "dummy", "map_args" => ""),
			"auth_user_md5.password" => array("callback" => "dummy", "map_args" => ""),
			"auth_user_md5.Nachname" => array("callback" => "doLdapMap", "map_args" => "sn"),
  	     		"auth_user_md5.Vorname" => array("callback" => "doLdapMap", "map_args" => "givenname"),
			      	"datafields_entries.6da14fb9d1c48d163a1530b1e5ca6eb4" =>
				array("callback" => "doLdapMap", "map_args" => "uniosmatrikelnumber")));


//some additional authification-settings
//NOTE: you MUST enable Standard authentication-plugin for this settings to take effect!

$ALLOW_CHANGE_USERNAME = FALSE;						//if true, users are allowed to change their username
$ALLOW_CHANGE_EMAIL = TRUE;							//if true, users are allowed to change their email-address
$ALLOW_CHANGE_NAME = FALSE;							//if true, users are allowed to change their name
$ALLOW_CHANGE_TITLE = TRUE;							//if true, users are allowed to change their titles
$ENABLE_SELF_REGISTRATION = FALSE;					//should it be possible for an user to register himself
$ENABLE_FREE_ACCESS = FALSE;							//if true, courses with public access are available

/*IDs of courses, in which users were entered when they are promoted to 'autor'
-----------------------------------------------------------------*/

//$AUTO_INSERT_SEM[1]="<seminar_id>";
$AUTO_INSERT_SEM[1]="0611ab8b5057e2a271fd51de0c75dc27";
//further courses can be added with their IDs here


/*format templates for LaTex renderer
-----------------------------------------------------------------
you can define specified templates, e.g. phonetic or arab fonts*/

$LATEX_FORMATS = array(
	"math" => array("tag" => "tex", "template" => "\documentclass[12pt]{article}\n \usepackage[latin1]{inputenc}\n \usepackage{amsmath}\n \usepackage{amsfonts}\n \usepackage{amssymb}\n \pagestyle{empty}\n \begin{document}\n $%s$\n \end{document}\n"));
/*
	Format of entries:
	------------------

	Internal format name => array (
		Format tag (e.g. "tex" --> [tex]...[/tex])
		Format template (must contain structure of an entire valid LaTeX-document and must contain exactly one %s placeholder that will be replaced be the code entered between [tag]....[/tag].
	)

  	Examples for additional formats:
  	--------------------------------

	IPA Phonetic font (needs LaTeX package tipa installed):
		"ipa" => array("tag" => "ipa", "template" => "\documentclass[12pt]{article}\n \usepackage[latin1]{inputenc}\n \usepackage{tipa}\n \pagestyle{empty}\n \begin{document}\n \\textipa{%s}\n \end{document}\n")

	Arab font (needs LaTeX package arabtex installed):
  		"arab" => array("tag" => "arab", "template" => "\documentclass[12pt]{article}\n \usepackage[latin1]{inputenc}\n \usepackage{arabtex,atrans}\n \pagestyle{empty}\n \begin{document}\n \begin{arabtext}%s\end{arabtext}\n \end{document}\n")


/*path generation
-----------------------------------------------------------------
(end of user defined settings)*/


//create the html-version of $UNI_NAME clean
$UNI_NAME=htmlentities($UNI_NAME_CLEAN, ENT_QUOTES);


require_once 'lib/phplib_local.inc.php';

//$Id: config_local.inc.php 6945 2006-11-12 14:30:12Z anoack $
?>
