<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RangeTreeObjectInst.class.php
// Class to handle items in the "range tree"
// 
// Copyright (c) 2002 Andr� Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
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
require_once ($ABSOLUTE_PATH_STUDIP . "/RangeTreeObject.class.php");
require_once ($ABSOLUTE_PATH_STUDIP . "/config.inc.php");
/**
* class for items in the "range tree"
*
* This class is used for items which are "Einrichtungen"
*
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class RangeTreeObjectInst extends RangeTreeObject {
	
	/**
	* Constructor
	*
	* Do not use directly, call factory method in base class instead
	* @access private
	* @param	string	$item_id
	*/
	function RangeTreeObjectInst($item_id) {
		$base_class = get_parent_class($this);
		//parent::$base_class($item_id); //calling the baseclass constructor 
		$this->$base_class($item_id); //calling the baseclass constructor PHP < 4.1.0
		$this->initItemDetail();
		$this->item_data_mapping = array('Strasse' => 'Stra�e', 'Plz' => 'Ort', 'telefon' => 'Tel.', 'fax' => 'Fax',
										'url' => 'Homepage', 'email' => 'Kontakt');
		$this->item_data['type'] = ($this->item_data['type']) ? $GLOBALS['INST_TYPE'][$this->item_data['type']]['name'] : $GLOBALS['INST_TYPE'][1]['name'];
		
	}
	
	
}

?>
