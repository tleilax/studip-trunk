<?
# Lifter002: TODO

// Jetzt ist ein Element nur noch eine kleine Werteklasse :( 
// naja etwas überzogen für ein paar Werte und eine Check-Methode, aber was solls _Maik
define('LANGUAGE_ID',"09c438e63455e3e1b3deabe65fdbc087");

require_once ("lib/functions.php");

class  AbstractStmElement {
	
	var $element_id;
	var $element_type_id;
	var $sws;
	var $workload;
	var $semester;
	var $stm_abstr_id;
	var $custom_name;
	var $elementgroup;
	var $position;
	var $msg;

	
/*	function &Search($custom_name, $sws, $workload, $semester, $element_type_id){
		$search_mask = "custom_name LIKE '%$custom_name%'";
		$search_mask .= (($sws == '')? '' : " AND sws = $sws");
		$search_mask .= (($workload == '')? '' : " AND workload = $workload");
		$search_mask .= (($semester == '-1')? '' : " AND semester = $semester");
		$search_mask .= (($element_type_id == '-1')? '' : " AND element_type_id = '$element_type_id'");

		$db = new DB_Seminar;	
		$result = $db->query("SELECT * FROM stm_abstract_elements WHERE $search_mask ORDER BY custom_name");		

		if ($db->ErrorNo() !=0)
			return $db->ErrorMsg();
		
		$res_array = array();
		
		while(!$result->EOF) {
			$res_array [] = $result->f;
			$result->MoveNext();
		}
		
		return $res_array;

	}
*/		
	function &GetInstance($id = false, $refresh_cache = false){
		
		static $abs_elements_object_pool;
		
		if ($id){
			if ($refresh_cache){
				$abs_elements_object_pool[$id] = null;
			}
			if (is_object($abs_elements_object_pool[$id]) && $abs_elements_object_pool[$id]->getId() == $id){
				return $abs_elements_object_pool[$id];
			} else {
				$abs_elements_object_pool[$id] = new AbstractStmElement($id);
				return $abs_elements_object_pool[$id];
			}
		} else {
			return new AbstractStmElement(false);
		}
	}
		
	function &GetStmElementTypes()
	{
		static $stm_element_types;

		$db = new DB_Seminar;	
		$stm_element_types = array();	
		$db->query("SELECT * FROM stm_element_types WHERE lang_id='".LANGUAGE_ID."' ORDER BY name");		
		if (!$db->num_rows()) 
			return array();
		while ($db->next_record()) {
			$stm_element_types[$db->f("element_type_id")] = array('name' => $db->f("name"), 'abbrev' => $db->f("abbrev"));	
		}
		
		return $stm_element_types;
	}

	function &AddElementType($name, $abbrev)
	{
		// pruefen, ob der Name schon existiert
		$db = new DB_Seminar;	
		$db->query("SELECT * FROM stm_element_types WHERE lang_id='".LANGUAGE_ID."' AND name='". $name ."'");		

		if ($db->num_rows())
			return array('error', _('Eine Lehr- und Lernform mit diesem Namen existiert bereits'));
		
		$db->query("INSERT INTO stm_element_types VALUES ('" . md5(uniqid("NewElementType",1)) . "','" . LANGUAGE_ID  . "','" .  $abbrev  . "','" .  $name  . "')");	

		if ($db->affected_rows())
			return array('msg', _('Die neue Lehr- und Lernform wurde angelegt'));
	}
	
	/**
	* Constructor
	*
	* Pass nothing to create a abstract stm, or the id from an existing stm to change or delete
	* @access	public
	* @param	string	$abs_stm_id	the seminar which should be retrieved
	*/
	function AbstractStmElement($id = false) {
		if ($id) {
			$this->element_id = $id;
			$this->restore();
		}
		if (!$this->element_id) {
			$this->element_id=$this->createId();
		}
	}

	/**
	*
	* creates an new id for this object
	* @access	private
	* @return	string	the unique id
	*/
	function createId() {
		return md5(uniqid("AbstractStm",1));
	}
	
	function getId() {
		return $this->element_id;
	}

	function getSws() {
		return $this->sws;
	}
	
	function getWorkload() {
		return $this->workload;
	}

	function getSemester() {
		return $this->semester;
	}

	function getElementTypeId() {
		return $this->element_type_id;
	}

	function getAbstractStmId() {
		return $this->stm_abstr_id;
	}

	function getName() {
		return $this->custom_name;
	}

	function getGroup() {
		return $this->elementgroup;
	}

	function getPosition() {
		return $this->position;
	}

	function setValues($val_array) {
		foreach($val_array as $name => $value) {
			$this->$name = $value;
		}
	}

	function restore() {
	
		$db = new DB_Seminar;	
		
		$db->query("SELECT stm_abstr_id, element_type_id, sws, workload, semester, elementgroup, position, custom_name FROM stm_abstract_elements WHERE element_id='" . $this->element_id ."'");

		if ($db->next_record()) {
			$vals = array(
				'stm_abstr_id' => $db->f('stm_abstr_id'),
				'element_type_id' => $db->f('element_type_id'), 
				'sws' => $db->f('sws'), 
				'workload' => $db->f('workload'), 
				'semester' => $db->f('semester'), 
				'elementgroup' => $db->f('elementgroup'), 
				'position' => $db->f('position'), 
				'custom_name' => $db->f('custom_name'));
			$this->setValues($vals);
			return TRUE;
		}
		return FALSE;
	}
	
	function store() {
		$db = new DB_Seminar;	

		$db->query("INSERT INTO stm_abstract_elements (stm_abstr_id, element_id , element_type_id , sws ,	workload , semester , elementgroup, position) 
		VALUES ('$this->stm_abstr_id','$this->element_id', '$this->element_type_id', '$this->sws', '$this->workload', '$this->semester', '$this->elementgroup', '$this->position')");

		if (!$db->affected_rows())
			$this->msg[] = array('error', "DB-Error beim Anlegen einer Kombination: %s");
	}

	function delete() {
		$db = new DB_Seminar;	

		$db->query("DELETE FROM stm_abstract_elements WHERE element_type_id = $this->element_id");
		if (!$db->affected_rows())
			$this->msg[] = array('error', "DB-Error beim Entfernen einer Kombination");
	}
		
	function getValues() {
		return array( 	'stm_abstr_id' => $this->stm_abstr_id,
						'element_id' => $this->element_id,
						'element_type_id' => $this->element_type_id,
						'sws' => $this->sws,
						'workload' => $this->workload,
						'semester' => $this->semester,
						'elementgroup' => $this->elementgroup,
						'position' => $this->position,
						'custom_name' => $this->custom_name
				);
	
	}
		
	function checkValues() {
		$required = array('element_type_id','sws','workload', 'semester');
		
		foreach($required as $name){
			if (!isset($this->$name) || $this->$name == '') {
				$msg[] = array('error', sprintf(_("Es wurden nicht alle notwendigen Felder ausgef&uuml;llt")));
				break;
			}
		}
		
		return $msg;
	}
}
?>
