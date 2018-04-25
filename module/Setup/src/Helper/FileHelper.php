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

namespace Setup\Helper;

use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;

class FileHelper
{
    /**
     * PhpArray config writer
     *
     * @var \Zend\Config\Writer\PhpArray
     */
    private $configWriter;

    /**
     * Gets an instance of PhpArray config writer
     *
     * @return \Zend\Config\Writer\PhpArray
     */
    private function getConfigWriter()
    {
        if (! $this->configWriter instanceof PhpArray) {
            $this->configWriter = new PhpArray();
            $this->configWriter->setUseBracketArraySyntax(true);
        }
        return $this->configWriter;
    }

    /**
     * Normalize a path by removing trailing slashes
     *
     * @param  string $path
     * @return string
     */
    public static function normalizePath(string $path)
    {
        $path = rtrim($path, '/');
        $path = rtrim($path, '\\');
        return $path;
    }

    /**
     * Helper function to replace config blocks in file
     *
     * @param string $filePath
     * @param array $newConfigArray
     */
    public function replaceConfigInFile(string $filePath, array $newConfigArray)
    {
        $config = null;

        // Reading current content of config file
        if (is_file($filePath)) {
            $config = include($filePath);
        }

        if (! is_array($config)) {
            $config = [];
        }

        $config = array_merge($config, $newConfigArray);

        $this->getConfigWriter()->toFile($filePath, new Config($config, false));
    }
}
