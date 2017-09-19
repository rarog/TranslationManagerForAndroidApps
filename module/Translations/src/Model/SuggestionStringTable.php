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

use ArrayObject;
use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;

class SuggestionStringTable
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
     * @param int $suggestionId
     * @throws RuntimeException
     * @return \Translations\Model\SuggestionString
     */
    public function getSuggestionString(int $suggestionId)
    {
        $rowset = $this->tableGateway->select(['suggestion_id' => $suggestionId]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $suggestionId));
        }

        return $row;
    }

    /**
     * Suggestion string save function
     *
     * @param SuggestionString $suggestionString
     * @throws RuntimeException
     * @return \Translations\Model\SuggestionString
     */
    public function saveSuggestionString(SuggestionString $suggestionString)
    {
        $data = [
            'value' => $suggestionString->Value,
        ];

        $suggestionId = (int) $suggestionString->SuggestionId;

        if ($suggestionId === 0) {
            throw new RuntimeException('Cannot handle entry with invalid id');
        }

        try {
            if ($this->getSuggestionString($suggestionId)) {
                $this->tableGateway->update($data, ['suggestion_id' => $suggestionId]);
            }
        } catch (RuntimeException $e) {
            $data['suggestion_id'] = $suggestionId;
            $this->tableGateway->insert($data);
        }

        return $suggestionString;
    }

    /**
     * Suggestion string delete function
     * It shouldn't be called directly and therefore commented out.
     *
     * @param int $suggestionId
     */
    /*public function deleteSuggestionString(int $suggestionId)
    {
        $this->tableGateway->delete(['suggestion_id' => $suggestionId]);
    }*/

    /**
     * Gets array of all suggestion strings for translation
     *
     * @param int $entryCommonId
     * @param int $userId
     * @param int $suggestionId
     * @return \ArrayObject
     */
    public function getAllSuggestionsForTranslations(int $entryCommonId, int $userId, int $suggestionId = 0)
    {
        // List of voters
        $select = new Select;
        $select->columns([])->from($this->tableGateway->table);

        $onSuggestions = new Expression('? = ? AND ? = ?', [
            ['suggestion.id' => Expression::TYPE_IDENTIFIER],
            ['suggestion_string.suggestion_id' => Expression::TYPE_IDENTIFIER],
            ['suggestion.entry_common_id' => Expression::TYPE_IDENTIFIER],
            [$entryCommonId => Expression::TYPE_VALUE],
        ]);
        $select->join('suggestion', $onSuggestions, 'id', Select::JOIN_INNER);

        $select->join('suggestion_vote', 'suggestion_vote.suggestion_id = suggestion.id',[], $select::JOIN_INNER);

        $select->join('user', 'user.user_id = suggestion_vote.user_id', 'username', $select::JOIN_INNER);

        if ($suggestionId > 0) {
            $select->where(['suggestion.id' => $suggestionId]);
        }

        $votes = [];

        $sql = new Sql($this->tableGateway->adapter, $this->tableGateway->table);
        $results = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($results as $result) {
            $result['id'] = (int) $result['id'];

            if (! array_key_exists($result['id'], $votes)) {
                $votes[$result['id']] = [];
            }
            $votes[$result['id']][] = $result['username'];
        }

        // Suggestions
        $select = new Select;
        $select->columns([
            'value',
        ])->from($this->tableGateway->table);

        $onSuggestions = new Expression('? = ? AND ? = ?', [
            ['suggestion.id' => Expression::TYPE_IDENTIFIER],
            ['suggestion_string.suggestion_id' => Expression::TYPE_IDENTIFIER],
            ['suggestion.entry_common_id' => Expression::TYPE_IDENTIFIER],
            [$entryCommonId => Expression::TYPE_VALUE],
        ]);
        $select->join('suggestion', $onSuggestions, [
            'id' => 'id',
            'entryCommonId' => 'entry_common_id',
            'userId' => 'user_id',
            'lastChange' => 'last_change',
        ], Select::JOIN_INNER);

        $select->join('user', 'user.user_id = suggestion.user_id', 'username', $select::JOIN_LEFT);

        $onEntryCount = new Expression('? = ? AND ? = ?', [
            ['vote_count.suggestion_id' => Expression::TYPE_IDENTIFIER],
            ['suggestion.id' => Expression::TYPE_IDENTIFIER],
            ['vote_count.user_id' => Expression::TYPE_IDENTIFIER],
            [$userId=> Expression::TYPE_VALUE],
        ]);
        $select->join(['vote_count' => 'suggestion_vote'], $onEntryCount,[
            'voteCount' => new Expression('count(distinct vote_count.user_id)'),
        ], $select::JOIN_LEFT);

        $select->join(['vote_count_all' => 'suggestion_vote'], 'vote_count_all.suggestion_id = suggestion.id',[
            'voteCountAll' => new Expression('count(distinct vote_count_all.user_id)'),
        ], $select::JOIN_LEFT);

        $select->group([
            'suggestion_string.value',
            'suggestion.id',
            'suggestion.entry_common_id',
            'suggestion.user_id',
            'suggestion.last_change',
        ]);

        if ($suggestionId > 0) {
            $select->where(['suggestion.id' => $suggestionId]);
        }

        $select->order([
            'voteCountAll DESC',
            'suggestion.last_change ASC',
        ]);

        $returnArray = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        $sql = new Sql($this->tableGateway->adapter, $this->tableGateway->table);
        $results = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($results as $result) {
            $result['id'] = (int) $result['id'];
            $result['entryCommonId'] = (int) $result['entryCommonId'];
            $result['userId'] = (int) $result['userId'];
            $result['lastChange'] = (int) $result['lastChange'];
            $result['voteCount'] = (int) $result['voteCount'];
            $result['voteCountAll'] = (int) $result['voteCountAll'];

            if (array_key_exists($result['id'], $votes)) {
                $result['votes'] = $votes[$result['id']];
            } else {
                $result['votes'] = [];
            }

            $returnArray[] = new ArrayObject($result, ArrayObject::ARRAY_AS_PROPS);
        }

        return $returnArray;
    }
}
