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
	 global $user,$loginfilenow;
	 $db2 = new DB_seminar;
	 $my_instids="('".implode("','",array_keys($my_inst))."')";
// Postings
	 $db2->query ("SELECT Seminar_id,count(*) as count FROM px_topics WHERE Seminar_id IN ".$my_instids." GROUP BY Seminar_id");
	 while($db2->next_record()) {
	 $my_inst[$db2->f("Seminar_id")]["postings"]=$db2->f("count");
	 }
	 $db2->query ("SELECT a.Seminar_id,count(*) as count FROM px_topics a LEFT JOIN loginfilenow_".$user->id." b USING (Seminar_id) WHERE a.Seminar_id IN ".$my_instids." AND chdate > b.loginfilenow AND user_id !='$user->id' GROUP BY a.Seminar_id");
	 while($db2->next_record()) {
	 $my_inst[$db2->f("Seminar_id")]["neuepostings"]=$db2->f("count");
	 }

//dokumente
	 $db2->query ("SELECT seminar_id , count(*) as count FROM dokumente WHERE seminar_id IN ".$my_instids." GROUP BY seminar_id");
	 while($db2->next_record()) {
	 $my_inst[$db2->f("seminar_id")]["dokumente"]=$db2->f("count");
	 }
	 $db2->query ("SELECT a.seminar_id , count(*) as count  FROM dokumente a LEFT JOIN loginfilenow_".$user->id." b USING (seminar_id) WHERE a.seminar_id IN ".$my_instids." AND chdate > b.loginfilenow AND user_id !='$user->id' GROUP BY a.seminar_id");
	 while($db2->next_record()) {
	 $my_inst[$db2->f("seminar_id")]["neuedokumente"]=$db2->f("count");
	 }

//News
	 $db2->query ("SELECT range_id,count(*) as count  FROM news_range  LEFT JOIN news USING(news_id) WHERE range_id IN ".$my_instids." GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_inst[$db2->f("range_id")]["news"]=$db2->f("count");
	 }
	 $db2->query ("SELECT range_id,count(*) as count  FROM news_range LEFT JOIN news  USING(news_id)  LEFT JOIN loginfilenow_".$user->id." b ON (b.Seminar_id=range_id) WHERE range_id IN ".$my_instids." AND date > b.loginfilenow AND user_id !='$user->id' GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_inst[$db2->f("range_id")]["neuenews"]=$db2->f("count");
	 }
// Literatur?
	 $db2->query ("SELECT range_id,chdate,user_id FROM literatur WHERE range_id IN ".$my_instids);
	while($db2->next_record()) {
	  if ($db2->f("chdate")>$loginfilenow[$db2->f("range_id")] AND $db2->f("user_id")!=$user->id){
		$my_inst[$db2->f("range_id")]["neueliteratur"]=TRUE;
		$my_inst[$db2->f("range_id")]["literatur"]=TRUE;
		}
	 else $my_inst[$db2->f("range_id")]["literatur"]=TRUE;
	 }
	 $db2->query ("SELECT range_id,count(*) as count FROM termine WHERE range_id IN ".$my_instids." GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_inst[$db2->f("range_id")]["termine"]=$db2->f("count");
	 }
	 $db2->query ("SELECT range_id,count(*) as count  FROM termine LEFT JOIN loginfilenow_".$user->id." b ON (b.Seminar_id=range_id) WHERE range_id IN ".$my_instids." AND chdate > b.loginfilenow AND autor_id !='$user->id' GROUP BY range_id");
	  while($db2->next_record()) {
	 $my_inst[$db2->f("range_id")]["neuetermine"]=$db2->f("count");
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


include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php");		 //hier werden die sessions initialisiert

require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php"); 		// Klarnamen fuer den Veranstaltungsstatus
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php"); 		// htmlReady fuer die Veranstaltungsnamen
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); 		// Semester-Namen fuer Admins

$cssSw=new cssClassSwitcher;                          					// Klasse f�r Zebra-Design
$cssSw->enableHover();
$db=new DB_Seminar;

// we are defintely not in an lexture or institute$SessSemName[0] = "";
$SessSemName[0] = "";
$SessSemName[1] = "";
$links_admin_data =''; 	//Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

?>

<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
  <title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
 </head>
<body>

<? echo "\n".cssClassSwitcher::GetHoverJSFunction()."\n";

