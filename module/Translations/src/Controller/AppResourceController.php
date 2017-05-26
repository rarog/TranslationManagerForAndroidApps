<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations\Controller;

use RuntimeException;
use Translations\Form\AppResourceForm;
use Translations\Form\DeleteHelperForm;
use Translations\Model\App;
use Translations\Model\AppResource;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\Helper\FileHelper;
use Zend\Form\Element\Button;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\ViewModel;

class AppResourceController extends AbstractActionController
{
    /**
     * @var AppResourceTable
     */
    private $appResourceTable;

    /**
     * @var AppTable
     */
    private $appTable;

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

        return $app;
    }

    /**
     * Helper for getting absolute path to app resource directory
     *
     * @param App $app
     * @throws RuntimeException
     * @return string
     */
    private function getAbsoluteAppResPath(App $app)
    {
        if (($path = realpath($this->configHelp('tmfaa')->app_dir)) === false) {
            throw new RuntimeException(sprintf(
                'Configured path app directory "%s" does not exist',
                $this->configHelp('tmfaa')->app_dir));
        }
        return FileHelper::concatenatePath($path, $this->getRelativeAppResPath($app));
    }

    /**
     * Helper for getting relative path to app resource directory
     *
     * @param App $app
     * @return string
     */
    private function getRelativeAppResPath(App $app)
    {
        $path = FileHelper::concatenatePath((string) $app->id, $app->pathToResFolder);
        return FileHelper::concatenatePath($path, 'res');
    }

    /**
     * Check if app has default values
     *
     * @param int $appId
     * @return boolean
     */
    private function getHasAppDefaultValues($appId)
    {
        $appId = (int) $appId;
        try {
            $this->appResourceTable->getAppResourceByAppIdAndName($appId, 'values');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns array of locale names
     *
     * @param string $inLocale
     * @return array
     */
    private function getLocaleNameArray($inLocale)
    {
        $inLocale = (string) $inLocale;

        $localeNames = $this->configHelp('settings')->locale_names->toArray();
        return $localeNames[$inLocale];
    }

    /**
     * Constructor
     *
     * @param AppResourceTable $appResourceTable
     * @param AppTable $appTable
     * @param Translator $translator
     */
    public function __construct(AppResourceTable $appResourceTable, AppTable $appTable, Translator $translator)
    {
        $this->appResourceTable = $appResourceTable;
        $this->appTable = $appTable;
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
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $hasDefaultValues = $this->getHasAppDefaultValues($app->id);
        $path = $this->getAbsoluteAppResPath($app);
        $valuesDirs = [];
        $errorMessage = '';
        $invalidResDir = false;

        if ($hasDefaultValues) {
            $existingValueDirs = [];
            foreach ($this->appResourceTable->fetchAll(['app_id' => $app->id]) as $entry) {
                $existingValueDirs[] = $entry->name;
            }

            if (!is_dir($path) &&
                !mkdir($path, 0775)) {
                    $errorMessage = sprintf(
                        $this->translator->translate('The app resource directory "%s" doesn\'t exist and couldn\'t be created.'),
                        $this->getRelativeAppResPath($app));
                    $invalidResDir = true;
            } else {
                foreach (scandir($path) as $entry) {
                    if ((substr($entry, 0, 7) === 'values-') &&
                        !in_array($entry, $existingValueDirs)) {
                        $valuesDirs[] = $entry;
                    }
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

        $form = new AppResourceForm();
        $form->get('app_id')->setValue($app->id);
        if ($hasDefaultValues) {
            $form->get('locale')->setOption('help-block', '');
        } else {
            $form->get('name')->setAttribute('readonly', 'readonly')
                ->setValue('values');
        }

        if (count($valuesDirs) === 0) {
            $folderSelectButton->setAttribute('disabled', 'disabled');
        }
        $form->get('name')->setOption('add-on-append', $folderSelectButton);
        $form->get('locale')->setValueOptions($this->getLocaleNameArray($this->translator->getLocale()));
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

        $appResource = new AppResource();
        $form->setInputFilter($appResource->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $resValuesName = $request->getPost('name');
        $path = FileHelper::concatenatePath($path, $resValuesName);

        if (!is_dir($path) &&
            !mkdir($path, 0775)) {
            $viewData['errorMessage'] = sprintf(
                $this->translator->translate('The app resource directory "%s" doesn\'t exist and couldn\'t be created.'),
                $resValuesName);
            return $viewData;
        }

        $appResource->exchangeArray($form->getData());
        $appResource = $this->appResourceTable->saveAppResource($appResource);

        return $this->redirect()->toRoute('appresource', ['appId' => $app->id, 'action' => 'index']);
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

        // Prevent deleting of resources, if default resource doesn't exist.
        if (!$this->getHasAppDefaultValues($app->id)) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'index',
            ]);
        }

        $id = (int) $this->params()->fromRoute('resourceId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'index',
            ]);
        }

        try {
            $appResource = $this->appResourceTable->getAppResource($id);
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

        // Prevent editing of resources, if default resource doesn't exist.
        if (!$this->getHasAppDefaultValues($app->id)) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'index',
            ]);
        }

        $id = (int) $this->params()->fromRoute('resourceId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->id,
                'action' => 'add',
            ]);
        }

        try {
            $appResource = $this->appResourceTable->getAppResource($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('app', [
                'appId'  => $app->id,
                'action' => 'index'
            ]);
        }

        $form = new AppResourceForm();
        $form->get('name')->setAttribute('readonly', 'readonly');
        $form->get('locale')->setValueOptions($this->getLocaleNameArray($this->translator->getLocale()));
        $form->bind($appResource);

        $viewData = [
            'app'  => $app,
            'id'   => $id,
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

        $this->appResourceTable->saveAppResource($appResource);

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

        $appResources = $this->appResourceTable->fetchAll();

        $localeNames = $this->getLocaleNameArray($this->translator->getLocale());

        return [
            'app'              => $app,
            'appResources'     => $appResources,
            'hasDefaultValues' => $this->getHasAppDefaultValues($app->id),
            'localeNames'      => $this->getLocaleNameArray($this->translator->getLocale()),
        ];
    }
}
