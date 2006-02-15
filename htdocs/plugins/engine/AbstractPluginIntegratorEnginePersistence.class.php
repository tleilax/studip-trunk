<?php
/**
 * Base class for all PluginEnginePersistence-Objects
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$ 
 * $Id$
 * @package pluginengine
 */
define("UNKNOWN_PLUGIN_ID",-1);

class AbstractPluginIntegratorEnginePersistence {
	var $connection;
	var $environment;
	var $user;

	function AbstractPluginIntegratorEnginePersistence(){
		$this->connection = null;
		$this->user = null;
	}
	
	function setEnvironment($dbenvironment){
		if (is_a($dbenvironment,"DBEnvironment") || is_subclass_of($dbenvironment,"DBEnvironment")){
			$this->environment = $dbenvironment;
		}
	}
	
	function getEnvironment(){
		return $this->environment;
	}
	
	function setUser($newuser){
		if (is_a($newuser,"StudIPUser") || is_subclass_of($newuser,"StudIPUser")){
			$this->user = $newuser;
		}
		else {
			// Fehler, ung�ltiger Parameter
			$this->user = null;
		}
	}
	
	function getUser(){
		return $this->user;
	}
	
	function setConnection($newconnection){
		$this->connection = $newconnection;
	}
	
	function &getConnection(){
		return $this->connection;
	}
    
    /**
		Registers a new plugin in the database
		@return the pluginid
	*/ 
	function registerPlugin($plugin, $pluginclassname, $pluginpath){
		$type = PluginEngine::getTypeOfPlugin($plugin);		
		if (strlen($type) > 0){			
			// try to find an existing entry to update
			$result =& $this->connection->execute("select pluginid from plugins where pluginclassname=? and plugintype=?",array($pluginclassname,$type));
			if ($result){
				$pluginid = $result->fields("pluginid");
				// try to update this entry
				$result =& $this->connection->execute("update plugins set pluginpath=? where plugintype=? and pluginid=?", array($pluginpath,$type,$pluginid));									
			}
			else {
				$result =& $this->connection->execute("insert into plugins (pluginid,pluginclassname,pluginname,pluginpath,plugintype,enabled,navigationpos) select 0,?,?,?,?,'no',max(navigationpos)+1 from plugins where plugintype=?", array($pluginclassname,$plugin->getPluginname(), $pluginpath,$type,$type));					
			}
			$this->connection->CacheFlush();
		}
	}
    
    /**
    	updates plugin base data like position in the navigation
    */
    function savePlugin($plugin){
    	// keine Funktion
    	$enabled = "";
    	if ($plugin->isEnabled()){
	    	$enabled = "yes";
    	}
    	else{
	    	$enabled = "no";
    	}
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	$this->connection->execute("Update plugins set pluginname=?, enabled=?, navigationpos=? where pluginid=?", array($plugin->getPluginname(),$enabled,$plugin->getNavigationPosition(),$plugin->getPluginid()));
    	$this->connection->CacheFlush();
    	return;
    }
    
    /**
    *
    */
    function &executePluginQuery($filter,$params=array()){
    	// cache results for 60*60 seconds
    	$plugins = array();
    	$result = &$this->connection->CacheExecute(3600,"Select * from plugins " . $filter,$params);
    	// $result = &$this->connection->Execute("Select * from plugins " . $filter,$params);
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		return array();
    	}
    	else {
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
    			
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
            	
				if ($plugin != null){	
	            	$plugin->setPluginid($result->fields("pluginid"));
	        		$plugin->setPluginname($result->fields("pluginname"));
	        		$plugin->setNavigationPosition($result->fields("navigationpos"));
	        		if ($result->fields("enabled") == 'yes'){
	        			$plugin->setEnabled(true);
	        		}
	        		else {
	        			$plugin->setEnabled(false);
	        		}
	        		$plugins[] = $plugin; 
				}            	
            	$result->MoveNext();
        	}    
        	$result->Close();
        	return $plugins; 
    	}
    }
    
    /**
     * Liefert alle in der Datenbank bekannten Plugins zur�ck
     */
    function getAllInstalledPlugins(){
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	return $this->executePluginQuery("order by plugintype, navigationpos, pluginname, enabled");
    }
    
    function getPlugins($enabled=false){
    
    	if ($enabled){
    	   $filter = 'yes';
    	}
    	else {
    	   $filter = 'no';
    	}
    	return $this->executePluginQuery("where enabled=? order by navigationpos, pluginname, plugintype", array($filter));
    }
    
    /**
     * Liefert alle in der Datenbank bekannten und aktivierten Plugins zur�ck
     */
    function getAllEnabledPlugins(){
        return $this->getPlugins(true);
    }
    
    /**
     * Liefert alle in der Datenbank bekannten und aktivierten Plugins zur�ck
     */
    function getAllDisabledPlugins(){
    	return $this->getPlugins(false);
    }
    
    /*
    */
    function getAllActivatedPlugins(){
	    // TODO: Implementierung
	    return null;
    }
    
    /*
    */
    function getAllDeactivatedPlugins(){
	    // TODO: Implementierung
	    return null;
    }
    
       
    function getPlugin($id){
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	$plugins = $this->executePluginQuery("where pluginid=?",array($id));
    	if (count($plugins) == 1){
    		return $plugins[0];
    	}
    	else {
    		return null;
    	}
    }
    
    function deinstallPlugin($plugin){
		$this->connection->execute("Delete from plugins where pluginid=?", array($plugin->getPluginid()));
		$this->connection->execute("Delete from plugins_activated where pluginid=?", array($plugin->getPluginid()));
	}
	
	/**
	* Searches for $pluginclassname in the plugins database
	* @return true - plugin called $pluginclassname was found in the database
	*  		  false - plugin not found
	*/
	function isPluginRegistered($pluginclassname){
		$result = &$this->connection->execute("select * from plugins where pluginclassname=?", array($pluginclassname));
		if (!$result){
		   return false;
		}
		else {
			 $result->Close();
			 return true;
		}
	}
	
	/**
	* Searches for $pluginname in the plugins database
	* @return the id of the plugin
	*/
	function getPluginId($pluginclassname){
		$result = &$this->connection->execute("select * from plugins where pluginclassname=?", array($pluginclassname));
		if (!$result){
		   return UNKNOWN_PLUGIN_ID;
		}
		else {
			if (!$result->EOF){
				return $result->fields("pluginid");
			}
			$result->Close();
			return true;
		}
	}
}
?>
