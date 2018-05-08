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

namespace SetupTest\Helper;

use Application\Command\CleanCache;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ZF\Console\Route;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Console\ColorInterface;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Listener\ListenerOptions;
use ReflectionClass;
use stdClass;
use phpmock\MockBuilder;

class CleanCacheTest extends TestCase
{
    private $moduleListenerOptions = [
        'config_cache_key' => 'application.config.cache',
        'module_map_cache_key' => 'application.module.cache',
        'cache_dir' => 'data/cache/', // Path as it could be set in application.config.php
    ];

    private $cacheDir = 'data/cache'; // ListenerOptions will normalize path and remove trailing slash.

    private $realPathResult = '/some/path/data/cache'; // Realpath result for mocking

    private $configCacheFile = 'data/cache/module-config-cache.application.config.cache.php';

    private $moduleMapCacheFile = 'data/cache/module-classmap-cache.application.module.cache.php';

    private $route;

    private $console;

    private $consoleWriteLineCall;

    private $cache;

    private $unsupportedCache;

    private $cleanCache;

    private $cacheAdaptersProperty;

    private $mockRealpath;

    private $mockIsDir;

    private $mockIsFile;

    private $mockUnlink;

    protected function setUp()
    {
        $this->route = $this->prophesize(Route::class);

        $consoleWriteLineCall = [];
        $this->consoleWriteLineCall = &$consoleWriteLineCall;

        $this->console = $this->prophesize(AdapterInterface::class);
        $this->console->writeLine(Argument::cetera())->will(
            function ($args) use (&$consoleWriteLineCall) {
                $consoleWriteLineCall['message'] = $args[0];
                $consoleWriteLineCall['color'] = $args[1];
            }
        );

        $this->cache = $this->prophesize(FlushableInterface::class);

        $this->unsupportedCache = new stdClass();

        $this->cleanCache = new CleanCache(
            [
                $this->cache->reveal(),
                $this->unsupportedCache,
            ],
            new ListenerOptions($this->moduleListenerOptions)
        );

        $reflection = new ReflectionClass(CleanCache::class);

        $this->cacheAdaptersProperty = $reflection->getProperty('cacheAdapters');
        $this->cacheAdaptersProperty->setAccessible(true);

        $cacheDir = $this->cacheDir;
        $realPathResult = $this->realPathResult;
        $configCacheFile = $this->configCacheFile;
        $moduleMapCacheFile = $this->moduleMapCacheFile;

        $builder = new MockBuilder();
        $builder->setNamespace($reflection->getNamespaceName())
            ->setName('realpath')
            ->setFunction(
                function ($path) use ($cacheDir, $realPathResult) {
                    return ($path === $cacheDir) ? $realPathResult : '';
                }
            );
        $this->mockRealpath = $builder->build();

        $builder = new MockBuilder();
        $builder->setNamespace($reflection->getNamespaceName())
            ->setName('is_dir')
            ->setFunction(
                function ($filename) use ($realPathResult) {
                    return $filename === $realPathResult;
                }
            );
        $this->mockIsDir = $builder->build();

        $builder = new MockBuilder();
        $builder->setNamespace($reflection->getNamespaceName())
            ->setName('is_file')
            ->setFunction(
                function ($filename) use ($configCacheFile, $moduleMapCacheFile) {
                    return ($filename === $configCacheFile || $filename === $moduleMapCacheFile);
                }
            );
        $this->mockIsFile = $builder->build();

        $builder = new MockBuilder();
        $builder->setNamespace($reflection->getNamespaceName())
            ->setName('unlink')
            ->setFunction(
                function ($filename) use ($configCacheFile, $moduleMapCacheFile) {
                    return ($filename === $configCacheFile || $filename === $moduleMapCacheFile);
                }
            );
        $this->mockUnlink = $builder->build();
    }

    protected function tearDown()
    {
        unset($this->mockUnlink);
        unset($this->mockIsFile);
        unset($this->mockIsDir);
        unset($this->mockRealpath);
        unset($this->cleanCache);
        unset($this->unsupportedCache);
        unset($this->cache);
        unset($this->console);
        unset($this->consoleWriteLineCall);
        unset($this->route);
    }

    public function testInvoke()
    {
        $cleanCache = $this->cleanCache;

        $cacheAdapters = $this->cacheAdaptersProperty->getValue($cleanCache);

        $this->assertInternalType('array', $cacheAdapters);
        $this->assertEquals(1, count($cacheAdapters));
        $this->assertInstanceOf(FlushableInterface::class, $cacheAdapters[0]);

        $this->cache->flush()->shouldBeCalledTimes(1);

        try {
            $this->mockRealpath->enable();
            $this->mockIsDir->enable();
            $this->mockIsFile->enable();
            $this->mockUnlink->enable();

            $result = $cleanCache($this->route->reveal(), $this->console->reveal());
        } finally {
            $this->mockUnlink->disable();
            $this->mockIsFile->disable();
            $this->mockIsDir->disable();
            $this->mockRealpath->disable();
        }

        $this->assertEquals(0, $result);
        $this->assertEquals(
            'Cache cleaned',
            $this->consoleWriteLineCall['message']
        );
        $this->assertEquals(ColorInterface::NORMAL, $this->consoleWriteLineCall['color']);
    }
}
