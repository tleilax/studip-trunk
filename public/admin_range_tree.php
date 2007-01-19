<?php
/**
* Frontend
* 
* 
*
* @author		Andr� Noack <andre.noack@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup	admin_modules
* @module		admin_range_tree
* @package		Admin
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_range_tree.php
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("autor");

require_once("visual.inc.php");
require_once("lib/classes/StudipRangeTreeViewAdmin.class.php");


include("seminar_open.php"); //hier werden die sessions initialisiert
include('include/html_head.inc.php');
include('include/header.php');   //hier wird der "Kopf" nachgeladen 
include('include/links_admin.inc.php');  //Linkleiste fuer admins

?>
<table class="blank" cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr><td class="topic" align="left">&nbsp; <b><?=$UNI_NAME . " - " . _("Einrichtungshierarchie bearbeiten")?></b></td></tr>
	<tr><td  align="center" class="blank"><br />
	<table class="blank" cellspacing="0" cellpadding="0" border="0" width="99%">
	<tr>
	<td align="center" class="blank">
<?
$the_tree = new StudipRangeTreeViewAdmin();
$the_tree->open_ranges['root'] = true;

$the_tree->showTree();

echo '</td></tr></table><br /></td></tr></table>';
include ('include/html_end.inc.php');
page_close();

// <!--$Id$-->
?>