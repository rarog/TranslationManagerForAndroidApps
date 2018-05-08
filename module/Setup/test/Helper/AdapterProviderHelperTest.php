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
use Zend\Db\Adapter\Driver\ConnectionInterface;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\Pdo\Pdo;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\Column\BigInteger;
use Zend\Db\Sql\Ddl\Column\Varchar;
use Zend\Db\Sql\Sql;
use Exception;
use ReflectionClass;
use RuntimeException;
use Zend\Db\Sql\Select;

class AdapterProviderHelperTest extends TestCase
{
    const DRIVER_EMPTY = 'Pdo';

    const DRIVER_MYSQL = 'Pdo_Mysql';

    const DRIVER_PGSQL = 'Pdo_Pgsql';

    const DRIVER_SQLITE = 'Pdo_Sqlite';

    private $adapterProviderHelper;

    private $dbAdapterProperty;

    private $connection;

    private $driver;

    private $adapter;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->adapterProviderHelper = new AdapterProviderHelper();

        $reflection = new ReflectionClass(AdapterProviderHelper::class);

        $this->dbAdapterProperty = $reflection->getProperty('dbAdapter');
        $this->dbAdapterProperty->setAccessible(true);

        $this->connection = $this->prophesize(ConnectionInterface::class);

        $this->driver = $this->prophesize(DriverInterface::class);
        $this->driver->checkEnvironment()->willReturn(true);
        $this->driver->getConnection()->willReturn($this->connection);

        $this->adapter = $this->prophesize(Adapter::class);
        $this->adapter->getDriver()->willReturn($this->driver);
        $this->adapter->getDriver()->willReturn($this->driver);
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->adapter);
        unset($this->driver);
        unset($this->connection);
        unset($this->dbAdapterProperty);
        unset($this->adapterProviderHelper);
    }

    public function testSetDbAdapterEmptyArray()
    {
        $this->adapterProviderHelper->setDbAdapter([]);
        $this->assertEquals(self::DRIVER_EMPTY, $this->adapterProviderHelper->getDbDriverName());

        $adapter = $this->adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());
    }

    public function testGetDbAdapterWillCallSetDbAdapterWithEmptyArray()
    {
        $this->assertEquals(self::DRIVER_EMPTY, $this->adapterProviderHelper->getDbDriverName());

        $adapter = $this->adapterProviderHelper->getDbAdapter();

        $this->assertEquals(self::DRIVER_EMPTY, $this->adapterProviderHelper->getDbDriverName());

        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());
    }

    public function testSetDbAdapterInvalidConfigArray()
    {
        $this->adapterProviderHelper->setDbAdapter([
            'driver' => new \stdClass()
        ]);
        $this->assertEquals(self::DRIVER_EMPTY, $this->adapterProviderHelper->getDbDriverName());

        $adapter = $this->adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());

        $this->adapterProviderHelper->setDbAdapter([
            'driver' => 'unknownDriver'
        ]);
        $this->assertEquals(self::DRIVER_EMPTY, $this->adapterProviderHelper->getDbDriverName());

        $adapter = $this->adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertInstanceOf(Pdo::class, $adapter->getDriver());
        $this->assertEmpty($adapter->getDriver()
            ->getDatabasePlatformName());
    }

    public function testSetDbAdapterValidConfigArray()
    {
        $this->adapterProviderHelper->setDbAdapter([
            'driver' => self::DRIVER_MYSQL
        ]);
        $this->assertEquals(self::DRIVER_MYSQL, $this->adapterProviderHelper->getDbDriverName());

        $adapter = $this->adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertEquals('Mysql', $adapter->getDriver()
            ->getDatabasePlatformName());

        $this->adapterProviderHelper->setDbAdapter([
            'driver' => self::DRIVER_PGSQL
        ]);
        $this->assertEquals(self::DRIVER_PGSQL, $this->adapterProviderHelper->getDbDriverName());

        $adapter = $this->adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertEquals('Postgresql', $adapter->getDriver()
            ->getDatabasePlatformName());

        $this->adapterProviderHelper->setDbAdapter([
            'driver' => self::DRIVER_SQLITE
        ]);
        $this->assertEquals(self::DRIVER_SQLITE, $this->adapterProviderHelper->getDbDriverName());

        $adapter = $this->adapterProviderHelper->getDbAdapter();
        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertEquals('Sqlite', $adapter->getDriver()
            ->getDatabasePlatformName());
    }

    public function testSetDbAdapterResetsSqlProperty()
    {
        $this->assertAttributeEmpty('sql', $this->adapterProviderHelper);

        $this->adapterProviderHelper->getSql();
        $this->assertAttributeInstanceOf(Sql::class, 'sql', $this->adapterProviderHelper);

        $this->adapterProviderHelper->setDbAdapter([]);
        $this->assertAttributeEmpty('sql', $this->adapterProviderHelper);
    }

    public function testCanConnectReturnsFalseOnException()
    {
        $this->dbAdapterProperty->setValue($this->adapterProviderHelper, $this->adapter->reveal());

        $this->adapter->getDriver()->willThrow(new Exception('Controlled Exception during test'));

        $this->assertEquals(false, $this->adapterProviderHelper->canConnect());
    }

    public function testCanConnect()
    {
        $this->dbAdapterProperty->setValue($this->adapterProviderHelper, $this->adapter->reveal());

        $this->connection->connect()->will(function () {
        });

        $this->connection->isConnected()->willReturn(false);
        $this->assertEquals(false, $this->adapterProviderHelper->canConnect());

        $this->connection->isConnected()->willReturn(true);
        $this->assertEquals(true, $this->adapterProviderHelper->canConnect());
    }

    public function testGetSql()
    {
        $this->assertAttributeEmpty('sql', $this->adapterProviderHelper);
        $this->assertInstanceOf(Sql::class, $this->adapterProviderHelper->getSql());
        $this->assertAttributeInstanceOf(Sql::class, 'sql', $this->adapterProviderHelper);
    }

    public function testExecuteSqlStatement()
    {
        // This test exploits the fact that with default configuration in-memory SQLite database is created.
        $this->adapterProviderHelper->setDbAdapter([
            'driver' => self::DRIVER_SQLITE
        ]);

        // Testing by creating table, which is SqlInterface...
        $table = new CreateTable('some_table');
        $table->addColumn(new BigInteger('id'))
            ->addColumn(new Varchar('another_column', 32));
        $this->adapterProviderHelper->executeSqlStatement($table);

        // ...then inserting data by execution of raw strings...
        $this->adapterProviderHelper->executeSqlStatement(
            'INSERT INTO some_table (id, another_column) VALUES (1, \'First row\');' . "\n" .
            'INSERT INTO some_table (id, another_column) VALUES (6, \'Another row\');'
        );

        $expectedRow1 = [
            'id' => 1,
            'another_column' => 'First row'
        ];
        $expectedRow2 = [
            'id' => 1,
            'another_column' => 'First row'
        ];

        // ...which are by getting results via prepared statement
        $result = $this->adapterProviderHelper->executeSqlStatement(new Select('some_table'));
        $this->assertEquals(2, $result->count());
        $this->assertEquals($expectedRow1, $result->current());
        $this->assertEquals($expectedRow2, $result->current());

        // Finally provoking exception by submitting unsupported parameter like an integer.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp(
            '/Function executeSqlStatement was called with unsupport parameter of type "\w+"./'
        );
        $this->adapterProviderHelper->executeSqlStatement(9);
    }
}
