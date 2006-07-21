<?php
/*
new_user_md5.php - die globale Benutzerverwaltung von Stud.IP.
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA	02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("admin");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); 		// initialise Stud.IP-Session
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php"); 		// Funktionen fuer Nachrichtenmeldungen
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php"); 		// Wir brauchen den Namen der Uni
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/user_visible.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/UserManagement.class.php");

$cssSw=new cssClassSwitcher;

//-- hier muessen Seiten-Initialisierungen passieren --

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");	 //hier wird der "Kopf" nachgeladen

include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");	//Linkleiste fuer admins


// Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;

// Check if there was a submission
if (check_ticket($studipticket)){
	while ( is_array($HTTP_POST_VARS)
			 && list($key, $val) = each($HTTP_POST_VARS)) {
		switch ($key) {

		// Create a new user
		case "create_x":

			$UserManagement = new UserManagement;

			if (!$title_front)
				$title_front = $title_front_chooser;
			if (!$title_rear)
				$title_rear = $title_rear_chooser;

			$newuser = array(	'auth_user_md5.username' => stripslashes(trim($username)),
												'auth_user_md5.Vorname' => stripslashes(trim($Vorname)),
												'auth_user_md5.Nachname' => stripslashes(trim($Nachname)),
												'auth_user_md5.Email' => stripslashes(trim($Email)),
												'auth_user_md5.perms' => implode($perms,","),
												'user_info.title_front' => stripslashes(trim($title_front)),
												'user_info.title_rear' => stripslashes(trim($title_rear)),
												'user_info.geschlecht' => stripslashes(trim($geschlecht)),
											);

			$UserManagement->createNewUser($newuser);

			break;


		// Change user parameters
		case "u_edit_x":

			$UserManagement = new UserManagement($u_id);

			$newuser = array();
			if (isset($username))
				$newuser['auth_user_md5.username'] = stripslashes(trim($username));
			if (isset($Vorname))
				$newuser['auth_user_md5.Vorname'] = stripslashes(trim($Vorname));
			if (isset($Nachname))
				$newuser['auth_user_md5.Nachname'] = stripslashes(trim($Nachname));
			if (isset($Email))
				$newuser['auth_user_md5.Email'] = stripslashes(trim($Email));
			if (isset($perms))
				$newuser['auth_user_md5.perms'] = implode($perms,",");

			$newuser['auth_user_md5.locked']     = (isset($locked) ? $locked : 0);
			$newuser['auth_user_md5.lock_comment']    = (isset($lock_comment) ? stripslashes(trim($lock_comment)) : "");
			$newuser['auth_user_md5.locked_by'] = ($locked==1 ? $auth->auth["uid"] : "");

		if (isset($visible))
			$newuser['auth_user_md5.visible'] = $visible;
			if (isset($title_front) || isset($title_front_chooser)) {
				if (!$title_front)
					$title_front = $title_front_chooser;
				$newuser['user_info.title_front'] = stripslashes(trim($title_front));
			}
			if (isset($title_rear) || isset($title_rear_chooser)) {
				if (!$title_rear)
					$title_rear = $title_rear_chooser;
				$newuser['user_info.title_rear'] = stripslashes(trim($title_rear));
			}
			if (isset($geschlecht))
				$newuser['user_info.geschlecht'] = stripslashes(trim($geschlecht));

			$UserManagement->changeUser($newuser);

			break;


		// Change user password
		case "u_pass_x":

			$UserManagement = new UserManagement($u_id);

			$UserManagement->setPassword();

			break;


		// Delete the user
		case "u_kill_x":

			$UserManagement = new UserManagement($u_id);

			$UserManagement->deleteUser();

			break;


		default:
			break;
		}
	}
}

// einzelnen Benutzer anzeigen
if (isset($_GET['details'])) {
	if ($details=="__" && in_array("Standard",$GLOBALS['STUDIP_AUTH_PLUGIN'])) { // neuen Benutzer anlegen
		?>
		<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
		<tr valign=top align=middle>
			<td class="topic" colspan=2 align="left"><b>&nbsp;<?=_("Eingabe eines neuen Benutzer-Accounts")?></b></td>
		</tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<tr><td class="blank" colspan=2>

			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?php echo $PHP_SELF ?>">
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Benutzername:")?></b></td>
					<td>&nbsp;<input type="text" name="username" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("globaler Status:")?>&nbsp;</b></td>
					<td>&nbsp;<? print $perm->perm_sel("perms", $db->f("perms")) ?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Sichtbarkeit")?>&nbsp;</b></td>
					<td>&nbsp;&nbsp;<?=vis_chooser($db->f("visible")) ?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Vorname:")?></b></td>
					<td>&nbsp;<input type="text" name="Vorname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Nachname:")?></b></td>
					<td>&nbsp;<input type="text" name="Nachname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
				<td><b>&nbsp;<?=_("Titel:")?></b>
				</td><td align="right"><select name="title_front_chooser" onChange="document.edit.title_front.value=document.edit.title_front_chooser.options[document.edit.title_front_chooser.selectedIndex].text;">
				<?
				for($i = 0; $i < count($TITLE_FRONT_TEMPLATE); ++$i){
					echo "\n<option>$TITLE_FRONT_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td>&nbsp;<input type="text" name="title_front" value="" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td><b>&nbsp;<?=_("Titel nachgest.:")?></b>
				</td><td align="right"><select name="title_rear_chooser" onChange="document.edit.title_rear.value=document.edit.title_rear_chooser.options[document.edit.title_rear_chooser.selectedIndex].text;">
				<?
				for($i = 0; $i < count($TITLE_REAR_TEMPLATE); ++$i){
					echo "\n<option>$TITLE_REAR_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td>&nbsp;<input type="text" name="title_rear" value="" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td colspan="2"><b>&nbsp;<?=_("Geschlecht:")?></b></td>
				<td>&nbsp;<input type="RADIO" checked name="geschlecht" value="0"><?=_("m&auml;nnlich")?>&nbsp;
				<input type="RADIO" name="geschlecht" value="1"><?=_("weiblich")?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("E-Mail:")?></b></td>
					<td>&nbsp;<input type="text" name="Email" size=48 maxlength=63 value="">&nbsp;</td>
				</tr>
				<td colspan=3 align=center>&nbsp;
				<input type="IMAGE" name="create" <?=makeButton("anlegen", "src")?> value=" <?=_("Benutzer anlegen")?> ">&nbsp;
				<input type="IMAGE" name="nothing" <?=makeButton("abbrechen", "src")?> value=" <?=_("Abbrechen")?> ">
				<input type="hidden" name="studipticket" value="<?=get_ticket();?>">
				&nbsp;</td></tr>
			</form></table>

		</td></tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		</table>
		<?

	} else { // alten Benutzer bearbeiten

		$db->query("SELECT auth_user_md5.*, (changed + 0) as changed_compat, mkdate, title_rear, title_front, geschlecht FROM auth_user_md5 LEFT JOIN ".$GLOBALS['user']->that->database_table." ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) WHERE username ='$details'");
		while ($db->next_record()) {
			if ($db->f("changed_compat") != "") {
				$stamp = mktime(substr($db->f("changed_compat"),8,2),substr($db->f("changed_compat"),10,2),substr($db->f("changed_compat"),12,2),substr($db->f("changed_compat"),4,2),substr($db->f("changed_compat"),6,2),substr($db->f("changed_compat"),0,4));
				$inactive = floor((time() - $stamp) / 3600 / 24)	." " . _("Tagen");
			} else {
				$inactive = _("nie benutzt");
			}
			?>

			<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
			<tr valign=top align=middle>
				<td class="topic" colspan=2 align="left"><b>&nbsp;<?=_("Ver&auml;ndern eines bestehenden Benutzer-Accounts")?></b></td>
			</tr>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>
			<tr><td class="blank" colspan=2>

			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?php echo $PHP_SELF ?>">
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Benutzername:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.username", $db->f('auth_plugin'))) {
						echo $db->f("username");
					} else {
					?><input type="text" name="username" size=24 maxlength=63 value="<?php $db->p("username") ?>"><?
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("globaler Status:")?>&nbsp;</b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.perms", $db->f('auth_plugin'))) {
						echo $db->f("perms");
					} else {
						print $perm->perm_sel("perms", $db->f("perms"));
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Sichtbarkeit:")?>&nbsp;</b></td>
					<td class="steel1">&nbsp;&nbsp;<?=vis_chooser($db->f("visible"))?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Vorname:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.Vorname", $db->f('auth_plugin'))) {
						echo $db->f("Vorname");
					} else {
						?><input type="text" name="Vorname" size=24 maxlength=63 value="<?php $db->p("Vorname") ?>"><?
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Nachname:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.Nachname", $db->f('auth_plugin'))) {
						echo $db->f("Nachname");
					} else {
						?><input type="text" name="Nachname" size=24 maxlength=63 value="<?php $db->p("Nachname") ?>"><?
					}
					?>
					</td>
				</tr>
				<td class="steel1"><b>&nbsp;<?=_("Titel:")?></b>
				</td><td class="steel1" align="right">
				<?
				if (StudipAuthAbstract::CheckField("user_info.title_front", $db->f('auth_plugin'))) {
						echo "&nbsp;</td><td class=\"steel1\">&nbsp;" . $db->f("title_front");
				} else {
				?>
				<select name="title_front_chooser" onChange="document.edit.title_front.value=document.edit.title_front_chooser.options[document.edit.title_front_chooser.selectedIndex].text;">
				<?
				 for($i = 0; $i < count($TITLE_FRONT_TEMPLATE); ++$i){
					 echo "\n<option";
					 if($TITLE_FRONT_TEMPLATE[$i] == $db->f("title_front"))
					 	echo " selected ";
					 echo ">$TITLE_FRONT_TEMPLATE[$i]</option>";
					}
				?>
				</select></td>
				<td class="steel1">&nbsp;<input type="text" name="title_front" value="<?=$db->f("title_front")?>" size=24 maxlength=63>
				<?
				}
				?>
				</td>
				</tr>
				<tr>
				<td class="steel1"><b>&nbsp;<?=_("Titel nachgest.:")?></b>
				</td><td class="steel1" align="right">
				<?
				if (StudipAuthAbstract::CheckField("user_info.title_rear", $db->f('auth_plugin'))) {
						echo "&nbsp;</td><td class=\"steel1\">&nbsp;" . $db->f("title_rear");
				} else {
				?>
				<select name="title_rear_chooser" onChange="document.edit.title_rear.value=document.edit.title_rear_chooser.options[document.edit.title_rear_chooser.selectedIndex].text;">
				<?
				 for($i = 0; $i < count($TITLE_REAR_TEMPLATE); ++$i){
					 echo "\n<option";
					 if($TITLE_REAR_TEMPLATE[$i] == $db->f("title_rear"))
					 	echo " selected ";
					 echo ">$TITLE_REAR_TEMPLATE[$i]</option>";
					}
				?>
				</select></td>
				<td class="steel1">&nbsp;<input type="text" name="title_rear" value="<?=$db->f("title_rear")?>" size=24 maxlength=63>
				<?
				}
				?>
				</td>
				</tr>
				<tr>
				<td colspan="2" class="steel1"><b>&nbsp;<?=_("Geschlecht:")?></b></td>
				<td class="steel1">&nbsp;
				<?
				if (StudipAuthAbstract::CheckField("user_info.geschlecht", $db->f('auth_plugin'))) {
					echo "&nbsp;" . (!$db->f("geschlecht") ? _("m&auml;nnlich") : _("weiblich"));
				} else {
				?>
				<input type="RADIO" <? if (!$db->f("geschlecht")) echo "checked";?> name="geschlecht" value="0"><?=_("m&auml;nnlich")?>&nbsp;
				<input type="RADIO" <? if ($db->f("geschlecht")) echo "checked";?> name="geschlecht" value="1"><?=_("weiblich")?>
				<?
				}
				?>
				</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("E-Mail:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.Email", $db->f('auth_plugin'))) {
						echo $db->f("Email");
					} else {
					?><input type="text" name="Email" size=48 maxlength=63 value="<?php $db->p("Email") ?>">&nbsp;
					<?
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("inaktiv seit:")?></b></td>
					<td class="steel1">&nbsp;<? echo $inactive ?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("registriert seit:")?></b></td>
					<td class="steel1">&nbsp;<? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo _("unbekannt"); ?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Authentifizierung:")?></b></td>
					<td class="steel1">&nbsp;<?=($db->f("auth_plugin") ? $db->f("auth_plugin") : "Standard")?></td>
				</tr>

				<?
				if ($perm->have_perm("root") || ($db->f("perms") != "admin" && $db->f("perms") != "root") || $db2->f("admin_ok")) {

					echo "<tr>\n";
                                	echo "  <td class=\"steel1\"><b>&nbsp;"._("Benutzer sperren:")."</b></td>\n";
                                	echo "  <td class=\"steel1\">\n";
                                	echo "    <INPUT TYPE=\"checkbox\" NAME=\"locked\" VALUE=\"1\" ".($db->f("locked")==1 ? "CHECKED" : "").">"._("sperren")."\n";
                                	echo "  </td>\n";
                                	echo "  <td class=\"steel1\">\n";
                                	echo "    &nbsp;"._("Kommentar:")."&nbsp;\n";
                                	echo "    <INPUT TYPE=\"text\" NAME=\"lock_comment\" VALUE=\"".$db->f("lock_comment")."\" SIZE=\"24\" MAXLENGTH=\"255\">\n";
                                	echo "  </td>\n";
                               		echo "</tr>\n";
					if ($db->f("locked")==1) 
                                        	echo "<TR><TD CLASS=\"steel1\" COLSPAN=\"3\" ALIGN=\"center\"><FONT SIZE=\"-2\">"._("Gesperrt von:")." ".get_fullname($db->f("locked_by"))." (<A HREF=\"about.php?username=".get_username($db->f("locked_by"))."\">".get_username($db->f("locked_by"))."</A>)</FONT></TD></TR>\n";
				}
				?>

				<td class="steel1" colspan=3 align=center>&nbsp;
				<input type="hidden" name="u_id"	 value="<?= $db->f("user_id") ?>">
				<?
				if ($perm->is_fak_admin() && $db->f("perms") == "admin"){
					$db2->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id)
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '$user->id' AND c.inst_perms='admin')
							WHERE a.user_id ='".$db->f("user_id")."' AND a.inst_perms = 'admin'");
					$db2->next_record();
				}

				if ($perm->have_perm("root") ||
					($db->f("perms") != "admin" && $db->f("perms") != "root") ||
					$db2->f("admin_ok")) {
					?>
					<input type="IMAGE" name="u_edit" <?=makeButton("uebernehmen", "src")?> value=" <?=_("Ver&auml;ndern")?> ">&nbsp;
					<?
					if (!StudipAuthAbstract::CheckField("auth_user_md5.password", $db->f('auth_plugin'))) {
						?>
						<input type="IMAGE" name="u_pass" <?=makeButton("neuespasswort", "src")?> value=" <?=_("Passwort neu setzen")?> ">&nbsp;
						<?
					}
					?>
					<input type="IMAGE" name="u_kill" <?=makeButton("loeschen", "src")?> value=" <?=_("L&ouml;schen")?> ">&nbsp;
					<?
		 		}
				?>
				<input type="IMAGE" name="nothing" <?=makeButton("abbrechen", "src")?> value=" <?=_("Abbrechen")?> ">
				&nbsp;</td></tr>
			<input type="hidden" name="studipticket" value="<?=get_ticket();?>">
			</form>

			<tr><td colspan=3 class="blank">&nbsp;</td></tr>

			<? // links to everywhere
			print "<tr><td class=\"steelgraulight\" colspan=3 align=\"center\">";
				printf("&nbsp;" . _("pers&ouml;nliche Homepage") . " <a href=\"about.php?username=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/einst.gif\" border=0 alt=\"Zur pers&ouml;nlichen Homepage des Benutzers\" align=\"texttop\"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp", $db->f("username"));
				printf("&nbsp;" . _("Nachricht an BenutzerIn") . " <a href=\"sms_send.php?rec_uname=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" alt=\"Nachricht an den Benutzer verschicken\" border=0 align=\"texttop\"></a>", $db->f("username"));
			print "</td></tr>";

			$temp_user_id = $db->f("user_id");
			if ($perm->have_perm("root"))
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (Institut_id) WHERE user_id ='$temp_user_id' AND inst_perms != 'user'");
			elseif ($perm->is_fak_admin())
				$db2->query("SELECT a.Institut_id,b.Name FROM user_inst AS a
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id)
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id )
							WHERE a.user_id ='".$db->f("user_id")."' AND a.inst_perms = 'admin' AND c.user_id = '$user->id' AND c.inst_perms='admin'");
			else
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst AS x LEFT JOIN user_inst AS y USING (Institut_id) LEFT JOIN Institute USING (Institut_id) WHERE x.user_id ='$temp_user_id' AND x.inst_perms != 'user' AND y.user_id = '$user->id' AND y.inst_perms = 'admin'");
			if ($db2->num_rows()) {
				print "<tr><td class=\"steel2\" colspan=3 align=\"center\">";
				print "<b>&nbsp;" . _("Link zur MitarbeiterInnen-Verwaltung") . "&nbsp;</b>";
				print "</td></tr>\n";
			}
			while ($db2->next_record()) {
				print "<tr><td class=\"steel2\" colspan=3 align=\"center\">";
				printf ("&nbsp;%s <a href=\"inst_admin.php?details=%s&admin_inst_id=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/admin.gif\" border=0 align=\"texttop\" alt=\"&Auml;ndern der Eintr&auml;ge des Benutzers in der jeweiligen Einrichtung\"></a>&nbsp;", htmlReady($db2->f("Name")), $db->f("username"), $db2->f("Institut_id"));
				print "</td></tr>\n";
			}
			?>

			</table>

			</td></tr>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>

			</table>
			<?
		}
	}

} else {

	// Gesamtliste anzeigen

	?>

	<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
	<tr valign=top align=middle>
		<td class="topic" colspan=2 align="left"><b>&nbsp;<?=_("Verwaltung aller BenutzerInnen des Systems")?></b></td>
	</tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>

	<?
	parse_msg($UserManagement->msg);
	?>

	<tr><td class="blank" colspan=2>
	<?
	if (in_array("Standard",$GLOBALS['STUDIP_AUTH_PLUGIN'])){
		?><p><b><a href="<? echo $PHP_SELF . "?details=__"?>">&nbsp;<?=_("Neuen Benutzer-Account anlegen")?></a></b></p><?
	} else {
		echo "<p>&nbsp;" . _("Die Standard Authentifizierung ist ausgeschaltet. Das Anlegen von neuen Benutzern ist nicht m�glich!") . "</p>";
	}

	include ("pers_browse.inc.php");
	print "<br>\n";
	parse_msg($msg);


	if (isset($pers_browse_search_string)) { // Es wurde eine Suche initiert

		// nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
		if (!isset($sortby) || $sortby=="") {
			if (!isset($new_user_md5_sortby) || $new_user_md5_sortby == "") {
				$new_user_md5_sortby = "username";
			}
		} else {
			$new_user_md5_sortby = $sortby;
			$sess->register("new_user_md5_sortby");
		}

		// Traverse the result set
		$db->query("SELECT auth_user_md5.*, (changed + 0) as changed_compat, mkdate FROM auth_user_md5 LEFT JOIN ".$GLOBALS['user']->that->database_table." ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) $pers_browse_search_string ORDER BY $new_user_md5_sortby");

		if ($db->num_rows() == 0) { // kein Suchergebnis
			print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" cellspacing=0 cellpadding=2 width=\"80%\">";
			print "<tr valign=\"top\" align=\"middle\">";
			print "<td>" . _("Es wurden keine Personen gefunden, auf die die obigen Kriterien zutreffen.") . "</td>";
			print "</tr><tr><td class=\"blank\">&nbsp;</td></tr></table>";

		} else { // wir haben ein Suchergebnis
			print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" cellspacing=0 class=blank cellpadding=2 width=\"100%\">";

			if ($GLOBALS["PLUGINS_ENABLE"]){
				// plugins activated
				$userkillplugin = null;
				$pluginengine = PluginEngine::getPluginPersistence("Administration");
				foreach ($pluginengine->getAllActivatedPlugins() as $activatedplugin){
					if (strtolower($activatedplugin->getPluginclassname()) == 'userkillplugin') $userkillplugin =& $activatedplugin;
				}
				if (is_object($userkillplugin)){
					echo "<tr valign=\"top\"><td colspan=\"7\"><a href=\"" . PluginEngine::getLink($userkillplugin,array('transfer_search' => 1))."\">"._("Suchergebnis in L�schformular �bernehmen")."</a></td></tr>";
				}
			}
			
			print "<tr valign=\"top\" align=\"middle\">";
				if ($db->num_rows() == 1)
			 		print("<td colspan=7>" . _("Suchergebnis: Es wurde <b>1</b> Person gefunden.") . "</td></tr>\n");
				else
			 		printf("<td colspan=7>" . _("Suchergebnis: Es wurden <b>%s</b> Personen gefunden.") . "</td></tr>\n", $db->num_rows());
			?>
			 <tr valign="top" align="middle">
				<th align="left"><a href="new_user_md5.php?sortby=username"><?=_("Benutzername")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=perms"><?=_("Status")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Vorname"><?=_("Vorname")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Nachname"><?=_("Nachname")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Email"><?=_("E-Mail")?></a></th>
				<th><a href="new_user_md5.php?sortby=changed"><?=_("inaktiv")?></a></th>
				<th><a href="new_user_md5.php?sortby=mkdate"><?=_("registriert seit")?></a></th>
				<th><a href="new_user_md5.php?sortby=auth_plugin"><?=_("Authentifizierung")?></a></th>
			 </tr>
			<?

			while ($db->next_record()):
				if ($db->f("changed_compat") != "") {
					$stamp = mktime(substr($db->f("changed_compat"),8,2),substr($db->f("changed_compat"),10,2),substr($db->f("changed_compat"),12,2),substr($db->f("changed_compat"),4,2),substr($db->f("changed_compat"),6,2),substr($db->f("changed_compat"),0,4));
					$inactive = floor((time() - $stamp) / 3600 / 24);
				} else {
					$inactive = _("nie benutzt");
				}
				?>
				<tr valign=middle align=left>
					<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>"><a href="<?php echo $PHP_SELF . "?details=" . $db->f("username") ?>"><?php $db->p("username") ?></a>
					<? if ($db->f("locked")=="1") echo "<FONT SIZE=\"-1\" COLOR=\"RED\">&nbsp;<B>"._("gesperrt!")."</B></FONT>"; ?>
					</TD>
					<td class="<? echo $cssSw->getClass() ?>"><?=$db->f("perms") ?></td>
					<td class="<? echo $cssSw->getClass() ?>"><?=htmlReady($db->f("Vorname")) ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?=htmlReady($db->f("Nachname")) ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?=htmlReady($db->f("Email"))?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><?php echo $inactive ?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo _("unbekannt"); ?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><?=($db->f("auth_plugin") ? $db->f("auth_plugin") : "Standard")?></td>
				</tr>
				<?
			endwhile;
			print ("</table>");
		}
	}
	print ("</td></tr></table>");


}

page_close();
?>
</body>
</html>
