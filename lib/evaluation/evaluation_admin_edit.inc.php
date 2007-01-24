<?php
/**
 * Beschreibung
 *
 * @author      Christian Bauer <alfredhitchcock@gmx.net>
 * @version     $Id$
 * @copyright   2004 Stud.IP-Project
 * @access      public
 * @package     evaluation
 * @modulegroup evaluation_modules
 *
 */

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Copyright (C) 2001-2004 Stud.IP
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

# PHP-LIB: open session ===================================================== #
/*page_open (array ("sess" => "Seminar_Session", 
		  "auth" => "Seminar_Auth",
		  "perm" => "Seminar_Perm", 
		  "user" => "Seminar_User"));
$auth->login_if ($auth->auth["uid"] == "nobody");
$perm->check ("autor");*/
# ============================================================== end: PHP-LIB #

# Include all required files ================================================ #

#require_once ('lib/seminar_open.php');
#require_once ('lib/include/html_head.inc.php');
#require_once ('lib/include/header.php');
#require_once ('lib/include/links_admin.inc.php');

require_once ('lib/evaluation/evaluation.config.php');
require_once (EVAL_LIB_EDIT);
require_once (EVAL_FILE_EDIT_TREEVIEW);

# ====================================================== end: including files #

# define constancs ========================================================== #

/**
 * @const NEW_EVALUATION_TITLE  title of a new question block
 * @access public
 */
define ("NEW_EVALUATION_TITLE", _("Neue Evaluation"));

/**
 * @const FIRST_ARRANGMENT_BLOCK_TITLE  title of a new arrangment block
 * @access public
 */
define ("FIRST_ARRANGMENT_BLOCK_TITLE", _("Erster Gruppierungsblock"));

# ====================================================== end: define constancs #

$debug = "<pre class=\"steelgroup6\" style=\"font-size:10pt\">"
	. "<pre class=\"steelgroup3\" style=\"font-size:10pt\"><font color=\"purple\">"
	. "Welcome to BugReport 1.02 "
	. "<font align=\"right\" style=\"font-size:7pt\">[Sharewareversion]</font>"
	. "</font></pre>";

# check the evalID ========================================================= #

global $user;

if ($_REQUEST["newButton_x"]){
	$debug .= "neue Eval!<br>";	
	// create the first group
	$group = &new EvaluationGroup();
	$group->setTitle(FIRST_ARRANGMENT_BLOCK_TITLE, QUOTED);
	$group->setText("");
	if ($group->isError ())
		return EvalCommon::showErrorReport ($group, _("Fehler beim Anlegen einer Gruppe"));
	
	// create a new eval
	$eval = new Evaluation ();
	
	$rangeID = $_REQUEST["rangeID"];
	if ($rangeID == get_username ($user->id))
		$rangeID = $user->id;

	$eval->setAuthorID ($user->id);
	$eval->setTitle (NEW_EVALUATION_TITLE);
	$eval->setAnonymous (YES);
	$evalID = $eval->getObjectID();
	$eval->addChild ($group);
	$eval->save ();
	
	if ($eval->isError ())
		return EvalCommon::showErrorReport ($eval, _("Fehler beim Anlegen einer Evaluation"));
			
	$groupID = $group->getObjectID();
	$evalID = $eval->getObjectID();

} elseif (isset($_REQUEST["evalID"]) && ($_REQUEST["evalID"] != NULL)) {	
	$debug .= "isset _REQUTEST[evalID]!<br>";
	$evalID = $_REQUEST["evalID"];
	$eval = new Evaluation ($evalID, NULL, EVAL_LOAD_NO_CHILDREN);
	if ($eval->isError ()) {
		$error = EvalCommon::createReportMessage (
			_("Es wurde eine ung�ltige Evaluations-ID �bergeben."),
			EVAL_PIC_ERROR, EVAL_CSS_ERROR);
		$error_msgs[] = $error->createContent();
	} elseif ($evalID == NULL) {
		$error = EvalCommon::createReportMessage (
			_("Es wurde keine Evaluations-ID �bergeben"),
			EVAL_PIC_ERROR, EVAL_CSS_ERROR);
		$error_msgs[] = $error->createContent();
	}
	
} elseif (isset($evalID)) {

	$debug .= "isset [evalID]!<br>";
	// evalID known as sess. variable
	if (($evalID == NULL) || ($evalID == "") || ($evalID == "NULL")){
		$error = EvalCommon::createReportMessage (
			_("Es wurde keine Evaluations-ID �bergeben"),
			EVAL_PIC_ERROR, EVAL_CSS_ERROR);
		$error_msgs[] = $error->createContent();
	}
	
} else {

	$debug .= "keine evalID!<br>";
	$error = EvalCommon::createReportMessage (
		_("Es wurde keine Evaluations-ID �bergeben"),
		EVAL_PIC_ERROR, EVAL_CSS_ERROR);
	$error_msgs[] = $error->createContent();
}


$sess->register("evalID");

# ===================================================== END: check the evalID #

# check the itemID =========================================================  #

if (isset($_REQUEST['itemID'])) {
	$itemID = $_REQUEST['itemID'];
	$sess->register("itemID");
} elseif ($_REQUEST["newButton_x"]) {
	$itemID = "root";
}else {
	# $itemID = "root";
}

# ===================================================== END: check the itemID #

# check the rangeID ========================================================  #

