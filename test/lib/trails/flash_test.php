<?php

# Copyright (c)  2007 - Marcus Lunzenauer <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

require_once 'lib/trails/TrailsController.php';
require_once 'lib/trails/TrailsDispatcher.php';
require_once 'lib/trails/TrailsFlash.php';
require_once 'lib/trails/TrailsInflector.php';
require_once 'lib/trails/TrailsResponse.php';
require_once 'lib/exceptions/TrailsException.php';
require_once 'mocks.php';

class FlashWithoutSessionTestCase extends UnitTestCase {

  function test_should_throw_an_error_without_a_session() {
    $this->expectException('TrailsSessionRequiredException');
    $flash = TrailsFlash::instance();
  }

  function test_should_not_throw_an_error_with_a_session() {
    $_SESSION = array();
    $flash = TrailsFlash::instance();
    $flash['key'] = 'do not provoke an exception';
    unset($_SESSION);
  }
}

class FlashWithSessionTestCase extends UnitTestCase {

  function setup() {
    $_SESSION = array();
    $this->flash = TrailsFlash::instance();
  }

  function tearDown() {
    unset($_SESSION, $this->flash);
  }

  function simulateRedirect() {
    $this->flash->__sleep();
    $this->flash->__wakeUp();
    $this->flash = TrailsFlash::instance();
  }

  function test_should_be_a_singleton() {
    $flash1 = TrailsFlash::instance();
    $flash2 = TrailsFlash::instance();
    $this->assertReference($flash1, $flash2);
  }

  function test_should_not_throw_exception_when_accessing_with_a_session() {
    $this->flash['key'] = 'value';
  }

  function test_should_return_previously_set_values() {
    $a_value = 'value';
    $this->flash['key'] = $a_value;
    $this->assertEqual($a_value, $this->flash['key']);
  }

  function test_should_return_previously_set_values_as_references() {
    $a_value = new stdClass();
    $this->flash['key'] = $a_value;
    $this->assertReference($a_value, $this->flash['key']);
  }

  function test_should_remove_value_with_unset() {
    $this->flash['key'] = 'value';
    unset($this->flash['key']);
    $this->assertFalse($this->flash['key']);
  }

  function test_should_be_able_to_use_isset_to_ask_for_existence() {
    $this->flash['key'] = 'value';
    $this->assertTrue(isset($this->flash['key']));
    $this->assertFalse(isset($this->flash['not a key']));
  }

  function test_should_return_previously_set_values_after_one_redirect() {
    $a_value = 'value';
    $this->flash['key'] = $a_value;
    $this->simulateRedirect();
    $this->assertEqual($a_value, $this->flash['key']);
  }

  function test_should_return_NULL_after_more_than_one_redirect() {
    $a_value = 'value';
    $this->flash['key'] = $a_value;
    $this->simulateRedirect();
    $this->simulateRedirect();
    $this->assertEqual(NULL, $this->flash['key']);
  }

  function test_should_return_reference_after_set_ref() {
    $a = new stdClass();
    $this->flash->set_ref('a', $a);
    $got = $this->flash->get('a');
    $this->assertReference($got, $a);
  }

  function test_should_access_reference_after_set_ref() {
    $a = array();
    $this->flash->set_ref('a', $a);
    $a[] = 42;
    $got = $this->flash->get('a');
    $this->assertEqual($got, array(42));
  }

  function test_should_discard_one() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # discard a
    $this->flash->discard('a');

    # simulate redirect
    $this->simulateRedirect();

    # a should be deleted, but not b
    $this->assertNull($this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));
  }

  function test_should_discard_all() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # discard
    $this->flash->discard();

    # simulate redirect
    $this->simulateRedirect();

    # a and b should be deleted
    $this->assertNull($this->flash->get('a'));
    $this->assertNull($this->flash->get('b'));
  }

  function test_should_keep_one() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # simulate 1st redirect
    $this->simulateRedirect();

    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    $this->flash->keep('b');

    # simulate 2nd redirect
    $this->simulateRedirect();

    # a should be deleted, but not b
    $this->assertNull($this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));
  }

  function test_should_keep_all() {
    # set a and b
    $this->flash->set('a', 42);
    $this->flash->set('b', 23);
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    # simulate 1st redirect
    $this->simulateRedirect();

    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));

    $this->flash->keep();

    # simulate 2nd redirect
    $this->simulateRedirect();

    # neither a nor b should be deleted
    $this->assertEqual(42, $this->flash->get('a'));
    $this->assertEqual(23, $this->flash->get('b'));
  }
}

