<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipForm.class.php
// Class to build HTML formular and handle persistence using PhpLib
// 
// Copyright (c) 2003 Andr� Noack <noack@data-quest.de>
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


require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");


/**
* Class to build Studip HTML forms
*
* 
*
* @access	public	
* @author	Andr� Noack <noack@data-quest.de>
* @version	$Id$
* @package	
**/
class StudipForm {
	
	var $form_name;
	
	var $field_attributes_default = array();
	
	var $form_fields = array();
							
	var $form_buttons = array();
	
	var $persistent_values = true;
	
	var $form_values = array();
	
	var $value_changed = array();
	
	function StudipForm($form_fields, $form_buttons, $form_name = "studipform", $persistent_values = true){
		global $_REQUEST, $sess;
		
		$this->form_name = $form_name;
		$this->persistent_values = $persistent_values;
		$this->form_fields = $form_fields;
		$this->form_buttons = $form_buttons;
		
		if ($this->persistent_values && is_object($sess)){
			if (!$sess->is_registered("_" . $this->form_name . "_values")){
				$sess->register("_" . $this->form_name . "_values");
			}
			$this->form_values =& $GLOBALS["_" . $this->form_name . "_values"];
		}
		if ($this->isSended()){
			foreach ($this->form_fields as $name => $value){
				if (isset($_REQUEST[$this->form_name . "_" . $name])){
					if (is_array($_REQUEST[$this->form_name . "_" . $name])){
						foreach ($_REQUEST[$this->form_name . "_" . $name] as $key => $value){
							$new_form_values[$name][$key] = trim(stripslashes($value));
						}
					} else {
						$new_form_values[$name] = trim(stripslashes($_REQUEST[$this->form_name . "_" . $name]));
					}
				} else {
					$new_form_values[$name] = null;
				}
			}
			foreach ($this->form_fields as $name => $value){
				if ($value['type'] == 'combo'){
					if ($this->form_values[$name] != $new_form_values[$value['text']]){ //textfeld wurde ver�ndert
						$new_form_values[$name] = $new_form_values[$value['text']];
					} else if ($this->form_values[$name] != $new_form_values[$value['select']] && !$new_form_values[$value['text']]){ //textfeld nicht ge�ndert, select ge�ndert
						$new_form_values[$name] = $new_form_values[$value['select']];
					} else {
						$new_form_values[$name] = $this->form_values[$name];
					}
				}
				if ($value['type'] == 'date'){
					$new_form_values[$name] = trim(stripslashes($_REQUEST[$this->form_name . "_" . $name . "_year"])) . "-" 
											. trim(stripslashes($_REQUEST[$this->form_name . "_" . $name . "_month"])) . "-" 
											. trim(stripslashes($_REQUEST[$this->form_name . "_" . $name . "_day"]));
				}
				if (isset($this->form_values[$name]) && $this->form_values[$name] != $new_form_values[$name]){
					$this->value_changed[$name] = true;
				}
			}
			$this->form_values = array_merge($this->form_values, $new_form_values);
		}
	}
	
	function getFormField($name, $attributes = false, $default = false, $subtype = false){
		global $_REQUEST;
		if (!$attributes){
			$attributes = $this->field_attributes_default;
		}
		if (!$default){
			if (isset($this->form_values[$name])){
				$default = $this->form_values[$name];
			} else {
				$default = $this->form_fields[$name]['default_value'];
			}
		}
		if($this->form_fields[$name]['type']){
			$method = "getFormField" . $this->form_fields[$name]['type'];
			return $this->$method($name,$attributes,$default,$subtype);
		}
	}
	
	function getFormFieldText($name, $attributes, $default){
		$ret = "\n<input type=\"text\" name=\"{$this->form_name}_{$name}\" " . (($default) ? "value=\"$default\" " : "");
		$ret .= $this->getAttributes($attributes);
		$ret .= ">";
		return $ret;
	}
	
