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

namespace Translations\Model;

use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
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
    private $columns = ['id', 'team_id', 'name', 'path_to_res_folder', 'git_repository', 'git_username', 'git_password', 'git_user', 'git_email'];

    /**
     * Prefixed columns of the app table
     *
     * @var array
     */
    private $columnsPrefixed = ['app.id', 'app.team_id', 'app.name', 'app.path_to_res_folder', 'app.git_repository', 'app.git_username', 'app.git_password', 'app.git_user', 'app.git_email'];

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
                    ->join('app_resource', 'app_resource.app_id = app.id', ['resource_count' => new Expression('count(distinct app_resource.id)')], $select::JOIN_LEFT)
                    ->join('app_resource_file', 'app_resource_file.app_id = app.id', ['resource_file_count' => new Expression('count(distinct app_resource_file.id)')], $select::JOIN_LEFT)
                    ->group($this->columnsPrefixed);

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
    public function fetchAllAllowedToUser(int $userId)
    {
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
                    ->join('app_resource_file', 'app_resource_file.app_id = app.id', ['resource_file_count' => new Expression('count(app_resource_file.app_id)')], $select::JOIN_LEFT)
                    ->group($this->columnsPrefixed);
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
    public function getApp(int $id)
    {
        $rowset = $this->fetchAll(['app.id' => $id]);
        $row = $rowset->current();
        if (! $row) {
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
    public function hasUserPermissionForApp(int $userId, int $appId)
    {
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
            'team_id' => $app->TeamId,
            'name' => $app->Name,
            'path_to_res_folder' => $app->PathToResFolder,
            'git_repository' => $app->GitRepository,
            'git_username' => $app->GitUsername,
            'git_password' => $app->GitPassword,
            'git_user' => $app->GitUser,
            'git_email' => $app->GitEmail,
        ];

        $id = (int) $app->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $app->Id = $this->tableGateway->getLastInsertValue();
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
    public function deleteApp(int $id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }

    /**
     * Gets array of all apps and their resources allowed to user
     *
     * @param int $userId
     * @return array
     */
    public function getAllAppsAndResourcesAllowedToUser(int $userId)
    {
        $select = new Select;
        $select->columns([
            'app_id'   => 'id',
            'app_name' => 'name',
        ])->from($this->tableGateway->table);

        if ($userId > 0) {
            $onTeamMember = new Expression('? = ? AND ? = ?', [
                ['team_member.team_id' => Expression::TYPE_IDENTIFIER],
                ['app.team_id' => Expression::TYPE_IDENTIFIER],
                ['team_member.user_id' => Expression::TYPE_IDENTIFIER],
                [$userId  => Expression::TYPE_VALUE]]);
            $select->join('team_member', $onTeamMember, [], Select::JOIN_INNER);
        }

        $select->join('app_resource', 'app_resource.app_id = app.id', [
            'app_resource_id'   => 'id',
            'app_resource_name' => 'name',
            'locale'            => 'locale',
        ], $select::JOIN_INNER);

        if ($userId > 0) {
            $onUserLanguages = new Expression('? = ? AND ? = ?', [
                ['user_languages.locale' => Expression::TYPE_IDENTIFIER],
                ['app_resource.primary_locale' => Expression::TYPE_IDENTIFIER],
                ['user_languages.user_id' => Expression::TYPE_IDENTIFIER],
                [$userId  => Expression::TYPE_VALUE]]);
            $select->join('user_languages', $onUserLanguages, [], Select::JOIN_INNER);
        }

        $select->order([
            'app.name',
            'app_resource.locale'
        ]);

        $returnArray = [];

        $sql = new Sql($this->tableGateway->adapter, $this->tableGateway->table);
        $results = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($results as $result) {
            $returnArray[] = $result;
        }

        return $returnArray;
    }
}
