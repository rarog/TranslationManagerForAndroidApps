<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class TeamTable
{
    /**
     * @var TableGateway
     */
    private $tableGateway;

    /**
     * Constructor
     *
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Gets all entries
     *
     * @param \Zend\Db\Sql\Where|\Closure|string|array $where
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll($where = null)
    {
        return $this->tableGateway->select(
            function (Select $select) use ($where) {
                if ($where) {
                    $select->where($where);
                }
            }
        );
    }

    /**
     * Gets all entries allowed to user
     *
     * @param int $userId
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAllAllowedToUser($userId)
    {
        $userId = (int) $userId;
        return $this->tableGateway->select(
            function (Select $select) use ($userId) {
                $onTeamMember = new Expression('? = ? AND ? = ?', [
                    ['team_member.team_id' => Expression::TYPE_IDENTIFIER],
                    ['team.id' => Expression::TYPE_IDENTIFIER],
                    ['team_member.user_id' => Expression::TYPE_IDENTIFIER],
                    [$userId  => Expression::TYPE_VALUE]]);
                $select->join('team_member', $onTeamMember, [], Select::JOIN_INNER);
            }
        );
    }

    /**
     * Get entry
     *
     * @param  int $id
     * @throws RuntimeException
     * @return \Translations\Model\Team
     */
    public function getTeam($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    /**
     * Checks if user has permission for team
     *
     * @param int $userId
     * @param int $teamId
     * @return boolean
     */
    public function hasUserPermissionForTeam($userId, $teamId)
    {
        $userId = (int) $userId;
        $teamId = (int) $teamId;
        $rowset = $this->tableGateway->select(
            function (Select $select) use ($userId, $teamId) {
                $onTeamMember = new Expression('? = ? AND ? = ?', [
                    ['team_member.team_id' => Expression::TYPE_IDENTIFIER],
                    ['team.id' => Expression::TYPE_IDENTIFIER],
                    ['team_member.user_id' => Expression::TYPE_IDENTIFIER],
                    [$userId  => Expression::TYPE_VALUE]]);
                $select->join('team_member', $onTeamMember, [], Select::JOIN_INNER)
                    ->where(['id' => $teamId]);
            }
        );
        $row = $rowset->current();

        return !is_null($row);
    }

    /**
     * Team save function
     *
     * @param  Team $team
     * @throws RuntimeException
     * @return \Translations\Model\Team
     */
    public function saveTeam(Team $team)
    {
        $data = [
            'name' => $team->Name,
        ];

        $id = (int) $team->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $team->Id = $this->tableGateway->getLastInsertValue();
            return $team;
        }

        if (!$this->getTeam($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update team with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $team;
    }

    /**
     * Team delete function
     *
     * @param int $id
     */
    public function deleteTeam($id)
    {
        $id = (int) $id;
        $this->tableGateway->delete(['id' => $id]);
    }
}
