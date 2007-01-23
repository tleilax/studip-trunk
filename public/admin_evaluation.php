<?php
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_evaluation.php
//
// Show the admin pages
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
 * admin_evaluation.php
 *
 *
 * @author	cb
 * @version	10. Juni 2003
 * @access	public
 * @package	evaluation
 */

ob_start(); // start output buffering

page_open (array ("sess" => "Seminar_Session", 
                  "auth" => "Seminar_Auth",
                  "perm" => "Seminar_Perm",
                  "user" => "Seminar_User"));
$perm->check ("autor");

$HELP_KEYWORD="Basis.Evaluationen";

require_once ('lib/evaluation/evaluation.config.php');

include_once('lib/seminar_open.php');
include_once('include/html_head.inc.php');
include_once('include/header.php');

if ($list || $view)
	include ('include/links_admin.inc.php');
else
	include ('include/links_about.inc.php');

if (($SessSemName[1]) && (($view == "vote_sem") || ($view == "vote_inst"))) 
	$the_range = $SessSemName[1];
else
	$the_range = $_REQUEST['rangeID'];

if ($the_range){
	if (get_Username($the_range))
		$the_range = get_Username($the_range);
	if (get_Userid($the_range))
		$isUserrange = 1;
} elseif ($_REQUEST['view']){
	$the_range = $SessSemName[1];
}

if (empty($the_range)) {
	$the_range = $user->id;
	$isUserrange = 1;
}


if ($the_range != $auth->auth['uname'] && $the_range != 'studip' && !$isUserrange){
	$view_mode = get_object_type($the_range);
	if ($view_mode == "fak"){
		$view_mode = "inst";
	}
} 

if (array_key_exists ("page", $_REQUEST) && $_REQUEST["page"] == "edit")
	include (EVAL_PATH.EVAL_FILE_EDIT);
else
	include (EVAL_PATH.EVAL_FILE_OVERVIEW);

page_close ();
?>
