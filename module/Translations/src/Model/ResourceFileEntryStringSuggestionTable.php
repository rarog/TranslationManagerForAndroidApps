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

class ResourceFileEntryStringSuggestionTable
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
     * @return ResourceFileEntryStringSuggestion
     */
    public function getResourceFileEntryStringSuggestion(int $id)
    {
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $id));
        }

        return $row;
    }

    /**
     * Resource file entry string suggestion save function
     *
     * @param ResourceFileEntryStringSuggestion $resourceFileEntryStringSuggestion
     * @throws RuntimeException
     * @return ResourceFileEntryStringSuggestion
     */
    public function saveResourceFileEntryStringSuggestion(ResourceFileEntryStringSuggestion $resourceFileEntryStringSuggestion)
    {
        $data = [
            'resource_file_entry_string_id' => $resourceFileEntryStringSuggestion->ResourceFileEntryStringId,
            'user_id' => $resourceFileEntryStringSuggestion->UserId,
            'value' => $resourceFileEntryStringSuggestion->Value,
            'created' => $resourceFileEntryStringSuggestion->Created,
        ];

        $id = (int) $resourceFileEntryStringSuggestion->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $resourceFileEntryStringSuggestion->Id = $this->tableGateway->getLastInsertValue();
            return $resourceFileEntryStringSuggestion;
        }

        if (!$this->getResourceFileEntryStringSuggestion($id)) {
            throw new RuntimeException(sprintf('Cannot update resource file entry string suggestion with identifier %d; does not exist', $id));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $resourceFileEntryStringSuggestion;
    }

    /**
     * Resource file entry string suggestion delete function
     *
     * @param int $id
     */
    public function deleteResourceFileEntryStringSuggestion(int $id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }
}
