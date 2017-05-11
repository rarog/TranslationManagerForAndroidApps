<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
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
        return $this->tableGateway->select($where);
    }

    /**
     * Get entry
     *
     * @param int $userId
     * @param int $teamId
     * @throws RuntimeException
     * @return \Translations\Model\TeamMember
     */
    public function getTeamMember($userId, $teamId)
    {
        $userId = (int) $userId;
        $teamId = (int) $teamId;
        $rowset = $this->tableGateway->select([
            'user_id' => $userId,
            'team_id' => $teamId,
        ]);
        $row = $rowset->current();
        if (!$row) {
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
    public function saveUserSettings(TeamMember $teamMember)
    {
        $data = [
            'user_id' => $teamMember->userId,
            'team_id' => $teamMember->teamId,
        ];

        $userId = (int) $teamMember->userId;
        $teamId = (int) $teamMember->teamId;

        if (($userId === 0) || ($teamId === 0)) {
            throw new RuntimeException('Cannot handle user settings with invalid ids');
        }

        try {
            if ($this->getTeamMember($userId, $teamId)) {
                return $teamMember;
            }
        } catch (RuntimeException $e) {
            $this->tableGateway->insert($data);
        }

        return $teamMember;
    }

    /**
     * Team member delete function
     *
     * @param int $userId
     * @param int $teamId
     */
    public function deleteUserSettings($userId, $teamId)
    {
        $this->tableGateway->delete([
            'user_id' => (int) $userId,
            'team_id' => (int) $teamId,
        ]);
    }
}
