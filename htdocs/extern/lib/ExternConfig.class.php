<?
/**
* ExternConfig.class.php
* 
* This class is a wrapper class for configuration files.
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternConfig
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElement.class.php
// This is a wrapper class for configuration files.
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/extern_functions.inc.php");


class ExternConfig {

	var $id;
	var $config = array();
	var $module_type;
	var $module_name;
	var $config_name;
	var $range_id;
	var $file_name;

	/**
	*
	*/
	function ExternConfig ($range_id, $module_name, $config_id = "") {
		
		if ($config_id != "") {
			if ($configuration = get_configuration($range_id, $config_id)) {
				$this->id = $config_id;
				$this->module_type = $configuration["id"];
				$this->module_name = $configuration["module_name"];
				$this->config_name = $configuration["name"];
				$this->range_id = $range_id;
				$this->file_name = $config_id . ".cfg";
				$this->parse();
			}
			else
				ExternModule::printError();
		}
		else {
			$module_name = ucfirst(strtolower($module_name));
			foreach ($GLOBALS["EXTERN_MODULE_TYPES"] as $type => $module) {
				if ($module["module"] == $module_name) {
					$this->module_name = $module_name;
					$this->module_type = $type;
					$this->range_id = $range_id;
					break;
				}
			}
		}
		
	}

	/**
	*
	*/
	function getName () {
		return $this->module_name;
	}
	
	/**
	*
	*/
	function getConfigName () {
		return $this->config_name;
	}

	/**
	*
	*/
	function getType () {
		global $EXTERN_MODULE_TYPES;
		reset($EXTERN_MODULE_TYPES);
		foreach ($EXTERN_MODULE_TYPES as $key => $known_module) {
			if ($known_module["name"] == $this->module_type)
				return $key;
		}
		
		return FALSE;
	}

	/**
	*
	*/
	function getTypeName () {
		return $this->module_type;
	}

	/**
	*
	*/
	function &getConfiguration () {
		return $this->config;
	}
	
	function setConfiguration ($type, $config) {
		if ($type == "NEW") {
			$this->id = $this->makeId();
			$this->file_name = $this->id . ".cfg";
			$this->config_name = $this->createConfigName($this->range_id);
			
			// take the new configuration, write the name in the configuration
			// insert it into the database and write it to the specified path with
			// id.cfg as file name
			$this->config = $config;
			$this->setValue("Main", "name", $this->config_name);
			if (insert_config($this))
				$this->store();
			else
				ExternModule::printError();
		}
		elseif ($type == "DEFAULT") {
			$this->config = $config;
		}
		else
			ExternModule::printError();
	}
	
	/**
	*
	*/
	function getParameterNames () {
	}

	/**
	*
	*/
	
	function getAllParameterNames () {
	}

	/**
	*
	*/
	function getValue ($element_name, $attribute) {
	
		// if the first character is a pipe symbol (|) convert it
		// into an array
		$value = $this->config[$element_name][$attribute];
		
		if ($value{0} == "|")
			return explode("|", substr($this->config[$element_name][$attribute], 1));
		
		return $value;
	}

	/**
	*
	*/
	function setValue ($element_name, $attribute, $value) {
		
		// if $value is an array convert it into a string, each value is separated
		// by the pipe symbol (|) the first character of this string is |
		if (is_array($value)) {
			ksort($value, SORT_NUMERIC);
			$value = "|" . implode("|", $value);
		}
			
		$this->config[$element_name][$attribute] = htmlentities(stripslashes($value), ENT_QUOTES);
	}
	
	/**
	*
	*/
	function getAttributes ($element_name, $tag, $second_set = FALSE) {
		$attributes = FALSE;
		
		reset($this->config);
		if ($second_set) {
			foreach ($this->config[$element_name] as $tag_attribute_name => $value) {
				if ($value) {
					$tag_attribute = explode("_", $tag_attribute_name);
					if ($tag_attribute[0] == $tag && !isset($tag_attribute[2])) {
						if ($this->config[$element_name]["{$tag_attribute_name}2_"] == "")
							$attributes .= " {$tag_attribute[1]}=\"$value\"";
						else {
							$attributes .= " {$tag_attribute[1]}=\""
									. $this->config[$element_name]["{$tag_attribute_name}2_"] . "\"";
						}
					}
				}
			}
		}
		else {
			foreach ($this->config[$element_name] as $tag_attribute_name => $value) {
				if ($value) {
					$tag_attribute = explode("_", $tag_attribute_name);
					if ($tag_attribute[0] == $tag && !isset($tag_attribute[2]))
						$attributes .= " {$tag_attribute[1]}=\"$value\"";
				}
			}
		}
		
		return $attributes;
	}
	
	/**
	*
	*/
	function store ($module = "", $element_name = "", $values = "") {
		$file_content = "; Configuration file for the extern modules"
				. " $this->module_name in Stud.IP\n"
				. "; (range_id: $this->range_id)\n"
				. "; DO NOT EDIT !!!\n";
		$changed = FALSE;
		$store_own = FALSE;
		
		// store the own configuration if the function is called without parameters
		if ($values != "") {
			if ($module) {
				if ($element_name)
					$module_elements[$element_name] = $module->elements[$element_name];
				else
					$module_elements = $module->elements;
			
				reset($module_elements);
				foreach ($module_elements as $element_name => $element_obj) {
				
					$attributes = $element_obj->getAttributes();
					
					reset($attributes);
					foreach ($attributes as $attribute) {
						$form_name = $element_name . "_" . $attribute;
						
						if (isset($values[$form_name])) {
							if (is_array($values[$form_name])) {
								ksort($values[$form_name], SORT_NUMERIC); 
								$form_value = "|" . implode("|", $values[$form_name]);
								$config_tmp[$attribute] =
										htmlentities(stripslashes($form_value), ENT_QUOTES);
							}
							else {
								$config_tmp[$attribute] =
										htmlentities(stripslashes($values[$form_name]), ENT_QUOTES);
							}
						}
					}
					$this->config[$element_name] = $config_tmp;
				}
			}
		}
		
		reset($this->config);
		foreach ($this->config as $element => $attributes) {
			$file_content .= "\n[" . $element . "]\n";
			
			reset($attributes);
			foreach ($attributes as $attribute => $value)
				$file_content .= $attribute . " = \"" . $value . "\"\n";
			
		}

		if ($file = @fopen($GLOBALS["EXTERN_CONFIG_FILE_PATH"] . $this->file_name, 'w')) {
			fputs($file, $file_content);
			fclose($file);
			update_config($this->range_id, $this->id);
		}
		else
			ExternModule::printError();
		
	}
	
	/**
	*
	*/
	function parse () {
		$file_name = $GLOBALS["EXTERN_CONFIG_FILE_PATH"] . $this->file_name;
		
		if (file_exists($file_name))
			$this->config = parse_ini_file($file_name, TRUE);
		else {
			// error handling
		}
	}
	
	/**
	*
	*/
	function makeId () {
		mt_srand((double) microtime() * 1000000);
		
		return md5(uniqid(mt_rand(), 1));
	}
	
	/**
	*
	*/
	function getId () {
		return $this->id;
	}
	
	/**
	*
	*/
	function createConfigName ($range_id) {
		$configurations = get_all_configurations($range_id, $this->module_type);
		
		$config_name_prefix = "Configuration ";
		$config_name_suffix = 1;
		$config_name = $config_name_prefix . $config_name_suffix;
		$all_config_names = "";
		
		if ($configurations) {
			foreach ($configurations[$this->module_name] as $configuration)
				$all_config_names .= $configuration["name"];
		}
		
		while(stristr($all_config_names, $config_name)) {
			$config_name = $config_name_prefix . $config_name_suffix;
			$config_name_suffix++;
		}
		
		return $config_name;
	}
	
	/**
	*
	*/
	function checkFormValues ($element_name, $attributes) {
		global $HTTP_POST_VARS;
		$fault = array();
		
	/*	echo "<pre>";
		print_r($HTTP_POST_VARS);
		echo "</pre>";
	*/	
		// these two values are included in every "main"-element
		$HTTP_POST_VARS["Main_order"] = $this->config["Main"]["order"];
		$HTTP_POST_VARS["Main_visible"] = $this->config["Main"]["visible"];
		
		// Check for an alternative input field. All names of alternative input
		// fields begin with an underscore. The alternative input field overwrites
		// the input field having the same name but without the leading underscore.
		$form_values = $HTTP_POST_VARS;
		foreach ($form_values as $form_name => $value) {
			if ($form_name{0} == "_" && $value != "")
				$HTTP_POST_VARS[substr($form_name, 1)] = $value;
		}
	
		foreach ($attributes as $attribute) {
			$form_name = $element_name . "_" . $attribute;
			
			if (is_array($HTTP_POST_VARS[$form_name]))
				$value = $HTTP_POST_VARS[$form_name];
			else
				$value = array($HTTP_POST_VARS[$form_name]);
			
			$val_temp = array();
			
			$splitted_attribute = explode("_", $attribute);
			if (sizeof($splitted_attribute) == 1)
				$html_attribute = $splitted_attribute[0];
			else
				$html_attribute = $splitted_attribute[1] . $splitted_attribute[2];
			
			for ($i = 0; $i < sizeof($value); $i++) { //foreach ($value as $value[$i] {
			
				// Don't accept strings longer than 200 characters!
				if (strlen($value[$i]) > 200) {
					$fault[$form_name] = TRUE;
					break;
				}
					
				switch ($html_attribute) {
			
					case "color" :
					case "bgcolor" :
					case "bordercolor" :
						$fault[$form_name] = (preg_match("/.*(<|>|\"|(script)|\?|(php)).*/i", $value[$i]));
						break;
					case "height" :
						$fault[$form_name] = (!preg_match("/^\d{0,3}$/", $value[$i])
								|| $value[$i]> 100 || $value[$i]< 0);
						break;
					case "cellpadding" :
					case "cellspacing" :
					case "border" :
					case "sort" :
						$fault[$form_name] = (!preg_match("/^\d{0,2}$/", $value[$i])
								|| $value[$i]> 30 || $value[$i]< 0);
						break;
					case "width" :
						$fault[$form_name] = (!preg_match("/^\d{0,4}$/", $value[$i])
								|| $value[$i]> 2000 || $value[$i]< 0);
						if ($HTTP_POST_VARS["{$form_name}pp"] == "%") {
							if (is_array($HTTP_POST_VARS[$form_name]))
								$HTTP_POST_VARS[$form_name][$i] = $HTTP_POST_VARS[$form_name][$i] . "%";
							else
								$HTTP_POST_VARS[$form_name] = $HTTP_POST_VARS[$form_name] . "%";
						}
						break;
					case "valign" :
						$fault[$form_name] = !preg_match("/^(top|bottom|center)$/", $value[$i]);
						break;
					case "align" :
						$fault[$form_name] = !preg_match("/^(left|right|center)$/", $value[$i]);
						break;
					case "size" :
						$fault[$form_name] = !preg_match("/^(-|\+)*(1|2|3|4|5|6|7)$/", $value[$i]);
						break;
					case "face" :
						$fault[$form_name] = !preg_match("/^(Verdana,Arial,Helvetica,sans-serif|"
								. "Times,Times New Roman,serif|Courier,Courier New,monospace)$/", $value[$i]);
						break;
					case "aliases" :
					case "class" :
					case "style" :
					case "title" :
					case "urlcss" :
					case "nodatatext" :
						$fault[$form_name] = ($value[$i] != ""
								&& preg_match("/^.*(<|>|\"|script|\?|php).*$/i", $value[$i]));
						break;
					case "iconjpg" :
					case "icontxt" :
					case "iconpdf" :
					case "iconppt" :
					case "iconxls" :
					case "iconrtf" :
					case "iconzip" :
					case "icondefault" :
						$fault[$form_name] = ($value[$i] != ""
								&& (preg_match("/^.*(<|>|\"|script|\?|php).*$/i", $value[$i])
								|| !preg_match("/^.*\.(png|jpg|jpeg|gif)$/i", $value[$i])));
						break;
					case "wholesite" :
						// This is especially for checkbox-values. If there is no checkbox
						// checked, the variable is not declared and it is necessary to set the
						// variable to 0.
						if (!isset($HTTP_POST_VARS[$form_name])) {
							$HTTP_POST_VARS[$form_name] = 0;
							break;
						}
						$fault[$form_name] = !($value[$i] == "1" || $value[$i] == "0" || !isset($value[$i]));
						break;
					case "name" :
						$HTTP_POST_VARS[$form_name] = trim($HTTP_POST_VARS[$form_name]);
						$fault[$form_name] = (preg_match("/^.*(script|php).*$/i", $value[$i])
								|| !preg_match("/^[0-9a-z\._\- ]+$/i", $value[$i]));
						break;
					case "widthpp" :
						$fault[$form_name] = ($value[$i] != "" || $value[$i] != "%");
						break;
						
				}
					
				if ($fault[$form_name])
					break;
			}
			
		}
	
		if (in_array(TRUE, $fault))
			return $fault;
		
		return FALSE;
	}
	
}

?>
