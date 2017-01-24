<?php
namespace Studip;

/**
 * Custom derived ZipArchive class with convenience methods for
 * zip archive handling.
 *
 * This replaces the before-used PCLZip vendor library.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.0
 */
class ZipArchive extends \ZipArchive
{
    /**
     * Create and open an archive. Will add .zip extension if missing.
     *
     * @static
     * @param String $filename Name of the zip archive
     * @return Studip\ZipArchive
     */
    public static function create($filename)
    {
        if (mb_strtolower(mb_substr($filename, -3)) !== 'zip') {
            $filename = $filename . '.zip';
        }

        $archive = new self();
        $archive->open($filename, self::CREATE);
        return $archive;
    }

    /**
     * Tests whether a zip archive is not corrupted.
     *
     * @static
     * @param String $filename Name of the zip archive
     * @return bool indicating whether the archive is not corrupted
     */
    public static function test($filename)
    {
        $archive = new self();
        $result = $archive->open($filename, self::CHECKCONS);

        if ($result === true) {
            $archive->close();
            return true;
        }

        return false;
    }

    /**
     * Extracts a zip archive to a certain path. Filenames will be
     * converted during this process. Malicious items containing ../
     * will be excluded.
     *
     * @static
     * @param String $filename Name of the zip archive
     * @param String $path     Local path to extract to
     * @return bool indicating whether the archive could be extracted.
     * @todo A little more error checking would be nice.
     */
    public static function extractToPath($filename, $path)
    {
        $path = rtrim($path, '/') . '/';

        $archive = new self();
        $result = $archive->open($filename);

        if ($result !== true) {
            return false;
        }

        for ($i = 0; $i < $archive->numFiles; $i += 1) {
            $zip_filename = $archive->getNameIndex($i, self::FL_UNCHANGED);
            $filename = self::convertArchiveFilename($zip_filename);

            if (mb_strpos($filename, '../') !== false) {
                continue;
            }

            if (mb_substr($zip_filename, -1) === '/') {
                mkdir($path . mb_substr($zip_filename, 0, -1));
            } else {
                $source = $archive->getStream($zip_filename);
                $target = fopen($path . $filename, 'wb+');
                stream_copy_to_stream($source, $target);
                fclose($source);
                fclose($target);
            }
        }

        $archive->close();

        return true;
    }

    /**
     * Adds a single file.
     *
     * @param String $filename  Name of the file to add
     * @param String $localname Name of the file inside the archive,
     *                          will default to $filename
     * @param int   $start      Unused but required (according to php doc)
     * @param int   $length     Unused but required (according to php doc)
     * @return false on error, $localname otherwise
     */
    public function addFile($filename, $localname = null, $start = 0, $length = 0)
    {
        $localname = self::convertLocalFilename($localname ?: basename($filename));
        return parent::addFile($filename, $localname, $start, $length)
             ? $localname
             : false;
    }

    /**
     * Adds all files from a certain path.
     *
     * @param String $path Path name to add
     * @return Array of local filenames
     * @uses Studip\ZipArchive::addFile
     */
    public function addFromPath($path, $folder = '')
    {
        $result = [];

        $files = glob(rtrim($path, '/') . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $result = array_merge(
                    $result,
                    $this->addFromPath($file, $folder . basename($file) . '/')
                );
            } else {
                $result[] = $this->addFile($file, $folder . basename($file));
            }
        }
        return array_filter($result);
    }

    /**
     * Converts the filename to a format that a zip file should be able
     * to handle.
     *
     * @param String $file Name of the input file
     * @return String containing the converted filename
     */
    private static function convertLocalFilename($filename)
    {
        return iconv('ISO-8859-1', 'IBM437', $filename);
    }

    /**
     * Converts the filename from a format that a zip file should be able
     * to handle.
     *
     * @param String $file Name of the input file from the archive
     * @return String containing the converted filename
     */
    private static function convertArchiveFilename($filename)
    {
        return iconv('IBM437', 'ISO-8859-1', $filename);
    }
}
