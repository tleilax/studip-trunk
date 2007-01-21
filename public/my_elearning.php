<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// my_elearning.php
//
// Copyright (c) 2005 Arne Schroeder <schroeder@data-quest.de>
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("autor");

include ("seminar_open.php"); // initialise Stud.IP-Session

require_once ('config.inc.php');
require_once ('lib/visual.inc.php');

include ('include/html_head.inc.php'); // Output of html head
include ('include/header.php');   // Output of Stud.IP head


if ($ELEARNING_INTERFACE_ENABLE)
{
	require_once ($RELATIVE_PATH_ELEARNING_INTERFACE . "/ELearningUtils.class.php");
	ELearningUtils::bench("start");


	include ('include/links_about.inc.php');

	if ($elearning_open_close["type"] != "user")
	{
		$sess->unregister("elearning_open_close");
		unset($elearning_open_close);
	}
	$elearning_open_close["type"] = "user";
	$elearning_open_close["id"] = $auth->auth["uid"];
	if (isset($do_open))
		$elearning_open_close[$do_open] = true;
	elseif (isset($do_close))
		$elearning_open_close[$do_close] = false;
	$sess->register("elearning_open_close");


	?><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="3">&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/icon-lern.gif" align="texttop">&nbsp;
		<b>
		<?
			echo _("Meine Lernmodule und Benutzer-Accounts");
		?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="3">&nbsp;
		</td>
	</tr>
	<tr valign="top">
                <td width="1%" class="blank">
                	&nbsp;
                </td>
		<td width="90%" class="blank">
	<?

	if ($new_account_cms != "")
		$new_account_form = ELearningUtils::getNewAccountForm($new_account_cms);
	foreach($ELEARNING_INTERFACE_MODULES as $cms => $cms_preferences)
		if (ELearningUtils::isCMSActive($cms) AND ($cms_preferences["auth_necessary"] == true))
		{
			ELearningUtils::loadClass($cms);
			ELearningUtils::bench("load cms $cms");
			$new_module_form[$cms] = ELearningUtils::getNewModuleForm($cms);
		}

	if ($messages["info"] != "")
	{
		echo "<table>";
		my_info($messages["info"]);
		echo "</table>";
	}
	if ($messages["error"] != "")
	{
		echo "<table>";
		my_error($messages["error"]);
		echo "</table>";
	}

	ELearningUtils::bench("init");

	echo $page_content;
	foreach($ELEARNING_INTERFACE_MODULES as $cms => $cms_preferences)
	{
		if (ELearningUtils::isCMSActive($cms))
		{
			if (($cms_preferences["auth_necessary"] == true))
			{
				if ($GLOBALS["module_type_" . $cms] != "")
					echo "<a name='anker'></a>";
//				ELearningUtils::loadClass($cms);
//				ELearningUtils::bench("load cms $cms");

				echo ELearningUtils::getCMSHeader($connected_cms[$cms]->getName());
				echo "<font size=\"-1\">";
				echo "<br>\n";
				echo "</font>";

				echo ELearningUtils::getHeader(sprintf(_("Mein Benutzeraccount")));
				if ($connected_cms[$cms]->user->isConnected())
				{
					$account_message = "<b>" . _("Loginname: ") . "</b>" . $connected_cms[$cms]->user->getUsername();
					$start_link = $connected_cms[$cms]->link->getStartpageLink(_("Startseite"));
					if ($start_link != false)
						$account_message .=  "<br><br>" . sprintf(_("Hier gelangen Sie in das angebundene System: %s"), $start_link);
				}
				else
					$account_message = sprintf(_("Sie haben im System %s bisher keinen Benutzer-Account."), $connected_cms[$cms]->getName());

				if ($new_account_cms != $cms)
				{
					echo ELearningUtils::getMyAccountForm("<font size=\"-1\">" . $account_message . "</font>", $cms);

					echo "<br>\n";

					if ($connected_cms[$cms]->user->isConnected())
					{
						echo ELearningUtils::getHeader(sprintf(_("Meine Lernmodule")));

						$connected_cms[$cms]->soap_client->setCachingStatus(false);
						$user_content_modules = $connected_cms[$cms]->getUserContentModules();
						$connected_cms[$cms]->soap_client->setCachingStatus(true);

						if (! ($user_content_modules == false))
						{
							foreach ($user_content_modules as $key => $connection)
							{
								$connected_cms[$cms]->setContentModule($connection, false);
								$connected_cms[$cms]->content_module[$current_module]->view->show();
							}
						}
						else
							echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"6\"><tr><td><font size=\"-1\">" . sprintf(_("Sie haben im System %s keine eigenen Lernmodule."), $connected_cms[$cms]->getName()) . "<br>\n<br>\n</font></td></tr></table>";

						echo "<br>\n";
						echo $new_module_form[$cms];

					}
				}
				else
				{
					echo $new_account_form;
					echo "<br>\n";
				}

//				echo "<br>\n";
				echo ELearningUtils::getCMSFooter($connected_cms[$cms]->getLogo());
				echo "<br>\n";
				ELearningUtils::bench("fetch data from $cms");
			}
		}
	 }

// Cachen der SOAP-Daten
	if (is_array($connected_cms))
		foreach($connected_cms as $system)
			$system->terminate();

//	ELearningUtils::bench("fetch data");
	if ($debug != "")
		ELearningUtils::showbench();

	// Anzeige, wenn noch keine Account-Zuordnung besteht
		$infobox = array	(
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(
							array (	"icon" => "ausruf_small.gif",
									"text"  => _("Auf dieser Seite sehen Sie Ihre Benutzer-Accounts und Lernmodule in angebundenen Systemen.")
								 )
							)
			)
		);
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "forumgrau.gif" ,
										"text"  => _("Sie k&ouml;nnen f&uuml;r jedes externe System einen eigenen Benutzer-Account erstellen oder zuordnen.")
									);

			$infobox[1]["eintrag"][] = array (	"icon" => "icon-lern.gif" ,
										"text"  => sprintf(_("Wenn Sie &uuml;ber die entsprechenden Rechte verf&uuml;gen, k&ouml;nnen Sie eigene Lernmodule erstellen."))
									);

		$cssSw = new cssClassSwitcher;									// Klasse f�r Zebra-Design


		?>
		<br>
		</td>
		<td width="270" NOWRAP class="blank" align="center" valign="top">
		<?
			print_infobox ($infobox,"lernmodule.jpg");
		?>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="3">&nbsp;
		</td>
	</tr>
	</table>
	<?

// terminate objects
	if (is_array($connected_cms))
		foreach($connected_cms as $system)
			$system->terminate();

}
else
{
	// Start of Output
	parse_window ("error�" . _("Die Schnittstelle f�r die Integration von Lernmodulen ist nicht aktiviert. Damit Lernmodule verwendet werden k�nnen, muss die Verbindung zu einem LCM-System in der Konfigurationsdatei von Stud.IP hergestellt werden. Wenden Sie sich bitte an den/die AdministratorIn."), "�",
				_("E-Learning-Schnittstelle nicht eingebunden"));
}

include ('include/html_end.inc.php');
page_close();
?>