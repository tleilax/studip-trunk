<?php
/**
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$ 
 * $Id$
 * @package pluginengine
 */
define("PLUGIN_ADMINISTRATION_POIID","admin");

class AdministrationPluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {
    
    function AdministrationPluginIntegratorEnginePersistence(){
    }
    
    /**
     * Liefert alle in der Datenbank bekannten Plugins zur�ck
     */
    function getAllInstalledPlugins(){
    	
    	$plugins = array();
    	// nur Administrations-Plugins liefern
    	$plugins = parent::executePluginQuery("where plugintype='Administration' order by navigationpos, pluginname");
    	

    	foreach ($plugins as $plugin){
    		//$result = &$this->connection->execute("Select * from plugins_administration_activated where pluginid=?",array($plugin->getPluginid()));
    		$result = &$this->connection->execute("Select * from plugins_activated where pluginid=? and poiid=?",array($plugin->getPluginid(),PLUGIN_ADMINISTRATION_POIID));
    		if ($result){
    			// 
    			if (!$result->EOF){
    				$plugin->setActivated(true);
    			}
    			else {
    				$plugin->setActivated(false);
    			}
    		} 
    		else {
    			$plugin->setActivated(false);
    		}
    		$result->Close();
    		$extplugins[] = $plugin;
    	}
    	return $extplugins; 

    }
    
    /**
     * Liefert alle in der Datenbank bekannten und aktivierten Plugins zur�ck
     */
    function getAllActivatedPlugins(){
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	//$result = &$this->connection->execute("SELECT p.* FROM plugins_administration_activated a left join plugins p on p.pluginid=a.pluginid where p.plugintype='Administration' order by p.navigationpos");
    	$plugins = array();
    	$result = &$this->connection->execute("SELECT p.* FROM plugins_activated a left join plugins p on p.pluginid=a.pluginid where a.poiid=? and p.plugintype='Administration' order by p.navigationpos",array(PLUGIN_ADMINISTRATION_POIID));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		// echo ("keine aktivierten Plugins<br>");
    		return array();
    	}
    	else {
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname,$pluginpath);
            	if ($plugin != null){
            		$plugin->setPluginid($result->fields("pluginid"));
	        		$plugin->setPluginname($result->fields("pluginname"));
	        		$plugin->setActivated(true);
	        		$plugin->setUser($this->getUser());
	        		$plugins[] = $plugin; 
            	}
            	$result->MoveNext();
        	}    
        	$result->Close();
        	return $plugins; 
    	}
    }
    
    /**
     * Liefert alle in der Datenbank bekannten und aktivierten Plugins zur�ck
     */
    function getAllDeActivatedPlugins(){
    	$plugins = array();
    	    	
    	//$result = &$this->connection->execute("SELECT p.* FROM plugins p left join plugins_administration_activated a on p.pluginid=a.pluginid where p.plugintype='Administration' and (a.pluginid is null)");
    	$result = &$this->connection->execute("SELECT p.* FROM plugins p left join plugins_activated a on p.pluginid=a.pluginid where a.poiid=? p.plugintype='Administration' and (a.pluginid is null)",array(PLUGIN_ADMINISTRATION_POIID));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		return array();
    	}
    	else {
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname,$pluginpath);
            	if ($plugin != null){
	            	$plugin->setPluginid($result->fields("pluginid"));
	            	$plugin->setPluginname($result->fields("pluginname"));
	            	$plugin->setActivated(false);
	            	$plugin->setUser($this->getUser());
	            	$plugins[] = $plugin; 
            	}
            	$result->MoveNext();
        	}    
        	$result->Close();
        	return $plugins; 
    	}
    }
    
    /**
     * Speichere ein Plugin
     */
    function savePlugin($plugin){
    	Parent::savePlugin($plugin);
    	if (is_object($plugin) && is_subclass_of($plugin,'AbstractStudIPAdministrationPlugin')){
    		// Plugin speichern
    		if ($plugin->isActivated()){
    			//$this->connection->execute("replace into plugins_administration_activated (pluginid) values(?)", array($plugin->getPluginId()));
    			$this->connection->execute("replace into plugins_activated (pluginid,poiid) values(?,?)", array($plugin->getPluginId(),PLUGIN_ADMINISTRATION_POIID));
    		} 
    		else {
    			// Plugin aus der aktiven Tabelle l�schen 
    			//$this->connection->execute("delete from plugins_administration_activated where pluginid=?", array($plugin->getPluginId()));
    			$this->connection->execute("delete from plugins_activated where pluginid=?", array($plugin->getPluginId()));
    		}
    	}
    	else {
    		// TODO: richtige Fehlerbehandlung
    		echo ("ERROR: kein g�ltiger Parameter<br>");
    		echo ("<pre>");
    		print_r($plugin);
    		echo ("</pre>");
    	}
    }
    
    function getPlugin($id){
    	//$result = &$this->connection->execute("Select p.* from plugins p left join plugins_administration_activated a on p.pluginid=a.pluginid where p.pluginid=? and p.plugintype='Administration' and (a.pluginid is null)",array($id));
    	$result = &$this->connection->execute("Select p.* from plugins p left join plugins_activated a on p.pluginid=a.pluginid where a.poiid=? and p.pluginid=? and p.plugintype='Administration' and (a.pluginid is null)",array(PLUGIN_ADMINISTRATION_POIID,$id));
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