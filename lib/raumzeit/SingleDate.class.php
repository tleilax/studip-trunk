<?php
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// SingleDate.class.php
//
// Diese Klasse stellt einen einzelnen Eintrag in der Tabelle termine, bzw. ex_termine dar.
//
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


/**
 * SingleDate.class.php
 *
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */

require_once('lib/raumzeit/SingleDateDB.class.php');
require_once('lib/dates.inc.php');
require_once('lib/classes/HolidayData.class.php');
require_once($GLOBALS['RELATIVE_PATH_RESOURCES'].'/lib/RoomRequest.class.php');
//require_once($RELATIVE_PATH_RESOURCES.'/lib/VeranstaltungResourcesAssign.class.php');

class SingleDate {
	var $termin_id = '';
	var $date_typ = 1;
	var $metadate_id = '';
	var $date = 0;
	var $end_time = 0;
	var $mkdate = 0;
	var $chdate = 0;
	var $orig_ex = FALSE;
	var $ex_termin = FALSE;
	var $range_id = '';
	var $author_id = '';
	var $resource_id = '';
	var $raum = '';
	var $request_id = NULL;
	var $requestData = NULL;
	var $update = FALSE;
	var $issues = NULL;
	var $messages = NULL;
	var $content = '';

	//var $check_array = array()		// This array is used to check if the singledate has been changed and needs to be stored

	function SingleDate ($data = '') {
		global $user, $id;
		if (is_array($data)) {
			if ($data['termin_id']) $termin_id = $data['termin_id'];
			if ($data['seminar_id']) $id = $data['seminar_id'];
		} else {
			$termin_id = $data;
		}
		if ($termin_id != '') {
			$this->termin_id = $termin_id;
			$this->update = TRUE;
			$this->restore();
		} else {
			$this->termin_id = md5(uniqid('SingleDate',1));
			$this->author_id = $user->id;
			$this->range_id = $id;
			$this->mkdate = time();
			$this->chdate = time();
			$this->update = FALSE;
		}
	}

	function getStartTime() {
		return $this->date;
	}

	function setTime($start, $end) {
		if (($start == 0) || ($end == 0)) return FALSE;

		if (($this->date != $start) || ($this->end_time != $end)) {
			if ($this->validate($start, $end)) {
				$before = $this->toString();

				$this->date = $start;
				$this->end_time = $end;
				if ($this->resource_id) {
					$tmp_resource_id = $this->resource_id;
					$this->killAssign();
					$this->bookRoom($tmp_resource_id);
				}

				$after = $this->toString();
				// logging >>>>>>
				log_event("SINGLEDATE_CHANGE_TIME", $this->range_id, $before, $before.' -> '.$after);
				// logging <<<<<<
				return TRUE;
			}
			return FALSE;
		}

		return FALSE;
	}

	function getEndTime() {
		return $this->end_time;
	}

	function setComment($comment) {
		$this->content = $comment;
	}

	function getComment() {
		return $this->content;
	}

	function getMetaDateID() {
		return $this->metadate_id;
	}

