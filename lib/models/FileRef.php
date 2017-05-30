<?php
/**
 * FileRef.php
 * model class for table file_refs
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
 *
 *
 * @property string id database column
 * @property string file_id database column
 * @property string folder_id database column
 * @property string user_id database column
 * @property string name database column
 * @property string downloads database column
 * @property string description database column
 * @property string license database column
 * @property string content_terms_of_use_id database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property SimpleORMap file belongs_to File
 * @property SimpleORMap folder belongs_to Folder
 * @property SimpleORMap owner belongs_to User
 * @property SimpleORMap terms_of_use belongs_to ContentTermsOfUse
 */
class FileRef extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'file_refs';
        $config['belongs_to']['file'] = [
            'class_name'  => 'File',
            'foreign_key' => 'file_id',
        ];
        $config['belongs_to']['folder'] = [
            'class_name'  => 'Folder',
            'foreign_key' => 'folder_id',
        ];
        $config['belongs_to']['owner'] = [
            'class_name'  => 'User',
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['terms_of_use'] = [
            'class_name' => 'ContentTermsOfUse',
            'foreign_key' => 'content_terms_of_use_id',
            'assoc_func' => 'findOrBuild'
        ];

        $config['additional_fields']['size'] = ['file', 'size'];
        $config['additional_fields']['mime_type'] = ['file', 'mime_type'];
        $config['additional_fields']['download_url']['get'] = 'getDownloadURL';
        $config['additional_fields']['author_name']['get'] = 'getAuthorName';
        $config['additional_fields']['is_link']['get'] = 'isLink';

        $config['registered_callbacks']['after_delete'][] = 'cbRemoveFileIfOrphaned';
        $config['registered_callbacks']['before_store'][] = 'cbMakeUniqueFilename';

        $config['notification_map']['after_create'] = 'FileRefDidCreate';
        $config['notification_map']['after_store'] = 'FileRefDidUpdate';
        $config['notification_map']['before_create'] = 'FileRefWillCreate';
        $config['notification_map']['before_store'] = 'FileRefWillUpdate';
        $config['notification_map']['after_delete'] = 'FileRefDidDelete';
        $config['notification_map']['before_delete'] = 'FileRefWillDelete';

        parent::configure($config);
    }

    public function cbRemoveFileIfOrphaned()
    {
        if (!self::countBySql('file_id = ?', [$this->file_id])) {
            File::deleteBySQL("id = ?", [$this->file_id]);
        }
    }

    public function cbMakeUniqueFilename()
    {
        if (isset($this->folder) && $this->isFieldDirty('name')) {
            $this->name = $this->folder->getUniqueName($this->name);
        }
    }

    public function getDownloadURL($dltype = 'normal')
    {
        $mode = Config::get()->SENDFILE_LINK_MODE ?: 'normal';
        $link = [];
        $type = '0';
        $file_name = $this->name;
        $file_id = $this->id;

        switch($mode) {
            case 'rewrite':
                $link[] = 'download/';
                switch ($dltype) {
                    case 'zip':
                        $link[] = 'zip/';
                        break;
                    case 'force':
                    case 'force_download':
                        $link[] = 'force_download/';
                        break;
                    default:
                        $link[] = 'normal/';
                }
                $link[] = $type . '/' . $file_name;
                break;
            default:
                $link[] = 'sendfile.php?';
                if ($dltype == 'zip'){
                    $link[] = 'zip=1&';
                } elseif (in_array($dltype,  ['force_download', 'force'])) {
                    $link[] = 'force_download=1&';
                }
                $link[] = 'type='.$type;
                $link[] = '&file_id=' . $file_id;
                $link[] = '&file_name=' . $file_name;
        }
        return URLHelper::getScriptURL(implode('', $link));
    }

    public function getAuthorName()
    {
        if (isset($this->owner)) {
            return $this->owner->getFullName('no_title');
        }
        return $this->file->author_name;
    }

    public function incrementDownloadCounter()
    {
        $this->downloads += 1;
        if (!$this->isNew()) {
            $where_query = join(' AND ' , $this->getWhereQuery());
            $query = "UPDATE `{$this->db_table}`
                      SET `downloads` = `downloads` + 1
                      WHERE {$where_query}";
            return DBManager::get()->exec($query);
        }
    }

    /**
     * Returns the license object for this file.
     *
     * @return Object (to be specified!)
     */
    public function getLicenseObject()
    {
        if (class_exists($this->license)) {
            return new $this->license();
        }
        throw new UnexpectedValueException("class: {$this->license} not found");
    }


    public function isLink()
    {
        return $this->file->url_access_type === 'redirect';
    }

    /**
     * Determines if the FileRef references an image file.
     *
     * @return bool True, if the file is an image file, false otherwise.
     */
    public function isImage()
    {
        return $this->file
            && mb_strpos($this->file->mime_type, 'image/') === 0;
    }

    /**
     * Determines if the FileRef references an audio file.
     *
     * @return bool True, if the file is an audio file, false otherwise.
     */
    public function isAudio()
    {
        return $this->file
            && mb_strpos($this->file->mime_type, 'audio/') === 0;
    }


    /**
     * Determines if the FileRef references a video file.
     *
     * @return bool True, if the file is a video file, false otherwise.
     */
    public function isVideo()
    {
        return $this->file
            && mb_strpos($this->file->mime_type, 'video/') === 0;
    }
}
