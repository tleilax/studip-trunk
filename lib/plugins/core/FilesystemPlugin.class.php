<?php

interface FilesystemPlugin
{

    /**
     * @return null|Navigation with title and image
     */
    public function getFileSelectNavigation();

    /**
     * Returns an URL to a page, where the filesystem can be configured.
     * @return mixed
     */
    public function filesystemConfigurationURL();

    /**
     * This method is used to get a folder-object for this plugin.
     * Not recommended but still possible is to return a Flexi_Template for the folder, if you want to
     * take care of the frontend of displaying the folder as well.
     * @param null $folder_id : folder_id of folder to get or null if you want the top-folder
     * @return FolderType|Flexi_Template
     */
    public function getFolder($folder_id = null);

    /**
     * @param $file_id : The id for the file in the given filesystem of the plugin.
     * @return array : the already prepared File just like a file-upload-array
     */
    public function getPreparedFile($file_id);



    public function hasSearch();

    /**
     * Spezielles Format
     * @return array(array(), ...)
     */
    public function getSearchParameters();

    /**
     * @param $text
     * @param array $parameters
     * @return FolderType|null
     */
    public function search($text, $parameters = array());

}
