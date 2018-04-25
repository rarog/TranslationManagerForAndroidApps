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
use Prophecy\Argument;
use Setup\Helper\AdapterProviderHelper;
use Setup\Helper\DatabaseHelper;
use Zend\Config\Config;
use Zend\Mvc\I18n\Translator;
use ZfcUser\Options\ModuleOptions;
use ReflectionClass;
use RuntimeException;
use phpmock\MockBuilder;

class DatabaseHelperTest extends TestCase
{
    private $unsupportedConfig = [
        'db' => [
            'driver' => 'Pdo',
        ],
    ];

    private $mysqlConfig = [
        'db' => [
            'driver' => 'Pdo_Mysql',
        ],
    ];

    private $sqliteConfig = [
        'db' => [
            'driver' => 'Pdo_Sqlite',
        ],
    ];

    private $pgsqlConfig = [
        'db' => [
            'driver' => 'Pdo_Pgsql',
        ],
    ];

    private $adapterProvider;

    protected function setUp()
    {
        $this->adapterProvider = $this->prophesize(AdapterProviderHelper::class);
        $this->adapterProvider->setDbAdapter(Argument::type('array'))->will(function ($args) {
            $this->getDbDriverName()->willReturn($args[0]['driver']);
        });
    }

    /**
     * @return DatabaseHelper
     */
    private function getDatabaseHelper(array $config)
    {
        $setupConfig = include './module/Setup/config/setup.global.php.dist';

        return new DatabaseHelper(
            new Config(array_merge($config, $setupConfig)),
            $this->adapterProvider->reveal(),
            $this->createMock(Translator::class),
            $this->createMock(ModuleOptions::class)
        );
    }

    /**
     * @return \phpmock\Mock
     */
    private function getMockScandirMysqlSchema()
    {
        $databaseHelper = new ReflectionClass(DatabaseHelper::class);
        $builder = new MockBuilder();
        $builder->setNamespace($databaseHelper->getNamespaceName())
            ->setName('scandir')
            ->setFunction(
                function ($directory, $sorting_order = null, $context = null) {
                    return [
                        '.',
                        '..',
                        'schema.mysql.1.sql',
                        'schema.mysql.10.sql',
                        'schema.mysql.9.sql',
                        'some.file',
                    ];
                }
            );

        return $builder->build();
    }

