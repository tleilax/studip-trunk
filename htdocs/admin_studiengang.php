<?php
/*
admin_studiengang.php - Studiengang-Verwaltung von Stud.IP.
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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


## straight from the Seminars...
  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
  $perm->check("root");

## Set this to something, just something different...
  $hash_secret = "dudeldoe";
  
## If is set 'cancel', we leave the adminstration form...
 if (isset($cancel)) unset ($i_view);


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


<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert

// hier muessen Seiten-Initialisierungen passieren

	include "header.php";   //hier wird der "Kopf" nachgeladen 
?>
<body>

<?php
	include "links_admin.inc.php";  //Linkleiste fuer admins
	require_once ("msg.inc.php"); //Funktionen fuer Nachrichtenmeldungen
	require_once ("visual.inc.php");
	
	$cssSw=new cssClassSwitcher;
?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<tr valign=top align=middle>
	<td class="topic"colspan=2 align="left"><b>&nbsp;Verwaltung der F&auml;cher</b></td>
</tr>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>


<?php

###
### Submit Handler
###

## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;

## Check if there was a submission


while ( is_array($HTTP_POST_VARS) 
     && list($key, $val) = each($HTTP_POST_VARS)) {
  switch ($key) {
  

  ## Neuer Studiengang
  case "create":
    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben sie eine Bezeichnug f&uuml;r das Fach ein!</b>");
      break;
    }
    
    ## Does the Studiengang already exist?
    ## NOTE: This should be a transaction, but it isn't...
    $db->query("SELECT * FROM studiengaenge WHERE name='$Name'");
    if ($db->nf()>0) {
      my_error(" <b>Der Studiengang \"".htmlReady(stripslashes($Name))."\" existiert bereits!");
      break;
    }

    ## Create an id
    $i_id=md5(uniqid($hash_secret));

		## Einfuegen des Faches

    $query = "INSERT INTO studiengaenge VALUES('$i_id','$Name','$Beschreibung', '".time()."', '".time()."') ";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: $query </b>");
      break;
    }
    ELSE {
         unset($i_view);  // gibt keine Detailansicht
         my_msg("<b>Der Studiengang \"".htmlReady(stripslashes($Name))."\" wurde angelegt!");
         break;
    }

  ## Change Studiengangname
  case "i_edit":

    ## Do we have all necessary data?
    if (empty($Name)) {
      my_error("<b>Bitte geben Sie eine Bezeichnug f&uuml;r den Studiengang ein!</b>");
      break;
    }
		
    ## Update Studiengang information.
    $query = "UPDATE studiengaenge SET name='$Name', beschreibung='$Beschreibung' WHERE studiengang_id = '$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
        unset($i_view);  // wurde nix ver�ndert...
        my_msg("<b>Keine �nderungen am Studiengang \"".htmlReady(stripslashes($Name))."\" durchgef�hrt.</b>");
        break;
	}
    else
    	$db->query("UPDATE studiengaenge SET chdate='".time()."' ");
    
    my_msg("<b>Die Daten des Studiengangs \"".htmlReady(stripslashes($Name))."\" wurden ver&auml;ndert.</b>");
    unset($i_view);  // gibt keine Detailansicht
  break;

  ## Delete the Studiengang
  
  // diese Passage w�re zu diskutieren. Darf man Studieng�nge l�schen, denen sich Studis bereits zugeordnet haben?
  // Zur Vorsicht erst mal dringelassen.

  case "i_kill":
    ## Studiengang in use?
		$db->query("SELECT * FROM user_studiengang WHERE studiengang_id = '$i_id'");
    if ($db->next_record()) {
      my_error("<b>Dieser Studiengang kann nicht gel&ouml;scht werden, da sich noch Studierende zugeordnet haben!</b>");
      break;
    }
    
    ## Delete that Fach
    $query = "DELETE FROM studiengaenge WHERE studiengang_id='$i_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      my_error("<b>Datenbankoperation gescheitert: </b> $query</b>");
      break;
    }
    
    unset($i_view);  // gibt keine Detailansicht
    my_msg("<b>Der Studiengang \"".htmlReady(stripslashes($Name))."\" wurde gel&ouml;scht!");
    break;

    default:
    break;
 }
}


//Anzeige der Studiengangdaten; das tatseachliche Aenderungsmodul

if ($i_view){
    if ($i_view<>"new") {
      $db->query("SELECT studiengaenge.*, count(user_studiengang.user_id) AS number FROM studiengaenge LEFT JOIN user_studiengang USING(studiengang_id) WHERE studiengaenge.studiengang_id = '$i_view' GROUP BY studiengang_id");
      $db->next_record();
    }
    $i_id= $db->f("studiengang_id");

  ?>
    <tr><td class="blank" colspan=2>
    <table border=0 bgcolor="#eeeeee" align="center" width="50%" cellspacing=0 cellpadding=5>
	<form method="POST" name="edit" action="<? echo $PHP_SELF?>">
	<tr><td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>">Studiengangname: </td><td class="<? echo $cssSw->getClass() ?>"><input type="text" name="Name" size=60 maxlength=254 value="<?php echo htmlReady($db->f("name")) ?>"></td></tr>
	<tr><td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>">Beschreibung: </td><td class="<? echo $cssSw->getClass() ?>"><textarea cols=50 ROWS=4 name="Beschreibung" value="<?php $db->p("beschreibung") ?>"><?php echo htmlReady($db->f("beschreibung")) ?></textarea></td></tr>
	<tr><td class="<? echo $cssSw->getClass() ?>"colspan=2 align="center">
	
  <?
	if ($i_view<>"new") {
		if ($db->f("number") < 1):
			?>
			<input type="submit" name="i_kill" value=" L&ouml;schen ">
			<?
		endif;
		?>
		<input type="submit" name="i_edit" value=" Ver&auml;ndern ">
		<input type="hidden" name="i_id"   value="<?php $db->p("studiengang_id") ?>">
		<?
	}
	else {
	     echo "<input type=\"submit\" name=\"create\" value=\"Anlegen\">";
	}
	?>
	<input type="submit" name="cancel" value=" Abbrechen ">
	<input type="hidden" name="i_view" value="<? echo $i_view; ?>">
  	</form></td></tr></table>
	<br><br>

 
<?
}

### Output Studiengang administration forms, including all updated
### information, if we come here after a submission...

if (!$i_view) {
?>
  <tr><td class="blank" colspan=2><b><a href="<?echo $PHP_SELF?>?i_view=new">&nbsp;Neuen Studiengang anlegen</a><b><br><br></td></tr>
  <tr><td class="blank" colspan=2>
  <table align=center bg="#ffffff" width="80%" border=0 cellpadding=2 cellspacing=1>
  <tr valign=top align=middle>
  <th width="80%"><a href="<?echo $PHP_SELF?>?sortby=name">Name des Studiengangs</a></th>
  <th width="20%"><a href="<?echo $PHP_SELF?>?sortby=number">Anzahl der Studierenden</a></th>
  </tr>
<?  
  ## nachsehen, ob wir ein Sortierkriterium haben, sonst nach Name
  if (!isset($sortby) || $sortby=="") $sortby = "name";
  ## Traverse the result set
  $db->query("SELECT studiengaenge.*, count(user_studiengang.studiengang_id) AS number FROM studiengaenge LEFT JOIN user_studiengang USING(studiengang_id) GROUP BY studiengang_id ORDER BY $sortby");
  while ($db->next_record()) {        //Aufbauen der &Uuml;bersichtstabelle
?>
           <!-- existing Institut -->
           <tr valign=middle align=left>
           <td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>"><a href="<?echo $PHP_SELF?>?i_view=<?$db->p("studiengang_id")?>">&nbsp;<?php echo htmlReady($db->f("name")) ?></a></td>
           <td class="<? echo $cssSw->getClass() ?>" align=center>&nbsp;<?php $db->p("number") ?></td>
           </tr>
           <?php
  }
?>
  </table><br><br>
  </td></tr>
  <?php
}

echo"</table>";
page_close();
?>
</body>
</html>
<!-- $Id$ -->
