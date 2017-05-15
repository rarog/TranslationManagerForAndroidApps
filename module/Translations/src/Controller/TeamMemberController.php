<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\DeleteHelperForm;
use Translations\Form\TeamMemberForm;
use Translations\Model\Team;
use Translations\Model\TeamMember;
use Translations\Model\TeamMemberTable;
use Translations\Model\TeamTable;
use Translations\Model\UserTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer as Renderer;

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
     * @var UserTable
     */
    private $userTable;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * Checks if current user has permission to work on this team
     *
     * @param Team $team
     * @return void|\Zend\Http\Response
     */
    private function checkPermission(Team $team)
    {
        if ($this->isGranted('team.viewAll')) {
            return;
        }

        try {
            $this->teamMemberTable->getTeamMember($this->zfcUserAuthentication()->getIdentity()->getId(), $team->id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }
    }

    /**
     * Constructor
     *
     * @param TeamMemberTable $teamMemberTable
     * @param TeamTable $teamTable
     * @param UserTable $userTable
     * @param Translator $translator
     * @param Renderer $renderer
     */
    public function __construct(TeamMemberTable $teamMemberTable, TeamTable $teamTable, UserTable $userTable, Translator $translator, Renderer $renderer)
    {
        $this->teamMemberTable = $teamMemberTable;
        $this->teamTable = $teamTable;
        $this->userTable = $userTable;
        $this->translator = $translator;
        $this->renderer = $renderer;
    }

    /**
     * Team member add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $teamId = (int) $this->params()->fromRoute('teamId', 0);
        try {
            $team = $this->teamTable->getTeam($teamId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }
        $this->checkPermission($team);

        $form = new TeamMemberForm();

        $request = $this->getRequest();
        $message = '';

        if ($request->isPost()) {
            $teamMember = new TeamMember();
            $form->setInputFilter($teamMember->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $teamMember->exchangeArray($form->getData());
                $teamMember = $this->teamMemberTable->saveTeamMember($teamMember);

                $message = sprintf(
                    $this->translator->translate('User with email "%s" was added to team "%s".'),
                    $teamMember->email,
                    $teamMember->teamName);

                $viewModel = new ViewModel([
                    'type'     => 'success',
                    'message'  => $message,
                    'canClose' => true,
                ]);
                $viewModel->setTemplate('partial/alert.phtml')
                    ->setTerminal(true);
                $message = $this->renderer->render($viewModel);
            }
        }

        return [
            'form'           => $form,
            'message'        => $message,
            'team'           => $team,
            'usersNotInTeam' => $this->userTable->fetchAllNotInTeam($teamId),
        ];
    }

    /**
     * Team member remove action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function removeAction()
    {
        $teamId = (int) $this->params()->fromRoute('teamId', 0);
        try {
            $team = $this->teamTable->getTeam($teamId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }
        $this->checkPermission($team);

        $userId = (int) $this->params()->fromRoute('userId', 0);
        try {
            $teamMember = $this->teamMemberTable->getTeamMember($userId, $teamId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('teammember', [
                'teamId' => $teamId,
                'action' => 'index',
            ]);
        }

        $form = new DeleteHelperForm();
        $form->add([
            'name' => 'team_id',
            'type' => 'hidden',
        ])->add([
            'name' => 'user_id',
            'type' => 'hidden',
        ])->bind($teamMember);

        $request = $this->getRequest();
        $viewData = [
            'form' => $form,
            'teamMember' => $teamMember,
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
            $teamId = (int) $request->getPost('team_id');
            $userId = (int) $request->getPost('user_id');
            $this->teamMemberTable->deleteTeamMember($userId, $teamId);
        }
        return $this->redirect()->toRoute('teammember', [
            'teamId' => $teamId,
            'action' => 'index',
        ]);
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
        $this->checkPermission($team);

        return [
            'team'        => $team,
            'teamMembers' => $this->teamMemberTable->fetchAll(),
        ];
    }
}
