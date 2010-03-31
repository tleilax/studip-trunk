<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
/**
 * online.php
 *
 * Anzeigemodul fuer Personen die Online sind
 *
 * PHP Version 5
 *
 * @author      Andr� Noack <andre.noack@gmx.net>
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @copyright   2002-2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 * @package     studip_core
 * @access      public
 */

page_open(array(
    "sess" => "Seminar_Session",
    "auth" => "Seminar_Auth",
    "perm" => "Seminar_Perm",
    "user" => "Seminar_User"
));
$perm->check("user");

// Imports
require_once 'lib/functions.php';
require_once 'lib/msg.inc.php';
require_once 'lib/visual.inc.php';
require_once 'lib/messaging.inc.php';
require_once 'lib/contact.inc.php';
require_once 'lib/user_visible.inc.php';
require_once 'lib/classes/Avatar.class.php';
require_once 'lib/classes/StudipKing.class.php';

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

if ($GLOBALS['CHAT_ENABLE'])
{
    include_once $RELATIVE_PATH_CHAT.'/chat_func_inc.php';
    $chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
    $chatServer->caching = true;
}

$msging=new messaging;
$cssSw=new cssClassSwitcher;

$HELP_KEYWORD="Basis.InteraktionWhosOnline";
$CURRENT_PAGE = _("Wer ist online?");

if (Request::get('change_view')) {
    Navigation::activateItem('/account/messaging');
} else {
    Navigation::activateItem('/community/who');
}

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

ob_start();

$kompletter_datensatz= get_users_online($my_messaging_settings['active_time'], $user->cfg->getValue($user->id, "ONLINE_NAME_FORMAT"));
$alle=count($kompletter_datensatz);
/*
 * Start to filter
 */

//Only use visible users
$visible_users=array();

foreach($kompletter_datensatz as $key=>$val){
    if($val['is_visible']){
        $visible_users[$key]=$val;
    }
}

if ($cmd=="add_user") {
    $msging->add_buddy ($add_uname);
    $visible_users[$add_uname]['is_buddy'] = true;
}

if ($cmd=="delete_user"){
    $msging->delete_buddy ($delete_uname);
    $visible_users[$delete_uname]['is_buddy'] = false;
}

//now seperate the buddies from the others
$filtered_buddies=array();
$others=array();

foreach($visible_users as $key=>$val){
    if($val['is_buddy']){
        $filtered_buddies[$key]=$val;
    } else {
        $others[$key]=$val;
    }
}

$user_count = count($others);
$weitere = $alle - count($filtered_buddies) - $user_count;

$page = Request::int('page', 1);

if($page < 1 || $page > ceil($user_count/25)) $page = 1;

//Slice the array to limit data
$other_users = array_slice($others,($page-1) * 25, 25);

if ($sms_msg) {
    $msg = $sms_msg;
    $sms_msg = '';
    $sess->unregister('sms_msg');
}

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
    include 'lib/include/messagingSettings.inc.php';
    change_messaging_view();
    echo "</tr></td></table>";
    page_close();
    die;
}


?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<?
if ($msg)
    {
    echo"<tr><td class=\"blank\"colspan=2><br>";
    parse_msg ($msg);
    echo"</td></tr>";
    }

    ?>
    <tr>
        <td class="onlineinfo">
        <?
        print(_("Hier k&ouml;nnen Sie sehen, wer au&szlig;er Ihnen im Moment online ist.") . "<p>");
        printf(_("Sie k&ouml;nnen diesen Usern eine Nachricht schicken %s oder sie zum Chatten %s einladen."), sprintf("<img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" width=\"24\" height=\"21\" %s border=\"0\"><br>", tooltip(_("Nachricht an User verschicken"))), sprintf("<img src=\"".$GLOBALS['ASSETS_URL']."images/chat1.gif\" width=\"24\" height=\"21\" %s border=\"0\">", tooltip(_("zum Chatten einladen"))));
        print("\n<br>" . _("Wenn Sie auf den Namen klicken, kommen Sie zur Homepage des Users."));

        if ($SessSemName[0] && $SessSemName["class"] == "inst")
            echo "<br><br><a href=\"institut_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung") . "</a>";
        elseif ($SessSemName[0])
            echo "<br><br><a href=\"seminar_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung") . "</a>";
        ?>
        <td class="blank" align="right"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/online.jpg" border="0"></td>
    </tr>
    <tr>
        <td class="blank" colspan="2">
    <?
