<?php
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_vote.php
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
 * admin_vote.php
 *
 *
 * @author	Michael Cohrs <michael@cohrs.de>
 * @version	10. Juni 2003
 * @access	public
 * @package	vote
 */
ob_start(); // start output buffering

page_open (array ("sess" => "Seminar_Session", "auth" => "Seminar_Auth",
		  "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check ("autor");

$HELP_KEYWORD="Basis.Votings";

include_once($ABSOLUTE_PATH_STUDIP . "seminar_open.php");
include_once($ABSOLUTE_PATH_STUDIP . "html_head.inc.php");
include_once($ABSOLUTE_PATH_STUDIP . "header.php");
$the_range = isset($showrangeID) ? $showrangeID : $rangeID;
	
if (!empty($the_range) && $the_range != $auth->auth['uname'] && $the_range != 'studip'){
	$view_mode = get_object_type($the_range);
	if ($view_mode == "fak"){
		$view_mode = "inst";
	}
	include ($ABSOLUTE_PATH_STUDIP . "links_admin.inc.php");
} else {
	include ($ABSOLUTE_PATH_STUDIP . "links_about.inc.php");
}
if ($page == "edit")
	include ($ABSOLUTE_PATH_STUDIP . "/vote/vote_edit.inc.php");
else
	include ($ABSOLUTE_PATH_STUDIP . "/vote/vote_overview.inc.php");

page_close ();
?>
