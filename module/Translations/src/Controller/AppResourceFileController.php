<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\AppResourceFileForm;
use Translations\Form\DeleteHelperForm;
use Translations\Model\AppResourceFile;
use Translations\Model\AppResourceFileTable;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\Helper\AppHelperInterface;
use Translations\Model\Helper\AppHelperTrait;
use Translations\Model\Helper\FileHelper;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;

class AppResourceFileController extends AbstractActionController implements AdapterAwareInterface, AppHelperInterface
{
    use AdapterAwareTrait;
    use AppHelperTrait;

    /**
     * @var AppResourceFileTable
     */
    private $appResourceFileTable;

    /**
     * @var AppTable
     */
    private $appTable;

    /**
     * @var AppResourceTable
     */
    private $appResourceTable;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * Check if current user has permission to the app and return it
     *
     * @param int $appId
     * @return void|\Zend\Http\Response|\Translations\Model\App
     */
    private function getApp(int $appId)
    {
        if (0 === $appId) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        try {
            $app = $this->appTable->getApp($appId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        if (!$this->isGranted('app.viewAll') &&
            !$this->appTable->hasUserPermissionForApp(
                $this->zfcUserAuthentication()->getIdentity()->getId(),
                $app->Id)) {
            return $this->redirect()->toRoute('app', [
                'action' => 'index',
            ]);
        }

        // Prevent further action, if default values don't exist.
        try {
            $this->appResourceTable->getAppResourceByAppIdAndName($app->Id, 'values');
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('appresource', [
                'appId' => $app->Id,
                'action' => 'index',
            ]);
        }

        return $app;
    }

    /**
     * Constructor
     *
     * @param AppResourceFileTable $appResourceFileTable
     * @param AppTable $appTable
     * @param AppResourceTable $appResourceTable
     * @param Translator $translator
     * @param DbAdapter $dbAdapter
     */
    public function __construct(AppResourceFileTable $appResourceFileTable, AppTable $appTable, AppResourceTable $appResourceTable, Translator $translator, DbAdapter $dbAdapter)
    {
        $this->appResourceFileTable = $appResourceFileTable;
        $this->appTable = $appTable;
        $this->appResourceTable = $appResourceTable;
        $this->translator = $translator;
        $this->setDbAdapter($dbAdapter);
    }

    /**
     * App resource file add action
     *
     * @throws RuntimeException
     * @return ViewModel
     */
    public function addAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);
        $this->setAppDirectory($this->configHelp('tmfaa')->app_dir);

        $path = $this->getAbsoluteAppResValuesPath($app);
        $resourceFiles = [];
        $messages = [];
        $invalidResDir = false;

        $existingResourceFiles = [];
        foreach ($this->appResourceFileTable->fetchAll(['app_id' => $app->Id]) as $entry) {
            $existingResourceFiles[] = $entry->name;
        }

        if (!is_dir($path) &&
            !mkdir($path, 0775, true)) {
            $messages[] = [
                'canClose' => true,
                'message' => sprintf($this->translator->translate('The app resource default values directory "%s" doesn\'t exist and couldn\'t be created.'), $this->getRelativeAppResValuesPath($app)),
                'type' => 'danger',
            ];
            $invalidResDir = true;
        } else {
            foreach (scandir($path) as $entry) {
                if ((substr($entry, -4) === '.xml') &&
                    FileHelper::isFileValidResource(FileHelper::concatenatePath($path, $entry)) &&
                    !in_array($entry, $existingResourceFiles)) {
                    $resourceFiles[] = $entry;
                }
            }
        }

        $folderSelectButton = new \Zend\Form\Element\Button('name-selection-button',[
            'glyphicon' => 'folder-open',
        ]);
        $folderSelectButton->setAttributes([
            'data-toggle' => 'modal',
            'data-target' => '#resFileNameSelection',
        ]);

        $form = new AppResourceFileForm();
        $form->get('app_id')->setValue($app->Id);

        if (count($resourceFiles) === 0) {
            $folderSelectButton->setAttribute('disabled', 'disabled');
        }
        $form->get('name')->setOption('add-on-append', $folderSelectButton);
        if ($invalidResDir) {
            $form->get('submit')->setAttribute('disabled', 'disabled');
        }

        $request = $this->getRequest();
        $viewData = [
            'app' => $app,
            'messages' => $messages,
            'form' => $form,
            'valuesDirs' => $resourceFiles,
        ];

        if (!$request->isPost() || $invalidResDir) {
            return $viewData;
        }

        $appResourceFile = new AppResourceFile();
        $appResourceFile->setDbAdapter($this->adapter);
        $form->setInputFilter($appResourceFile->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $path = FileHelper::concatenatePath($path, 'values');

        if (!is_dir($path) &&
            !mkdir($path, 0775, true)) {
            $viewData['messages'][] = [
                'canClose' => true,
                'message' => sprintf($this->translator->translate('The app resource directory "%s" doesn\'t exist and couldn\'t be created.'), $resValuesName),
                'type' => 'danger',
            ];

            return $viewData;
        }

        $appResourceFile->exchangeArray($form->getData());
        $appResourceFile = $this->appResourceFileTable->saveAppResourceFile($appResourceFile);

        return $this->redirect()->toRoute('appresourcefile', ['appId' => $app->Id, 'action' => 'index']);
    }

    /**
     * App resource file delete action
     *
     * @return ViewModel
     */
    public function deleteAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $id = (int) $this->params()->fromRoute('resourceFileId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresource', [
                'appId' => $app->Id,
                'action' => 'index',
            ]);
        }

