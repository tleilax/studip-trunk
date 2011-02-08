<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * user_webservice.php - User webservice for Stud.IP
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'lib/webservices/api/studip_user.php';


/**
 * Service definition regarding Stud.IP users.
 *
 * @package   studip
 * @package   webservice
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */

class UserService extends AccessControlledService {

  /**
   * This method is called before every other service method and tries to
   * authenticate an incoming request using the first argument as an so
   * called "api key". If the "api key" matches a valid one, the request will
   * be authorized, otherwise a fault is sent back to the caller.
   *
   * @param string the function's name.
   * @param array an array of arguments that will be delivered to the function.
   *
   * @return mixed if this method returns a "soap_fault" or "FALSE", further
   *               processing will be aborted and a "soap_fault" delivered.
   */

   static function parse_msg_to_clean_text($long_msg,$separator="�") {
       $msg = explode ($separator,$long_msg);
       $ret = array();
       for ($i=0; $i < count($msg); $i=$i+2) {
           if ($msg[$i+1]) $ret[] = trim(decodeHTML(preg_replace ("'<[\/\!]*?[^<>]*?>'si", "", $msg[$i+1])));
       }
       return join("\n", $ret);
   }

   function UserService() {
       $this->add_api_method('create_user',
           array('', 'Studip_User'),
           'Studip_User',
           'creates a new user');
       $this->add_api_method('find_user_by_user_name',
           array('', ''),
           'Studip_User',
           'finds a user by username');
       $this->add_api_method('update_user',
           array('', 'Studip_User'),
           'Studip_User',
           'updates user');
       $this->add_api_method('delete_user',
           array('', ''),
           true,
           'deletes user with given username');
       $this->add_api_method('check_credentials',
           array('', '', ''),
           true,
           'checks if given username and password match');
   }

  function before_filter($name, &$args) {
      global $auth, $user, $perm;

      $auth = new Seminar_Auth();
      $auth->auth = array('uid' => 'ws',
          'uname' => 'ws',
          'perm' => 'root');

      $user = new Seminar_User();
      $user->fake_user = true;
      $user->register_globals = false;
      $user->start('ws');
      $perm = new Seminar_Perm();
      $GLOBALS['MAIL_VALIDATE_BOX'] = false;

      return parent::before_filter($name, $args);

  }


  /**
   * Create a User using a User struct as defined in the service's WSDL.
   *
   * @param string the api key.
   * @param string a partially filled User struct.
   *
   * @return string the updated User struct. At least the user's id is filled
   *                in. If the user cannot be created, a fault containing a
   *                textual representation of the error will be delivered
   *                instead.
   */
  function create_user_action($api_key, $user) {

      $user = new Studip_User($user);

      if (!$user->save())
          return new Studip_Ws_Fault(self::parse_msg_to_clean_text($user->error));

      return $user;
  }


  /**
   * Searches for a user using the user's user name.
   *
   * @param string the api key.
   * @param string the user's username.
   *
   * @return mixed the found User struct or a fault if the user could not be
   *               found.
   */
  function find_user_by_user_name_action($api_key, $user_name) {
    $user = Studip_User::find_by_user_name($user_name);

    if (!$user)
            return new Studip_Ws_Fault('No such user.');

    return $user;
  }


  /**
   * Updates a user.
   *
   * @param string the api key.
   * @param array an array representation of an user
   *
   * @return mixed the user's User struct or a fault if the user could not be
   *               updated.
   */
  function update_user_action($api_key, $user) {

    $user = new Studip_User($user);

    if (!$user->id)
            return new Studip_Ws_Fault('You have to give the user\'s id.');

    if (!$user->save())
        return new Studip_Ws_Fault(self::parse_msg_to_clean_text($user->error));

        return Studip_User::find_by_user_id($user->id);
  }


  /**
   * Deletes a user.
   *
   * @param string the api key.
   * @param string the user's username.
   *
   * @return boolean returns TRUE if deletion was successful or a fault
   *                 otherwise.
   */
  function delete_user_action($api_key, $user_name) {

    $user = Studip_User::find_by_user_name($user_name);

    if (!$user)
            return new Studip_Ws_Fault('No such user.');

    if (!$user->destroy())
        return new Studip_Ws_Fault(self::parse_msg_to_clean_text($user->error));

    return TRUE;
  }

  /**
   * check authentication for a user.
   *
   * @param string the api key.
   * @param string the user's username.
   * @param string the user's username.
   *
   * @return boolean returns TRUE if authentication was successful or a fault
   *                 otherwise.
   */
  function check_credentials_action($api_key, $username, $password) {
      list($user_id, $error_msg, $is_new_user) = array_values(StudipAuthAbstract::CheckAuthentication($username, $password));
      if($user_id === false){
          return new Studip_Ws_Fault(strip_tags($error_msg));
      } else {
          return true;
      }
  }
}
