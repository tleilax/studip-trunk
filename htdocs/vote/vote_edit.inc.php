<?php

/**
 * The page to create/edit votes ... vote_edit.inc.php
 *
 * @author     Michael Cohrs <michael@cohrs.de>
 * @version    $Id$
 * @copyright  2003 Stud.IP-Project
 * @access     public
 * @module     vote_edit
 * @package    vote
 *
 */

// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+

/* -------------------------------------------------------- */
define( "MODE_CREATE", 0 );
define( "MODE_MODIFY", 1 );
define( "MODE_RESTRICTED", 2 );

define( "TITLE_HELPTEXT",
	($auth->auth["jscript"])
	? _("Geben Sie hier einen Titel ein (optional)")
	: _("Geben Sie hier einen Titel ein") );

define( "QUESTION_HELPTEXT",
	_("Geben Sie hier Ihre Frage ein") );
/* -------------------------------------------------------- */

include_once ($ABSOLUTE_PATH_STUDIP . "seminar_open.php"); // initialise Stud.IP-Session

ob_start(); // start output buffering

require_once ($ABSOLUTE_PATH_STUDIP . "html_head.inc.php"); // Output of html head
require_once ($ABSOLUTE_PATH_STUDIP . "header.php");   // Output of Stud.IP head
require_once ($ABSOLUTE_PATH_STUDIP . "links_admin.inc.php");  //Linkleiste fuer admins

include_once ($ABSOLUTE_PATH_STUDIP . "vote/Vote.class.php");
include_once ($ABSOLUTE_PATH_STUDIP . "vote/TestVote.class.php");
include_once ($ABSOLUTE_PATH_STUDIP . "vote/view/vote_edit.lib.php");
include_once ($ABSOLUTE_PATH_STUDIP . "vote/view/visual.inc.php");

global $auth, $perm;

/* If there is no rights to edit ------------------------------------------- */
if ($voteID) {
   $vote = new Vote ($voteID);
   $rangeID = $vote->getRangeID ();
}
if (!($perm->have_studip_perm ("tutor", $rangeID) OR
      get_username($userID) == $rangeID)
    ) {

    $reason = ( ! is_object($vote)
		? _("Es macht wenig Sinn, die Editierseite aufzurufen, ohne den zu editierenden Vote anzugeben...")
		: ( ! $vote->voteDB->isExistant($voteID)
		    ? _("Angegebener Vote existiert nicht (mehr?) ...")
		    : ($vote->instanceof() == INSTANCEOF_TEST
		       ? sprintf(_("Sie haben keine Berechtigung den Test '%s' zu editieren."), $vote->getTitle())
		       : sprintf(_("Sie haben keine Berechtigung das Voting '%s' zu editieren."), $vote->getTitle())
		       )
		    )
		);

    echo "<br>";
    parse_window( "error�" .
		  _("Zugriff verweigert.").
		  "<br /><font size=-1 color=black>".
		  $reason.
		  "</font>",
		  "�", _("Zugriff auf Editierseite verweigert"), 
		  "<br />&nbsp;"
		  );
    
    page_close ();
    exit;
}
/* ------------------------------------------------------------------------- */

/*******************************************************************/
/******************** initialization *******************************/

// get and memorize the url, where we came from
$referer = $_POST['referer'];
if( ! $referer ) {
    $referer = $_SERVER['HTTP_REFERER'];
    $referer = removeArgFromURL( $referer, "voteaction" );
    $referer = removeArgFromURL( $referer, "voteID" );
    $referer = removeArgFromURL( $referer, "showrangeID" );
    if( $_POST['rangeID'] )
	$referer .= "&showrangeID=".$_POST['rangeID'];
    elseif( $_REQUEST["showrangeID"] )
	$referer .= "&showrangeID=".$showrangeID;
}

$voteID = $_POST['voteID'];    if( ! $voteID ) $voteID = $_GET['voteID'];
$rangeID = $_POST['rangeID'];  if( ! $rangeID ) $rangeID = $_GET['rangeID'];
$type = $_POST['type'];        if( ! $type ) $type = $_GET['type'];
if( ! $type ) $type = "vote";
$makeACopy = $_GET['makecopy'];

if ($type=="test") { $vote = &new TestVote( $voteID ); }
else               { $vote = &new Vote    ( $voteID ); }

if( $voteID && !$makeACopy ) {
    if( $vote->isInUse( $voteID ) )  # && ! $perm->have_perm ("root") )
	$pageMode = MODE_RESTRICTED;
    else
	$pageMode = MODE_MODIFY;
} else {
    $pageMode = MODE_CREATE;
}

