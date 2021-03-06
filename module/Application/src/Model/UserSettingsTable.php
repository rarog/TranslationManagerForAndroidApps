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

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;

class UserSettingsTable
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
     * @param  int $id
     * @throws RuntimeException
     * @return \Application\Model\UserSettings
     */
    public function getUserSettings(int $id)
    {
        $rowset = $this->tableGateway->select(['user_id' => $id]);
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
     * User settings save function
     *
     * @param  UserSettings $userSettings
     * @throws RuntimeException
     * @return \Application\Model\UserSettings
     */
    public function saveUserSettings(UserSettings $userSettings)
    {
        $data = [
            'locale' => $userSettings->Locale,
        ];

        $userId = (int) $userSettings->UserId;

        if ($userId === 0) {
            throw new RuntimeException('Cannot handle user settings with invalid id');
        }

        try {
            if ($this->getUserSettings($userId)) {
                $this->tableGateway->update($data, ['user_id' => $userId]);
            }
        } catch (RuntimeException $e) {
            $data['user_id'] = $userId;
            $this->tableGateway->insert($data);
        }

        return $userSettings;
    }

    /**
     * User settings delete function
     * It shouldn't be called directly and therefore commented out.
     *
     * @param int $id
     */
    /*public function deleteUserSettings(int $id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }*/
}
