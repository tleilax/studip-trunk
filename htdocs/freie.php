<?php
/*
index.php - Startseite von Stud.IP (anhaengig vom Status)
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));


function get_my_sem_values(&$my_sem) {
	 $db2 = new DB_seminar;
	 $my_semids="('".implode("','",array_keys($my_sem))."')";
// Postings
	 $db2->query ("SELECT Seminar_id,count(*) as count FROM px_topics WHERE Seminar_id IN ".$my_semids." GROUP BY Seminar_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("Seminar_id")]["postings"]=$db2->f("count");
	 }
//dokumente
	 $db2->query ("SELECT seminar_id , count(*) as count FROM dokumente WHERE seminar_id IN ".$my_semids." GROUP BY seminar_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("seminar_id")]["dokumente"]=$db2->f("count");
	 }
//News
	 $db2->query ("SELECT range_id,count(*) as count  FROM news_range  LEFT JOIN news USING(news_id) WHERE range_id IN ".$my_semids." GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("range_id")]["news"]=$db2->f("count");
	 }
// Literatur?
	 $db2->query ("SELECT range_id,chdate,user_id FROM literatur WHERE range_id IN ".$my_semids);
	while($db2->next_record()) {
	 $my_sem[$db2->f("range_id")]["literatur"]=TRUE;
	 }
//termine
	 $db2->query ("SELECT range_id,count(*) as count FROM termine WHERE range_id IN ".$my_semids." GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("range_id")]["termine"]=$db2->f("count");
	 }
	 return;
}  // Ende function get_my_sem_values


function print_seminar_content($semid,$my_sem_values) {
  // Postings
  IF ($my_sem_values["postings"]) ECHO "<a href=\"seminar_main.php?auswahl=$semid&redirect_to=forum.php\">&nbsp; <img src='pictures/icon-posting.gif' border=0 alt='".$my_sem_values["postings"]." Postings'></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";
  //Dokumente
  IF ($my_sem_values["dokumente"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=folder.php&cmd=tree\"><img src='pictures/icon-disc.gif' border=0 alt='".$my_sem_values["dokumente"]." Dokumente'></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //News
  IF ($my_sem_values["news"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid\"><img src='pictures/icon-news.gif' border=0 alt='".$my_sem_values["news"]." News'></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //Literatur
  IF ($my_sem_values["literatur"]) {
    ECHO "<a href=\"seminar_main.php?auswahl=$semid&redirect_to=literatur.php\">";
		ECHO "&nbsp; <img src=\"pictures/icon-lit.gif\" border=0 alt='Zur Literatur und Linkliste'></a>";
  }
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  // Termine
  IF ($my_sem_values["termine"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=dates.php\"><img src='pictures/icon-uhr.gif' border=0 alt='".$my_sem_values["termine"]." Termine'></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  echo "&nbsp;&nbsp;";

} // Ende function print_seminar_content

$_language_domain = "studip_core";
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
// -- wir sind jetzt definitiv in keinem Seminar, also... --
$SessSemName[0] = "";
$SessSemName[1] = "";

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");


$db=new DB_Seminar;
$db2=new DB_Seminar;

  if (!isset($sortby)) $sortby="Name";
	$db->query("SELECT seminare.*, Institute.Name AS Institut, Institute.Institut_id AS id FROM seminare LEFT JOIN Institute USING (institut_id) WHERE Lesezugriff='0' ORDER BY $sortby");
	$num_my_sem=$db->num_rows();
  if (!$num_my_sem) $meldung="error�". _("Es gibt keine Veranstaltungen, die einen freien Zugriff erlauben!")."�".$meldung;

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0 align=center>
<tr><td class="topic" colspan="2">&nbsp;<b><? echo _("&Ouml;ffentliche Veranstaltungen"); echo " - "; echo $UNI_NAME ?></b></td></tr>
<tr><td class="blank" width="99%"><br>
<?
	print("<blockquote>");
	print( _("Die folgenden Veranstaltungen k&ouml;nnen Sie betreten, ohne sich im System registriert zu haben."));
	print("<br></blockquote>");
	print("<blockquote>");
	printf( _("In den %s blau markierten Veranstaltungen d&uuml;rfen Sie nur Lesen und Dokumente herunterladen."), "<font class=\"gruppe6\">&nbsp;&nbsp;</font>");
	print("<br>");
	printf( _("In den %s orange markierten Veranstaltungen k&ouml;nnen Sie sich zus&auml;tzlich mit eigenen Beitr&auml;gen im Forum beteiligen."), "<font class=\"gruppe2\">&nbsp;&nbsp;</font>");
	print("</blockquote>");
	print("<blockquote>");
	print( _("In der rechten Spalte erfahren Sie, was in den einzelnen Veranstaltungen an Inhalten vorhanden ist."));
	print("</blockquote>");
?>
	</td>
	<td class="blank"  width="1%" align="right" valign="top"><img src="pictures/board1.jpg" border="0"></td>
</tr>

<tr>
	<td class="blank" width="100%" colspan="2">&nbsp;
		<?
		if ($meldung) parse_msg($meldung);
		?>
	</td>
</tr>

<?php
//Anzeigemodul fuer freie Seminare

if ($num_my_sem){
?>
	<tr><td colspan="2">
	<table border="0" cellpadding="2" cellspacing="0" width="100%" align="center">
	<tr valign"top" align="center">
		<th width="2%" colspan=2>&nbsp;</th>
		<th width="70%"><a href="<? echo $PHP_SELF . "?sortby=Name\">" . _("Name")?></a></th>
		<th width="70%"><a href="<? echo $PHP_SELF . "?sortby=status\">" . _("Veranstaltungstyp")?></a></th>
		<th width="70%"><a href="<? echo $PHP_SELF . "?sortby=Institut\">" . _("Einrichtung")?></a></th>
		<th width="10%"><? echo _("Inhalt") ?></th>
	</tr>
	<?

	while ($db->next_record())
		$my_sem[$db->f("Seminar_id")]=array("name"=>$db->f("Name"),"status"=>$db->f("status"),"Institut"=>$db->f("Institut"),"id"=>$db->f("id"),"Schreibzugriff"=>$db->f("Schreibzugriff"));

  get_my_sem_values(&$my_sem);
	$c=1;

  foreach ($my_sem as $semid=>$values){
	  if ($c % 2)
			$class="steel1";
		else
			$class="steelgraulight"; 
		$c++;
		print "<tr>";
		if ($values["Schreibzugriff"])
			print "<td class=\"gruppe6\">&nbsp;</td>";
		else
			print "<td class=\"gruppe2\">&nbsp;</td>";
		print "<td class=\"$class\" align=\"center\">&nbsp;</td>";
		printf ("<td class=\"$class\"><a href=\"seminar_main.php?auswahl=$semid\">%s</a></td>", $values["name"]);
		printf ("<td class=\"$class\" align=\"center\">&nbsp;%s&nbsp;</td>", $SEM_TYPE[$values["status"]]["name"]);
		printf ("<td class=\"$class\" align=\"center\"><a href=\"institut_main.php?auswahl=%s\">&nbsp;%s&nbsp;</a></td>", $values["id"], htmlReady($values["Institut"]));
// Inhalt
		print "<td class=\"$class\" align=\"left\" nowrap>";
		print_seminar_content($semid, $values);
		print "</td>";
	}
	echo "</tr></table>";  // Ende der Anzeige-Tabelle
	echo "</td></tr>";
}  // Ende des Anzeige-Moduls bei vorhandenen freien Veranstaltungen
?>

</table>
</body>
</html>
<?php
  // Save data back to database.
  page_close()
 ?>
<!-- $Id$ -->