	function setMetaDateID($id) {
		if ($id != '') {
			$this->metadate_id = $id;
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function getRangeID() {
		return $this->range_id;
	}

	function setDateType($typ) {
		$this->date_typ = $typ;
		return TRUE;
	}

	function getDateType() {
		return $this->date_typ;
	}

	function getTypeName() {
		global $TERMIN_TYP;
		return $TERMIN_TYP[$this->date_typ]['name'];
	}

    function getAuthorID() {
        return $this->author_id;
    }

	function getChDate() {
		return $this->chdate;
	}

	function getMkDate() {
		return $this->mkdate;
	}

	function setSeminarID($seminar_id) {
		$this->range_id = $seminar_id;
	}

	function getSeminarID() {
		return $this->range_id;
	}

	function getSingleDateID() {
		return $this->termin_id;
	}

	function getResourceID() {
		return $this->resource_id;
	}

	function getTerminID() {
		return $this->termin_id;
	}

	function getFreeRoomText() {
		return $this->raum;
	}

	function setFreeRoomText($freeRoomText) {
		$this->raum = $freeRoomText;
	}

	function getCycleID() {
		return $this->metadate_id;
	}

	function killIssue() {
		// We delete the issue, cause there is now chance, anybody can get to it without expert view
		if(!$GLOBALS["RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW"]){
			if ($issue_ids = $this->getIssueIDs()) {
				foreach ($issue_ids as $issue_id) {
					// delete this issue
					$issue = new Issue(array('issue_id' => $issue_id));
					$issue->delete();
				}
			}
		}
	}

	function delete() {
		$this->chdate = time();
		$this->killAssign();
		$this->killIssue();

		return SingleDateDB::deleteSingleDate($this->termin_id, $this->ex_termin);
	}

	function store() {
		$this->chdate = time();
		if ($this->ex_termin) {
			$this->killAssign();
			$this->killIssue();
			if (!$this->metadate_id) {
				SingleDateDB::deleteSingleDate($this->termin_id, $this->orig_ex);
				return true;
			}
		}

		// if date_typ is 0, it defaults to the TERMIN_TYP[1], so we have to set it to 1 for matching real world to date_typ
		if (!$this->date_typ) $this->date_typ = 1;

		if ($this->orig_ex != $this->ex_termin) {
			SingleDateDB::deleteSingleDate($this->termin_id, $this->orig_ex);
		}

		return SingleDateDB::storeSingleDate($this);
	}

	function restore() {
		if (!($data = SingleDateDB::restoreSingleDate($this->termin_id))) {
			return FALSE;
		}
		$this->fillValuesFromArray($data);
		return TRUE;
	}

	function setExTermin($ex) {
		if ($ex != $this->ex_termin) {
			$this->update = false;
			$this->ex_termin = $ex;
			return TRUE;
		}

		return FALSE;
	}

	function isExTermin() {
		return $this->ex_termin;
	}

    function isUpdate() {
        return $this->update;
    }

	function isHoliday() {
		foreach (HolidayData::GetAllHolidaysArray() as $val) {
			if (($val['beginn'] <= $this->date) && ($val['ende'] >= $this->end_time)) {
				$name = $val['name'];
			}
		}

		if (!$name) {
			$holy_type = holiday($this->date);
			$name = $holy_type['name'];
		}

		if ($name) {
			return $name;
		} else {
			return FALSE;
		}
	}

	function fillValuesFromArray($daten) {
		$this->metadate_id = $daten['metadate_id'];
		$this->termin_id = $daten['termin_id'];
		if ($daten['date_typ'] != 0) {  // TODO: should it be allowed, that there can be unspecified singledates?
			$this->date_typ = $daten['date_typ'];
		}
		$this->date = $daten['date'];
		$this->end_time = $daten['end_time'];
		$this->mkdate = $daten['mkdate'];
		$this->chdate = $daten ['chdate'];
		$this->ex_termin = $daten['ex_termin'];
		$this->orig_ex = $daten['ex_termin'];
		$this->range_id = $daten['range_id'];
		$this->author_id = $daten['autor_id'];
		$this->resource_id = $daten['resource_id'];
		$this->raum = $daten['raum'];
		$this->content = $daten['content'];
		$this->update = TRUE;
		return TRUE;
	}

	function toString() {
		return getWeekDay(date('w', $this->date)).'., '.date('d.m.Y, H:i', $this->date).' - '.date('H:i', $this->end_time);
	}

	function bookRoom($roomID) {
		if ($this->ex_termin) return FALSE;
		$this->raum = '';
		if ($this->resource_id != '') {
			return $this->changeAssign($roomID);
		} else {
			return $this->insertAssign($roomID);
		}
	}

	function insertAssign($roomID) {
		$createAssign =& AssignObject::Factory(FALSE, $roomID, $this->termin_id, '',
				$this->date, $this->end_time, $this->end_time,
				0, 0, 0, 0, 0, 0);

		$overlaps = $createAssign->checkOverlap(TRUE);
		if (is_array($overlaps) && (sizeof($overlaps) > 0)) {
			$resObj =& ResourceObject::Factory($roomID);
			$raum = $resObj->getFormattedLink(TRUE, TRUE, TRUE);
			$msg = sprintf(_("F�r den Termin %s konnte der Raum %s nicht gebucht werden, da es �berschneidungen mit folgenden Terminen gibt:"), $this->toString(), $raum).'<br/>';
			foreach ($overlaps as $tmp_assign_id => $val) {
				if ($val["lock"])
					$msg .= sprintf(_("%s, %s Uhr bis %s, %s Uhr (Sperrzeit)")."\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("d.m.Y", $val["end"]), date("H:i", $val["end"]));
				else
					$msg .= sprintf(_("%s von %s bis %s Uhr")."\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("H:i", $val["end"]));
			}
			$this->messages[] = 'error�'.$msg.'�';
			return FALSE;
		}

		if ($createAssign->create()) {
			$resObj =& ResourceObject::Factory($roomID);
  	  $raum = $resObj->getFormattedLink(TRUE, TRUE, TRUE);
			$msg = sprintf(_("F�r den Termin %s wurde der Raum %s gebucht."), $this->toString(), $raum);
			$this->messages[] = 'msg�'.$msg.'�';
			$this->resource_id = $roomID;
			return TRUE;
		}
		return FALSE;
	}

	function changeAssign($roomID) {
		if ($assign_id = SingleDateDB::getAssignID($this->termin_id)) {
			$changeAssign =& AssignObject::Factory($assign_id);
			$changeAssign->setResourceId($roomID);

			$changeAssign->chng_flag = TRUE;

			$changeAssign->setBegin($this->date);
			$changeAssign->setEnd($this->end_time);
			$changeAssign->setRepeatEnd($this->end_time);
			$changeAssign->setRepeatQuantity(0);
			$changeAssign->setRepeatInterval(0);
			$changeAssign->setRepeatMonthOfYear(0);
			$changeAssign->setRepeatDayOfMonth(0);
			$changeAssign->setRepeatWeekOfMonth(0);
			$changeAssign->setRepeatDayOfWeek(0);

			$overlaps = $changeAssign->checkOverlap(TRUE);
			if (is_array($overlaps) && (sizeof($overlaps) > 0)) {
				$resObj =& ResourceObject::Factory($roomID);
				$raum = $resObj->getFormattedLink(TRUE, TRUE, TRUE);
				$msg = sprintf(_("F�r den Termin %s konnte der Raum %s nicht gebucht werden, da es �berschneidungen mit folgenden Terminen gibt:"), $this->toString(), $raum).'<br/>';
				foreach ($overlaps as $tmp_assign_id => $val) {
					if ($val["lock"])
						$msg .= sprintf(_("%s, %s Uhr bis %s, %s Uhr (Sperrzeit)")."\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("d.m.Y", $val["end"]), date("H:i", $val["end"]));
					else
						$msg .= sprintf(_("%s von %s bis %s Uhr")."\n", date("d.m.Y", $val["begin"]), date("H:i", $val["begin"]), date("H:i", $val["end"]));
				}
				$this->messages[] = 'error�'.$msg.'�';
				return FALSE;
			}

			$this->resource_id = $roomID;
			$changeAssign->store();
			/*if (!$changeAssign->getId())
				$changeAssign->createId();*/
			$resObj =& ResourceObject::Factory($roomID);
      $raum = $resObj->getFormattedLink(TRUE, TRUE, TRUE);
			$msg = sprintf(_("F�r den Termin %s wurde der Raum %s gebucht."), $this->toString(), $raum);
			$this->messages[] = 'msg�'.$msg.'�';
			return TRUE;
		}
		return FALSE;
	}

	function killAssign() {
		$this->resource_id = '';
		if ($assign_id = SingleDateDB::getAssignID($this->termin_id)) {
			$killAssign =& AssignObject::Factory($assign_id);
			$killAssign->delete();
		}

		/*if ($request_id = SingleDateDB::getRequestID($this->termin_id)) {
			$killRequest = new RoomRequest ($request_id);
			$killRequest->delete();
		}*/
	}

	function hasRoom() {
		return ($this->resource_id) ? TRUE : FALSE;
	}

	function getRoom() {
		if (!$this->resource_id) {
			return null;
		} else {
			$resObj =& ResourceObject::Factory($this->resource_id);
			return $resObj->getName();
		}
	}

	function hasRoomRequest() {
		if (getDateRoomRequest($this->termin_id)) {
			if (!$this->request_id) {
				$this->request_id = SingleDateDB::getRequestID($this->termin_id);
			}
			$rD =& new RoomRequest($this->request_id);
			if (($rD->getClosed() == 1) || ($rD->getClosed() == 2)) {
				return FALSE;
			}
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function getRequestedRoom() {
		if ($this->hasRoomRequest()) {
			$rD =& new RoomRequest($this->request_id);
			$resObject =& ResourceObject::Factory($rD->resource_id);
			return $resObject->getName();
		}
		return FALSE;
	}

	function getRoomRequestInfo() {
		if ($this->hasRoomRequest()) {
			if (!$this->requestData) {
				$rD =& new RoomRequest($this->request_id);
				$resObject =& ResourceObject::Factory($rD->resource_id);
				$this->requestData .= 'Raum: '.$resObject->getName().'\n';
				$this->requestData .= 'verantworlich: '.$resObject->getOwnerName().'\n\n';
				foreach ($rD->getProperties() as $val) {
					$this->requestData .= $val['name'].': ';
					if ($val['type'] == 'bool') {
						if ($val['state'] == 'on') {
							$this->requestData .= 'vorhanden\n';
						} else {
							$this->requestData .= 'nicht vorhanden\n';
						}
					} else {
						$this->requestData .= $val['state'].'\n';
					}
				}
				if  ($rD->getClosed() == 0) {
					$txt = _("Die Anfrage wurde noch nicht bearbeitet.");
				} else if ($rD->getClosed() == 3) {
					$txt = _("Die Anfrage wurde bearbeitet und abgelehnt.");
				} else {
					$txt = _("Die Anfrage wurde bearbeitet.");
				}

				$this->requestData .= '\nStatus: '.$txt.'\n';

				$this->requestData .= '\nNachricht an den Raumadministrator:\n';
				$this->requestData .= str_replace("\r", '', str_replace("\n", '\n', $rD->getComment()));

			}

			return $this->requestData;
		} else {
			return FALSE;
		}
	}

	function removeRequest() {
		return SingleDateDB::deleteRequest($this->termin_id);
	}

	function readIssueIDs() {
		if (!$this->issues) {
			if ($data = SingleDateDB::getIssueIDs($this->termin_id)) {
				foreach ($data as $val) {
					$this->issues[$val['issue_id']] = $val['issue_id'];
				}
			}
		}
		return TRUE;
	}

	function getIssueIDs() {
		$this->readIssueIDs();
		return $this->issues;
	}

	function addIssueID($issue_id) {
		$this->readIssueIDs();
		$this->issues[$issue_id] = $issue_id;
		return TRUE;
	}

	function deleteIssueID($issue_id) {
		$this->readIssueIDs();
		unset($this->issues[$issue_id]);
		SingleDateDB::deleteIssueID($issue_id, $this->termin_id);
		return TRUE;
	}

	function getMessages() {
		$temp = $this->messages;
		$this->messages = NULL;
		return $temp;
	}

	// checks, if the single-date has plausible values
	function validate($start = 0, $end = 0) {
		if ($start == 0) {
			$start = $this->date;
		}
		if ($end == 0) {
			$end = $this->end_time;
		}

		if ($start < 100000) return FALSE;
		if ($end < 100000)  return FALSE;
		if ($start > $end) {
			$this->messages[] = 'error�'._("Die Endzeitpunkt darf nicht vor dem Anfangszeitpunkt liegen!").'�';
			return FALSE;
		}
		return TRUE;
	}
}
