<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
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
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    /**
     * Get entry
     *
     * @param  int $id
     * @throws RuntimeException
     * @return \Translations\Model\Team
     */
    public function getApp($id)
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
     * Team save function
     *
     * @param  Team $team
     * @throws RuntimeException
     * @return \Translations\Model\Team
     */
    public function saveTeam(Team $team)
    {
        $data = [
            'name' => $team->name,
        ];

        $id = (int) $team->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $team->id = $this->tableGateway->getLastInsertValue();
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
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}
