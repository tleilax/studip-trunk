<?php
/**
 * upload.php - Handle file upload.
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

/**
 * Handle file upload requests.
 */
function post_file() {
    Utils\verifyPostRequest();
    Utils\verifyPermission('autor');  // minimum permission level for uploading
    CSRFProtection::verifyUnsafeRequest();

    // store uploaded files as StudIP documents
    $folder_id = post_folder();  // StudIP document folder for storing files
    $response = array(); // data for HTTP response
    foreach (Utils\FILES() as $file) {
        try {
            $newfile = Utils\uploadFile($file, $folder_id);
            $response['files'][] = Array(
                'name' => utf8_encode($newfile['filename']),
                'type' => $file['type'],
                'url' => GetDownloadLink($newfile->getId(), $newfile['filename']));
        } catch (AccessDeniedException $e) { // creation of Stud.IP doc failed
            $response['files'][] = Array(
                'name' => $file['name'],
                'type' => $file['type'],
                'error' => $e->getMessage());
        }
    }
    Utils\sendAsJson($response);
}

function utf8POST(variable, must_exist=FALSE) {
    if (isset(variable)) {
        return studip_utf8decode($_POST[variable]);
    }
    if (must_exist) {
        throw new Exception("POST variable $variable not set.");
    }
    return NULL;
}

/**
 * Create new Stud.IP document folder and returns its identifier.
 *
 * If the folder already exists then the existing folder's identifier is 
 * returned and its folder description is updated (if a description was 
 * posted).
 *
 * POST variables:
 *
 *     folder               Stud.IP document folder name.
 *     folder_description   Stud.IP document folder description.
 *
 * @return string  Identifier of document folder.
 */
function post_folder() {
    // TODO change this, so root folder is returned if folder not set
    return Utils\getFolderId(utf8POST('folder', TRUE),
                             utf8POST('folder_description'));
}
