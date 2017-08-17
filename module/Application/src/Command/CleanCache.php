<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
namespace Application\Command;

use Zend\Console\Adapter\AdapterInterface;
use Zend\Console\ColorInterface;
use ZF\Console\Route;

class CleanCache
{

    /**
     * Removes a directory recursively
     *
     * @param string $dir
     * @return boolean
     */
    private function rmdirRecursive($dir)
    {
        $dir = (string) $dir;

        $files = array_diff(scandir($dir), [
            '.',
            '..'
        ]);
        foreach ($files as $file) {
            $file = $dir . DIRECTORY_SEPARATOR . $file;
            (is_dir($file)) ? rmdirRecursive($file) : unlink($file);
        }

        return rmdir($dir);
    }

    /**
     * Main routine
     *
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        $msg = 'Nothing to do';
        $appConfig = include ('config/application.config.php');
        if (is_array($appConfig) && array_key_exists('module_listener_options', $appConfig) && is_array($appConfig['module_listener_options']) && array_key_exists('cache_dir', $appConfig['module_listener_options']) && is_string($appConfig['module_listener_options']['cache_dir'])) {
            $cacheDir = $appConfig['module_listener_options']['cache_dir'];
            if ($handle = opendir($cacheDir)) {
                $cacheDir = realpath($cacheDir);

                if (! is_dir($cacheDir)) {
                    exit();
                }

                while (false !== ($entry = readdir($handle))) {
                    if (($entry === '.') || ($entry === '..')) {
                        continue;
                    }

                    $path = $cacheDir . '/' . $entry; // echo $path."\n";
                    if (is_dir($path) && (substr($entry, 0, 11) === 'tmfaa:cache')) {
                        self::rmdirRecursive($path);
                    } elseif (is_file($path) && (substr($entry, - 4, 4) === '.php') && ((substr($entry, 0, 22) === 'module-classmap-cache.') || (substr($entry, 0, 20) === 'module-config-cache.'))) {
                        unlink($path);
                    }
                }

                closedir($handle);

                $msg = 'Cache cleaned';
            }
        }
        $console->writeLine($msg, ColorInterface::NORMAL);
        return 0;
    }
}
