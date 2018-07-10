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

use Common\Model\AbstractDbTable;
use RuntimeException;

class SuggestionTable extends AbstractDbTable
{
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
            $suggestion->setId($this->tableGateway->getLastInsertValue());
            return;
        }

        try {
            if ($this->getSuggestion($id)) {
                $this->tableGateway->update($data, ['id' => $id]);
            }
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf(
                'Cannot update row with identifier %d; does not exist',
                $id
            ));
        }
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

    /**
     * Suggestion delete function - deletes by entry common id
     *
     * @param int $entryCommonId
     */
    public function deleteSuggestionByEntryCommonId(int $entryCommonId)
    {
        $this->tableGateway->delete(['entry_common_id' => $entryCommonId]);
    }
}
