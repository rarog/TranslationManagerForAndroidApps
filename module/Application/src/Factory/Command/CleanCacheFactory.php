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

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class CleanCacheFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $cacheArray = [];

        $config = $container->get('Config');
        if (array_key_exists('caches', $config)) {
            foreach ($config['caches'] as $key => $val) {
                if ($container->has($key) && (($cache = $container->get($key)) instanceof \Zend\Cache\Storage\FlushableInterface)) {
                    $cacheArray[] = $cache;
                }
            }
        }

        return new \Application\Command\CleanCache(
            $cacheArray
        );
    }
}
