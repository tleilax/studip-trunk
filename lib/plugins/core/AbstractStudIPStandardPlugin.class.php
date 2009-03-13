<?php
# Lifter002: TODO

// vim: noexpandtab
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once("lib/classes/TreeAbstract.class.php");

class AbstractStudIPStandardPlugin extends AbstractStudIPLegacyPlugin
  implements StudIPStandardPlugin {

	// relativer Name des Icons f�r �nderungen an diesem Plugin
	var $changeindicatoriconname;

	// Id, der dieses Plugin zugeordnet ist (bspw. Veranstaltung oder Institution)
	var $id;

	// wird dieses Plugin in der �bersicht (z.B. meine_seminare) angezeigt
	var $overview;

	function AbstractStudIPStandardPlugin() {
		parent::AbstractStudIPLegacyPlugin();
		$this->pluginiconname = "";
		$this->changeindicatoriconname = "";
		$this->overview = false;
		$this->pluginengine = PluginEngine::getPluginPersistence("Standard");
		// create the standard AdminInfo
		$admininfo = new AdminInfo();
		$this->setPluginAdminInfo($admininfo);
	}

	/**
	 * set the current user
	 *
	 * @param StudIPUser $user
	 */
	function setUser(StudIPUser $user) {
		parent::setUser($user);
		$this->user->permission->setPoiid($this->getId());
	}

	function setId($newid) {
		$this->id = $newid;
	}

	function getId() {
		if ($this->id === NULL) {
			$this->id = $_SESSION['SessSemName'][1];
		} else {
			$this->id = str_replace($_SESSION["SessSemName"]["class"], '', $this->id);
	 	}
		return $this->id;
	}

	/**
	 * Hat sich seit dem letzten Login etwas ge�ndert?
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 */
	function hasChanged($lastlogin) {
		return false;
	}

	/**
	 * Nachricht f�r tooltip in der �bersicht
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 */
	function getOverviewMessage($has_changed = false) {
		return $this->getPluginname() . ($has_changed ? ' ' . _("ge�ndert") : '');
	}

	/**
	 * Wird dieses Plugin in der �bersicht angezeigt?
	 */
	function isShownInOverview() {
		return $this->overview;
	}

	/**
	 * Liefert die �nderungsmeldungen f�r die �bergebenen ids zur�ck
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 * @param ids - ein Array von Veranstaltungs- bzw. Institutionsids, zu denen
	 * die �nderungsnachricht bestimmt werden soll.
	 * @return �nderungsmeldungen
	 */
	function getChangeMessages($lastlogin, $ids) {
		return array();
	}

	/**
	 * Getter- und Setter f�r die Attribute
   */
	function getChangeindicatoriconname() {
		return $this->getPluginURL() . '/' . $this->changeindicatoriconname;
	}

	function setChangeindicatoriconname($newicon) {
		$this->changeindicatoriconname = $newicon;
	}

	function setShownInOverview($value = true) {
		$this->overview = $value;
	}

	/**
	 * returns the score which the current user get's for activities in this plugin
	 *
	 */
	function getScore()  {
		return 0;
	}


	/**
	 * This abstract method sets everything up to perform the given action and
	 * displays the results or anything you want to.
	 *
	 * @param  string the name of the action to accomplish
	 *
	 * @return void
	 */
	function display_action($action) {

		mark_public_course();

		$GLOBALS['CURRENT_PAGE'] =
			$_SESSION['SessSemName']['header_line'] . ' - ' . $this->getDisplayTitle();

		include 'lib/include/html_head.inc.php';
		include 'lib/include/header.php';

		$pluginparams = $_GET["plugin_subnavi_params"];

		include 'lib/include/links_openobject.inc.php';

		// let the plugin show its view
		StudIPTemplateEngine::startContentTable(true);
		$this->$action($pluginparams);
		StudIPTemplateEngine::endContentTable();

		// close the page
		include 'lib/include/html_end.inc.php';
		page_close();
	}
}
