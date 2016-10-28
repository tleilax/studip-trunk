<?php

interface FilesystemPlugin
{

    /**
     * @return null|Navigation with title and image
     */
    public function getFileSelectNavigation();

    /**
     * @param null $folder_id : folder_id of folder to get or null if it should be the top-folder
     * @return Folder-object
     */
    public function getFolder($folder_id = null);

    /**
     * @param $file_id : The id for the file in the given filesystem of the plugin.
     * @return File : the already prepared File with all metadata and the binary data at place
     */
    public function getPreparedFile($file_id);
}
