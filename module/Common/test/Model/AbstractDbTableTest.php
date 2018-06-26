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

namespace CommonTest\Model;

use Common\Model\AbstractDbTable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use ReflectionClass;

class AbstractDbTableTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $tableGateway;

    /**
     * @var AbstractDbTable
     */
    private $abstractDbTable;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGateway::class);

        $this->abstractDbTable = new class($this->tableGateway->reveal()) extends AbstractDbTable {
        };
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->tableGateway);
    }

    /**
     * @covers Common\Model\AbstractDbTable::__construct
     */
    public function testConstructor()
    {
        $reflection = new ReflectionClass(AbstractDbTable::class);
        $tableGatewayProperty = $reflection->getProperty('tableGateway');
        $tableGatewayProperty->setAccessible(true);

        $this->assertSame($tableGatewayProperty->getValue($this->abstractDbTable), $this->tableGateway->reveal());
    }

    /**
     * @covers Common\Model\AbstractDbTable::fetchAll
     */
    public function testFetchAll()
    {
        $resultSet = $this->prophesize(ResultSet::class);
        $this->tableGateway->select(Argument::any())
            ->willReturn($resultSet->reveal())
            ->shouldBeCalledTimes(1);

        $this->assertSame($resultSet->reveal(), $this->abstractDbTable->fetchAll('some where condition'));
    }
}
