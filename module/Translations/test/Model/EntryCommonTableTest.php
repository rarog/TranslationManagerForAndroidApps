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
use Translations\Model\EntryCommon;
use Translations\Model\EntryCommonTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\StatementContainer;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\TableGateway\TableGateway;
use RuntimeException;

class EntryCommonTableTest extends TestCase
{
    private $exampleArrayData = [
        'id' => '42',
        'app_resource_id' => 12,
        'resource_file_entry_id' => 11,
        'last_change' => 12345654,
        'notification_status' => 1,
    ];

    private $tableGateway;

    private $statement;

    private $mockDriver;

    private $entryCommonTable;

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGateway::class);
        $this->tableGateway->getTable()->willReturn('entry_common');

        $internalStatement = new StatementContainer();

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

        $this->entryCommonTable = new EntryCommonTable($this->tableGateway->reveal());
    }

    public function testFetchAll()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select(null)->willReturn($resultSet);

        $this->assertSame($resultSet, $this->entryCommonTable->fetchAll());
    }

    public function testGetEntryCommon()
    {
        $entryCommon = new EntryCommon($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn($entryCommon);

        $this->tableGateway->select([
            'id' => $this->exampleArrayData['id'],
        ])->willReturn($resultSet->reveal());

        $returnedResultSet = $this->entryCommonTable->getEntryCommon($this->exampleArrayData['id']);
        $this->assertInstanceOf(EntryCommon::class, $returnedResultSet);
        $this->assertEquals($entryCommon->getArrayCopy(), $returnedResultSet->getArrayCopy());
    }

    public function testGetEntryCommonExceptionThrown()
    {
        $invalidId = 1;

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn(null);

        $this->tableGateway->select([
            'id' => $invalidId,
        ])->willReturn($resultSet->reveal());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find row with identifier %s',
                $invalidId
            )
        );
        $this->entryCommonTable->getEntryCommon(1);
    }

    public function testSaveEntryCommonInsertCalled()
    {
        $newArrayData = $this->exampleArrayData;
        unset($newArrayData['id']);

        $newId = 1;

        $entryCommon = new EntryCommon($newArrayData);

        $this->tableGateway->insert(Argument::type('array'))->will(function () use ($newId) {
            $this->getLastInsertValue()->willReturn($newId);
        });
        $this->tableGateway->insert(Argument::type('array'))->shouldBeCalled();
        $this->tableGateway->update(Argument::any())->shouldNotBeCalled();

        $this->entryCommonTable->saveEntryCommon($entryCommon);

        $this->assertEquals($newId, $entryCommon->getId());
    }

    public function testSaveEntryCommonUpdateCalled()
    {
        $entryCommon = new EntryCommon($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn($this->exampleArrayData);

        $this->tableGateway->select([
            'id' => $this->exampleArrayData['id'],
        ])->willReturn($resultSet->reveal());
        $this->tableGateway->insert(Argument::any())->shouldNotBeCalled();
        $this->tableGateway
            ->update(
                array_diff_key($this->exampleArrayData, ['id' => null]),
                ['id' => $this->exampleArrayData['id']]
            )->shouldBeCalled();

        $this->entryCommonTable->saveEntryCommon($entryCommon);
    }

    public function testSaveEntryCommonExceptionThrown()
    {
        $entryCommon = new EntryCommon($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn(null);

        $this->tableGateway->select([
            'id' => $this->exampleArrayData['id'],
        ])->willReturn($resultSet->reveal());
        $this->tableGateway->insert(Argument::any())->shouldNotBeCalled();
        $this->tableGateway->update(Argument::any())->shouldNotBeCalled();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot update row with identifier %d; does not exist', $this->exampleArrayData['id'])
        );

        $this->entryCommonTable->saveEntryCommon($entryCommon);
    }

    public function testDeleteEntryCommon()
    {
        $id = 42;
        $this->tableGateway->delete(['id' => $id])->shouldBeCalled();
        $this->entryCommonTable->deleteEntryCommon($id);
    }
}
