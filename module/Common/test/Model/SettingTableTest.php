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

use Common\Model\Setting;
use Common\Model\SettingTable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\TableGateway\TableGateway;
use RuntimeException;

class SettingTableTest extends TestCase
{
    /**
     * @var array
     */
    private $exampleArrayData = [
        'id' => 42,
        'path' => 'some/path',
        'value' => 'A value',
    ];

    /**
     * @var TableGateway
     */
    private $tableGateway;

    /**
     * @var SettingTable
     */
    private $settingTable;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGateway::class);

        $this->settingTable = new SettingTable(
            $this->tableGateway->reveal()
        );
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->settingTable);
        unset($this->tableGateway);
    }

    /**
     * @covers Common\Model\SettingTable::getSetting
     */
    public function testGetSetting()
    {
        $setting = new Setting($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn($setting);

        $this->tableGateway->select([
            'id' => $this->exampleArrayData['id'],
        ])->willReturn($resultSet->reveal());

        $returnedResultSet = $this->settingTable->getSetting(
            $this->exampleArrayData['id']
        );
        $this->assertInstanceOf(Setting::class, $returnedResultSet);
        $this->assertEquals($setting->getArrayCopy(), $returnedResultSet->getArrayCopy());
    }

    /**
     * @covers Common\Model\SettingTable::getSetting
     */
    public function testGetSettingExceptionThrown()
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
        $this->settingTable->getSetting($invalidId);
    }

    /**
     * @covers Common\Model\SettingTable::getSettingByPath
     */
    public function testGetSettingByPath()
    {
        $setting = new Setting($this->exampleArrayData);

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn($setting);

        $this->tableGateway->select([
            'path' => $this->exampleArrayData['path'],
        ])->willReturn($resultSet->reveal());

        $returnedResultSet = $this->settingTable->getSettingByPath(
            $this->exampleArrayData['path']
        );
        $this->assertInstanceOf(Setting::class, $returnedResultSet);
        $this->assertEquals($setting->getArrayCopy(), $returnedResultSet->getArrayCopy());
    }

    /**
     * @covers Common\Model\SettingTable::getSettingByPath
     */
    public function testGetSettingByPathExceptionThrown()
    {
        $invalidId = 1;

        $resultSet = $this->prophesize(ResultSetInterface::class);
        $resultSet->current()->willReturn(null);

        $this->tableGateway->select([
            'path' => $invalidId,
        ])->willReturn($resultSet->reveal());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find row with path %s',
                $invalidId
            )
        );
        $this->settingTable->getSettingByPath($invalidId);
    }

    /**
     * @covers Common\Model\SettingTable::saveSetting
     */
    public function testSaveSettingInsertCalled()
    {
        $newArrayData = $this->exampleArrayData;
        unset($newArrayData['id']);

        $newId = 1;

        $setting = new Setting($newArrayData);

        $this->tableGateway->insert(Argument::type('array'))->will(function () use ($newId) {
            $this->getLastInsertValue()->willReturn($newId);
        });
        $this->tableGateway->insert(Argument::type('array'))->shouldBeCalledTimes(1);
        $this->tableGateway->update(Argument::any())->shouldNotBeCalled();

        $this->settingTable->saveSetting($setting);

        $this->assertEquals($newId, $setting->getId());
    }

    /**
     * @covers Common\Model\SettingTable::saveSetting
     */
    public function testSaveSettingUpdateCalled()
    {
        $setting = new Setting($this->exampleArrayData);

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
            )->shouldBeCalledTimes(1);

        $this->settingTable->saveSetting($setting);
    }

    /**
     * @covers Common\Model\SettingTable::saveSetting
     */
    public function testSaveSettingExceptionThrown()
    {
        $locale = new Setting($this->exampleArrayData);

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

        $this->settingTable->saveSetting($locale);
    }

    /**
     * @covers Common\Model\SettingTable::deleteSetting
     */
    public function testDeleteSetting()
    {
        $id = 42;
        $this->tableGateway->delete(['id' => $id])->shouldBeCalledTimes(1);
        $this->settingTable->deleteSetting($id);
    }
}
