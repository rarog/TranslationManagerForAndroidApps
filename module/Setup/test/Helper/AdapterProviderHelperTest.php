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

use PHPUnit\Framework\TestCase;
use Setup\Helper\AdapterProviderHelper;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Pdo\Pdo;

class AdapterProviderHelperTest extends TestCase
{

    const DRIVER_EMPTY = 'Pdo';

    const DRIVER_MYSQL = 'Pdo_Mysql';

    const DRIVER_PGSQL = 'Pdo_Pgsql';

    const DRIVER_SQLITE = 'Pdo_Sqlite';

    public function testSetDbAdapterEmptyArray()
    {
        $adapterProviderHelper = new AdapterProviderHelper();

        $adapterProviderHelper->setDbAdapter([]);
        $this->assertEquals(self::DRIVER_EMPTY, $adapterProviderHelper->getDbDriverName());

        $adapter = $adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());
    }

    public function testGetDbAdapterWillCallSetDbAdapterWithEmptyArray()
    {
        $adapterProviderHelper = new AdapterProviderHelper();

        $this->assertEquals(self::DRIVER_EMPTY, $adapterProviderHelper->getDbDriverName());

        $adapter = $adapterProviderHelper->getDbAdapter();

        $this->assertEquals(self::DRIVER_EMPTY, $adapterProviderHelper->getDbDriverName());

        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());
    }

    public function testSetDbAdapterInvalidConfigArray()
    {
        $adapterProviderHelper = new AdapterProviderHelper();

        $adapterProviderHelper->setDbAdapter([
            'driver' => new \stdClass()
        ]);
        $this->assertEquals(self::DRIVER_EMPTY, $adapterProviderHelper->getDbDriverName());

        $adapter = $adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());

        $adapterProviderHelper->setDbAdapter([
            'driver' => 'unknownDriver'
        ]);
        $this->assertEquals(self::DRIVER_EMPTY, $adapterProviderHelper->getDbDriverName());

        $adapter = $adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());
    }

    public function testSetDbAdapterValidConfigArray()
    {
        $adapterProviderHelper = new AdapterProviderHelper();

        $adapterProviderHelper->setDbAdapter([
            'driver' => self::DRIVER_MYSQL
        ]);
        $this->assertEquals(self::DRIVER_MYSQL, $adapterProviderHelper->getDbDriverName());

        $adapter = $adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertEquals('Mysql', $adapter->getDriver()
            ->getDatabasePlatformName());

        $adapterProviderHelper->setDbAdapter([
            'driver' => self::DRIVER_PGSQL
        ]);
        $this->assertEquals(self::DRIVER_PGSQL, $adapterProviderHelper->getDbDriverName());

        $adapter = $adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertEquals('Postgresql', $adapter->getDriver()
            ->getDatabasePlatformName());

        $adapterProviderHelper->setDbAdapter([
            'driver' => self::DRIVER_SQLITE
        ]);
        $this->assertEquals(self::DRIVER_SQLITE, $adapterProviderHelper->getDbDriverName());

        $adapter = $adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertEquals('Sqlite', $adapter->getDriver()
            ->getDatabasePlatformName());
    }
}