	function getFormFieldCheckbox($name, $attributes, $default){
		$ret = "\n<input type=\"checkbox\" name=\"{$this->form_name}_{$name}\" value=\"{$this->form_fields[$name]['value']}\"" . (($default) ? " checked " : "");
		$ret .= $this->getAttributes($attributes);
		$ret .= ">";
		return $ret;
	}
	
	function getFormFieldRadio($name, $attributes, $default, $subtype){
		if (is_array($this->form_fields[$name]['options'])){
			$options = $this->form_fields[$name]['options'];
		} else if ($this->form_fields[$name]['options_callback']){
			$options = call_user_func($this->form_fields[$name]['options_callback'],$this,$name);
		}
		if($subtype !== false){
			return $this->getOneRadio($name, $attributes, ($default == $options[$subtype]['value']), $subtype);
		} else {
			for ($i = 0; $i < count($options); ++$i){
				$ret .= $this->getOneRadio($name, $attributes, ($default == $options[$i]['value']), $i);
				$ret .= "\n" . $this->form_fields[$name]['separator'];
			}
		}
		return $ret;
	}
	
	function getOneRadio($name, $attributes, $default, $subtype){
		$ret = "\n<input type=\"radio\" name=\"{$this->form_name}_{$name}\" value=\"{$this->form_fields[$name]['options'][$subtype]['value']}\"" . (($default) ? " checked " : "");
		$ret .= $this->getAttributes($attributes);
		$ret .= ">";
		$ret .= $this->getFormFieldCaption($this->form_fields[$name]['options'][$subtype]['name'], $attributes);
		return $ret;
	}
	
	function getFormFieldTextarea($name, $attributes, $default){
		$ret = "\n<textarea wrap=\"virtual\"  name=\"{$this->form_name}_{$name}\" ";
		$ret .= $this->getAttributes($attributes);
		$ret .= ">";
		$ret .= $default;
		$ret .= "</textarea>";
		return $ret;
	}
	
	function getFormFieldDate($name, $attributes, $default){
		$date_values = explode("-", $default); //YYYY-MM-DD
		$ret = $this->getFormFieldText($name . "_day", array_merge(array('size'=>2,'maxlength'=>2), $attributes), $date_values[2]);
		$ret .= "\n" . $this->form_fields[$name]['separator'];
		$ret .= $this->getFormFieldText($name . "_month", array_merge(array('size'=>2,'maxlength'=>2), $attributes), $date_values[1]);
		$ret .= "\n" . $this->form_fields[$name]['separator'];
		$ret .= $this->getFormFieldText($name . "_year", array_merge(array('size'=>4,'maxlength'=>4), $attributes), $date_values[0]);
		return $ret;
	}
	
	function getFormFieldSelect($name, $attributes, $default){
		$ret = "\n<select name=\"{$this->form_name}_{$name}";
		if ($this->form_fields[$name]['multiple']){
			$ret .= "[]\" multiple ";
		} else {
			$ret .= "\" ";
		}
		$ret .= $this->getAttributes($attributes);
		$ret .= ">";
		if ($default === false){
			$default = $this->form_fields[$name]['default_value'];
		}
		if (is_array($this->form_fields[$name]['options'])){
			$options = $this->form_fields[$name]['options'];
		} else if ($this->form_fields[$name]['options_callback']){
			$options = call_user_func($this->form_fields[$name]['options_callback'],$this,$name);
		}
		for ($i = 0; $i < count($options); ++$i){
			$options_name = (is_array($options[$i])) ? $options[$i]['name'] : $options[$i];
			$options_value = (is_array($options[$i])) ? $options[$i]['value'] : $options[$i];
			$selected = false;
			if ((is_array($default) && in_array("" . $options_value, $default))
			|| (!is_array($default) && ($default == "" . $options_value))){
				$selected = true;
			}
			$ret .= "\n<option value=\"$options_value\" " . (($selected) ? " selected " : "");
			$ret .= ">$options_name</option>";
		}
		$ret .= "\n</select>";
		return $ret;
	}
	
