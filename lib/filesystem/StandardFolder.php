<?php
/**
 * StandardFolder.php
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
class StandardFolder implements FolderType
{
    private $folderdata;
    private $permission = 7;
    private $range_id;
    private $range_type;
    private $perms = array('x' => 1, 'w' => 2, 'r' => 4, 'f' => 8);
    private $must_have_perm;


    public function __construct($folderdata)
    {
        $this->setFolderData($folderdata);
    }

    public function getFolderData()
    {
        return $this->folderdata;
    }

    public function setFolderData($folderdata)
    {
        $this->folderdata = $folderdata;
        $this->permission = $folderdata['data_content']['permission'] ?: 7;
        $this->range_id = $folderdata['range_id'];
        $this->range_type = $folderdata['range_type'];
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

    public function getName()
    {
        return $this->folderdata['name'];
    }

    public function getIcon()
    {
        return Icon::create('folder');
    }

    public function getDescriptionTemplate()
    {

    }

    public function getEditTemplate()
    {

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
        }
        if ($this->range_type == 'course') {
            $status = $GLOBALS['perm']->get_studip_perm($this->range_id, $user_id);
            $active_upload_type = Course::find($this->range_id)->status;
        } elseif ($this->range_type == 'institute') {
                $status = $GLOBALS['perm']->get_studip_perm($this->range_id, $user_id);
                $active_upload_type = 'institute';
        } else {
            $status = $GLOBALS['perm']->get_perm($user_id);
            $active_upload_type = "personalfiles";
        }
        if (!isset($GLOBALS['UPLOAD_TYPES'][$active_upload_type])) {
            $active_upload_type = 'default';
        }
        $upload_type = $GLOBALS['UPLOAD_TYPES'][$active_upload_type];
        if ($upload_type["file_sizes"][$status] < $uploadedfile['size']) {
            return sprintf(_("Die maximale Größe für einen Upload (%s) wurde überschritten."), relsize($upload_type["file_sizes"][$status]));
        }
        $ext = strtolower(pathinfo($uploadedfile['name'], PATHINFO_EXTENSION));
        $types = array_map('strtolower', $upload_type['file_types']);
        if (!in_array($ext, $types) && $upload_type['type'] == 'deny') {
            return sprintf(_("Sie dürfen nur die Dateitypen %s hochladen!"), join(',', $upload_type['file_types']));
        }
        if (in_array($ext, $types) && $upload_type['type'] == 'allow') {
            return sprintf(_("Sie dürfen den Dateityp %s nicht hochladen!"), $ext);
        }
    }
}