ob_end_flush();
ob_start();
    //Erzeugen der Liste aktiver und inaktiver Buddies
    $different_groups=FALSE;


    $owner_id = $user->id;
    $db=new DB_Seminar;
    $db2=new DB_Seminar;



        foreach($filtered_buddies as $username=>$value) { //alle durchgehen die online sind
            $user_id = $value["userid"];
                    $db2->query ("SELECT statusgruppen.position, name, statusgruppen.statusgruppe_id FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE range_id = '$owner_id' AND user_id = '$user_id' ORDER BY statusgruppen.position ASC LIMIT 1");
                    if ($db2->next_record()) { // er ist auch einer Gruppe zugeordnet
                        $group_buddies[]=array($db2->f("position"), $db2->f("name"), $filtered_buddies[$username]["name"],$filtered_buddies[$username]["last_action"],$username,$db2->f("statusgruppe_id"),$user_id);
                    } else {    // buddy, aber keine Gruppe
                        $non_group_buddies[]=array($filtered_buddies[$username]["name"],$filtered_buddies[$username]["last_action"],$username,$user_id);
                    }
        }

    foreach($other_users as $username=>$value) {
        $user_id = $value["userid"];
            $n_buddies[]=array($other_users[$username]["name"],$other_users[$username]["last_action"],$username,$user_id);
    }


 if (is_array($group_buddies))
    sort ($group_buddies);

if (is_array($non_group_buddies))
    sort ($non_group_buddies);

    $cssSw->switchClass();
    //Anzeige
?>
    <table width="100%" cellspacing="0" border="0" cellpadding="2">
        <tr>