    /**
     * Call protected/private method of an object.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     * @return mixed Method return.
     */
    private function invokeMethod($object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Set protected/private property of an object.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $propertyName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     * @return mixed Method return.
     */
    private function setPropertyValue($object, string $propertyName, $value)
    {
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($object, $value);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getInstallationSchemaRegex
     */
    public function testGetInstallationSchemaRegexUnsupported()
    {
        $databaseHelper = $this->getDatabaseHelper($this->unsupportedConfig);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Database config contains unsupported driver "\w+"./');
        $result = $this->invokeMethod($databaseHelper, 'getInstallationSchemaRegex');
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getInstallationSchemaRegex
     */
    public function testGetInstallationSchemaRegexMysql()
    {
        $databaseHelper = $this->getDatabaseHelper($this->mysqlConfig);
        $result = $this->invokeMethod($databaseHelper, 'getInstallationSchemaRegex');
        $this->assertEquals('/schema\.mysql\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getInstallationSchemaRegex
     */
    public function testGetInstallationSchemaRegexSqlite()
    {
        $databaseHelper = $this->getDatabaseHelper($this->sqliteConfig);
        $result = $this->invokeMethod($databaseHelper, 'getInstallationSchemaRegex');
        $this->assertEquals('/schema\.sqlite\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getInstallationSchemaRegex
     */
    public function testGetInstallationSchemaRegexPgsql()
    {
        $databaseHelper = $this->getDatabaseHelper($this->pgsqlConfig);
        $result = $this->invokeMethod($databaseHelper, 'getInstallationSchemaRegex');
        $this->assertEquals('/schema\.pgsql\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaRegex
     */
    public function testGetUpdateSchemaRegexUnsupported()
    {
        $databaseHelper = $this->getDatabaseHelper($this->unsupportedConfig);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Database config contains unsupported driver "\w+"./');
        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaRegex');
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaRegex
     */
    public function testGetUpdateSchemaRegexMysql()
    {
        $databaseHelper = $this->getDatabaseHelper($this->mysqlConfig);
        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaRegex');
        $this->assertEquals('/schemaUpdate\.mysql\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaRegex
     */
    public function testGetUpdateSchemaRegexSqlite()
    {
        $databaseHelper = $this->getDatabaseHelper($this->sqliteConfig);
        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaRegex');
        $this->assertEquals('/schemaUpdate\.sqlite\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaRegex
     */
    public function testGetUpdateSchemaRegexPgsql()
    {
        $databaseHelper = $this->getDatabaseHelper($this->pgsqlConfig);
        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaRegex');
        $this->assertEquals('/schemaUpdate\.pgsql\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getSchemaInstallationFilepath
     */
    public function testGetSchemaInstallationFilepath()
    {
        $mock = $this->getMockScandirMysqlSchema();

        $databaseHelper = $this->getDatabaseHelper($this->mysqlConfig);

        $mock->enable();
        $result = $this->invokeMethod($databaseHelper, 'getSchemaInstallationFilepath');
        $mock->disable();
        $maxVersion = $databaseHelper->getLastParsedSchemaVersion();

        $this->assertEquals('data/schema/schema.mysql.10.sql', $result);
        $this->assertEquals(10, $maxVersion);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getSchemaInstallationFilepath
     */
    public function testGetSchemaInstallationFilepathException()
    {
        $mock = $this->getMockScandirMysqlSchema();

        $databaseHelper = $this->getDatabaseHelper($this->sqliteConfig);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No valid installation schema file found.');

        $mock->enable();
        $this->invokeMethod($databaseHelper, 'getSchemaInstallationFilepath');
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::canConnect
     */
    public function testCanConnect()
    {
        $databaseHelper = $this->getDatabaseHelper($this->unsupportedConfig);

        $this->adapterProvider->canConnect()->willReturn(false);
        $this->assertEquals(false, $databaseHelper->canConnect());
        $this->adapterProvider->canConnect()->willReturn(true);
        $this->assertEquals(true, $databaseHelper->canConnect());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getLastParsedSchemaVersion
     */
    public function testGetLastParsedSchemaVersion()
    {
        $schemaVersion1 = 10;
        $schemaVersion2 = 1;

        $databaseHelper = $this->getDatabaseHelper($this->unsupportedConfig);

        $this->setPropertyValue($databaseHelper, 'lastParsedSchemaVersion', $schemaVersion1);
        $this->assertEquals($schemaVersion1, $databaseHelper->getLastParsedSchemaVersion());

        $this->setPropertyValue($databaseHelper, 'lastParsedSchemaVersion', $schemaVersion2);
        $this->assertEquals($schemaVersion2, $databaseHelper->getLastParsedSchemaVersion());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getLastStatus
     */
    public function testGetLastStatus()
    {
        $status1 = 42;
        $status2 = 123;

        $databaseHelper = $this->getDatabaseHelper($this->unsupportedConfig);

        $this->setPropertyValue($databaseHelper, 'lastStatus', $status1);
        $this->assertEquals($status1, $databaseHelper->getLastStatus());

        $this->setPropertyValue($databaseHelper, 'lastStatus', $status2);
        $this->assertEquals($status2, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getLastMessage
     */
    public function testGetLastMessage()
    {
        $message1 = 'Message 1';
        $message2 = 'Another message 2';

        $databaseHelper = $this->getDatabaseHelper($this->unsupportedConfig);

        $this->setPropertyValue($databaseHelper, 'lastMessage', $message1);
        $this->assertEquals($message1, $databaseHelper->getLastMessage());

        $this->setPropertyValue($databaseHelper, 'lastMessage', $message2);
        $this->assertEquals($message2, $databaseHelper->getLastMessage());
    }
}
