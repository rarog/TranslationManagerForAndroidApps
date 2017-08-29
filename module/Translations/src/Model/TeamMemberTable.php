<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class TeamMemberTable
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
                $select->join('user', 'user.user_id = team_member.user_id', ['username', 'email', 'display_name'], $select::JOIN_LEFT)
                    ->join('team', 'team.id = team_member.team_id', ['team_name' => 'name'], $select::JOIN_LEFT);
                if ($where) {
                    $select->where($where);
                }
                $select->order(['team.name ASC', 'username ASC']);
            }
        );
    }

    /**
     * Get entry
     *
     * @param int $userId
     * @param int $teamId
     * @throws RuntimeException
     * @return \Translations\Model\TeamMember
     */
    public function getTeamMember(int $userId, int $teamId)
    {
        $rowset = $this->fetchAll([
            'team_member.user_id' => $userId,
            'team_member.team_id' => $teamId,
        ]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifiers %d,%d',
                $userId,
                $teamId
            ));
        }

        return $row;
    }

    /**
     * Team member save function
     *
     * @param TeamMember $teamMember
     * @throws RuntimeException
     * @return \Translations\Model\TeamMember
     */
    public function saveTeamMember(TeamMember $teamMember)
    {
        $data = [
            'user_id' => $teamMember->UserId,
            'team_id' => $teamMember->TeamId,
        ];

        $userId = (int) $teamMember->UserId;
        $teamId = (int) $teamMember->TeamId;

        if (($userId === 0) || ($teamId === 0)) {
            throw new RuntimeException('Cannot handle team member with invalid ids');
        }

        try {
            if (($teamMember = $this->getTeamMember($userId, $teamId))) {
                return $teamMember;
            }
        } catch (RuntimeException $e) {
            $this->tableGateway->insert($data);
            $teamMember = $this->getTeamMember($userId, $teamId);
            return $teamMember;
        }
    }

    /**
     * Team member delete function
     *
     * @param int $userId
     * @param int $teamId
     */
    public function deleteTeamMember(int $userId, int $teamId)
    {
        $this->tableGateway->delete([
            'user_id' => $userId,
            'team_id' => $teamId,
        ]);
    }
}
