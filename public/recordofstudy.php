<?php
# Lifter002: TODO
/**

 * Creates a record of study and exports the data to pdf

 *

 * @author      Christian Bauer <alfredhitchcock@gmx.net>

 * @version     $Id$

 * @copyright   2003 Stud.IP-Project

 * @access      public

 * @module      recordofstudy

 */

/* ************************************************************************** *
/*
/* the structure of the pdf-template:
/*
/*		-- form 'university'		// the name of the university
/*		-- form 'fieldofstudy'		// the field of study
/*		-- form 'studentname'		// the complete name of the student
/*		-- form 'semester'			// the semester
/*		-- form 'semesternumber'	// the semester number
/*
/*         (X := 0 -> last entry)/*
/*		-- form 'seminarnumber.X'	// the number of the seminar
/*		-- form 'tutor.X'			// the complete tutor name
/*		-- form 'sws.X'				// the average hours per semester
/*		-- form 'description.X'		// the name (+ discription) of the seminar
/* 																			  *
/* ************************************************************************* */

/* ************************************************************************** *
/*																			  *
/* initialise Stud.IP-Session												  *
/*																			  *
/* ************************************************************************* */
page_open (array ("sess" => "Seminar_Session", "auth" => "Seminar_Auth",
		  "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check ("autor");
include ('lib/seminar_open.php');
require_once('config.inc.php');
require_once('lib/datei.inc.php');

{
// needed session-variables
$sess->register("seminars");
$sess->register("semestersAR");
$sess->register("template");
}

/* **END*of*initialise*Stud.IP-Session*********************************** */

/* ************************************************************************** *
/*																			  *
/* including needed files													  *
/*																			  *
/* ************************************************************************* */

include_once($PATH_EXPORT ."/recordofstudy.lib.php");
include_once($PATH_EXPORT ."/recordofstudyDB.php");
/* **END*of*initialize*post/get*variables*********************************** */

/* ************************************************************************** *
/*																			  *
/* identify the current site-mode  											  *
/*																			  *
/* ************************************************************************* */
$semester = $_POST['semester'];
if(	(isset($_POST["semester_selected_x"])) || (isset($_POST["add_seminars_x"])) ||
	(isset($_POST["delete_seminars_x"])))
	$mode = "edit";
elseif (isset($_POST["create_pdf_x"]))
	$mode = "pdf_assortment";
elseif (isset($_GET["create_pdf"]))
	$mode = "create_pdf";
else
	$mode = "new";

/* **END*of*identify*the*current*site-mode*********************************** */


/* ************************************************************************** *
/*																			  *
/* collecting the data  													  *
/*																			  *
/* ************************************************************************* */
$infobox = createInfoxboxArray($mode);

if ($mode == "new"){
	// collect the current seminars and concerning semesters from the archiv
	$semestersAR = getSemesters();
}
elseif ($mode == "edit"){
	global $UNI_NAME;

	// get the basic data
	if ($_POST['template']){
		$template = $_POST['template'];
	};

	$university = htmlReady(stripslashes($_POST['university']));
	if (empty($university)) $university = $UNI_NAME;
	$fieldofstudy = htmlReady(stripslashes($_POST['fieldofstudy']));
	if (empty($fieldofstudy)) $fieldofstudy = getFieldOfStudy();
	$studentname = htmlReady(stripslashes($_POST['studentname']));
	if (empty($studentname)) $studentname = getStudentname();
	$semesterid = htmlReady(stripslashes($_POST['semesterid']));
	$semester = htmlReady(stripslashes($_POST['semester']));
	if (empty($semester))
		$semester = $semestersAR[$semesterid]["name"];
	$semesternumber = htmlReady($_POST['semesternumber']);

	$basicdata = array(
		"university"	=> $university,
		"fieldofstudy"	=> $fieldofstudy,
		"studentname"	=> $studentname,
		"semester"		=> $semester,
		"semesternumber"=> $semesternumber
	);

	// get the seminars from the db
	if ($semester = $_POST['semester_selected_x']){
		$seminareAR = getSeminare($semesterid,$_POST['onlyseminars']);
	}
	// get the seminars from post
	else{
		$seminare_max = $_POST['seminare_max'];
		$deletenumbers = 0;
		for($i=0;$i+1<=$seminare_max;$i++){

			// delete this entry
			if(($_POST['delete'.$i]) &&
			  (!($_POST['add_seminars_x']) && (($_POST['delete'.$i])))){
				$deletenumbers++;
			}
			else{
				// adding this one to the current seminas-array
				$seminarnumber = htmlReady(stripslashes($_POST['seminarnumber'.$i]));
				$tutor = htmlReady(stripslashes($_POST['tutor'.$i]));
				$sws = htmlReady(stripslashes($_POST['sws'.$i]));
				$description = htmlReady(stripslashes($_POST['description'.$i]));

				$seminareAR[$i-$deletenumbers] = array(
					"id" 			=> $i,
					"seminarid" 	=> $seminarid,
					"seminarnumber" => $seminarnumber,
					"tutor" 		=> $tutor,
					"sws"			=> $sws,
					"description" 	=> $description
				);
			}
		}
	}

	// this is the new max of seminar_fields
	$seminars_max = $i;

	// add new ones
	if(($_POST['add_seminars_x']) && (!($_POST['delete'.$i]))){
		$numberofnew = $_POST['newseminarfields'];
		for($i=1;$i<=$numberofnew;$i++){
			$seminareAR[$i+$seminare_max] = array("id" => $i+$seminars_max);
		}

	}
}
elseif($mode == "pdf_assortment"){

	// the last entry
	$seminare_max = $_POST['seminare_max'];

	// the basic data
	$university = stripslashes($_POST['university']);
	$fieldofstudy = stripslashes($_POST['fieldofstudy']);
	$studentname = stripslashes($_POST['studentname']);
	$semester = stripslashes($_POST['semester']);
	$semesternumber = stripslashes($_POST['semesternumber']);
	$seminars = array (
		"university" => $university,
		"fieldofstudy" => $fieldofstudy,
		"studentname" => $studentname,
		"semester" => $semester,
		"semesternumber" => $semesternumber
	);
	// creating the seminare-arrays cut into ones with the size of 10
	$runner = 10;
	// $j is the current page
	for($j=0;$j<=$seminare_max/10;$j++){
		// $runner notices the last entry
		if ($j+1>$seminare_max/10)
			$runner = $seminare_max%10;
		// $i is the current page-entry (0-9)
		for($i=0;$i+1<=$runner;$i++){
				// $y is the running nummber from 0 -> last seminar
				$y = $i+($j*10);
				$seminars[$j][$i]["seminarnumber"] = stripslashes($_POST['seminarnumber'.$y]);
				$seminars[$j][$i]["tutor"] = stripslashes($_POST['tutor'.$y]);
				$seminars[$j][$i]["sws"] = stripslashes($_POST['sws'.$y]);
				$seminars[$j][$i]["description"] = stripslashes($_POST['description'.$y]);
		}
	}
	$exemptions = array (10,20,30,40,50,60,70,80,90,100);
	if (in_array($seminare_max,$exemptions))
		$j--;
	$seminars["numberofseminars"] = $seminare_max;
	$seminars["numberofpages"] = $j;
}
elseif($mode == 'create_pdf'){
	$pdf_file['full_path'] = $ABSOLUTE_URI_STUDIP . sprintf('sendfile.php?type=3&file_id=%1$s&file_name=%1$s', $record_of_study_templates[$template]['template']);
	$fdfAR = createFdfAR($seminars);
};

/* **END*of*collecting*the*data********************************************* */

/* ************************************************************************** *
/*																			  *
/* displays the site	  													  *
/*																			  *
/* ************************************************************************* */
$CURRENT_PAGE = _("Veranstaltungs�bersicht erstellen");
ob_start();
if ($mode == "new"){
	printSelectSemester($infobox,$semestersAR);
}
elseif ($mode == "edit"){
	$CURRENT_PAGE .= ': ' . $basicdata["semester"];

	// display a notice for the user?
	if (sizeof($seminareAR) > 10)
		$notice = "above_limit";
	elseif (sizeof($seminareAR) < 1)
		$notice = "empty";

	printRecordOfStudies($infobox, $basicdata, $seminareAR, $notice);
}
elseif ($mode == "pdf_assortment"){
	$CURRENT_PAGE .= ': ' . $seminars["semester"];
	printPdfAssortment($infobox, $seminars);
}
elseif ($mode == "create_pdf"){
	ob_end_clean();
	$out = printPDF($pdf_file ,$fdfAR);
}

// if you wanna create a pdf no html-header should be send to the browser
if (!isset($_GET["create_pdf"])){
	$out = ob_get_clean();
	require_once('lib/include/html_head.inc.php');
	require_once('lib/include/header.php');
	require_once('lib/include/links_seminare.inc.php');
}
echo $out;
page_close ();
/* **END*of*displays*the*site*********************************************** */


/* ************************************************************************** *
/*																			  *
/* private functions														  *
/*																			  *
/* ************************************************************************* */

/**
 * creates an array with the data to fill the pdf
 *
 * @access  private
 * @param   string $seminars	the seminars
 * @returns array				an array with the data for the pdf
 *
 */
function createFdfAR($seminars){

	$page = $_GET['page']-1;
	$university = $seminars["university"];
	$fieldofstudy = $seminars["fieldofstudy"];
	$studentname = $seminars["studentname"];
	$semester = $seminars["semester"];
	$semesternumber = $seminars["semesternumber"];

	$fdfAR = array (
		"university" => $university,
		"fieldofstudy" => $fieldofstudy,
		"studentname" => $studentname,
		"semester" => $semester,
		"semesternumber" => $semesternumber
	);

	for($i=0;$i+1<=10;$i++){
			$fdfAR["seminarnumber.".$i] = (string)$seminars[$page][$i]["seminarnumber"];
			$fdfAR["tutor.".$i] = (string)$seminars[$page][$i]["tutor"];
			$fdfAR["sws.".$i] = (string)$seminars[$page][$i]["sws"];
			$fdfAR["description.".$i] = (string)$seminars[$page][$i]["description"];
	}
	return $fdfAR;
}

/**
 * creates a fdf and sends it to the browser
 *
 * @access  private
 * @param   string $pdf_file	the URL of the pdf-template
 * @param   array $pdf_data		the key and values to send
 *
 */
 function printPDF ($pdf_file, $pdf_data) {
	 $fdf = "%FDF-1.2\n%����\n";
	 $fdf .= "1 0 obj \n<< /FDF ";
	 $fdf .= "<< /Fields [\n";
	 foreach ($pdf_data as $key => $value){
		 $key = addcslashes($key, "\n\r\t\\()");
		 $value = addcslashes($value, "\n\r\t\\()");
		 $fdf .= "<< /T ($key) /V ($value) >> \n";
	 }
	 $fdf .= "]\n/F (".$pdf_file["full_path"].") >>";
	 $fdf .= ">>\nendobj\ntrailer\n<<\n";
	 $fdf .= "/Root 1 0 R \n\n>>\n";
	 $fdf .= "%%EOF";
	 // Now we display the FDF data which causes Acrobat to start
	 header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
	 header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
	 header("Pragma: public");
	 header("Cache-Control: private");
	 header("Content-Type: application/vnd.fdf");
	 header("Content-disposition: attachment; filename=\"".md5(uniqid('fdf',1)).".fdf\"");
	 header("Content-Length: " . strlen($fdf));
	 return $fdf;
 }

/**
 * replaces the semester token
 *
 * @access  public
 * @param   string $semname	a semestertitle (exampl: 'SS 2003')
 * @returns string         	the full semestertitle
 *
 */
function convertSemester($semname){
	global $SEMESTER;

	if ($semname[0].$semname[1] == "WS")
		return str_replace("WS", _("Wintersemester"),$semname);
	elseif ($semname[0].$semname[1] == "SS")
		return str_replace("SS", _("Sommersemester"),$semname);
	else
		return $semname;
}

/**
 * creates an array which conntains infobox labels
 *
 * @access  private
 * @param   string $mode	the current site-mode
 * @returns array         	an array with infobox labels
 *
 */
function createInfoxboxArray($mode){
	if ($mode == "new"){
		$infobox = array	(
			array ("kategorie"  => "Information:",
				"eintrag" => array	(
						array	 (	"icon" => "ausruf_small.gif",
								"text"  => _("Um eine Druckansicht Ihrer Veranstaltungen zu erstellen, w�hlen Sie bitte zun�chst das entsprechende Semester aus und engen gegebenenfalls ihre Suchabfrage ein.")
								),
						)
			),
		);
	}
	elseif ($mode == "edit") {
		$infobox = array(
			array  ("kategorie"  => "Information:",
					"eintrag" =>	array (
							array (	"icon" => "ausruf_small.gif",
									"text"  => _("Erstellen Sie ihre Veranstaltungs�bersicht und bearbeiten Sie fehlende oder falsche Eintr�ge.")
									),
									)
			),
			array  ("kategorie" => "Aktionen:",
					"eintrag" => array(
						array (	"icon" => "trash.gif",
								"text"  => _("Entfernen Sie nicht ben�tigte Veranstaltungen mit Hilfe der Markierungsboxen und/oder f�gen Sie  beliebig viele neue Veranstallungen hinzu.")
								),
						array (	"icon" => "icon-disc.gif",
								"text"  => _("Nachdem alle Informationen korrekt angezeigt werden, erstellen Sie Ihre Veranstaltungs�bersicht mit Hilfe des Buttons 'speichern'.")
								),
								)
			),
		);
	}
	elseif ($mode == "pdf_assortment"){
		$infobox = array(
			array  ("kategorie"  => "Information:",
					"eintrag" =>	array (
							array (	"icon" => "icon-posting.gif",
									"text"  => _("�ber den/die Link(s) k�nnen Sie sich Ihre Veranstaltungs�bersicht anzeigen lassen.")
									),
									)
			)
		);
	};

	return $infobox;
}
?>