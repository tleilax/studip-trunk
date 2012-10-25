<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
about.php - Anzeige der persoenlichen Userseiten von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>, Niclas Nohlen <nnohlen@gwdg.de>

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


require '../lib/bootstrap.php';

unregister_globals();

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if(Request::quoted('again') && ($auth->auth["uid"] == "nobody"));
$perm->check("user");



include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- hier muessen Seiten-Initialisierungen passieren --

require_once 'lib/functions.php';
require_once('config.inc.php');
require_once('lib/dates.inc.php');
require_once('lib/messaging.inc.php');
require_once('lib/msg.inc.php');
require_once('lib/statusgruppe.inc.php');
require_once('lib/showNews.inc.php');
require_once('lib/show_dates.inc.php');
require_once('lib/classes/DbView.class.php');
require_once('lib/classes/DbSnapshot.class.php');
require_once('lib/classes/DataFieldEntry.class.php');
require_once('lib/classes/guestbook.class.php');
require_once('lib/object.inc.php');
require_once('lib/classes/score.class.php');
require_once('lib/classes/SemesterData.class.php');
require_once('lib/user_visible.inc.php');
require_once('lib/classes/StudipLitList.class.php');
require_once('lib/classes/Avatar.class.php');
require_once('lib/classes/StudipKing.class.php');

DbView::addView('sem_tree');

function print_kings($username) {

    $uid = get_userid($username);
    $is_king = StudipKing::is_king($uid, TRUE);

    $result = '';
    foreach ($is_king as $type => $text) {
        $type = str_replace('_', '-', $type);
        $result .= Assets::img("crowns/crown-$type.png", array(
            'alt'   => $text,
            'title' => $text
        ));
    }

    if ($result !== '') {
    ?>
        <p>
            <?= $result ?>
        </p>
    <?
    }
}

function prettyViewPermString ($viewPerms) {
    switch ($viewPerms) {
        case 'all'   : return _('alle');
        case 'root'  : return _('SystemadministratorInnen');
        case 'admin' : return _('AdministratorInnen');
        case 'dozent': return _('DozentInnen');
        case 'tutor' : return _('TutorInnen');
        case 'autor' : return _('Studierenden');
        case 'user'  : return _('NutzerInnen');
    }
    return '';
}


function isDataFieldArrayEmpty ($array) {
    foreach ($array as $v)
        if (trim($v->getValue()) != '')
            return false;
    return true;
}


UrlHelper::bindLinkParam('about_data', $about_data);

$current_user = User::findByUsername(Request::get('username', $user->username));

if (get_config('CHAT_ENABLE')){
    include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
    if (Request::get('kill_chat')){
        chat_kill_chat(Request::option('kill_chat'));
    }
}

if (get_config('VOTE_ENABLE')) {
    include_once ("lib/vote/vote_show.inc.php");
}

if (get_config('NEWS_RSS_EXPORT_ENABLE')){
    $news_author_id = StudipNews::GetRssIdFromUserId($current_user->user_id);
    if ($news_author_id) {
        PageLayout::addHeadElement('link', array('rel'   => 'alternate',
                                                 'type'  => 'application/rss+xml',
                                                 'title' => 'RSS',
                                                 'href'  => 'rss.php?id='.$news_author_id));
    }
}


$db = DBManager::get();
$semester = new SemesterData;

$msging = new messaging;

//Buddie hinzufuegen
if (Request::option('cmd') == "add_user") {
    $msging->add_buddy (Request::get('add_uname'), 0);
}


//Auf und Zuklappen Termine
if (Request::option('dopen'))
    $about_data["dopen"]=Request::option('dopen');

if (Request::option('dclose'))
    $about_data["dopen"]='';

//Auf und Zuklappen News
process_news_commands($about_data);

$msg = "";
if ($_SESSION['sms_msg']) {
    $msg = $_SESSION['sms_msg'];
    unset($_SESSION['sms_msg']);
}

