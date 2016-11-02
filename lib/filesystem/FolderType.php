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
    /**
     * Returns the name of the FolderType (not the folder)
     * @return string
     */
    static public function getTypeName();

    /**
     * @return Icon
     */
    static public function getIcon();

    /**
     * @param string $range_type : "course", "user", "institute"
     * @return boolean
     */
    static public function creatableInStandardFolder($range_type);

    public function getName();

    /**
     * @param $user_id
     * @return boolean
     */
    public function isVisible($user_id);

    /**
     * @param $user_id
     * @return boolean
     */
    public function isReadable($user_id);

    /**
     * @param $user_id
     * @return boolean
     */
    public function isWritable($user_id);

    /**
     * @param $user_id
     * @return boolean
     */
    public function isSubfolderAllowed($user_id);

    /**
     * @return string
     */
    public function getDescriptionTemplate();

    public function getEditTemplate();

    public function setData($request);

    public function validateUpload($file, $user_id);
}