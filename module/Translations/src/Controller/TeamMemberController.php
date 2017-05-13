<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\DeleteHelperForm;
use Translations\Model\TeamMember;
use Translations\Model\TeamMemberTable;
use Translations\Model\TeamTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TeamMemberController extends AbstractActionController
{
    /**
     * @var TeamMemberTable
     */
    private $teamMemberTable;

    /**
     * @var TeamTable
     */
    private $teamTable;

    /**
     * Constructor
     *
     * @param TeamTable $teamMemberTable
     */
    public function __construct(TeamMemberTable $teamMemberTable, TeamTable $teamTable)
    {
        $this->teamMemberTable = $teamMemberTable;
        $this->teamTable = $teamTable;
    }

    /**
     * Team member add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
    }

    /**
     * Team member remove action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function removeAction()
    {
        $teamId = (int) $this->params()->fromRoute('teamId', 0);
        $userId = (int) $this->params()->fromRoute('userId', 0);
        try {
            $teamMember = $this->teamMemberTable->getTeamMember($userId, $teamId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }

        $form = new DeleteHelperForm();
        $form->bind($teamMember);

        $request = $this->getRequest();
        $viewData = [
            'id'   => $id,
            'name' => $teamMember->name,
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($teamMember->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        if ($request->getPost('del', 'false') === 'true') {
            $id = (int) $request->getPost('id');
            $this->table->deleteTeam($id);
        }

        return $this->redirect()->toRoute('team', ['action' => 'index']);
    }

    /**
     * Team member overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $teamId = (int) $this->params()->fromRoute('teamId', 0);
        try {
            $team = $this->teamTable->getTeam($teamId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }

        return [
            'team'        => $team,
            'teamMembers' => $this->teamMemberTable->fetchAll(),
        ];
    }
}