// Help
PageLayout::setHelpKeyword("Basis.Homepage");
if($current_user['user_id'] == $user->id && !$current_user['locked']){
    PageLayout::setTitle(_("Mein Profil"));
} elseif ($current_user['user_id'] && ($perm->have_perm("root") || (!$current_user['locked'] && get_visibility_by_id($current_user['user_id'])))) {
    PageLayout::setTitle(_("Profil")  . ' - ' . get_fullname($current_user['user_id']));
} else {
    PageLayout::setTitle(_("Profil"));
    unset($current_user);
}
# and start the output buffering
ob_start();
if (isset($current_user)) {
    $user_id = $current_user->user_id;
    $username = $current_user->username;

    // count views of Page
    if ($user_id != $user->id) {
        object_add_view($user_id);
    } else {
        $homepage_cache_own = time();
        UserConfig::get($user_id)->store('homepage_cache_own', $homepage_cache_own);
    }

    //Wenn er noch nicht in user_info eingetragen ist, kommt er ohne Werte rein
    if ($current_user->mkdate === null) {
        $current_user->store();
    }

    // generische Datenfelder aufsammeln
    $short_datafields = array();
    $long_datafields  = array();
    foreach (DataFieldEntry::getDataFieldEntries($user_id, 'user') as $entry) {
        if ($entry->structure->accessAllowed($perm, $user->id, $user_id) &&
            $entry->getDisplayValue()) {
            if ($entry instanceof DataFieldTextareaEntry) {
                $long_datafields[] = $entry;
            }
            else {
                $short_datafields[] = $entry;
            }
        }
    }

    Navigation::activateItem('/profile/view');

    // TODO this can be removed when page output is moved to a template
    URLHelper::addLinkParam('username', $username);

    // add skip link
    SkipLinks::addIndex(_("Benutzerprofil"), 'user_profile', 100);

    $visibilities = get_local_visibility_by_id($user_id, 'homepage');
    if (is_array(json_decode($visibilities, true))) {
        $visibilities = json_decode($visibilities, true);
    } else {
        $visibilities = array();
    }

    ?>
    <table id="user_profile" width="100%" border="0" cellpadding="1" cellspacing="0">
        <? if ($msg) : ?>
            <?= parse_msg($msg) ?>
        <? endif ?>
        <tr>
            <td class="table_row_even" valign="top">
                <br>
                <?php $avatar_user_id = is_element_visible_for_user($user->id, $user_id, $visibilities['picture']) ? $user_id : 'nobody'; ?>
                <?= Avatar::getAvatar($avatar_user_id)->getImageTag(Avatar::NORMAL) ?>

                <br>
                <br>

                <font size="-1">&nbsp;<?= _("Besucher dieses Profils:") ?>&nbsp;<?= object_return_views($user_id) ?></font>
                <br>

                <?
                // Die Anzeige der Stud.Ip-Punkte
                $score = new Score($user_id);


                if ($score->IsMyScore()) {
                    echo "&nbsp;<a href=\"". URLhelper::getLink("score.php") ."\" " . tooltip(_("Zur Rangliste")) . "><font size=\"-1\">"
                         . _("Ihre Stud.IP-Punkte:") . " ".$score->ReturnMyScore()."<br>&nbsp;"
                         . _("Ihr Rang:") . " ".$score->ReturnMyTitle()."</a></font><br>";
                }
                elseif ($score->ReturnPublik()) {
                    $scoretmp = $score->GetScore($user_id);
                    $title = $score->gettitel($scoretmp, $score->GetGender($user_id));
                    echo "&nbsp;<a href=\"". URLhelper::getLink("score.php") ."\"><font size=\"-1\">"
                         . _("Stud.IP-Punkte:") . " ".$scoretmp."<br>&nbsp;"
                         . _("Rang:") . " ".$title."</a></font><br>";
                }

                if ($username != $auth->auth["uname"]) {
                    if (CheckBuddy($username)==FALSE) {
                        echo "<br><a href=\"". URLHelper::getLink("?cmd=add_user&add_uname=".$username) ."\">"
                             . Assets::img('icons/16/blue/person.png', array('title' =>_("zu den Kontakten hinzuf�gen"), 'class' => 'middle'))
                             . " " . _("zu den Kontakten hinzuf�gen") . " </a>";
                    }
                    echo "<br><a href=\"". URLHelper::getLink("sms_send.php?sms_source_page=about.php&rec_uname=".$username) ."\">"
                         . Assets::img('icons/16/blue/mail.png', array('title' => _("Nachricht an Nutzer verschicken"), 'class' => 'middle'))
                         . " " . _("Nachricht an Nutzer") . "</a>";

                }

                // Export dieses Users als Vcard
                echo "<br><a href=\"". URLHelper::getLink("contact_export.php") ."\">"
                     . Assets::img('icons/16/blue/vcard.png', array('title' => _("vCard herunterladen"), 'class' => 'middle'))
                     . " " . _("vCard herunterladen") ."</a>";

                if (($username != $auth->auth['uname']) && $GLOBALS['perm']->have_perm('root')) {
                    echo '<br>';
                    printf('<a href="%s">%s %s</a>',
                           URLHelper::getLink('dispatch.php/admin/user/edit/' . $user_id),
                           Assets::img('icons/16/blue/edit', array('title' => _('Diesen Benutzer bearbeiten'), 'class' => 'middle')),
                           _('Diesen Benutzer bearbeiten'));
                }

                ?>

                <br>
                <br>
            </td>

            <td class="table_row_even" width="99%" valign="top" style="padding: 10px;">
                <h1><?= htmlReady($current_user->getFullName()) ?></h1>
                    <? if ($current_user['motto'] &&
                            is_element_visible_for_user($user->id, $user_id, $visibilities['motto'])) : ?>
                        <h3><?= htmlReady($current_user['motto']) ?></h3>
                    <? endif ?>

                    <? if (!get_visibility_by_id($user_id)) : ?>
                        <? if ($user_id != $user->id) : ?>
                            <p>
                                <font color="red"><?= _("(Dieser Nutzer ist unsichtbar.)") ?></font>
                            </p>
                        <? else : ?>
                            <p>
                                <font color="red"><?= _("(Sie sind unsichtbar. Deshalb k�nnen nur Sie diese Seite sehen.)") ?></font>
                            </p>
                        <? endif ?>
                    <? endif ?>

                    <br>

                    <? if (($email = get_visible_email($user_id)) != '') : ?>
                        <b>&nbsp;<?= _("E-Mail:") ?></b>
                        <a href="mailto:<?= htmlReady($email) ?>"><?= htmlReady($email) ?></a>
                        <br>
                    <? endif ?>

                    <? if ($current_user["privatnr"] != "" &&
                            is_element_visible_for_user($user->id, $user_id, $visibilities['private_phone'])) : ?>
                        <b>&nbsp;<?= _("Telefon (privat):") ?></b>
                        <?= htmlReady($current_user["privatnr"]) ?>
                        <br>
                    <? endif ?>

                    <? if ($current_user["privatcell"] != "" &&
                            is_element_visible_for_user($user->id, $user_id, $visibilities['private_cell'])) : ?>
                        <b>&nbsp;<?= _("Mobiltelefon:") ?></b>
                        <?= htmlReady($current_user["privatcell"]) ?>
                        <br>
                    <? endif ?>

                    <? if (get_config("ENABLE_SKYPE_INFO") &&
                           UserConfig::get($user_id)->SKYPE_NAME &&
                           is_element_visible_for_user($user->id, $user_id, $visibilities['skype_name'])) : ?>
                        <?php $skype_name = UserConfig::get($user_id)->SKYPE_NAME ?>
                        <b>&nbsp;<?= _("Skype:") ?></b>
                        <a href="skype:<?= htmlReady($skype_name) ?>?call">
                            <? if (UserConfig::get($user_id)->SKYPE_ONLINE_STATUS &&
                           is_element_visible_for_user($user->id, $user_id, $visibilities['skype_online_status'])) : ?>
                                <img src="http://mystatus.skype.com/smallicon/<?= htmlReady($skype_name) ?>" style="vertical-align:middle;" width="16" height="16" alt="My status">
                            <? else : ?>
                                <?= Assets::img('icon_small_skype.gif', array('style' => 'vertical-align:middle;')) ?>
                            <? endif ?>
                            <?= htmlReady($skype_name) ?>
                        </a>
                        <br>
                    <? endif ?>

                    <? if ($current_user["privadr"] != "" &&
                            is_element_visible_for_user($user->id, $user_id, $visibilities['privadr'])) : ?>
                        <b>&nbsp;<?= _("Adresse (privat):") ?></b>
                        <?= htmlReady($current_user["privadr"]) ?>
                        <br>
                    <? endif ?>

                    <? if ($current_user["Home"] != "" &&
                            is_element_visible_for_user($user->id, $user_id, $visibilities['homepage'])) : ?>
                        <b>&nbsp;<?= _("Homepage:") ?></b>
                        <?= formatLinks($current_user["Home"]) ?>
                        <br>
                    <? endif ?>

                    <? if ($perm->have_perm("root") && $current_user['locked']) : ?>
                        <br>
                        <b>
                            <font color="red" size="+1"><?= _("BENUTZER IST GESPERRT!") ?></font>
                        </b>
                        <br>
                    <? endif ?>

                    <?
                    // Anzeige der Institute an denen (hoffentlich) studiert wird:

                    if($current_user['perms'] != 'dozent') {
                        $sth = $db->prepare("SELECT Institute.* FROM user_inst LEFT JOIN Institute  USING (Institut_id) WHERE user_id = ? AND inst_perms = 'user'");
                        $sth->execute(array($user_id));
                        $inst_results = $sth->fetchAll(PDO::FETCH_ASSOC);
                        if (count($inst_results) && is_element_visible_for_user($user->id, $user_id, $visibilities['studying'])) {
                            echo "<br><b>&nbsp;" . _("Wo ich studiere:") . "&nbsp;&nbsp;</b><br>";
                            foreach ($inst_results as $inst_result) {
                                echo "&nbsp; &nbsp; &nbsp; &nbsp;<a href=\"". URLHelper::getLink("institut_main.php?auswahl=".$inst_result["Institut_id"]) ."\">".htmlReady($inst_result["Name"])."</a><br>";
                            }
                        }
                    }

                    // Anzeige der Institute an denen gearbeitet wird

                    $query = "SELECT a.*,b.Name FROM user_inst a LEFT JOIN Institute b USING (Institut_id) ";
                    $query .= "WHERE user_id = ? AND inst_perms != 'user' AND visible = 1 ORDER BY priority ASC";
                    $sth = $db->prepare($query);
                    $sth->execute(array($user_id));
                    $inst_results = $sth->fetchAll(PDO::FETCH_ASSOC);
                    IF (count($inst_results)) {
                        echo "<br><b>&nbsp;" . _("Wo ich arbeite:") . "&nbsp;&nbsp;</b><br>";
                    }

                    //schleife weil evtl. mehrere sprechzeiten und institut nicht gesetzt...

                    foreach ($inst_results as $inst_result) {
                        $institut = $inst_result["Institut_id"];
                        echo "&nbsp; &nbsp; &nbsp; &nbsp;<a href=\"". URLHelper::getLink("institut_main.php?auswahl=".$institut) ."\">".htmlReady($inst_result["Name"])."</a>";

                        echo "<font size=-1>";
                        IF ($inst_result["raum"]!="")
                            echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Raum:") . " </b>", htmlReady($inst_result["raum"]);
                        IF ($inst_result["sprechzeiten"]!="")
                            echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Sprechzeit:") . " </b>", htmlReady($inst_result["sprechzeiten"]);
                        IF ($inst_result["Telefon"]!="")
                            echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Telefon:") . " </b>", htmlReady($inst_result["Telefon"]);
                        IF ($inst_result["Fax"]!="")
                            echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; " . _("Fax:") . " </b>", htmlReady($inst_result["Fax"]);

                        echo '<table cellspacing="0" cellpadding="0" border="0">';
                        $entries = DataFieldEntry::getDataFieldEntries(array($user_id, $institut));
                        if (!isDataFieldArrayEmpty($entries)) {
                            foreach ($entries as $entry) {
                                $view = DataFieldStructure::permMask($auth->auth['perm']) >= DataFieldStructure::permMask($entry->structure->getViewPerms());
                                $show_star = false;
                                if (!$view && ($user_id == $user->id)) {
                                    $view = true;
                                    $show_star = true;
                                }

                                if (trim($entry->getValue()) && $view) {
                                    echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td>' . htmlReady($entry->getName()) . ": " .'&nbsp;&nbsp;</td><td>'. $entry->getDisplayValue();
                                    if ($show_star) echo ' *';
                                }
                            }
                        }

                        echo '</table>';

                        if ($groups = GetAllStatusgruppen($institut, $user_id)) {
                            $default_entries = DataFieldEntry::getDataFieldEntries(array($user_id, $institut));
                            $data = get_role_data_recursive($groups, $user_id, $default_entries);
                            echo '<table cellpadding="0" cellspacing="0" border="0">';
                            echo $data['standard'];
                            echo '</table>';
                        } else {
                            echo '<br>';
                        }

                        echo "</font>";
                        echo '<br>';
                    }

                    if (($user_id == $user->id) && $GLOBALS['has_denoted_fields']) {
                        echo '<br>';
                        echo '<font size="-1">';
                        echo ' * Diese Felder sind nur f�r Sie und AdministratorInnen sichtbar.<br>';
                        echo '</font>';
                    }

                    if ($score->IsMyScore() || $score->ReturnPublik()) {
                        echo "<p>";
                        print_kings($username);
                    }

                    ?>


                    <br>

                    <? foreach ($short_datafields as $entry) : ?>

                        <?
                        $vperms = $entry->structure->getViewPerms();
                        $visible = 'all' == $vperms
                                   ? _("sichtbar f�r alle")
                                   : sprintf(_("sichtbar nur f�r Sie und alle %s"),
                                             prettyViewPermString($vperms));
                        ?>

                        &nbsp;<strong><?= htmlReady($entry->getName()) ?>:</strong>
                        <?= $entry->getDisplayValue() ?>
                        <span class="minor">(<?= $visible ?>)</span>
                        <br>
                    <? endforeach ?>
            </td>
    </tr>
    </table>

    <br>

    <?

    // News zur person anzeigen!!!
    $show_admin = ($perm->have_perm("autor") && $auth->auth["uid"] == $user_id) ||
        (isDeputyEditAboutActivated() && isDeputy($auth->auth["uid"], $user_id, true));
    if (is_element_visible_for_user($user->id, $user_id, $visibilities['news'])) {
        show_news($user_id, $show_admin, 0, $about_data["nopen"], "100%", 0, $about_data);
    }

    // alle persoenlichen Termine anzeigen, aber keine privaten
    if (get_config('CALENDAR_ENABLE')) {
        if ($current_user['perms'] != "root" && $current_user['perms'] != "admin") {
            $start_zeit = time();
            $show_admin = ($perm->have_perm("autor") &&
                $auth->auth["uid"] == $user_id);
            if (is_element_visible_for_user($user->id, $user_id, $visibilities['termine']))
                show_personal_dates($user_id, $start_zeit, -1, FALSE, $show_admin, $about_data["dopen"]);
        }
    }

    // include and show friend-of-a-friend list
    // (direct/indirect connection via buddy list)
    if ($GLOBALS['FOAF_ENABLE']
        && ($auth->auth['uid']!=$user_id)
        && UserConfig::get($user_id)->FOAF_SHOW_IDENTITY) {
            include("lib/classes/FoafDisplay.class.php");
            $foaf=new FoafDisplay($auth->auth['uid'], $user_id, $username);
            $foaf_open = Request::option('foaf_open');
            $foaf->show($foaf_open);
    }

    // include and show votes and tests
    if (get_config('VOTE_ENABLE') && is_element_visible_for_user($user->id, $user_id, $visibilities['votes'])) {
        show_votes($username, $auth->auth["uid"], $perm, YES);
    }


    // show Guestbook
    $guest = new Guestbook($user_id, Request::int('guestpage', 0));

    if (($guest->active == TRUE || $guest->rights == TRUE) && is_element_visible_for_user($user->id, $user_id, $visibilities['guestbook'])) {
        if (Request::quoted('guestbook') && $perm->have_perm('autor')) {
            $guestbook = Request::quoted('guestbook');
            $post = Request::quoted('post');
            $deletepost = Request::quoted('deletepost');
            $studipticket = Request::option('studipticket');
            $guest->actionsGuestbook($guestbook,$post,$deletepost,$studipticket);
        }
        $guest->showGuestbook();
    }

    // show chat info
    if (get_config('CHAT_ENABLE')) {
        chat_show_info($user_id);
    }

    $layout = $GLOBALS['template_factory']->open('shared/index_box');

    // show literature info
    if (get_config('LITERATURE_ENABLE')) {
        // Ausgabe von Literaturlisten
        $lit_list = StudipLitList::GetFormattedListsByRange($user_id);
        if ($user_id == $user->id){
            $layout->admin_url = 'admin_lit_list.php?_range_id=self';
            $layout->admin_title = _('Literaturlisten bearbeiten');
        }

        if (is_element_visible_for_user($user->id, $user_id, $visibilities['literature'])) {
            echo $layout->render(array('title' => _('Literaturlisten'), 'content_for_layout' => $lit_list));
            $layout->clear_attributes();
        }
    }

    // Hier werden Lebenslauf, Hobbys, Publikationen und Arbeitsschwerpunkte ausgegeben:
    $ausgabe_felder = array('lebenslauf' => _("Lebenslauf"),
                'hobby' => _("Hobbys"),
                'publi' => _("Publikationen"),
                'schwerp' => _("Arbeitsschwerpunkte")
                );

    foreach ($ausgabe_felder as $key => $value) {
        if (is_element_visible_for_user($user->id, $user_id, $visibilities[$key]))
            echo $layout->render(array('title' => $value, 'content_for_layout' => formatReady($current_user[$key])));
    }

    $layout->clear_attributes();

    // add the free administrable datafields (these field are system categories -
    // the user is not allowed to change the categories)
    foreach ($long_datafields as $entry) {
        if (is_element_visible_for_user($user->id, $user_id, $visibilities[$entry->getName()])) {
            $vperms = $entry->structure->getViewPerms();
            $visible = 'all' == $vperms
                       ? _("sichtbar f�r alle")
                       : sprintf(_("sichtbar nur f�r Sie und alle %s"),
                                 prettyViewPermString($vperms));
            echo $layout->render(array('title' => $entry->getName() . "($visible)", 'content_for_layout' => $entry->getDisplayValue()));
        }
    }

    $layout->clear_attributes();

    // Pr�fen, ob HomepagePlugins vorhanden sind.
    $homepageplugins = PluginEngine::getPlugins('HomepagePlugin');

    foreach ($homepageplugins as $homepageplugin){
        // hier nun die HomepagePlugins anzeigen
        $template = $homepageplugin->getHomepageTemplate($user_id);

        if ($template) {
            echo $template->render(NULL, $layout);
            $layout->clear_attributes();
        }
    }

    //add the own categories - this ones are self created by the user
    $query = "SELECT name, content, kategorie_id
              FROM kategorien
              WHERE range_id = ?
              ORDER BY priority";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($user_id));
    while ($category = $statement->fetch())  {
        $head = $category['name'];
        $body = $category['content'];
        if ($user->id == $user_id) {
            switch ($visibilities['kat_' . $category['kategorie_id']]) {
                case VISIBILITY_ME:
                    $vis_text = _('nur f�r mich sichtbar');
                    break;
                case VISIBILITY_BUDDIES:
                    $vis_text = _('nur f�r meine Buddies sichtbar');
                    break;
                case VISIBILITY_DOMAIN:
                    $vis_text = _('nur f�r meine Nutzerdom�ne sichtbar');
                    break;
                case VISIBILITY_EXTERN:
                    $vis_text = _('auf externen Seiten sichtbar');
                    break;
                default:
                case VISIBILITY_STUDIP:
                    $vis_text = _('f�r alle Stud.IP-Nutzer sichtbar');
                    break;
            }
            $head .= ' (' . $vis_text . ')';
        }
        // oeffentliche Rubrik oder eigene Homepage
        if (is_element_visible_for_user($user->id, $user_id, $visibilities['kat_' . $category['kategorie_id']])) {
            echo $layout->render(array('title' => $head, 'content_for_layout' => formatReady($body)));
        }
    }

    // Anzeige der Seminare
    if ($current_user['perms'] == 'dozent') {
        $all_semester = SemesterData::GetSemesterArray();
        $current_semester_index = SemesterData::GetInstance()->GetSemesterIndexById(Semester::findCurrent()->semester_id);
        if ($current_semester_index && isset($all_semester[$current_semester_index + 1])) {
            $start_semester_index = $current_semester_index + 1;
        } else {
            $start_semester_index = count($all_semester) - 1;
        }
        $view = new DbView();
        $output = '';
        for ($i = $start_semester_index; $i > $start_semester_index - 3; --$i){
            $view->params[0] = $user_id;
            $view->params[1] = "dozent";
            $view->params[2] = " HAVING (sem_number <= $i AND (sem_number_end >= $i OR sem_number_end = -1)) ";
            $snap = new DbSnapshot($view->get_query("view:SEM_USER_GET_SEM"));
            if ($snap->numRows){
                $sem_name = $all_semester[$i]['name'];
                if ($output) $output .= '<br>';
                $output .= "<font size=\"+1\"><b>$sem_name</b></font><br><br>";
                $snap->sortRows("Name");
                while ($snap->nextRow()) {
                    $ver_name = $snap->getField("Name");
                    $sem_number_start = $snap->getField("sem_number");
                    $sem_number_end = $snap->getField("sem_number_end");
                    if ($sem_number_start != $sem_number_end){
                        $ver_name .= " (" . $all_semester[$sem_number_start]['name'] . " - ";
                        $ver_name .= (($sem_number_end == -1) ? _("unbegrenzt") : $all_semester[$sem_number_end]['name']) . ")";
                    }
                    $output .= '<b><a href="'. URLHelper::getLink("details.php?sem_id=".$snap->getField('Seminar_id')). '">' . htmlReady($ver_name) . '</a></b><br>';
                }
            }
        }

        echo $layout->render(array('title' => _('Veranstaltungen'), 'content_for_layout' => $output));
    }
} else {
    echo MessageBox::error(_("Dieses Profil ist nicht verf�gbar."), array(_("Der Benutzer hat sich unsichtbar geschaltet oder ist im System nicht vorhanden.")));
}

# get the layout template
$layout = $GLOBALS['template_factory']->open('layouts/base_without_infobox');

$layout->content_for_layout = ob_get_clean();

echo $layout->render();

// Save data back to database.
page_close();
