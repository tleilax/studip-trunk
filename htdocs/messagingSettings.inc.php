<?
//Variable registrieren
//$user->register("my_messaging_settings");

require_once ("config.inc.php");
require_once ("functions.php");
require_once ("visual.inc.php");
require_once ("messaging.inc.php");

$msging=new messaging;

//vorgenommene Anpassungen der Ansicht in Uservariablen schreiben
if ($messaging_cmd=="change_view_insert") {

	$my_messaging_settings["changed"]=TRUE;
	$my_messaging_settings["show_only_buddys"]=$show_only_buddys;
	$my_messaging_settings["delete_messages_after_logout"]=$delete_messages_after_logout;
	$my_messaging_settings["start_messenger_at_startup"]=$start_messenger_at_startup;
	$my_messaging_settings["active_time"]=$active_time;
	$my_messaging_settings["sms_sig"]=$sms_sig;
	$my_messaging_settings["changed"]="TRUE";
	}

if ($do_add_user_x)
	$msging->add_buddy ($add_user, 0);

if ($delete_user)
	$msging->delete_buddy ($delete_user);
	
if (is_array($buddy_grp)) {
	foreach ($buddy_grp as $key=>$value)  {
		$my_buddies[$buddy_key[$key]]["group"] =$value;
	}
}


