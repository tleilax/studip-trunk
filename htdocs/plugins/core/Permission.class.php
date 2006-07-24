<?php

/**
* The permission of an object, usually a user.
* @author Dennis Reil, <dennis.reil@offis.de>
* @version $Revision$
* @package pluginengine
* $Id$
*/

class Permission {
	var $permissionid;
	var $userid;
	var $perm;
	
	
    function Permission($userid="") {
	    $this->permissionid = "guest";
		$this->userid = $userid;    
		$this->perm = new Seminar_Perm();	
    }
    
    function hasRootPermission(){
    	return $this->perm->have_perm("root",$this->userid);
	}    
	
	function hasAdminPermission(){  	
    	return $this->perm->have_perm("admin",$this->userid);
	}   
	
	function hasTutorPermission(){	
    	return $this->perm->have_perm("tutor",$this->userid);	 
	}
	
	function hasTeacherPermission(){			
    	return $this->perm->have_perm("dozent",$this->userid);	 
	}	
	
	function hasStudentPermission(){		
    	return $this->perm->have_perm("autor",$this->userid);	 
	}
	
	function isStudent(){		
    	return $this->perm->have_perm("autor",$this->userid) && !$this->perm->have_perm("dozent",$this->userid);	 
	}
	
	function hasTeacherPermissionInPOI(){		
		return $this->perm->have_studip_perm("dozent",$GLOBALS["SessSemName"][1],$this->userid);
	}
	
	function hasTutorPermissionInPOI(){		
		return $this->perm->have_studip_perm("tutor",$GLOBALS["SessSemName"][1],$this->userid);
	}
	
	function hasStudentPermissionInPOI(){		
		return $this->perm->have_studip_perm("autor",$GLOBALS["SessSemName"][1],$this->userid);
	}
}
?>