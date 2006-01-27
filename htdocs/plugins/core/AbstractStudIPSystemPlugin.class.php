<?php

/**
 * Ausgangspunkt f�r Systemplugins. Systemplugins k�nnen in das Hauptmen�
 * integriert werden oder aber ohne GUI im Hintergrund laufen.
 * @author Dennis Reil <dennis.reil@offis.de>
 */

class AbstractStudIPSystemPlugin extends AbstractStudIPPlugin{
	
	function AbstractStudIPSystemPlugin(){
		// Konstruktor der Basisklasse aufrufen
		AbstractStudIPPlugin::AbstractStudIPPlugin();
		$this->pluginengine = PluginEngine::getPluginPersistence("System");
	}
}
?>