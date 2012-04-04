<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * messagingSettings.php - displays editable personal messaging-settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nils K. Windisch <studip@nkwindisch.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     message
 */

use Studip\Button, Studip\LinkButton;

require_once ('lib/language.inc.php');
require_once ('config.inc.php');
require_once 'lib/functions.php';
require_once ('lib/visual.inc.php');
require_once ('lib/user_visible.inc.php');
require_once ('lib/messaging.inc.php');
require_once ('lib/contact.inc.php');

// access to user's config setting
$user_cfg = UserConfig::get($GLOBALS['user']->id);

check_messaging_default();
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$reset_txt = '';

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
$db2->next_record();

$smsforward['copy'] = $db2->f("smsforward_copy");
$smsforward['rec'] = $db2->f("smsforward_rec");

$email_forward = $db2->f("email_forward");

if ($email_forward == "0") $email_forward = $GLOBALS["MESSAGING_FORWARD_DEFAULT"];

//vorgenommene Anpassungen der Ansicht in Uservariablen schreiben

if ($messaging_cmd=="change_view_insert" && !$set_msg_default_x && Request::submitted('newmsgset')) {
        $db2->query("UPDATE user_info SET email_forward = '".$send_as_email."' WHERE user_id = '".$user->id."'");
        $email_forward = $send_as_email;

    // write to user config table
    $user_cfg->store("ONLINE_NAME_FORMAT", $_REQUEST['online_format']);
    $user_cfg->store("MAIL_AS_HTML", Request::int('mail_format'));

    $my_messaging_settings["changed"] = TRUE;
    $my_messaging_settings["delete_messages_after_logout"] = $delete_messages_after_logout;
    $my_messaging_settings["start_messenger_at_startup"] = $start_messenger_at_startup;
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
    $my_messaging_settings['confirm_reading'] = $confirm_reading;
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
} else if ($messaging_cmd=="change_view_insert" && Request::submitted('set_msg_default')) {
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

function change_messaging_view()
{
    global $_fullname_sql,$my_messaging_settings, $PHP_SELF, $perm, $user, $search_exp,
           $add_user, $add_user_x, $do_add_user_x, $new_search_x, $i_page, $search_exp,
           $gosearch_x, $smsforward, $reset_txt, $email_forward, $user_cfg, $FOAF_ENABLE;

    $msging=new messaging;
    $db=new DB_Seminar;
    $db2=new DB_Seminar;
    $db3=new DB_Seminar;
    $cssSw=new cssClassSwitcher;
    ?>
    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
        <tr>
            <td class="blank" colspan=2>&nbsp;</td>
        </tr>
        <tr>

            <td class="blank" width="100%" colspan="2" align="center">

            <form action="<?=$PHP_SELF?>?messaging_cmd=change_view_insert" method="post">
            <?= CSRFProtection::tokenTag() ?>
            <? if ($reset_txt) {
                ?><table width="70%" align="center" cellpadding=8 cellspacing=0 border=0><tr><td align="left" class="steel1"><?
                echo $reset_txt; ?>
                <br><div align="center">
                <?=_("M�chten Sie fortfahren?")?>
                <?=LinkButton::createAccept(_("JA!"), URLHelper::getURL('', array('messaging_cmd' => 'reset_msg_settings', 'change_view' => TRUE, 'view' =>'Messaging')))?>&nbsp;
                <?=LinkButton::createCancel(_("NEIN!"), URLHelper::getURL('', array('view' =>'Messaging')))?><div>
                </td></tr></table><br><?
            } ?>
            <table width="70%" align="center"cellpadding=8 cellspacing=0 border=0  id="main_content">
                <tr>
                    <th width="50%" align=center><?=_("Option")?></th>
                    <th align=center><?=_("Auswahl")?></th>
                </tr>

                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="opennew"><?print _("Neue Nachrichten immer aufgeklappt");?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <input type="checkbox" value="1" name="opennew" id="opennew"<? if ($my_messaging_settings["opennew"] == "1") echo " checked"; ?>>
                    </td>
                </tr>

                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="openall"><?print _("Alle Nachrichten immer aufgeklappt");?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <input type="checkbox" value="1" name="openall" id="openall"<? if ($my_messaging_settings["openall"] == "1") echo " checked"; ?>>
                    </td>
                </tr>

                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="save_snd"><?print _("Gesendete Nachrichten im Postausgang speichern");?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <input type="checkbox" value="1" name="save_snd" id="save_snd"<? if ($my_messaging_settings["save_snd"] == "1") echo " checked"; ?>>
                    </td>
                </tr>

                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="delete_messages_after_logout"><?print _("Beim Logout alle Nachrichten l�schen");?></label>
                            <div class="setting_info">(<?=_("davon ausgenommen sind gesch�tzte Nachrichten")?>)</div>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <input type="checkbox" value="1" name="delete_messages_after_logout" id="delete_messages_after_logout"<? if ($my_messaging_settings["delete_messages_after_logout"] == "1") echo " checked"; ?>>
                    </td>
                </tr>

                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="logout_markreaded"><?print _("Beim Logout alle Nachrichten als gelesen speichern");?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <input type="checkbox" value="1" name="logout_markreaded" id="logout_markreaded"<? if ($my_messaging_settings["logout_markreaded"] == "1") echo " checked"; ?>>
                    </td>
                </tr>

                <? if ($GLOBALS["MESSAGING_FORWARD_AS_EMAIL"]) { ?>
                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <?print _("Kopie empfangener Nachrichten an eigene E-Mail-Adresse schicken");?>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <label><input type="radio" name="send_as_email" value="1"<?=($email_forward == "1") ? " checked": "";?>>&nbsp;<?=_("nie")?></label><br>
                        <label><input type="radio" name="send_as_email" value="2"<?=($email_forward == "2") ? " checked": "";?>>&nbsp;<?=_("immer")?></label><br>
                        <label><input type="radio" name="send_as_email" value="3"<?=($email_forward == "3") ? " checked": "";?>>&nbsp;<?=_("wenn vom Absender gew�nscht")?></label>
                    </td>
                </tr>
                <tr  <? $cssSw->switchClass() ?>>
                  <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                    <?print _("E-Mail in folgendem Format versenden");?>
                  </td>
                  <td <?=$cssSw->getFullClass()?>>
                     <label><input type="radio" name="mail_format" value="0" <?= !$user_cfg->getValue('MAIL_AS_HTML') ? 'checked' : '' ?>>&nbsp;<?=_("Text")?></label><br>
                     <label><input type="radio" name="mail_format" value="1" <?= $user_cfg->getValue('MAIL_AS_HTML') ? 'checked' : '' ?>>&nbsp;<?=_("HTML")?></label>
                   </td>
                 </tr>
                <? } ?>

                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                       <?print _("Umgang mit angeforderter Lesebest�tigung");?>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <label><input type="radio" name="confirm_reading" value="1"<?=($my_messaging_settings["confirm_reading"] == "1") ? " checked": "";?>>&nbsp;<?=_("ignorieren")?></label><br>
                        <label><input type="radio" name="confirm_reading" value="2"<?=($my_messaging_settings["confirm_reading"] == "2") ? " checked": "";?>>&nbsp;<?=_("immer automatisch best�tigen")?></label><br>
                        <label><input type="radio" name="confirm_reading" value="3"<?=($my_messaging_settings["confirm_reading"] == "3") ? " checked": "";?>>&nbsp;<?=_("je Nachricht selbst entscheiden")?></label>
                    </td>
                </tr>


                <tr <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="search_exp"><?print _("Weiterleitung empfangener Nachrichten");?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>> <?
                        $query = "SELECT * FROM user_info WHERE user_id='".$user->id."'";
                        $db2->query($query);
                        while ($db2->next_record()) {
                            $smsforward['copy'] = $db2->f("smsforward_copy");
                            $smsforward['rec'] = $db2->f("smsforward_rec");
                        }
                        if ($smsforward['rec']) { // empfaenger ausgewaehlt
                            printf("&nbsp;<font size=\"-1\">"._("Empf�nger: %s%s%s")."</font>&nbsp;&nbsp;<input type=\"image\" name=\"del_forwardrec\" src=\"" . Assets::image_path('icons/16/blue/trash.png') . "\" ".tooltip(_("Empf�nger und Weiterleitung l�schen.")).">&nbsp;<input type=\"image\" name=\"del_forwardrec\" src=\"".Assets::image_path('icons/16/blue/search.png')."\"  ".tooltip(_("Neuen Empf�nger suchen."))."><br>", "<a href=\"about.php?username=".get_username($smsforward['rec'])."\">", get_fullname($smsforward['rec'],'full',true), "</a>");
                            echo "<label><input type=\"checkbox\" value=\"1\" name=\"smsforward_copy\"";
                            if ($smsforward['copy'] == "1") echo " checked";
                            echo ">&nbsp;<font size=\"-1\">"._("Kopie im pers�nlichen Posteingang speichern.")."</label></font>";
                        } else { // kein empfaenger ausgewaehlt
                            if ($search_exp == "") { ?>
                                <input type="text" name="search_exp" id="search_exp" size="30" value="">
                                <input type="image" name="gosearch" src="<?=Assets::image_path('icons/16/blue/search.png') ?>" class="middle" title="<?= _("Nach Empf�nger suchen") ?>" border="0"><?
                            } else {
                                $db->query("SELECT username, ".$_fullname_sql['full_rev']." AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE (username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') AND ".get_vis_query('auth_user_md5')." ORDER BY Nachname ASC");
                                if (!$db->num_rows()) { // wenn keine treffer
                                    echo '<input type="image" name="reset_serach" src="' . Assets::image_path('icons/16/blue/refresh.png') . '" class="text-top" value="' . _("Suche zur�cksetzen") . '" ' . tooltip(_("setzt die Suche zur�ck")) . '>';
                                    echo "<font size=\"-1\">&nbsp;"._("keine Treffer")."</font>";
                                } else { // treffer ausw�hlen
                                    echo "<input type=\"image\" name=\"add_smsforward_rec\" ".tooltip(_("als Empf�nger weitergeleiteter Nachrichten eintragen"))." value=\""._("als Empf�nger ausw�hlen")."\" src=\"" . Assets::image_path('icons/16/blue/accept.png') . "\" border=\"0\">&nbsp;&nbsp;";
                                    echo "<select size=\"1\" name=\"smsforward_rec\">";
                                    while ($db->next_record()) {
                                        if (get_username($user->id) != $db->f("username")) {
                                            echo "<option value=\"".$db->f("username")."\">".htmlReady(my_substr($db->f("fullname"),0,35))." (".$db->f("username").") - ".$db->f("perms")."</option>";
                                        }
                                    } ?>
                                    </select>
                                    <input type="image" name="reset_serach" src="<?= Assets::image_path('icons/16/blue/refresh.png') ?>" class="text-top" value="<?=_("Suche zur�cksetzen")?>" <?=tooltip(_("setzt die Suche zur�ck"))?>> <?
                                }
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr <? $cssSw->switchClass() ?>>
                    <td align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="timefilter"><?echo _("Zeitfilter der Anzeige in Postein- bzw. ausgang");?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        &nbsp;<select name="timefilter" id="timefilter"> <?
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
                    <td align="right" class="blank" style="border-bottom:1px dotted black;">
                        <?echo _("Signatur gesendeten Nachrichten anh�ngen");?>
                    </td>
                    <td align="left" <?=$cssSw->getFullClass()?>>
                        <label><input type="checkbox" value="1" name="addsignature"<? if ($my_messaging_settings["addsignature"] == "1") echo " checked"; ?>>&nbsp;<?=_("Signatur anh�ngen")?></label><br>
                        &nbsp;<textarea name="sms_sig" aria-label="<?= _("Signatur") ?>" rows=3 cols=30><? echo htmlready($my_messaging_settings["sms_sig"]); ?></textarea>
                    </td>
                </tr>

                <tr <? $cssSw->resetClass() ?>>
                    <td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;">
                        <b><?=_("Stud.IP-Messenger")?></b>
                    </td>
                </tr>
                <tr <? $cssSw->switchClass() ?>>
                    <td align="right" class="blank">
                        <label for="start_messenger_at_startup"><?=_("Stud.IP-Messenger automatisch nach dem Login starten")?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <input type="checkbox" id="start_messenger_at_startup" name="start_messenger_at_startup" <? if ($my_messaging_settings["start_messenger_at_startup"]) echo " checked"; ?> >
                    </td>
                </tr>
                <tr <? $cssSw->switchClass() ?>>
                    <td colspan="2" align="center" class="steelgraulight" style="border-bottom:1px dotted black;border-top:1px dotted black;">
                        <b><?=_("Buddies/ Wer ist online?")?></b>
                    </td>
                </tr>
                <tr  <? $cssSw->switchClass() ?>>
                    <td  align="right" class="blank" style="border-bottom:1px dotted black;">
                        <label for="online_format"><?print _("Formatierung der Namen auf &raquo;Wer ist Online?&laquo;");?></label>
                    </td>
                    <td <?=$cssSw->getFullClass()?>>
                        <select name="online_format" id="online_format">
                        <?
                        foreach($GLOBALS['NAME_FORMAT_DESC'] as $key => $value){
                            echo "\n<option value=\"$key\"";
                            if($user_cfg->getValue("ONLINE_NAME_FORMAT") == $key) echo " selected ";
                            echo ">$value</option>";
                        }
                        ?>
                        </select>

                    </td>
                </tr>
                <tr <? $cssSw->switchClass() ?>>
                    <td  <?=$cssSw->getFullClass()?> colspan="2" align="center">
                        <input type="hidden" name="view" value="Messaging">
                        <?=Button::create(_('�bernehmen'), 'newmsgset', array('title' => _("�nderungen �bernehmen")))?>
                        &nbsp;
                        <?=Button::create(_('Zur�cksetzen'), 'set_msg_default', array('title' => _("Einstellungen zur�cksetzen")))?>
                        </form>
                    </td>
                </tr>
                </form>
            </table>
            <br>
            <br>
            </td>
        </tr>
    </table>
<?
}
?>
