<?php
class FileHelper
{
    public static function AdjustFilename($filename)
    {
        $pathinfo = pathinfo($filename);

        if (preg_match('/\s?\((\d+)\)$/', $pathinfo['filename'], $match)) {
            $filename = str_replace($match[0], '', $pathinfo['filename']);
            $number = $match[1] + 1;
        } else {
            $filename = $pathinfo['filename'];
            $number = 1;
        }
        
        $filename .= sprintf(' (%u)', $number);

        if (!empty($pathinfo['extension'])) {
            $filename .= '.' . $pathinfo['extension'];
        }
        if (!empty($pathinfo['dirname']) && $pathinfo['dirname'] !== '.') {
            $filename = $pathinfo['dirname'] . '/' . $filename;
        }

        return $filename;
    }
    
    public function getBreadCrumbs($entry_id)
    {
        $crumbs = array();

        do {
            try {
                $entry = new DirectoryEntry($entry_id);
                $crumbs[$entry->getFile()->file_id] = array(
                    'id'   => $entry_id,
                    'name' => $entry->getFile()->filename,
                    'description' => $entry->description,
                );
                $entry_id = $this->getParentId($entry_id);
            } catch (Exception $e) {
            }
        } while ($entry_id !== $this->context_id);

        $crumbs[$this->context_id] = array(
            'id'   => $this->context_id,
            'name' => _('Hauptverzeichnis'),
            'description' => '',
        );

        return array_reverse($crumbs);
    }

    public static function GetDirectoryTree($folder_id)
    {
        // TODO Top level?
        $result = array();
        
        $folder = new StudipDirectory($folder_id);
        
        foreach ($folder->listFiles() as $entry) {
            $file = $entry->getFile();
            if ($file instanceof StudipDirectory) {
                $result[$file->file_id] = array(
                    'ref_id'      => $entry->id,
                    'filename'    => $file->filename,
                    'description' => $entry->description,
                    'children'    => self::getDirectoryTree($file->file_id),
                );
            }
        }

        return $result;
    }
}