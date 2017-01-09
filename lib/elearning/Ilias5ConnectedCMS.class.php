<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * main-class for connection to ILIAS 5.2
 *
 * This class contains the main methods of the elearning-interface to connect to ILIAS 5. Extends Ilias3ConnectedCMS.
 *
 * @author   Arne Schr�der <schroeder@data-quest.de>
 * @access   public
 * @modulegroup  elearning_interface_modules
 * @module       Ilias5ConnectedCMS
 * @package  ELearning-Interface
 */
class Ilias5ConnectedCMS extends Ilias4ConnectedCMS
{
    var $user_category_node_id;
    var $ldap_enable;
    /**
     * constructor
     *
     * init class.
     * @access public
     * @param string $cms system-type
     */
    function __construct($cms)
    {
        parent::__construct($cms);
    }
}
