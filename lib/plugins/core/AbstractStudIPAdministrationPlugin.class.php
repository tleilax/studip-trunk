<?php

/**
 * Ausgangspunkt f�r Administrationsplugins, also Plugins, die speziell im
 * Adminstrator- / Root-Bereich angezeigt werden.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPAdministrationPlugin extends AbstractStudIPPlugin{

	var $topnavigation;

	function AbstractStudIPAdministrationPlugin(){
		// Konstruktor der Basisklasse aufrufen
    	AbstractStudIPPlugin::AbstractStudIPPlugin();
    	$this->topnavigation = null;
    	$this->pluginengine = PluginEngine::getPluginPersistence("Administration");
	}

    /**
     * Verf�gt dieses Plugin �ber einen Eintrag auf der Startseite des
     * Administrators
     * @return  true 	- Hauptmen� vorhanden
     * 			false	- kein Hauptmen� vorhanden
     */
    function hasTopNavigation(){
    	if ($this->topnavigation != null){
    		return true;
    	}
    	else {
    		return false;
    	}
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
    function setTopnavigation($newnavigation){
    	if (is_a($newnavigation,'PluginNavigation') || is_subclass_of($newnavigation,'PluginNavigation')){
	    	// $newnavigation->setPluginpath($this->getPluginpath());
    		$this->topnavigation = $newnavigation;
    	}
    }
}
?>
