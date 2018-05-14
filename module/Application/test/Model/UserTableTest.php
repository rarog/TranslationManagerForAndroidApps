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

namespace ApplicationTest\Model;

use Application\Model\User;
use Application\Model\UserTable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\StatementContainer;
use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Adapter\Platform\Sql92;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;
use ZfcUser\Entity\User as ZfcUser;
use ZfcUser\Mapper\User as UserMapper;
use RuntimeException;

class UserTableTest extends TestCase
{
    private $exampleArrayData = [
        'user_id' => 12,
        'username' => 'auser',
        'email' => 'someboday@localhost.localdomain',
        'display_name' => 'A user',
        'password' => 123456789,
        'password_verify' => null,
        'state' => 1,
    ];

    private $tableGateway;

    private $userMapper;

    private $statement;

    private $mockDriver;

    private $userTable;

    protected function setUp()
    {
        $this->tableGateway = $this->prophesize(TableGateway::class);
        $this->tableGateway->getTable()->willReturn('user');

        $this->userMapper = $this->prophesize(UserMapper::class);

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

        $this->userTable = new UserTable($this->tableGateway->reveal(), $this->userMapper->reveal());
    }

    protected function tearDown()
    {
        unset($this->userTable);
        unset($this->mockDriver);
        unset($this->statement);
        unset($this->userMapper);
        unset($this->tableGateway);
    }

    public function testFetchAll()
    {
        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select(null)
            ->willReturn($resultSet)
            ->shouldBeCalledTimes(1);

        $this->assertSame($resultSet, $this->userTable->fetchAll());
    }

    public function testFetchAllNotInTeam()
    {
        $teamId = 10;

        $resultSet = $this->prophesize(ResultSetInterface::class)->reveal();
        $this->tableGateway->select(Argument::type(Where::class))
            ->willReturn($resultSet)
            ->shouldBeCalledTimes(1);

        $this->assertSame($resultSet, $this->userTable->fetchAllNotInTeam($teamId));
    }

    public function testGetUser()
    {
        $zfcUser = new ZfcUser();
        $zfcUser->setId($this->exampleArrayData['user_id']);
        $zfcUser->setUsername($this->exampleArrayData['username']);
        $zfcUser->setEmail($this->exampleArrayData['email']);
        $zfcUser->setDisplayName($this->exampleArrayData['display_name']);
        $zfcUser->setPassword($this->exampleArrayData['password']);
        $zfcUser->setState($this->exampleArrayData['state']);

        $user = new User($this->exampleArrayData);

        $this->userMapper->findById($this->exampleArrayData['user_id'])->willReturn($zfcUser);

        $returnedUser = $this->userTable->getUser($this->exampleArrayData['user_id']);
        $this->assertInstanceOf(User::class, $returnedUser);
        $this->assertEquals($user->getArrayCopy(), $returnedUser->getArrayCopy());
    }

    public function testGetUserExceptionThrown()
    {
        $invalidId = 1;

        $this->userMapper->findById($invalidId)->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Could not find row with identifier %s',
                $invalidId
            )
        );
        $this->userTable->getUser($invalidId);
    }
}
