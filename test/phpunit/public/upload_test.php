<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

class UploadTest extends PHPUnit_Framework_TestCase {
    public function testUploadingNoFiles() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder does not exist, $_FILES is empty
        // should not fail
        // should not create default folder
        // should return empty JSON array
    }

    public function testUploadingOneFileWithoutFolder() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder does not exist yet
        // should create default folder
        // should return JSON array with download link (success)
        // -> remove default folder in teardown!!
    }

    public function testUploadingOneFileWithDefaultFolder() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder does already exist
        // should create default folder
        // should return JSON array with download link (success)
        // -> remove default folder in teardown!!
    }

    public function testUploadingOneFileWithValidFolderId() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder doesn't exist, folder ID exists
        // should create default folder
        // should return JSON array with download link (success)
        // -> remove default folder in teardown!!
    }

    public function testUploadingOneFileWithInvalidFolderId() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder doesn't exist, folder ID doesn't exist
        // should create default folder
        // should return JSON array with download link (success)
        // -> remove default folder in teardown!!
    }

    public function testUploadingForbiddenFiles() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder doesn't exist, no folder ID is given
    }

    public function testUploadingSingleFileThatExceedsSizeLimits() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder doesn't exist, no folder ID is given
    }

    public function testUploadingMultipleFilesThatTogetherExceedSizeLimits() {
        $this->markTestIncomplete('Not implemented.');
        // precondition: default folder doesn't exist, no folder ID is given
    }
}
