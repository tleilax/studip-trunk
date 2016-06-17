<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

class FilterTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp() {}
    public function tearDown() {}

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists('\Studip\Activity\Filter'));
    }

    public function test_create()
    {
        $this->assertInstanceOf("\Studip\Activity\Filter", new \Studip\Activity\Filter());
    }

    public function test_set_end_date()
    {

        $enddate = 5;

        $filter = new Studip\Activity\Filter();
        $filter->setEndDate($enddate);

        $this->assertEquals($enddate, $filter->getEndDate());
    }

    public function test_set_start_date()
    {

        $startdate = 5;

        $filter = new Studip\Activity\Filter();
        $filter->setStartDate($startdate);

        $this->assertEquals($startdate, $filter->getStartDate());
    }

    public function test_set_type()
    {
        $type = 'forum';

        $filter = new Studip\Activity\Filter();
        $filter->setType($type);
        
        $this->assertEquals($type, $filter->getType());
    }
}
