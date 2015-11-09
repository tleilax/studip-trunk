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

class ActivityTestCase extends PHPUnit_Framework_TestCase {


    function setUp() {
    }


    function tearDown() {
    }


    function test_class_should_exist()
    {
        $this->assertTrue(class_exists('\Studip\Activity\Activity'));
    }

    function test_create()
    {
        $this->assertInstanceOf("\Studip\Activity\Activity", new \Studip\Activity\Activity());
    }

    function test_set_description()
    {
        $text = 'Individuum X hat einen Beitrag im Forum geschrieben';

        $activity = new \Studip\Activity\Activity();
        $activity->setDescription($text);

        $this->assertEquals($text, (string)$activity);
        $this->assertEquals($text, $activity->getDescription());
    }

    function test_set_actor_user()
    {
        $user_id = 5;

        // user who created the activity
        $activity = new \Studip\Activity\Activity();
        $activity->setActor('user', $user_id);

        $expected_output = array('objectType' => 'user', 'id' => $user_id);
        $this->assertEquals($expected_output, $activity->getActor());
    }

    function test_set_actor_system()
    {
        $activity = new \Studip\Activity\Activity();
        $activity->setActor('system', 'system');

        $expected_output = array('objectType' => 'system', 'id' => 'system');
        $this->assertEquals($expected_output, $activity->getActor());
    }

    function test_set_valid_verbs()
    {
        // verb that describes the activity: https://github.com/adlnet/tin-can-verbs/tree/master/verbs
        
        $allowed_verbs = array(
            'answered',
            'attempted',
            'attended',
            'completed',
            'created',
            'experienced',
            'failed',
            'imported',
            'interacted',
            'passed',
            'shared',
            'voided'
        );

        $activity = new \Studip\Activity\Activity();

        foreach ($allowed_verbs as $verb) {
            $activity->setVerb($verb);

            $this->assertEquals($verb, $activity->getVerb());
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function test_invalid_verb()
    {
        $activity = new \Studip\Activity\Activity();

        $activity->setVerb('does_not_exist');
    }

    function test_set_object()
    {
        // the object of the action, eg. a posting, a photo, a file
        $activity = new \Studip\Activity\Activity();

        // url is the Stud.IP-URL to the object, route is the REST-API-Route to the object
        $object_type = 'posting';
        $url         = 'http://example.com/dispatch.php/forum/posting/1234';
        $route       = 'http://example.com/api/forum/posting/1234';

        $activity->setObject($object_type, $url, $route);

        $expected_output = array('objectType' => $object_type, 'url' => $url, 'route' => $route);
        $this->assertEquals($expected_output, $activity->getObject());
    }
    
    function test_set_provider()
    {
        // wiki, forum, files, ...
        $activity = new \Studip\Activity\Activity();

        $provider = 'forum';
        $activity->setProvider($provider);

        $this->assertEquals($provider, $activity->getProvider());
    }

    function test_check_mkdate() {
        $date = time();
        $activity = new \Studip\Activity\Activity();

        $this->assertGreaterThanOrEqual($date, $activity->getMkdate());
        $this->assertLessThanOrEqual(($date + 2), $activity->getMkdate());
    }
}