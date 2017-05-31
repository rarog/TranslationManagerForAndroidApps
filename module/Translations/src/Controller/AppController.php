<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\AppForm;
use Translations\Form\DeleteHelperForm;
use Translations\Model\App;
use Translations\Model\AppTable;
use Translations\Model\Helper\FileHelper;
use Translations\Model\TeamTable;
use Translations\Model\UserSettingsTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;

class AppController extends AbstractActionController
{
    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * @var TeamTable
     */
    private $teamTable;

    /**
     * @var UserSettingsTable
     */
    private $userSettingsTable;

    /**
     * @var array
     */
    private $allTeamsAsArray;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * Helper to return all teams as key => val array
     *
     * @return array
     */
    private function getAllTeamsAsArray()
    {
        if (!$this->allTeamsAsArray) {
            $this->allTeamsAsArray = [];

            if ($this->isGranted('team.viewAll')) {
                $teams = $this->teamTable->fetchAll();
            } else {
                $teams = $this->teamTable->fetchAllAllowedToUser($this->zfcUserAuthentication()->getIdentity()->getId());
            }

            foreach ($teams as $team) {
                $this->allTeamsAsArray[$team->id] = $team->name;
            }
        }
        return $this->allTeamsAsArray;
    }

    /**
     * Helper for getting path to app directory
     *
     * @param int $id
     * @throws RuntimeException
     * @return string
     */
    private function getAppPath($id)
    {
        if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
            throw new RuntimeException(sprintf(
                    'Configured path app directory "%s" does not exist',
                    $this->configHelp('tmfaa')->app_dir
                    ));
        }
        return FileHelper::concatenatePath($path, (string) $id);
    }

    /**
     * Constructor
     *
     * @param AppTable $appTable
     * @param TeamTable $teamTable
     * @param UserSettingsTable $userSettingsTable
     * @param Translator $translator
     */
    public function __construct(AppTable $appTable, TeamTable $teamTable, UserSettingsTable $userSettingsTable, Translator $translator)
    {
        $this->appTable = $appTable;
        $this->teamTable = $teamTable;
        $this->userSettingsTable = $userSettingsTable;
        $this->translator = $translator;
    }

    /**
     * App add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $form = new AppForm();
        $teamField = $form->get('team_id')->setValueOptions($this->getAllTeamsAsArray());

        if ($this->zfcUserAuthentication()->hasIdentity() &&
            ($userSettings = $this->userSettingsTable->getUserSettings($this->zfcUserAuthentication()->getIdentity()->getId()))) {
            $teamField->setValue($userSettings->UserId);
        }

        $request = $this->getRequest();
        $viewData = [
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $app = new App();
        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $app->exchangeArray($form->getData());
        $app = $this->appTable->saveApp($app);

        $path =  $this->getAppPath($app->id);
        if (!mkdir($path, 0775, true)) {
            throw new RuntimeException(sprintf(
                'Could not create path "%s"',
                $path
            ));
        }

        return $this->redirect()->toRoute('app', [
            'action' => 'index',
        ]);
    }

    /**
     * App delete action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        try {
            $app = $this->appTable->getApp($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        $form = new DeleteHelperForm();
        $form->add([
            'name' => 'id',
            'type' => 'hidden',
        ])->add([
            'name' => 'team_id',
            'type' => 'hidden',
        ])->add([
            'name' => 'name',
            'type' => 'hidden',
        ])->bind($app);

        $viewData = [
            'app'      => $app,
            'form'     => $form,
            'messages' => [],
        ];

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $viewData;
        }

        $postId = (int) $request->getPost('id');
        $postDataInconsistent = ($postId !== $id);
        if ($postDataInconsistent) {
            $viewData['messages'][] = [
                'canClose' => true,
                'message'  => $this->translator->translate('Form data seems to be inconsistent. For security reasons the last input was corrected.'),
                'type'     => 'warning',
            ];
        }

        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if ($postDataInconsistent || !$form->isValid()) {
            $form->setData([
                'id' => $id,
            ]);
            return $viewData;
        }

        if ($request->getPost('del', 'false') === 'true') {
            $id = (int) $request->getPost('id');
            $this->appTable->deleteApp($id);
            FileHelper::rmdirRecursive($this->getAppPath($id));
        }

        return $this->redirect()->toRoute('app', [
            'action' => 'index',
        ]);
    }

    /**
     * App edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {

        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('app', [
                'action' => 'add',
            ]);
        }

        try {
            $app = $this->appTable->getApp($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        $form = new AppForm();
        $form->get('team_id')->setValueOptions($this->getAllTeamsAsArray());
        $form->bind($app);

        $viewData = [
            'app'  => $app,
            'form' => $form,
        ];

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $this->appTable->saveApp($app);

        return $this->redirect()->toRoute('app', [
            'action' => 'index',
        ]);
    }

    /**
     * App overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        if ($this->isGranted('team.viewAll')) {
            $apps = $this->appTable->fetchAll();
        } else {
            $apps = $this->appTable->fetchAllAllowedToUser($this->zfcUserAuthentication()->getIdentity()->getId());
        }

        return [
            'apps'  => $apps,
            'teams' => $this->getAllTeamsAsArray(),
        ];
    }
}
