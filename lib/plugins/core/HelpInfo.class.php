<?php
# Lifter002: TODO
# Lifter007: TODO

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class HelpInfo {

	var $helppagename; // relativer Pfad zur Hilfeseite

    function HelpInfo() {
    	$this->helppagename = "";
    }

    /**
     * GETTER UND SETTER f�r die Attribute
     */

    function getHelppagename(){
    	return $this->helppagename;
    }

    function setHelppagename($newfile){
    	$this->helppagename = $newfile;
    }
}
?>
