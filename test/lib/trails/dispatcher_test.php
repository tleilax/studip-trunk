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

class DispatcherTestCase extends UnitTestCase {

  function setUp() {
    $this->setUpFS();
    $this->dispatcher = new PartialMockDispatcher();
    $this->dispatcher->__construct("var://app/", "http://trai.ls", "default");
  }

  function tearDown() {
    stream_wrapper_unregister("var");
    unset($this->dispatcher);
  }

  function setUpFS() {
    ArrayFileStream::set_filesystem(array(
      'app' => array(
        'controllers' => array(
          'foo.php' => '<?'
        ),
      ),
    ));
    stream_wrapper_register("var", "ArrayFileStream") or die("Failed to register protocol");
  }

  function test_should_instantiate_controller() {
    $controller = new RescueController();
    $controller->__construct($this->dispatcher);

    # Dispatching to FooController#index_action won't set a response thus
    # provoking an error. By calling #render_nothing before dispatching we can
    # preclude this.
    $controller->render_nothing();

    $this->dispatcher->expectOnce('load_controller', array('foo'));
    $this->dispatcher->setReturnValue('load_controller', $controller);
    $this->dispatcher->expectOnce('parse');
    $this->dispatcher->setReturnValue('parse', array('foo', ''));

    $result = $this->dispatcher->dispatch("/foo");
  }

  function test_should_display_error_on_framework_exception() {
    $exception = new TrailsException(500);
    $this->dispatcher->throwOn('load_controller', $exception);
    $this->dispatcher->expectOnce('trails_error', array($exception));
    $this->dispatcher->setReturnValue('trails_error', new TrailsResponse());
    $result = $this->dispatcher->dispatch("/foo");
  }

  function test_should_throw_an_exception_if_default_controller_could_not_be_found() {
    $dispatcher = new PartialMockDispatcher();
    $dispatcher->expectOnce('trails_error');
    $dispatcher->setReturnValue('trails_error', new TrailsResponse());
    $dispatcher->dispatch("");
  }
}

