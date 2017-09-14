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
use Zend\Db\TableGateway\TableGateway;

class SuggestionVoteTable
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
     * @param int $suggestionId
     * @param int $userId
     * @throws RuntimeException
     * @return SuggestionVote
     */
    public function getSuggestionVote(int $suggestionId, int $userId)
    {
        $rowset = $this->tableGateway->select(['suggestion_id' => $suggestionId]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifiers %d,%d', $suggestionId, $userId));
        }

        return $row;
    }

    /**
     * Suggestion vote save function
     *
     * @param SuggestionVote $userLanguage
     * @throws RuntimeException
     * @return SuggestionVote
     */
    public function saveSuggestionVote(SuggestionVote $suggestionVote)
    {
        $data = [
            'suggestion_id' => $suggestionVote->SuggestionId,
            'user_id' => $suggestionVote->UserId,
        ];

        $suggestionId = (int) $suggestionVote->SuggestionId;
        $userId = (int) $suggestionVote->UserId;

        if ($suggestionId === 0 || $userId === 0) {
            throw new RuntimeException('Cannot handle row with invalid id');
        }

        try {
            if (($suggestionVote = $this->getSuggestionVote($suggestionId, $userId))) {
                return $suggestionVote;
            }
        } catch (RuntimeException $e) {
            $this->tableGateway->insert($data);
            return $suggestionVote;
        }
    }


    /**
     * Suggestion vote delete function
     *
     * @param int $suggestionId
     * @param int $userId
     */
    public function deleteSuggestionVote(int $suggestionId, int $userId = 0)
    {
        $deleteParams = ['suggestion_id' => $suggestionId];

        if ($userId > 0) {
            $deleteParams = ['user_id' => $userId];
        }

        $this->tableGateway->delete($deleteParams);
    }
}
