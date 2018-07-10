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
use Translations\Model\Suggestion;
use Translations\Model\SuggestionTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\StatementContainer;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\TableGateway\TableGateway;
use RuntimeException;

class SuggestionTableTest extends TestCase
{
    /**
     * @var array
     */
    private $exampleArrayData = [
        'id' => 42,
        'entry_common_id' => 12,
        'user_id' => 11,
        'last_change' => 12345654,
    ];

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $tableGateway;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $statement;

    /**
     * @var \phpmock\MockBuilder
     */
    private $mockDriver;

    /**
     * @var SuggestionTable
     */
    private $suggestionTable;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
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

        $this->suggestionTable = new SuggestionTable($this->tableGateway->reveal());
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->suggestionTable);
        unset($this->mockDriver);
        unset($this->statement);
        unset($this->tableGateway);
    }

    /**
     * @covers Translations\Model\SuggestionTable::getSuggestion
     */
    public function testGetSuggestion()
    {
        $suggestion = new Suggestion($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn($suggestion);

        $this->tableGateway->select([
            'id' => $this->exampleArrayData['id'],
        ])->willReturn($resultSet->reveal());

        $returnedResultSet = $this->suggestionTable->getSuggestion($this->exampleArrayData['id']);
        $this->assertInstanceOf(Suggestion::class, $returnedResultSet);
        $this->assertEquals($suggestion->getArrayCopy(), $returnedResultSet->getArrayCopy());
    }

    /**
     * @covers Translations\Model\SuggestionTable::getSuggestion
     */
    public function testGetSuggestionExceptionThrown()
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
        $this->suggestionTable->getSuggestion(1);
    }

    /**
     * @covers Translations\Model\SuggestionTable::saveSuggestion
     */
    public function testSaveSuggestionInsertCalled()
    {
        $newArrayData = $this->exampleArrayData;
        unset($newArrayData['id']);

        $newId = 1;

        $suggestion = new Suggestion($newArrayData);

        $this->tableGateway->insert(Argument::type('array'))->will(function () use ($newId) {
            $this->getLastInsertValue()->willReturn($newId);
        });
        $this->tableGateway->insert(Argument::type('array'))->shouldBeCalled();
        $this->tableGateway->update(Argument::any())->shouldNotBeCalled();

        $this->suggestionTable->saveSuggestion($suggestion);

        $this->assertEquals($newId, $suggestion->getId());
    }

    /**
     * @covers Translations\Model\SuggestionTable::saveSuggestion
     */
    public function testSaveSuggestionUpdateCalled()
    {
        $suggestion = new Suggestion($this->exampleArrayData);

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

        $this->suggestionTable->saveSuggestion($suggestion);
    }

    /**
     * @covers Translations\Model\SuggestionTable::saveSuggestion
     */
    public function testSaveSuggestionExceptionThrown()
    {
        $suggestion = new Suggestion($this->exampleArrayData);

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

        $this->suggestionTable->saveSuggestion($suggestion);
    }

    /**
     * @covers Translations\Model\SuggestionTable::deleteSuggestion
     */
    public function testDeleteSuggestion()
    {
        $id = 42;
        $this->tableGateway->delete(['id' => $id])->shouldBeCalled();
        $this->suggestionTable->deleteSuggestion($id);
    }

    /**
     * @covers Translations\Model\SuggestionTable::deleteSuggestionByEntryCommonId
     */
    public function testDeleteSuggestionByEntryCommonId()
    {
        $entryCommonId = 12;
        $this->tableGateway->delete(['entry_common_id' => $entryCommonId])->shouldBeCalled();
        $this->suggestionTable->deleteSuggestionByEntryCommonId($entryCommonId);
    }
}
