<?php
/*
sem_portal.php - Portal fuer Seminarfreischaltung von Stud.IP
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>

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


        page_open(array("sess" => "Seminar_Session", "auth" => "Seminar", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
				$perm->check("user");
?>
<html>
<head>
	<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
	<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
</head>


<?php
        include "seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php
        include "header.php";   //hier wird der "Kopf" nachgeladen
        if ($SessSemName[1]) {
        	include "links1.php";
       	}
        	
        $sess->register ("show_bereich_data");
	$db=new DB_Seminar;

	if ($id)
		$show_bereich_data["id"]=$id;
	
	if (!$level)
		$level=$sem_browse_data["level"];
	
	switch ($level) {
		case "sbb": 
			$bereich_typ="Studienbereich";
			$db->query("SELECT name FROM bereiche WHERE bereich_id='".$show_bereich_data["id"]."'");
			$db->next_record();
			$head_text="&nbsp; &Uuml;bersicht aller Veranstaltungen eines Studienbereichs";
			$intro_text="Alle Veranstaltungen, die dem Studienbereich <b>".$db->f("name")."</b> zugeordnet wurden.";
		break;
		case "s":
			$bereich_typ="Einrichtung";
			$db->query("SELECT Name FROM Institute WHERE Institut_id='".$show_bereich_data["id"]."'");
			$db->next_record();
			$head_text="&nbsp; &Uuml;bersicht aller Veranstaltungen einer Einrichtung";
			$intro_text="Alle Veranstaltungen der Einrichtung <b>".$db->f("Name")."</b>";
		break;
	}

?>
<body>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><b><? echo $head_text ?></td>
</tr>
<tr>
	<td class="blank" colspan=2><br /><blockquote><? echo $intro_text ?></blockquote></td>
</tr>

<tr><td class="blank" colspan=2>
<?

	$target_url="details.php";	//teilt der nachfolgenden Include mit, wo sie die Leute hinschicken soll
	$target_id="sem_id"; 		//teilt der nachfolgenden Include mit, wie die id die &uuml;bergeben wird, bezeichnet werden soll

	include "sem_browse.inc.php"; 		//der zentrale Seminarbrowser wird hier eingef&uuml;gt.

?>
</td>
</tr>
</table>
</table>
<?
     page_close()
 ?>
</body>
</html>