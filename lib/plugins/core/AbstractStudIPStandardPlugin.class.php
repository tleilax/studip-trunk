<?php
# Lifter007: TODO

// vim: noexpandtab
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class AbstractStudIPStandardPlugin extends AbstractStudIPLegacyPlugin
  implements StandardPlugin {

	// Id, der dieses Plugin zugeordnet ist (bspw. Veranstaltung oder Institution)
	var $id;

	// wird dieses Plugin in der �bersicht (z.B. meine_seminare) angezeigt
	var $overview;

	// relativer Name des Icons f�r �nderungen an diesem Plugin
	var $changeindicatoriconname;

	function AbstractStudIPStandardPlugin() {
		parent::__construct();
		$this->id = $_SESSION['SessSemName'][1];
		$this->user->permission->setPoiid($this->id);
	}

	/**
	 * Sets the navigation of this plugin.
	 *
	 * @deprecated
	 */
	function setNavigation(StudipPluginNavigation $navigation) {
		parent::setNavigation($navigation);

		// prepend copy of navigation to its sub navigation
		$item_names = array_keys($navigation->getSubNavigation());
		$navigation_copy = clone $navigation;
		$navigation_copy->clearSubmenu();
		$navigation->insertSubNavigation('self', $item_names[0], $navigation_copy);
		$navigation->setTitle($this->getDisplayTitle());

		if (Navigation::hasItem('/course')) {
			Navigation::addItem('/course/' . $this->getPluginclassname(), $navigation);
		}
	}

	/**
	 * Set the current course id - deprecated, do not use.
	 *
	 * @deprecated
	 */
	function setId($id) {
		$this->id = $id;
	}

	/**
	 * Return current course id - deprecated, do not use.
	 *
	 * @deprecated
	 */
	function getId() {
		return $this->id;
	}

	/**
	 * Return a navigation object representing this plugin in the
	 * course overview table or return NULL if you want to display
	 * no icon for this plugin (or course). The navigation object's
	 * title will not be shown, only the image (and its associated
	 * attributes like 'title') and the URL are actually used.
	 *
	 * By convention, new or changed plugin content is indicated
	 * by a different icon and a corresponding tooltip.
	 *
	 * TODO: remove $last_visit parameter (needs new API?)
	 *
	 * @param  string   course or institute range id
	 * @param  int      time of user's last visit
	 *
	 * @return object   navigation item to render or NULL
	 */
	function getIconNavigation($course_id, $last_visit) {
		$this->setId($course_id);

		// $sem_type = get_object_type($course_id);
		// $last_visit = object_get_visit($this->id, $sem_type, 'current');

		if ($this->isShownInOverview() && $this->hasNavigation()) {
			$navigation = $this->getNavigation();

		  	if ($this->hasChanged($last_visit)) {
				$navigation->setImage($this->getChangeindicatoriconname(),
						array('title' => $this->getOverviewMessage(true)));
		  	} else {
				$navigation->setImage($this->getPluginiconname(),
						array('title' => $this->getOverviewMessage(false)));
		  	}
		}

		return $navigation;
	}

	/**
	 * Gehen beim Deaktivieren des Plugins Daten verloren?
	 *
	 * @deprecated
	 */
	function getPluginExistingItems($course_id) {
		return 0;
	}

	/**
	 * Return a warning message to be printed before deactivation of
	 * this plugin in the given context.
	 *
	 * @param $context   context range id
	 */
	function deactivationWarning($context) {
		if ($this->getPluginExistingItems($context)) {
			return _('Achtung: Beim Deaktivieren dieses Plugins gehen m�glicherweise Einstellungen verloren.');
		}

		return NULL;
	}

	/**
	 * Hat sich seit dem letzten Login etwas ge�ndert?
	 * @param lastlogin - letzter Loginzeitpunkt des Benutzers
	 *
	 * @deprecated
	 */
	function hasChanged($lastlogin) {
		return false;
	}

	/**
	 * Nachricht f�r tooltip in der �bersicht
	 * @param has_changed - hat sich etwas ge�ndert?
	 *
	 * @deprecated
	 */
	function getOverviewMessage($has_changed = false) {
		return $this->getPluginName() . ($has_changed ? ' ' . _("ge�ndert") : '');
	}

	/**
	 * Wird dieses Plugin in der �bersicht angezeigt?
	 *
	 * @deprecated
	 */
	function isShownInOverview() {
		return $this->overview;
	}

	/**
	 * Getter- und Setter f�r die Attribute
	 *
	 * @deprecated
         */
	function getChangeindicatoriconname() {
		return $this->getPluginURL() . '/' . $this->changeindicatoriconname;
	}

	function setChangeindicatoriconname($icon) {
		$this->changeindicatoriconname = $icon;
	}

	function setShownInOverview($value = true) {
		$this->overview = $value;
	}

	/**
	 * returns the score which the current user get's for activities in this plugin
	 *
	 * @deprecated
	 */
	function getScore()  {
		return 0;
	}

	/**
	 * Get the activation status of this plugin in the given context.
	 * This also checks the plugin default activations.
	 *
	 * @deprecated
	 *
	 * @param $context   context range id
	 */
	function isActivated($context = NULL) {
		return parent::isActivated($context ? $context : $this->id);
	}

	/**
	 * Sets the activation status of this plugin.
	 *
	 * @deprecated
	 *
	 * @param boolean    plugin status (true or false)
	 */
	function setActivated($value) {
		$plugin_manager = PluginManager::getInstance();

		$plugin_manager->setPluginActivated($this->getPluginId(), $this->id, $value);
	}

	/**
	 * This method sets everything up to perform the given action and
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

		parent::display_action($action);
	}
}
