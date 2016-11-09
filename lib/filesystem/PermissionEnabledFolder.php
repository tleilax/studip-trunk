<?php
/**
 * PermissionEnabledFolder.php
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
class PermissionEnabledFolder extends StandardFolder
{
    protected $permission = 7;
    protected $perms = array('x' => 1, 'w' => 2, 'r' => 4, 'f' => 8);
    protected $must_have_perm;

    static public function creatableInStandardFolder($range_type)
    {
        return $range_type == 'course' || $range_type == 'institute';
    }

    public function setFolderData($folderdata)
    {
        $this->folderdata = $folderdata;
        $this->permission = $folderdata['data_content']['permission'] ?: 7;
        $this->must_have_perm = $this->range_type == 'course' ? 'tutor' : 'autor';
    }

    public function getPermissionString()
    {
        $perms = $this->perms;
        array_pop($perms);
        $r = array_flip($perms);
        foreach($perms as $v => $p) if(!($this->permission & $p)) $r[$p] = '-';
        return join('', array_reverse($r));
    }

    protected function checkPermission($perm, $user_id)
    {
        if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id)) {
            return true;
        }
        return (bool)($this->permission & $this->perms[$perm]);
    }

    public function isVisible($user_id)
    {
        return $this->checkPermission('x', $user_id);
    }

    public function isReadable($user_id)
    {
        return $this->checkPermission('r', $user_id);
    }

    public function isWritable($user_id)
    {
        return $this->checkPermission('w', $user_id);
    }

    public function isSubfolderAllowed($user_id)
    {
        return $this->checkPermission('f', $user_id);
    }

    public function getDescriptionTemplate()
    {

    }

    public function getEditTemplate()
    {

    }
    
    static public function getTypeName()
    {
        return _("Ordner mit Zugangsbeschränkung");
    }

    public function setData($request)
    {
        $this->permission = $request['permission'];
        $this->folderdata['data_content']['permission'] = $this->permission;
    }

    public function validateUpload($uploadedfile, $user_id)
    {
        if (!$this->isWritable($user_id)) {
            return _("Der Dateiordner ist nicht beschreibbar.");
        } else {
            return parent::validateUpload($uploadedfile, $user_id);
        }
    }
}