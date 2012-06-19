<?php

error_reporting(E_ALL);

/**
 * Anmeldesets - class.LimitedAdmission.php
 *
 * $Id$
 *
 * This file is part of Anmeldesets.
 *
 * Automatically generated on 31.05.2012, 15:43:29 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include AdmissionRule
 *
 * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
 */
require_once('class.AdmissionRule.php');

/* user defined includes */
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A9F-includes begin
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A9F-includes end

/* user defined constants */
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A9F-constants begin
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A9F-constants end

/**
 * Short description of class LimitedAdmission
 *
 * @access public
 * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
 */
class LimitedAdmission
    extends AdmissionRule
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute maxNumber
     *
     * @access private
     * @var Integer
     */
    private $maxNumber = null;

    // --- OPERATIONS ---

    /**
     * Short description of method LimitedAdmission
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  Integer maxNumber
     * @return LimitedAdmission
     */
    public function LimitedAdmission( Integer $maxNumber)
    {
        $returnValue = null;

        // section -124--25-73--96--2ef23cd4:136c98157e7:-8000:0000000000000B71 begin
        // section -124--25-73--96--2ef23cd4:136c98157e7:-8000:0000000000000B71 end

        return $returnValue;
    }

    /**
     * Short description of method getCustomMaxNumber
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  userId
     * @return Integer
     */
    public function getCustomMaxNumber($userId)
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000D44 begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000D44 end

        return $returnValue;
    }

    /**
     * Short description of method getMaxNumber
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @return Integer
     */
    public function getMaxNumber()
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000ABE begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000ABE end

        return $returnValue;
    }

    /**
     * Short description of method setCustomMaxNumber
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  String userId
     * @param  Integer maxNumber
     * @return LimitedAdmission
     */
    public function setCustomMaxNumber( String $userId,  Integer $maxNumber)
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000D47 begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000D47 end

        return $returnValue;
    }

    /**
     * Short description of method setMaxNumber
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  Integer newMaxNumber
     * @return LimitedAdmission
     */
    public function setMaxNumber( Integer $newMaxNumber)
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000AC0 begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000AC0 end

        return $returnValue;
    }

} /* end of class LimitedAdmission */

?>