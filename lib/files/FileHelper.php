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
}