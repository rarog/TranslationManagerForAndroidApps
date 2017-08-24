<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;

class ResourceFileEntryTable
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
     * @return ResourceFileEntry
     */
    public function getResourceFileEntry($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (!$row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $id));
        }

        return $row;
    }

    /**
     * Resource file entry save function
     *
     * @param ResourceFileEntry $resourceFileEntry
     * @throws RuntimeException
     * @return ResourceFileEntry
     */
    public function saveResourceFileEntry(ResourceFileEntry $resourceFileEntry)
    {
        $data = [
            'app_resource_file_id' => $resourceFileEntry->AppResourceFileId,
            'resource_type_id' => $resourceFileEntry->ResourceTypeId,
            'name' => $resourceFileEntry->Name,
            'product' => $resourceFileEntry->Product,
            'description' => $resourceFileEntry->Description,
            'deleted' => $resourceFileEntry->Deleted,
            'translatable' => $resourceFileEntry->Translatable,
        ];

        $id = (int) $resourceFileEntry->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $resourceFileEntry->Id = $this->tableGateway->getLastInsertValue();
            return $resourceFileEntry;
        }

        if (!$this->getResourceFileEntry($id)) {
            throw new RuntimeException(sprintf('Cannot update resource file entry with identifier %d; does not exist', $id));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $resourceFileEntry;
    }

    /**
     * Resource file entry delete function
     *
     * @param int $id
     */
    public function deleteResourceFileEntry($id)
    {
        $id = (int) $id;
        $this->tableGateway->delete(['id' => $id]);
    }
}
