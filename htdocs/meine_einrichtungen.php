<?php
/*
meine_einrichtungen.php - Anzeige der besuchten/benutzten Einrichtungen
Copyright (C) 2002 	Stefan Suchi <suchi@gmx.de>,
				Ralf Stockmann <rstockm@gwdg.de>,
				Cornelis Kater <ckater@gwdg.de>
				Suchi & Berg GmbH <info@data-quest.de>

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


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

ob_start(); //Outputbuffering f�r maximal Performance

function get_my_inst_values(&$my_inst) {
	 global $user;
	 $db2 = new DB_seminar();
// Postings
	$db2->query("SELECT b.Seminar_id,count(topic_id) as count, count(IF((chdate > b.loginfilenow AND user_id !='".$user->id."'),a.topic_id,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN px_topics a USING (Seminar_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		$my_inst[$db2->f("Seminar_id")]["neuepostings"]=$db2->f("neue");
		$my_inst[$db2->f("Seminar_id")]["postings"]=$db2->f("count");
	}

//dokumente
	$db2->query("SELECT b.Seminar_id,count(dokument_id) as count, count(IF((chdate > b.loginfilenow AND user_id !='".$user->id."'),a.dokument_id,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN dokumente a USING (Seminar_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		$my_inst[$db2->f("Seminar_id")]["neuedokumente"]=$db2->f("neue");
		$my_inst[$db2->f("Seminar_id")]["dokumente"]=$db2->f("count");
	}

//News
	$db2->query("SELECT b.Seminar_id,count(range_id) as count, count(IF((date > b.loginfilenow AND user_id !='".$user->id."'),range_id,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN news_range ON (b.Seminar_id=range_id) LEFT JOIN news  USING(news_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		$my_inst[$db2->f("Seminar_id")]["neuenews"]=$db2->f("neue");
		$my_inst[$db2->f("Seminar_id")]["news"]=$db2->f("count");
	}
// Literatur?
	$db2->query("SELECT b.Seminar_id,IF(literatur !='' OR links != '',1,0) AS literatur,
			IF((chdate > b.loginfilenow AND user_id !='".$user->id."' AND (literatur !='' OR links != '')),1,0) AS neue 
			FROM loginfilenow_".$user->id." b  LEFT JOIN literatur ON (range_id = b.Seminar_id)");
	while($db2->next_record()) {
		$my_inst[$db2->f("Seminar_id")]["neueliteratur"]=$db2->f("neue");
		$my_inst[$db2->f("Seminar_id")]["literatur"]=$db2->f("literatur");
	}

	$db2->query("SELECT b.Seminar_id,count(termin_id) as count, count(IF((chdate > b.loginfilenow AND autor_id !='".$user->id."'),a.termin_id,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN termine a ON (b.Seminar_id=range_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		$my_inst[$db2->f("Seminar_id")]["neuetermine"]=$db2->f("neue");
		$my_inst[$db2->f("Seminar_id")]["termine"]=$db2->f("count");
	}
	return;
}


function print_institut_content($instid,$my_inst_values) {
  // Postings
  IF ($my_inst_values["neuepostings"])  ECHO "<a href=\"institut_main.php?auswahl=$instid&redirect_to=forum.php&view=neue\">&nbsp; <img src='pictures/icon-posting2.gif' border=0 ".tooltip($my_inst_values["postings"]." Postings, ".$my_inst_values["neuepostings"]." Neue")."></a>";
  ELSEIF ($my_inst_values["postings"]) ECHO "<a href=\"institut_main.php?auswahl=$instid&redirect_to=forum.php\">&nbsp; <img src='pictures/icon-posting.gif' border=0 ".tooltip($my_inst_values["postings"]." Postings")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";
  //Dokumente
  IF ($my_inst_values["neuedokumente"]) ECHO "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=folder.php&cmd=all\"><img src='pictures/icon-disc2.gif' border=0 ".tooltip($my_inst_values["dokumente"]." Dokumente, ".$my_inst_values["neuedokumente"]." neue")."></a>";
  ELSEIF ($my_inst_values["dokumente"]) ECHO "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=folder.php&cmd=tree\"><img src='pictures/icon-disc.gif' border=0 ".tooltip($my_inst_values["dokumente"]." Dokumente")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //News
  IF ($my_inst_values["neuenews"]) ECHO "&nbsp; <a href=\"institut_main.php?auswahl=$instid\"><img src='pictures/icon-news2.gif' border=0 ".tooltip($my_inst_values["news"]." News, ".$my_inst_values["neuenews"]." neue")." </a>";
  ELSEIF ($my_inst_values["news"]) ECHO "&nbsp; <a href=\"institut_main.php?auswahl=$instid\"><img src='pictures/icon-news.gif' border=0 ".tooltip($my_inst_values["news"]." News")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //Literatur
IF ($my_inst_values["literatur"]) {
	ECHO "<a href=\"institut_main.php?auswahl=$instid&redirect_to=literatur.php\">";
	if ($my_inst_values["neueliteratur"])
	  ECHO "&nbsp; <img src=\"pictures/icon-lit2.gif\" border=0 ".tooltip("Zur Literatur und Linkliste (ge�ndert)")."></a>";
		else
		  ECHO "&nbsp; <img src=\"pictures/icon-lit.gif\" border=0 ".tooltip("Zur Literatur und Linkliste")."></a>";
  }
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  // Termine
  IF ($my_inst_values["neuetermine"]) ECHO "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=dates.php\"><img src='pictures/icon-uhr2.gif' border=0 ".tooltip($my_inst_values["termine"]." Termine, ".$my_inst_values["neuetermine"]." neue")."></a>";
  ELSEIF ($my_inst_values["termine"]) ECHO "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=dates.php\"><img src='pictures/icon-uhr.gif' border=0 ".tooltip($my_inst_values["termine"]." Termine")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  echo "&nbsp;&nbsp;";

} // Ende function print_institut_content


include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php"); 		// Klarnamen fuer den Veranstaltungsstatus
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php"); 		// htmlReady fuer die Veranstaltungsnamen
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); 		// Semester-Namen fuer Admins
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php"); 		// Semester-Namen fuer Admins

$cssSw=new cssClassSwitcher;                          					// Klasse f�r Zebra-Design
$cssSw->enableHover();
$db=new DB_Seminar;
$db2=new DB_Seminar;
// we are defintely not in an lexture or institute
closeObject();
$links_admin_data =''; 	//Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

echo "\n".cssClassSwitcher::GetHoverJSFunction()."\n";

include ("$ABSOLUTE_PATH_STUDIP/links_seminare.inc.php");   	//hier wird die Navigation nachgeladen

//bei Bedarf aus seminar_user austragen
if ($cmd=="kill") {
	$db->query("DELETE FROM user_inst WHERE user_id='$user->id' AND Institut_id='$auswahl'");
	if ($db->affected_rows() == 0)  $meldung="error�Datenbankfehler!";
	else {
	  
	  $db->query("SELECT Name FROM Institute WHERE Institut_id = '$auswahl'");
	  $db->next_record();
	  $meldung="msg�Die Zuordnung zur Einrichtung <b>".$db->f("Name")."</b> wurde aufgehoben.";
	}
}


//This view is only for users up to admin
IF ( !$perm->have_perm("root")){

	 if (!isset($sortby)) $sortby="Name";
	 if ($sortby == "count")
	 $sortby = "count DESC";
	$db->query ("SELECT b.Name, b.Institut_id,b.type, user_inst.inst_perms,IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst LEFT JOIN Institute b USING (Institut_id) WHERE user_inst.user_id = '$user->id' GROUP BY Institut_id ORDER BY $sortby");
	$num_my_inst=$db->num_rows();
	 if (!$num_my_inst)
	 	if ($perm->have_perm("dozent"))
	 		$meldung="info�Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zust&auml;ndigen <a href=\"impressum.php?view=ansprechpartner\">Administratoren</a>.�".$meldung;
	 	else
			$meldung="info�Sie haben sich noch keinen Einrichtungen zugeordnet. Um sich Einrichtungen zuzuordnen, nutzen Sie bitte die entsprechende <a href=\"edit_about.php?view=Karriere#einrichtungen\">Option</a> unter \"universit&auml;re Daten\" in ihren pers&ouml;nlichen Einstellungen.�".$meldung;
	 ?>
	 <table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="topic" colspan="3">
			<img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b>Meine Einrichtungen</b>
		</td>
	</tr>
	 <?
	 if ($num_my_inst){
	 ?>
	 	<tr>
	 		<td valign="top" class="blank">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
					<tr align="center">
						<td class="blank" colspan="3">&nbsp;
						</td>
					</tr>
					<tr align="center">
						<td valign="top" align="center">
							<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center" class="blank">
								<? if ($meldung) {
									echo "<tr><td>&nbsp; </td></tr>";
									parse_msg($meldung);
								}
								?>
								<tr valign="top" align="center">
									<th width="1%">&nbsp; </th>
									<th width="86%" align="left"><a href="<? echo $PHP_SELF ?>?sortby=Name&view=<? echo $view?>">Name</a></th>
									<th width="10%"><b>Inhalt</b></th>
									<?
									if ($view=="ext") { 
									?>
										<th width="10%"><b>&nbsp;besucht&nbsp;</b></th>
										<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=inst_perms&view=<? echo $view?>">&nbsp;Status&nbsp;</a></th>
									<? }?>
									<th width="3%"><b>&nbsp;X&nbsp;</b></th>
								</tr>
	<?
	ob_end_flush(); //Buffer leeren, damit der Header zu sehen ist
	ob_start();
	 while ($db->next_record()){
			$my_inst[$db->f("Institut_id")]=array(name=>$db->f("Name"),status=>$db->f("inst_perms"),type=>($db->f("type")) ? $db->f("type") : 1);
			$value_list.="('".$db->f("Institut_id")."',0".$loginfilenow[$db->f("Institut_id")]."),";
			if ($db->f("is_fak") && $db->f("inst_perms") == "admin"){
				$db2->query("SELECT a.Institut_id, a.Name, a.type FROM Institute a 
					 WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND a.Institut_id!='" .$db->f("Institut_id") . "' 
					 ORDER BY $sortby");
				while($db2->next_record()){
					$my_inst[$db2->f("Institut_id")]=array(name=>$db2->f("Name"),status=>"admin",type=>($db2->f("type")) ? $db2->f("type") : 1);
					$value_list.="('".$db2->f("Institut_id")."',0".$loginfilenow[$db2->f("Institut_id")]."),";
			
				}
			}
	 }
	 $value_list=substr($value_list,0,-1);
	 $db->query("CREATE  TEMPORARY TABLE IF NOT EXISTS loginfilenow_".$user->id." ( Seminar_id varchar(32) NOT NULL PRIMARY KEY, loginfilenow int(11) NOT NULL DEFAULT 0, INDEX(loginfilenow) ) TYPE=HEAP");
	 $ins_query="REPLACE INTO loginfilenow_".$user->id." (Seminar_id, loginfilenow) VALUES ".$value_list;
	 $db->query($ins_query);
	 get_my_inst_values($my_inst);
	 $db->query("DROP TABLE loginfilenow_".$user->id);

  foreach ($my_inst as $instid=>$values){

		$cssSw->switchClass();
		$lastVisit = $loginfilenow[$instid];
		ECHO "<tr ".$cssSw->getHover().">";
		ECHO "<td class=\"".$cssSw->getClass()."\">&nbsp; </td>";
// Name-field		
		ECHO "<td class=\"".$cssSw->getClass()."\"><a href=\"institut_main.php?auswahl=$instid\">";
		ECHO "<font size=-1>".htmlReady($INST_TYPE[$values["type"]]["name"] . ": " . $values["name"])."</font>";
		print ("</a></td>");
// Content-field
		echo "<td class=\"".$cssSw->getClass()."\"  align=\"left\" nowrap>";
		print_institut_content($instid, $values);
		echo "</td>";

// Extendet views:

	// last visited-field
		IF ($view=="ext") {
			IF ($loginfilenow[$instid]==0) {
				echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>n.b.</font></td>";
			} ELSE  {
				 echo "<td class=\"".$cssSw->getClass()."\"align=\"center\" nowrap><font size=-1>", date("d.m.", $loginfilenow[$instid]),"</font></td>";
			}
	// Status-field
		echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>". $values["status"]."&nbsp;</font></td>";
		}

// delete Entry from List:
		if (($values["status"]=="dozent") || ($values["status"]=="tutor") || ($values["status"]=="admin") || ($values["status"]=="autor"))
			echo "<td class=\"".$cssSw->getClass()."\" align=center>&nbsp;</td>";
		else
			printf("<td class=\"".$cssSw->getClass()."\" align=center align=center><a href=\"$PHP_SELF?auswahl=%s&cmd=kill\"><img src=\"pictures/trash.gif\" ".tooltip("aus der Einrichtung austragen")." border=\"0\"></a></td>", $instid);
		 echo "</tr>\n";
		}
	 } else {
	 ?>
	 <tr>
	 	<td class="blank" colspan="2">&nbsp; 
	 	</td>
	 </tr>
	 <tr>
		 <td valign="top" class="blank">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
				<?
				if ($meldung)	{
		//			echo "<tr><td>&nbsp; </td></tr>";
					parse_msg($meldung);
				}
	}

//Info-field on the right side
?>
	</table>
		</td>
			<td class="blank" width="270" valign="top">


<?


	$db->query("SELECT count(*) as count  FROM Institute");
	$db->next_record();
	$anzahltext = "Es sind ".($db->f("count")-$num_my_sem)." weitere Einrichtungen vorhanden.";
	
IF (!$perm->have_perm("dozent")) {
	$infobox = array	(			
	array  ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => $anzahltext
								)
		)
	),
	array  ("kategorie" => "Aktionen:",
	       "eintrag" => array	(	
						array	 (	"icon" => "pictures/suchen.gif",
								"text"  => "Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die <a href=\"institut_browse.php\">Suchfunktion.</a>"
								),
						array	 (	"icon" => "pictures/meinesem.gif",
								"text"  => "Wenn Sie weitere Einrichtungen in ihre pers&ouml;nliche Auswahl aufzunehmen m&ouml;chten, k&ouml;nnen sie sich hier <a href=\"edit_about.php?view=Karriere#einrichtungen\">zuordnen.</a>"
								)
			)
		)
	);
}

ELSE {
	$db->query("SELECT count(*) as count  FROM Institute");
	$db->next_record();
	$anzahltext = "Es sind ".($db->f("count")-$num_my_sem)." weitere Einrichtungen vorhanden.";


	$infobox = array	(			
	array  ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => $anzahltext
								)
		)
	),
	array  ("kategorie" => "Aktionen:",
	       "eintrag" => array	(	
						array	 (	"icon" => "pictures/suchen.gif",
								"text"  => "Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die <a href=\"institut_browse.php\">Suchfunktion.</a>"
								)
			)
		)
	);
 }


// print the info_box

print_infobox ($infobox,"pictures/einrichtungen.jpg");

?>				
				<br />
			</td>
		</tr>
	  </table>
     </td>
    </tr>

<?
}
// Save data back to database.
ob_end_flush(); //Outputbuffering beenden
page_close();
 ?>
</table>
<!-- $Id$ -->
