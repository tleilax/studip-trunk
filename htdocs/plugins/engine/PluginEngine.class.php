<?php
/**
* Factory Class for the plugin engine
* @author Dennis Reil, <dennis.reil@offis.de>
* @version $Revision$
* @package pluginengine
* $Id$
*/
define("UNKNOWN_PLUGINTYPE","undefined");

class PluginEngine{
	
	/**
	* Returns the plugin persistence object for the required plugin type.
	* @param $plugintype - Standard, Administration, System
	* @return a persistence object 
	*/
	function &getPluginPersistence($plugintype="Abstract"){
		$classname = $plugintype . "PluginIntegratorEnginePersistence";
		$persistence =& new $classname();
		$conn =& PluginEngine::getPluginDatabaseConnection();
		
		$persistence->setConnection($conn);
		$persistence->setEnvironment($GLOBALS["plugindbenv"]);
		
		// now set the user
		$persistence->setUser(new StudIPUser());
		return $persistence;
	}
	
	/**
	* @param the plugin for which a persistence object should be instantiated
	*/
	function &getPluginPersistenceByPlugin($plugin){
		return PluginEngine::getPluginPersistence(PluginEngine::getTypeOfPlugin($plugin));
	}
	
	/**
	* Returns an active connection to the plugin database
	* @return active connection to the database
	* @todo Caching of database connections ?
	*/
	function &getPluginDatabaseConnection(){
		$env = $GLOBALS["plugindbenv"]; // get the environment
		$connection =& NewADOConnection($env->dbtype);
	
		// connect to the database
		// TODO: persistent connection ok ?
  		$connection->PConnect($env->dbhost,$env->dbuser,$env->dbpassword,$env->dbname); 
    
    	return $connection;
	}
	
	/**
	* Generates a Link which can be shown in user interfaces
	* @param $plugin - the plugin to which should be linked
	* @param $params - an array with name value pairs
	* @param $cmd - command to execute by clicking the link
	* @return a link to the current plugin with the additional $params
	*/
	function getLink($plugin, $params=array(), $cmd="show"){
		if (is_null($plugin)){
			return "";
		}
		$link = "plugins.php?cmd=$cmd&id=" . urlencode($plugin->getPluginid());
		if (PluginEngine::getTypeOfPlugin($plugin) == "Homepage"){
			$requser = $plugin->getRequestedUser();
			if (is_object($requser)){
				$params["requesteduser"] = $requser->getUsername();
			}
		}
		// add Params
		foreach ($params as $paramkey=>$paramval){
			$link .= "&" . urlencode($paramkey) . "=" . urlencode($paramval);
		}
		return $link;
	}
	
	/**
	* Generates a Link to the plugin administration which can be shown in user interfaces
	* @param $params - an array with name value pairs
	* @return a link to the administration plugin with the additional $params
	*/
	function getLinkToAdministrationPlugin($params=array()){
		$link = "plugins.php?cmd=show&id=1"; 
		// add Params
		foreach ($params as $paramkey=>$paramval){
			$link .= "&" . urlencode($paramkey) . "=" . urlencode($paramval);
		}
		return $link;
	}
	
	/**
	* Returns the plugin type
	* @return returns the type of the plugin if known by the engine
			  otherwise returns undefined
	*/
	function getTypeOfPlugin($plugin){
	  if (is_a($plugin,'AbstractStudIPStandardPlugin') || is_subclass_of($plugin,'AbstractStudIPStandardPlugin')){
			return "Standard";
		} else if (is_a($plugin,'AbstractStudIPAdministrationPlugin') || is_subclass_of($plugin,'AbstractStudIPAdministrationPlugin')) {
			return "Administration";
		} else if (is_a($plugin,'AbstractStudIPSystemPlugin') || is_subclass_of($plugin,'AbstractStudIPSystemPlugin')) {
			return "System";
		} else if (is_a($plugin, 'AbstractStudIPHomepagePlugin') || is_subclass_of($plugin, 'AbstractStudIPHomepagePlugin')){
			return "Homepage";
		} else if (is_a($plugin, 'AbstractStudIPPortalPlugin') || is_subclass_of($plugin, 'AbstractStudIPPortalPlugin')){
			return "Portal";
		} else if (is_a($plugin, 'AbstractStudIPCorePlugin') || is_subclass_of($plugin, 'AbstractStudIPCorePlugin')){
			return "Core";
		} 		
		return UNKNOWN_PLUGINTYPE;
  }
  
  
   /**
    * Creates an instance of the desired plugin class
    * @param pluginclassname - the desired class name
    * @param pluginpath - the path to the plugin
    * @return an instance of the desired plugin or null otherwise
    */
   function &instantiatePlugin($pluginclassname, $pluginpath){
   		$env = $GLOBALS["plugindbenv"];
	    $absolutepluginfile = $env->getPackagebasepath() . "/" . $pluginpath . "/" . $pluginclassname . ".class.php";
	    if (!file_exists($absolutepluginfile)){
		    return null;
	    }
	    else {
			//anoack: unsch�ner workaround, aber auf die Schnelle kaum anders zu l�sen, solange Plugins auch vorhandenen Stud.IP code nutzen wollen :)
			global $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_RESOURCES, $RELATIVE_PATH_CALENDAR,$RELATIVE_PATH_LEARNINGMODULES,$RELATIVE_PATH_CHAT;
			require_once($absolutepluginfile);
		    $plugin =& new $pluginclassname();
		    $plugin->setEnvironment($env);			    	    	    
		    $plugin->setPluginpath($env->getRelativepackagepath() . "/" . $pluginpath);
		    $plugin->setBasepluginpath($pluginpath);
		    return $plugin;
	    }
   }
   