//Anpassen der Ansicht
function change_messaging_view() {
	global $_fullname_sql,$my_messaging_settings, $my_buddies, $PHP_SELF, $perm, $user, $search_exp, $add_user, $add_user_x, $do_add_user_x, $new_search_x, $i_page;
		
	$db=new DB_Seminar;
	$cssSw=new cssClassSwitcher;	
	
	if ((!$search_exp) && (!$add_user))
		$new_search_x=TRUE;
	
	?>
	<table width ="100%" cellspacing=0 cellpadding=0 border=0>
	<tr>
		<td class="topic" colspan=2><img src="pictures/meinesem.gif" border="0" align="texttop"><b>&nbsp;Einstellungen f&uuml;r das Stud.IP Messaging anpassen</b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>&nbsp;
			<blockquote>
				Hier k&ouml;nnen Sie sie Einstellungen f&uuml;r das Stud.IP-Messaging &auml;ndern. <br />
			<br>
			</blockquote>
		</td>
	</tr>	
	<tr>
		<td class="blank" colspan=2>
			<form method="POST" action="<? echo $PHP_SELF ?>?messaging_cmd=change_view_insert">
			<table width ="99%" align="center" cellspacing=0 cellpadding=2 border=0>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp; &nbsp; <b>Systeminterne Kurznachrichten (SMS)</b>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>automatisches L&ouml;schen:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["delete_messages_after_logout"]) echo " checked"; ?>
					 name="delete_messages_after_logout">
					&nbsp; Gelesene Nachrichten automatisch nach dem Logout l&ouml;schen
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Signatur:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<textarea name="sms_sig" rows=3 cols=40><? echo htmlready($my_messaging_settings["sms_sig"]); ?></textarea>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp; &nbsp; <b>Stud.IP Messenger</b>
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Starten:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["start_messenger_at_startup"]) echo " checked"; ?>
					 name="start_messenger_at_startup">
					&nbsp; Stud.IP-Messenger automatisch nach dem Login starten
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
					&nbsp; &nbsp; <b>Buddies</b><a name="buddy_anker"></a>
					</td>
				</tr>
				<?
				if (is_array($my_buddies)) {
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Anzeige:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; 
					<input type="CHECKBOX" 
					<? if ($my_messaging_settings["show_only_buddys"]) echo " checked"; ?>
					 name="show_only_buddys">
					&nbsp; Nur Buddys in der &Uuml;bersicht der aktiven Benutzer anzeigen
					</td>
				</tr>
				<?
				}
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Dauer bis inaktiv:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">&nbsp; Benutzer nach&nbsp; 
					<select name="active_time">
					<? 
					for ($i=0; $i<=15; $i=$i+5) {
					if ($i)
						if ($my_messaging_settings["active_time"] == $i) 
							echo "<option selected>$i</option>";
						else
							echo "<option>$i</option>"; 
							}
					?>
					</select>
					&nbsp; Minuten nicht mehr anzeigen
					</td>
				</tr>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Buddy hinzuf&uuml;gen:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">
					<?
					if ((!$new_search_x)){
						$db->query("SELECT " . $_fullname_sql['full_rev'] ." AS fullname, username, auth_user_md5.user_id FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%' ORDER BY Nachname");
						if ($db->affected_rows()) {
							echo "<b><font size=-1>&nbsp; Es wurden ", $db->affected_rows(), " Benutzer gefunden </font><br /></b>";
							echo "<font size=-1>&nbsp; Bitte w&auml;hlen Sie den Benutzer aus der Liste aus:</font>&nbsp;<font size=-1><select name=\"add_user\">";
							while ($db->next_record()) {
								echo "<option value=",$db->f("username"),">",$db->f("fullname"), ", (",$db->f("username"),") </option>";
							}
							echo "</select></font>";
							echo "<br /><br />&nbsp; <font size=-1><input type=\"IMAGE\"  src=\"pictures/buttons/hinzufuegen-button.gif\" border=0  name=\"do_add_user\" value=\"Diesen Benutzer hinzuf&uuml;gen\" /></font>&nbsp;";
							echo "<font size=-1>&nbsp;<input type=\"IMAGE\"  name=\"new_search\" src=\"pictures/buttons/neuesuche-button.gif\" border=0  value=\"Neue Suche\" /></font>";
							echo "<input type=\"HIDDEN\" name=search_exp value=\"$search_exp\">";
							echo "<a name=\"anker\"></a>";
							}
						}
					if (((!$db->affected_rows())) || ($new_search_x)) {
						if (($add_user) && (!$db->affected_rows)  && (!$new_search_x))
							echo "<br /><b><font size=-1>&nbsp; Es wurde kein Benutzer zu dem eingegebenem Suchbegriff gefunden!</font></b><br />";
						echo "<font size=-1>&nbsp; Bitte Namen, Vornamen oder Usernamen eingeben:</font>&nbsp; ";
						echo "<input type=\"TEXT\" size=20 maxlength=255 name=\"search_exp\" />";
						echo "&nbsp;<input type=\"IMAGE\"  name=\"add_user\" src=\"pictures/buttons/suchestarten-button.gif\" border=0  value=\"Suche starten\" />";	
						}
					?>
					</td>
				</tr>
				<?
				if (is_array($my_buddies)) {
					//temp sort
					foreach ($my_buddies as $key=>$val)
						$tmp_buddies[$key]=array(group=>$val["group"], username=>$val["username"]);
					sort ($tmp_buddies);
				?>				
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">
					<blockquote><br><b>Buddies:</b></blockquote>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%">
				<?
						echo "<table cellspacing=0 cellpadding=0 width=\"100%\" border=0 height=\"100%\" bgcolor=\"white\" border=0>\n";
						echo "<tr><td class=\"".$cssSw->getClass() ."\" width=\"70%\">&nbsp; </td>";
						for ($k=0; $k<8; $k++)
							echo "<td class=\"gruppe".$k."\" width=\"1%\">&nbsp;</td>\n";
						echo "<td class=\"".$cssSw->getClass() ."\" width=\"10%\" align=\"center\">&nbsp; </td>";
						echo "</tr>";
						$i=0;
						foreach ($tmp_buddies as $a) {
							$db->query("SELECT " . $_fullname_sql['full'] ." AS fullname, username FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE username = '".$a["username"]."' ");
							$db->next_record();

							if ($i % 2)
								$class="steelgraulight"; 
							else
								$class="steel1";
							
							echo "<tr><td class=\"$class\" width=\"70%\">";
							echo "<input type=\"HIDDEN\" name=\"buddy_key[]\" value=\"".$db->f("username")."\"/> ";
							echo "<a href=\"about.php?username=",$db->f("username"),"\">",$db->f("fullname"), "</a></td>\n";
							for ($k=0; $k<8; $k++) {
								echo "<td class=\"$class\" width=\"1%\"><input type=\"RADIO\" name=\"buddy_grp[$i]\" value=$k ";
								if ($a["group"]==$k)
									echo " checked";
								echo " /></td>";
								}
							echo "<td class=\"$class\" width=\"10%\" align=\"center\"><a href=\"$PHP_SELF?view=Messaging&delete_user=",$db->f("username"),"\">&nbsp; <img src=\"pictures/trash.gif\" border=0></a></td>";
							echo "</tr>\n";
							$i++;
							}
						echo "</table>";
					?>
					</td>
				</tr>
				<?
					}
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" width="20%">&nbsp;
					</td>
					<td class="<? echo $cssSw->getClass() ?>" width="80%"><br>&nbsp; 
					<font size=-1><input type="IMAGE" src="pictures/buttons/uebernehmen-button.gif" border=0 value="&Auml;nderungen &uuml;bernehmen"></font>&nbsp; 
					<?
					if ($i_page == "online.php")
						echo "<a href=\"online.php\"><img src=\"pictures/buttons/zurueck2-button.gif\" border=0></a>";
					if ($i_page == "sms.php")
						echo "<a href=\"sms.php\"><img src=\"pictures/buttons/zurueck2-button.gif\" border=0></a>";
					?>
					<input type="HIDDEN" name="view" value="Messaging">
					</td>
				</tr>
			</table>
		</form>	
	<?
	}

?>
