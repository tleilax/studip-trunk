<?php

/**
 * Class FolderFactory
 * Simple class to instantiate FolderType-objects by
 *
 *     FolderFactory::get()->init($folder_id);
 */
class FolderFactory {

    static $factory = null;

    /**
     * Returns the singleton FolderFactory object. One is enough.
     * @return FolderFactory
     */
    static public function get()
    {
        if (!self::$factory) {
            self::$factory = new FolderFactory();
        }
        return self::$factory;
    }

    /**
     * Initializes and returns a FolderType-object
     * @param array|Folder|string $data_or_id : either the data as Folder-object or array or the folder_id
     * @return FolderType
     */
    public function init($data_or_id)
    {
        if (!$data_or_id) {
            return null;
        }
        if (!is_string($data_or_id)) {
            $foldertype = $data_or_id['folder_type'];
            $foldertype = new $foldertype($data_or_id);
        } else {
            $folder = new Folder($data_or_id);
            $foldertype = $folder['folder_type'];
            $foldertype = new $foldertype($folder);
        }
        if (!is_a($folder, "FolderType")) {
            //throw new Exception("FolderType %s not possible");

            //A plugin might be disactivated or uninstalled, return a StandardFolder instead
            $foldertype = new StandardFolder($data_or_id);
        }
        return $foldertype;
    }

}