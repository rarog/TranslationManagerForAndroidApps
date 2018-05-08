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
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\PreparableSqlInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Ddl\AlterTable;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Mvc\I18n\Translator;
use ZfcUser\Options\ModuleOptions;
use Exception;
use ReflectionClass;
use RuntimeException;
use phpmock\MockBuilder;

class DatabaseHelperTest extends TestCase
{
    const DEFAULT_TEST_SETUPID = 'TestSetupId';

    private $defaultConfig = [
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

    private $schemaInstalledResult = [
        [
            'setupid' => self::DEFAULT_TEST_SETUPID,
        ]
    ];

    private $adapterProvider;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $select = $this->prophesize(Select::class);
        $select->columns(Argument::cetera())->willReturn($select->reveal());
        $select->where(Argument::cetera())->willReturn($select->reveal());

        $insert = $this->prophesize(Insert::class);
        $insert->columns(Argument::cetera())->willReturn($insert->reveal());
        $insert->values(Argument::cetera())->willReturn($insert->reveal());

        $statement = $this->prophesize(StatementInterface::class);

        $sql = $this->prophesize(Sql::class);
        $sql->select(Argument::any())->willReturn($select->reveal());
        $sql->insert(Argument::any())->willReturn($insert->reveal());
        $sql->prepareStatementForSqlObject(Argument::type(PreparableSqlInterface::class))->willReturn(
            $statement->reveal()
        );

        $this->adapterProvider = $this->prophesize(AdapterProviderHelper::class);
        $this->adapterProvider->setDbAdapter(Argument::type('array'))->will(function ($args) {
            $this->getDbDriverName()->willReturn($args[0]['driver']);
        });
        $this->adapterProvider->getSql()->willReturn($sql->reveal());
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->adapterProvider);
    }

    /**
     * @return DatabaseHelper
     */
    private function getDatabaseHelper(array $config)
    {
        $setupConfig = include './module/Setup/config/setup.global.php.dist';
        $config = array_merge_recursive($setupConfig, $config);
        $config['setup']['setup_id'] = self::DEFAULT_TEST_SETUPID;

        return new DatabaseHelper(
            new Config($config),
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
     * @param bool $expectedResult Expected result
     * @return \phpmock\Mock
     */
    private function getMockFileExistsMysqlSchema(callable $function)
    {
        $databaseHelper = new ReflectionClass(DatabaseHelper::class);
        $builder = new MockBuilder();
        $builder->setNamespace($databaseHelper->getNamespaceName())
            ->setName('file_exists')
            ->setFunction($function);

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
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);
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
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaPattern
     */
    public function testGetUpdateSchemaRegexUnsupported()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageRegExp('/Database config contains unsupported driver "\w+"./');
        $this->invokeMethod($databaseHelper, 'getUpdateSchemaPattern', [
            0
        ]);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaPattern
     */
    public function testGetUpdateSchemaPatternMysql()
    {
        $databaseHelper = $this->getDatabaseHelper($this->mysqlConfig);

        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaPattern', [
            3
        ]);
        $this->assertEquals('schemaUpdate.mysql.3.sql', $result);

        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaPattern', [
            42
        ]);
        $this->assertEquals('schemaUpdate.mysql.42.sql', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaPattern
     */
    public function testGetUpdateSchemaPatternSqlite()
    {
        $databaseHelper = $this->getDatabaseHelper($this->sqliteConfig);

        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaPattern', [
            3
        ]);
        $this->assertEquals('schemaUpdate.sqlite.3.sql', $result);

        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaPattern', [
            42
        ]);
        $this->assertEquals('schemaUpdate.sqlite.42.sql', $result);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::getUpdateSchemaPattern
     */
    public function testGetUpdateSchemaRegexPgsql()
    {
        $databaseHelper = $this->getDatabaseHelper($this->pgsqlConfig);

        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaPattern', [
            3
        ]);
        $this->assertEquals('schemaUpdate.pgsql.3.sql', $result);

        $result = $this->invokeMethod($databaseHelper, 'getUpdateSchemaPattern', [
            42
        ]);
        $this->assertEquals('schemaUpdate.pgsql.42.sql', $result);
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
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

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

        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

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

        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

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

        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $this->setPropertyValue($databaseHelper, 'lastMessage', $message1);
        $this->assertEquals($message1, $databaseHelper->getLastMessage());

        $this->setPropertyValue($databaseHelper, 'lastMessage', $message2);
        $this->assertEquals($message2, $databaseHelper->getLastMessage());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSchemaInstalled
     */
    public function testIsSchemaInstalledCantConnect()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $this->adapterProvider->canConnect()->willReturn(false);

        $this->assertEquals(false, $databaseHelper->isSchemaInstalled());
        $this->assertEquals(DatabaseHelper::NODBCONNECTION, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSchemaInstalled
     */
    public function testIsSchemaInstalledStatementCatchesException()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->willThrow(new Exception('Some exception'));

        $this->assertEquals(false, $databaseHelper->isSchemaInstalled());
        $this->assertEquals(DatabaseHelper::DBNOTINSTALLEDORTABLENOTPRESENT, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSchemaInstalled
     */
    public function testIsSchemaInstalledResultIsNull()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(function () {
            $result = new ResultSet();
            $result->initialize([]);

            return $result;
        });

        $this->assertEquals(false, $databaseHelper->isSchemaInstalled());
        $this->assertEquals(DatabaseHelper::TABLEEXISTSBUTISEMPTY, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSchemaInstalled
     */
    public function testIsSchemaInstalledResultDoesntHaveSetupIdKeyInArray()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(function () {
            $result = new ResultSet();
            $result->initialize([
                []
            ]);

            return $result;
        });

        $this->assertEquals(false, $databaseHelper->isSchemaInstalled());
        $this->assertEquals(DatabaseHelper::TABLEEXISTSBUTHASWRONGSTRUCTURE, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSchemaInstalled
     */
    public function testIsSchemaInstalledResultHasWrongSetupId()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(function () {
            $result = new ResultSet();
            $result->initialize([
                [
                    'setupid' => self::DEFAULT_TEST_SETUPID . 'Wrong',
                ]
            ]);

            return $result;
        });

        $this->assertEquals(false, $databaseHelper->isSchemaInstalled());
        $this->assertEquals(DatabaseHelper::TABLEEXISTSBUTHASWRONGSETUPID, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSchemaInstalled
     */
    public function testIsSchemaInstalledSetupIdCorrect()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $schemaInstalledResult = $this->schemaInstalledResult;

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(function () use ($schemaInstalledResult) {
            $result = new ResultSet();
            $result->initialize($schemaInstalledResult);

            return $result;
        });

        $this->assertEquals(true, $databaseHelper->isSchemaInstalled());
        $this->assertEquals(DatabaseHelper::DBSCHEMASEEMSTOBEINSTALLED, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSetupComplete
     */
    public function testIsSetupCompleteReturnsFalseIfNotSchemaInstalled()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $this->adapterProvider->canConnect()->willReturn(false);

        $this->assertEquals(false, $databaseHelper->isSetupComplete());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSetupComplete
     */
    public function testIsSetupCompleteCatchesExceptionReturnsFalse()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $schemaInstalledResult = $this->schemaInstalledResult;

        $executeCallCount = 0;

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(
            function () use ($schemaInstalledResult, &$executeCallCount) {
                if ($executeCallCount === 0) {
                    $result = new ResultSet();
                    $result->initialize($schemaInstalledResult);
                    $executeCallCount ++;

                    return $result;
                } else {
                    throw new Exception('Some exception');
                }
            }
        );

        $this->assertEquals(false, $databaseHelper->isSetupComplete());
        $this->assertEquals(DatabaseHelper::SOMETHINGISWRONGWITHWITHUSERTABLE, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSetupComplete
     */
    public function testIsSetupCompleteReturnsFalseIfNumberOfUsersLessThanOne()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $schemaInstalledResult = $this->schemaInstalledResult;

        $executeCallCount = 0;
        $numberOfUsers = 0;

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(
            function () use ($schemaInstalledResult, &$executeCallCount, $numberOfUsers) {
                $result = new ResultSet();

                if ($executeCallCount === 0) {
                    $result->initialize($schemaInstalledResult);
                } else {
                    $result->initialize([
                        [
                            'count' => $numberOfUsers
                        ]
                    ]);
                }

                $executeCallCount ++;

                return $result;
            }
        );

        $this->assertEquals(false, $databaseHelper->isSetupComplete());
        $this->assertEquals(DatabaseHelper::USERTABLESEEMSTOBEOK, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::isSetupComplete
     */
    public function testIsSetupCompleteReturnsTrueIfNumberOfUsersGreaterZero()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $schemaInstalledResult = $this->schemaInstalledResult;

        $executeCallCount = 0;
        $numberOfUsers = 42;

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(
            function () use ($schemaInstalledResult, &$executeCallCount, $numberOfUsers) {
                $result = new ResultSet();

                if ($executeCallCount === 0) {
                    $result->initialize($schemaInstalledResult);
                } else {
                    $result->initialize([
                        [
                            'count' => $numberOfUsers
                        ]
                    ]);
                }

                $executeCallCount ++;

                return $result;
            }
        );

        $this->assertEquals(true, $databaseHelper->isSetupComplete());
        $this->assertEquals(DatabaseHelper::USERTABLESEEMSTOBEOK, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::setDbConfigArray
     */
    public function testSetDbConfigArray()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);

        $adapterProvider = $this->adapterProvider->reveal();

        $this->assertEquals('Pdo', $adapterProvider->getDbDriverName());

        $databaseHelper->setDbConfigArray($this->mysqlConfig['db']);

        $this->assertEquals('Pdo_Mysql', $adapterProvider->getDbDriverName());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::updateSchema
     */
    public function testUpdateSchemaSetupIncomplete()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);
        $schemaInstalledResult = $this->schemaInstalledResult;

        $this->adapterProvider->canConnect()->willReturn(false);

        $databaseHelper->updateSchema();

        $this->assertEquals(DatabaseHelper::SETUPINCOMPLETE, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::updateSchema
     */
    public function testUpdateSchemaIncorrectVersionInformation()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);
        $schemaInstalledResult = $this->schemaInstalledResult;

        $executeCallCount = 0;

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(
            function () use ($schemaInstalledResult, &$executeCallCount) {
                $result = new ResultSet();

                if ($executeCallCount === 0) {
                    $result->initialize($schemaInstalledResult);
                } elseif ($executeCallCount === 1) {
                    $result->initialize([
                        [
                            'count' => 1
                        ]
                    ]);
                } else {
                    $result->initialize([]);
                }

                $executeCallCount ++;

                return $result;
            }
        );

        $databaseHelper->updateSchema();

        $this->assertEquals(DatabaseHelper::SETUPINCOMPLETE, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::updateSchema
     */
    public function testUpdateSchemaExecuteSqlStatementThrowsException()
    {
        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);
        $schemaInstalledResult = $this->schemaInstalledResult;

        $executeCallCount = 0;

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(
            function () use ($schemaInstalledResult, &$executeCallCount) {
                $result = new ResultSet();

                if ($executeCallCount === 0) {
                    $result->initialize($schemaInstalledResult);
                } elseif ($executeCallCount === 1) {
                    $result->initialize([
                        [
                            'count' => 1
                        ]
                    ]);
                } else {
                    throw new Exception('Some exception');
                }

                $executeCallCount ++;

                return $result;
            }
        );

        $databaseHelper->updateSchema();

        $this->assertEquals(DatabaseHelper::SETUPINCOMPLETE, $databaseHelper->getLastStatus());
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::updateSchema
     */
    public function testUpdateSchemaSetupComplete()
    {
        $databaseHelper = $this->getDatabaseHelper($this->mysqlConfig);
        $schemaInstalledResult = $this->schemaInstalledResult;

        $executeCallCount = 0;

        $this->adapterProvider->canConnect()->willReturn(true);
        $this->adapterProvider->executeSqlStatement(Argument::any())->will(
            function () use ($schemaInstalledResult, &$executeCallCount) {
                $result = new ResultSet();

                if ($executeCallCount === 0) {
                    $result->initialize($schemaInstalledResult);
                } elseif ($executeCallCount === 1) {
                    $result->initialize([
                        [
                            'count' => 1
                        ]
                    ]);
                } else {
                    $result->initialize([
                        [
                            'version' => 10
                        ]
                    ]);
                }

                $executeCallCount ++;

                return $result;
            }
        );

        $fileExistsCallCount = 0;

        $mockFileExists = $this->getMockFileExistsMysqlSchema(
            function ($filename) use (&$fileExistsCallCount) {
                $fileExistsCallCount++;
                return false;
            }
        );
        $mockFileExists->enable();

        $databaseHelper->updateSchema();

        $mockFileExists->disable();

        $this->assertEquals(DatabaseHelper::CURRENTSCHEMAISLATEST, $databaseHelper->getLastStatus());
        $this->assertEquals(1, $fileExistsCallCount);

        $executeCallCount = 0;
        $fileExistsCallCount = 0;

        $mockFileExists = $this->getMockFileExistsMysqlSchema(
            function ($filename) use (&$fileExistsCallCount) {
                $fileExistsCallCount++;
                if (($filename === 'data/schema/schemaUpdate.mysql.11.sql')
                    || ($filename === 'data/schema/schemaUpdate.mysql.12.sql')) {
                    return true;
                }
                return false;
            }
        );
        $mockFileExists->enable();

        $databaseHelperReflection = new ReflectionClass(DatabaseHelper::class);
        $builder = new MockBuilder();
        $builder->setNamespace($databaseHelperReflection->getNamespaceName())
            ->setName('file_get_contents')
            ->setFunction(
                function ($filename) {
                    return '';
                }
            );

        $mockFileGetContents = $builder->build();
        $mockFileGetContents->enable();

        $databaseHelper->updateSchema();

        $mockFileGetContents->disable();
        $mockFileExists->disable();

        $this->assertEquals(DatabaseHelper::SCHEMAUPDATED, $databaseHelper->getLastStatus());
        $this->assertEquals(3, $fileExistsCallCount);
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::parseAddColumn
     */
    public function testParseAddColumn()
    {
        $input1 = [];
        $expectedResult1 = 0;

        $input2 = [
            'addColumn' => [
                [
                    'name' => 'columnWithNoType',
                ],
                [
                    'type' => 'unknownType',
                    'name' => 'columnWithInvalidType',
                ],
                [
                    'type' => 'Integer',
                    // has no name
                ],
                [
                    'type' => 'Integer',
                    'name' => 1,
                    // name isn't string
                ],
                [
                    'type' => 'Integer',
                    'name' => 'validColumnWithDefaultAndOptions',
                    'default' => 0,
                    'options' => [
                        'autoincrement' => true,
                    ],
                ],
                [
                    'type' => 'Varchar',
                    'name' => 'nullableValidColumnWithLength',
                    'length' => 25,
                    'nullable' => true,
                ],
            ],
        ];
        $expectedResult2 = 2;

        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);
        $sql = $this->prophesize(CreateTable::class);

        $this->assertEquals(
            $expectedResult1,
            $this->invokeMethod($databaseHelper, 'parseAddColumn', [
                $sql->reveal(),
                $input1,
            ])
        );
        $this->assertEquals(
            $expectedResult1,
            $this->invokeMethod($databaseHelper, 'parseAddColumn', [
                new \stdClass(),
                $input1,
            ])
        );
        $this->assertEquals(
            $expectedResult2,
            $this->invokeMethod($databaseHelper, 'parseAddColumn', [
                $sql->reveal(),
                $input2,
            ])
        );
    }

    /**
     * @covers \Setup\Helper\DatabaseHelper::parseChangeColumn
     */
    public function testParseChangeColumn()
    {
        $input1 = [];
        $expectedResult1 = 0;

        $input2 = [
            'changeColumn' => [
                [
                    'name' => 'columnWithNoType',
                ],
                [
                    'type' => 'unknownType',
                    'name' => 'columnWithInvalidType',
                ],
                [
                    'type' => 'Integer',
                    // has no name
                ],
                [
                    'type' => 'Integer',
                    'name' => 1,
                    // name isn't string
                ],
                [
                    'type' => 'Integer',
                    'name' => 'validColumnWithDefaultAndOptions',
                    'default' => 0,
                    'options' => [
                        'autoincrement' => true,
                    ],
                ],
                [
                    'type' => 'Varchar',
                    'name' => 'nullableValidColumnWithLength',
                    'length' => 25,
                    'nullable' => true,
                ],
            ],
        ];
        $expectedResult2 = 2;

        $databaseHelper = $this->getDatabaseHelper($this->defaultConfig);
        $sql = $this->prophesize(AlterTable::class);

        $this->assertEquals(
            $expectedResult1,
            $this->invokeMethod($databaseHelper, 'parseChangeColumn', [
                $sql->reveal(),
                $input1,
            ])
        );
        $this->assertEquals(
            $expectedResult2,
            $this->invokeMethod($databaseHelper, 'parseChangeColumn', [
                $sql->reveal(),
                $input2,
            ])
        );
    }
}
