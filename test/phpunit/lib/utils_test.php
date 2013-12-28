<?php
require_once dirname(__FILE__) . '/../bootstrap.php';
require_once 'lib/utils.php';

class UtilsTest extends PHPUnit_Framework_TestCase {
    function testExecuteQuery() {
        $this->markTestIncomplete('not implemented');
    }

    function testGetFolder() {
        $this->markTestIncomplete('not implemented');
    }

    function testFolderExists() {
        $this->markTestIncomplete('not implemented');
    }

    function testGetFolderId() {
        $this->markTestIncomplete('not implemented');
    }

    function testCreateFolder() {
        $this->markTestIncomplete('not implemented');
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

    function testVerifyUpload() {
        $this->markTestIncomplete('not implemented');
    }

    function testGetStudipDocumentData() {
        $this->markTestIncomplete('not implemented');
    }

    function testGetDownloadLink() {
        $this->markTestIncomplete('not implemented');
    }

    function testUploadFile() {
        $this->markTestIncomplete('not implemented');
    }
}