	function getFormFieldCombo($name, $attributes, $default , $subtyp = false){
		global $_REQUEST;
		$ret = "";
		$combo_text_name = $this->form_fields[$name]['text'];
		$combo_select_name = $this->form_fields[$name]['select'];
		$select_attributes = array('onChange' => "document.{$this->form_name}.{$this->form_name}_{$combo_text_name}.value="
		."document.{$this->form_name}.{$this->form_name}_{$combo_select_name}.options[document.{$this->form_name}.{$this->form_name}_{$combo_select_name}.selectedIndex].text; ");
		if (is_array($attributes)){
			$select_attributes = array_merge($select_attributes, $attributes);
		}
		if (!$subtype){
			$ret .= "\n" . $this->getFormFieldSelect($combo_select_name, $select_attributes, $default);
			$ret .= "\n" . $this->form_fields[$name]['separator'];
			$ret .= $this->getFormFieldText($combo_text_name, $attributes, $default);
		} else if ($subtype == "text"){
			$ret .= "\n" . $this->getFormFieldText($combo_text_name, $attributes, $default);
		} else {
				$ret .= $this->getFormFieldSelect($combo_select_name, $select_attributes, $default);
		}
		return $ret;
	}
		
	function getFormButton($name, $attributes = false){
		$ret = "\n<input type=\"image\" name=\"{$this->form_name}_{$name}\" " . makeButton($this->form_buttons[$name]['type'],"src") 
			. tooltip($this->form_buttons[$name]['info']);
		$ret .= $this->getAttributes($attributes);
		$ret .= " border=\"0\">";
		return $ret;
	}
	
	function getFormFieldCaption($name, $attributes = false){
		if (isset($this->form_fields[$name]['caption'])){
			$name = $this->form_fields[$name]['caption'];
		}
		return "\n<span " . $this->getAttributes($attributes) . ">" . htmlReady($name) . "</span>";
	}
	
	function getFormFieldInfo($name){
		return "\n<img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/info.gif\""
				. tooltip($this->form_fields[$name]['info'], TRUE, TRUE) . " align=\"absmiddle\">";
	}
	
	function getFormStart($action = false, $attributes = false){
		if (!$action){
			$action = $GLOBALS['PHP_SELF'];
		}
		$ret = "\n<form action=\"$action\" method=\"post\" name=\"{$this->form_name}\" " . $this->getAttributes($attributes) . ">";
		return $ret;
	}
	
	function getFormEnd(){
		$ret = "";
		foreach ($this->form_fields as $field_name => $field_content){
			if ($field_content['type'] == 'hidden'){
				$ret .= $this->getHiddenField($field_name);
			}
		}
		$ret .= $this->getHiddenField(md5("is_sended"),1);
		return $ret . "\n</form>";
	}
	
	function getFormFieldValue($name){
		if (isset($this->form_values[$name])){
			$value = $this->form_values[$name];
		} else {
			$value = $this->form_fields[$name]['default_value'];
		}
		return $value;
	}
	
	function getHiddenField($name, $value = false){
		if (!$value){
			$value = $this->getFormFieldValue($name);
		}
		return "\n<input type=\"hidden\" name=\"{$this->form_name}_{$name}\" value=\"{$value}\">";
	}
	
	function doFormReset(){
		$this->form_values = null;
		return true;
	}
	
	function isChanged($name){
		return isset($this->value_changed[$name]);
	}
	
	function IsSended($form_name = false){
		global $_REQUEST;
		if ($form_name === false){
			$form_name = $this->form_name;
		}
		return isset($_REQUEST[$form_name . "_" . md5("is_sended")]);
	}
	
