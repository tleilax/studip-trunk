<?php

/**
 *
 *	@author Dennis Reil, <dennis.reil@offis.de>
 *	@version $Revision$
 *	@package pluginengine
 * @subpackage core
 *
 */

class PluginNavigation extends StudipPluginNavigation {

  var $active = FALSE;
  var $linkparams = array();


  function getLink(){
    return PluginEngine::getLink($this->plugin, $this->getLinkParams());
    }

    /**
     * Getter und Setter zu den Attributen
     */


    /**
    *
    * @return array with parameters for the link generated by the pluginengine
    */
    function getLinkParams(){
	return $this->linkparams;
    }

    /**
    * Add a new parameter for the link generation. If the key is already
    */
    function addLinkParam($key, $value){
    	$this->linkparams[$key] = $value;
    }


    function isActive() {
        foreach ($this->linkparams as $key => $val) {
            if (!isset($_REQUEST[$key]) || $_REQUEST[$key] != $val) {
                return false;
            }
        }

        return true;
    }


    /**
     * @deprecated
     */
    function setLinkParam($newlink){
	$this->addLinkParam('plugin_subnavi_params', $newlink);
    }


    /**
     * @deprecated
     */
    function setActive($value=true){
    	$this->active = $value;
    }

    /**
     * L�scht das komplette Untermen�
     */
    function clearSubmenu(){
    	$this->submenu = array();
    }
}
?>
