<?php

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
}