$debug.="referer: $referer\n";
$debug.="pagemode: $pageMode\n";

$vote->finalize(); // Um erstmal den ErrorHandler wieder zur�ckzusetzen

// perm check? vote owner?

$answers           = $_POST['answers'];
$title             = $_POST['title'] != TITLE_HELPTEXT ? $_POST['title'] : NULL;
$question          = $_POST['question'] != QUESTION_HELPTEXT ? $_POST['question'] : NULL;
$startMode         = $_POST['startMode'];
$startDay          = $_POST['startDay'];
$startMonth        = $_POST['startMonth'];
$startYear         = $_POST['startYear'];
$startHour         = $_POST['startHour'];
$startMinute       = $_POST['startMinute'];
if( $startDay )    $startDate = $vote->date2timestamp( $startDay, $startMonth, $startYear, $startHour, $startMinute );
$stopMode          = $_POST['stopMode'];
$stopDay           = $_POST['stopDay'];
$stopMonth         = $_POST['stopMonth'];
$stopYear          = $_POST['stopYear'];
$stopHour          = $_POST['stopHour'];
$stopMinute        = $_POST['stopMinute'];
if( $stopDay )     $stopDate = $vote->date2timestamp( $stopDay, $stopMonth, $stopYear, $stopHour, $stopMinute );
$timeSpan          = $_POST['timeSpan'];
$multipleChoice    = $_POST['multipleChoice'];
$resultVisibility  = $_POST['resultVisibility'];
$co_visibility     = $_POST['co_visibility'];
$anonymous         = $_POST['anonymous'];
$changeable        = $_POST['changeable'];

if( !isset( $answers ) ) {
    $answers = $vote->getAnswers();
    if( $makeACopy ) {
	for( $i=0; $i<count($answers); $i++ ) {
	    $answers[$i]['answer_id'] = md5(uniqid(rand()));
	    $answers[$i]['counter']   = 0;
	}
    }
}
	
if( empty( $answers ) ) {
    if( !isset( $addAnswersButton_x ) && !isset( $saveButton_x ) && !isset( $deleteAnswersButton_x ) ) {
	for( $i=0; $i<5; $i++ )
	    $answers[$i] = makeNewAnswer();
    } else
	$answers = array();
}

if( !isset( $title ) )           { $title = $vote->getTitle(); if( $makeACopy ) $title .= _(" (Kopie)"); }
if( !isset( $question ) )          $question = $vote->getQuestion();
if( !isset( $startDay ) )          $startDate = $vote->getStartDate();
if( !isset( $stopDay ) )           $stopDate = $vote->getStopDate();
if( !isset( $timeSpan ) )          $timeSpan = $vote->getTimeSpan();
if( !isset( $multipleChoice ) )    $multipleChoice = $vote->isMultipleChoice();
if( !isset( $resultVisibility ) )  $resultVisibility = $vote->getResultVisibility();
if( !isset( $anonymous ) )         $anonymous = $vote->isAnonymous();
if( !isset( $changeable ) )        $changeable = $vote->isChangeable();
if( $type == "test" ) {
    if( !isset( $co_visibility ) ) $co_visibility = $vote->getCo_Visibility();
}
if( !isset( $startMode ) ) {
    if( $startDate && $pageMode != MODE_CREATE )
	$startMode = "timeBased";
    elseif( $pageMode != MODE_CREATE )
	$startMode = "manual";
}
if( !isset( $stopMode ) ) {
    if( $stopDate )
	$stopMode = "timeBased";
    elseif ( $timeSpan )
	$stopMode = "timeSpanBased";
    else
	$stopMode = "manual";
}
if( !isset( $voteID ) )   $voteID = $vote->getVoteID();
if( !isset( $rangeID ) )  $rangeID = $vote->getRangeID();

// special case: creator wants to modify things in a running vote,
// but in the meantime the first user has voted...
if( $pageMode == MODE_RESTRICTED && !empty( $_POST["question"]) )
     $vote->throwError(666, _("Inzwischen hat jemand abgestimmt! Sie k&ouml;nnen daher die meisten ".
			      "&Auml;nderungen nicht mehr vornehmen."), __LINE__, __FILE__);

/*******************************************************************/
/******************** page commands ********************************/

