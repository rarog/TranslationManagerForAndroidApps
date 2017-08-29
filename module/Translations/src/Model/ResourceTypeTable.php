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

class ResourceTypeTable
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
     * @return ResourceType
     */
    public function getResourceType($id)
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
     * Resource type save function
     *
     * @param ResourceType $resourceType
     * @throws RuntimeException
     * @return ResourceType
     */
    public function saveResourceType(ResourceType $resourceType)
    {
        $data = [
            'name' => $resourceType->Name,
            'node_name' => $resourceType->NodeName,
        ];

        $id = (int) $resourceType->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $resourceType->Id = $this->tableGateway->getLastInsertValue();
            return $resourceType;
        }

        if (!$this->getResourceType($id)) {
            throw new RuntimeException(sprintf('Cannot update resource type with identifier %d; does not exist', $id));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $resourceType;
    }

    /**
     * Resource type delete function
     *
     * @param int $id
     */
    public function deleteResourceType($id)
    {
        $id = (int) $id;
        $this->tableGateway->delete(['id' => $id]);
    }
}
