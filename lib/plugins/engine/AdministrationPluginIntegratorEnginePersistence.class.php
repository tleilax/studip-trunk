<?php
/* vim: noexpandtab */
/**
 * poiid of administrators
 */
define("PLUGIN_ADMINISTRATION_POIID","admin");

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class AdministrationPluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {

	/**
	 * Liefert alle in der Datenbank bekannten Plugins zur�ck
	 */
	function getAllInstalledPlugins() {

		$plugins = array();
		// nur Administrations-Plugins liefern
		$plugins = parent::executePluginQuery("where plugintype='Administration' order by navigationpos, pluginname");

		foreach ($plugins as $plugin) {
			$db = DBManager::get();
			$stmt = $db->prepare("SELECT * FROM plugins_activated WHERE pluginid=? and poiid=?");
			$result = $stmt->execute(array($plugin->getPluginid(),
			                               PLUGIN_ADMINISTRATION_POIID));
			$plugin->setActivated($result && $result->columnCount() === 1);
			$extplugins[] = $plugin;
		}
		return $extplugins;
	}

	/**
	 * Liefert alle in der Datenbank bekannten und aktivierten Plugins zur�ck
	 */
	function getAllActivatedPlugins() {

		$db = DBManager::get();

		$user = $this->getUser();
		$userid = $user->getUserid();

		$stmt = $db->prepare(
			"SELECT p.* FROM plugins p ".
			"JOIN plugins_activated a ON p.pluginid=a.pluginid ".
			"JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			"JOIN roles_user r ON rp.roleid=r.roleid ".
			"WHERE a.poiid=? AND p.plugintype='Administration' AND r.userid=? ".

			"UNION ".

			"SELECT distinct p.* FROM plugins p ".
			"JOIN plugins_activated a ON p.pluginid=a.pluginid ".
			"JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			"JOIN roles_studipperms rps ON rps.roleid=rp.roleid ".
			"JOIN auth_user_md5 au ON rps.permname = au.perms ".
			"WHERE au.user_id=? AND a.poiid=? AND p.plugintype='Administration' ".
			"ORDER BY navigationpos, pluginname");


		$result = $stmt->execute(array(PLUGIN_ADMINISTRATION_POIID,
		                               $userid,
		                               $userid,
		                               PLUGIN_ADMINISTRATION_POIID));

		$plugins = array();

		// TODO: Fehlermeldung ausgeben
		// keine aktivierten Plugins
		if (!$result) {
			return array();
		}

		else {
			while ($row = $stmt->fetch()) {
				$pluginclassname = $row["pluginclassname"];
				$pluginpath = $row["pluginpath"];

				// Klasse instanziieren
				$plugin = PluginEngine::instantiatePlugin($pluginclassname,
				                                          $pluginpath);

				if ($plugin !== null) {
					$plugin->setPluginid($row["pluginid"]);
					$plugin->setPluginname($row["pluginname"]);
					$plugin->setActivated(true);
					$plugin->setUser($this->getUser());
					$plugins[] = $plugin;
				}
			}
			return $plugins;
		}
	}

	/**
	  * Speichere ein Plugin
	  */
	function savePlugin(AbstractStudIPAdministrationPlugin $plugin) {

		parent::savePlugin($plugin);

		// Plugin speichern
		if ($plugin->isActivated()) {
			# TODO (mlunzena) migrate to pdo
			$this->connection->execute("replace into plugins_activated (pluginid,poiid) values(?,?)", array($plugin->getPluginId(),PLUGIN_ADMINISTRATION_POIID));
		}
		// Plugin aus der aktiven Tabelle l�schen
		else {
			# TODO (mlunzena) migrate to pdo
			$this->connection->execute("delete from plugins_activated where pluginid=?", array($plugin->getPluginId()));
		}
	}

	function getPlugin($id) {

		$db = DBManager::get();
		$stmt = $db->prepare(
		  "SELECT p.* FROM plugins p ".
		  "LEFT JOIN plugins_activated a ON p.pluginid=a.pluginid ".
		  "WHERE a.poiid=? AND p.pluginid=? AND p.plugintype='Administration' ".
		  "AND (a.pluginid is null)");
		$result = $stmt->execute(array(PLUGIN_ADMINISTRATION_POIID, $id));

		// TODO: Fehlermeldung ausgeben
		# TODO (mlunzena) verl�sst sich jemand auf die "null"?
		#                 ansonsten w�re eine exception angesagt
		if (!$result) {
			return null;
		}

		$plugin = NULL;

		if ($row = $stmt->fetch()) {
			$pluginclassname = $row["pluginclassname"];
			$pluginpath = $row["pluginpath"];

			// Klasse instanziieren
			$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
			if ($plugin !== null){
				$plugin->setPluginid($row["pluginid"]);
				$plugin->setPluginname($row["pluginname"]);
				$plugin->setUser($this->getUser());
			}
		}

		return $plugin;
	}
}
