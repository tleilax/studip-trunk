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
// $Id$

page_open(array('sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User'));

$auth->login_if($again && ($auth->auth['uid'] == 'nobody'));

// database object
$db=new DB_Seminar;

// evaluate language clicks
// has to be done before seminar_open to get switching back to german (no init of i18n at all))
if (isset($set_language)) {
	$sess->register('forced_language');
	$forced_language = $set_language;
	$_language = $set_language;
}

// store  user-specific language preference
if ($auth->is_authenticated() && $user->id != 'nobody') {
	// store last language click
	if (isset($forced_language)) {
		$db->query("UPDATE user_info SET preferred_language = '$forced_language' WHERE user_id='$user->id'");
		$_language = $forced_language;
		$sess->unregister('forced_language');
	}
}

include ("seminar_open.php"); // initialise Stud.IP-Session
require_once ("config.inc.php");
require_once 'lib/functions.php';
require_once ("visual.inc.php");
include_once("lib/classes/RSSFeed.class.php");
// -- hier muessen Seiten-Initialisierungen passieren --

// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

$sess->register('index_data');

//Auf und Zuklappen News
require_once ("show_news.php");
process_news_commands($index_data);

// Auf- und Zuklappen Termine
if ($dopen)
	$index_data['dopen']=$dopen;

if ($dclose)
	$index_data['dopen']='';

if (get_config('NEWS_RSS_EXPORT_ENABLE') && ($auth->is_authenticated() && $user->id != 'nobody')){
	$rss_id = StudipNews::GetRssIdFromRangeId('studip');
	if($rss_id){
		$_include_additional_header = '<link rel="alternate" type="application/rss+xml" '
									.'title="RSS" href="' . $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'rss.php?id='.$rss_id.'"/>';
	}
}

$HELP_KEYWORD="Basis.Startseite"; // set keyword for new help

// Start of Output

include ('include/html_head.inc.php'); // Output of html head
include ('include/header.php');

