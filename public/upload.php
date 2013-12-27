<?php
/**
 * upload.php - Handle file uploads.
 * 
 * Files must be posted as an HTML array with the name "files":
 *   <input type="file" name="files[]" multiple />
 *
 * A folder identifier can be given in the POST variable "folder":
 *   <input type="hidden" name="folder_id" value="IDENTIFIER" />
 *
 * If no folder identifier is given, files will be stored in a folder
 * named "Uploads".
 *
 *
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
 * @author      Robert Costa <zabbarob@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
require_once('../lib/utils.php');

$upload_permission = 'autor';  // minimum permission level for uploading
$default_folder = _('Uploads');
$default_description
    = _('Automatisch hochgeladene Dateien (z.B. vom WYSIWYG Editor).');

// initialize session management and verify permissions
Utils\startSession();
Utils\verifyPostRequest();
Utils\verifyPermission($upload_permission);
CSRFProtection::verifyUnsafeRequest();

// get folder ID
if (isset($_POST['folder_id']) && Utils\folderExists($_POST['folder_id']) {
    $folder_id = $_POST['folder_id'];
} else {
    $folder_id = Utils\createFolder($default_folder, $default_description)
        or exit('unable to create default folder');
}

// store uploaded files as StudIP documents
$response = array();  // data for HTTP response
foreach (Utils\getUploadedFiles() as $file) {
    try {
        $newfile = Utils\uploadFile($file, $folder_id);
        $response['files'][] = Array(
            'name' => utf8_encode($newfile['filename']),
            'type' => $file['type'],
            'url' => $newfile->download_link);
    } catch (AccessDeniedException $e) {  // creation of Stud.IP doc failed
        $response['files'][] = Array(
            'name' => $file['name'],
            'type' => $file['type'],
            'error' => $e->getMessage());
    }
}

// send HTTP response to client
Utils\sendAsJson($response);
