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

    /**
     * @param $attribute
     * @return mixed
     */
    public function __get($attribute)
    {
        if ($attribute == 'viewable') {
            return $this->folderdata['data_content']['viewable'];
        }
        return $this->folderdata[$attribute];
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if ($name == 'viewable') {
            return $this->folderdata['data_content']['viewable'] = $value;
        }
        return $this->folderdata[$name] = $value;
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

    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/public_folder/edit.php');
        $template->set_attribute('public_folder_viewable', $this->viewable);
        return $template;
    }

    public function setDataFromEditTemplate($request)
    {
        $this->viewable = (int)$request['public_folder_viewable'];
        return parent::setDataFromEditTemplate($request);
    }
}