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

namespace ApplicationTest\Factory\Command;

use Application\Command\CleanCache;
use Application\Factory\Command\CleanCacheFactory;
use PHPUnit\Framework\TestCase;
use Zend\Cache\Storage\Adapter\BlackHole;
use Zend\ServiceManager\ServiceManager;

class CleanCacheFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new CleanCacheFactory();

        $serviceManager = new ServiceManager();

        $serviceManager->setService(
            'config',
            [
                'caches' => [
                    'Cache\Persistent' => [
                        'adapter' => BlackHole::class,
                    ],
                ],
            ]
        );

        $serviceManager->setService(
            'ApplicationConfig',
            [
                'module_listener_options' => [],
            ]
        );

        $cleanCache = $factory($serviceManager, null);
        $this->assertInstanceOf(CleanCache::class, $cleanCache);
    }
}
