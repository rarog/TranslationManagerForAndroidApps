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

namespace Translations\Controller;

use Application\Model\UserTable;
use RuntimeException;
use Translations\Form\DeleteHelperForm;
use Translations\Form\TeamMemberForm;
use Translations\Model\TeamMember;
use Translations\Model\TeamMemberTable;
use Translations\Model\TeamTable;
use Zend\Http\Response as HttpResponse;
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
     * Check if current user has permission to the team and return it
     *
     * @param int $teamId
     * @return \Zend\Http\Response|\Translations\Model\Team
     */
    private function getTeam(int $teamId)
    {
        if (0 === $teamId) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }

        try {
            $team = $this->teamTable->getTeam($teamId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }

        if ($this->isGranted('team.viewAll')) {
            return $team;
        }

        if (!$this->isGranted('team.viewAll') &&
            !$this->teamTable->hasUserPermissionForTeam(
                $this->zfcUserAuthentication()->getIdentity()->getId(),
                $team->id)) {
            return $this->redirect()->toRoute('team', ['action' => 'index']);
        }

        return $team;
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
     * @return ViewModel
     */
    public function addAction()
    {
        $teamId = (int) $this->params()->fromRoute('teamId', 0);
        $team = $this->getTeam($teamId);

        if ($team instanceof HttpResponse) {
            return $team;
        }

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
                    'canClose' => true,
                    'message' => $message,
                    'type' => 'success',
                ]);
                $viewModel->setTemplate('partial/alert.phtml')
                    ->setTerminal(true);
                $message = $this->renderer->render($viewModel);
            }
        }

        return [
            'form' => $form,
            'message' => $message,
            'team' => $team,
            'usersNotInTeam' => $this->userTable->fetchAllNotInTeam($teamId),
        ];
    }

    /**
     * Team member remove action
     *
     * @return ViewModel
     */
    public function removeAction()
    {
        $teamId = (int) $this->params()->fromRoute('teamId', 0);
        $team = $this->getTeam($teamId);

        if ($team instanceof HttpResponse) {
            return $team;
        }

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
            'messages' => [],
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $postTeamId = (int) $request->getPost('team_id');
        $postUserId = (int) $request->getPost('user_id');
        $postDataInconsistent = ($postTeamId !== $teamId) || ($postUserId !== $userId);
        if ($postDataInconsistent) {
            $viewData['messages'][] = [
                'canClose' => true,
                'message' => $this->translator->translate('Form data seems to be inconsistent. For security reasons the last input was corrected.'),
                'type' => 'warning',
            ];
        }

        $form->setInputFilter($teamMember->getInputFilter());
        $form->setData($request->getPost());

        if ($postDataInconsistent || !$form->isValid()) {
            $form->setData([
                'team_id' => $teamId,
                'user_id' => $userId,
            ]);
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
     * @return ViewModel
     */
    public function indexAction()
    {
        $teamId = (int) $this->params()->fromRoute('teamId', 0);
        $team = $this->getTeam($teamId);

        if ($team instanceof HttpResponse) {
            return $team;
        }

        return [
            'team' => $team,
            'teamMembers' => $this->teamMemberTable->fetchAll(['team_id' => $team->id]),
        ];
    }
}
