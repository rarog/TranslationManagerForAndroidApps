<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Model;

use Application\Model\UserLanguages;
use RuntimeException;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
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
    public function fetchAllOfUser($userId)
    {
        $userId = (int) $userId;
        return $this->fetchAll(['user_id' => $userId]);
    }

    /**
     * Get entry
     *
     * @param int $userId
     * @param string $locale
     * @throws RuntimeException
     * @return \Application\Model\UserLanguages
     */
    public function getUserLanguage($userId, $locale)
    {
        $userId = (int) $userId;
        $locale = (string) $locale;
        $rowset = $this->fetchAll([
            'user_id' => $userId,
            'locale'  => $locale,
        ]);
        $row = $rowset->current();
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifiers %d,%d',
                $userId,
                $locale
            ));
        }

        return $row;
    }

    /**
     * User language save function
     *
     * @param UserLanguages $userLanguage
     * @throws RuntimeException
     * @return \Application\Model\UserLanguages
     */
    public function saveUserLanguage(UserLanguages $userLanguage)
    {
        $data = [
            'user_id' => $userLanguage->UserId,
            'locale'  => $userLanguage->Locale,
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
    public function deleteUserLanguage($userId, $locale)
    {
        $userId = (int) $userId;
        $locale = (string) $locale;
        $this->tableGateway->delete([
            'user_id' => $userId,
            'locale'  => $locale,
        ]);
    }
}