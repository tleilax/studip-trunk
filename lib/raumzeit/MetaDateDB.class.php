<?
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// MetaDateDB.class.php
//
// Datenbank-Abfragen f�r MetaDate.class.php
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
 * MetaDateDB.class.php
 *
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */

class MetaDateDB {
	function storeMetaData($metadate) {
		$db = new DB_Seminar();

		$db->query("UPDATE seminare SET metadata_dates = '".$metadate->getSerializedMetaData()."' WHERE Seminar_id = '".$metadate->getSeminarID()."'");
		return TRUE;
	}

	function restoreMetaData($seminar_id) {
		$db = new DB_Seminar();
		$db->query("SELECT metadata_dates FROM seminare WHERE Seminar_id = '$seminar_id'");
		if ($db->next_record()) {
			return $db->f('metadata_dates');
		} else {
			return FALSE;
		}
	}

}
?>
