<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ilias3_referrer.php
//
// Copyright (c) 2005 Arne Schroeder <schroeder@data-quest.de> 
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
ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("autor");

include ("seminar_open.php"); // initialise Stud.IP-Session

require_once ("config.inc.php");

//include ("html_head.inc.php"); // Output of html head
//include ("header.php");   // Output of Stud.IP head

if ($ELEARNING_INTERFACE_ENABLE)
{
	require_once ("" . $RELATIVE_PATH_ELEARNING_MODULES . "elearning/ELearningUtils.class.php");
	ELearningUtils::bench("start");

	$GLOBALS['ALWAYS_SELECT_DB'] = true;

	

	if (isset($ELEARNING_INTERFACE_MODULES[$cms_select]["name"]))
	{

		ELearningUtils::loadClass($cms_select);
		// init session now
		$sess_id = $connected_cms[$cms_select]->user->getSessionId();
		$connected_cms[$cms_select]->terminate();
		ob_end_clean();
		if (!$sess_id){
			include ('html_head.inc.php'); // Output of html head
			include ('header.php');   // Output of Stud.IP head
			parse_window('error�' 
					. sprintf(_("Automatischer Login f�r das System <b>%s</b> (Nutzername:%s) fehlgeschlagen."), htmlReady($connected_cms[$cms_select]->getName()), $connected_cms[$cms_select]->user->getUsername()),'�'
					, _("Login nicht m&ouml;glich")
					, '<div style="margin:10px">'
					._("Dieser Fehler kann dadurch hervorgerufen werden, dass sie Ihr Passwort ge�ndert haben. In diesem Fall versuchen sie bitte Ihren Account erneut zu verkn�pfen.")
					.  '<br>' . sprintf(_("%sZur&uuml;ck%s zu Meine Lernmodule"), '<a href="my_elearning.php"><b>', '</b></a>') . '</div>');
			page_close();
			echo '</body>';
			die;
		}
		$parameters = "?sess_id=$sess_id";
		if (isset($client_id))
			$parameters .= "&client_id=$client_id";
		if (isset($target))
			$parameters .= "&target=$target";
		if (isset($ref_id))
			$parameters .= "&ref_id=$ref_id";
		if (isset($type))
			$parameters .= "&type=$type";
		
		// refer to studip_referrer.php
		header("Location: ".$ELEARNING_INTERFACE_MODULES[$cms_select]["ABSOLUTE_PATH_ELEARNINGMODULES"] . $ELEARNING_INTERFACE_MODULES[$cms_select]["target_file"] . $parameters);
		exit();
	}
}
?>
