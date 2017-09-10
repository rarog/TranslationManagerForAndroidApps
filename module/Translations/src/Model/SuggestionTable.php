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

class SuggestionTable
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
     * @param int $id
     * @throws RuntimeException
     * @return \Translations\Model\Suggestion
     */
    public function getSuggestion(int $id)
    {
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $id));
        }

        return $row;
    }

    /**
     * Suggestion save function
     *
     * @param Suggestion $suggestion
     * @throws RuntimeException
     * @return \Translations\Model\Suggestion
     */
    public function saveSuggestion(Suggestion $suggestion)
    {
        $data = [
            'entry_common_id' => $suggestion->EntryCommonId,
            'user_id' => $suggestion->UserId,
            'last_change' => $suggestion->LastChange,
        ];

        $id = (int) $suggestion->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $suggestion->Id = $this->tableGateway->getLastInsertValue();
            return $suggestion;
        }

        if (! $this->getSuggestion($id)) {
            throw new RuntimeException(sprintf('Cannot update row with identifier %d; does not exist', $id));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $suggestion;
    }

    /**
     * Suggestion delete function
     *
     * @param int $id
     */
    public function deleteSuggestion(int $id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }
}
