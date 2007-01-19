<?php
/*
 * Central point of access to plugins. Builds the top navigation and shows
 * the result of a plugins show implementation in the middle
 *
 * 
 * @author Dennis Reil, CELab <dennis.reil@offis.de>
 * @date 04.07.2005
 * @version $Revision$
 * @package pluginengine
 * $HeadURL$
 * $Revision$
 * $Author$
 */
ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");
// ini_set("display_errors","on");

include ("seminar_open.php"); 		// initialise Stud.IP-Session
include ('include/html_head.inc.php');
include ('include/header.php');

// read in the command and pluginid
$cmd = 'Action' . $_GET["cmd"];
$pluginid = $_GET["id"];

// create plugin persistence objects
$pluginengine = PluginEngine::getPluginPersistence();

// create an instance of the queried pluginid
$plugin = $pluginengine->getPlugin($pluginid);

if ($plugin == null){
	// maybe the pluginid is not a number
	// try to find a plugin class, satisfying the request
	$pluginid = $pluginengine->getPluginId($pluginid);

	if ($pluginid == UNKNOWN_PLUGIN_ID){
		StudIPTemplateEngine::makeHeadline(_("Plugin nicht vorhanden"));
		StudIPTemplateEngine::showErrorMessage(_("Das angeforderte Plugin ist nicht vorhanden."));
		die();
	}
	else {
		// create an instance of the queried pluginid
		$plugin = $pluginengine->getPlugin($pluginid);
		if ($plugin == null){
			StudIPTemplateEngine::makeHeadline(_("Plugin nicht vorhanden"));
			StudIPTemplateEngine::showErrorMessage(_("Das angeforderte Plugin ist nicht vorhanden."));
			die();
		}
	}	
}

if (!array_search(strtolower($cmd),array_map('strtolower', get_class_methods($plugin)))) {	
	die(_("Das Plugin verf�gt nicht �ber die gew�nschte Operation"));
}

if (array_search('initialize',array_map('strtolower', get_class_methods($plugin)))){
	// the plugin has an initialize-method
	// call it
	$plugin->initialize();
}

$type = PluginEngine::getTypeOfPlugin($plugin);

// set the gettext-domain
$domain = "gtdomain_" . get_class($plugin);
bindtextdomain($domain,$plugindbenv->getBasepath() . $plugin->getPluginpath() . "/locale");
textdomain($domain);
$pluginparams = $_GET["plugin_subnavi_params"];

