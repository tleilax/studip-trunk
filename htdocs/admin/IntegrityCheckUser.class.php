<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// IntegrityCheckAbstract.class.php
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
* integrity check plugin for 'User'
*
* 
*
* @access	public	
* @author	Andr� Noack <andre.noack@gmx.net>
* @version	$Id$
* @package	Admin
* @see		IntegrityCheckAbstract
*/
class IntegrityCheckUser extends IntegrityCheckAbstract{
	
	/**
	* constructor
	*
	* calls the base class constructor and initializes checklist array
	* @access	public
	*/
	function IntegrityCheckUser(){
		$baseclass = get_parent_class($this);
		//parent::$baseclass(); //calling the baseclass constructor 
		$this->$baseclass(); //calling the baseclass constructor PHP < 4.1.0
		$this->master_table = "auth_user_md5";
		$this->checklist[] = array('detail_table' => 'user_info',
									'query' => 'view:USER_USERINFO:');
		$this->checklist[] = array('detail_table' => 'seminar_user',
									'query' => 'view:USER_SEMUSER:');
		$this->checklist[] = array('detail_table' => 'user_inst',
									'query' => 'view:USER_INSTUSER:');
		$this->checklist[] = array('detail_table' => 'user_studiengang',
									'query' => 'view:USER_STUDUSER:');
		$this->checklist[] = array('detail_table' => 'archiv_user',
									'query' => 'view:USER_ARCHIVUSER:');
		$this->checklist[] = array('detail_table' => 'admission_seminar_user',
									'query' => 'view:USER_ADMISSIONUSER:');
		$this->checklist[] = array('detail_table' => 'active_sessions',
									'query' => 'view:USER_SESSION:');
		$this->checklist[] = array('detail_table' => 'contact',
									'query' => 'view:USER_CONTACT:');
		$this->checklist[] = array('detail_table' => 'statusgruppe_user',
									'query' => 'view:USER_STATUSGRUPPEUSER:');
		
	}

}
?>
