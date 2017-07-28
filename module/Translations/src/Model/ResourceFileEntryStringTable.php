<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;

class ResourceFileEntryStringTable
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
     * @param  int $id
     * @throws RuntimeException
     * @return \Translations\Model\ResourceFileEntry
     */
    public function getResourceFileEntryString($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    /**
     * Resource file entry string save function
     *
     * @param  ResourceFileEntryString $resourceFileEntryString
     * @throws RuntimeException
     * @return \Translations\Model\ResourceFileEntryString
     */
    public function saveResourceFileEntryString(ResourceFileEntryString $resourceFileEntryString)
    {
        $data = [
            'app_resource_id'        => $resourceFileEntryString->AppResourceId,
            'resource_file_entry_id' => $resourceFileEntryString->ResourceFileEntryId,
            'value'                  => $resourceFileEntryString->Value,
            'last_change'            => $resourceFileEntryString->LastChange,
        ];

        $id = (int) $resourceFileEntryString->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $resourceFileEntryString->Id = $this->tableGateway->getLastInsertValue();
            return $resourceFileEntryString;
        }

        if (!$this->getResourceFileEntryString($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update resource file entry string with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $resourceFileEntryString;
    }

    /**
     * Resource file entry string delete function
     *
     * @param int $id
     */
    public function deleteResourceFileEntryString($id)
    {
        $id = (int) $id;
        $this->tableGateway->delete(['id' => $id]);
    }
}
