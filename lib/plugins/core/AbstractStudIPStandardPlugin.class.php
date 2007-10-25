<?php

require_once("lib/classes/TreeAbstract.class.php");

/**
 * Starting point for creating "normal" course or institute plugins.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPStandardPlugin extends AbstractStudIPLegacyPlugin{

	var $changeindicatoriconname; 	// relativer Name des Icons f�r �nderungen an diesem Plugin
	/**
	* @todo umbenennen in poiid
	*/
	var $id; 						// Id, der dieses Plugin zugeordnet ist (bspw. Veranstaltung oder Institution)
	var $overview; 					// wird dieses Plugin in der �bersicht (z.B. meine_seminare) angezeigt

	/**
	 */
    function AbstractStudIPStandardPlugin() {
    	// Konstruktor der Basisklasse aufrufen
    	parent::AbstractStudIPLegacyPlugin();
    	$this->pluginiconname = "";
    	$this->changeindicatoriconname = "";
    	$this->id = UNKNOWN_ID;
    	$this->overview = false;
    	$this->pluginengine = PluginEngine::getPluginPersistence("Standard");
    	// create the standard AdminInfo
    	$admininfo = new AdminInfo();
    	$this->setPluginAdminInfo($admininfo);
    }

    function setId($newid){
	    $this->id=$newid;
    }

    function getId(){
    	if ($this->id == UNKNOWN_ID){
    		$this->id = $GLOBALS['SessSemName'][1];
    	}
    	else {
    		$this->id = trim(str_replace($GLOBALS["SessSemName"]["class"],"",$this->id));
    	}
	    return $this->id;
    }

    /**
     * Hat sich seit dem letzten Login etwas ge�ndert?
     * @param lastlogin - letzter Loginzeitpunkt des Benutzers
     */
    function hasChanged($lastlogin){
    	return false;
    }
    /**
     * Nachricht f�r tooltip in der �bersicht
     * @param lastlogin - letzter Loginzeitpunkt des Benutzers
     */

	function getOverviewMessage($has_changed = false){
		return $this->getPluginname() . ($has_changed ? ' ' . _("ge�ndert") : '');
	}

    /**
     * Wird dieses Plugin in der �bersicht angezeigt?
     */
    function isShownInOverview(){
    	return $this->overview;
    }

    /**
     * Liefert die �nderungsmeldungen f�r die �bergebenen ids zur�ck
     * @param lastlogin - letzter Loginzeitpunkt des Benutzers
     * @param ids - ein Array von Veranstaltungs- bzw. Institutionsids, zu denen
     * die �nderungsnachricht bestimmt werden soll.
     * @return �nderungsmeldungen
     */
    function getChangeMessages($lastlogin, $ids){
    	return array();
    }

    /**
     * Getter- und Setter f�r die Attribute
     */


    function getChangeindicatoriconname(){
    	return $this->getPluginpath() . "/" . $this->changeindicatoriconname;
    }

    function setChangeindicatoriconname($newicon){
    	// TODO: Icon testen
    	$this->changeindicatoriconname = $newicon;
    }

    function setShownInOverview($value=true){
    	$this->overview = $value;
    }


    /**
    * Shows the standard configuration.
    */
    function actionShowConfigurationPage(){
    	$user = $this->getUser();
    	$permission = $user->getPermission();

    	if (!$permission->hasAdminPermission()){
    		StudIPTemplateEngine::showErrorMessage(_("Sie besitzen keine Berechtigung, um dieses Plugin zu konfigurieren."));
		}
		else {
			StudIPTemplateEngine::makeContentHeadline(_("Default-Aktivierung"));
			$sel_institutes = $_POST["sel_institutes"];
			if ($_GET["selected"]){
				if ($_POST["nodefault"] == true){
					if ($this->pluginengine->removeDefaultActivations($this)){
						StudIPTemplateEngine::showSuccessMessage(_("Die Voreinstellungen wurden erfolgreich gel�scht."));
						$sel_institutes = array();
					}
					else {
						StudIPTemplateEngine::showErrorMessage(_("Die Voreinstellungen konnten nicht gel�scht werden"));
					}
				}
				else {
					// save selected institutes
					if ($this->pluginengine->saveDefaultActivations($this,$sel_institutes)){
						// show info
						if (count($sel_institutes) > 1){
							StudIPTemplateEngine::showSuccessMessage(_("F�r die ausgew�hlten Institute wurde das Plugin standardm��ig aktiviert!"));
						}
						else {
							StudIPTemplateEngine::showSuccessMessage(_("F�r das ausgew�hlte Institut wurde das Plugin standardm��ig aktiviert!"));
						}
					}
					else {
						StudIPTemplateEngine::showErrorMessage(_("Das Abspeichern der Default-Einstellungen ist fehlgeschlagen"));
					}
				}
			}
			else {
				// load old config
				$sel_institutes = $this->pluginengine->getDefaultActivations($this);
			}

			?>
			<tr>
				<td>
					<?
					echo _("W�hlen Sie die Einrichtungen, in deren Veranstaltungen das Plugin automatisch aktiviert sein soll.<p>");
					$institutes = StudIPCore::getInstitutes();
					?>
					<form action="<?= PluginEngine::getLink($this,array("selected" => true),"showConfigurationPage") ?>" method="POST">
					<select name="sel_institutes[]" multiple size="20">
					<?

					foreach ($institutes as $institute) {
						// if id is in selected institutes, the mark it as selected

						if (array_search($institute->getId(),$sel_institutes) !== false){
							$selected = "selected";
						}
						else {
							$selected = "";
						}
						echo(sprintf("<option value=\"%s\" %s> %s </option>",$institute->getId(),$selected, $institute->getName()));
						$childs = $institute->getAllChildInstitutes();
						foreach ($childs as $child) {
							if (array_search($child->getId(),$sel_institutes) !== false){
								$selected = "selected";
							}
							else {
								$selected = "";
							}
							echo(sprintf("<option value=\"%s\" %s>&nbsp;&nbsp;&nbsp;&nbsp; %s </option>",$child->getId(),$selected, $child->getName()));
						}
					}

					?>
					</select><br>
					<input type="checkbox" name="nodefault"><?= _("keine Voreinstellung w�hlen") ?>
					<p>

					<?= makeButton("uebernehmen","input",_("Einstellungen speichern")) ?>
					<a href="<?= PluginEngine::getLinkToAdministrationPlugin() ?>"><?= makeButton("zurueck","img",_("Zur�ck zur Plugin-Verwaltung")) ?></a>
					</form>
				</td>
			</tr>

			<?php

			StudIPTemplateEngine::createInfoBoxTableCell();
			$infobox = array	(
						array  ("kategorie"  => _("Hinweise:"),
								"eintrag" => array	(
									array (	"icon" => "ausruf_small.gif",
													"text"  => _("W�hlen Sie die Institute, in deren Veranstaltungen das Plugin standardm��ig eingeschaltet werden soll.")
									),
									array (	"icon" => "ausruf_small.gif",
													"text"  => _("Eine Mehrfachauswahl ist durch Dr�cken der Strg-Taste m�glich.")
									)
								)
						)
				);
			print_infobox ($infobox);
			StudIPTemplateEngine::endInfoBoxTableCell();
		}
    }

    /**
     * returns the score which the current user get's for activities in this plugin
     *
     */
    function getScore(){
    	return 0;
    }


  /**
   * This abstract method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display($action) {

    include 'lib/include/html_head.inc.php';
    include 'lib/include/header.php';

    $pluginparams = $_GET["plugin_subnavi_params"];


    // diplay the admin_menu
    if ($action == "actionshowConfigurationPage"
        && $GLOBALS['perm']->have_perm("admin")){
      include 'lib/include/links_admin.inc.php';
    }

    // display the course menu
    include 'lib/include/links_openobject.inc.php';

    // let the plugin show its view
    $pluginnav = $this->getNavigation();

    if (is_object($pluginnav)){
      $iconname = "";
      if ($pluginnav->hasIcon()){
        $iconname = $this->getPluginpath() . "/" . $pluginnav->getIcon();
      }
      else {
        $iconname = $this->getPluginiconname();
      }

      if (isset($GLOBALS['SessSemName']["header_line"])){
        StudIPTemplateEngine::makeHeadline(
          sprintf("%s - %s", $GLOBALS['SessSemName']["header_line"],
                  $this->getDisplaytitle()),
          true,
          $iconname);
      }
      else {
        StudIPTemplateEngine::makeHeadline($this->getDisplaytitle(), true,
                                           $iconname);
      }
    }
    else {
      StudIPTemplateEngine::makeHeadline($this->getPluginname(), true,
                                         $this->getPluginiconname());
    }

    StudIPTemplateEngine::startContentTable(true);
    $this->$action($pluginparams);
    StudIPTemplateEngine::endContentTable();

    // close the page
    include 'lib/include/html_end.inc.php';
    page_close();
  }
}