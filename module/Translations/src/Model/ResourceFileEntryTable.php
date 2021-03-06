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
    public function getResourceFileEntry(int $id)
    {
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
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
            'app_resource_file_id' => (int) $resourceFileEntry->AppResourceFileId,
            'resource_type_id' => (int) $resourceFileEntry->ResourceTypeId,
            'name' => (string) $resourceFileEntry->Name,
            'product' => (string) $resourceFileEntry->Product,
            'description' => is_null($resourceFileEntry->Description) ? null : (string) $resourceFileEntry->Description,
            'deleted' => (int) $resourceFileEntry->Deleted,
            'translatable' => (int) $resourceFileEntry->Translatable,
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
    public function deleteResourceFileEntry(int $id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }
}
