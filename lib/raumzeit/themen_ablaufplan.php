<?php
/*
themen_ablaufplan.php: GUI for default-view of the theme managment
Copyright (C) 2005-2007 Till Gl�ggler <tgloeggl@uos.de>

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

// -- here you have to put initialisations for the current page
define('SELECTED', ' checked');
define('NOT_SELECTED', '');

$sess->register('issue_open');
$sess->register('raumzeitFilter');

$issue_open = array();

require_once ('lib/classes/Seminar.class.php');
require_once ('lib/raumzeit/raumzeit_functions.inc.php');
require_once ('lib/raumzeit/themen_ablaufplan.inc.php');

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
	$resList = new ResourcesUserRoomsList($user->id, TRUE, FALSE, TRUE);
}

// Start of Output
include ("lib/include/html_head.inc.php"); // Output of html head
include ("lib/include/header.php");   // Output of Stud.IP head
include ("lib/include/links_admin.inc.php");

$powerFeatures = TRUE;

$sem = new Seminar($id);
$sem->checkFilter();
$themen =& $sem->getIssues();
if (isset($_REQUEST['cmd'])) {
	$cmd = $_REQUEST['cmd'];
}

// Workaround for multiple submit buttons
foreach ($_REQUEST as $key => $val) {
	if ( ($key[strlen($key)-2] == '_') && ($key[strlen($key)-1] == 'x') ) {
		$cmd = substr($key, 0, (strlen($key) - 2));
	}
}

foreach ($_REQUEST as $key => $val) {
	if ($_REQUEST['allOpen']) {
		if (strstr($key, 'theme_title')) {
			$keys = explode('�', $key);
			$changeTitle[$keys[1]] = $val;
		}
		if (strstr($key, 'theme_description')) {
			$keys = explode('�', $key);
			$changeDescription[$keys[1]] = $val;
		}
		if (strstr($key, 'forumFolder')) {
			$keys = explode('�', $key);
			$changeForum[$keys[1]] = $val;
		}
		if (strstr($key, 'fileFolder')) {
			$keys = explode('�', $key);
			$changeFile[$keys[1]] = $val;
		}
	}
}

$sem->registerCommand('open', 'themen_open');
$sem->registerCommand('close', 'themen_close');
$sem->registerCommand('openAll', 'themen_openAll');
$sem->registerCommand('closeAll', 'themen_closeAll');
$sem->registerCommand('editAll', 'themen_saveAll');
$sem->registerCommand('editIssue', 'themen_changeIssue');
$sem->registerCommand('addIssue', 'themen_doAddIssue');
//$sem->registerCommand('checkboxAction', 'themen_checkboxAction');
$sem->processCommands();

unset($themen);
$themen =& $sem->getIssues();	// read again, so we have the actual sort order and so on
?>
<FORM action="<?=$PHP_SELF?>" method="post">
<TABLE width="100%" border="0" cellpadding="2" cellspacing="0">
	<TR>
		<TD colspan="2" class="topic">
			&nbsp; <B><?=getHeaderLine($id)." -  "._("Themen / Ablaufplan");?></B>
		</TD>
	</TR>
	<TR>
		<TD class="blank">
			<A name="filter">
			<?
				$all_semester = $semester->getAllSemesterData();
				$passed = false;
				foreach ($all_semester as $val) {
					if ($sem->getStartSemester() <= $val['vorles_beginn']) $passed = true;
					if ($passed && ($sem->getEndSemesterVorlesEnde() >= $val['vorles_ende'])) {
						$tpl['semester'][$val['beginn']] = $val['name'];
						if ($raumzeitFilter != ($val['beginn'])) {
						} else {
							$tpl['seleceted'] = $val['beginn'];
						}
					}
				}
				$tpl['selected'] = $raumzeitFilter;
				$tpl['semester']['all'] = _("Alle Semester");
				if ($sem->hasDatesOutOfDuration()) {
					$tpl['forceShowAll'] = TRUE;
					if ($raumzeitFilter != 'all') {
						$sem->createInfo(_("Es gibt weitere Termine, die au&szlig;erhalb der regul&auml;ren Laufzeit der Veranstaltung liegen.<br/> Um diese anzuzeigen w&auml;hlen Sie bitte \"Alle Semester\"!"));
					}
				} else {
					$tpl['forceShowAll'] = FALSE;
				}
				include('lib/raumzeit/templates/choose_filter.tpl');
			?>
		</TD>
		<TD class="blank" align="right">
		<?
			$tpl['view']['simple'] = 'Einfach';
			$tpl['view']['expert'] = 'Experte';
			$tpl['selected'] = $viewModeFilter;
			include('lib/raumzeit/templates/choose_view.tpl');
		?>
		</TD>
  </TR>
	<? while ($msg = $sem->getNextMessage()) { ?>
	<TR>
		<TD class="blank" colspan=2><br />
			<?parse_msg($msg);?>
		</TD>
	</TR>
	<? } ?>
	<TR>
		<TD class="blank" colspan="5" height="15"></TD>
	</TR>
  <TR>
		<TD align="center" class="blank" width="80%" valign="top">
			<TABLE width="99%" cellspacing="0" cellpadding="0" border="0">
				<TR>
					<TD class="steelgraulight" colspan="5" height="24" align="center">
						<A href="<?=$PHP_SELF?>?cmd=<?=($openAll) ? 'close' : 'open'?>All">
							<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/<?=($openAll) ? 'close' : 'open'?>_all.gif" border="0" <?=tooltip(sprintf("Alle Termine %sklappen", ($openAll) ? 'zu' : 'auf'))?>>
						</A>
					</TD>
				</TR>
				<TR>
					<TD class="blank" colspan="5" height="2"></TD>
				</TR>
				<?

				$termine = getAllSortedSingleDates($sem);

				foreach ($termine as $singledate_id => $singledate) {

					if ( ($grenze == 0) || ($grenze < $singledate->getStartTime()) ) {
						foreach ($all_semester as $zwsem) {
							if ( ($zwsem['beginn'] < $singledate->getStartTime()) && ($zwsem['ende'] > $singledate->getStartTime()) ) {
								$grenze = $zwsem['ende'];
								?>
								<TR>
									<TD class="steelgraulight" align="center" colspan="9">
										<FONT size="-1"><B><?=$zwsem['name']?></B></FONT>
									</TD>
								</TR>
								<?
							}
						}
					}

					// Template fuer einzelnes Datum
					$showSpecialDays = FALSE;
					$tpl = getTemplateDataForSingleDate($singledate, $metadate_id);
					if (!$tpl['deleted']) {
						$tpl['class'] = 'printhead';
						$tpl['cycle_id'] = $metadate_id;
						if ($tpl['type'] != 1) {
							$tpl['art'] = $TERMIN_TYP[$tpl['type']]['name'];
						} else {
							$tpl['art'] = FALSE;
						}

						$issue_id = '';
						if (is_array($tmp_ids = $singledate->getIssueIDs())) {
							foreach ($tmp_ids as $val) {
								$issue_id = $val;
								break;
							}
						}
						if ($issue_id == '') {
							$tpl['submit_name'] = 'addIssue';
						} else {
							$tpl['submit_name'] = 'editIssue';
							$tpl['issue_id'] = $issue_id;
							if (!empty($themen[$issue_id])) {
								$thema =& $themen[$issue_id];
								$tpl['theme_title'] = htmlReady($thema->getTitle());
								$tpl['theme_description'] = htmlReady($thema->getDescription());
							} else {
								$tpl['theme_title'] = '';
								$tpl['theme_description'] = '';
							}
							$tpl['forumEntry'] = ($thema->hasForum()) ? SELECTED : NOT_SELECTED;
							$tpl['fileEntry'] = ($thema->hasFile()) ? SELECTED : NOT_SELECTED;
						}

						include('lib/raumzeit/templates/singledate_ablaufplan.tpl');
					}
				}

			if ($openAll) {
				?>
				<TR>
					<TD class="steelgraulight" colspan="5" align="center" height="30" valign="middle">
						<INPUT type="hidden" name="allOpen" value="TRUE">
						<INPUT type="image" <?=makebutton('allesuebernehmen', 'src')?> name="editAll" align="absmiddle">&nbsp;&nbsp;&nbsp;
						<A href="<?=$PHP_SELF?>?cmd=closeAll">
							<IMG <?=makebutton('abbrechen', 'src')?> border="0" align="absmiddle">
						</A>
					</TD>
				</TR>
			<? } ?>
			</TABLE>
		</TD>
		<TD class="blank" valign="top">
			<?
			/* * * * * * * * * * * * * * *
			 *       I N F O B O X       *
			 * * * * * * * * * * * * * * */

			if ($sem->metadates->art == 0) {
				$times_info .= '<B>'._("Typ").':</B> '._("regelm&auml;&szlig;ige Veranstaltung").'<BR/>';
				$z = 0;
				if (is_array($turnus = $sem->getFormattedTurnusDates())) {
					foreach ($turnus as $val) {
						if ($z != 0) { $times_info .= '<BR/>'; } $z = 1;
						$times_info .= $val;
					}
				}
			} else {
				$times_info .= '<B>'._("Typ").':</B> '._("unregelm&auml;&szlig;ige Veranstaltung").'<BR/>';
			}

			$infobox[0]["kategorie"] = _("Informationen:");
			$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[0]["eintrag"][] = array ("icon" => "ausruf_small.gif",
					"text"  => _("Hier k&ouml;nnen Sie f&uuml;r die einzelnen Termine Beschreibungen eingeben, Themen im Forum und Dateiordner anlegen."));
			$infobox[0]["eintrag"][] = array ("icon" => "ausruf_small.gif",
					"text"  => sprintf(_("Zeit&auml;nderungen, Raumbuchungen und Termine anlegen k&ouml;nnen Sie unter %s Zeiten %s."), '<a href="raumzeit.php">', '</a>'));
			$infobox[0]["eintrag"][] = array ("icon" => "blank.gif",
					"text"  => $times_info);
			$infobox[1]["eintrag"][] = array ("icon" => "link_intern.gif",
					"text"  => "<a href=\"raumzeit.php?cmd=createNewSingleDate#newSingleDate\">"._("Einen neuen Termin anlegen").'</a><br/><font color="red">'._("(Link f&uuml;hrt zur Seite Zeiten!)").'</font>');
			$infobox[1]["eintrag"][] = array ("icon" => "link_intern.gif",
					"text"  => sprintf(_("Um die allgemeinen Zeiten der Veranstaltung zu &auml;ndern, nutzen Sie bitte den Men&uuml;punkt %s Zeiten %s"), "<a href=\"raumzeit.php\">", "</a>"));
			?>
			<? print_infobox ($infobox, "schedules.jpg"); ?>
		</TD>
	</TR>
	<TR>
		<TD class="blank" colspan="5">
			&nbsp;
		</TD>
	</TR>
</TABLE>
</FORM>
<?
	$sem->store();
	page_close();
?>
