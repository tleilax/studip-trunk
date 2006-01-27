<?php
/**
 * Abstract class for a plugin in Stud.IP.
 * Don't use this as a base class for creating your own plugin. Look at 
 * AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin or AbstractStudIPAdministrationPlugin 
 * for creating a plugin.
 * 
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 * @see AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin, AbstractStudIPAdministrationPlugin 
 * $Id$
 */

class AbstractStudIPPlugin {
	var $pluginname;
	var $pluginid;
	var $pluginpath;
	
	
	var $pluginadmininfo;
	var $pluginiconname;
	var $user;
	var $helpinfo;
	
	var $navigation;
	var $activated;
	var $environment;
	var $enabled; // plugin available in system
	var $navposition; // the position in the navigation men�

	
	/**
	  Constructor
	*/
	function AbstractStudIPPlugin(){
		$this->pluginname = "";
		$this->pluginid = "-1";
		$this->pluginadmininfo = null;
		$this->pluginiconname = "";
		$this->helpinfo = null;
		$this->navigation = null;
		$this->activated = false;
		$this->user=new StudIPUser();
		$this->environment=null;
		$this->pluginpath = "";
		$this->enabled = false;
		$this->navposition = 99999; // a high value to put it at the end of the list
	}
	
	function getPluginclassname(){
		return get_class($this);
	}
	
	/**
	* Shows a page describing the plugin's functionality, dependence on other plugins, ... 
	*/
	function showDescriptionalPage(){	
		
		if (is_object($this->user)){
		   $permission = $this->user->getPermission();
		   if (!$permission->hasAdminPermission()){
		   	  return;
		   }
		}

	   $plugininfos = PluginEngine::getPluginManifest($this->environment->getBasepath() . $this->pluginpath . "/");
	   $version = $plugininfos["version"];
	   $vendor = $plugininfos["origin"];
	   $origname = $plugininfos["pluginname"];
	   StudIPTemplateEngine::makeHeadline(_("Plugin-Details"));
	   StudIPTemplateEngine::startContentTable();
		?>
				<tr>
					<td>Name:</td>
					<td align="left"><?= $this->pluginname ?></td>
				</tr>
				<tr>
					<td>Name (original):</td>
					<td align="left"><?= $origname ?></td>
				</tr>
				<tr>
					<td>Klasse:</td>
					<td align="left"><?= $this->getPluginclassname() ?></td>
				</tr>
				<tr>
					<td>Origin:</td>
					<td align="left"><?= $vendor ?></td>
				</tr>
				<tr>
					<td>Version:</td>
					<td align="left"><?= $version ?></td>
				</tr>
				
		<?php
		StudIPTemplateEngine::endContentTable();
	}
	
	function showAdministrationPage(){
		echo ("AdministrationPage not implemented yet.");
	}
	
	function setUser($newuser){
		if (is_a($newuser,'StudIPUser') || is_subclass_of($newuser,'StudIPUser')){
			$this->user = $newuser;
		}
	}
	
	function getUser(){
		return $this->user;
	}
	
	function setActivated($value=false){
		$this->activated = $value;		
	}
	
	function setEnabled($value=false){
		$this->enabled = $value;
	}
	
	function isEnabled(){
		return $this->enabled;
	}
	
	/**
	 * Diese Methode �berpr�ft, ob alle Voraussetzungen f�r den Einsatz dieses
	 * Plugins erf�llt sind.
	 * @param die Stud.IP-Version von der dieses Plugin eingebunden wird
	 * @return false - Voraussetzungen f�r das Plugin sind nicht erf�llt, Plugin
	 *                 kann  nicht installiert werden.
	 * 			true - alles in Ordnung, Plugin kann installiert werden.
	 */
	function checkVersion($studipversion){
		return false;
	}
	
	/**
	 * Liefert die Administrationsinformationen zu diesem Plugin zur�ck
	 */
	function getPluginAdminInfo(){
		return $this->pluginadmininfo;
	}
	
	/**
	 * setzt neue Administrationsinformationen zu diesem Plugin
	 * @param AdminInfo
	 */
	function setPluginAdminInfo($admininfo){
		if (is_a($admininfo,'AdminInfo') || is_subclass_off($admininfo,'AdminInfo')){
			$this->pluginadmininfo = $admininfo;
		}
		else {
			echo "Incompatible Paramter type";
		}
	}
	
	/**
	 * Aktiviert das Plugin
	 * @return  true - Erfolg 			
	 * 			false	 - kein Erfolg
	 */
	function activatePlugin(){
		$this->activated = true;
		return false;
	}
	
	/**
	 * Dektiviert das Plugin
	 * @return  true - Erfolg 			
	 * 			false	 - kein Erfolg
	 */
	function deactivatePlugin(){
		$this->activated = false;
		return false;
	}
	
	/**
	 * Liefert den relativen Namen des Icons dieses Plugins zur�ck
	 * @return den relativen Namen des Icons
	 */
	function getPluginiconname(){
		return $this->getPluginpath() . "/" . $this->pluginiconname;
	}
	
	/**
	 * Liefert die Persistenzschnittstelle zu diesem Plugin zur�ck.
	 * @return die Persisitenzschnittstelle vom Typ AbstractPluginPersistence
	 */
	/*
	function getPersistencePlugin(){
		return $this->pluginpersistence;
	}
	*/
	
	
	/**
	 * Getter und Setter f�r die Attribute der Klasse
	 */
	function setPluginiconname($newicon){
		$this->pluginiconname = $newicon;
	}
	
	function getPluginname(){
		return $this->pluginname;
	}
	
	function setPluginname($newname){
		$this->pluginname = $newname;
	}
	
	function setPluginid($newid){
		$this->pluginid = $newid;
	}
	
	function getPluginid(){
		return $this->pluginid;
	}
	
	function getHelpinfo(){
		return $this->helpinfo;
	}
	
	function setHelpInfo($newhelpinfo){
		if (is_a($newhelpinfo,'HelpInfo') || is_subclass_of($newhelpinfo,'HelpInfo')){
			$this->helpinfo = $newhelpinfo;
		}
	}
	
	function getNavigation(){
		return $this->navigation;
	}
	
	function setNavigation($newnavigation){
		if (is_a($newnavigation,'PluginNavigation') || is_subclass_of($newnavigation,'PluginNavigation')){
		    $this->navigation = $newnavigation;
		}
	}
	
	function hasNavigation(){
		if ($this->navigation != null){
		   return true;
		}
		else {
		   return false;
		}
	}
	
	function isActivated(){
		return $this->activated;
	}
	
	
	function setEnvironment($newenv){
		$this->environment = $newenv;
		$this->setPluginPath($newenv->getRelativepackagepath());
	}
	
	function getEnvironment(){
		return $this->environment;
	}
	
	function setPluginpath($newpath){
		$this->pluginpath = $newpath;
	}
	
	function getPluginpath(){
		return $this->pluginpath;
	}
	
	/*
	function setPluginengine($engine){
		if (is_a($engine,'PluginIntegratorEngine') || is_subclass_of($engine,'PluginIntegratorEngine')){
		   $this->pluginengine=$engine;
		}
	}
	
	
	function getPluginengine(){
		return $this->pluginengine;
	}
	*/
	
	/**
	* @param $subnavigationparam - set if a subnavigation item was clicked. The value is plugin dependant and specified by the plugins subnavigation link params.
	*/
	function show($subnavigationparam=null){
	}
	
	function getNavigationPosition(){
		return $this->navposition;
	}
		
	function setNavigationPosition($newpos){
		$this->navposition = $newpos;
	}

}
 
?>
