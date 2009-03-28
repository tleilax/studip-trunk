<?php
# Lifter002: TODO
// vim: noexpandtab
/**
 * Factory Class for the plugin engine
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

require_once 'PluginManager.class.php';

class PluginEngine {


	/**
	 * Contains the current plugin's ID
	 *
	 * @var mixed
	 */
	private static $currentPluginId;


	/**
	 * TODO
	 *
	 * @return int  returns the current plugin's ID
	 */
	public static function getCurrentPluginId() {
		return PluginEngine::$currentPluginId;
	}


	/**
	 * TODO
	 *
	 * @param  int  the current plugin's ID
	 *
	 * @return int  returns the current plugin's ID
	 */
	public static function setCurrentPluginId($id) {
		return (PluginEngine::$currentPluginId = $id);
	}

	/**
	 * This function maps an incoming request to a tuple
	 * (pluginclassname, unconsumed rest).
	 *
	 * @return array the above mentioned tuple
	 */
	public static function routeRequest($dispatch_to) {
		$dispatch_to = ltrim($dispatch_to, '/');
		if (strlen($dispatch_to) === 0) {
			throw new Studip_PluginNotFoundException(
			  _("Es wurde kein Plugin gew�hlt."));
		}
		$pos = strpos($dispatch_to, '/');
		return $pos === FALSE
			? array($dispatch_to, '')
			: array(substr($dispatch_to, 0, $pos), substr($dispatch_to, $pos + 1));
	}

	/**
	 * Get instance of the plugin specified by plugin class name.
	 *
	 * @param $class   class name of plugin
	 */
	public static function getPlugin ($class) {
		return PluginManager::getInstance()->getPlugin($class);
	}

	/**
	 * Get instances of all plugins of the specified type. A type of NULL
	 * returns all enabled plugins. The optional context parameter can be
	 * used to get only plugins that are activated in the given context.
	 *
	 * @param $type      plugin type or NULL (all types)
	 * @param $context   context range id (optional)
	 */
	public static function getPlugins ($type, $context = NULL) {
		return PluginManager::getInstance()->getPlugins($type, $context);
	}

	/**
	 * Sends a message to all activated plugins and returns an array of the return
	 * values.
	 *
	 * @param  type       plugin type or NULL (all types)
	 * @param  context    context range id (optional)
	 * @param  string     the method name that should be send to all plugins
	 * @param  mixed      a variable number of arguments
	 *
	 * @return array      an array containing the return values
	 */
	function sendMessage($type, $context, $method /* ... */) {
		$args = func_get_args();
		$args = array_slice($args, 3);
		$results = array();
		foreach (self::getPlugins($type, $context) as $plugin) {
			$results[] = call_user_func_array(array($plugin, $method), $args);
		}
		return $results;
	}

	/**
	* Generates a URL which can be shown in user interfaces
	* @param $plugin - the plugin to which should be linked
	* @param $params - an array with name value pairs
	* @param $cmd - command to execute by clicking the link
	* @return a link to the current plugin with the additional $params
	*/
	public static function getURL($plugin, $params=array(), $cmd="") {
		if (is_null($plugin)) {
			throw new InvalidArgumentException(_("Es wurde kein Plugin gew�hlt."));
		}
		$link = sprintf("plugins.php/%s/%s", urlencode($plugin->getPluginclassname()), $cmd);

		return URLHelper::getURL($link, $params);
	}

	/**
	* Generates a Link (entity encoded URL) which can be shown in user interfaces
	* @param $plugin - the plugin to which should be linked
	* @param $params - an array with name value pairs
	* @param $cmd - command to execute by clicking the link
	* @return a link to the current plugin with the additional $params
	*/
	public static function getLink($plugin, $params=array(), $cmd="") {
		return htmlspecialchars(PluginEngine::getURL($plugin, $params, $cmd));
	}

	/**
	 * Generates a Link to the plugin administration which can be shown in user interfaces
	 *
	 * @param   array   an optional array with name value pairs
	 * @param   string  an optional command defaulting to 'show'
	 *
	 * @return  string  a link to the administration plugin with the additional $params
	 */
	public static function getLinkToAdministrationPlugin($params = array(), $cmd = 'show') {
		$link = "plugins.php/pluginadministrationplugin/" . $cmd;

		return URLHelper::getLink($link, $params);
	}

	/**
	 * Creates an instance of the desired plugin class
	 * @param pluginclassname - the desired class name
	 * @param pluginpath - the path to the plugin
	 * @param args - arguments passed to the plugin
	 * @return an instance of the desired plugin or null otherwise
	 */
	public static function instantiatePlugin($pluginclassname, $pluginpath) {
		global $pluginenv;

		$basepath = $pluginenv->getPackagebasepath();
		$pluginfile = $basepath.'/'.$pluginpath.'/'.$pluginclassname.'.class.php';

		if (!file_exists($pluginfile)) {
			return NULL;
		}

		require_once $pluginfile;

		$plugin_class = new ReflectionClass($pluginclassname);
		$plugin = $plugin_class->newInstance();
		$plugin->setEnvironment($pluginenv);
		$plugin->setPluginpath($pluginenv->getRelativepackagepath().'/'.$pluginpath);
		$plugin->setBasepluginpath($pluginpath);

		return $plugin;
	}

	/**
	 * Reads the manifest of the plugin in the given path
	 * @return array containing the manifest information
	 * @todo Klasse f�r die R�ckgabe realisieren
	 */
	public static function getPluginManifest($pluginpath) {
		$manifest = file($pluginpath . '/plugin.manifest');
		$result = array();

		if ($manifest !== false) {
			foreach ($manifest as $line) {
				list($key, $value) = explode('=', $line);
				$key = trim($key);
				$value = trim($value);

				if ($key === '' || $key[0] === '#') {
					continue;
				}

				if ($key === 'pluginclassname' && isset($result[$key])) {
					$result['additionalclasses'][] = $value;
				} else {
					$result[$key] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * Searches for plugins in the plugins installation directory, if enabled in local.inc
	 * @return list of installable names of plugin packages
	 *
	 */
	public static function getInstallablePlugins() {
		$newpluginsdir = $GLOBALS["NEW_PLUGINS_PATH"];

		if (!isset($newpluginsdir)) {
			// there's no dir defined in the local.inc
			return array();
		}
		else {
			if (!file_exists($newpluginsdir)) {
				// the directory doesn't exist
				return array();
			}
			$dir = dir($newpluginsdir);
			$installableplugins = array();
			while ($file = readdir($dir->handle)) {
				if (preg_match("/(.*)\.zip/",$file) > 0) {
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
	public static function saveToSession($plugin,$key,$value) {
		$_SESSION["PLUGIN_SESSION_SPACE"][strtolower(get_class($plugin))][$key] =serialize($value);
	}


	/**
	 * Retrieves the value to key from the global plugin session
	 *
	 */
	public static function getValueFromSession($plugin,$key) {
		return unserialize($_SESSION["PLUGIN_SESSION_SPACE"][strtolower(get_class($plugin))][$key]);
	}
}
