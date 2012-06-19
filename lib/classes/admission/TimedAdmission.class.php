<?php

error_reporting(E_ALL);

/**
 * Anmeldesets - class.TimedAdmission.php
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
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A7A-includes begin
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A7A-includes end

/* user defined constants */
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A7A-constants begin
// section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A7A-constants end

/**
 * Short description of class TimedAdmission
 *
 * @access public
 * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
 */
class TimedAdmission
    extends AdmissionRule
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute distributionTime
     *
     * @access public
     * @var Integer
     */
    public $distributionTime = null;

    /**
     * Short description of attribute endTime
     *
     * @access private
     * @var Integer
     */
    private $endTime = null;

    /**
     * Short description of attribute startTime
     *
     * @access private
     * @var Integer
     */
    private $startTime = null;

    // --- OPERATIONS ---

    /**
     * Short description of method TimedAdmission
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  Integer startTime
     * @param  Integer endTime
     * @return TimedAdmission
     */
    public function TimedAdmission( Integer $startTime,  Integer $endTime)
    {
        $returnValue = null;

        // section -124--25-73--96--2ef23cd4:136c98157e7:-8000:0000000000000B6B begin
        // section -124--25-73--96--2ef23cd4:136c98157e7:-8000:0000000000000B6B end

        return $returnValue;
    }

    /**
     * Short description of method getDistributionTime
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @return Integer
     */
    public function getDistributionTime()
    {
        $returnValue = null;

        // section -124--25-73--96-2d356ee:137168bd783:-8000:0000000000000BEC begin
        // section -124--25-73--96-2d356ee:137168bd783:-8000:0000000000000BEC end

        return $returnValue;
    }

    /**
     * Short description of method getEndTime
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @return Integer
     */
    public function getEndTime()
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A8F begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A8F end

        return $returnValue;
    }

    /**
     * Short description of method getStartTime
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @return Integer
     */
    public function getStartTime()
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A8D begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A8D end

        return $returnValue;
    }

    /**
     * Short description of method setDistributionTime
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  Integer newDistributionTime
     * @return TimedAdmission
     */
    public function setDistributionTime( Integer $newDistributionTime)
    {
        $returnValue = null;

        // section -124--25-73--96-2d356ee:137168bd783:-8000:0000000000000BEE begin
        // section -124--25-73--96-2d356ee:137168bd783:-8000:0000000000000BEE end

        return $returnValue;
    }

    /**
     * Short description of method setEndTime
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  Integer newEndTime
     * @return TimedAdmission
     */
    public function setEndTime( Integer $newEndTime)
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A94 begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A94 end

        return $returnValue;
    }

    /**
     * Short description of method setStartTime
     *
     * @access public
     * @author Thomas Hackl, <thomas.hackl@uni-passau.de>
     * @param  Integer newStartTime
     * @return TimedAdmission
     */
    public function setStartTime( Integer $newStartTime)
    {
        $returnValue = null;

        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A91 begin
        // section -124--25-73--96-6de7c8e2:136b9d49c42:-8000:0000000000000A91 end

        return $returnValue;
    }

} /* end of class TimedAdmission */

?>