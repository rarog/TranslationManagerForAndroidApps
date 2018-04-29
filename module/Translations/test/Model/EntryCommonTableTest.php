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
use ReflectionClass;
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

    private $entryCommonTable;

    private function getMethod($class, $methodName)
    {
        $reflection = new ReflectionClass($class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }

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
            'id' => 42,
        ])->willReturn($resultSet->reveal());

        $returnedResultSet = $this->entryCommonTable->getEntryCommon(42);
        $this->assertInstanceOf(EntryCommon::class, $returnedResultSet);
        $this->assertEquals($entryCommon->getArrayCopy(), $returnedResultSet->getArrayCopy());
    }

    public function testEntryCommonExceptionThrown()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn(null);

        $this->tableGateway->select([
            'id' => 1,
        ])->willReturn($resultSet->reveal());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find row with identifier 1');
        $this->entryCommonTable->getEntryCommon(1);
    }
}
