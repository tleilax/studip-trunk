<?php
# Lifter002: TODO

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/functions.php';
require_once 'lib/exceptions/access_denied.php';

# set base url for URLHelper class
URLHelper::setBaseUrl($CANONICAL_RELATIVE_PATH_STUDIP);

# initialize Stud.IP-Session
page_open(array('sess' => 'Seminar_Session',
                'auth' => 'Seminar_Default_Auth',
                'perm' => 'Seminar_Perm',
                'user' => 'Seminar_User'));

try {

  require_once 'lib/seminar_open.php';

  unregister_globals();

  # get plugin class from request
  $dispatch_to = isset($_SERVER['PATH_INFO']) ?$_SERVER['PATH_INFO'] : '';
  list($plugin_class, $unconsumed) = PluginEngine::routeRequest($dispatch_to);

  # retrieve corresponding plugin info
  $plugin_manager = PluginManager::getInstance();
  $plugin_info = $plugin_manager->getPluginInfo($plugin_class);

  PluginEngine::setCurrentPluginId($plugin_info['id']);

  # create an instance of the queried plugin
  $plugin = PluginEngine::getPlugin($plugin_class);

  # user is not permitted, show login screen
  if (is_null($plugin)) {
    # TODO (mlunzena) should not getPlugin throw this exception?
    throw new Studip_AccessDeniedException();
  }

  if (is_callable(array($plugin, 'initialize'))) {
    $plugin->initialize();
  }

  # let the show begin
  $plugin->perform($unconsumed);

} catch (Studip_AccessDeniedException $ade) {

  global $auth;

  $auth->login_if($auth->auth["uid"] == "nobody");
  throw $ade;

}
