<?
# Lifter002: TODO
function themen_autoAssign() {
	global $sem, $cycle_id;

	$sem->autoAssignIssues($_REQUEST['themen'], $cycle_id);
}

function themen_changeChronoGroupedFilter() {
	global $chronoGroupedFilter;
	$chronoGroupedFilter = $_REQUEST['newFilter'];
}

function themen_chronoAutoAssign() {
	global $sem;
	
	$termine = getAllSortedSingleDates($sem);
	foreach ($termine as $singledate_id => $singledate) {	
		if (!$singledate->isExTermin() && !$singledate->getIssueIDs()) {
			if ($data = array_shift($_REQUEST['themen'])) {
				$singledate->addIssueID($data);
				$singledate->store();
			} else {
				break;
			}
		}
	}
}

function themen_open() {
	global $issue_open;
	
	$issue_open[$_REQUEST['open_close_id']] = true;
}

function themen_close() {
	global $issue_open;

	$issue_open[$_REQUEST['open_close_id']] = false;
	unset ($issue_open[$_REQUEST['open_close_id']]);
}

function themen_doAddIssue() {
	global $id, $sem;

	$issue = new Issue(array('seminar_id' => $id));
	$issue->setTitle($_REQUEST['theme_title']);
	$issue->setDescription($_REQUEST['theme_description']);
	$issue->setForum(($_REQUEST['forumFolder'] == 'on') ? TRUE : FALSE);
	$issue->setFile(($_REQUEST['fileFolder'] == 'on') ? TRUE : FALSE);
	$issue->store();
	$sem->addIssue($issue);
	$sem->createMessage(_("Folgendes Thema wurde hinzugef�gt:").'<br/><li>'.htmlReady($issue->toString()));
}

function themen_deleteIssueID() {  
	global $sem ;

	$termin = $sem->getSingleDate($_REQUEST['sd_id'], $_REQUEST['cycle_id']);
	$termin->deleteIssueID($_REQUEST['issue_id']);
}

function themen_changeIssue() {
	global $sem,$themen;

	$msg .= sprintf(_("Das Thema \"%s\" wurde ge�ndert."), htmlReady($themen[$_REQUEST['issue_id']]->toString())) . '<br/>';
	$themen[$_REQUEST['issue_id']]->setDescription($_REQUEST['theme_description']);	
	$themen[$_REQUEST['issue_id']]->setTitle($_REQUEST['theme_title']);	
	$themen[$_REQUEST['issue_id']]->setForum(($_REQUEST['forumFolder'] == 'on') ? TRUE : FALSE);
	$themen[$_REQUEST['issue_id']]->setFile(($_REQUEST['fileFolder'] == 'on') ? TRUE : FALSE);
	$themen[$_REQUEST['issue_id']]->store();
	if ($zw = $themen[$_REQUEST['issue_id']]->getMessages()) {
		foreach ($zw as $val) {
			$msg .= $val.'<br/>';
		}
	}
	$sem->createMessage($msg);
}

function themen_deleteIssue() {
	global $sem, $themen;

	$sem->createMessage(_("Folgendes Thema wurde gel�scht:").'<br/><li>'.htmlReady($themen[$_REQUEST['issue_id']]->toString()));
	$sem->deleteIssue($_REQUEST['issue_id']);
}

function themen_addIssue() {
	global $sem, $numIssues, $cmd, $id;

	if ($numIssues > 20) {		// for security reasons, it should not be possible to add thousands of issues at one time
		unset($cmd);
		unset($numIssues);
		break;
	}
	if ($numIssues > 1) {
		$sem->createMessage(sprintf(_("Es wurden %s Themen hinzugef�gt."), $numIssues));
		for ($i = 1; $i <= $numIssues; $i++) {
			$issue = new Issue(array('seminar_id' => $id));
			$issue->setTitle(_("Thema").' '.$i);
			$issue->store();
			$sem->addIssue($issue);
			unset($issue);
		}
		unset($cmd);
	}
}

