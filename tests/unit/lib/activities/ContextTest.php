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

class ContextTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp() {}
    public function tearDown() {}

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists('\Studip\Activity\CourseContext'));
        $this->assertTrue(class_exists('\Studip\Activity\InstituteContext'));
        $this->assertTrue(class_exists('\Studip\Activity\UserContext'));
        $this->assertTrue(class_exists('\Studip\Activity\SystemContext'));
    }

    public function test_create()
    {
        $this->assertInstanceOf("\Studip\Activity\CourseContext",    new \Studip\Activity\CourseContext('1234'));
        $this->assertInstanceOf("\Studip\Activity\InstituteContext", new \Studip\Activity\InstituteContext('1234'));
        $this->assertInstanceOf("\Studip\Activity\UserContext",      new \Studip\Activity\UserContext('4321'));
        $this->assertInstanceOf("\Studip\Activity\SystemContext",    new \Studip\Activity\SystemContext());
    }

    public function test_course_context()
    {
        $seminar_id = 'seminar_id';
        $context = new Studip\Activity\CourseContext($seminar_id);

        $this->assertEquals($seminar_id, $context->getRangeId());

        $this->assertTrue(
            method_exists($context, 'getActivities'),
            'Class CourseContext has no method getActivities'
        );

        $this->assertTrue(
            $context instanceof Studip\Activity\Context,
            'Class CourseContext does not implement Studip\Activity\ActivityContext'
        );
    }

    public function test_institute_context()
    {
        $inst_id = 'inst_id';
        $context = new Studip\Activity\InstituteContext($inst_id);

        $this->assertEquals($inst_id, $context->getRangeId());

        $this->assertTrue(
            method_exists($context, 'getActivities'),
            'Class InstituteContext has no method getActivities'
        );

        $this->assertTrue(
            $context instanceof Studip\Activity\Context,
            'Class InstituteContext does not implement Studip\Activity\ActivityContext'
        );
    }

    public function test_user_context()
    {
        $user_id = 'user_id';
        $context = new Studip\Activity\UserContext($user_id);

        $this->assertEquals($user_id, $context->getRangeId());

        $this->assertTrue(
            method_exists($context, 'getActivities'),
            'Class UserContext has no method getActivities'
        );

        $this->assertTrue(
            $context instanceof Studip\Activity\Context,
            'Class UserContext does not implement Studip\Activity\ActivityContext'
        );
    }

    public function test_system_context()
    {
        $context = new Studip\Activity\SystemContext();

        $this->assertTrue(
            method_exists($context, 'getActivities'),
            'Class SystemContext has no method getActivities'
        );

        $this->assertTrue(
            $context instanceof Studip\Activity\Context,
            'Class SystemContext does not implement Studip\Activity\ActivityContext'
        );
    }
}