<?  //Kopfzeile
    if ($my_messaging_settings["show_only_buddys"])
        echo "\n<td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=1 height=20><font size=-1><b>" . _("Buddies") . "</b></font></td></tr>\n";
    else
        echo "\n<td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=1 height=20><font size=-1><b>" . _("Buddies") . "</b></font></td><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=1 height=20><font size=-1><b>" . _("andere Nutzer") . "</b></font></td></tr>\n";
    echo "<tr>";

    //Buddiespalte

    // Nutzer hat gar keine buddies
    if (!GetNumberOfBuddies()) { ?>
        <td width="50%" valign="top">
            <table width="100%" cellspacing="0" cellpadding="1" border="0">
                <tr>
                    <td class="steel1" width="50%" align="center">
                        <font size="-1">
                            <?= _("Sie haben keine Buddies ausgew&auml;hlt.") ?>
                            <br>
                            <? printf(_("Zum Adressbuch (%d Eintr&auml;ge) klicken Sie %shier%s"),
                                      GetSizeofBook(),
                                      "<a href=\"contact.php\">", "</a>") ?>
                        </font>
                    </td>
                </tr>
            </table>
        </td>

    <? } else { // nutzer hat prinzipiell buddies ?>

        <td width="50%" valign="top">
            <table width="100%" cellspacing="0" cellpadding="1" border="0">
                <? if ($group_buddies || $non_group_buddies) { ?>
                    <tr>
                        <td class="steelgraudunkel" colspan="3" width="65%">
                            <font size="-1" color="white">
                                &nbsp;
                                <b><?= _("Name") ?></b>
                            </font>
                        </td>
                        <td class="steelgraudunkel" width="20%" colspan="4">
                            <font size="-1" color="white">
                                <b><?= _("letztes Lebenszeichen") ?></b>
                            </font>
                        </td>
                    </tr>
                <? } else { // gar keine Buddies online ?>
                    <tr>
                        <td class="steelgraudunkel" width="50%" align="center" colspan="7">
                            <font size="-1" color="white">
                                <b><?= _("Es sind keine Ihrer Buddies online.") ?></b>
                            </font>
                        </td>
                    </tr>
                <? } ?>


                <? if (sizeof($group_buddies)) {
                    reset ($group_buddies);
                    $lastgroup = "";
                    $groupcount = 0;
                    $template = $GLOBALS['template_factory']->open('online/user');
                    while (list($index)=each($group_buddies)) {
                        list($position,$gruppe,$fullname,$zeit,$tmp_online_uname,$statusgruppe_id,$tmp_user_id)=$group_buddies[$index];
                        //list($fullname, $zeit, $tmp_online_uname, $tmp_user_id) = $n_buddies[$index];
                        if ($gruppe != $lastgroup) {// Ueberschrift fuer andere Gruppe
                            printf("\n<tr><td colspan=\"7\" align=\"middle\" class=\"steelkante\"><a href=\"contact.php?view=gruppen&filter=%s\"><font size=\"2\" color=\"#555555\">%s</font></a></td></tr>",$statusgruppe_id, htmlready($gruppe));
                            $groupcount++;
                            if ($groupcount > 10) //irgendwann gehen uns die Farben aus
                                $groupcount = 1;
                        }
                        $lastgroup = $gruppe;
                        $args = compact('fullname', 'zeit', 'tmp_online_uname', 'tmp_user_id');
                        $args['gruppe'] = "gruppe$groupcount";
                        $args['is_buddy'] = TRUE;
                        $template->clear_attributes();
                        echo $template->render($args);
                        $cssSw->switchClass();
                    }
                }

                if (sizeof($non_group_buddies)) {
                    echo "\n<tr><td colspan=7 class=\"steelkante\" align=\"center\"><font size=-1 color=\"#555555\"><a href=\"contact.php?view=gruppen&filter=all\"><font size=-1 color=\"#555555\">"._("Buddies ohne Gruppenzuordnung").":</font></a></font></td></tr>";
                    reset ($non_group_buddies);
                    $template = $GLOBALS['template_factory']->open('online/user');
                    while (list($index)=each($non_group_buddies)) {
                        list($fullname,$zeit,$tmp_online_uname,$tmp_user_id)=$non_group_buddies[$index];
                        $args = compact('fullname', 'zeit', 'tmp_online_uname', 'tmp_user_id');
                        $args['is_buddy'] = TRUE;
                        $template->clear_attributes();
                        echo $template->render($args);
                    }
                } ?>

                <tr>
                    <td class="blank" width="50%" align="center" colspan="7">
                        <font size="-1">
                            <br>
                            Zum Adressbuch (<?= GetSizeofBook() ?> Eintr�ge) klicken Sie
                            <a href="<?= URLHelper::getLink("contact.php") ?>">
                                hier
                            </a>
                        </font>
                    </td>
                </tr>
            </table>
        </td>

    <? }

    ob_end_flush();
    ob_start();

    //Spalte anderer Benutzer
    if (!$my_messaging_settings["show_only_buddys"])
    {
        echo "\n<td width=\"50%\" valign=\"top\">";
        echo "\n<table width=\"100%\" cellspacing=0 cellpadding=1 border=0><tr>\n";

        if (is_array($n_buddies)) {
            echo "\n<td class=\"steelgraudunkel\"  colspan=3><font size=-1 color=\"white\"><b>&nbsp;" . _("Name") . "</b></font></td><td class=\"steelgraudunkel\" colspan=4><font size=-1 color=\"white\"><b>" . _("letztes Lebenszeichen") . "</b></font></td></tr>\n";
            reset($n_buddies);
            $template = $GLOBALS['template_factory']->open('online/user');
            while (list($index)=each($n_buddies)) {
                list($fullname, $zeit, $tmp_online_uname, $tmp_user_id) = $n_buddies[$index];
                $args = compact('fullname', 'zeit', 'tmp_online_uname', 'tmp_user_id');
                $args['background'] = $cssSw->getClass();
                $args['is_buddy'] = FALSE;
                $template->clear_attributes();
                echo $template->render($args);
                $cssSw->switchClass();
            }

           } else {
            // if we previously found unvisible users who are online
            if ($weitere > 0) {
            ?>
            <tr>
                <td class="steelgraudunkel" align="center">
                    <font size="-1" color="white">
                        <b>&nbsp;<?=_("Keine sichtbaren Nutzer online.")?></b>
                    </font>
                </td>
            </tr>
            <?
            } else {
            ?>
            <td class="steelgraudunkel" width="50%" align="center">
                <font size="-1" color="white">
                    <b><?=_("Kein anderer Nutzer ist online.")?></b>
                </font>
            </td>
            </tr>
            </table>
            </td>
            <?
            }
        }
    }
?>
            </tr>
            </table>
            <? if ($user_count > 25) : ?>
            <div style="text-align:right; padding-top: 2px; padding-bottom: 2px" class="steelgraudunkel">
            <?
            $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
            $pagination->clear_attributes();
            $pagination->set_attribute('perPage', 25);
            $pagination->set_attribute('num_postings', $user_count);
            $pagination->set_attribute('page', $page);
            $pagination->set_attribute('pagelink', 'online.php?page=%s');
            echo $pagination->render("shared/pagechooser");
            ?>
            </div>
            <? endif; ?>
            <? if ($weitere > 0) : ?>
                <div align="center"><font size="-1" align="center"><br><?=sprintf(_("+ %s unsichtbare NutzerInnen"), $weitere)?></font></div>
            <? endif; ?>
        </td>
    </tr>
</table>
<?php
    ob_end_flush();
    include ('lib/include/html_end.inc.php');
    page_close();
?>