function themen_changePriority() {
	global $sem,$themen;

	if ($themen[$_REQUEST['issueID']]->getPriority() > $_REQUEST['newPriority']) {
		$sem->createMessage(sprintf(_("Das Thema \"%s\" wurde um eine Position nach oben verschoben."), htmlReady($themen[$_REQUEST['issueID']]->toString())));
	} else {
		$sem->createMessage(sprintf(_("Das Thema \"%s\" wurde um eine Position nach unten verschoben."), htmlReady($themen[$_REQUEST['issueID']]->toString())));
	}
	$sem->changeIssuePriority($_REQUEST['issueID'], $_REQUEST['newPriority']);
}

function themen_openAll() {
	global $sem, $openAll;

	$sem->createInfo(_("Es wurden alle Themen ge�ffnet. Sie k�nnen diese nun unten bearbeiten."));
	$openAll = TRUE;
}

function themen_saveAll() {
	global $sem, $themen, $changeTitle, $changeFile, $changeForum, $changeDescription;

	$msg = _("Folgende Themen wurden bearbeitet:").'<br/>';
	foreach ($changeTitle as $key => $val) {	// we use the changeTitle-array for running through all themes ($key = issue_id and $val = title)
		$forumValue = ($changeForum[$key] == 'on') ? TRUE : FALSE;
		$fileValue = ($changeFile[$key] == 'on') ? TRUE : FALSE;
		if (	($themen[$key]->getTitle() != $val) || 
				($themen[$key]->getDescription() != $changeDescription[$key]) ||
				($themen[$key]->hasForum() != $forumValue) ||
				($themen[$key]->hasFile() != $fileValue)
			 ) {
			$msg .= '<li>'.htmlReady($themen[$key]->toString()).'<br/>';
		}
		$themen[$key]->setTitle($val);
		$themen[$key]->setDescription($changeDescription[$key]);
		$themen[$key]->setForum($forumValue);
		$themen[$key]->setFile($fileValue);
		$themen[$key]->store();
	}

	$msg .= '<br/>'._("Folgende weitere Aktionen wurde durchgef�hrt:").'<br/>';

	foreach ($themen as $val) {
		if ($zw = $val->getMessages()) {
			foreach ($zw as $iss_msg) {
				$msg .= '<li>'.$iss_msg.'<br/>';
			}
		}
	}
	$sem->createMessage($msg);
}

function themen_checkboxAction() {
	global $sem, $choosen;

	switch ($_REQUEST['checkboxAction']) {
		case 'chooseAll':
			break;

		case 'chooseNone':
			break;

		case 'invert':
			foreach ($_REQUEST['themen'] as $val) {
				$choosen[$val] = TRUE;
			}
			break;

		case 'deleteChoosen':
			if (!$_REQUEST['themen']) break;
			$msg = _("Folgende Themen wurden gel�scht:").'<br/>';
			foreach ($_REQUEST['themen'] as $val) {
				$thema =& $sem->getIssue($val);
				$msg .= '<li>'.htmlReady($thema->toString()).'<br/>';
				unset($thema);
				$sem->deleteIssue($val);
			}
			$sem->createMessage($msg);
			break;

		case 'deleteAll':
			if ($_REQUEST['approveDeleteAll'] != TRUE) {	// security-question
				$msg = _("Sind Sie sicher, dass Sie alle Themen l�schen m�chten?");
				$msg .= "<br/><a href=\"{$GLOBALS['PHP_SELF']}?cmd=checkboxAction&checkboxAction=deleteAll&approveDeleteAll=TRUE\">";
				$msg .= '<img '.makebutton('ja2', 'src').' border="0"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$msg .= "<a href=\"{$GLOBALS['PHP_SELF']}\">";
				$msg .= '<img '.makebutton('nein', 'src').' border="0"></a>';
				$sem->createInfo($msg);			// create an info
			} else {											// deletion approved, so we do the job
				$msg = _("Alle Themen wurden gel�scht.");
				$sem->createMessage($msg);	// create a message
				foreach ($sem->getIssues() as $id => $issue) {
					$sem->deleteIssue($id);
				}
			}
			break;

	}
}

?>