include ("$ABSOLUTE_PATH_STUDIP/header.php");   			//hier wird der "Kopf" nachgeladen
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
IF ($auth->is_authenticated() && $user->id != "nobody" && !$perm->have_perm("root")){

	 if (!isset($sortby)) $sortby="Name";
	 if ($sortby == "count")
	 $sortby = "count DESC";
	$db->query ("SELECT Institute.Name, Institute.Institut_id, user_inst.inst_perms FROM user_inst LEFT JOIN Institute  USING (Institut_id) WHERE user_inst.user_id = '$user->id' GROUP BY Institut_id ORDER BY $sortby");
	$num_my_inst=$db->num_rows();
	 if (!$num_my_inst)
	 	if ($perm->have_perm("dozent"))
	 		$meldung="info�Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zust&auml;ndigen <a href=\"impressum.php?view=ansprechpartner\">Administratoren</a>.�".$meldung;
	 	else
			$meldung="info�Sie haben sich noch keinen Einrichtungen zugeordnet. Um sich Einrichtungen zuzuordnen, nutzen Sie bitte die entsprechende <a href=\"edit_about.php?view=Karriere#einrichtungen\">Option</a> unter \"universt&auml;re Daten\" in ihren pers&ouml;nlichen Einstellungen.�".$meldung;
	 ?>
	 <table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan="2"><img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b>Meine Einrichtungen</></td>
	</tr>
	<?
	if ($meldung) parse_msg($meldung);
	?>
	 <?
	 if ($num_my_inst){
	 ?>
	 	<tr>
	 		<td valign="top" class="blank">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
					<tr valign="top" align="center">
						<td align="center">
							<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center" class="blank">
								<tr>
									<td class="blank" colspan="2">&nbsp;
									
									</td>
								</tr>
								<tr valign="top" align="center">
									<th width="1%">&nbsp; </th>
									<th width="86%" align="left"><a href="<? echo $PHP_SELF ?>?sortby=Name">Name</a></th>
									<th width="10%"><b>Inhalt</b></th>
									<?
									if ($view=="ext") { 
									?>
										<th width="10%"><b>&nbsp;besucht&nbsp;</b></th>
										<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=status">&nbsp;Status&nbsp;</a></th>
									<? }?>
									<th width="3%"><b>&nbsp;X&nbsp;</b></th>
								</tr>
	<?
	ob_end_flush(); //Buffer leeren, damit der Header zu sehen ist
	ob_start();
	 while ($db->next_record())
		{
	  $my_inst[$db->f("Institut_id")]=array(name=>$db->f("Name"),status=>$db->f("inst_perms"));
	  $value_list.="('".$db->f("Institut_id")."',0".$loginfilenow[$db->f("Institut_id")]."),";
	 }
	 $value_list=substr($value_list,0,-1);
	 $db->query("CREATE  TEMPORARY TABLE IF NOT EXISTS loginfilenow_".$user->id." ( Seminar_id varchar(32) NOT NULL PRIMARY KEY, loginfilenow int(11) NOT NULL DEFAULT 0, INDEX(loginfilenow) ) TYPE=HEAP");
	 $ins_query="REPLACE INTO loginfilenow_".$user->id." (Seminar_id, loginfilenow) VALUES ".$value_list;
	 $db->query($ins_query);
	 get_my_inst_values($my_inst);
	 $db->query("DROP TABLE loginfilenow_".$user->id);

  foreach ($my_inst as $instid=>$values){

		$cssSw->switchClass();
		$lastVisit = $loginfilenow[$semid];
		ECHO "<tr ".$cssSw->getHover().">";
		ECHO "<td class=\"".$cssSw->getClass()."\">&nbsp; </td>";
// Name-field		
		ECHO "<td class=\"".$cssSw->getClass()."\"><a href=\"institut_main.php?auswahl=$instid\">";
		ECHO "<font size=-1>".htmlReady($values["name"])."</font>";
		print ("</a></td>");
// Content-field
		echo "<td class=\"".$cssSw->getClass()."\"  align=\"left\" nowrap>";
		print_institut_content($instid, $values);
		echo "</td>";

// Extendet views:

	// last visited-field
		IF ($view=="ext") {
			IF ($loginfilenow[$instid]==0) {
				echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>nicht besucht</font></td>";
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
		 <td valign="top" class="blank">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
				<?
				if ($meldung)	{
					echo "<tr><td><br />";
					parse_msg($meldung);
					echo "</td></tr>"; 
				}
	}

//Info-field on the right side
?>
	</table>
		</td>
			<td class="blank">
				&nbsp;&nbsp;
			</td>
			<td class="blank" width="240" valign="top">
				<table "center" width="100%" border=0 cellpadding=0 cellspacing=0>
					<? if ($meldung) {
						echo "<tr><td><br />";
						parse_msg($meldung);
						echo "</td></tr>"; 
					}
					?>
					<tr>
						<td class="blank" width="100%" align="right" colspan=2>
							<img src="pictures/einrichtungen.jpg">
						</td>
					</tr>
					<tr>
						<td class="angemeldet" width="100%" colspan=2>
							<table "center" width="99%" border=0 cellpadding=4 cellspacing=0>
								<tr>
									<td class="blank" width="100%" colspan=2>
										<font size=-1><b><? print "Information" ?>:</b></font>
										<br>
									</td>
								</tr>
								<tr>
									<td width="1%" valign="top">
										<img src="./pictures/ausruf_small.gif">
									</td>
									<td class="blank" width="100%">
									 	<?  $db->query("SELECT count(*) as count  FROM Institute");
										 $db->next_record();?>
										<font size=-1>Es sind <? echo ($db->f("count")-$num_my_sem) ?> weitere Einrichtungen vorhanden.</font><br>
									</td>
								</tr>
								<tr>
									<td class="blank" width="100%" colspan=2>
										<font size=-1><b><? print "Aktionen" ?>:</b></font>
										<br>
									</td>
								</tr>
								<tr>
									<td width="1%" valign="top">
										<img src="./pictures/suchen.gif">
									</td>
									<td class="blank" width="100%">
										<font size=-1>Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die <a href="institut_browse.php">Suchfunktion.</a><br /></font>
									</td>
								</tr>
							<?  if (!$perm->have_perm("dozent")) {  ?>
								<tr>
									<td width="1%" valign="top">
										<img src="./pictures/einst.gif">
									</td>
									<td class="blank" width="100%">
										<? echo "<font size=-1>Wenn Sie weitere Einrichtungen in ihre pers&ouml;nliche Auswahl aufzunehmen m&ouml;chten, k&ouml;nnen sie sich hier <a href=\"edit_about.php?view=Karriere#einrichtungen\">zuordnen.</a></font>"; ?>
									</td>
								</tr>  
							 <? }  ?>
							</table>
						</td>
					</tr>
				</table>
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