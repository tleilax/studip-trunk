<?php

class CourseTopicFolder extends StandardFolder implements FolderType
{
    static public function getTypeName()
    {
        return _('Themen-Ordner');
    }
    
    
    public function getIcon($role)
    {
        return Icon::create(count($this->getFiles()) ? 'folder-topic-full' : 'folder-topic-empty', $role);
    }
}
