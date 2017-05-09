<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

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
     * @return \Translations\Model\UserSettings
     */
    public function getUserSettings($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['user_id' => $id]);
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
     * User settings save function
     *
     * @param  UserSettings $userSettings
     * @throws RuntimeException
     * @return \Translations\Model\UserSettings
     */
    public function saveUserSettings(UserSettings $userSettings)
    {
        $data = [
            'locale'  => $userSettings->locale,
            'team_id' => $userSettings->teamId,
        ];

        $id = (int) $userSettings->id;

        if ($id === 0) {
            throw new RuntimeException('Cannot handle user settings with invalid id');
        }

        if ($this->getUserSettings($id)) {
            unset($data['id']);
            $this->tableGateway->update($data, ['id' => $id]);
        } else {
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
    /*public function deleteUserSettings($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }*/
}