if( $pageMode != MODE_RESTRICTED ) {

    /**** Command: add Answers ****/
    if( isset( $addAnswersButton_x ) ) {
	for( $i=0; $i<$newAnswerFields; $i++ )
	    array_push( $answers, makeNewAnswer() );
    }
    
    /**** Command: move Answers ****/
    elseif( isset( $move_up ) ) {
	for( $i=0; $i<count($answers); $i++ )
	    if( isset( $move_up[$i] ) )
		moveAnswerUp( &$answers, $i );
    }
    elseif( isset( $move_down ) ) {
	for( $i=0; $i<count($answers); $i++ )
	    if( isset( $move_down[$i] ) )
		moveAnswerDown( &$answers, $i );
    }
    
    /**** Command: delete Answers ****/
    elseif( isset( $deleteAnswersButton_x ) ) {
	for( $i=0; $i<count($answers); $i++ ) {
	    if( $deleteAnswers[$i] == "on" ) {
		deleteAnswer( $i, &$answers, &$deleteAnswers );
		$i--;
	    }
	}
    }
}


/**** Command: SAVE VOTE ****/
/* -------------------------------------------------------- */
if( isset( $saveButton_x ) ) {

    $vote->setTitle( $title );
    /* -------------------------------------------------------- */

    if( $pageMode != MODE_RESTRICTED ) {
	/* -------------------------------------------------------- */
	$vote->setQuestion( $question );
    
	// remove any empty answers
	for( $i=0; $i<count($answers); $i++ ) {
	    if( empty( $answers[$i]['text'] ) ) {
		deleteAnswer( $i, &$answers, &$deleteAnswers );
		$i--;
	    }
	}
	/* -------------------------------------------------------- */
	$vote->setAnswers( $answers );
	/* -------------------------------------------------------- */
	switch( $startMode ) {
	case "manual":
	    $vote->setStartDate( NULL );
	    break;
	case "timeBased":
	    $vote->setStartDate( $startDate );
	    break;
	case "immediate":
	    $vote->setStartDate( time()-1 );
	    break;
	}
	/* -------------------------------------------------------- */
	$vote->setMultipleChoice( $multipleChoice );
	$vote->setAnonymous( $anonymous );
	if( $type == "test" ) $vote->setCo_Visibility( $co_visibility );
	/* -------------------------------------------------------- */
	if( $pageMode == MODE_CREATE ) {
	    $vote->setRangeID( $rangeID );
	    $vote->setAuthorID( $auth->auth["uid"] );
	}
	/* -------------------------------------------------------- */

    }

    // other values to be written in ANY pageMode...
    /* -------------------------------------------------------- */
    switch( $stopMode  ) {
    case "manual":
	$vote->setStopDate( NULL );
	$vote->setTimeSpan( NULL );
	break;
    case "timeBased":
	$vote->setTimeSpan( NULL );
	$vote->setStopDate( $stopDate );
	break;
    case "timeSpanBased":
	$vote->setStopDate( NULL );
	$vote->setTimeSpan( $timeSpan );
	break;
    }
    /* -------------------------------------------------------- */
    $vote->setResultVisibility( $resultVisibility );
    if( isset($changeable) ) $vote->setChangeable( $changeable );
    /* -------------------------------------------------------- */
    // now all values are set...

    if( $pageMode != MODE_CREATE ) {
	if( $vote->getAuthorID() != $auth->auth["uid"] ) {
	    // user's vote has been modified by admin/root
	    // --> send notification sms
	    $sms = new messaging();
	    $sms->insert_sms( $vote->voteDB->getAuthorUsername($vote->getAuthorID()),
			      mysql_escape_string( sprintf( _("An Ihrem %s \"%s\" wurden von dem Administrator oder der ".
							      "Administratorin %s �nderungen vorgenommen."),
							    ($vote->instanceof() == INSTANCEOF_TEST
							    ? _("Test") : _("Voting")), $vote->getTitle(),
							    $vote->voteDB->getAuthorRealname($auth->auth["uid"]) ) ),
			      "____%system%____" );
	}
    }

    if( ! $vote->isError() ) {
	// save vote to database!
	$vote->executeWrite();

	if ( ! $vote->isError() ) {

	    // clear outbut buffer, as we are leaving the edit page
	    ob_end_clean();
	    $referer .= ( ! strpos( $referer, "?" ) ) ? "?" : "&";
	    $referer .= "voteaction=".($pageMode == MODE_CREATE ? "created" : "saved");
	    $referer .= "&voteID=".$vote->getVoteID();
	    header( "Location: ".$referer );
	}
    }
    else {
	// Errors occured!
	// They will be automatically printed by 'printFormStart'
	// and the form will be displayed again...
    }
}

/**** Command: cancel ****/
elseif( isset( $cancelButton_x ) ) {

    // clear outbut buffer, as we are leaving the edit page.
    ob_end_clean();
    $referer .= ( ! strpos( $referer, "?" ) ) ? "?" : "&";
    $referer .= "voteID=".$vote->getVoteID();
    header( "Location: ".$referer );
}


