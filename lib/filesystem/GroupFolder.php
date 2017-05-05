<?php
/**
 *  GroupFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.gruppe
 *
 * @author Dominik Feldschnieders <dofeldsc@uos.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class GroupFolder extends StandardFolder
{
    protected $group_id;
    // tutor and higher of a course can do everything with this foldertype
    protected $must_have_perm = "tutor";

    static public function getTypeName()
    {
        return _("Gruppenordner");
    }

    static public function creatableInStandardFolder($range_type)
    {
        return $range_type == 'course';
    }

    public function checkPermission($user_id){
        $group = new Statusgruppen($this->folderdata['data_content']['group']);

        if($group->isMember($user_id)) {
            return true;
        } elseif($user_id && is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id)) {
            return true;
        } else {
                return false;
        }
    }

    public function isVisible($user_id)
    {
        return $this->checkPermission($user_id);
    }

    public function isReadable($user_id)
    {
        return $this->checkPermission($user_id);
    }

    public function isWritable($user_id)
    {
        return $this->checkPermission($user_id);
    }

    public function isSubfolderAllowed($user_id)
    {
        return $this->checkPermission($user_id);
    }

    // ToDo Icon anpassen
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) == 0 ? 'group2' : 'group2';
        return Icon::create($shape, $role);
    }

    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/group_folder/edit.php');
        $template->set_attribute('folder', $this);
        return $template;
    }

    public function setDataFromEditTemplate($request)
    {
        if($request['group'] == null){
            return MessageBox::error(_('Es wurde keine gültige Gruppe ausgewählt.'));
        }

        $group_id = $request['group'];

        $this->folderdata['data_content']['group'] = $group_id;

        return parent::setDataFromEditTemplate($request);
    }

    public function getDescriptionTemplate()
    {
        $group = new Statusgruppen($this->folderdata['data_content']['group']);

        $template = $GLOBALS['template_factory']->open('filesystem/group_folder/description.php');
        $template->set_attribute('type', self::getTypeName());
        $template->set_attribute('folder', $this);
        $template->set_attribute('groupname', $group->name);
        return $template;
    }
}