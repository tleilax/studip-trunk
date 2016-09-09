<?php
/**
 * FileManager.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class FileManager
{

    public static function handleFileUpload(Array $uploadedfiles, FolderType $folder, $user_id)
    {
        $result = array();
        if (is_array($uploadedfiles['name'])) {
            $error = [];
            foreach ($uploadedfiles['name'] as $key => $filename) {
                if ($uploadedfiles['error'][$key] === UPLOAD_ERR_INI_SIZE) {
                    $error[] = _("Die maximale Dateigröße wurde überschritten.");
                    continue;
                }
                if ($uploadedfiles['error'][$key] > 0) {
                    $error[] = _("Ein Systemfehler ist beim Upload aufgetreten. Fehlercode: " . $uploadedfiles['error'][$key]);
                    continue;
                }
                $filetype = $uploadedfiles['type'][$key] ?: get_mime_type($filename);
                $tmpname = $uploadedfiles['tmp_name'][$key];
                $size = $uploadedfiles['size'][$key];
                if ($folder_error = $folder->validateUpload(['name' => $filename, 'size' => $size], $user_id)) {
                    $error[] = $folder_error;
                    continue;
                }
                $file = new File();
                $file->user_id = $user_id;
                $file->name = $filename;
                $file->mime_type = $filetype;
                $file->size = $size;
                $file->storage = 'disk';
                $file->id = $file->getNewId();
                if ($file->connectWithDataFile($tmpname)) {
                    $result['files'][] = $file;
                } else {
                    $error[] = _("Ein Systemfehler ist beim Upload aufgetreten.");
                }
            }
        }
        return array_merge($result, ['error' => $error]);
    }
}