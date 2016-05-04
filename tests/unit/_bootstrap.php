<?php

// Copyright (c)  2007 - Marcus Lunzenauer <mlunzena@uos.de>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

// set error reporting
error_reporting(E_ALL & ~E_NOTICE);
if (version_compare(phpversion(), '5.4', '>=')) {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}

// set include path
$inc_path = ini_get('include_path');
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../..';
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../../config';
ini_set('include_path', $inc_path);

// load varstream for easier filesystem testing
require_once 'varstream.php';

define("TEST_FIXTURES_PATH", dirname(dirname(__FILE__)) . "/fixtures/");

require 'lib/classes/StudipAutoloader.php';
require 'lib/functions.php';

$STUDIP_BASE_PATH = realpath(dirname(__FILE__) . '/../..');

StudipAutoloader::register();
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'activities');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'models');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'exceptions');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'sidebar');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'helpbar');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'engine');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'core');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'db');

// load config-variables
StudipFileloader::load('config/config_local.inc.php', $GLOBALS);

require_once 'vendor/yaml/lib/sfYamlParser.php';
$yaml = new \sfYamlParser();
$config = $yaml->parse(file_get_contents(dirname(__FILE__) .'/../unit.suite.yml'));

// connect to database if configured
if (isset($config['modules']['config']['Db'])) {
    DBManager::getInstance()->setConnection('studip',
        $config['modules']['config']['Db']['dsn'],
        $config['modules']['config']['Db']['user'],
        $config['modules']['config']['Db']['password']);
}