   /**
	* Reads the manifest of the plugin in the given path
	* @return array containing the manifest information
	* @todo Klasse f�r die R�ckgabe realisieren
	*/
	function getPluginManifest($pluginpath){		
	   $pluginpath = trim($pluginpath);	   
	   if (!(strrpos($pluginpath,"/") == strlen($pluginpath)-1)) $pluginpath .= "/";
	   if (!file_exists($pluginpath . "plugin.manifest")){	
	   	  return array();
	   }	 
	   $manifest = fopen($pluginpath . "plugin.manifest","r");
	   $plugininfos = array();		
		while (!feof($manifest)){
			// Suche nach STRING1=STRING2
			$result = fscanf($manifest,"%[^=]=%[^\n]");
			if ($result){
				if ($result[0] == "pluginclassname"){
					if ($plugininfos["class"] != "") {
						$plugininfos["additionalclasses"][] = trim($result[1]);
					}
					else {
						$plugininfos["class"] = trim($result[1]);
					}
				}
				else if ($result[0] == "origin"){
					$plugininfos["origin"] = trim($result[1]);
				}
				else if ($result[0] == "version"){
					$plugininfos["version"] = trim($result[1]);
				}
				else if ($result[0] == "pluginname"){
					$plugininfos["pluginname"] = trim($result[1]);
				}
				else if ($result[0] == "dbscheme"){
					$plugininfos["dbscheme"] = trim($result[1]);
				}
				else if ($result[0] == "uninstalldbscheme") {
					$plugininfos["uninstalldbscheme"] = trim($result[1]);
				}
			}
		}
		fclose($manifest);
		return $plugininfos;
	}
	
	/**
	 * Searches for plugins in the plugins installation directory, if enabled in local.inc
	 * @return list of installable names of plugin packages
	 *
	 */
	function getInstallablePlugins(){
		$newpluginsdir = $GLOBALS["NEW_PLUGINS_PATH"];

		if (!isset($newpluginsdir)){
			// there's no dir defined in the local.inc
			return array();
		}
		else {
			if (!file_exists($newpluginsdir)){
				// the directory doesn't exist
				return array();
			}
			$dir = dir($newpluginsdir);
			$installableplugins = array();
			while ($file = readdir($dir->handle)){
				if (preg_match("/(.*)\.zip/",$file) > 0){
					$installableplugins[] = $file;
				}
			}
			return $installableplugins;
		}
	}	
	
	/**
	 * Saves a value to the global session
	 *
	 * @param AbstractStudIPPlugin $plugin - the plugin for which the value should be saved
	 * @param string $key - a key for the value. has to be unique for the calling plugin
	 * @param string $value - the value, which should be saved into the session
	 */
	function saveToSession($plugin,$key,$value){
		$_SESSION["PLUGIN_SESSION_SPACE"][get_class($plugin)][$key] =serialize($value);
	}
	
	
	/**
	 * Retrieves the value to key from the global plugin session
	 *
	 */
	function getValueFromSession($plugin,$key){
		return unserialize($_SESSION["PLUGIN_SESSION_SPACE"][get_class($plugin)][$key]);
	}
	
	/**
	 * for internal use only
	 *
	 * @param unknown_type $key
	 * @return unknown
	 */
	function getEngineValueFromSession($key){
		return unserialize($_SESSION["PLUGIN_SESSION_SPACE"]["PLUGINENGINE"][$key]);		
	}
	
	/**
	 * for internal use only
	 *
	 * @param unknown_type $key
	 * @param unknown_type $value
	 */
	function saveEngineValueToSession($key,$value){
		$_SESSION["PLUGIN_SESSION_SPACE"]["PLUGINENGINE"][$key] = serialize($value);
	}
}
?>