	function IsClicked($button, $form_name = false){
		global $_REQUEST;
		if ($form_name === false){
			$form_name = $this->form_name;
		}
		return isset($_REQUEST[$form_name . "_" . $button . "_x"]);
	}
	
	function getAttributes($attributes){
		$ret = "";
		if ($attributes){
			foreach($attributes as $key => $value){
				$ret .= " $key=\"$value\"";
			}
		}
		return $ret;
	}
	
	
}

// test & demo
/*
function getSomeOptions(&$caller, $name){
	$options[] = md5($name);
	foreach($caller->form_fields as $key => $value){
		$options[]=$key;
	}
	return $options;
}

page_open(array("sess" => "Seminar_Session"));
$_language = $DEFAULT_LANGUAGE;
$_language_path = $INSTALLED_LANGUAGES[$_language]["path"];

$form_fields = array('text1'		=> 	array('type' => 'text', 'caption' => 'Testtextfeld1', 'info' => 'Hier Schwachsinn eingeben'),
					'text2'			=> 	array('type' => 'textarea','caption' => 'Testtextfeld2', 'info' => 'Hier Schwachsinn eingeben','default' => 'blablubb'),
					'select1'		=> 	array('type' => 'select', 'options' => array(	array('name' =>_("UND"),'value' => 'AND'),
																						array('name' =>_("ODER"),'value' => 'OR'))),
					'select2'		=>	array('type' => 'select','options_callback' => 'getSomeOptions'),
					'combo1_text'	=>	array('type' => 'text'),
					'combo1_select'	=>	array('type' => 'select', 'options' => array("",_("Eins"),_("Zwei"), _("Drei"))),
					'combo1' 		=>	array('type' => 'combo', 'text' => 'combo1_text', 'select' => 'combo1_select', 'separator' => '--'),
					'date1' 		=>	array('type' => 'date',  'separator' => '.', 'default' => 'YYYY-MM-DD'),
					'checkbox'		=> array('type' => 'checkbox', 'caption' => 'Tolle Checkbox ?', value => '1'),
					'radio_group'	=> array('type' => 'radio', 'separator' => "&nbsp;", 'options' => array(	array('name' =>_("UND"),'value' => 'AND'),
																						array('name' =>_("ODER"),'value' => 'OR'),
																						array('name' =>_("NICHT"),'value' => 'NOT')))
					);
							
$form_buttons = array('send' => array('type' => 'abschicken', 'info' => _("Dieses Formular abschicken")),
					'not_send' => array('type' => 'abbrechen', 'info' => _("Eingabe abbrechen")));
		
$test = new StudipForm($form_fields, $form_buttons);
echo "<table width='400'><tr><td>";
echo $test->getFormStart();
echo $test->getFormFieldCaption("text1");
echo "&nbsp;" . $test->getFormFieldInfo("text1") . "&nbsp;";
echo $test->getFormField("text1");
echo $test->getFormField("text2");
echo $test->getFormFieldCaption("select1");
echo "&nbsp;" . $test->getFormFieldInfo("select1") . "&nbsp;";
echo $test->getFormField("select1");
echo $test->getFormFieldCaption("select2");
echo "&nbsp;" . $test->getFormFieldInfo("select2") . "&nbsp;";
echo $test->getFormField("select2");
echo $test->getFormField("date1", array('style' => 'vertical-align:middle'));
echo "<br>" . $test->getFormField("combo1",array('style' => 'vertical-align:middle'));
echo $test->getFormFieldCaption("checkbox", array('style' => 'vertical-align:middle'));
echo "&nbsp;" . $test->getFormField("checkbox",array('style' => 'vertical-align:middle'));
echo "<br>" . $test->getFormField("radio_group",array('style' => 'vertical-align:middle;font-size:10pt;'));
echo $test->getFormButton("send",array('style' => 'vertical-align:middle;'));
echo $test->getFormEnd();
echo "</td></tr></table>";
echo "<pre>";
page_close();
*/
?>