// end output buffering, we are still on the edit page...
ob_end_flush(); 

/*******************************************************************/
/************************ output calls *****************************/

printJSfunctions();

printFormStart( $voteID, $rangeID, $referer );

printTitleField( $title );

printQuestionField( $question );

printAnswerFields( $answers );

printRightRegion();

printRuntimeSettings( $startMode, $stopMode, $startDate, $stopDate, $timeSpan );

printProperties( $multipleChoice, $resultVisibility, $co_visibility, $anonymous, $changeable );

printFormEnd();


/*******************************************************************/
/******************** internal functions ***************************/

/**
 * creates a new answer
 *
 * @access  private
 * @return  array    the created answer as an array with keys 'answer_id' => new md5 id,
 *                                                            'text' => "",
 *                                                            'counter' => 0,
 *                                                            'correct' => NO
 */

function makeNewAnswer( ) {

    return array( 'answer_id' => md5(uniqid(rand())),
		  'text'      => "",
		  'counter'   => 0,
		  'correct'   => NO
		  );
}

/**
 * moves the answer at position 'pos' from the array 'answers' one field up
 *
 * @access  private
 * @param   array  &$answers    the answerarray
 * @param   int    $pos         the position of the answer to be moved
 *
 */

function moveAnswerUp( &$answers, $pos ) {

    if( $pos == 0 ) {
	$temp = $answers[0];
	unset( $answers[0] );

	// move all other answers a field up
	for( $i=0; $i<count($answers); $i++ ) {
	    $answers[$i] = $answers[$i+1];
	    unset( $answers[$i+1] );
	}
	$answers[count($answers)] = $temp;
    }

    else {
	$temp = $answers[$pos-1];
	$answers[$pos-1] = $answers[$pos];
	$answers[$pos] = $temp;
    }
    return;
}

/**
 * moves the answer at position 'pos' from the array 'answers' one field down
 *
 * @access  private
 * @param   array  &$answers    the answerarray
 * @param   int    $pos         the position of the answer to be moved
 *
 */

function moveAnswerDown( &$answers, $pos ) {

    $last = count($answers)-1;
    if( $pos == $last ) {
	$temp = $answers[$last];
	unset( $answers[$last] );

	// move all other answers a field down
	for( $i=$last; $i>0; $i-- ) {
	    $answers[$i] = $answers[$i-1];
	    unset( $answers[$i-1] );
	}
	$answers[0] = $temp;
    }

    else {
	$temp = $answers[$pos+1];
	$answers[$pos+1] = $answers[$pos];
	$answers[$pos] = $temp;
    }
    return;
}

/**
 * deletes the answer at position 'pos' from the array 'answers'
 * and modifies the array 'deleteAnswers' respectively
 *
 * @access  public
 * @param   array  &$answers        the answerarray
 * @param   array  &$deleteAnswers  the array containing the deleteCheckbox-bool-value for each answer
 * @param   int    $pos             the position of the answer to be deleted
 *
 */

function deleteAnswer( $pos, &$answers, &$deleteAnswers ) {
     
    unset( $answers[$pos] );
    if( is_array( $deleteAnswers ) )
	unset( $deleteAnswers[$pos] );

    for( $i=$pos; $i<count($answers); $i++ ) {
	 
	if( !isset( $answers[$i] ) ) {
	    $answers[$i] = $answers[$i+1];
	    unset( $answers[$i+1] );
	    if( is_array( $deleteAnswers ) ) {
		$deleteAnswers[$i] = $deleteAnswers[$i+1];
		unset( $deleteAnswers[$i+1] );
	    }
	}
    }
    return;
}
 

/**
 * deletes argument '&arg=value' from URL
 *
 * @access  public
 * @param   string $URL    the URL to be modified
 * @param   string $arg    the name of the argument
 * @returns string         the new URL
 *
 */

function removeArgFromURL( $URL, $arg ) {
    $pos = strpos( $URL, "$arg=" );

    if( $pos ) {
	if( $URL[$pos-1] == "&" ) {
	    // If pos-1 is pointing to a '&', knock pos back one, so it is removed.
	    $pos--;
	}
	$nMax = strlen( $URL );
	$nEndPos = strpos( $URL, "&", $pos+1 );

	if( $nEndPos === false ) {
	    // $arg is on the end of the URL
	    $URL = substr( $URL, 0, $pos );
	}
	else {
	    // $arg is in the URL
	    $URL = str_replace( substr( $URL, $pos, $nEndPos-$pos ), '', $URL );
	}
    }
    return $URL;
}
?>
