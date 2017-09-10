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

namespace TranslationsTest\Model;

use PHPUnit\Framework\TestCase;
use phpmock\MockBuilder;
use Setup\Model\DatabaseHelper;

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

    /**
     * @return DatabaseHelper
     */
    private function getDatabaseHelper(array $config)
    {
        $setupConfig = include './module/Setup/config/setup.global.php.dist';

        return new DatabaseHelper(
            new \Zend\Config\Config(array_merge($config, $setupConfig)),
            $this->createMock(\Zend\Mvc\I18n\Translator::class),
            $this->createMock(\ZfcUser\Options\ModuleOptions::class)
        );
    }

    /**
     * @return \phpmock\Mock
     */
    private function getMockScandirMysqlSchema()
    {
        $builder = new MockBuilder();
        $builder->setNamespace('Setup\Model')
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
     * Call protected/private method of a class.
     *
     * @param object $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     * @return mixed Method return.
     */
    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @covers \Setup\Model\DatabaseHelper::getInstallationSchemaRegex
     * @expectedException RuntimeException
     * @expectedExceptionMessageRegExp /Database config contains unsupported driver "\w+"./
     */
    public function testGetInstallationSchemaRegexUnsupported()
    {
        $databaseHelper = $this->getDatabaseHelper($this->unsupportedConfig);
        $result = $this->invokeMethod($databaseHelper, 'getInstallationSchemaRegex');
        $this->assertEquals('/schema\.mysql\.(\d)\.sql/', $result);
    }

    /**
     * @covers \Setup\Model\DatabaseHelper::getInstallationSchemaRegex
     */
    public function testGetInstallationSchemaRegexMysql()
    {
        $databaseHelper = $this->getDatabaseHelper($this->mysqlConfig);
        $result = $this->invokeMethod($databaseHelper, 'getInstallationSchemaRegex');
        $this->assertEquals('/schema\.mysql\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Model\DatabaseHelper::getInstallationSchemaRegex
     */
    public function testGetInstallationSchemaRegexSqlite()
    {
        $databaseHelper = $this->getDatabaseHelper($this->sqliteConfig);
        $result = $this->invokeMethod($databaseHelper, 'getInstallationSchemaRegex');
        $this->assertEquals('/schema\.sqlite\.(\d+)\.sql/', $result);
    }

    /**
     * @covers \Setup\Model\DatabaseHelper::getSchemaInstallationFilepath
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
     * @covers \Setup\Model\DatabaseHelper::getSchemaInstallationFilepath
     * @expectedException RuntimeException
     * @expectedExceptionMessage No valid installation schema file found.
     */
    public function testGetSchemaInstallationFilepathException()
    {
        $mock = $this->getMockScandirMysqlSchema();

        $databaseHelper = $this->getDatabaseHelper($this->sqliteConfig);

        $mock->enable();
        try {
            $result = $this->invokeMethod($databaseHelper, 'getSchemaInstallationFilepath');
        } finally {
            $mock->disable();
        }
    }
}
