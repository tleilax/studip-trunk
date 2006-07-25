<?php
/**
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$ 
 * $Id$
 * @package pluginengine
 */

class PortalPluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {
    
    function PortalPluginIntegratorEnginePersistence(){
    	parent::AbstractPluginIntegratorEnginePersistence();
    }
    
    /**
     * Liefert alle in der Datenbank bekannten Plugins zur�ck
     */
    function getAllInstalledPlugins(){
    	// nur Standard-Plugins liefern
    	$plugins = parent::executePluginQuery("where plugintype='Portal'");
    	return $plugins;
    }
    
    /**
     * Returns all activated system plugins
     * @return all activated system plugins
     */
    function getAllActivatedPlugins(){
    	// return all activated system plugins
    	$plugins = parent::executePluginQuery("where plugintype='Portal' and enabled='yes'");
    	return $plugins;
    }
    
    /**
     * Returns all deactivated system plugins
     * @return all deactivated plugins
     */
    function getAllDeActivatedPlugins(){
    	// return all deactivated system plugins
    	$plugins = parent::executePluginQuery("where plugintype='Portal' and enabled='no'");
    	return $plugins;
    }
   
    
    function getPlugin($id){
    	$result = &$this->connection->execute("Select p.* from plugins p where p.pluginid=? and p.plugintype='Portal'",array($id));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		return null;
    	}
    	else {
    		if (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname,$pluginpath);
            	if ($plugin != null){
	            	$plugin->setPluginid($result->fields("pluginid"));
	            	$plugin->setPluginname($result->fields("pluginname"));
	            	$plugin->setUser($this->getUser());	
            	}
        	}    
        	$result->Close();
        	return $plugin; 
    	}
    }
}
?>
