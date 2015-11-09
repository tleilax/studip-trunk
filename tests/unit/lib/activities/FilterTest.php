<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Gl�ggler <tgloeggl@uos.de>
 * @author      Andr� Kla�en <klassen@elan-ev.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

class FilterTestCase extends PHPUnit_Framework_TestCase
{
    function setUp() {}
    function tearDown() {}

    function test_class_should_exist()
    {
        $this->assertTrue(class_exists('\Studip\Activity\Filter'));
    }

    function test_create()
    {
        $this->assertInstanceOf("\Studip\Activity\Filter", new \Studip\Activity\Filter());
    }

    function test_set_max_age()
    {
        $age = 5;

        $filter = new Studip\Activity\Filter();
        $filter->setMaxAge($age);

        $this->assertEquals($age, $filter->getMaxAge());
    }

    function test_set_type()
    {
        $type = 'forum';

        $filter = new Studip\Activity\Filter();
        $filter->setType($type);
        
        $this->assertEquals($type, $filter->getType());
    }
}
