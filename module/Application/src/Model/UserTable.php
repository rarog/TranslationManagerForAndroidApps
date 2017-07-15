<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Model;

use RuntimeException;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\TableGateway;
use ZfcUser\Entity\User;
use ZfcUser\Mapper\User as UserMapper;

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
    public function fetchAllNotInTeam($teamId)
    {
        $teamId = (int) $teamId;

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
    public function getUser($id)
    {
        $id = (int) $id;
        $row = $this->userMapper->findById($id);
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }
}