        try {
            $appResourceFile = $this->appResourceFileTable->getAppResourceFile($id);
            if ($appResourceFile->appId !== $app->Id) {
                return $this->redirect()->toRoute('appresource', [
                    'appId' => $app->Id,
                    'action' => 'index',
                ]);
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('appresource', [
                'appId' => $app->Id,
                'action' => 'index',
            ]);
        }

        $appResourceFile->setDbAdapter($this->adapter);
        $form = new DeleteHelperForm();
        $form->add([
            'name' => 'id',
            'type' => 'hidden',
        ])->add([
            'name' => 'app_id',
            'type' => 'hidden',
        ])->add([
            'name' => 'name',
            'type' => 'hidden',
        ])->bind($appResourceFile);

        $viewData = [
            'app' => $app,
            'appResourceFile' => $appResourceFile,
            'form' => $form,
            'messages' => [],
        ];

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $viewData;
        }

        $postId = (int) $request->getPost('id');
        $postAppId = (int) $request->getPost('app_id');
        $postDataInconsistent = ($postId !== $id) || ($postAppId !== $app->Id);
        if ($postDataInconsistent) {
            $viewData['messages'][] = [
                'canClose' => true,
                'message' => $this->translator->translate('Form data seems to be inconsistent. For security reasons the last input was corrected.'),
                'type' => 'warning',
            ];
        }

        $form->setInputFilter($appResourceFile->getInputFilter());
        $form->setData($request->getPost());

        if ($postDataInconsistent || !$form->isValid()) {
            $form->setData([
                'id' => $id,
                'app_id' => $app->Id,
            ]);
            return $viewData;
        }

        if ($request->getPost('del', 'false') === 'true') {
            $this->appResourceFileTable->deleteAppResourceFile($postId);
        }

        return $this->redirect()->toRoute('appresourcefile', [
            'appId'  => $app->Id,
            'action' => 'index'
        ]);
    }

    /**
     * App resource file edit action
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $id = (int) $this->params()->fromRoute('resourceFileId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresourcefile', [
                'appId' => $app->Id,
                'action' => 'add',
            ]);
        }

        try {
            $appResourceFile = $this->appResourceFileTable->getAppResourceFile($id);
            if ($appResourceFile->AppId !== $app->Id) {
                return $this->redirect()->toRoute('appresourcefile', [
                    'appId' => $app->Id,
                    'action' => 'index',
                ]);
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('appresourcefile', [
                'appId' => $app->Id,
                'action' => 'index',
            ]);
        }

        $appResourceFile->setDbAdapter($this->adapter);
        $form = new AppResourceFileForm();
        $form->bind($appResourceFile);

        $viewData = [
            'app' => $app,
            'id' => $id,
            'form' => $form,
        ];

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($appResourceFile->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $this->appResourceFileTable->saveAppResourceFile($appResourceFile);

        return $this->redirect()->toRoute('appresourcefile', [
            'appId' => $app->Id,
            'action' => 'index'
        ]);
    }

    /**
     * App resource file overview action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $appResourceFiles = $this->appResourceFileTable->fetchAll([
            'app_id' => $app->Id,
        ]);

        return [
            'app' => $app,
            'appResourceFiles' => $appResourceFiles,
        ];
    }
}
