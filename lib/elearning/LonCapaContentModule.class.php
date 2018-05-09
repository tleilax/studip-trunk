<?php

require_once("ContentModule.class.php");
require_once('LonCapaRequest.class.php');
/**
*
* This class contains methods to handle LonCapa learning modules
*
* @access   public
* @modulegroup  elearning_interface_modules
* @module       LonCapaContentModule
* @package  ELearning-Interface
*/

class LonCapaContentModule extends ContentModule
{
    public $lcRequest;
    public $cmsUrl;

    public function __construct($module_id = "", $module_type, $cms_type){
        $this->lcRequest = new LonCapaRequest();
        $this->cmsUrl = $GLOBALS['ELEARNING_INTERFACE_MODULES'][$cms_type]['ABSOLUTE_PATH_ELEARNINGMODULES'];

        parent::__construct($module_id, $module_type, $cms_type);
    }

    /**
    * reads data for content module
    */
    public function readData(){
        $url = $this->cmsUrl.'/course/'.urlencode($this->id);

        if($response = $this->lcRequest->request($url)){

            $courses = new SimpleXMLElement($response);
            $course = $courses->course[0];

            list($author, $dummy) = explode(':', (string)$course->owner);

            $this->id = (string)$course->id;
            $this->title = (string) $course->description;
            $this->authors = $author;

        }

    }

    /**
    * get permission-status
    *
    * returns true, if operation is allowed
    * @access public
    * @param string $operation operation
    * @return boolean allowed
    */
    public function isAllowed($operation)
    {
        return true;
    }

    function setConnection($seminar_id){
        $this->is_connected = true;
        return ObjectConnections::setConnection($seminar_id, $this->id, $this->module_type, $this->cms_type);
    }
}
