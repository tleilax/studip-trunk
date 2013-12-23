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
require_once('../lib/bootstrap.php');
require_once('../lib/utils.php');

page_open(array("sess" => "Seminar_Session",  // init session management
                "auth" => "Seminar_Auth",
                "perm" => "Seminar_Perm",
                "user" => "Seminar_User"));
post_file();                                  // upload files
page_close();                                 // stop session management

/**
 * Handle folder creation and file upload requests.
 *
 * Files must be posted as HTML array, as in the following HTML code:
 *   <input type="file" name="files[]" multiple />
 *
 * A Stud.IP document folder can be given if files should not end up in
 * Stud.IP's document folder root. See `post_folder` for details.
 *
 * Posting a folder without uploading files can be used to create new folders 
 * or to update a folder's description.
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
 * @return string  Stud.IP document folder identifier (see `Utils\getFolderId`).
 *
 * @throws Exception  If folder name has not been posted.
 * TODO if no folder is given, return root folder ID
 */
function post_folder() {
    // TODO change this, so root folder is returned if folder not set
    return Utils\getFolderId(Utils\utf8POST('folder', TRUE),
                             Utils\utf8POST('folder_description'));
}
