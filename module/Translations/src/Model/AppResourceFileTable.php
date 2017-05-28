<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;

class AppResourceFileTable
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
     * @return \Translations\Model\AppResourceFile
     */
    public function getAppResourceFile($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id));
        }

        return $row;
    }

    /**
     * App resource file save function
     *
     * @param  AppResourceFile $appResourceFile
     * @throws RuntimeException
     * @return \Translations\Model\AppResourceFile
     */
    public function saveAppResourceFile(AppResourceFile $appResourceFile)
    {
        $data = [
            'app_id' => $appResourceFile->AppId,
            'name'   => $appResourceFile->Name,
        ];

        $id = (int) $appResourceFile->Id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $appResourceFile->Id = $this->tableGateway->getLastInsertValue();
            return $appResourceFile;
        }

        if (!$this->getAppResourceFile($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update app resource file with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $appResourceFile;
    }

    /**
     * App resource file delete function
     *
     * @param int $id
     */
    public function deleteAppResourceFile($id)
    {
        $id = (int) $id;
        $this->tableGateway->delete(['id' => $id]);
    }
}
