<?php

require_once('ConnectedCMS.class.php');
require_once('LonCapaRequest.class.php');

/**
* main-class for connection to LonCapa
*
* This class contains the main methods of the elearning-interface to connect to LonCapa. Extends ConnectedCMS.
*
* @access   public
* @modulegroup  elearning_interface_modules
* @module       LonCapaConnectedCMS
* @package  ELearning-Interface
*/

class LonCapaConnectedCMS extends ConnectedCMS
{
    protected $seminarId;
    public $user;
    protected $lcRequest;
    protected $cmsUrl;

    public function __construct($cms = ""){
        parent::__construct($cms);

        $this->seminarId = Context::getId();
        $this->user = User::findCurrent();
        $this->lcRequest = new LonCapaRequest();
        $this->cmsUrl = $this->ABSOLUTE_PATH_ELEARNINGMODULES;
    }


    /**
    * search for content modules
    *
    * returns found content modules
    * @access public
    * @param string $key keyword
    * @return array list of content modules
    */

    public function searchContentModules($key){

        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->seminarId)) {
            throw new AccessDeniedException('Keine Berechtigung.');
        }

        $url = $this->cmsUrl.'/courses?search='.urlencode($key).'&owner='.urlencode($this->user->username);

        if($response = $this->lcRequest->request($url)){
            $courses = new SimpleXMLElement($response);
            $result = array();
            foreach($courses->course as $course){
                $temp = explode(':', (string)$course->owner);

                $result[] = array(
                    'ref_id' => (string)$course->id,
                    'title' => (string) $course->description,
                    'authors' => $temp[0],
                    'type' => $this->cms_type
                );

            }
        }

        return $result;
    }
}
