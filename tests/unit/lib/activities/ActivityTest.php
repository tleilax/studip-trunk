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

class ActivityTestCase extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        StudipTestHelper::set_up_tables(array('activities'));
    }

    public function tearDown()
    {
        StudipTestHelper::tear_down_tables();
    }

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists('\Studip\Activity\Activity'));
    }

    public function test_create()
    {
        $this->assertInstanceOf("\Studip\Activity\Activity", new \Studip\Activity\Activity());
    }

    public function test_set_valid_verbs()
    {
        // verb that describes the activity: https://github.com/adlnet/tin-can-verbs/tree/master/verbs

        $allowed_verbs = array(
            'answered',
            'attempted',
            'attended',
            'completed',
            'created',
            'deleted',
            'edited',
            'experienced',
            'failed',
            'imported',
            'interacted',
            'passed',
            'shared',
            'sent',
            'voided'
        );

        $activity = new \Studip\Activity\Activity();

        foreach ($allowed_verbs as $verb) {
            $activity->setVerb($verb);
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_invalid_verb()
    {
        $activity = new \Studip\Activity\Activity();

        $activity->setVerb('does_not_exist');
    }

    public function test_add_url()
    {
        // the object of the action, eg. a posting, a photo, a file
        $activity = new \Studip\Activity\Activity();

        // url is the Stud.IP-URL to the object, route is the REST-API-Route to the object
        $url = 'http://example.com/dispatch.php/forum/posting/1234';

        $activity->addUrl($url, 'link');
    }
}
