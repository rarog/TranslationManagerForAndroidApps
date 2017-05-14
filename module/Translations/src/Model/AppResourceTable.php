<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;

class AppResourceTable
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
     * @return \Translations\Model\AppResource
     */
    public function getAppResource($id)
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
     * App resource save function
     *
     * @param  AppResource $appResource
     * @throws RuntimeException
     * @return \Translations\Model\AppResource
     */
    public function saveAppResource(AppResource $appResource)
    {
        $data = [
            'app_id'      => $appResource->appId,
            'name'        => $appResource->name,
            'locale'      => $appResource->locale,
            'description' => $appResource->description,
        ];

        $id = (int) $appResource->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $appResource->id = $this->tableGateway->getLastInsertValue();
            return $appResource;
        }

        if (!$this->getAppResource($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update app resource with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $appResource;
    }

    /**
     * App resource delete function
     *
     * @param int $id
     */
    public function deleteAppResource($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}
