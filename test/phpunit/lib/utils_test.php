<?php
/**
 * utils_test.php - Test various functions of lib/utils.php.
 *
 * This test needs a running (simulated) Stud.IP environment, since it will 
 * access the database, session cookies and so on.
 *
 * To avoid headers being sent by PHPunit output, redirect stdout:
 *     phpunit --stderr utils_test.php
 *
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2013 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */
require_once dirname(__FILE__) . '/../bootstrap_globals.php';

// setup fake environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTP_CONNECTION'] = 'keep-alive';
$_SERVER['HTTP_CACHE_CONTROL'] = 'no-cache';
$_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
$_SERVER['HTTP_PRAGMA'] = 'no-cache';
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';
$_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate,sdch';
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8,is;q=0.6,de-DE;q=0.4,de;q=0.2,pt-PT;q=0.2,pt;q=0.2,fr-FR;q=0.2,fr;q=0.2';
$_SERVER['HTTP_COOKIE'] = 'Seminar_Session=7003c8ec0e75b569af2b1918af8db6ba';
$_SERVER['PATH'] = '/usr/bin:/bin:/usr/sbin:/sbin';
$_SERVER['SERVER_SIGNATURE'] = '';
$_SERVER['SERVER_SOFTWARE'] = 'Apache/2.2.24 (Unix) DAV/2 PHP/5.5.7 mod_ssl/2.2.24 OpenSSL/0.9.8y';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['DOCUMENT_ROOT'] = '/Library/WebServer/Documents';
$_SERVER['SERVER_ADMIN'] = 'you@example.com';
$_SERVER['SCRIPT_FILENAME'] = '/Users/rcosta/Sites/step00256/upload.php';
$_SERVER['REMOTE_PORT'] = '51544';
$_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = 'cid=a07535cf2f8a72df33c12ddfa4b53dde';
$_SERVER['REQUEST_URI'] = '/~rcosta/step00256/upload.php?cid=a07535cf2f8a72df33c12ddfa4b53dde';
$_SERVER['SCRIPT_NAME'] = '/~rcosta/step00256/upload.php';
$_SERVER['PHP_SELF'] = '/~rcosta/step00256/upload.php';
$_SERVER['REQUEST_TIME_FLOAT'] = '1388515838.508';
$_SERVER['REQUEST_TIME'] = '1388515838';

$_GET['cid'] = 'a07535cf2f8a72df33c12ddfa4b53dde';
$_COOKIE['Seminar_Session'] = '7003c8ec0e75b569af2b1918af8db6ba'; // ID in table session_data
$_REQUEST['cid'] = 'a07535cf2f8a72df33c12ddfa4b53dde'; // where is cookie??
//$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

//$_SESSION['auth_user'] = 'root';
//$GLOBALS['user'] = 

//PHPUnit_Framework_Error_Warning::$enabled = FALSE;  // Warning
//PHPUnit_Framework_Error_Notice::$enabled = FALSE;   // notice, strict:

require_once 'lib/utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        Utils\startSession();
    }

    function testGetSeminarId() {
        $seminar_id = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $this->assertEquals($seminar_id, Utils\getSeminarId());
    }