// only for authenticated users
if ($auth->is_authenticated() && $user->id != 'nobody') {
	//                     Menuename, Link, Target
	$menue[ 1] = array( _("Meine Veranstaltungen"), 'meine_seminare.php', false);
	$menue[ 2] = array( _("Veranstaltung hinzuf&uuml;gen"), 'sem_portal.php', false);
	$menue[ 3] = array( _("Mein Planer"), 'calendar.php', false);
	$menue[ 4] = array( _("Terminkalender"), 'calendar.php', false);
	$menue[ 5] = array( _("Adressbuch"), 'contact.php', false);
	$menue[ 6] = array( _("Stundenplan"), 'mein_stundenplan.php', false);
	$menue[ 7] = array( _("pers&ouml;nliche Homepage"), 'about.php', false);
	$menue[ 8] = array( _("individuelle Einstellungen"), 'edit_about.php?view=allgemein', false);
	$menue[ 9] = array( _("Suchen"), 'auswahl_suche.php', false);
	$menue[10] = array( _("Personensuche"), 'browse.php', false);
	$menue[11] = array( _("Veranstaltungssuche"), 'sem_portal.php', false);

	if (get_config("EXTERNAL_HELP")) {
		$help_url=format_help_url("Basis.Allgemeines");
	} else {
		$help_url="help/index.php";
	}
	$menue[12] = array( _("Hilfe"), $help_url, '_new');
	if (get_config("EXTERNAL_HELP")) {
		$help_url=format_help_url("Basis.SchnellEinstiegKomplett");
	} else {
		$help_url="help/index.php?help_page=schnelleinstieg.htm";
	}
	$menue[13] = array( _("Schnelleinstieg"), $help_url, '_new');
	// dozent
	$menue[20] = array( _("Verwaltung von Veranstaltungen"), 'adminarea_start.php?list=TRUE', false);
	$menue[21] = array( _("neue Veranstaltung anlegen"), 'admin_seminare_assi.php?new_session=TRUE', false);
	// admin
	$menue[30] = array( _("Veranstaltungen an meinen Einrichtungen"), 'meine_seminare.php', false);
	$menue[31] = array( _("Verwaltung von Veranstaltungen"), 'adminarea_start.php?list=TRUE', false);
	$menue[32] = array( _("Verwaltung von Einrichtungen"), 'admin_institut.php?list=TRUE', false);
	$menue[33] = array( _("globale Benutzerverwaltung"), 'new_user_md5.php', false);
	// root
	$menue[40] = array( _("Veranstaltungs&uuml;bersicht"), 'sem_portal.php', false);
	$menue[41] = array( _("Verwaltung globaler Einstellungen"), 'new_user_md5.php', false);

	if ($GLOBALS["PLUGINS_ENABLE"] && $perm->have_perm('admin')){
		// plugins activated
		$pluginengine = PluginEngine::getPluginPersistence("Administration");
		$activatedplugins = $pluginengine->getAllActivatedPlugins();
		if (!is_array($activatedplugins)){
			$activatedplugins = array();
		}
		foreach ($activatedplugins as $activatedplugin){
			if ($activatedplugin->hasTopNavigation()){

				$pluginnav = $activatedplugin->getTopNavigation();
				$pluginid = $activatedplugin->getPluginid();

				$menue["pluginnav_" . $pluginid] = array(
				  $pluginnav->getDisplayname(),
				  PluginEngine::getLink($activatedplugin),
				  false);

        $submenu = array();
        foreach ($pluginnav->getSubMenu() as $subkey => $subitem) {
          $menue["pluginnav_" . $pluginid . '_' . $subkey] = array(
            $subitem->getDisplayname(),
            PluginEngine::getLink($activatedplugin,
                                  $subitem->getLinkParams()),
            false);
          $submenu[] = "pluginnav_" . $pluginid . '_' . $subkey;
        }
				$pluginmenue[] = array("pluginnav_" . $activatedplugin->getPluginid(),
				                       $submenu);
			}
		}
	}

	if ($perm->have_perm('root')) { // root
		$ueberschrift = _("Startseite f&uuml;r Root bei Stud.IP");
		//                 array(mainmenue number, array(submenue numbers));
		$menue_auswahl[] = array(40, array());
		$menue_auswahl[] = array(31, array());
		$menue_auswahl[] = array(32, array());
		$menue_auswahl[] = array(41, array());
		$menue_auswahl[] = array(9, array(10, 11));

		//insert plugin menus
		if ($GLOBALS["PLUGINS_ENABLE"]){
			if (!is_array($pluginmenue)){
				$pluginmenue = array();
			}
			foreach($pluginmenue as $item){
				$menue_auswahl[] = $item;
			}
		}

	} elseif ($perm->have_perm('admin')) { // admin
		$ueberschrift = _("Startseite f&uuml;r AdministratorInnen bei Stud.IP");
		$menue_auswahl[] = array(30, array());
		$menue_auswahl[] = array(31, array());
		$menue_auswahl[] = array(32, array());
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(33, array());

		//insert plugin menus
		if ($GLOBALS["PLUGINS_ENABLE"]){
			foreach($pluginmenue as $item){
				$menue_auswahl[] = $item;
			}
		}

	} elseif ($perm->have_perm('dozent')) { // dozent
		$ueberschrift = _("Startseite f&uuml;r DozentInnen bei Stud.IP");
		$menue_auswahl[] = array( 1, array());
		$menue_auswahl[] = array(20, array(21));
		$menue_auswahl[] = array( 3, array(4, 5, 6));
		$menue_auswahl[] = array( 7, array(8));
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(12, array(13));

	} elseif ($perm->have_perm('autor')) { // autor, tutor
		$ueberschrift = _("Ihre pers&ouml;nliche Startseite bei Stud.IP");
		$menue_auswahl[] = array( 1, array(2));
		$menue_auswahl[] = array( 3, array(4, 5, 6));
		$menue_auswahl[] = array( 7, array(8));
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(12, array(13));

	} else { // user
		$ueberschrift = _("Ihre pers&ouml;nliche Startseite bei Stud.IP");
		$menue_auswahl[] = array( 1, array(2));
		$menue_auswahl[] = array( 3, array(4, 5, 6));
		$menue_auswahl[] = array( 7, array());
		$menue_auswahl[] = array( 9, array(10, 11));
		$menue_auswahl[] = array(12, array(13));
		// Warning for Users
	?>
		<div align="center">
		<table width="70%" border=0 cellpadding=0 cellspacing=0 >
		<tr><td class="topicwrite" colspan=3><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/nachricht1.gif" border="0" align="texttop"><b>&nbsp;<?=_("Best�tigungsgsmail beachten!")?></b></td></tr>
		<tr>
			<td width="5%" class="blank" valign="middle">&nbsp;</td>
			<td width="90%" class="blank" valign="top">
				<table cellpadding="2">
					<tr><td class="blank" colspan="2">
					<?
						if (get_config("EXTERNAL_HELP")) {
							$help_url=format_help_url("Basis.AnmeldungMail");
						} else {
							$help_url="help/index.php?help_page=ii_bestaetigungsmail.htm";
						}
						my_info('<font size=-1>' . sprintf(_("Sie haben noch nicht auf Ihre %s Best�tigungsmail %s geantwortet.<br>Bitte holen Sie dies nach, um Stud.IP Funktionen wie das Belegen von Veranstaltungen nutzen zu k�nnen.<br>Bei Problemen wenden Sie Sich an: %s"),'<a href="'.$help_url.'" target="new">','</a>', '<a href="mailto:'.$GLOBALS['UNI_CONTACT'].'">'.$GLOBALS['UNI_CONTACT'].'</a></font>')); ?>
					</td></tr>
				</table>
			</td>
			<td class="blank" align="right" valign="top" background="<?= $GLOBALS['ASSETS_URL'] ?>images/sms3.jpg"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width="235" height="1"></td>
		</tr>
		</table>
		<br><br>
	<?
	}

	// Display banner ad
	if ($GLOBALS['BANNER_ADS_ENABLE']) {
		require_once('lib/banner_show.inc.php');
		banner_show();
	}

	// display menue
	echo	'<div align="center">', "\n", '<table width="70%" border=0 cellpadding=0 cellspacing=0 >', "\n",
		'<tr><td class="topic" colspan=3><img src="'.$GLOBALS['ASSETS_URL'].'images/home.gif" border="0" align="texttop"><b>&nbsp;', $ueberschrift,'</b></td>',"\n",
		'</tr>', "\n";
	echo	'<tr>', "\n", '<td width="5%" class="blank" valign="middle">&nbsp;</td>', "\n",
		'<td width="90%" class="blank" valign="top">', "\n", '<table cellpadding=4>', "\n";

		for ($i=0; $i < count($menue_auswahl); $i++) { // mainmenue
			if ($menue[$menue_auswahl[$i][0]][1]) {
				echo	'<tr><td class="blank"><a href="'.$menue[$menue_auswahl[$i][0]][1]. '"'.
				(($menue[$menue_auswahl[$i][0]][2])? ' target="'.$menue[$menue_auswahl[$i][0]][2].'"':'').
				'><img src="'.$GLOBALS['ASSETS_URL'].'images/forumrot.gif" border=0>&nbsp;'. $menue[$menue_auswahl[$i][0]][0]. '</a>';

			} else {
				echo	'<tr><td class="blank"><img src="'.$GLOBALS['ASSETS_URL'].'images/forumrot.gif" border=0>&nbsp;'. $menue[$menue_auswahl[$i][0]][0];
			}
			for ($k = 0; $k < count($menue_auswahl[$i][1]); $k++) { // submenue
				echo	(($k == 0)? '<br />&nbsp; &nbsp; ':'&nbsp;/&nbsp;');
				echo	'<font size="-1"><a href="',$menue[$menue_auswahl[$i][1][$k]][1].'"'.
					(($menue[$menue_auswahl[$i][1][$k]][2])? ' target="'.$menue[$menue_auswahl[$i][1][$k]][2].'"':'').
					'>'. $menue[$menue_auswahl[$i][1][$k]][0].'</a>';
			}
			echo	'</td></tr>', "\n";
		}
	echo	'</table>'. "\n". '</td>'. "\n".
		'<td class="blank" align="right" valign="top" background="' . $GLOBALS['ASSETS_URL'] . 'images/indexbild.jpg"><img src="' . $GLOBALS['ASSETS_URL'] . 'images/blank.gif" width="235"></td>'. "\n". '</tr>'."\n". '</table> <br />'. "\n";

	// display news
	if (show_news('studip', $perm->have_perm('root'), 0, $index_data['nopen'], "70%", $LastLogin, $index_data))
		echo "<br />\n";
	// display dates
	if (!$perm->have_perm('admin')) { // only dozent, tutor, autor, user
		include('show_dates.inc.php');
		$start = time();
		$end = $start + 60 * 60 * 24 * 7;
		if ($GLOBALS['CALENDAR_ENABLE']) {
			show_all_dates($start, $end, TRUE, FALSE, $index_data['dopen']);
		} else {
			show_dates($start, $end, $index_data['dopen']);
		}
	}
	// display votes
	if ($GLOBALS['VOTE_ENABLE']) {
		include ('show_vote.php');
		show_votes ('studip', $auth->auth['uid'], $perm);
	}

	if ($GLOBALS["PLUGINS_ENABLE"]){
		// PluginEngine aktiviert.
		// Pr�fen, ob PortalPlugins vorhanden sind.
		$portalpluginpersistence = PluginEngine::getPluginPersistence("Portal");
		$activatedportalplugins = $portalpluginpersistence->getAllActivatedPlugins();
		if (!is_array($activatedportalplugins)){
			$activatedportalplugins = array();
		}

		foreach ($activatedportalplugins as $activatedportalplugin){
			if (!$activatedportalplugin->hasAuthorizedView()){
				// skip this plugin
				continue;
			}
			// set the gettext-domain
			$domain = "gtdomain_" . strtolower(get_class($activatedportalplugin));
			bindtextdomain($domain,$plugindbenv->getBasepath() . $activatedportalplugin->getPluginpath() . "/locale");
			textdomain($domain);
			// hier nun die PortalPlugins anzeigen
			if ($activatedportalplugin->hasAdministration()){
				echo "<table class=\"blank\" width=\"70%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><img src=\"" . $activatedportalplugin->getPluginiconname() . "\" border=0 /><b>&nbsp;" . $activatedportalplugin->getDisplaytitle() .
				 " </b></td><td align = \"right\" width=\"1%\" class=\"topic\" nowrap>&nbsp;<a href=\"". PluginEngine::getLink($activatedportalplugin,array(),"showAdministrationPage") ."\"><img src=\"".$GLOBALS['ASSETS_URL']."images/pfeillink.gif\" border=\"0\" alt=\"bearbeiten\" title=\"" . _("Administration") .  "\" ></a>&nbsp;</tr>";
			}
			else {
				echo "<table class=\"blank\" width=\"70%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><img src=\"" . $activatedportalplugin->getPluginiconname() . "\" border=0 /><b>&nbsp;" . $activatedportalplugin->getDisplaytitle() .
				 " </b></td><td align = \"right\" width=\"1%\" class=\"topic\" nowrap>&nbsp;&nbsp;</tr>";
			}
			echo ("<tr><td class=\"steel1\" colspan=\"2\">&nbsp;</td></tr><tr><td class=\"steel1\" colspan=\"2\"><blockquote>");
			$activatedportalplugin->showOverview();
			echo ("</blockquote></td></tr></table><br>\n");
			// restore the domain
			textdomain("studip");
		}
	}
	page_close(); // end session
	$db->query(sprintf("SELECT * FROM rss_feeds WHERE user_id='%s' AND hidden=0 ORDER BY priority",$auth->auth["uid"]));
	while ($db->next_record()) {
		if ($db->f("name")!="" && $db->f("url")!="") {
			$feed = new RSSFeed($db->f("url"));
			if($db->f('fetch_title') && $feed->ausgabe->channel['title']) $feedtitle = $feed->ausgabe->channel['title'];
			else $feedtitle = $db->f("name");
			echo "<table class=\"blank\" width=\"70%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
			echo "<tr><td class=\"topic\"><b>&nbsp;<FONT COLOR=\"white\">" . htmlReady($feedtitle) . "</FONT></b></td></tr>";
			echo "<tr><td class=\"steel1\">&nbsp;</td></tr><tr><td class=\"steel1\"><blockquote>";

			$feed->rssfeed_start();

			echo "</blockquote></td></tr>";
			echo "<tr><td class=\"steel1\">&nbsp;</td></tr>";
			echo "</table><br>\n";
		}
	}



} else { //displaymodul for nobody
	$mtxt  = '<img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="13" height="50" border="0" align="left"><br>'. "\n";
	$mtxt .= '<table cellspacing="0" cellpadding="0" border="0"><tr>'. "\n";
	$mtxt .= '<td class="steel1" width="280" valign="middle"><a class="index" href="%s">'. "\n";
	$mtxt .= '<img src="'.$GLOBALS['ASSETS_URL'].'images/indexpfeil.gif" align=left border="0"><font size="4"><b>%s</b></font><br><font color="#555555" size="1">%s</font></a>&nbsp; </td>'. "\n";
	$mtxt .= '<td class="shadowver" width="3"><img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="3" border="0"></td>'. "\n";
	$mtxt .= '</tr><tr><td class="shadowhor" width="280"><img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="10" height="3" border="0"></td>'. "\n";
	$mtxt .= '<td class="shadowcor" width="3"><img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="3" border="0"></td>'. "\n";
	$mtxt .= '</tr></table>';

	?>

	<table class="blank" width="600"  border="0" cellpadding="0" cellspacing="0" align="center">
	<tr><td colspan=3 class="topic" valign="middle">&nbsp;<b><? echo $UNI_NAME;?></b><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="16" width="5" border="0"></td></tr>
	<tr>
		<td valign="middle" height="260" colspan=3 background="<?= $GLOBALS['ASSETS_URL'] ?>images/startseite.jpg" alt="Stud.IP - <?=$UNI_NAME?>"">
			<?
			echo sprintf($mtxt, 'index.php?again=yes', _("Login"), _("f&uuml;r registrierte NutzerInnen"));
			if (array_search("CAS",$GLOBALS["STUDIP_AUTH_PLUGIN"])){
				echo sprintf($mtxt, 'index.php?again=yes&sso=true', _("Single Sign On"), _("f&uuml;r NutzerInnen des Central Authentication Services"));
			}
			if ($GLOBALS['ENABLE_SELF_REGISTRATION'])
				echo sprintf($mtxt, 'register1.php', _("Registrieren"), _("um NutzerIn zu werden"));
			echo sprintf($mtxt, 'freie.php', _("Freier Zugang"), _("ohne Registrierung"));

			if (get_config("EXTERNAL_HELP")) {
				$help_url=format_help_url("Basis.Allgemeines");
			} else {
				$help_url="help/index.php";
			}
			echo sprintf($mtxt, $help_url, _("Hilfe"), _("zu Bedienung und Funktionsumfang")), '<br>';

			if(isset($UNI_LOGIN_ADD) && ($UNI_LOGIN_ADD != '')) {
				echo '</td></tr>';
				echo '<tr><td align=justify colspan="3" bgcolor="#FFFFFF"><blockquote><font size="-1">&nbsp;<br>';
				echo $UNI_LOGIN_ADD;
				echo '</font></blockquote>';
			}
			?>
		</td>
	</tr>
	<?
	unset($temp_language_key); unset($temp_language);
	?>
	<tr>
	<td class="blank" align="left" valign="middle">
		<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="85" width="38" border="0">
	</td>
	<td class="blank" valign="middle" align="left"><a href="http://www.studip.de"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/logoklein.gif" border="0" <?=tooltip(_("Zur Portalseite"))?>></a></td>
	<td class="blank" align=right nowrap valign="middle">
	<?

	//Statistics
	$db=new DB_Seminar;
	echo "<table cellspacing=\"0\" cellpadding=\"0\">";
	$db->query("SELECT count(*) from seminare");
	$db->next_record();
	$anzahl = $db->f(0);
	echo "<tr><td class=\"steel1\"><font size=\"2\" color=\"#555555\">&nbsp; "._("Aktive Veranstaltungen").":</font></td><td class=\"steel1\" align=right><font size=\"2\" color=\"#555555\">&nbsp; $anzahl&nbsp; </font></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>";
	$db->query("SELECT count(*) from auth_user_md5 WHERE perms <> 'user'");
	$db->next_record();
	$anzahl = $db->f(0);
	echo "<tr><td class=\"steel1\"><font size=\"2\" color=\"#555555\">&nbsp; "._("Registrierte NutzerInnen").":</font></td><td class=\"steel1\" align=right><font size=\"2\" color=\"#555555\">&nbsp; $anzahl&nbsp; </font></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>";
	$active_time = 10; //minutes
	$anzahl = get_users_online_count($active_time);
	echo "<tr><td class=\"steel1\"><font size=\"2\" color=\"#555555\">&nbsp; "._("Davon online").":</font></td><td class=\"steel1\" align=right><font size=\"2\" color=\"#555555\">&nbsp; $anzahl&nbsp; </font></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>";
	echo "<tr><td height=\"30\" class=\"blank\" valign=\"middle\">";
	// choose language
	foreach ($INSTALLED_LANGUAGES as $temp_language_key => $temp_language) {
		printf ("&nbsp;&nbsp;<a href=\"%s?set_language=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/languages/%s\" %s border=\"0\"></a>", $PHP_SELF, $temp_language_key, $temp_language["picture"], tooltip($temp_language["name"]));
	}
	echo "</td><td align= right valign=\"top\" class=\"blank\"><a href=\"./impressum.php?view=statistik\"><font size=\"2\" color=#888888>"._("mehr")."... </font></a></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>";
	echo "</table>";
}
?>
</td></tr>
<tr>
	<td colspan="3" align="center" height="30"></td>
</tr>
<?
if ($GLOBALS["PLUGINS_ENABLE"]){
	$portalpluginpersistence = PluginEngine::getPluginPersistence("Portal");
	$activatedportalplugins = $portalpluginpersistence->getAllActivatedPlugins();
	// we already should have the activatedportalplugins here
	if (!empty($activatedportalplugins)){
		foreach ($activatedportalplugins as $activatedplugin){

			if ($activatedplugin->hasUnauthorizedView()){
?>
				<tr>
					<td colspan="3"class="topic">&nbsp;<b><?= $activatedplugin->getDisplaytitle() ?></b></td>
				</tr>
				<tr>
					<td colspan="3" class="steel1"><blockquote><?= $activatedplugin->showOverview(false) ?><blockquote></td>
				</tr>

				<td colspan="3" align="center" height="30"></td>

				</tr>
<?php
			}
		}
	}
}

echo '</table>';
include ('include/html_end.inc.php');
?>