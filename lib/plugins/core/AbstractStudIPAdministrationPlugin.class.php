<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/**
 * Ausgangspunkt f�r Administrationsplugins, also Plugins, die speziell im
 * Adminstrator- / Root-Bereich angezeigt werden.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPAdministrationPlugin extends AbstractStudIPLegacyPlugin
	implements AdministrationPlugin {

	var $topnavigation;

	function AbstractStudIPAdministrationPlugin(){
		// Konstruktor der Basisklasse aufrufen
		parent::AbstractStudIPLegacyPlugin();
	}

	function setNavigation(StudipPluginNavigation $navigation) {
		parent::setNavigation($navigation);

		if (Navigation::hasItem('/admin/plugins')) {
			Navigation::addItem('/admin/plugins/' . $this->getPluginclassname(), $navigation);
		}
	}

    /**
     * Verf�gt dieses Plugin �ber einen Eintrag auf der Startseite des
     * Administrators
     * @return  true 	- Hauptmen� vorhanden
     * 			false	- kein Hauptmen� vorhanden
     */
    function hasTopNavigation(){
    	return $this->topnavigation != null;
    }

    /**
     * Liefert den Men�eintrag zur�ck
     * @return das Men�, oder null, wenn kein Men� vorhanden ist
     */
    function getTopNavigation(){
    	return $this->topnavigation;
    }

    /**
     * Setzt das Hauptmen� des Plugins
     */
    function setTopnavigation(StudipPluginNavigation $navigation){
    		$this->topnavigation = $navigation;

		if ($navigation instanceof PluginNavigation) {
			$this->topnavigation->setPlugin($this);
		}

		Navigation::addItem('/start/' . $this->getPluginclassname(), $navigation);
   	}


  /**
   * This method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display_action($action) {
    // Administration-Plugins only accessible by users with admin rights
    if (!$GLOBALS['perm']->have_perm('admin')) {
      throw new Exception(_('Sie verf�gen nicht �ber ausreichend Rechte f�r diese Aktion.'));
    }

    parent::display_action($action);
  }
}
?>
