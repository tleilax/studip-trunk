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


class FakeContext extends \Studip\Activity\Context
{

    protected function getProvider()
    {
        // TODO: Implement getProvider() method.
    }

    public function getRangeId()
    {
        // TODO: Implement getRangeId() method.
    }

    protected function getContextType()
    {
        // TODO: Implement getContextType() method.
    }
}

class Stream extends PHPUnit_Framework_TestCase
{

    function setUp() {
    }


    function tearDown() {
    }


    function test_class_should_exist()
    {
        $this->assertTrue(class_exists('\Studip\Activity\Stream'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    function test_stream_wants_context()
    {
        $observer_id = 'observer_id';
        $stream = new \Studip\Activity\Stream($observer_id, '', new Studip\Activity\Filter());
    }

    function test_stream_has_array_iterator()
    {
        $observer_id = 'observer_id';
        $stream = new \Studip\Activity\Stream($observer_id, new FakeContext(), new Studip\Activity\Filter());

        $this->assertTrue(
            $stream instanceof ArrayAccess,
            'Class Stream does not implement ArrayAccess.'
        );

        $this->assertTrue(
            $stream instanceof Countable,
            'Class Stream does not implement Countable.'
        );

        $this->assertTrue(
            $stream instanceof IteratorAggregate,
            'Class Stream does not implement IteratorAggregate.'
        );
    }

    function test_stream_takes_more_than_one_context()
    {
        $observer_id = 'observer_id';
        $stream = new \Studip\Activity\Stream($observer_id, array(new FakeContext(), new FakeContext()), new Studip\Activity\Filter());

        $this->assertEquals(2, sizeof($stream));

        foreach ($stream as $activity) {
            $this->assertTrue(
                $activity instanceof Studip\Activity\Activity,
                'Class stream contains elements that are not Studip\Activity'
            );
        }
    }
}
