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

use Application\Model\UserSettingsTable;
use RuntimeException;
use Translations\Form\AppForm;
use Translations\Form\DeleteHelperForm;
use Translations\Model\App;
use Translations\Model\AppTable;
use Translations\Model\Helper\EncryptionHelper;
use Translations\Model\Helper\FileHelper;
use Translations\Model\TeamTable;
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
     * @var EncryptionHelper
     */
    private $encryptionHelper;

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
    private function getAppPath(int $id)
    {
        if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
            throw new RuntimeException(sprintf('Configured path app directory "%s" does not exist', $this->configHelp('tmfaa')->app_dir));
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
     * @param EncryptionHelper $encryptionHelper
     */
    public function __construct(AppTable $appTable, TeamTable $teamTable, UserSettingsTable $userSettingsTable, Translator $translator, EncryptionHelper $encryptionHelper)
    {
        $this->appTable = $appTable;
        $this->teamTable = $teamTable;
        $this->userSettingsTable = $userSettingsTable;
        $this->translator = $translator;
        $this->encryptionHelper = $encryptionHelper;
    }

    /**
     * App add action
     *
     * @throws RuntimeException
     * @return ViewModel
     */
    public function addAction()
    {
        $form = new AppForm();
        $form->remove('git_password_delete');
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
        if ($app->GitPassword != '') {
            $app->GitPassword = $this->encryptionHelper->encrypt($app->GitPassword);
        }
        $app = $this->appTable->saveApp($app);

        $path =  $this->getAppPath($app->id);
        if (!mkdir($path, 0775, true)) {
            throw new RuntimeException(sprintf('Could not create path "%s"', $path));
        }

        return $this->redirect()->toRoute('app', [
            'action' => 'index',
        ]);
    }

    /**
     * App delete action
     *
     * @return ViewModel
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
     * @return ViewModel
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

        $oldPassword = $app->GitPassword;

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

        if ($request->getPost('git_password_delete') == '1') {
            // Password should be deleted
            $app->GitPassword = '';
        } elseif ($app->GitPassword == '') {
            // No new password entered - use old
            $app->GitPassword = $oldPassword;
        } else {
            // New password - encrypt it
            $app->GitPassword = $this->encryptionHelper->encrypt($app->GitPassword);
        }

        $this->appTable->saveApp($app);

        return $this->redirect()->toRoute('app', [
            'action' => 'index',
        ]);
    }

    /**
     * App overview action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        if ($this->isGranted('team.viewAll')) {
            $apps = $this->appTable->fetchAll();
        } else {
            $apps = $this->appTable->fetchAllAllowedToUser($this->zfcUserAuthentication()->getIdentity()->getId());
        }

        return [
            'apps' => $apps,
            'teams' => $this->getAllTeamsAsArray(),
        ];
    }
}
