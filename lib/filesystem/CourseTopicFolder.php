<?php
/**
 * CourseTopicFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class CourseTopicFolder extends StandardFolder implements FolderType
{
    public static function getTypeName()
    {
        return _('Themen-Ordner');
    }

    public static function creatableInStandardFolder($range_type)
    {
        return $range_type == 'course';
    }

    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create(
            count($this->getFiles()) ? 'folder-topic-full' : 'folder-topic-empty',
            $role
        );
    }


}
