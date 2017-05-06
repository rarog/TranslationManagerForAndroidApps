<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGateway;

class AppTable
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
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    /**
     * @param  int $id
     * @throws RuntimeException
     * @return \Translations\Model\App
     */
    public function getApp($id)
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
     * App save function
     *
     * @param  App $app
     * @throws RuntimeException
     * @return \Translations\Model\App
     */
    public function saveApp(App $app)
    {
        $data = [
            'name'               => $app->name,
            'git_repository'     => $app->gitRepository,
            'path_to_res_folder' => $app->pathToResFolder,
        ];

        $id = (int) $app->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            $app->id = $this->tableGateway->getLastInsertValue();
            return $app;
        }

        if (!$this->getApp($id)) {
            throw new RuntimeException(sprintf(
                'Cannot update app with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
        return $app;
    }

    /**
     * App delete function
     *
     * @param int $id
     */
    public function deleteApp($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}
