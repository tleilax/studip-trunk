<?php
# Lifter002: TODO

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 */

if ($GLOBALS['PLUGINS_ENABLE']){
	// the plugin interface classes
	require_once("core/StudIPInstitute.class.php");
	require_once("core/AbstractStudIPPluginVisualization.class.php");
	require_once("core/AdminInfo.class.php");
	require_once("core/ChangeMessage.class.php");
	require_once("core/HelpInfo.class.php");
	require_once("core/StudipPluginNavigation.class.php");
	require_once("core/PluginNavigation.class.php");
	require_once("core/Permission.class.php");
	require_once("core/StudIPUser.class.php");
	require_once("core/Environment.class.php");
	require_once("core/AbstractStudIPPlugin.class.php");
	require_once("core/AbstractStudIPLegacyPlugin.class.php");
	require_once("core/StudIPCorePlugin.class.php");
	require_once("core/StudIPAdministrationPlugin.class.php");
	require_once("core/StudIPStandardPlugin.class.php");
	require_once("core/StudIPSystemPlugin.class.php");
	require_once("core/StudIPHomepagePlugin.class.php");
	require_once("core/StudIPPortalPlugin.class.php");
	require_once("core/AbstractStudIPCorePlugin.class.php");
	require_once("core/AbstractStudIPAdministrationPlugin.class.php");
	require_once("core/AbstractStudIPStandardPlugin.class.php");
	require_once("core/AbstractStudIPSystemPlugin.class.php");
	require_once("core/AbstractStudIPHomepagePlugin.class.php");
	require_once("core/AbstractStudIPPortalPlugin.class.php");
	require_once("core/AbstractStudienmodulManagementPlugin.class.php");
	require_once("core/StudIPCore.class.php");
	require_once("engine/AbstractPluginIntegratorEnginePersistence.class.php");
	require_once("engine/StandardPluginIntegratorEnginePersistence.class.php");
	require_once("engine/AdministrationPluginIntegratorEnginePersistence.class.php");
	require_once("engine/SystemPluginIntegratorEnginePersistence.class.php");
	require_once("engine/HomepagePluginIntegratorEnginePersistence.class.php");
	require_once("engine/PortalPluginIntegratorEnginePersistence.class.php");
	require_once("engine/CorePluginIntegratorEnginePersistence.class.php");
	require_once("engine/StudIPTemplateEngine.class.php");
	require_once("engine/PluginEngine.class.php");
	require_once("engine/PluginNotFound.class.php");
	require_once("core/de_studip_Role.class.php");
	require_once("db/de_studip_RolePersistence.class.php");

	// create a plugin environment
	$pluginenv = new Environment();
	$pluginenv->setBasepath($GLOBALS["STUDIP_BASE_PATH"] .'/public/');
	if (isset($GLOBALS["PLUGINS_PATH"]) && !empty($GLOBALS["PLUGINS_PATH"])){
		$pluginenv->setPackagebasepath($GLOBALS["PLUGINS_PATH"]);
	}
	else {
		// set the default
		$pluginenv->setPackagebasepath($GLOBALS["STUDIP_BASE_PATH"].
		                                 '/public/plugins_packages');
	}
	$pluginenv->setRelativepackagepath("plugins_packages");
	$pluginenv->setTmppath($GLOBALS["TMP_PATH"]);
}

?>
