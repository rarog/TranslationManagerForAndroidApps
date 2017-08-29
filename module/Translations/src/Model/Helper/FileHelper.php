<?php
/**
 * Translation Manager for Android Apps
 *
 * PHP version 7
 *
 * @category  PHP
 * @package   TranslationManagerForAndroidApps
 * @author    Andrej Sinicyn <rarogit@gmail.com>
 * @copyright 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps
 */

namespace Translations\Model\Helper;

class FileHelper
{
    /**
     * Concatenates 2 paths together
     *
     * @param  string $path1
     * @param  string $path2
     * @return string
     */
    public static function concatenatePath($path1, $path2)
    {
        $path1 = self::normalizePath($path1);

        if (($path2 = self::normalizePath($path2, true)) !== '') {
            $path1 .= DIRECTORY_SEPARATOR . $path2;
        }

        return $path1;
    }


    /**
     * Checks if file is valid XML file with root node <resources>
     *
     * @param  string $filePath
     * @return boolean
     */
    public static function isFileValidResource($filePath)
    {
        $filePath = (string) $filePath;

        if (file_exists($filePath) && is_file($filePath)) {
            try {
                libxml_use_internal_errors(true);
                $xml = simplexml_load_file($filePath);

                if (($xml !== false) && ($xml->getName() === 'resources')) {
                    return true;
                }
            } finally {
                libxml_use_internal_errors(false);
            }
        }

        return false;
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     * @param  bool $trimLeft
     * @return string
     */
    public static function normalizePath($path, $trimLeft = false)
    {
        $path = (string) $path;
        $trimLeft = (bool) $trimLeft;

        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');

        if ($trimLeft) {
            $path = ltrim($path, '/');
            $path = ltrim($path, '\\');
        }

        return $path;
    }


    /**
     * Removes a directory with all files and subdirectories
     *
     * @param  string $dir
     * @param  bool $onlyContent
     * @return boolean
     */
    public static function rmdirRecursive($dir, $onlyContent = false)
    {
        $dir = (string) $dir;
        $onlyContent = (bool) $onlyContent;

        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            $file = $dir . DIRECTORY_SEPARATOR . $file;
            (is_dir($file)) ? self::rmdirRecursive($file) : unlink($file);
        }

        if ($onlyContent) {
            return true;
        }
        return rmdir($dir);
    }
}
