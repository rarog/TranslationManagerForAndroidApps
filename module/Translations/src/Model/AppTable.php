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

class AppTable
{
    /**
     * @var TableGateway
     */
    private $tableGateway;

    /**
     * Columns of the app table
     *
     * @var array
     */
    private $columns = ['id', 'team_id', 'name', 'git_repository', 'path_to_res_folder'];

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
                $select->columns($this->columns)
                    ->join('app_resource', 'app_resource.app_id = app.id', ['resource_count' => new Expression('count(app_resource.app_id)')], $select::JOIN_LEFT)
                    ->group($this->columns);

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
                    ['app.team_id' => Expression::TYPE_IDENTIFIER],
                    ['team_member.user_id' => Expression::TYPE_IDENTIFIER],
                    [$userId  => Expression::TYPE_VALUE]]);
                $select->columns($this->columns)
                    ->join('team_member', $onTeamMember, [], Select::JOIN_INNER)
                    ->join('app_resource', 'app_resource.app_id = app.id', ['resource_count' => new Expression('count(app_resource.app_id)')], $select::JOIN_LEFT)
                    ->group($this->columns);
            }
        );
    }

    /**
     * Get entry
     *
     * @param  int $id
     * @throws RuntimeException
     * @return \Translations\Model\App
     */
    public function getApp($id)
    {
        $id = (int) $id;
        $rowset = $this->fetchAll(['app_resource.id' => $id]);
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
     * Checks if user has permission for app
     *
     * @param int $userId
     * @param int $appId
     * @return boolean
     */
    public function hasUserPermissionForApp($userId, $appId)
    {
        $userId = (int) $userId;
        $appId = (int) $appId;
        $rowset = $this->tableGateway->select(
                function (Select $select) use ($userId, $appId) {
                    $onTeamMember = new Expression('? = ? AND ? = ?', [
                        ['team_member.team_id' => Expression::TYPE_IDENTIFIER],
                        ['app.team_id' => Expression::TYPE_IDENTIFIER],
                        ['team_member.user_id' => Expression::TYPE_IDENTIFIER],
                        [$userId  => Expression::TYPE_VALUE]]);
                    $select->join('team_member', $onTeamMember, [], Select::JOIN_INNER)
                        ->where(['id' => $appId]);
                });
        $row = $rowset->current();

        return !is_null($row);
    }

    /**
     * App save function
     *
     * @param  App $app
     * @throws RuntimeException
     * @return \Translations\Model\App
     */
    public function saveApp(App $app)
    {
        $data = [
            'team_id'            => $app->teamId,
            'name'               => $app->name,
            'git_repository'     => $app->gitRepository,
            'path_to_res_folder' => $app->pathToResFolder,
        ];

        $id = (int) $app->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $app->id = $this->tableGateway->getLastInsertValue();
            return $app;
        }

        if (!$this->getApp($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update app with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $app;
    }

    /**
     * App delete function
     *
     * @param int $id
     */
    public function deleteApp($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}