/*
// TODO implement unit test
static function testMediaUrl($a, $b) {
    $c = Utils::getMediaUrl($a);
    \assert($c == $b, "getMediaUrl($a)\n== $c\n!= $b\n");
}

// TODO implement unit test
static function testGetMediaUrl() {
    \header('Content-type: text/plain; charset=utf-8');

    // studip must be at localhost:8080/studip for tests to work
    // LOAD_EXTERNAL_MEDIA must be set to 'proxy'
    $studip_document = 'http://localhost:8080/studip/sendfile.php?type=0&file_id=abc123&file_name=test.jpg';
    $studip_document_ip = 'http://127.0.0.1:8080/studip/sendfile.php?type=0&file_id=abc123&file_name=test.jpg';
    $external_document = 'http://pflanzen-enzyklopaedie.eu/wp-content/uploads/2012/11/Sumpfdotterblume-multiplex-120x120.jpg';
    $proxy_document = 'http://localhost:8080/studip/dispatch.php/media_proxy?url=http%3A%2F%2Fpflanzen-enzyklopaedie.eu%2Fwp-content%2Fuploads%2F2012%2F11%2FSumpfdotterblume-multiplex-120x120.jpg';
    $studip_document_no_domain = '/studip/sendfile.php?type=0&file_id=abc123&file_name=test.jpg';
    // $proxy_no_domain = '/studip/dispatch.php/media_proxy?url=http%3A%2F%2Fwww.ecult.me%2Fimages%2Flogo.png';

    testMediaUrl($studip_document, $studip_document);
    testMediaUrl('invalid url', NULL);
    testMediaUrl($studip_document_ip, $studip_document);
    testMediaUrl($external_document, $proxy_document);
    testMediaUrl($proxy_document, $proxy_document);
    testMediaUrl($studip_document_no_domain, $studip_document);
}

    function testFolderNameExists() {
        // Note: If a folder by that name already exists, the test will
        // fail if Utils\folderNameExists works correctly.
        // Solution: Use a testing database with a sane setup...
        $name = 'f0b37cbb5b88cdfe73fc4b536e6aedb8';  // random md5 hash
        $this->assertFalse(Utils\folderNameExists($name));
        $id = Utils\createFolder($name);
        $this->assertFalse(Utils\folderNameExists($name));

        // teardown: delete created folder
        $this->assertTrue(Utils\executeQuery(
            'DELETE FROM folder WHERE folder_id=:id',
            array('id' => $id),
            FALSE));
    }

    function testRandomFolderName() {
        // any negative number of retries yields NULL
        $this->assertNull(Utils\randomfolderName('', -1)); // -1 retries
        $this->assertNull(Utils\randomfolderName('', -10)); // -10 retries

        // the create name doesn't exist and the given prefix is used
        $prefix = 'test random folder name';
        $name = Utils\randomFolderName($prefix);
        $this->assertFalse(Utils\folderExists($name));
        $this->assertTrue(Utils\startsWith($prefix, $name));
    }

    function testGetFolderId() {
        // get ID of top-level folder
        $name = Utils\randomFolderName('testGetFolderId');
        $id = Utils\createFolder($name);
        $this->assertNotNull($id);
        $this->assertEquals($id, Utils\getFolderId($name));

        // get ID of sub-folder
        $subname = Utils\randomFolderName('testGetFolderId');
        $subid = Utils\createFolder($name, $id);
        $this->assertNotNull($subid);
        $this->assertEquals($subid, Utils\getFolderId($subname, $id));

        // teardown: delete created folders
        $this->assertTrue(Utils\executeQuery(
            'DELETE FROM folder WHERE folder_id IN (:id, :subid)',
            array('id' => $id, 'subid' => $subid),
            FALSE));
    }

    function testCreateFolder() {
        // fail
        $this->assertNull(Utils\createFolder(NULL));
        $this->assertNull(Utils\createFolder(str_repeat('1', 256)));

        // create a new top-level folder
        $name = Utils\randomFolderName('testCreateFolder');
        $id = Utils\createFolder($name);
        $this->assertNotNull($id);

        $folder = Utils\getFolderById($id);
        $this->assertEquals($name, $folder['name']);
        $this->assertEquals('', $folder['description']);
        $this->assertEquals(Utils\getSeminarId(), $folder['seminar_id']);
        $this->assertEquals(Utils\getSeminarId(), $folder['range_id']);
        $this->assertEquals(7, $folder['permission']);

        // create a subfolder
        $sub_name = 'sub name';
        $sub_description = 'sub description';
        $sub_permission = 3;
        $sub_id = Utils\createFolder(
            $sub_name, $sub_description, $id, $sub_permission);
        $this->assertNotNull($sub_id);

        $sub_folder = Utils\getFolderBy($sub_id);
        $this->assertEquals($name, $sub_folder['name']);
        $this->assertEquals('', $sub_folder['description']);
        $this->assertEquals(Utils\getSeminarId(), $sub_folder['seminar_id']);
        $this->assertEquals(Utils\getSeminarId(), $sub_folder['range_id']);
        $this->assertEquals(7, $sub_folder['permission']);

        // teardown: delete created folders
        $this->assertTrue(Utils\executeQuery(
            'DELETE FROM folder WHERE folder_id IN (:id, :subid)',
            array('id' => $id, 'subid' => $sub_id),
            FALSE));
    }

    function testTransposeArrayDetectsBadData() {
        $data = array(
            'NULL'              => NULL,
            'array(0)'          => array(0),
            'array(1)'          => array(1),
            "array('a' => 0)"   => array('a' => 0),
            "array('a' => 1)"   => array('a' => 1),
            'array(array(), 0)' => array(array(), 0),
            'array(array(), 1)' => array(array(), 1)
        );
        foreach ($data as $message => $test) {
            $this->assertNull(Utils\transposeArray($test), "Parameter: $message");
        }
    }

    function testTransposeArrayKeepsSymmetry() {
        $data = array(
            'empty1' => array(),
            'empty2' => array(array()),
            'empty3' => array(array(), array()),
            'one'    => array(array(1)),
            'assoc'  => array('a' => array('a' => 1)),
            'sym1'   => array(array(1, 2, 3),
                              array(2, 4, 5),
                              array(3, 5, 6)),
            'sym2'   => array('a' => array('a' => 1, 'b' => 2, 'c' => 3),
                              'b' => array('a' => 2, 'b' => 4, 'c' => 5),
                              'c' => array('a' => 3, 'b' => 5, 'c' => 6))
        );
        foreach ($data as $message => $test) {
            $this->assertEquals($test, Utils\transposeArray($test), $message);
        }
    }

    function testTransposeArrayNormalizesOuter() {
        $data = array(
            'empty' => array(
                array('a' => array()),
                array(array()),
                array(array())),
            'one' => array(
                array('a' => array(), 'b' => array(NULL)),
                array(array('b' => NULL)),
                array('b' => array(NULL))),
            'two' => array(
                array('a' => array(), 'b' => array(NULL), 'c' => array(NULL)),
                array(array('b' => NULL, 'c' => NULL)),
                array('b' => array(NULL), 'c' => array(NULL))),
        );
        foreach ($data as $message => $test) {
            $this->assertEquals($test[1],
                                Utils\transposeArray($test[0]),
                                'b == T(a) ' . $message);

            $this->assertEquals($test[2],
                                Utils\transposeArray(
                                    Utils\transposeArray($test[0])),
                                'bT == T(T(a)) ' . $message);

            $this->assertEquals($test[1],
                                Utils\transposeArray(
                                    Utils\transposeArray(
                                        Utils\transposeArray($test[0]))),
                                'b == T(T(T(a))) ' . $message);
        }
    }

    function testTransposeArrayNormalizesInner() {
        $data = array(
            'one' => array(
                array('a' => array(), array('b' => NULL)),
                array(array(), 'b' => array(NULL)),
                array(array('b' => NULL)),
                array('b' => array(NULL))),
            'two' => array(
                array('a' => array(), array('b' => NULL), array('c' => NULL)),
                array(array(), 'b' => array(NULL), 'c' => array(1 => NULL)),
                array(array('b' => NULL), array('c' => NULL)),
                array('b' => array(NULL), 'c' => array(1 => NULL))),
            'another two' => array(
                array('a' => array(), array('b' => NULL), 'c' => array(NULL)),
                array(array('c' => NULL), 'b' => array(NULL)),
                array('c' => array(NULL), array('b' => NULL)),
                array(array('c' => NULL), 'b' => array(NULL)))
        );
        foreach ($data as $message => $test) {
            $this->assertEquals($test[1],
                                Utils\transposeArray($test[0]),
                                'b == T(a) ' . $message);

            $this->assertEquals($test[2],
                                Utils\transposeArray(
                                    Utils\transposeArray($test[0])),
                                'c == T(T(a)) ' . $message);

            $this->assertEquals($test[3],
                                Utils\transposeArray(
                                    Utils\transposeArray(
                                        Utils\transposeArray($test[0]))),
                                'cT == T(T(T(a))) ' . $message);

            $this->assertEquals($test[2],
                                Utils\transposeArray(
                                    Utils\transposeArray(
                                        Utils\transposeArray(
                                            Utils\transposeArray($test[0])))),
                                'c == T(T(T(T(a)))) ' . $message);
        }
    }

    function testTransposeArrayTransposes() {
        $data = array(
            'assoc0' => array(
                array('a' => array(NULL)),
                array(array('a' => NULL))),
            'assoc1' => array(
                array('a' => array(1)),
                array(array('a' => 1))),
            'assoc2' => array(
                array('a' => array('b' => 'c')),
                array('b' => array('a' => 'c'))),
            'matrix' => array(
                array(array(11, 12, 13, 14),
                      array(21, 22, 23, 24),
                      array(31, 32, 33, 34)),
                array(array(11, 21, 31),
                      array(12, 22, 32),
                      array(13, 23, 33),
                      array(14, 24, 34))),
            'complex1' => array(
                array('A' => array('a' => 11, 'b' => 12, 'c' => 13, 'd' => 14),
                      'B' => array('a' => 21, 'b' => 22),
                      'C' => array('a' => 31)),
                array('a' => array('A' => 11, 'B' => 21, 'C' => 31),
                      'b' => array('A' => 12, 'B' => 22),
                      'c' => array('A' => 13),
                      'd' => array('A' => 14))),
            'complex2' => array(
                array('A' => array('a' => 11, 'b' => 12, 'c' => 13, 'd' => 14),
                      'B' => array('e' => 21, 'f' => 22),
                      'C' => array('g' => 31)),
                array('a' => array('A' => 11),
                      'b' => array('A' => 12),
                      'c' => array('A' => 13),
                      'd' => array('A' => 14),
                      'e' => array('B' => 21),
                      'f' => array('B' => 22),
                      'g' => array('C' => 31)))
        );
        foreach ($data as $message => $test) {
            $this->assertEquals($test[0], Utils\transposeArray($test[1]),
                                'a == T(aT) ' . $message);

            $this->assertEquals($test[1], Utils\transposeArray($test[0]),
                                'aT == T(a) ' . $message);

            $this->assertEquals($test[0],
                                Utils\transposeArray(
                                    Utils\transposeArray($test[0])),
                                'a == T(T(a)) ' . $message);
        }
    }

    function testGetUploadedFilesHandlesZeroFiles() {
        // not uploading at all
        $_FILES = NULL;
        $this->assertEquals(array(), Utils\getUploadedFiles());

        // uploading zero files
        $_FILES = array('files' => array('name' => array()));
        $this->assertEquals(array(), Utils\getUploadedFiles());
    }

    function testGetUploadedFilesHandlesOneFile() {
        // uploading one file as HTML array
        // <input type="file" name="files[]" multiple />
        $_FILES = array('files' => array(
            'name'     => array('name'),
            'tmp_name' => array('temp'),
            'type'     => array('mime'),
            'size'     => array(0),
            'error'    => array(1)
        ));
        $result = array(array('name'     => 'name',
                              'tmp_name' => 'temp',
                              'type'     => 'mime',
                              'size'     => 0,
                              'error'    => 1));
        $this->assertEquals($result, Utils\getUploadedFiles());
    }

    function testGetUploadedFilesHandlesMultipleFiles() {
        // uploading two files as HTML array
        // <input type="file" name="files[]" multiple />
        $_FILES = array('files' => array(
            'name'     => array('name1', 'name2'),
            'tmp_name' => array('temp1', 'temp2'),
            'type'     => array('mime1', 'mime2'),
            'size'     => array(10, 20),
            'error'    => array(11, 21)
        ));
        $result = array(array('name'     => 'name1',
                              'tmp_name' => 'temp1',
                              'type'     => 'mime1',
                              'size'     => 10,
                              'error'    => 11),
                        array('name'     => 'name2',
                              'tmp_name' => 'temp2',
                              'type'     => 'mime2',
                              'size'     => 20,
                              'error'    => 21));
        $this->assertEquals($result, Utils\getUploadedFiles());
    }
*/
}
