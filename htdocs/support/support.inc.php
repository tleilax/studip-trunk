<?
/**
* resourcesControl.php
* 
* the controlling body of the resource-management
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		support
* @module		support.inc.php
* @package		support
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// resourcesControl.php
// zentrale Steuerung der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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



/*****************************************************************************
Startups... 
/*****************************************************************************/
require_once ($ABSOLUTE_PATH_STUDIP."msg.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."functions.php");
require_once ($RELATIVE_PATH_SUPPORT."/views/Msg.class.php");
require_once ($RELATIVE_PATH_SUPPORT."/supportFunctions.inc.php");

//We need a stud.ip object opened before
checkObject();

$sess->register("supportdb_data");

$msg = new Msg;
$db=new DB_Seminar;

/*****************************************************************************
headers
/*****************************************************************************/
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php");
include ("$ABSOLUTE_PATH_STUDIP/header.php");


/*****************************************************************************
evaluate values and handle commands
/*****************************************************************************/
//handle values
include ("$RELATIVE_PATH_SUPPORT/lib/evaluate_values.php");

//load content, text, pictures and stuff
include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
include ("$RELATIVE_PATH_SUPPORT/views/page_intros.inc.php");

?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="topic" >&nbsp;<img src="pictures/meinetermine.gif" border="0" align="absmiddle" alt="Ressourcen"><b>&nbsp;<? echo $title; ?></b></td>
	</tr>
	<?
	if ($infobox) {
	?>
	<tr>
		<td class="blank">&nbsp;
		</td>
	</tr>
	<?
	}
	?>
	<tr>
		<td class="blank" valign ="top">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td valign ="top">
					<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<?
						if ($msg->checkMsgs()) {	
							$msg->displayAllMsg($view_mode = "line");
							print "<tr><td class=\"blank\">&nbsp; </td></tr>";						}
						if ($page_intro) {
						?>
						<tr>
							<td class="blank"><? (!$infobox) ? print "<br />":"" ?>
								<table width="99%" align="center" border="0" cellpadding="2" cellspacing ="0">
									<tr><td>
										<font size="-1"><? echo $page_intro ?></font><br />&nbsp; 
									</td></tr>
								</table>
							</td>
						</tr>	
						<?
						}
						?>
						<tr>
							<td class="blank" valign ="top">
	
	<?	

/*****************************************************************************
overview, the contracts the customer has concluded
/*****************************************************************************/
if ($supportdb_data["view"] == "overview"){
	require_once ("$RELATIVE_PATH_SUPPORT/views/Overview.class.php");

	if ($edit_con_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}
	
	$overview = new Overview;
	$overview->ShowOverview($SessSemName[1]);
	
	if ($edit_con_object) {
		echo"</form>";
	}
}

/*****************************************************************************
the single requests
/*****************************************************************************/
if ($supportdb_data["view"] == "requests") {
	require_once ("$RELATIVE_PATH_SUPPORT/views/Request.class.php");

	if (($edit_req_object) || ($supportdb_data["evt_edits"])) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}

	$request = new Request;
	$request->ShowRequests($supportdb_data["actual_con"]);
	
	if (($edit_req_object) || ($supportdb_data["evt_edits"])) {
		echo"</form>";
	}
}


/*****************************************************************************
Listview, die Listendarstellung, views: lists, _lists, openobject_main
/*****************************************************************************/
if ($resources_data["view"]=="processes") {

}

/*****************************************************************************
Seite abschliessen und Infofenster aufbauen
/*****************************************************************************/
		?>						</td>
							</tr>
						</table>
					</td>
				<?
				if ($infobox) {
					?>
					<td class="blank" width="270" align="right" valign="top">
						<? print_infobox ($infobox, $infopic);?>
					</td>
					<?
				}
			?>				
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="blank">&nbsp; 
		</td>
	</tr>
</table>
<?
page_close();
?>