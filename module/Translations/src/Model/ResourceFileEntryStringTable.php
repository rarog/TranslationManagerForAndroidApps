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
     * @var AppResourceTable
     */
    private $appResourceTable;

    /**
     * Constructor
     *
     * @param TableGateway $tableGateway
     * @param AppResourceTable $appResourceTable
     */
    public function __construct(TableGateway $tableGateway, AppResourceTable $appResourceTable)
    {
        $this->tableGateway = $tableGateway;
        $this->appResourceTable = $appResourceTable;
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
     * @return ResourceFileEntryString
     */
    public function getResourceFileEntryString($id)
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
     * Resource file entry string save function
     *
     * @param ResourceFileEntryString $resourceFileEntryString
     * @throws RuntimeException
     * @return ResourceFileEntryString
     */
    public function saveResourceFileEntryString(ResourceFileEntryString $resourceFileEntryString)
    {
        $data = [
            'app_resource_id' => $resourceFileEntryString->AppResourceId,
            'resource_file_entry_id' => $resourceFileEntryString->ResourceFileEntryId,
            'value'  => $resourceFileEntryString->Value,
            'last_change' => $resourceFileEntryString->LastChange,
        ];

        $id = (int) $resourceFileEntryString->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $resourceFileEntryString->Id = $this->tableGateway->getLastInsertValue();
            return $resourceFileEntryString;
        }

        if (!$this->getResourceFileEntryString($id)) {
            throw new RuntimeException(sprintf('Cannot update resource file entry string with identifier %d; does not exist', $id));
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
    * @param int $entryId
    * @return array
    */
    public function getAllResourceFileEntryStringsForTranslations($appId, $appResourceId, $entryId)
    {
        $appId = (int) $appId;
        $appResourceId = (int) $appResourceId;
        $entryId = (int) $entryId;

        try {
            $defaultAppResource = $this->appResourceTable->getAppResourceByAppIdAndName($appId, 'values');
        } catch (\Exception $e) {
            $defaultAppResource = false;
        }

        $select = new Select;
        $select->columns([
            'defaultId' => 'id',
            'appResourceId'  => 'app_resource_id',
            'resourceFileEntryId' => 'resource_file_entry_id',
            'defaultValue' => 'value',
            'defaultLastChange' => 'last_change',
        ])->from(['default' => $this->tableGateway->table]);

        $onAppResource = new Expression('? = ? AND ? = ? AND ? = ?', [
            ['app_resource.id'  => Expression::TYPE_IDENTIFIER],
            ['default.app_resource_id' => Expression::TYPE_IDENTIFIER],
            ['app_resource.app_id' => Expression::TYPE_IDENTIFIER],
            [$appId => Expression::TYPE_VALUE],
            ['app_resource.name' => Expression::TYPE_IDENTIFIER],
            ['values' => Expression::TYPE_VALUE],
        ]);
        $select->join('app_resource', $onAppResource, [], Select::JOIN_INNER);

        if (($defaultAppResource === false) || ($defaultAppResource->Id !== $appResourceId)) {
            $onResourceFileEntry = new Expression('? = ? AND ? = ? AND ? = ?', [
                ['resource_file_entry.id' => Expression::TYPE_IDENTIFIER],
                ['default.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
                ['resource_file_entry.deleted' => Expression::TYPE_IDENTIFIER],
                [0 => Expression::TYPE_VALUE],
                ['resource_file_entry.translatable' => Expression::TYPE_IDENTIFIER],
                [1 => Expression::TYPE_VALUE],
            ]);
        } else {
            $onResourceFileEntry = new Expression('? = ? AND ? = ?', [
                ['resource_file_entry.id' => Expression::TYPE_IDENTIFIER],
                ['default.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
                ['resource_file_entry.deleted' => Expression::TYPE_IDENTIFIER],
                [0 => Expression::TYPE_VALUE],
            ]);
        }
        $select->join('resource_file_entry', $onResourceFileEntry, [
            'resourceTypeId' => 'resource_type_id',
            'name' => 'name',
            'product' => 'product',
            'description' => 'description',
        ], Select::JOIN_INNER);

        $onEntryCount = new Expression('? = ? AND ? = ? AND ? = ?', [
            ['entry_count.app_resource_file_id' => Expression::TYPE_IDENTIFIER],
            ['resource_file_entry.app_resource_file_id' => Expression::TYPE_IDENTIFIER],
            ['entry_count.name' => Expression::TYPE_IDENTIFIER],
            ['resource_file_entry.name' => Expression::TYPE_IDENTIFIER],
            ['entry_count.deleted' => Expression::TYPE_IDENTIFIER],
            [0 => Expression::TYPE_VALUE],
        ]);
        $select->join(['entry_count' => 'resource_file_entry'], $onEntryCount,[
            'entryCount' => new Expression('count(distinct entry_count.id)'),
        ], $select::JOIN_LEFT);

        $onResourceFileEntryString = new Expression('? = ? AND ? = ?', [
            ['resource_file_entry_string.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
            ['default.resource_file_entry_id'  => Expression::TYPE_IDENTIFIER],
            ['resource_file_entry_string.app_resource_id' => Expression::TYPE_IDENTIFIER],
            [$appResourceId => Expression::TYPE_VALUE],
        ]);
        $select->join('resource_file_entry_string', $onResourceFileEntryString, [
            'id' => 'id',
            'value' => 'value',
            'lastChange' => 'last_change',
        ], Select::JOIN_LEFT);

        $select->join('resource_file_entry_string_suggestion', 'resource_file_entry_string_suggestion.resource_file_entry_string_id = resource_file_entry_string.id',[
            'suggestionCount' => new Expression('count(distinct resource_file_entry_string_suggestion.id)'),
        ], $select::JOIN_LEFT);

        $select->group([
            'default.id',
            'default.app_resource_id',
            'default.resource_file_entry_id',
            'default.value',
            'default.last_change',
            'resource_file_entry.name',
            'resource_file_entry.product',
            'resource_file_entry.description',
            'resource_file_entry_string.id',
            'resource_file_entry_string.value',
            'resource_file_entry_string.last_change',
        ]);

        if ($entryId > 0) {
            $select->where(['default.id' => $entryId]);
        }

        $returnArray = [];

        $sql = new Sql($this->tableGateway->adapter, $this->tableGateway->table);
        $results = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($results as $result) {
            $returnArray[] = $result;
        }

        return $returnArray;
    }
}
