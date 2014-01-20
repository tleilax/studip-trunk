<?php
/**
 * wysiwyg.php - Provide web services for the WYSIWYG editor.
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
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */
require_once 'authenticated_controller.php';
use Studip\Utils;

class WysiwygController extends AuthenticatedController
{
    /**
     * Handle the WYSIWYG editor's file uploads.
     *
     * Files must be posted as an HTML array named "files":
     *   <input type="file" name="files[]" multiple />
     *
     * Files will be stored in a folder named "Wysiwyg Uploads". If the
     * folder doesn't exist, it will be created.
     *
     * Results are returned as JSON-encoded array:
     *
     * [{"name": filename, "type": mime-type, "url": download-link},
     *  {"name": filename, "type": mime-type, "error": error-message},
     *  ...]
     *
     * Each array-entry corresponds to a single file, each file that was
     * sent with the post request has exactly one entry.
     *
     * Entries with the property "url" correspond to successful uploads.
     * Entries with the property "error" correspond to failed uploads.
     */
    public function upload_action() {
        // verify access permissions
        Utils::verifyPostRequest();
        CSRFProtection::verifyUnsafeRequest();
        //Utils\startSession();  // ==> done by AuthenticatedController::before_filter
        Utils::verifyPermission('autor');
        // verify minimum permission level for uploading and editing
        //$GLOBALS['perm']->check('autor');

        // get folder ID
        $folder_id = Utils::createFolder(
            _('Wysiwyg Uploads'),
            _('Vom WYSIWYG Editor hochgeladene Dateien.')
        ) or exit(_('Erstellen des Upload-Ordners fehlgeschlagen.'));

        // store uploaded files as StudIP documents
        $response = array();  // data for HTTP response
        foreach (Utils::getUploadedFiles() as $file) {
            try {
                $newfile = Utils::uploadFile($file, $folder_id);
                $response['files'][] = Array(
                    'name' => utf8_encode($newfile['filename']),
                    'type' => $file['type'],
                    'url' => Utils::getDownloadLink($newfile->getId()));
            } catch (AccessDeniedException $e) {  // creation of Stud.IP doc failed
                $response['files'][] = Array(
                    'name' => $file['name'],
                    'type' => $file['type'],
                    'error' => $e->getMessage());
            }
        }

        // send HTTP response to client
        Utils::sendAsJson($response);
        $this->performed = TRUE;
    }
}
