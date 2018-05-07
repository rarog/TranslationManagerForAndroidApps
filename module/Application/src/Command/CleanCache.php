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
namespace Application\Command;

use ZF\Console\Route;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Console\ColorInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Listener\ListenerOptions;

class CleanCache
{
    /**
     * Array of cache adapters
     *
     * @var array
     */
    private $cacheAdapters;

    /**
     * Listener options from application.config.php
     *
     * @var ListenerOptions
     */
    private $listenerOptions;

    /**
     * Constructor
     *
     * @param array $cacheAdapters
     * @param ListenerOptions $listenerOptions
     */
    public function __construct(array $cacheAdapters, ListenerOptions $listenerOptions)
    {
        $this->cacheAdapters = [];
        foreach ($cacheAdapters as $adapter) {
            if ($adapter instanceof FlushableInterface) {
                $this->cacheAdapters[] = $adapter;
            }
        }

        $this->listenerOptions = $listenerOptions;
    }

    /**
     * Main routine
     *
     * @param Route $route
     * @param AdapterInterface $console
     */
    public function __invoke(Route $route, AdapterInterface $console)
    {
        foreach ($this->cacheAdapters as $adapter) {
            $adapter->flush();
        }

        $cacheDir = $this->listenerOptions->getCacheDir();
        if (! is_null($cacheDir)) {
            $cacheDir = realpath($cacheDir);

            if (is_dir($cacheDir)) {
                $configCacheFile = $this->listenerOptions->getConfigCacheFile();
                if (is_file($configCacheFile)) {
                    unlink($configCacheFile);
                }

                $moduleMapCacheFile = $this->listenerOptions->getModuleMapCacheFile();
                if (is_file($moduleMapCacheFile)) {
                    unlink($moduleMapCacheFile);
                }
            }
        }

        $console->writeLine('Cache cleaned', ColorInterface::NORMAL);
        return 0;
    }
}
