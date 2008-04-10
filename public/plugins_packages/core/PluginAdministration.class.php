<?php
// vim: noexpandtab
/**
 * Basic methods for managing plugins
 * @author Dennis Reil <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once('lib/datei.inc.php');
require_once 'lib/migrations/db_migration.php';
require_once 'lib/migrations/db_schema_version.php';
require_once 'lib/migrations/migrator.php';

define("PLUGIN_UPLOAD_ERROR",1);
define("PLUGIN_MANIFEST_ERROR",2);
define("PLUGIN_ALLREADY_INSTALLED_ERROR",3);
define("PLUGIN_INSTANTIATION_EROR",5);
define("PLUGIN_MISSING_METHOD_ERROR",6);
define("PLUGIN_INSTALLATION_SUCCESSFUL",7);
define("PLUGIN_MISSING_MANIFEST_ERROR",8);
define("PLUGIN_ALREADY_REGISTERED_ERROR",9);

class PluginAdministration {
	var $environment;

	/**
	 * Creates a new object for plugin administration
	 *
	 * @param Environment $environment the plugin environment
	 */
	function PluginAdministration($environment){
		$this->environment = $environment;
	}

	/**
	 * Deletes a directory
	 * @param string $dir the directory, which should be deleted
	 */
	function deletePlugindir($dir) {
		if (file_exists($dir)){
			rmdirr($dir);
		}
	}

	/**
	 * Does the uninstallation of a plugin.
	 *
	 * @param unknown_type $plugin
	 */
	function deinstallPlugin($plugin){
		$type = PluginEngine::getTypeOfPlugin($plugin);
		$plugin->prepareUninstallation();
		$engine = PluginEngine::getPluginPersistence($type);
		if (is_object($engine)){
			$engine->deinstallPlugin($plugin);
		}
		$pluginenv = $plugin->getEnvironment();
                $pluginpath = $pluginenv->getBasepath() . '/' . $plugin->getPluginpath();
		$manifest = PluginEngine::getPluginManifest($pluginpath);

		// delete database if needed
		$this->deleteDBSchema($pluginpath, $manifest);

		// the old plugin directory has to be deleted
		$this->deletePlugindir($pluginpath);
	}

	/**
	 * Installs a new plugin.
	 * - grabs the uploaded file, unzips it, reads the manifest, creates a new plugin directory und
	 *   finally registers it in the global plugin space.
	 * @param string $uploadfilename the name of the uploaded file
	 * @return ERROR_CODE/SUCCESS_CODE
	 */
	function installPlugin($uploadfilename,$forceupdate=false){
		$currdate = mktime();
		$pluginstmpdir = $GLOBALS["TMP_PATH"] . "/plugins_tmp/";
		$tmppackagedir = $pluginstmpdir . $currdate . "/";
		// check if directory exists
		if (!file_exists($pluginstmpdir)){
			@mkdir($pluginstmpdir);
		}
		if (!file_exists($tmppackagedir)){
			@mkdir($tmppackagedir);
		}
		// create new filename
		$newuploadfilename = $tmppackagedir . basename($uploadfilename);
		// move package
		$copy_func = is_uploaded_file($uploadfilename) ? 'move_uploaded_file' : 'copy';
		if (!@$copy_func($uploadfilename,$newuploadfilename)){
			return PLUGIN_UPLOAD_ERROR;
		}
		else {
			// delete uploaded file
			@unlink($uploadfilename);
		}
		unzip_file($newuploadfilename,$tmppackagedir);

		// delete uploaded file
		@unlink($newuploadfilename);
		// search for the manifest
		if (!file_exists($tmppackagedir . "/plugin.manifest")){
			return PLUGIN_MISSING_MANIFEST_ERROR;
		}

		// everything ok, so far
		$plugininfos = PluginEngine::getPluginManifest($tmppackagedir);

		if ((strlen($plugininfos["class"]) > 0) && (strlen($plugininfos["origin"]) > 0) && (strlen($plugininfos["version"]) > 0)){
			// Plugin-Hauptclasse instanziieren
			$pluginclassname = trim($plugininfos["class"]);

			// Klasse instanziieren
			if (strlen($pluginclassname) > 0){
				// Neuen Pfad bestimmen
				$vendordir = $this->environment->getPackagebasepath() . "/" . $plugininfos["origin"];
				$newpluginpath = $vendordir . "/" . $pluginclassname; // . "_" . $plugininfos["version"];
				$pluginrelativepath = $plugininfos["origin"] . "/" . $pluginclassname; //  . "_" . $plugininfos["version"];
				$persistence = PluginEngine::getPluginPersistence();
				$pluginregistered = $persistence->isPluginRegistered($pluginclassname);

				if (!file_exists($vendordir)){
					@mkdir($vendordir);
				}

				if (!file_exists($newpluginpath)){
					// ok, plugin in exact this version is not installed
					@mkdir($newpluginpath);
					// do we have to delete the old plugin directory?
				}
				else {
					// directory exists
					// is the plugin already installed?
					if (!$pluginregistered){
						// not registered in database
						// delete directory
						$this->deletePlugindir($newpluginpath);
						// and create an empty directory
						@mkdir($newpluginpath);
					}
					else {
						// Plugin is already registered
						if (!$forceupdate){
							// plugin is registered and installed
							// and we didn't request to do an forced update
					 		return PLUGIN_ALLREADY_INSTALLED_ERROR;
						}
						else {
							// forced update
							$this->updateDBSchema($newpluginpath, $tmppackagedir, $plugininfos);
							// only delete the plugin directory
							// registration info will be updated automatically
							$this->deletePlugindir($newpluginpath);
						}
					}
				}
				// check to see, if the plugin is already registered
				if ($pluginregistered && !$forceupdate){
					return PLUGIN_ALREADY_REGISTERED_ERROR;
				}
				// everything fine, install it

				// copy files
				$this->copyr($tmppackagedir,$newpluginpath);
				// delete the temporary path
				$this->deletePlugindir($tmppackagedir);

				// create database if needed
				$this->createDBSchema($newpluginpath, $plugininfos);

				// instantiate plugin
				require_once($newpluginpath . '/' . $pluginclassname . ".class.php");

				$plugin = new $pluginclassname();
				if ($plugin == null){
					// delete Plugin directory
					$this->deletePlugindir($newpluginpath);
					return PLUGIN_INSTANTIATION_EROR;
				}
				else {
					// check if certain methods exist in the plugin
					$methods = array_map('strtolower', get_class_methods($plugin));
					if (array_search('show',$methods)){
						// now register the plugin in the database
						$newpluginid = $persistence->registerPlugin($plugin,$pluginclassname,$pluginrelativepath);
						if ($newpluginid > 0){
							$plugin->setPluginid($newpluginid);
						}
						// do we have additional plugin classes in this package?
						$additionalclasses = $plugininfos["additionalclasses"];
						if (is_array($additionalclasses)){
							foreach ($additionalclasses as $additionalclass){
								require_once($newpluginpath . '/' . $additionalclass . ".class.php");
								$additionalplugin = new $additionalclass();
								$persistence->registerPlugin($additionalplugin,$additionalclass,$pluginrelativepath,$plugin);
							}
						}
					}
					else {
						$this->deletePlugindir($newpluginpath);
						return PLUGIN_MISSING_METHOD_ERROR;
					}
				}
				return PLUGIN_INSTALLATION_SUCCESSFUL;
			}
		}
		else {
			return PLUGIN_MANIFEST_ERROR;
		}
	}

	/**
	 * Create the initial database schema for the plugin.
	 *
	 * @param string $pluginpath absolute path to the plugin
	 * @param array  $manifest   plugin manifest information
	 */
	function createDBSchema ($pluginpath, $manifest) {
		$pluginname = $manifest['pluginname'] ? $manifest['pluginname']
		                                      : $manifest['pluginclassname'];
		// TODO this probably should not happen during update
		if (isset($manifest['dbscheme'])) {
			$schemafile = $pluginpath.'/'.$manifest['dbscheme'];
			$statements = split(";[[:space:]]*\n", file_get_contents($schemafile));
			$db = DBManager::get();
			foreach ($statements as $statement) {
				$db->exec($statement);
			}
		}

		if (is_dir($pluginpath.'/migrations')) {
			$schema_version = new DBSchemaVersion($pluginname);
			$migrator = new Migrator($pluginpath.'/migrations', $schema_version);
			$migrator->migrate_to(null);
		}
	}

	/**
	 * Update the database schema maintained by the plugin.
	 *
	 * @param string $pluginpath absolute path to the plugin
	 * @param array  $manifest   plugin manifest information
	 */
	function updateDBSchema ($pluginpath, $new_pluginpath, $manifest) {
		$pluginname = $manifest['pluginname'] ? $manifest['pluginname']
		                                      : $manifest['pluginclassname'];

		if (is_dir($pluginpath.'/migrations')) {
			$schema_version = new DBSchemaVersion($pluginname);
			$new_version = 0;

			if (is_dir($new_pluginpath.'/migrations')) {
				$migrator = new Migrator($new_pluginpath.'/migrations', $schema_version);
				$new_version = $migrator->top_version();
			}

			$migrator = new Migrator($pluginpath.'/migrations', $schema_version);
			$migrator->migrate_to($new_version);
		}
	}

	/**
	 * Delete the database schema maintained by the plugin.
	 *
	 * @param string $pluginpath absolute path to the plugin
	 * @param array  $manifest   plugin manifest information
	 */
	function deleteDBSchema ($pluginpath, $manifest) {
		$pluginname = $manifest['pluginname'] ? $manifest['pluginname']
		                                      : $manifest['pluginclassname'];

		if (is_dir($pluginpath.'/migrations')) {
			$schema_version = new DBSchemaVersion($pluginname);
			$migrator = new Migrator($pluginpath.'/migrations', $schema_version);
			$migrator->migrate_to(0);
		}

		if (isset($manifest['uninstalldbscheme'])) {
			$schemafile = $pluginpath.'/'.$manifest['uninstalldbscheme'];
			$statements = split(";[[:space:]]*\n", file_get_contents($schemafile));
			$db = DBManager::get();
			foreach ($statements as $statement) {
				$db->exec($statement);
			}
		}
	}

	/**
	 * recursive copy
	 *
	 * @param string $source the sourcedirectory
	 * @param string $dest the destination directory
	 * @return true - success
	 *                false - error
	 */
	function copyr($source, $dest) {

		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}

		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest);
		}

		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// Deep copy directories
			if ($dest !== "$source/$entry") {
				$this->copyr("$source/$entry", "$dest/$entry");
			}
		}

		// Clean up
		$dir->close();
		return true;
	}

	/**
	 * Zips a plugin packages
	 *
	 * @param int $pluginid the id of the plugin
	 * @return string a link to the file
	 */
	function zipPluginPackage($pluginid){
		// zip the plugin-Directory and send it to the client
		$persistence = PluginEngine::getPluginPersistence();
		$plugin = $persistence->getPlugin($pluginid);
		$manifest = PluginEngine::getPluginManifest($this->environment->getBasepath() . $plugin->getPluginpath());
		$file_id = strtolower(get_class($plugin)) . "_" . $manifest["version"] . ".zip";
		create_zip_from_directory($this->environment->getBasepath() . $plugin->getPluginpath(), $GLOBALS["TMP_PATH"] . "/" . $file_id);
		return GetDownloadLink($file_id, $file_id, 4, 'force');
	}
}
