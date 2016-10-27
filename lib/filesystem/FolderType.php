<?php
/**
 * FolderType.php
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
interface FolderType
{
    public function isVisible($user_id);

    public function isReadable($user_id);

    public function isWritable($user_id);

    public function isSubfolderAllowed($user_id);

    public function getName();

    public function getIcon();

    public function getDescriptionTemplate();

    public function getEditTemplate();

    public function setData($request);

    public function validateUpload($file, $user_id);

    public function getAllowedRangeTypes();
}