<?
/**
* persons.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		persons
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// persons.inc.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"]."visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/extern_functions.inc.php");
global $_fullname_sql;

$range_id = $this->config->range_id;

//$all_groups = $this->config->getValue("Main", "groups");
if (!$all_groups = get_all_statusgruppen($range_id))
	die($GLOBALS["EXTERN_ERROR_MESSAGE"]);
else
	$all_groups = array_keys($all_groups);

if (!$group_ids = $this->config->getValue("Main", "groupsvisible"))
	die($GLOBALS["EXTERN_ERROR_MESSAGE"]);
else
	$group_ids = array_intersect($all_groups, $group_ids);

if (!is_array($group_ids))
	die($GLOBALS["EXTERN_ERROR_MESSAGE"]);

if (!$visible_groups = get_statusgruppen_by_id($range_id, $group_ids))
	die($GLOBALS["EXTERN_ERROR_MESSAGE"]);

$aliases_groups = $this->config->getValue("Main", "groupsalias");
$order = $this->config->getValue("Main", "order");
$sort = $this->config->getValue("Main", "sort");

$query_order = "";
foreach ($sort as $key => $position) {
	if ($position > 0)
		$query_order[$position] = $this->data_fields[$key];
}
if ($query_order) {
	ksort($query_order, SORT_NUMERIC);
	$query_order = " ORDER BY " . implode(",", $query_order);
}

$db = new DB_Seminar();
$grouping = $this->config->getValue("Main", "grouping");
if(!$grouping){
	$groups_ids = implode("','", $this->config->getValue("Main", "groupsvisible"));
	
	$query = "SELECT DISTINCT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,	Email, aum.user_id, username, ";
	$query .= $_fullname_sql[$this->config->getValue("Main", "nameformat")] . " AS fullname, aum.Nachname ";
	$query .= "FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id) ";
	$query .= "LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst ui USING(user_id) ";
	$query .= "WHERE statusgruppe_id IN ('$groups_ids') AND Institut_id = '$range_id'$query_order";
	
	$db->query($query);
	$visible_groups = array("");
}

// generic data fields
if ($generic_datafields = $this->config->getValue("Main", "genericdatafields")) {
	$datafields_obj =& new DataFields();
}

$repeat_headrow = $this->config->getValue("Main", "repeatheadrow");
$link_persondetails = $this->getModuleLink("Persondetails",
		$this->config->getValue("LinkIntern", "config"), $this->config->getValue("LinkIntern", "srilink"));
$data["data_fields"] = $this->data_fields;

$out = "";
$first_loop = TRUE;
foreach ($visible_groups as $group_id => $group) {
	if($grouping){
		$query = "SELECT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,	Email, aum.user_id, username, ";
		$query .= $_fullname_sql[$this->config->getValue("Main", "nameformat")] . " AS fullname, aum.Nachname ";
		$query .= "FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id) ";
		$query .= "LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst ui USING(user_id) ";
		$query .= "WHERE statusgruppe_id='$group_id' AND Institut_id = '$range_id'$query_order";
		
		$db->query($query);
		
		$position = array_search($group_id, $all_groups);
		if($aliases_groups[$position])
			$group = $aliases_groups[$position];
	}

	if ($db->num_rows()) {
		
		if ($grouping && $repeat_headrow == "beneath")
			$out .= $this->elements["TableGroup"]->toString(array("content" => htmlReady($group)));
		
		if($repeat_headrow || $first_loop)
			$out .= $this->elements["TableHeadrow"]->toString();
		
		
		if ($grouping && $repeat_headrow != "beneath")
			$out .= $this->elements["TableGroup"]->toString(array("content" => htmlReady($group)));

		while($db->next_record()){
		
			$data["content"] = array(
				"Nachname"			=> $this->elements["LinkIntern"]->toString(array("content" =>
														htmlReady($db->f("fullname")), "module" => "Persondetails",
														"link_args" => "username=" . $db->f("username"))),
												
				"Telefon"				=> htmlReady($db->f("Telefon")),
			
				"sprechzeiten"	=> htmlReady($db->f("sprechzeiten")),
			
				"raum"					=> htmlReady($db->f("raum")),
			
				"Email"					=> $this->elements["Link"]->toString(array("content" =>
														$db->f("Email"), "link" => "mailto:" . $db->f("Email")))
			);
			
			// include generic datafields
			
			$out .= $this->elements["TableRow"]->toString($data);
		}
		$first_loop = FALSE;
	}
}
	
$this->elements["TableHeader"]->printout(array("content" => $out));
?>