if (isset($_REQUEST["rangeID"])) {
	$rangeID = $_REQUEST["rangeID"];
	$sess->register("rangeID");
}

# ==================================================== END: check the rangeID #

# EVTAU: employees of the vote-team against urlhacking ====================== #

$eval = &new Evaluation($evalID, NULL, EVAL_LOAD_NO_CHILDREN);

// someone has voted
if ($eval->hasVoted()){
	$error = EvalCommon::createReportMessage (
		_("An dieser Evaluation hat bereits jemand teilgenommen. Sie darf nicht mehr ver�ndert werden."),
		EVAL_PIC_ERROR, EVAL_CSS_ERROR);
	$error_msgs[] = $error->createContent();
}


// only the author or user with tutor perm in all evalRangeIDs should edit an eval
$authorID = $eval->getAuthorID();
$db = new EvaluationObjectDB();

if ( $authorID != $user->id ) {

   $no_permisson = 0;
    
   if ( is_array ($eval->getRangeIDs()) ){
	
	foreach ($eval->getRangeIDs() as $rangeID){

		$user_perm 	 = $db->getRangePerm ($rangeID, $user->id, YES);
		
		// every range with a lower perm than Tutor
		if ($user_perm < 7 )
			$no_permisson++;
	}

	if ($no_permisson > 0){
	
		if ( $no_permisson == 1 )
			$no_permisson_msg = _("Sie haben in einem Bereich, in welchem diese Evaluation h�ngt, nicht aussreichene Rechte, um diese Eval zu bearbeiten.");
		else
			$no_permisson_msg = sprintf (_("Sie haben in %s Bereichen, in denen diese Evaluation h�ngt, nicht aussreichene Rechte, um diese Eval zu bearbeiten."), $no_permisson);

		$error = EvalCommon::createReportMessage (
			$no_permisson_msg,
			EVAL_PIC_ERROR, EVAL_CSS_ERROR);
			$error_msgs[] = $error->createContent();
	
	}
   
   }
}




# ============================================ end: Collection post/get-vars #

# Print Error MSG and end Site ============================================= #

if ($error_msgs){

	$back_button = ("&nbsp;&nbsp;&nbsp;")
				   	. "<a href=\"admin_evaluation.php?page=overview&rangeID=$rangeID\">"
					. _("Zur Evaluations-Verwaltung")
					. "</a>";
	
	if ( is_array($error_msgs) ){

		foreach ($error_msgs as $error_msg)
			$errors .= $error_msg . "<br>";
	
	}
	
	echo EvalEdit::createSite ($errors . $back_button, " " );

	include_once ('lib/include/html_end.inc.php');
	page_close ();
	exit ();

}



# ======================================== end: Print Error MSG and end Site #

# Creating the Tree ======================================================== #
$EditTree = new EvaluationTreeEditView($itemID, $evalID);

# Send messages to the tree ================================================ #

if ( $_REQUEST["newButton_x"] ) {
	$EditTree->msg["root"] = "msg�"
		. _("Erstellen Sie nun eine Evaluation.<br> Der erste Gruppierungsblock ist bereits angelegt worden. Wenn sie ihn �ffnen, k�nnen sie dort weitere Gruppierungsbl�cke oder Fragenbl�cke anlegen.");
}

# ============================================ end: Send messages to the tree #

/* Get the title bar ------------------------------------------------------- */
$editTitle = EvalCommon::createTitle (_("Evaluation editieren"), EVAL_PIC_ICON);
/* --------------------------------- end: do first all actions for templates */

/* Do first all actions for templates -------------------------------------- */
$templateSite = include(EVAL_FILE_TEMPLATE);
/* --------------------------------- end: do first all actions for templates */


/* Next do all actions for evaluations ------------------------------------- */
$editSite = $EditTree->showEvalTree($itemID,1);
/* ------------------------------------- end: Do all actions for evaluations */

/* Create content ---------------------------------------------------------- */
echo $editTitle->createContent ();
echo EvalEdit::createSite ($editSite, $templateSite );
/* ----------------------------------------------------- end: create content */


// debug-infos
/*
$debug .= "\n<b><font color=\"##3366FF\">postVars</font></b>";
foreach ($_POST as $key=>$item ) {
	if (is_array($item)){
		$debug .= "\n->$key [Array]:";
		foreach ($item as $key2 => $item2){
			if (is_array($item2)){
				$debug .= "\n____->$key2 [Array]:";
				foreach ($item2 as $key3 => $item3){
					$debug .= "\n________$key3=>$item3";
				}
			}
			else
				$debug .= "\n____$key2=>$item2";
			}
	}
	else
		$debug .= "\n->$key=>$item";
}
$debug .= "\n<b><font color=\"##3366FF\">getVars</font></b>";
foreach ($_GET as $key=>$item ) {
	if (is_array($item)){
		$debug .= "\n->$key [Array]:";
		foreach ($item as $key2 => $item2){
			if (is_array($item2)){
				$debug .= "\n____->$key2 [Array]:";
				foreach ($item2 as $key3 => $item3){
					$debug .= "\n________$key3=>$item3";
				}
			}
			else
				$debug .= "\n____$key2=>$item2";
			}
	}
	else
		$debug .= "\n->$key=>$item";
}


$debug .= "</pre>";
#echo $debug;
*/
# PHP-LIB: close session ==================================================== #
include_once ('lib/include/html_end.inc.php');
//page_close ();
# ============================================================== end: PHP-LIB #
?>
