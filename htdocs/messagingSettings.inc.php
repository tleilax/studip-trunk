<?

/**
* displays editable personal messaging-settings
* 
* @author				Nils K. Windisch <studip@nkwindisch.de>
* @access				public
* @modulegroup	Messaging
* @module				sms_box.php
* @package			Stud.IP Core
*/

/*
messagingSettings.php
Copyright (C) 2003 Nils K. Windisch <info@nkwindisch.de>

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

require_once ($ABSOLUTE_PATH_STUDIP."/language.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/messaging.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/contact.inc.php");
check_messaging_default();
$db2=new DB_Seminar;
$db3=new DB_Seminar;

## ACTION ##


// add forward_receiver
if ($add_smsforward_rec_x) { 
	$query = "UPDATE user_info SET smsforward_rec='".get_userid($smsforward_rec)."', smsforward_copy='1' WHERE user_id='".$user->id."'";
	$db3->query($query);
}

// del forward receiver
if ($del_forwardrec_x) {
	$query = "UPDATE user_info SET smsforward_rec='', smsforward_copy='1' WHERE user_id='".$user->id."'";
	$db3->query($query);
}

$query = "SELECT * FROM user_info WHERE user_id='".$user->id."'";
$db2->query($query);
while ($db2->next_record()) {
	$smsforward['copy'] = $db2->f("smsforward_copy");
	$smsforward['rec'] = $db2->f("smsforward_rec");
}

//vorgenommene Anpassungen der Ansicht in Uservariablen schreiben
if ($messaging_cmd=="change_view_insert" && !$set_msg_default_x && $newmsgset_x) {
	if (!$send_as_email) {
		$my_messaging_settings["send_as_email"] = FALSE;
	} else {
		$my_messaging_settings["send_as_email"] = TRUE;
	}
	$my_messaging_settings["changed"] = TRUE;
	$my_messaging_settings["show_only_buddys"] = $show_only_buddys;
	$my_messaging_settings["delete_messages_after_logout"] = $delete_messages_after_logout;
	$my_messaging_settings["start_messenger_at_startup"] = $start_messenger_at_startup;
	$my_messaging_settings["active_time"] = $active_time;
	$my_messaging_settings["sms_sig"] = $sms_sig;
	$my_messaging_settings["timefilter"] = $timefilter;
	$my_messaging_settings["openall"] = $openall;
	if (!$opennew) {
		$my_messaging_settings["opennew"] = "2";
	} else {
		$my_messaging_settings["opennew"] = $opennew;
	}
	$my_messaging_settings["logout_markreaded"] = $logout_markreaded;
	$my_messaging_settings["addsignature"] = $addsignature;
	$sms_data["sig"] = $addsignature;
	$my_messaging_settings["changed"] = "TRUE";
	if (!$save_snd) {
		$my_messaging_settings["save_snd"] = "2";
	} else {
		$my_messaging_settings["save_snd"] = $save_snd;
	}
	$sms_data["time"] = $my_messaging_settings["timefilter"];
	if ($smsforward['rec']) {
		if ($smsforward_copy && !$smsforward['copy'])  {
			$query = "UPDATE user_info SET smsforward_copy='1'  WHERE user_id='".$user->id."'";
			$db3->query($query);		
		}
		if (!$smsforward_copy && $smsforward['copy'])  {
			$query = "UPDATE user_info SET smsforward_copy=''  WHERE user_id='".$user->id."'";
			$db3->query($query);		
		}
	}
} else if ($messaging_cmd=="change_view_insert" && $set_msg_default_x) {
	$reset_txt = "<font size=\"-1\">"._("Durch das Zur�cksetzen werden die pers�nliche Messaging-Einstellungen auf die Startwerte zur�ckgesetzt <b>und</b> die pers�nlichen Nachrichten-Ordner gel�scht. <b>Nachrichten werden nicht entfernt.</b>")."</font><br>";
}

if ($messaging_cmd == "reset_msg_settings") {
	$user_id = $user->id;
	unset($my_messaging_settings);
	check_messaging_default();
	$db3->query("UPDATE user_info SET smsforward_copy='', smsforward_rec='' WHERE user_id='".$user_id."'");
	$db3->query("UPDATE message_user SET folder='' WHERE user_id='".$user_id."'");
}

if ($do_add_user_x)
	$msging->add_buddy ($add_user);

## FUNCTION ##

function change_messaging_view() {
	global $_fullname_sql,$my_messaging_settings, $PHP_SELF, $perm, $user, $search_exp, $add_user, $add_user_x, $do_add_user_x, $new_search_x, $i_page, $search_exp, $gosearch_x, $smsforward, $reset_txt;
	$msging=new messaging;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;	
	$cssSw=new cssClassSwitcher;	
	?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
		<tr>
			<td class="topic" colspan=2><img src="pictures/einst.gif" border="0" align="texttop"><b>&nbsp;<?print _("Einstellungen des Messagings anpassen");?></b></td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;</td>
		</tr>
		<tr>
			
			<td class="blank" width="100%" colspan="2" align="center">
			<blockquote>
				<font size="-1"><b><?print _("Auf dieser Seite k&ouml;nnen Sie die Eigenschaften des Stud.IP-Messagingsystems an Ihre Bed&uuml;rfnisse anpassen.");?>
			</blockquote>			
			<form action="<?=$PHP_SELF?>?messaging_cmd=change_view_insert" method="post">
			<? if ($reset_txt) {
				?><table width="70%" align="center" cellpadding=8 cellspacing=0 border=0><tr><td align="left" class="steel1"><?
				echo $reset_txt; ?>
				<br><div align="center"><font size="-1">
				<?=_("M�chten Sie fortfahren?")?>
				<a href="<?=$PHP_SELF?>?messaging_cmd=reset_msg_settings&change_view=TRUE"><?=makeButton("ja2", "img")?></a>&nbsp;
				<a href="<?=$PHP_SELF?>?change_view=TRUE"><?=makeButton("nein", "img")?></a></font><div>
				</td></tr></table><br><?
			} ?>
			<table width="70%" align="center"cellpadding=8 cellspacing=0 border=0>
				<tr>
					<th width="50%" align=center><?=_("Option")?></th>
					<th align=center><?=_("Auswahl")?></th>
				</tr>
				<tr>
					<td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;"><font size="-1"><b><?=_("Einstellungen des system-internen Nachrichten-Systems")?></b></font></td>
				</tr>
				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Neue Nachrichten immer aufgeklappt");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="opennew"<? if ($my_messaging_settings["opennew"] == "1") echo " checked"; ?>>
					</td>
				</tr>
			
				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Alle Nachrichten immer aufgeklappt");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="openall"<? if ($my_messaging_settings["openall"] == "1") echo " checked"; ?>>
					</td>
				</tr>

				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Gesendete Nachrichten im Postausgang speichern");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="save_snd"<? if ($my_messaging_settings["save_snd"] == "1") echo " checked"; ?>>
					</td>
				</tr>
				
				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Beim Logout alle Nachrichten l�schen");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="delete_messages_after_logout"<? if ($my_messaging_settings["delete_messages_after_logout"] == "1") echo " checked"; ?>>
						&nbsp;<font size="-1">(<?=_("davon ausgenommen sind gesch�tzte Nachrichten")?>)</font>
					</td>
				</tr>

				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Beim Logout alle Nachrichten als gelesen speichern");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="logout_markreaded"<? if ($my_messaging_settings["logout_markreaded"] == "1") echo " checked"; ?>>
					</td>
				</tr>	

				<? if ($GLOBALS["MESSAGING_FORWARD_AS_EMAIL"]) { ?>
				<tr  <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1"><?print _("Eine Kopie aller eingehenden Nachrichten an eigene E-Mail-Adresse schicken");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" value="1" name="send_as_email"<? if ($my_messaging_settings["send_as_email"] == "1") echo " checked"; ?>>
					</td>
				</tr>
				<? } ?>

				<tr <? $cssSw->switchClass() ?>>
					<td  align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size="-1">
						<?print _("Weiterleitung empfangener Nachrichten");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>> <?
						$query = "SELECT * FROM user_info WHERE user_id='".$user->id."'";
						$db2->query($query);
						while ($db2->next_record()) {
							$smsforward['copy'] = $db2->f("smsforward_copy");
							$smsforward['rec'] = $db2->f("smsforward_rec");
						}
						if ($smsforward['rec']) { // empfaenger ausgewaehlt
							printf("&nbsp;<font size=\"-1\">"._("Empf�nger: %s%s%s")."</font>&nbsp;&nbsp;<input type=\"image\" name=\"del_forwardrec\" src=\"./pictures/trash.gif\" border=\"0\" ".tooltip(_("Empf�nger und Weiterleitung l�schen.")).">&nbsp;<input type=\"image\" name=\"del_forwardrec\" src=\"./pictures/suche2.gif\" border=\"0\" ".tooltip(_("Neuen Empf�nger suchen."))."><br>", "<a href=\"about.php?username=".get_username($smsforward['rec'])."\">", get_fullname($smsforward['rec']), "</a>");
							echo "<input type=\"checkbox\" value=\"1\" name=\"smsforward_copy\"";
							if ($smsforward['copy'] == "1") echo " checked";
							echo ">&nbsp;<font size=\"-1\">".("Kopie im pers�nlichen Posteingang speichern.")."</font>";
						} else { // kein empfaenger ausgewaehlt
							if ($search_exp == "") { ?>
								<input type="text" name="search_exp" size="30" value="">
								<input type="image" name="gosearch" src="./pictures/suche2.gif" border="0"><?
							} else {
								$db->query("SELECT username, ".$_fullname_sql['full_rev']." AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE (username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ORDER BY Nachname ASC"); 
								if (!$db->num_rows()) { // wenn keine treffer
									echo "&nbsp;<input type=\"image\" name=\"reset_freesearch\" src=\"./pictures/rewind.gif\" border=\"0\" value=\""._("Suche zur&uuml;cksetzen")."\" ".tooltip(_("setzt die Suche zur�ck")).">";
									echo "<font size=\"-1\">&nbsp;"._("keine Treffer")."</font>";
								} else { // treffer ausw�hlen
									echo "<input type=\"image\" name=\"add_smsforward_rec\" ".tooltip(_("als Empf�nger weitergeleiteter Nachrichten eintragen"))." value=\""._("als Empf�nger ausw�hlen")."\" src=\"./pictures/vote_answer_correct.gif\" border=\"0\">&nbsp;&nbsp;";
									echo "<select size=\"1\" name=\"smsforward_rec\">";
									while ($db->next_record()) {
										if (get_username($user->id) != $db->f("username")) {
											echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
										}							
									} ?>
									</select>
									<input type="image" name="reset_serach" src="./pictures/rewind.gif" border="0" value="<?=_("Suche zur&uuml;cksetzen")?>" <?=tooltip(_("setzt die Suche zur�ck"))?>> <?
								}								
							}
						}
						?>
					</td>
				</tr>	
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank" style="border-bottom:1px dotted black;">
						<font size=-1><?echo _("Zeitfilter der Anzeige in Postein- bzw. ausgang");?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<font size=-1> &nbsp;<select name="timefilter"> <?
						printf("<option value=\"%s\" %s>%s</option>", "new", CheckSelected($my_messaging_settings["timefilter"], "new"), _("neue Nachrichten"));
						printf("<option value=\"%s\" %s>%s</option>", "all", CheckSelected($my_messaging_settings["timefilter"], "all"), _("alle Nachrichten"));
						printf("<option value=\"%s\" %s>%s</option>", "24h", CheckSelected($my_messaging_settings["timefilter"], "24h"), _("letzte 24 Stunden"));
						printf("<option value=\"%s\" %s>%s</option>", "7d", CheckSelected($my_messaging_settings["timefilter"], "7d"), _("letzte 7 Tage"));
						printf("<option value=\"%s\" %s>%s</option>", "30d", CheckSelected($my_messaging_settings["timefilter"], "30d"), _("letzte 30 Tage"));
						printf("<option value=\"%s\" %s>%s</option>", "older", CheckSelected($my_messaging_settings["timefilter"], "older"), _("&auml;lter als 30 Tage")); ?>
						</select>
					</td>
				</tr>	

				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?echo _("Signatur gesendeten Nachrichten anh�ngen");?></font>
					</td>
					<td align="left" <?=$cssSw->getFullClass()?>>
						<font size=-1><input type="checkbox" value="1" name="addsignature"<? if ($my_messaging_settings["addsignature"] == "1") echo " checked"; ?>>&nbsp;<?=_("Signatur anh�ngen")?></font> <br>
						&nbsp;<textarea name="sms_sig" rows=3 cols=30><? echo htmlready($my_messaging_settings["sms_sig"]); ?></textarea>
					</td>
				</tr>
				<tr <? $cssSw->resetClass() ?>>
					<td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;"><font size="-1"><b><?=_("Stud.IP-Messenger")?></b></font></td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?=_("Stud.IP-Messenger automatisch nach dem Login starten")?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" name="start_messenger_at_startup" <? if ($my_messaging_settings["start_messenger_at_startup"]) echo " checked"; ?> >
					</td>
				</tr>

				<tr <? $cssSw->switchClass() ?>>
					<td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;"><font size="-1"><b><?=_("Buddies/ Wer ist online?")?></b></font></td>
				</tr>
				<? if (GetNumberOfBuddies()) { ?>                      
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?=_("Nur Buddies in der &Uuml;bersicht der aktiven Benutzer anzeigen")?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<input type="checkbox" name="show_only_buddys"<? if ($my_messaging_settings["show_only_buddys"]) echo " checked"; ?> >
					</td>
				</tr>
				<? } ?>
				<tr <? $cssSw->switchClass() ?>>
					<td align="right" class="blank">
						<font size=-1><?=_("Dauer bis inaktiv:")?></font>
					</td>
					<td <?=$cssSw->getFullClass()?>>
						<select name="active_time"> <? 
						for ($i=0; $i<=15; $i=$i+5) {
							if ($i) {
								if ($my_messaging_settings["active_time"] == $i) {
									echo "<option selected>$i</option>";
								} else {
									echo "<option>$i</option>"; 
								}
							}
						} ?>
                        			</select>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td  <?=$cssSw->getFullClass()?> colspan=2 align="middle">
						<input type="hidden" name="view" value="Messaging">
						<font size=-1>
						<input type="image" <?=makeButton("uebernehmen", "src") ?> border=0 value="<?_("�nderungen �bernehmen")?>" name="newmsgset"></font>&nbsp;
						<input type="image" name="set_msg_default" <?=makeButton("zuruecksetzen", "src") ?> border=0 value="<?_("Einstellungen zur�cksetzen")?>"></font>
						</form>	
					</td>
				</tr>
				</form>	
			</table>
			<br />
			<br />
			</td>
		</tr>
	</table> 
<?
} 
?>
