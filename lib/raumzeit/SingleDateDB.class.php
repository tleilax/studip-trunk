<?
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// SingleDateDB.class.php
//
// Datenbank-Abfragen f�r SingleDate.class.php
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
 * SingleDateDB.class.php
 *
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */

class SingleDateDB {
    static function storeSingleDate($termin) {
        $db = new DB_Seminar();

        if ($termin->isExTermin()) {
            $table = 'ex_termine';
            $db->query("SELECT assign_id FROM resources_assign WHERE assign_user_id = '".$termin->getTerminID()."'");
            if ($db->next_record()) {
                $assign_id = $db->f('assign_id');
                $db->query("DELETE FROM resources_assign WHERE assign_user_id = '".$termin->getTerminID()."'");
                $db->query("DELETE FROM resources_requests WHERE termin_id = '".$termin->getTerminID()."'");
                $db->query("DELETE FROM resources_temporary_events WHERE assign_id = '$assign_id'");
            }
        } else {
            $table = 'termine';
        }

        $issueIDs = $termin->getIssueIDs();
        if (is_array($issueIDs)) {
            foreach ($issueIDs as $val) {
                $db->query($query = "REPLACE INTO themen_termine (termin_id, issue_id) VALUES ('".$termin->getTerminID()."', '$val')");
            }
        }

        if ($termin->isUpdate()) {
            $metadate_id = $termin->getMetaDateId() ? "'".$termin->getMetaDateID()."'" : 'NULL';
            $db->query($query = "UPDATE $table SET metadate_id = $metadate_id, date_typ = '".$termin->getDateType()."', date = '".$termin->getStartTime()."', end_time = '".$termin->getEndTime()."', range_id = '".$termin->getRangeID()."', autor_id = '".$termin->getAuthorID()."',raum = '".mysql_escape_string($termin->getFreeRoomText())."', content = '".$termin->getComment()."'  WHERE termin_id = '".$termin->getTerminID()."'");
            if ($db->affected_rows()) {
                $db->query("UPDATE $table SET chdate = '".$termin->getChDate()."' WHERE termin_id = '".$termin->getTerminID()."'");
            }
        } else {
            $db->query($query = "REPLACE INTO $table (metadate_id, date_typ, date, end_time, mkdate, chdate, termin_id, range_id, autor_id, raum, content) VALUES ('".$termin->getMetaDateID()."', '".$termin->getDateType()."', '".$termin->getStartTime()."', '".$termin->getEndTime()."', '".$termin->getMkDate()."', '".$termin->getChDate()."', '".$termin->getTerminID()."', '".$termin->getRangeID()."', '".$termin->getAuthorID()."', '".mysql_escape_string($termin->getFreeRoomText())."', '".$termin->getComment()."')");
        }

        $db = DBManager::get();
        $db->exec(
           "DELETE FROM termin_related_persons WHERE range_id = ".$db->quote($termin->getTerminId())." " .
        "");
        if (count($termin->related_persons)) {
            $query = "INSERT INTO termin_related_persons (range_id, user_id) VALUES ";
            foreach ($termin->getRelatedPersons() as $number => $user_id) {
                $query .= $number > 0 ? ", " : "";
                $query .= "(".$db->quote($termin->getTerminId()).", ".$db->quote($user_id).") ";
            }
            $db->exec($query);
        }

        return TRUE;
    }

    static function restoreSingleDate($termin_id) {
        $db = new DB_Seminar();
        $db->query("SELECT termine.*, resource_id FROM termine LEFT JOIN resources_assign ON (assign_user_id = termin_id) WHERE termin_id = '$termin_id'");
        $related_persons = DBManager::get()->query(
            "SELECT user_id FROM termin_related_persons " .
            "WHERE range_id = ".DBManager::get()->quote($termin_id)." " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        if ($db->next_record() && $db->f('termin_id')) {
            $ret = $db->Record;
            $ret['ex_termin'] = FALSE;
            $ret['related_persons'] = $related_persons;
            return $ret;
        } else {
            $db->query("SELECT * FROM ex_termine WHERE termin_id = '$termin_id'");
            if ($db->next_record()) {
                $ret = $db->Record;
                $ret['ex_termin'] = TRUE;
                $ret['related_persons'] = $related_persons;
                return $ret;
            } else {
                return FALSE;
            }
        }
    }

    static function deleteSingleDate($id, $ex_termin) {
        $db = new DB_Seminar();
        if ($ex_termin) {
            $table = 'ex_termine';
        } else  {
            $table = 'termine';
        }

        $db->query("DELETE FROM $table WHERE termin_id = '$id'");
        $db->query("DELETE FROM themen_termine WHERE termin_id = '$id'");
        $db->query("DELETE FROM termin_related_persons WHERE range_id = '$id'");

        return TRUE;
    }

    static function getAssignID($termin_id) {
        $db = new DB_Seminar();
        $db->query("SELECT assign_id FROM termine LEFT JOIN resources_assign ON (assign_user_id = termin_id) WHERE termin_id = '$termin_id'");
        if ($db->next_record()) {
            return $db->f('assign_id');
        }

        return FALSE;
    }

    static function getRequestID($termin_id) {
        $db = new DB_Seminar();
        $db->query("SELECT request_id FROM resources_requests WHERE termin_id = '$termin_id'");
        if ($db->next_record()) {
            return $db->f('request_id');
        }

        return FALSE;
    }

    static function getIssueIDs($termin_id) {
        $db = new DB_Seminar();
        $db->query("SELECT tt.* FROM themen_termine as tt LEFT JOIN themen as t ON (tt.issue_id = t.issue_id) WHERE termin_id = '$termin_id' AND t.issue_id IS NOT NULL");

        if ($db->num_rows() == 0) return NULL;

        while ($db->next_record()) {
            if ($db->f('issue_id')) {
                $ret[] = $db->Record;
            }
        }
        return $ret;
    }

    static function deleteIssueID($issue_id, $termin_id) {
        $db = new DB_Seminar();
        $db->query("DELETE FROM themen_termine WHERE termin_id = '$termin_id' AND issue_id = '$issue_id'");
        return TRUE;
    }

    static function deleteRequest($termin_id) {
        $db = new DB_Seminar();
        $db->query("DELETE FROM resources_requests WHERE termin_id = '$termin_id'");
        return TRUE;
    }

}
