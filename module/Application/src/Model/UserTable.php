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

namespace Application\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;
use ZfcUser\Mapper\User as UserMapper;
use ArrayObject;
use RuntimeException;

class UserTable
{
    /**
     * @var TableGateway
     */
    private $tableGateway;

    /**
     * @var UserMapper
     */
    private $userMapper;

    /**
     * Constructor
     *
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway, UserMapper $userMapper)
    {
        $this->tableGateway = $tableGateway;
        $this->userMapper = $userMapper;
    }

    /**
     * Gets all entries
     *
     * @param \Zend\Db\Sql\Where|\Closure|string|array $where
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll($where = null)
    {
        return $this->tableGateway->select($where);
    }

    /**
     * Gets all entries
     *
     * @param int $teamId
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAllNotInTeam(int $teamId)
    {
        $select = new Select();
        $select->columns(['user_id'])
            ->from('team_member')
            ->where(['team_id' => $teamId]);

        $where = new Where();
        $where->notIn('user_id', $select);

        return $this->fetchAll($where);
    }

    /**
     * Get entry
     *
     * @param  int $id
     * @throws RuntimeException
     * @return \ZfcUser\Entity\User
     */
    public function getUser(int $id)
    {
        $row = $this->userMapper->findById($id);
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $id));
        }

        return $row;
    }

    /**
     * Gets all entries with additional details
     *
     * @param int $limitToTeamsOfUser  User id. If 0, no restriction to user teams will be done
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAllPlus(int $limitToTeamsOfUser)
    {
        $select = new Select();
        $select->columns([
            'userId' => 'user_id',
            'username',
            'email',
            'displayName' => 'display_name',
            'state',
        ])
            ->from($this->tableGateway->table)
            ->join('user_role_linker', 'user_role_linker.user_id = user.user_id', [
            'roleId' => 'role_id'
        ], Select::JOIN_INNER)
            ->join('team_member', 'team_member.user_id = user.user_id', [], Select::JOIN_LEFT)
            ->join('team', 'team.id = team_member.team_id', [
            'teamName' => 'name',
        ], Select::JOIN_LEFT);

        $returnArray = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        $sql = new Sql($this->tableGateway->adapter, $this->tableGateway->table);
        $results = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($results as $result) {
            $result['userId'] = (int) $result['userId'];
            $result['username'] = (string) $result['username'];
            $result['email'] = (string) $result['email'];
            $result['displayName'] = (string) $result['displayName'];
            $result['state'] = (bool) $result['state'];
            $result['roleId'] = (string) $result['roleId'];

            $returnArray[] = new ArrayObject($result, ArrayObject::ARRAY_AS_PROPS);
        }

        return $returnArray;
    }
}
