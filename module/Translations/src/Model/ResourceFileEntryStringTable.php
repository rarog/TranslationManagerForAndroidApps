<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
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

    /**
    * Gets array of all strings for translation
    *
    * @param int $appId
    * @param int $appResourceId
    * @return array
    */
    public function getAllResourceFileEntryStringsForTranslations($appId, $appResourceId)
    {
        $appId = (int) $appId;
        $appResourceId = (int) $appResourceId;

        $select = new Select;
        $select->columns([
            'app_resource_id',
            'resource_file_entry_id',
            'default_value'       => 'value',
            'default_last_change' => 'last_change',
        ])->from(['default' => $this->tableGateway->table]);

        $onAppResource = new Expression('? = ? AND ? = ? AND ? = ?', [
            ['app_resource.id'         => Expression::TYPE_IDENTIFIER],
            ['default.app_resource_id' => Expression::TYPE_IDENTIFIER],
            ['app_resource.app_id'     => Expression::TYPE_IDENTIFIER],
            [$appId                    => Expression::TYPE_VALUE],
            ['app_resource.name'       => Expression::TYPE_IDENTIFIER],
            ['values'                  => Expression::TYPE_VALUE]]);
        $select->join('app_resource', $onAppResource, [], Select::JOIN_INNER);

        $onResourceFileEntry = new Expression('? = ? AND ? = ?', [
            ['resource_file_entry.id'         => Expression::TYPE_IDENTIFIER],
            ['default.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
            ['resource_file_entry.deleted'    => Expression::TYPE_IDENTIFIER],
            [0                                => Expression::TYPE_VALUE]]);
        $select->join('resource_file_entry', $onResourceFileEntry, [], Select::JOIN_INNER);

        $onResourceFileEntryString = new Expression('? = ? AND ? = ?', [
            ['resource_file_entry_string.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
            ['default.resource_file_entry_id'                    => Expression::TYPE_IDENTIFIER],
            ['resource_file_entry_string.app_resource_id'        => Expression::TYPE_IDENTIFIER],
            [$appResourceId                                      => Expression::TYPE_VALUE]]);
        $select->join('resource_file_entry_string', $onResourceFileEntryString, [
            'id',
            'value',
            'last_change',
        ], Select::JOIN_LEFT);

        $returnArray = [];

        $sql = new Sql($this->tableGateway->adapter, $this->tableGateway->table);
        $results = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($results as $result) {
            $returnArray[] = $result;
        }

        return $returnArray;
    }
}
