<?php


class PublicFolder extends StandardFolder
{

    static public function getTypeName()
    {
        return _('Ein Ordner für öffentlich zugängliche Daten');
    }


    static public function creatableInStandardFolder($range_type)
    {
        return $range_type === 'user';
    }


    public function isVisible($user_id)
    {
        return true;
    }


    public function isReadable($user_id)
    {
        return true;
    }


    public function isWritable($user_id)
    {
        return $user_id === $GLOBALS['user']->id;
    }


    public function isSubfolderAllowed($user_id)
    {
        return $user_id === $GLOBALS['user']->id;
    }


    public function getDescriptionTemplate()
    {
        return _("Öffentlich sichtbar für alle.");
    }


    public function createFile($file)
    {
        //to be implemented
    }


    public function isFileDownloadable($file_id, $user_id)
    {
        //public folder => everyone can download a file
        return true;
    }


    public function isFileEditable($file_id, $user_id)
    {
        //only the owner may edit files
        return ($user_id == $this->folderdata['user_id']);
    }


    public function isFileWritable($file_id, $user_id)
    {
        //only the owner may delete files
        return ($user_id == $this->folderdata['user_id']);
    }
}