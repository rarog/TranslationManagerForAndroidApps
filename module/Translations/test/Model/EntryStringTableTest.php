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

class EntryStringTableTest extends TestCase
{
    private $exampleArrayData = [
        'entry_common_id' => 42,
        'value' => 'A string value',
    ];

    private $tableGateway;

    private $appResourceTable;

    private $statement;

    private $mockDriver;

    private $entryStringTable;

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGateway::class);
        $this->tableGateway->getTable()->willReturn('entry_string');

        $this->appResourceTable = $this->prophesize(AppResourceTable::class);

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

        $this->entryStringTable = new EntryStringTable(
            $this->tableGateway->reveal(),
            $this->appResourceTable->reveal()
        );
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
}
