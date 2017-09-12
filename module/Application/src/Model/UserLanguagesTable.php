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

class UserLanguagesTable
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
     * Get all entries of user
     *
     * @param  int $userId
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAllOfUser(int $userId)
    {
        return $this->fetchAll(['user_id' => $userId]);
    }

    /**
     * Get entry
     *
     * @param int $userId
     * @param string $locale
     * @throws RuntimeException
     * @return UserLanguages
     */
    public function getUserLanguage(int $userId, string $locale)
    {
        $rowset = $this->fetchAll([
            'user_id' => $userId,
            'locale' => $locale,
        ]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifiers %d,%d', $userId, $locale));
        }

        return $row;
    }

    /**
     * User language save function
     *
     * @param UserLanguages $userLanguage
     * @throws RuntimeException
     * @return UserLanguages
     */
    public function saveUserLanguage(UserLanguages $userLanguage)
    {
        $data = [
            'user_id' => $userLanguage->UserId,
            'locale' => $userLanguage->Locale,
        ];

        $userId = (int) $userLanguage->UserId;

        if ($userId === 0) {
            throw new RuntimeException('Cannot handle userlanguage with invalid user id');
        }

        try {
            if (($userLanguage = $this->getUserLanguage($userId, $userLanguage->Locale))) {
                return $userLanguage;
            }
        } catch (RuntimeException $e) {
            $this->tableGateway->insert($data);
            $userLanguage = $this->getUserLanguage($userId, $userLanguage->Locale);
            return $userLanguage;
        }
    }

    /**
     * User language delete function
     *
     * @param int $userId
     * @param string $locale
     */
    public function deleteUserLanguage(int $userId, string $locale)
    {
        $this->tableGateway->delete([
            'user_id' => $userId,
            'locale'  => $locale,
        ]);
    }
}
