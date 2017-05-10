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
     * Constructor
     *
     * @param AppTable $appTable
     */
    public function __construct(AppTable $appTable, TeamTable $teamTable, UserSettingsTable $userSettingsTable)
    {
        $this->appTable = $appTable;
        $this->teamTable = $teamTable;
        $this->userSettingsTable = $userSettingsTable;
    }

    private function getAllTeamsAsArray()
    {
        if (!$this->allTeamsAsArray) {
            $this->allTeamsAsArray = [];
            foreach ($this->teamTable->fetchAll() as $team) {
                $this->allTeamsAsArray[$team->id] = $team->name;
            }
        }
        return $this->allTeamsAsArray;
    }

    private function getAppDir($id)
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
            $teamField->setValue($userSettings->userId);
        }

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return ['form' => $form];
        }

        $app = new App();
        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return ['form' => $form];
        }

        $app->exchangeArray($form->getData());
        $app = $this->appTable->saveApp($app);

        $path = $this->getAppDir($app->id);echo $path;
        if (!mkdir($path, 0775)) {
            throw new RuntimeException(sprintf(
                'Could not create path "%s"',
                $path
            ));
        }

        return $this->redirect()->toRoute('app', ['action' => 'index']);
    }

    /**
     * App delete action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        try {
            $app = $this->appTable->getApp($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', ['action' => 'index']);
        }

        $form = new DeleteHelperForm();
        $form->bind($app);

        $request = $this->getRequest();
        $viewData = [
            'id'   => $id,
            'name' => $app->name,
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        if ($request->getPost('del', 'false') === 'true') {
            $id = (int) $request->getPost('id');
            $this->appTable->deleteApp($id);
            FileHelper::rmdirRecursive($this->getAppDir($id));
        }

        return $this->redirect()->toRoute('app', ['action' => 'index']);
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
            return $this->redirect()->toRoute('app', ['action' => 'add']);
        }

        try {
            $app = $this->appTable->getApp($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', ['action' => 'index']);
        }

        $form = new AppForm();
        $form->get('team_id')->setValueOptions($this->getAllTeamsAsArray());
        $form->bind($app);

        $request = $this->getRequest();
        $viewData = [
            'id'   => $id,
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($app->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $this->appTable->saveApp($app);

        return $this->redirect()->toRoute('app', ['action' => 'index']);
    }


    /**
     * App overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return [
            'apps'  => $this->appTable->fetchAll(),
            'teams' => $this->getAllTeamsAsArray(),
        ];
    }
}
