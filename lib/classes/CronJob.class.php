<?php
/**
 *
 */
abstract class CronJob
{
    /**
     *
     */
    abstract public static function getName();

    /**
     *
     */
    abstract public static function getDescription();

    /**
     *
     */
    abstract public function execute($last_result, $parameters = array());

    /**
     *
     */
    public static function getParameters()
    {
        return array();
    }

    /**
     *
     */
    public function setUp()
    {
    }

    /**
     *
     */
    public function tearDown()
    {
    }
}
