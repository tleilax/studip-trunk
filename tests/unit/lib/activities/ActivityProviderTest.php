<?php

/**
 * File - description
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License
 * version 3 as published by the Free Software Foundation.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     https://www.gnu.org/licenses/agpl-3.0.html AGPL version 3
 */

class ActivityProviderTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp() {}
    public function tearDown() {}

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists('\Studip\Activity\ForumProvider'));
        $this->assertTrue(class_exists('\Studip\Activity\NewsProvider'));
        $this->assertTrue(class_exists('\Studip\Activity\DocumentsProvider'));
        $this->assertTrue(class_exists('\Studip\Activity\WikiProvider'));
        $this->assertTrue(class_exists('\Studip\Activity\ParticipantsProvider'));
        $this->assertTrue(class_exists('\Studip\Activity\MessageProvider'));
        $this->assertTrue(class_exists('\Studip\Activity\LiteratureProvider'));
        $this->assertTrue(class_exists('\Studip\Activity\BlubberProvider'));
    }

    public function test_providers_implement_interface()
    {
        $this->assertTrue(
            new \Studip\Activity\ForumProvider() instanceof \Studip\Activity\ActivityProvider,
            'ForumProvider: Missing implementation of Interface ActivityProvider'
        );

        $this->assertTrue(
            new \Studip\Activity\NewsProvider() instanceof \Studip\Activity\ActivityProvider,
            'NewsProvider: Missing implementation of Interface ActivityProvider'
        );

        $this->assertTrue(
            new \Studip\Activity\DocumentsProvider() instanceof \Studip\Activity\ActivityProvider,
            'DocumentsProvider: Missing implementation of Interface ActivityProvider'
        );

        $this->assertTrue(
            new \Studip\Activity\WikiProvider() instanceof \Studip\Activity\ActivityProvider,
            'WikiProvider: Missing implementation of Interface ActivityProvider'
        );

        $this->assertTrue(
            new \Studip\Activity\ParticipantsProvider() instanceof \Studip\Activity\ActivityProvider,
            'ParticipantsProvider: Missing implementation of Interface ActivityProvider'
        );

        $this->assertTrue(
            new \Studip\Activity\MessageProvider() instanceof \Studip\Activity\ActivityProvider,
            'MessageProvider: Missing implementation of Interface ActivityProvider'
        );

        $this->assertTrue(
            new \Studip\Activity\LiteratureProvider() instanceof \Studip\Activity\ActivityProvider,
            'LiteratureProvider: Missing implementation of Interface ActivityProvider'
        );

        $this->assertTrue(
            new \Studip\Activity\BlubberProvider() instanceof \Studip\Activity\ActivityProvider,
            'BlubberProvider: Missing implementation of Interface ActivityProvider'
        );
    }
}
