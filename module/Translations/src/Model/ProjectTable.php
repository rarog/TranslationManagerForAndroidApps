<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Model;

use RuntimeException;
use Zend\Db\TableGateway\TableGatewayInterface;

class ProjectTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function getProject($id)
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

    public function saveProject(Album $project)
    {
        $data = [
            'artist' => $project->artist,
            'title'  => $project->title,
        ];

        $id = (int) $project->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            return;
        }

        if (! $this->getProject($id)) {
            throw new RuntimeException(sprintf(
                    'Cannot update album with identifier %d; does not exist',
                    $id
                    ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
    }

    public function deleteProject($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}
