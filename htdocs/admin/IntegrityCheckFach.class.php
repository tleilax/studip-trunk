<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// IntegrityCheckFach.class.php
// Integrity checks for the Stud.IP database
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

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES."/IntegrityCheckAbstract.class.php";

/**
* integrity check plugin for 'Fach'
*
* 
*
* @access	public	
* @author	Andr� Noack <andre.noack@gmx.net>
* @version	$Id$
* @package	Admin
* @see		IntegrityCheckAbstract
*/
class IntegrityCheckFach extends IntegrityCheckAbstract{
	
	/**
	* constructor
	*
	* calls the base class constructor and initializes checklist array
	* @access	public
	*/
	function IntegrityCheckFach(){
		$baseclass = get_parent_class($this);
		parent::$baseclass(); //calling the baseclass constructor
		$this->master_table = "faecher";
		$this->checklist[] = array('detail_table' => 'bereich_fach',
									'query' => 'view:FACH_BEREICH:');
		$this->checklist[] = array('detail_table' => 'fach_inst',
									'query' => 'view:FACH_INST:');
	}

}
?>
