<?php
/*
admin_smileys.php - Smiley-Verwaltung von Stud.IP.
Copyright (C) 2004 Tobias Thelen <tthelen@uos.de>
Copyright (C) 2004 Jens Schmelzer <jens.schmelzer@fh-jena.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

page_open(array('sess' => 'Seminar_Session', 'auth' => 'Seminar_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));
$perm->check('root');

if (!$SMILEYADMIN_ENABLE) {
	print '<p>' . _("Smiley-Modul abgeschaltet."). '</p>';
	print '</body></html>';
	page_close();
	die;
}

include ('seminar_open.php'); // initialise Stud.IP-Session

require_once('lib/classes/smiley.class.php');

// Start of Output
include ('html_head.inc.php'); // Output of html head
include ('header.php');   // Output of Stud.IP head
include ('links_admin.inc.php');  //Linkleiste fuer admins


$sm = new smiley(true);

$cmd = (isset($_REQUEST['cmd']))? $_REQUEST['cmd']:'';

switch ($cmd) {
	case 'upload':
		$sm->imaging(); break;
	case 'updatetable':
		$sm->update_smiley_table(); break;
	case 'countsmiley':
		$sm->search_smileys(); break;
	case 'update':
		$sm->process_commands(); break;
	case 'delete':
		$sm->delete_smiley(); break;
	default:
	;
}

//
// Start output
//
$container=new ContainerTable();
echo $container->headerRow("<b>&nbsp;"._("Verwaltung der Smileys")."</b>");
echo $container->openCell(array('align'=>'center'));

$sm->display_msg();

$content=new ContentTable(array());
echo $content->open(), $content->openRow(), $content->openCell(array('valign'=>'top'));

$sm->show_menue();

echo $content->closeCell();
echo $content->openCell(array('valign'=>'top'));


$sm->show_upload_form();
$sm->show_smiley_list();

echo $content->close();
echo $container->blankRow();
echo $container->close();

page_close();
?>
</body>
</html>
<!-- $Id$ -->
