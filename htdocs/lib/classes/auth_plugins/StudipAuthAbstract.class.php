<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipAuthAbstract.class.php
// Abstract class, used as a template for authentication plugins
// 
// Copyright (c) 2003 Andr� Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/DbView.class.php");
require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/DbSnapshot.class.php");

			

/**
* abstract base class for authentication plugins
*
* abstract base class for authentication plugins
* to write your own authentication plugin, derive it from this class and 
* implement the following abstract methods: isUsedUsername($username) and 
* isAuthenticated($username, $password, $jscript)
* don't forget to call the parents constructor if you implement your own, php
* won't do that for you !
*
* @abstract
* @access	public
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipAuthAbstract {
	
	/**
	* indicates whether login form should use md5 challenge response auth
	*
	* this should only be true, if password is stored and accessible as md5 hash !
	* should be set in local.inc
	*
	* @access	public
	* @var		bool
	*/
	var $md5_challenge_response = false;
	
	/**
	*  md5 challenge sent by crcloginform
	*
	* 
	*
	* @access	private
	* @var		string
	*/
	var $challenge;
	
	
	/**
	* contains error message, if authentication fails
	*
	* 
	* @access	public
	* @var		string
	*/
	var $error_msg;
	
	/**
	* indicates whether the authenticated user logs in for the first time
	*
	* 
	* @access	public
	* @var		bool
	*/
	var $is_new_user = false;
	
	/**
	* associative array with mapping for database fields
	*
	* associative array with mapping for database fields,
	* should be set in local.inc
	* structure :
	* array("<table name>.<field name>" => array(	"callback" => "<name of callback method used for data retrieval>",
	*												"map_args" => "<arguments passed to callback method>"))
	* @access	public
	* @var		array $user_data_mapping
	*/
	var $user_data_mapping = null;
	
	/**
	* database connection
	*
	* database connection to the Stud.IP DB
	*
	* @access	public
	* @var		object DbView
	*/
	var $dbv;
	
	/**
	* name of the plugin 
	*
	* name of the plugin (last part of class name) is set in the constructor
	* @access	public
	* @var		string
	*/
	var $plugin_name;
	
	
	/**
	* static method to instantiate and retrieve a reference to an object (singleton)
	*
	* use always this method to instantiate a plugin object, it will ensure that only one object of each
	* plugin will exist
	* @access public
	* @static
	* @param	string	name of plugin, if omitted an array with all plugin objects will be returned
	* @return	mixed	either a reference to the plugin with the passed name, or an array with references to all plugins
	*/
	
	function &GetInstance( $plugin_name = false){
		static $plugin_instance;	//container to hold the plugin objects
		if (!is_array($plugin_instance)){
			foreach($GLOBALS['STUDIP_AUTH_PLUGIN'] as $plugin){
				$plugin = "StudipAuth" . $plugin;
				include_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/auth_plugins/" . $plugin . ".class.php";
				$plugin_instance[strtoupper($plugin)] = new $plugin;
			}
		}
		return ($plugin_name) ? $plugin_instance[strtoupper("StudipAuth" . $plugin_name)] : $plugin_instance;
	}
	
	/**
	* static method to check in all plugins, if md5 challenge/response is allowed
	*
	* if md5 challenge/response is disabled in one plugin, false is returned
	*
	* @access public
	* @static
	* @return	bool
	*/
	function CheckMD5(){
		$plugins =& StudipAuthAbstract::GetInstance(); //get a reference to the plugin container
		foreach($plugins as $object){
			if (!$object->md5_challenge_response){
				return false;
			}
		}
		return true;
	}
	
	/**
	* static method to check authentication in all plugins
	*
	* if authentication fails in one plugin, the error message is stored and the next plugin is used
	* if authentication succeeds, the uid element in the returned array will contain the Stud.IP user id
	*
	* @access public
	* @static
	* @param	string	the username to check
	* @param	string	the password to check
	* @param	bool	indicates if javascript was enabled/disabled during the login process
	* @return	array	structure: array('uid'=>'string <Stud.IP user id>','error'=>'string <error message>','is_new_user'=>'bool')
	*/
	function CheckAuthentication($username,$password,$jscript = false){
		$plugins =& StudipAuthAbstract::GetInstance();
		$error = false;
		$uid = false;
		foreach($plugins as $object){
			if ($uid = $object->authenticateUser($username,$password,$jscript)){
				return array('uid' => $uid,'error' => $error, 'is_new_user' => $object->is_new_user);
			} else {
				$error .= $object->plugin_name . ": " . $object->error_msg . "<br>";
			}
		}
		return array('uid' => $uid,'error' => $error);
	}
	
	/**
	* static method to check if passed username is used in external data sources
	*
	* all plugins are checked, the error messages are stored and returned
	*
	* @access public
	* @static
	* @param	string the username
	* @return	array	
	*/
	function CheckUsername($username){
		$plugins =& StudipAuthAbstract::GetInstance();
		$error = false;
		$found = false;
		foreach($plugins as $object){
			if ($found = $object->isUsedUsername($username)){
				return array('found' => $found,'error' => $error);
			} else {
				$error .= "<b>" . $object->plugin_name . "</b>: " . $object->error_msg . "<br>";
			}
		}
		return array('found' => $found,'error' => $error);
	}
	/**
	* static method to check for a mapped field
	*
	* this method checks in the plugin with the passed name, if the passed 
	* Stud.IP DB field is mapped to an external data source
	*
	* @access public
	* @static
	* @param	string	the name of the db field must be in form '<table name>.<field name>'
	* @param	string	the name of the plugin to check
	* @return	bool	true if the field is mapped, else false
	*/
	function CheckField($field_name,$plugin_name){
		if (!$plugin_name){
			return false;
		}
		$plugin =& StudipAuthAbstract::GetInstance($plugin_name);
		return (is_object($plugin) ? $plugin->isMappedField($field_name) : false);
	}
	
	
	/**
	* Constructor
	*
	* the constructor is private, you should use StudipAuthAbstract::GetInstance($plugin_name)
	* to get a reference to a plugin object. Make sure the constructor in the base class is called
	* when deriving your own plugin class, it assigns the settings from local.inc as members of the plugin
	* each key of the $STUDIP_AUTH_CONFIG_<plugin name> array will become a member of the object
	* 
	* @access private
	* 
	*/
	function StudipAuthAbstract() {
		$this->plugin_name = substr(get_class($this),10);
		//get configuration array set in local inc
		$config_var = $GLOBALS["STUDIP_AUTH_CONFIG_" . strtoupper($this->plugin_name)];
		//assign each key in the config array as a member of the plugin object
		if (isset($config_var)){
			foreach ($config_var as $key => $value){
				$this->$key = $value;
			}
		}
		$this->dbv = new DbView();
		//$challenge is a global set by PhpLib, contains a random md5 hash which is sent to the client and used as seed
		$this->challenge = $GLOBALS['challenge'];
	}
	
	/**
	* authentication method
	*
	* this method authenticates the passed username, it is used by StudipAuthAbstract::CheckAuthentication()
	* if authentication succeeds it calls StudipAuthAbstract::doDataMapping() to map data fields
	* if the authenticated user logs in for the first time it calls StudipAuthAbstract::doNewUserInit() to
	* initialize the new user
	* @access private
	* @param	string	the username to check
	* @param	string	the password to check
	* @param	bool	indicates if javascript was enabled/disabled during the login process
	* @return	string	if authentication succeeds the Stud.IP user id, else false
	*/
	function authenticateUser($username, $password, $jscript = false){
		if ($this->isAuthenticated($username, $password, $jscript)){
			$uid = $this->getStudipUserid($username);
			$this->doDataMapping($uid);
			if ($this->is_new_user){
				$this->doNewUserInit($uid);
			}
			return $uid;
		} else {
			return false;
		}
	}
	
	/**
	* method to retrieve the Stud.IP user id to a given username
	*
	* 
	* @access	private
	* @param	string	the username
	* @return	string	the Stud.IP user id or false if an error occurs
	*/
	function getStudipUserid($username){
		$this->dbv->params[] = $username;
		$db = $this->dbv->get_query("view:AUTH_USER_UNAME");
		if ($db->next_record()){
			$auth_plugin = is_null($db->f("auth_plugin")) ? "standard" : $db->f("auth_plugin");
			if ($auth_plugin != $this->plugin_name){
				$this->error_msg = sprintf(_("Dieser Username wird bereits �ber %s authentifiziert!"),$auth_plugin) . "<br>";
				return false;
			}
			$uid = $db->f("user_id");
			return $uid;
		}
		$uid = md5(uniqid($username,1));
		$this->dbv->params = array($uid,mysql_escape_string($username),"autor","","","","",$this->plugin_name);
		$db = $this->dbv->get_query("view:AUTH_USER_INSERT");
		$this->dbv->params = array($uid,time(),time(),$GLOBALS['_language']);
		$db = $this->dbv->get_query("view:USER_INFO_INSERT");
		$this->is_new_user = true;
		return $uid;
	}
	
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function doNewUserInit($uid){
		include ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/config.inc.php");
		$permlist = array('autor','tutor','dozent');
		$this->dbv->params[] = $uid;
		$db = $this->dbv->get_query("view:AUTH_USER_UID");
		$db->next_record();
		if (in_array($db->f("perms"), $permlist)){
			if (is_array($AUTO_INSERT_SEM)){
				foreach ($AUTO_INSERT_SEM as $sem_id) {
					$this->dbv->params = array($sem_id, $uid, 'autor', 0);
					$db = $this->dbv->get_query("view:SEM_USER_INSERT");
				}
			}
		return true;
		}
		return false;
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function doDataMapping($uid){
		if (is_array($this->user_data_mapping)){
			foreach($this->user_data_mapping as $key => $value){
				if (method_exists($this, $value['callback'])){
					$split = explode(".",$key);
					$table = $split[0];
					$field = $split[1];
					$mapped_value = call_user_method($value['callback'],$this,$value['map_args']);
					$this->dbv->params = array($table,$field,mysql_escape_string($mapped_value),$uid);
					$db = $this->dbv->get_query("view:GENERIC_UPDATE");
				}
			}
			return true;
		}
		return false;
	}
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function isMappedField($name){
		return isset($this->user_data_mapping[$name]);
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function isUsedUsername($username){
		$this->error = sprintf(_("Methode %s nicht implementiert!"),get_class($this) . "::isUsedUsername()");
		return false;
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function isAuthenticated($username, $password, $jscript){
		$this->error = sprintf(_("Methode %s nicht implementiert!"),get_class($this) . "::isAuthenticated()");
		return false;
	}
	
	
}
?>
