<?php
# Lifter007: TODO

/*
 * AbstractStudIPLegacyPlugin.class.php - bridge for legacy plugins
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Abstract class for old style plugins in Stud.IP. It implements
 * method #perform() using the template method design pattern. Just implement
 * the #route or #display method in your actual old style plugin to change
 * the plugin's behaviour.
 *
 * @author      Marcus Lunzenauer <mlunzena@uos.de>
 * @version     $Id$
 * @package     pluginengine
 * @subpackage  core
 */

abstract class AbstractStudIPLegacyPlugin extends AbstractStudIPPlugin {


    /**
     * deprecated plugin fields, do not use
     *
     * @deprecated
     */
    public $pluginname;
    public $pluginid;
    public $pluginpath;
    public $basepluginpath;
    public $environment;
    public $navposition;
    public $dependentonplugin;
    public $navigation;
    public $pluginiconname;
    public $user;


    /**
     * constructor
     */
    function AbstractStudIPLegacyPlugin() {
        parent::__construct();

        $this->pluginname        = $this->getPluginname();
        $this->pluginid          = $this->getPluginid();
        $this->pluginpath        = $this->getPluginpath();
        $this->basepluginpath    = $this->getBasepluginpath();
        $this->environment       = $this->getEnvironment();
        $this->navposition       = $this->getNavigationPosition();
        $this->dependentonplugin = $this->isDependentOnOtherPlugin();
        $this->user              = new StudIPUser();
    }

    /**
     * Aktiviert das Plugin - deprecated, do not use.
     *
     * @deprecated
     */
    function activatePlugin() {
        $this->setActivated(true);
    }

    /**
     * Deaktiviert das Plugin - deprecated, do not use.
     *
     * @deprecated
     */
    function deactivatePlugin() {
        $this->setActivated(false);
    }

    /**
     * Returns the URI to the administration page of this plugin. Override this
     * method, if you want another URI, or return NULL to signal, that there is
     * no such page.
     *
     * @return string if this plugin has an administration page return its URI,
     *                return NULL otherwise
     */
    function getAdminLink() {
        return PluginEngine::getLink($this, array(), 'showAdministrationPage');
    }

    /**
     * Returns the plugin's relative path - deprecated, do not use.
     *
     * @deprecated
     */
    function getBasepluginpath() {
        return $this->plugin_info['path'];
    }

    /**
     * Which text should be shown in certain titles?
     *
     * @return string title
     */
    function getDisplaytitle() {
        return $this->hasNavigation() ?  $this->navigation->getTitle() : $this->getPluginName();
    }

    /**
     * Returns plugin environment - deprecated, do not use.
     *
     * @deprecated
     */
    function getEnvironment() {
        return $GLOBALS['pluginenv'];
    }

    /**
     * Returns this plugins's navigation.
     *
     * @deprecated
     */
    function getNavigation() {
        return $this->navigation;
    }

    /**
     * Returns the plugin's navigation position - deprecated, do not use.
     *
     * @deprecated
     */
    function getNavigationPosition() {
        return $this->plugin_info['position'];
    }

    /**
     * Returns the class name of this plugin (in lower case).
     */
    function getPluginclassname() {
        return strtolower($this->plugin_info['class']);
    }

    /**
     * Liefert den Pfad zum Icon dieses Plugins zur�ck.
     *
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
     * Returns the current user.
     *
     * @return StudIPUser
     */
    function getUser() {
        return $this->user;
    }

    /**
     * Check whether this plugin has a navigation.
     *
     * @deprecated
     */
    function hasNavigation() {
        return $this->navigation != null;
    }

    /**
     * Always returns false - deprecated, do not use.
     *
     * @deprecated
     */
    function isActivated() {
        return false;
    }

    /**
     * Check if this plugin depends on another plugin - deprecated, do not use.
     *
     * @deprecated
     */
    function isDependentOnOtherPlugin() {
        return $this->plugin_info['depends'] != NULL;
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setActivated($value) {
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setBasepluginpath($path) {
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setDependentOnOtherPlugin($dependentplugin = true) {
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setEnvironment($env) {
    }

    /**
     * Sets the navigation of this plugin.
     *
     * @deprecated
     */
    function setNavigation(StudipPluginNavigation $navigation) {
        $this->navigation = $navigation;

        if ($navigation instanceof PluginNavigation) {
            $navigation->setPlugin($this);
        }

        $active_plugin = PluginEngine::getCurrentPluginId();

        if (isset($active_plugin) && $active_plugin == $this->getPluginid()) {
            $navigation->setActive(true);
        }
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setNavigationPosition($pos) {
    }

    /**
     * Setzt den Pfad zum Icon dieses Plugins.
     */
    function setPluginiconname($icon) {
        $this->pluginiconname = $icon;
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setPluginid($id) {
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setPluginname($name) {
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setPluginpath($path) {
    }

    /**
     * Does nothing - deprecated, do not use.
     *
     * @deprecated
     */
    function setUser(StudIPUser $user) {
    }


  /**
   * This is the standard action of this plugin.
   */
  function actionShow($param = null) {
    return $this->show($param);
  }


  /**
   * Does nothing - deprecated, do not use.
   *
   * @param $subnavigationparam - set if a subnavigation item was clicked.
   *                              The value is plugin dependent and specified
   *                              by the plugins subnavigation link params.
   *
   * @deprecated
   */
  function show($subnavigationparam = null) {}


  /**
   * This method dispatches and displays all actions. It uses the template
   * method design pattern, so you may want to implement the methods #route
   * and/or #display to adapt to your needs.
   *
   * @param  string  the part of the dispatch path, that were not consumed yet
   *
   * @return void
   */
  function perform($unconsumed_path) {

    # get cmd
    list($cmd, $this->unconsumed_path) = $this->route($unconsumed_path);

    # it's action time
    try {

      ob_start();

      $this->display_action($cmd);
      ob_end_flush();

    } catch (Exception $e) {

      # disable output buffering
      while (ob_get_level()) {
        ob_end_clean();
      }

      # defer exception handling
      throw $e;
    }
  }


  /**
   * Called by #perform to detect the action to be accomplished.
   *
   * @param  string  the part of the dispatch path, that were not consumed yet
   *
   * @return string  the name of the instance method to be called
   */
  function route($unconsumed_path) {

    $tokens = preg_split('@/@', $unconsumed_path, -1, PREG_SPLIT_NO_EMPTY);
    $action = sizeof($tokens) ? 'action' . array_shift($tokens) : 'actionShow';

    $class_methods = array_map('strtolower', get_class_methods($this));
    if (!in_array(strtolower($action), $class_methods)) {
      throw new Exception(_("Das Plugin verf�gt nicht �ber die gew�nschte Operation"));
    }

    return array($action, join('/', $tokens));
  }


  /**
   * This method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string  the name of the action to accomplish
   * @param  string  the unconsumed rest
   *
   * @return void
   */
  function display_action($action) {
    if (!isset($GLOBALS['CURRENT_PAGE'])) {
      $GLOBALS['CURRENT_PAGE'] = $this->getDisplayTitle();
    }

    include 'lib/include/html_head.inc.php';
    include 'lib/include/header.php';

    $pluginparams = $_GET['plugin_subnavi_params'];

    StudIPTemplateEngine::startContentTable();
    $this->$action($pluginparams);
    StudIPTemplateEngine::endContentTable();

    include 'lib/include/html_end.inc.php';
  }
}
