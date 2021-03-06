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

class EntryCommonTable
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
     * @return \Translations\Model\EntryCommon
     */
    public function getEntryCommon(int $id)
    {
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf('Could not find row with identifier %d', $id));
        }

        return $row;
    }

    /**
     * Resource file entry string save function
     *
     * @param EntryCommon $entryCommon
     * @throws RuntimeException
     */
    public function saveEntryCommon(EntryCommon $entryCommon)
    {
        $data = [
            'app_resource_id' => $entryCommon->getAppResourceId(),
            'resource_file_entry_id' => $entryCommon->getResourceFileEntryId(),
            'last_change' => $entryCommon->getLastChange(),
            'notification_status' => $entryCommon->getNotificationStatus(),
        ];

        $id = (int) $entryCommon->getId();

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $entryCommon->setId($this->tableGateway->getLastInsertValue());
            return;
        }

        try {
            if ($this->getEntryCommon($id)) {
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
     * Resource file entry string delete function
     *
     * @param int $id
     */
    public function deleteEntryCommon(int $id)
    {
        $this->tableGateway->delete(['id' => $id]);
    }
}
