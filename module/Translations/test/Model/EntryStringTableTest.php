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
use Prophecy\Argument;
use Translations\Model\EntryString;
use Translations\Model\EntryStringTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\StatementContainer;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\TableGateway\TableGateway;
use RuntimeException;
use Translations\Model\AppResourceTable;
use Translations\Model\AppResource;
use ArrayObject;

class EntryStringTableTest extends TestCase
{
    private $exampleArrayData = [
        'entry_common_id' => 42,
        'value' => 'A string value',
    ];

    private $tableGateway;

    private $appResourceTable;

    private $internalStatement;

    private $statement;

    private $mockDriver;

    private $entryStringTable;

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGateway::class);
        $this->tableGateway->getTable()->willReturn('entry_string');

        $this->appResourceTable = $this->prophesize(AppResourceTable::class);

        $internalStatement = new StatementContainer();
        $this->internalStatement = $internalStatement;

        $statement = $this->prophesize(StatementInterface::class);
        $this->statement = $statement;
        $this->statement->getParameterContainer()->will(
            function () use ($internalStatement) {
                return $internalStatement->getParameterContainer();
            }
        );
        $this->statement->getSql()->will(
            function () use ($internalStatement) {
                return $internalStatement->getSql();
            }
        );
        $this->statement->setSql(Argument::any())->will(
            function ($args) use ($internalStatement) {
                return $internalStatement->setSql($args[0]);
            }
        );

        $this->mockDriver = $this->getMockBuilder(DriverInterface::class)->getMock();
        $this->mockDriver->expects($this->any())
            ->method('formatParameterName')
            ->will($this->returnValue('?'));
        $this->mockDriver->expects($this->any())
            ->method('createStatement')
            ->will(
                $this->returnCallback(
                    function () use ($statement) {
                        return $this->statement->reveal();
                    }
                )
            );

        $adapter = new Adapter($this->mockDriver, new Sql92());
        $this->tableGateway->getAdapter()->willReturn($adapter);

        $this->entryStringTable = new EntryStringTable(
            $this->tableGateway->reveal(),
            $this->appResourceTable->reveal()
        );
    }

    protected function tearDown()
    {
        unset($this->entryStringTable);
        unset($this->mockDriver);
        unset($this->statement);
        unset($this->internalStatement);
        unset($this->appResourceTable);
        unset($this->tableGateway);
    }

    public function testFetchAll()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select(null)->willReturn($resultSet);

        $this->assertSame($resultSet, $this->entryStringTable->fetchAll());
    }

    public function testGetEntryString()
    {
        $entryCommon = new EntryString($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn($entryCommon);

        $this->tableGateway->select([
            'entry_common_id' => $this->exampleArrayData['entry_common_id'],
        ])->willReturn($resultSet->reveal());

        $returnedResultSet = $this->entryStringTable->getEntryString(
            $this->exampleArrayData['entry_common_id']
        );
        $this->assertInstanceOf(EntryString::class, $returnedResultSet);
        $this->assertEquals($entryCommon->getArrayCopy(), $returnedResultSet->getArrayCopy());
    }

    public function testGetEntryStringExceptionThrown()
    {
        $invalidId = 1;

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn(null);

        $this->tableGateway->select([
            'entry_common_id' => $invalidId,
        ])->willReturn($resultSet->reveal());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find row with identifier %s',
                $invalidId
            )
        );
        $this->entryStringTable->getEntryString($invalidId);
    }

    public function testSaveEntryStringExceptionThrown()
    {
        $arrayData = $this->exampleArrayData;
        unset($arrayData['entry_common_id']);

        $entrystring = new EntryString($arrayData);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot handle entry with invalid id');

        $this->entryStringTable->saveEntryString($entrystring);
    }

    public function testSaveEntryCommonUpdateCalled()
    {
        $entryString = new EntryString($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn($this->exampleArrayData);

        $this->tableGateway->select([
            'entry_common_id' => $this->exampleArrayData['entry_common_id'],
        ])->willReturn($resultSet->reveal());
        $this->tableGateway->insert(Argument::any())->shouldNotBeCalled();
        $this->tableGateway->update(
            array_diff_key($this->exampleArrayData, ['entry_common_id' => null]),
            ['entry_common_id' => $this->exampleArrayData['entry_common_id']]
        )->shouldBeCalled();

        $this->entryStringTable->saveEntryString($entryString);
    }

    public function testSaveEntryStringInsertCalled()
    {
        $entrystring = new EntryString($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn(null);

        $this->tableGateway->insert($this->exampleArrayData)->shouldBeCalled();
        $this->tableGateway->update(Argument::any())->shouldNotBeCalled();

        $this->entryStringTable->saveEntryString($entrystring);
    }

    public function testGetAllEntryStringsForTranslationsNoDefaultNoWhere()
    {
        $appId = 1;
        $appResourceId = 2;
        $entryId = 0;
        $expectedSql = 'SELECT "default"."value" AS "defaultValue", ' .
            '"default_common"."id" AS "defaultId", ' .
            '"default_common"."app_resource_id" AS "appResourceId", ' .
            '"default_common"."resource_file_entry_id" AS "resourceFileEntryId", ' .
            '"default_common"."last_change" AS "defaultLastChange", ' .
            '"resource_file_entry"."resource_type_id" AS "resourceTypeId", ' .
            '"resource_file_entry"."name" AS "name", ' .
            '"resource_file_entry"."product" AS "product", ' .
            '"resource_file_entry"."description" AS "description", ' .
            'count(distinct entry_count.id) AS "entryCount", ' .
            '"entry_common"."id" AS "id", ' .
            '"entry_common"."last_change" AS "lastChange", ' .
            '"entry_common"."notification_status" AS "notificationStatus", ' .
            '"entry_string"."value" AS "value", ' .
            'count(distinct suggestion.id) AS "suggestionCount" ' .
            'FROM "entry_string" AS "default" ' .
            'INNER JOIN "entry_common" AS "default_common" ON "default_common"."id" = "default"."entry_common_id" ' .
            'INNER JOIN "app_resource" ON "app_resource"."id" = "default_common"."app_resource_id" AND ' .
            '"app_resource"."app_id" = ? AND "app_resource"."name" = ? ' .
            'INNER JOIN "resource_file_entry" ON ' .
            '"resource_file_entry"."id" = "default_common"."resource_file_entry_id" AND ' .
            '"resource_file_entry"."deleted" = ? AND "resource_file_entry"."translatable" = ? ' .
            'LEFT JOIN "resource_file_entry" AS "entry_count" ON ' .
            '"entry_count"."app_resource_file_id" = "resource_file_entry"."app_resource_file_id" AND ' .
            '"entry_count"."name" = "resource_file_entry"."name" AND "entry_count"."deleted" = ? ' .
            'LEFT JOIN "entry_common" ON ' .
            '"entry_common"."resource_file_entry_id" = "default_common"."resource_file_entry_id" AND ' .
            '"entry_common"."app_resource_id" = ? ' .
            'LEFT JOIN "entry_string" ON "entry_string"."entry_common_id" = "entry_common"."id" ' .
            'LEFT JOIN "suggestion" ON "suggestion"."entry_common_id" = "entry_common"."id" ' .
            'GROUP BY "default"."value", "default_common"."id", "default_common"."app_resource_id", ' .
            '"default_common"."resource_file_entry_id", "default_common"."last_change", ' .
            '"resource_file_entry"."resource_type_id", "resource_file_entry"."name", ' .
            '"resource_file_entry"."product", "resource_file_entry"."description", "entry_common"."id", ' .
            '"entry_common"."last_change", "entry_common"."notification_status", "entry_string"."value"';
        $expectedParameters = [
            'join2part1' => $appId, // app_resource.app_id
            'join2part2' => 'values', // app_resource.name
            'join3part1' => 0, // resource_file_entry.deleted
            'join3part2' => 1, // resource_file_entry.translatable
            'join4part1' => 0, // entry_count.deleted
            'join5part1' => $appResourceId, // entry_common.app_resource_id
        ];

        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();

        $this->appResourceTable
            ->getAppResourceByAppIdAndName($appId, 'values')
            ->willThrow(new RuntimeException());
        $this->statement->execute()->willReturn($resultSet);
        $this->statement->execute()->shouldBeCalled();

        // $defaultAppResource will be false, no where condition set
        $this->assertInstanceOf(
            ArrayObject::class,
            $this->entryStringTable->getAllEntryStringsForTranslations(
                $appId,
                $appResourceId,
                $entryId
            )
        );
        $this->assertEquals($expectedSql, $this->internalStatement->getSql());
        $this->assertEquals($expectedParameters, $this->internalStatement->getParameterContainer()->getNamedArray());
    }

    public function testGetAllEntryStringsForTranslationsNoDefaultWhere()
    {
        $appId = 1;
        $appResourceId = 2;
        $entryId = 1;
        $expectedSql = 'SELECT "default"."value" AS "defaultValue", ' .
            '"default_common"."id" AS "defaultId", ' .
            '"default_common"."app_resource_id" AS "appResourceId", ' .
            '"default_common"."resource_file_entry_id" AS "resourceFileEntryId", ' .
            '"default_common"."last_change" AS "defaultLastChange", ' .
            '"resource_file_entry"."resource_type_id" AS "resourceTypeId", ' .
            '"resource_file_entry"."name" AS "name", ' .
            '"resource_file_entry"."product" AS "product", ' .
            '"resource_file_entry"."description" AS "description", ' .
            'count(distinct entry_count.id) AS "entryCount", ' .
            '"entry_common"."id" AS "id", ' .
            '"entry_common"."last_change" AS "lastChange", ' .
            '"entry_common"."notification_status" AS "notificationStatus", ' .
            '"entry_string"."value" AS "value", ' .
            'count(distinct suggestion.id) AS "suggestionCount" ' .
            'FROM "entry_string" AS "default" ' .
            'INNER JOIN "entry_common" AS "default_common" ON "default_common"."id" = "default"."entry_common_id" ' .
            'INNER JOIN "app_resource" ON "app_resource"."id" = "default_common"."app_resource_id" AND ' .
            '"app_resource"."app_id" = ? AND "app_resource"."name" = ? ' .
            'INNER JOIN "resource_file_entry" ON ' .
            '"resource_file_entry"."id" = "default_common"."resource_file_entry_id" AND ' .
            '"resource_file_entry"."deleted" = ? AND "resource_file_entry"."translatable" = ? ' .
            'LEFT JOIN "resource_file_entry" AS "entry_count" ON ' .
            '"entry_count"."app_resource_file_id" = "resource_file_entry"."app_resource_file_id" AND ' .
            '"entry_count"."name" = "resource_file_entry"."name" AND "entry_count"."deleted" = ? ' .
            'LEFT JOIN "entry_common" ON ' .
            '"entry_common"."resource_file_entry_id" = "default_common"."resource_file_entry_id" AND ' .
            '"entry_common"."app_resource_id" = ? ' .
            'LEFT JOIN "entry_string" ON "entry_string"."entry_common_id" = "entry_common"."id" ' .
            'LEFT JOIN "suggestion" ON "suggestion"."entry_common_id" = "entry_common"."id" ' .
            'WHERE "default_common"."id" = ? ' .
            'GROUP BY "default"."value", "default_common"."id", "default_common"."app_resource_id", ' .
            '"default_common"."resource_file_entry_id", "default_common"."last_change", ' .
            '"resource_file_entry"."resource_type_id", "resource_file_entry"."name", ' .
            '"resource_file_entry"."product", "resource_file_entry"."description", "entry_common"."id", ' .
            '"entry_common"."last_change", "entry_common"."notification_status", "entry_string"."value"';
        $expectedParameters = [
            'join2part1' => $appId, // app_resource.app_id
            'join2part2' => 'values', // app_resource.name
            'join3part1' => 0, // resource_file_entry.deleted
            'join3part2' => 1, // resource_file_entry.translatable
            'join4part1' => 0, // entry_count.deleted
            'join5part1' => $appResourceId, // entry_common.app_resource_id
            'where1' => $entryId, // default_common.id
        ];


        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();

        $this->appResourceTable
            ->getAppResourceByAppIdAndName($appId, 'values')
            ->willThrow(new RuntimeException());
        $this->statement->execute()->willReturn($resultSet);
        $this->statement->execute()->shouldBeCalled();

        // $defaultAppResource will be false, where condition set
        $this->assertInstanceOf(
            ArrayObject::class,
            $this->entryStringTable->getAllEntryStringsForTranslations(
                $appId,
                $appResourceId,
                $entryId
            )
        );
        $this->assertEquals($expectedSql, $this->internalStatement->getSql());
        $this->assertEquals($expectedParameters, $this->internalStatement->getParameterContainer()->getNamedArray());
    }

    public function testGetAllEntryStringsForTranslationsDefaultIdNotEqualToAppResourceIdNoWhere()
    {
        $appId = 1;
        $appResourceId = 2;
        $entryId = 0;
        $expectedSql = 'SELECT "default"."value" AS "defaultValue", ' .
            '"default_common"."id" AS "defaultId", ' .
            '"default_common"."app_resource_id" AS "appResourceId", ' .
            '"default_common"."resource_file_entry_id" AS "resourceFileEntryId", ' .
            '"default_common"."last_change" AS "defaultLastChange", ' .
            '"resource_file_entry"."resource_type_id" AS "resourceTypeId", ' .
            '"resource_file_entry"."name" AS "name", ' .
            '"resource_file_entry"."product" AS "product", ' .
            '"resource_file_entry"."description" AS "description", ' .
            'count(distinct entry_count.id) AS "entryCount", ' .
            '"entry_common"."id" AS "id", ' .
            '"entry_common"."last_change" AS "lastChange", ' .
            '"entry_common"."notification_status" AS "notificationStatus", ' .
            '"entry_string"."value" AS "value", ' .
            'count(distinct suggestion.id) AS "suggestionCount" ' .
            'FROM "entry_string" AS "default" ' .
            'INNER JOIN "entry_common" AS "default_common" ON "default_common"."id" = "default"."entry_common_id" ' .
            'INNER JOIN "app_resource" ON "app_resource"."id" = "default_common"."app_resource_id" AND ' .
            '"app_resource"."app_id" = ? AND "app_resource"."name" = ? ' .
            'INNER JOIN "resource_file_entry" ON ' .
            '"resource_file_entry"."id" = "default_common"."resource_file_entry_id" AND ' .
            '"resource_file_entry"."deleted" = ? AND "resource_file_entry"."translatable" = ? ' .
            'LEFT JOIN "resource_file_entry" AS "entry_count" ON ' .
            '"entry_count"."app_resource_file_id" = "resource_file_entry"."app_resource_file_id" AND ' .
            '"entry_count"."name" = "resource_file_entry"."name" AND "entry_count"."deleted" = ? ' .
            'LEFT JOIN "entry_common" ON ' .
            '"entry_common"."resource_file_entry_id" = "default_common"."resource_file_entry_id" AND ' .
            '"entry_common"."app_resource_id" = ? ' .
            'LEFT JOIN "entry_string" ON "entry_string"."entry_common_id" = "entry_common"."id" ' .
            'LEFT JOIN "suggestion" ON "suggestion"."entry_common_id" = "entry_common"."id" ' .
            'GROUP BY "default"."value", "default_common"."id", "default_common"."app_resource_id", ' .
            '"default_common"."resource_file_entry_id", "default_common"."last_change", ' .
            '"resource_file_entry"."resource_type_id", "resource_file_entry"."name", ' .
            '"resource_file_entry"."product", "resource_file_entry"."description", "entry_common"."id", ' .
            '"entry_common"."last_change", "entry_common"."notification_status", "entry_string"."value"';
        $expectedParameters = [
            'join2part1' => $appId, // app_resource.app_id
            'join2part2' => 'values', // app_resource.name
            'join3part1' => 0, // resource_file_entry.deleted
            'join3part2' => 1, // resource_file_entry.translatable
            'join4part1' => 0, // entry_count.deleted
            'join5part1' => $appResourceId, // entry_common.app_resource_id
        ];

        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();

        $this->appResourceTable
            ->getAppResourceByAppIdAndName($appId, 'values')
            ->willReturn(new AppResource([
                'id' => $appResourceId + 1,
            ]));
        $this->statement->execute()->willReturn($resultSet);
        $this->statement->execute()->shouldBeCalled();

        // $defaultAppResource->getId() !== $appId, no where condition set
        $this->assertInstanceOf(
            ArrayObject::class,
            $this->entryStringTable->getAllEntryStringsForTranslations(
                $appId,
                $appResourceId,
                $entryId
            )
        );
        $this->assertEquals($expectedSql, $this->internalStatement->getSql());
        $this->assertEquals($expectedParameters, $this->internalStatement->getParameterContainer()->getNamedArray());
    }

    public function testGetAllEntryStringsForTranslationsDefaultIdEqualsAppResourceIdNoWhere()
    {
        $appId = 1;
        $appResourceId = 2;
        $entryId = 0;
        $expectedSql = 'SELECT "default"."value" AS "defaultValue", ' .
            '"default_common"."id" AS "defaultId", ' .
            '"default_common"."app_resource_id" AS "appResourceId", ' .
            '"default_common"."resource_file_entry_id" AS "resourceFileEntryId", ' .
            '"default_common"."last_change" AS "defaultLastChange", ' .
            '"resource_file_entry"."resource_type_id" AS "resourceTypeId", ' .
            '"resource_file_entry"."name" AS "name", ' .
            '"resource_file_entry"."product" AS "product", ' .
            '"resource_file_entry"."description" AS "description", ' .
            'count(distinct entry_count.id) AS "entryCount", ' .
            '"entry_common"."id" AS "id", ' .
            '"entry_common"."last_change" AS "lastChange", ' .
            '"entry_common"."notification_status" AS "notificationStatus", ' .
            '"entry_string"."value" AS "value", ' .
            'count(distinct suggestion.id) AS "suggestionCount" ' .
            'FROM "entry_string" AS "default" ' .
            'INNER JOIN "entry_common" AS "default_common" ON "default_common"."id" = "default"."entry_common_id" ' .
            'INNER JOIN "app_resource" ON "app_resource"."id" = "default_common"."app_resource_id" AND ' .
            '"app_resource"."app_id" = ? AND "app_resource"."name" = ? ' .
            'INNER JOIN "resource_file_entry" ON ' .
            '"resource_file_entry"."id" = "default_common"."resource_file_entry_id" AND ' .
            '"resource_file_entry"."deleted" = ? ' .
            'LEFT JOIN "resource_file_entry" AS "entry_count" ON ' .
            '"entry_count"."app_resource_file_id" = "resource_file_entry"."app_resource_file_id" AND ' .
            '"entry_count"."name" = "resource_file_entry"."name" AND "entry_count"."deleted" = ? ' .
            'LEFT JOIN "entry_common" ON ' .
            '"entry_common"."resource_file_entry_id" = "default_common"."resource_file_entry_id" AND ' .
            '"entry_common"."app_resource_id" = ? ' .
            'LEFT JOIN "entry_string" ON "entry_string"."entry_common_id" = "entry_common"."id" ' .
            'LEFT JOIN "suggestion" ON "suggestion"."entry_common_id" = "entry_common"."id" ' .
            'GROUP BY "default"."value", "default_common"."id", "default_common"."app_resource_id", ' .
            '"default_common"."resource_file_entry_id", "default_common"."last_change", ' .
            '"resource_file_entry"."resource_type_id", "resource_file_entry"."name", ' .
            '"resource_file_entry"."product", "resource_file_entry"."description", "entry_common"."id", ' .
            '"entry_common"."last_change", "entry_common"."notification_status", "entry_string"."value"';
        $expectedParameters = [
            'join2part1' => $appId, // app_resource.app_id
            'join2part2' => 'values', // app_resource.name
            'join3part1' => 0, // resource_file_entry.deleted
            'join4part1' => 0, // entry_count.deleted
            'join5part1' => $appResourceId, // entry_common.app_resource_id
        ];

        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();

        $this->appResourceTable
        ->getAppResourceByAppIdAndName($appId, 'values')
        ->willReturn(new AppResource([
            'id' => $appResourceId,
        ]));
        $this->statement->execute()->willReturn($resultSet);
        $this->statement->execute()->shouldBeCalled();

        // $defaultAppResource->getId() !== $appId, no where condition set
        $this->assertInstanceOf(
            ArrayObject::class,
            $this->entryStringTable->getAllEntryStringsForTranslations(
                $appId,
                $appResourceId,
                $entryId
            )
        );
        $this->assertEquals($expectedSql, $this->internalStatement->getSql());
        $this->assertEquals($expectedParameters, $this->internalStatement->getParameterContainer()->getNamedArray());
    }

    public function testGetAllEntryStringsForTranslationsDefaultIdEqualsAppResourceIdWhere()
    {
        $appId = 1;
        $appResourceId = 2;
        $entryId = 1;
        $expectedSql = 'SELECT "default"."value" AS "defaultValue", ' .
            '"default_common"."id" AS "defaultId", ' .
            '"default_common"."app_resource_id" AS "appResourceId", ' .
            '"default_common"."resource_file_entry_id" AS "resourceFileEntryId", ' .
            '"default_common"."last_change" AS "defaultLastChange", ' .
            '"resource_file_entry"."resource_type_id" AS "resourceTypeId", ' .
            '"resource_file_entry"."name" AS "name", ' .
            '"resource_file_entry"."product" AS "product", ' .
            '"resource_file_entry"."description" AS "description", ' .
            'count(distinct entry_count.id) AS "entryCount", ' .
            '"entry_common"."id" AS "id", ' .
            '"entry_common"."last_change" AS "lastChange", ' .
            '"entry_common"."notification_status" AS "notificationStatus", ' .
            '"entry_string"."value" AS "value", ' .
            'count(distinct suggestion.id) AS "suggestionCount" ' .
            'FROM "entry_string" AS "default" ' .
            'INNER JOIN "entry_common" AS "default_common" ON "default_common"."id" = "default"."entry_common_id" ' .
            'INNER JOIN "app_resource" ON "app_resource"."id" = "default_common"."app_resource_id" AND ' .
            '"app_resource"."app_id" = ? AND "app_resource"."name" = ? ' .
            'INNER JOIN "resource_file_entry" ON ' .
            '"resource_file_entry"."id" = "default_common"."resource_file_entry_id" AND ' .
            '"resource_file_entry"."deleted" = ? ' .
            'LEFT JOIN "resource_file_entry" AS "entry_count" ON ' .
            '"entry_count"."app_resource_file_id" = "resource_file_entry"."app_resource_file_id" AND ' .
            '"entry_count"."name" = "resource_file_entry"."name" AND "entry_count"."deleted" = ? ' .
            'LEFT JOIN "entry_common" ON ' .
            '"entry_common"."resource_file_entry_id" = "default_common"."resource_file_entry_id" AND ' .
            '"entry_common"."app_resource_id" = ? ' .
            'LEFT JOIN "entry_string" ON "entry_string"."entry_common_id" = "entry_common"."id" ' .
            'LEFT JOIN "suggestion" ON "suggestion"."entry_common_id" = "entry_common"."id" ' .
            'WHERE "default_common"."id" = ? ' .
            'GROUP BY "default"."value", "default_common"."id", "default_common"."app_resource_id", ' .
            '"default_common"."resource_file_entry_id", "default_common"."last_change", ' .
            '"resource_file_entry"."resource_type_id", "resource_file_entry"."name", ' .
            '"resource_file_entry"."product", "resource_file_entry"."description", "entry_common"."id", ' .
            '"entry_common"."last_change", "entry_common"."notification_status", "entry_string"."value"';
        $expectedParameters = [
            'join2part1' => $appId, // app_resource.app_id
            'join2part2' => 'values', // app_resource.name
            'join3part1' => 0, // resource_file_entry.deleted
            'join4part1' => 0, // entry_count.deleted
            'join5part1' => $appResourceId, // entry_common.app_resource_id
            'where1' => $entryId, // default_common.id
        ];

        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();

        $this->appResourceTable
        ->getAppResourceByAppIdAndName($appId, 'values')
        ->willReturn(new AppResource([
            'id' => $appResourceId,
        ]));
        $this->statement->execute()->willReturn($resultSet);
        $this->statement->execute()->shouldBeCalled();

        // $defaultAppResource->getId() !== $appId, no where condition set
        $this->assertInstanceOf(
            ArrayObject::class,
            $this->entryStringTable->getAllEntryStringsForTranslations(
                $appId,
                $appResourceId,
                $entryId
            )
        );
        $this->assertEquals($expectedSql, $this->internalStatement->getSql());
        $this->assertEquals($expectedParameters, $this->internalStatement->getParameterContainer()->getNamedArray());
    }

    public function testGetAllEntryStringsForTranslationsArrayGeneration()
    {
        $appId = 1;
        $appResourceId = 2;
        $entryId = 0;

        $expectedResultSet = [
            [
                'defaultId' => 1,
                'appResourceId' => $appResourceId,
                'resourceFileEntryId' => 1,
                'defaultLastChange' => 123456,
                'resourceTypeId' => 1,
                'entryCount' => 4,
                'id' => 1,
                'lastChange' => 123456,
                'notificationStatus' => 0,
                'suggestionCount' => 2,
            ],
            [
                'defaultId' => 2,
                'appResourceId' => $appResourceId,
                'resourceFileEntryId' => 2,
                'defaultLastChange' => 123459,
                'resourceTypeId' => 1,
                'entryCount' => 1,
                'id' => 2,
                'lastChange' => 1234570,
                'notificationStatus' => 1,
                'suggestionCount' => 0,
            ],
        ];

        $this->appResourceTable
            ->getAppResourceByAppIdAndName($appId, 'values')
            ->willThrow(new RuntimeException());
        $this->statement->execute()->willReturn($expectedResultSet);
        $this->statement->execute()->shouldBeCalled();

        $result = $this->entryStringTable->getAllEntryStringsForTranslations(
            $appId,
            $appResourceId,
            $entryId
        );
        $this->assertInstanceOf(ArrayObject::class, $result);
        $this->assertEquals(count($expectedResultSet), count($result));

        for ($i = 0; $i < count($result); $i++) {
            $this->assertInstanceOf(ArrayObject::class, $result[$i]);
            $this->assertEquals($expectedResultSet[$i], $result[$i]->getArrayCopy());
        }
    }
}
