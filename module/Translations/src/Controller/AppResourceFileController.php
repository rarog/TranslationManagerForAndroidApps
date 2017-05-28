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
use Translations\Model\App;
use Translations\Model\AppResource;
use Translations\Model\AppResourceFile;
use Translations\Model\AppResourceFileTable;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\Helper\FileHelper;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\AdapterAwareTrait;
use Zend\Form\Element\Button;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;

class AppResourceFileController extends AbstractActionController implements AdapterAwareInterface
{
    use AdapterAwareTrait;

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
    private function getApp($appId)
    {
        $appId = (int) $appId;

        if (0 === $appId) {
            return $this->redirect()->toRoute('app', ['action' => 'index']);
        }

        try {
            $app = $this->appTable->getApp($appId);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', ['action' => 'index']);
        }

        if (!$this->isGranted('app.viewAll') &&
            !$this->appTable->hasUserPermissionForApp(
                $this->zfcUserAuthentication()->getIdentity()->getId(),
                $app->id)) {
            return $this->redirect()->toRoute('app', ['action' => 'index']);
        }

        // Prevent further action, if default values don't exist.
        try {
            $this->appResourceTable->getAppResourceByAppIdAndName($app->id, 'values');
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'index',
            ]);
        }

        return $app;
    }

    /**
     * Helper for getting absolute path to app resource default values directory
     *
     * @param App $app
     * @throws RuntimeException
     * @return string
     */
    private function getAbsoluteAppResValuesPath(App $app)
    {
        if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
            throw new RuntimeException(sprintf(
                'Configured path app directory "%s" does not exist',
                $this->configHelp('tmfaa')->app_dir));
        }
        return FileHelper::concatenatePath($path, $this->getRelativeAppResValuesPath($app));
    }

    /**
     * Helper for getting relative path to app resource default values directory
     *
     * @param App $app
     * @return string
     */
    private function getRelativeAppResValuesPath(App $app)
    {
        $path = FileHelper::concatenatePath((string) $app->id, $app->pathToResFolder);
        $path = FileHelper::concatenatePath($path, 'res');
        return FileHelper::concatenatePath($path, 'values');
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
     * App add action
     *
     * @throws RuntimeException
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $path = $this->getAbsoluteAppResValuesPath($app);
        $valuesDirs = [];
        $errorMessage = '';
        $invalidResDir = false;

        $existingResourceFiles = [];
        foreach ($this->appResourceFileTable->fetchAll(['app_id' => $app->id]) as $entry) {
            $existingResourceFiles[] = $entry->name;
        }

        if (!is_dir($path) &&
            !mkdir($path, 0775)) {
                $errorMessage = sprintf(
                    $this->translator->translate('The app resource default values directory "%s" doesn\'t exist and couldn\'t be created.'),
                    $this->getRelativeAppResValuesPath($app));
                $invalidResDir = true;
        } else {
            foreach (scandir($path) as $entry) {
                if ((substr($entry, 0, 7) === 'values-') &&
                    !in_array($entry, $existingResourceFiles)) {
                    $valuesDirs[] = $entry;
                }
            }
        }

        $folderSelectButton = new \Zend\Form\Element\Button('name-selection-button',[
            'glyphicon' => 'folder-open',
        ]);
        $folderSelectButton->setAttributes([
            'data-toggle' => 'modal',
            'data-target' => '#valueNameSelection',
        ]);

        $form = new AppResourceFileForm();
        $form->get('app_id')->setValue($app->id);

        if (count($valuesDirs) === 0) {
            $folderSelectButton->setAttribute('disabled', 'disabled');
        }
        $form->get('name')->setOption('add-on-append', $folderSelectButton);
        if ($invalidResDir) {
            $form->get('submit')->setAttribute('disabled', 'disabled');
        }

        $request = $this->getRequest();
        $viewData = [
            'app'          => $app,
            'errorMessage' => $errorMessage,
            'form'         => $form,
            'valuesDirs'   => $valuesDirs,
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
            !mkdir($path, 0775)) {
            $viewData['errorMessage'] = sprintf(
                $this->translator->translate('The app resource directory "%s" doesn\'t exist and couldn\'t be created.'),
                $resValuesName);
            return $viewData;
        }

        $appResourceFile->exchangeArray($form->getData());
        $appResourceFile = $this->appResourceFileTable->saveAppResource($appResourceFile);

        return $this->redirect()->toRoute('appresourcefile', ['appId' => $app->id, 'action' => 'index']);
    }

    /**
     * App delete action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $id = (int) $this->params()->fromRoute('resourceId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'index',
            ]);
        }

        try {
            $appResource = $this->appResourceTable->getAppResource($id);
            if ($appResource->appId !== $app->id) {
                return $this->redirect()->toRoute('appresource', [
                    'appId'  => $app->id,
                    'action' => 'index'
                ]);
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'index'
            ]);
        }

        // Prevent deletion of default resource, if other resources exist.
        if (($appResource->name == 'values') && ($app->resourceCount > 1)) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'index'
            ]);
        }

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
        ])->add([
            'name' => 'locale',
            'type' => 'hidden',
        ])->bind($appResource);

        $viewData = [
            'appName'     => $app->name,
            'appResource' => $appResource,
            'form'        => $form,
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

        if ($request->getPost('del', 'false') === 'true') {
            $id = (int) $request->getPost('id');
            $this->appResourceTable->deleteAppResource($id);
        }

        return $this->redirect()->toRoute('appresource', [
            'appId'  => $app->id,
            'action' => 'index'
        ]);
    }

    /**
     * App edit action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $id = (int) $this->params()->fromRoute('resourceId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresourcefile', [
                'appId'  => $app->id,
                'action' => 'add',
            ]);
        }

        try {
            $appResourceFile = $this->appResourceFileTable->getAppResourceFile($id);
            if ($appResourceFile->appId !== $app->id) {
                return $this->redirect()->toRoute('appresourcefile', [
                    'appId'  => $app->id,
                    'action' => 'index'
                ]);
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', [
                'appId'  => $app->id,
                'action' => 'index'
            ]);
        }

        $appResourceFile->setDbAdapter($this->adapter);
        $form = new AppResourceFileForm();
        $form->get('name')->setAttribute('readonly', 'readonly');
        $form->bind($appResourceFile);

        $viewData = [
            'app'  => $app,
            'id'   => $id,
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

        $this->appResourceTable->saveAppResource($appResourceFile);

        return $this->redirect()->toRoute('appresource', [
            'appId'  => $app->id,
            'action' => 'index'
        ]);
    }

    /**
     * App resource overview action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $appResourceFiles = $this->appResourceFileTable->fetchAll();

        return [
            'app'              => $app,
            'appResourceFiles' => $appResourceFiles,
        ];
    }
}