if ($type == "Standard"){
	// diplay the admin_menu
	if (($cmd == "showConfigurationPage") && $perm->have_perm("admin")){
		include('include/links_admin.inc.php');
		
	}
	// display the course menu
	include ('include/links_openobject.inc.php');
	
	// let the plugin show its view	
	$pluginnav = $plugin->getNavigation();
	
	if (is_object($pluginnav)){
		$iconname = "";
		if ($pluginnav->hasIcon()){
			$iconname = $plugin->getPluginpath() . "/" . $pluginnav->getIcon();
		}
		else {
			$iconname = $plugin->getPluginiconname();
		}
		
		if (isset($SessSemName["header_line"])){
			StudIPTemplateEngine::makeHeadline(sprintf(_("%s - %s"),$SessSemName["header_line"],$pluginnav->getDisplayname()),true,$iconname);
		}
		else {
			StudIPTemplateEngine::makeHeadline(sprintf(_("%s"),$pluginnav->getDisplayname()),true,$iconname);			
		}
	}
	else {
		StudIPTemplateEngine::makeHeadline($plugin->getPluginname(),true,$plugin->getPluginiconname());
	}
	
	StudIPTemplateEngine::startContentTable(true);
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();	
}
else if ($type == "Administration") {
	// Administration-Plugins only accessible by users with admin rights
	if ($perm->have_perm("admin")){
	   // display the admin menu
	   include ('include/links_admin.inc.php');
	   
	   // let the plugin show its view	
	   $pluginnav = $plugin->getNavigation();
	   if ($pluginnav->hasIcon()){
	   		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginpath() . "/" .$pluginnav->getIcon());
	   }
	   else {
	   		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginiconname());
	   }	   
	   StudIPTemplateEngine::startContentTable(true);
	   $plugin->$cmd($pluginparams);   
	   StudIPTemplateEngine::endContentTable();
	   
	}
	else {
		StudIPTemplateEngine::makeHeadline(_("fehlende Rechte"));
		StudIPTemplateEngine::showErrorMessage(_("Sie verf�gen nicht �ber ausreichend Rechte f�r diese Aktion."));
	}
}
else if ($type == "System") {
	$pluginnav = $plugin->getNavigation();
	if ($pluginnav->hasIcon()){
		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginpath() . "/" .$pluginnav->getIcon());	
	}
	else {
		StudIPTemplateEngine::makeHeadline($pluginnav->getDisplayname(),true,$plugin->getPluginiconname());
	}
	
	StudIPTemplateEngine::startContentTable();
	// let the plugin show its view	 
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();
}
else if ($type == "Homepage"){
	textdomain('studip');
	// show the admin-Tabs
	$hpusername = $_GET["requesteduser"];	
	$admin_darf = FALSE;
	$db = new DB_Seminar();
	
	if (empty($hpusername)){
		$hpusername = $GLOBALS["auth"]->auth["uname"];
	}
	
	if ($GLOBALS["auth"]->auth["uname"] == $hpusername){
		$admin_darf = true;
	}	
		
	$db->query("SELECT * FROM auth_user_md5  WHERE username ='$hpusername'");
	$db->next_record();
	if (!$db->nf()) {
		parse_window ("error�"._("Es wurde kein Nutzer unter dem angegebenen Nutzernamen gefunden!")."<br />"._(" Wenn Sie auf einen Link geklickt haben, kann es sein, dass sich der Username des gesuchten Nutzers ge&auml;ndert hat, oder der Nutzer gel&ouml;scht wurde.")."�", "�", _("Benutzer nicht gefunden"));
		die;
	} else{
		$user_id=$db->f("user_id");
	}
	
	$requser = new StudIPUser();
	$requser->setUserid($user_id);
	$plugin->setRequestedUser($requser);

	//Bin ich ein Inst_admin, und ist der user in meinem Inst Tutor oder Dozent?
	$db->query("SELECT b.inst_perms FROM user_inst AS a LEFT JOIN user_inst AS b USING (Institut_id) WHERE (b.user_id = '$user_id') AND (b.inst_perms = 'autor' OR b.inst_perms = 'tutor' OR b.inst_perms = 'dozent') AND (a.user_id = '$user->id') AND (a.inst_perms = 'admin')");
	if ($db->num_rows())
		$admin_darf = TRUE;
	if ($perm->is_fak_admin()){
		$db->query("SELECT c.user_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id)  LEFT JOIN user_inst c ON(b.Institut_id=c.Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND c.user_id='$user_id'");
		if ($db->next_record())
		$admin_darf = TRUE;
	}
	if ($perm->have_perm("root")) {
		$admin_darf=TRUE;
	}

	IF ($perm->have_perm("root") OR $admin_darf == TRUE) { // Es werden die Editreiter angezeigt, wenn ich &auml;ndern darf
		// rights should be checked
		$username = $hpusername;
		include('include/links_about.inc.php');	
	}
	textdomain($domain);
	$pluginnav = $plugin->getNavigation();
	StudIPTemplateEngine::makeHeadline($plugin->getDisplaytitle(),true,$plugin->getPluginiconname());
	StudIPTemplateEngine::startContentTable();
	// let the plugin show its view	 
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();
} else if ($type == "Portal" || $type == "Core"){
	StudIPTemplateEngine::makeHeadline($plugin->getDisplaytitle(),true,$plugin->getPluginiconname());
	StudIPTemplateEngine::startContentTable();
	// let the plugin show its view	 
	$plugin->$cmd($pluginparams);
	StudIPTemplateEngine::endContentTable();
}
else {
	 // Further plugin types have to be integrated here
	 echo (_("Unbekannter Plugin-Typ"));
}
// restore the domain
textdomain("studip");
// close the page
include ('include/html_end.inc.php');
page_close();
ob_end_flush();
?>
