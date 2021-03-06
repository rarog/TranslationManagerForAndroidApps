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

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use ArrayObject;
use RuntimeException;

class EntryStringTable
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
     * @param int $entryCommonId
     * @throws RuntimeException
     * @return \Translations\Model\EntryString
     */
    public function getEntryString(int $entryCommonId)
    {
        $rowset = $this->tableGateway->select(['entry_common_id' => $entryCommonId]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $entryCommonId));
        }

        return $row;
    }

    /**
     * Entry string save function
     *
     * @param EntryString $entryString
     * @throws RuntimeException
     * @return \Translations\Model\EntryString
     */
    public function saveEntryString(EntryString $entryString)
    {
        $data = [
            'value' => $entryString->getValue(),
        ];

        $entryCommonId = (int) $entryString->getEntryCommonId();

        if ($entryCommonId === 0) {
            throw new RuntimeException('Cannot handle entry with invalid id');
        }

        try {
            if ($this->getEntryString($entryCommonId)) {
                $this->tableGateway->update($data, ['entry_common_id' => $entryCommonId]);
            }
        } catch (RuntimeException $e) {
            $data['entry_common_id'] = $entryCommonId;
            $this->tableGateway->insert($data);
        }

        return $entryString;
    }

    /**
     * Entry string delete function
     * It shouldn't be called directly and therefore commented out.
     *
     * @param int $entryCommonId
     */
    /*public function deleteEntryString(int $entryCommonId)
    {
        $this->tableGateway->delete(['entry_common_id' => $entryCommonId]);
    }*/

    /**
     * Gets array of all strings for translation
     *
     * @param int $appId
     * @param int $appResourceId
     * @param int $entryId
     * @return \ArrayObject
     */
    public function getAllEntryStringsForTranslations(int $appId, int $appResourceId, int $entryId)
    {
        try {
            $defaultAppResource = $this->appResourceTable->getAppResourceByAppIdAndName($appId, 'values');
        } catch (RuntimeException $e) {
            $defaultAppResource = false;
        }

        $select = new Select;
        $select->columns([
            'defaultValue' => 'value',
        ])->from(['default' => $this->tableGateway->getTable()]);

        $onDefaultEntryCommon = new Expression('? = ?', [
            ['default_common.id' => Expression::TYPE_IDENTIFIER],
            ['default.entry_common_id' => Expression::TYPE_IDENTIFIER],
        ]);
        $select->join(['default_common' => 'entry_common'], $onDefaultEntryCommon, [
            'defaultId' => 'id',
            'appResourceId'  => 'app_resource_id',
            'resourceFileEntryId' => 'resource_file_entry_id',
            'defaultLastChange' => 'last_change',
        ], Select::JOIN_INNER);

        $onAppResource = new Expression('? = ? AND ? = ? AND ? = ?', [
            ['app_resource.id'  => Expression::TYPE_IDENTIFIER],
            ['default_common.app_resource_id' => Expression::TYPE_IDENTIFIER],
            ['app_resource.app_id' => Expression::TYPE_IDENTIFIER],
            [$appId => Expression::TYPE_VALUE],
            ['app_resource.name' => Expression::TYPE_IDENTIFIER],
            ['values' => Expression::TYPE_VALUE],
        ]);
        $select->join('app_resource', $onAppResource, [], Select::JOIN_INNER);

        if (($defaultAppResource === false) || ($defaultAppResource->getId() !== $appResourceId)) {
            $onResourceFileEntry = new Expression('? = ? AND ? = ? AND ? = ?', [
                ['resource_file_entry.id' => Expression::TYPE_IDENTIFIER],
                ['default_common.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
                ['resource_file_entry.deleted' => Expression::TYPE_IDENTIFIER],
                [0 => Expression::TYPE_VALUE],
                ['resource_file_entry.translatable' => Expression::TYPE_IDENTIFIER],
                [1 => Expression::TYPE_VALUE],
            ]);
        } else {
            $onResourceFileEntry = new Expression('? = ? AND ? = ?', [
                ['resource_file_entry.id' => Expression::TYPE_IDENTIFIER],
                ['default_common.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
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
        $select->join(['entry_count' => 'resource_file_entry'], $onEntryCount, [
            'entryCount' => new Expression('count(distinct entry_count.id)'),
        ], $select::JOIN_LEFT);

        $onEntryCommon = new Expression('? = ? AND ? = ?', [
            ['entry_common.resource_file_entry_id' => Expression::TYPE_IDENTIFIER],
            ['default_common.resource_file_entry_id'  => Expression::TYPE_IDENTIFIER],
            ['entry_common.app_resource_id' => Expression::TYPE_IDENTIFIER],
            [$appResourceId => Expression::TYPE_VALUE],
        ]);
        $select->join('entry_common', $onEntryCommon, [
            'id' => 'id',
            'lastChange' => 'last_change',
            'notificationStatus' => 'notification_status',
        ], Select::JOIN_LEFT);

        $onEntryString = new Expression('? = ?', [
            ['entry_string.entry_common_id' => Expression::TYPE_IDENTIFIER],
            ['entry_common.id' => Expression::TYPE_IDENTIFIER],
        ]);
        $select->join('entry_string', $onEntryString, [
            'value' => 'value',
        ], Select::JOIN_LEFT);

        $select->join('suggestion', 'suggestion.entry_common_id = entry_common.id', [
            'suggestionCount' => new Expression('count(distinct suggestion.id)'),
        ], $select::JOIN_LEFT);

        $select->group([
            'default.value',
            'default_common.id',
            'default_common.app_resource_id',
            'default_common.resource_file_entry_id',
            'default_common.last_change',
            'resource_file_entry.resource_type_id',
            'resource_file_entry.name',
            'resource_file_entry.product',
            'resource_file_entry.description',
            'entry_common.id',
            'entry_common.last_change',
            'entry_common.notification_status',
            'entry_string.value',
        ]);

        if ($entryId > 0) {
            $select->where(['default_common.id' => $entryId]);
        }

        $returnArray = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);

        $sql = new Sql($this->tableGateway->getAdapter(), $this->tableGateway->getTable());
        $results = $sql->prepareStatementForSqlObject($select)->execute();

        foreach ($results as $result) {
            $result['defaultId'] = (int) $result['defaultId'];
            $result['appResourceId'] = (int) $result['appResourceId'];
            $result['resourceFileEntryId'] = (int) $result['resourceFileEntryId'];
            $result['defaultLastChange'] = (int) $result['defaultLastChange'];
            $result['resourceTypeId'] = (int) $result['resourceTypeId'];
            $result['entryCount'] = (int) $result['entryCount'];
            $result['id'] = (int) $result['id'];
            $result['lastChange'] = (int) $result['lastChange'];
            $result['notificationStatus'] = (int) $result['notificationStatus'];
            $result['suggestionCount'] = (int) $result['suggestionCount'];

            $returnArray[] = new ArrayObject($result, ArrayObject::ARRAY_AS_PROPS);
        }

        return $returnArray;
    }
}
