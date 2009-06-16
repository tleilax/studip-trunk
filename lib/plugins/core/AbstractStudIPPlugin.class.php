<?php
# Lifter002: TODO
# Lifter007: TODO
// vim: noexpandtab
/**
 * Abstract class for a plugin in Stud.IP.
 * Don't use this as a base class for creating your own plugin. Look at
 * AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin or
 * AbstractStudIPAdministrationPlugin for creating a plugin.
 *
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 * @version $Revision$
 * @see AbstractStudIPStandardPlugin, AbstractStudIPSystemPlugin, AbstractStudIPAdministrationPlugin
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

abstract class AbstractStudIPPlugin {

	var $pluginname;
	var $pluginid;
	var $pluginpath;

	/**
	 * the pluginpath without the plugins_directory
	 */
	var $basepluginpath;


	var $pluginadmininfo;
	var $pluginiconname;
	var $user;
	var $helpinfo;

	var $navigation;
	var $activated;
	var $environment;

	/**
	 * plugin available in system
	 */
	var $enabled;

	/**
	 * the position in the navigation men�
	 */
	var $navposition;

	/**
	 * this plugin depends on another plugin
	 */
	var $dependentonplugin;


	/**
	  Constructor
	*/
	function AbstractStudIPPlugin() {
		$this->pluginname = "";
		$this->pluginid = "-1";
		$this->pluginadmininfo = null;
		$this->pluginiconname = NULL;
		$this->helpinfo = null;
		$this->navigation = null;
		$this->activated = false;
		$this->setUser(new StudIPUser());
		$this->environment=null;
		$this->pluginpath = "";
		$this->basepluginpath = "";
		$this->enabled = false;
		$this->navposition = 99999; // a high value to put it at the end of the list
		$this->dependentonplugin = false;
	}

	function getPluginclassname() {
		return strtolower(get_class($this));
	}

	function showAdministrationPage() {
		echo _("Eine Administrationsseite ist f�r dieses Plugin nicht vorhanden");
	}

	/**
	 * set the current user
	 *
	 * @param StudIPUser $newuser
	 */
	function setUser(StudIPUser $newuser) {
		$this->user = $newuser;
	}

	/**
	 * Returns the current user
	 *
	 * @return StudIPUser
	 */
	function getUser() {
		return $this->user;
	}

	/**
	 * Sets the state of the plugin.
	 *
	 * @param boolean $value
	 * @param boolean $requestedbyuser - true if the user requested to change the
	 *                status
	 */
	function setActivated($value = false, $requestedbyuser = false) {
		$this->activated = $value;
	}

	function setEnabled($value = false) {
		$this->enabled = $value;
	}

	function isEnabled() {
		return $this->enabled;
	}

	/**
	 * Diese Methode �berpr�ft, ob alle Voraussetzungen f�r den Einsatz dieses
	 * Plugins erf�llt sind.
	 * @param die Stud.IP-Version von der dieses Plugin eingebunden wird
	 * @return false - Voraussetzungen f�r das Plugin sind nicht erf�llt, Plugin
	 *                 kann  nicht installiert werden.
	 *         true  - alles in Ordnung, Plugin kann installiert werden.
	 */
	function checkVersion($studipversion) {
		return false;
	}

	/**
	 * Liefert die Administrationsinformationen zu diesem Plugin zur�ck
	 */
	function getPluginAdminInfo() {
		return $this->pluginadmininfo;
	}

	/**
	 * setzt neue Administrationsinformationen zu diesem Plugin
	 * @param AdminInfo
	 */
	function setPluginAdminInfo(AdminInfo $admininfo) {
		$this->pluginadmininfo = $admininfo;
	}

	/**
	 * Aktiviert das Plugin
	 * @return  true - Erfolg
	 *          false - kein Erfolg
	 */
	function activatePlugin() {
		$this->activated = true;
		return false;
	}

	/**
	 * Dektiviert das Plugin
	 * @return  true - Erfolg
	 *          false - kein Erfolg
	 */
	function deactivatePlugin() {
		$this->activated = false;
		return false;
	}

	/**
	 * Liefert den Pfad zum Icon dieses Plugins zur�ck
	 * @return den Pfad zum Icon
	 */
	function getPluginiconname() {
		if ($this->hasNavigation() && $this->navigation->hasIcon()) {
			return $this->getPluginURL().'/'.$this->navigation->getIcon();
		} else if (isset($this->pluginiconname)) {
			return $this->getPluginURL().'/'.$this->pluginiconname;
		} else {
			return Assets::image_path('icon-leer.gif');
		}
	}

	/**
	 * Getter und Setter f�r die Attribute der Klasse
	 */
	function setPluginiconname($newicon) {
		$this->pluginiconname = $newicon;
	}

	function getPluginname() {
		if ($this->pluginname == "") {
			$this->pluginname = strtolower(get_class($this));
		}
		return $this->pluginname;
	}

	function setPluginname($newname) {
		$this->pluginname = $newname;
	}

	function setPluginid($newid) {
		$this->pluginid = $newid;
	}

	function getPluginid() {
		return $this->pluginid;
	}

	function getHelpinfo() {
		return $this->helpinfo;
	}

	function setHelpInfo(HelpInfo $newhelpinfo) {
		$this->helpinfo = $newhelpinfo;
	}

	function getNavigation() {
		return $this->navigation;
	}

	function setNavigation(StudipPluginNavigation $newnavigation) {
		$this->navigation = $newnavigation;
		$this->navigation->setPlugin($this);
	}

	function hasNavigation() {
		return $this->navigation != null;
	}

	function isActivated() {
		return $this->activated;
	}


	function setEnvironment($newenv) {
		$this->environment = $newenv;
		$this->setPluginPath($newenv->getRelativepackagepath());
	}

	function getEnvironment() {
		return $this->environment;
	}

	function setPluginpath($newpath) {
		$this->pluginpath = $newpath;
	}

	function getPluginpath() {
		return $this->pluginpath;
	}

	function getPluginURL() {
		return $GLOBALS['ABSOLUTE_URI_STUDIP'].$this->getPluginpath();
	}

	function setBasepluginpath($newpath) {
		$this->basepluginpath = $newpath;
	}

	function getBasepluginpath() {
		return $this->basepluginpath;
	}


	function getNavigationPosition() {
		return $this->navposition;
	}

	function setNavigationPosition($newpos) {
		$this->navposition = $newpos;
	}

	/**
	 * Which text should be shown in certain titles
	 * @return string title
	 */
	function getDisplaytitle() {
		if ($this->hasNavigation()) {
			return $this->navigation->getDisplayname();
		}
		else {
			return $this->getPluginname();
		}
	}

	/**
	 * Sets, if the plugin is main oder dependent on other plugins
	 * @param boolean $dependentplugin
	 */
	function setDependentOnOtherPlugin($dependentplugin = true) {
		if (is_bool($dependentplugin)) {
			$this->dependentonplugin = $dependentplugin;
		}
	}

	/**
	 * returns true, if this plugin depends on another plugin
	 *
	 */
	function isDependentOnOtherPlugin() {
		return $this->dependentonplugin;
	}

	/**
	 * Returns the URI to the administration page of this plugin. Override this
	 * method, if you want another URI, or return NULL to signal, that there is
	 * no such page.
	 *
	 * @return mixed  if this plugin has an administration page return its URI,
	 *                return NULL otherwise
	 */
	function getAdminLink() {
		return PluginEngine::getLink($this, array(), 'showAdministrationPage');
	}

  /**
   * This method dispatches all actions.
   *
   * @param  string  the part of the dispatch path, that were not consumed yet
   *
   * @return void
  */
  abstract function perform($unconsumed_path);
}

