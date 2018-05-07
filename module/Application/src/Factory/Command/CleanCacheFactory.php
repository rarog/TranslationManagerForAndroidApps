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

namespace Application\Factory\Command;

use Application\Command\CleanCache;
use Interop\Container\ContainerInterface;
use Zend\Cache\Storage\FlushableInterface;
use Zend\ModuleManager\Listener\ListenerOptions;
use Zend\ServiceManager\Factory\FactoryInterface;

class CleanCacheFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $cacheArray = [];
        $cacheDir = '';

        $config = $container->has('config') ? $container->get('config') : [];
        if (array_key_exists('caches', $config)) {
            foreach ($config['caches'] as $key => $val) {
                if ($container->has($key) && (($cache = $container->get($key)) instanceof FlushableInterface)) {
                    $cacheArray[] = $cache;
                }
            }
        }

        $moduleListenerOptions = null;
        $appConfig = $container->has('ApplicationConfig') ? $container->get('ApplicationConfig') : [];
        if (is_array($appConfig) && array_key_exists('module_listener_options', $appConfig)) {
            $moduleListenerOptions = $appConfig['module_listener_options'];
        }

        return new CleanCache(
            $cacheArray,
            new ListenerOptions($moduleListenerOptions)
        );
    }
}
