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

use RuntimeException;
use Translations\Form\AppResourceForm;
use Translations\Form\DeleteHelperForm;
use Translations\Model\App;
use Translations\Model\AppResource;
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

class AppResourceController extends AbstractActionController implements AdapterAwareInterface, AppHelperInterface
{
    use AdapterAwareTrait;
    use AppHelperTrait;

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
            throw new RuntimeException(sprintf('Configured path app directory "%s" does not exist', $this->configHelp('tmfaa')->app_dir));
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
        $path = FileHelper::concatenatePath((string) $app->Id, $app->PathToResFolder);
        return FileHelper::concatenatePath($path, 'res');
    }

    /**
     * Returns array of locale names
     *
     * @param string $inLocale
     * @return array
     */
    private function getLocaleNameArray(string $inLocale)
    {
        $localeNames = $this->configHelp('settings')->locale_names->toArray();
        return $localeNames[$inLocale];
    }

    /**
     * Constructor
     *
     * @param AppResourceTable $appResourceTable
     * @param AppTable $appTable
     * @param Translator $translator
     * @param DbAdapter $dbAdapter
     */
    public function __construct(AppResourceTable $appResourceTable, AppTable $appTable, Translator $translator, DbAdapter $dbAdapter)
    {
        $this->appResourceTable = $appResourceTable;
        $this->appTable = $appTable;
        $this->translator = $translator;
        $this->setDbAdapter($dbAdapter);
    }

    /**
     * App resource add action
     *
     * @throws RuntimeException
     * @return ViewModel
     */
    public function addAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $hasDefaultValues = $this->getHasAppDefaultValues($app);
        $path = $this->getAbsoluteAppResPath($app);
        $valuesDirs = [];
        $messages = [];
        $invalidResDir = false;

        if ($hasDefaultValues) {
            $existingValueDirs = [];
            foreach ($this->appResourceTable->fetchAll(['app_id' => $app->Id]) as $entry) {
                $existingValueDirs[] = $entry->name;
            }

            if (!is_dir($path) &&
                !mkdir($path, 0775, true)) {
                $messages[] = [
                    'canClose' => true,
                    'message' => sprintf($this->translator->translate('The app resource directory "%s" doesn\'t exist and couldn\'t be created.'), $this->getRelativeAppResPath($app)),
                    'type' => 'danger',
                ];
                $invalidResDir = true;
            } else {
                foreach (scandir($path) as $entry) {
                    if ((substr($entry, 0, 7) === 'values-') &&
                        is_dir(FileHelper::concatenatePath($path, $entry)) &&
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
        $form->get('app_id')->setValue($app->Id);
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
            'app' => $app,
            'messages' => $messages,
            'form' => $form,
            'valuesDirs' => $valuesDirs,
        ];

        if (!$request->isPost() || $invalidResDir) {
            return $viewData;
        }

        $appResource = new AppResource();
        $appResource->setDbAdapter($this->adapter);
        $form->setInputFilter($appResource->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $resValuesName = $request->getPost('name');
        $path = FileHelper::concatenatePath($path, $resValuesName);

        if (!is_dir($path) &&
            !mkdir($path, 0775, true)) {
            $viewData['messages'][] = [
                'canClose' => true,
                'message' => sprintf($this->translator->translate('The app resource directory "%s" doesn\'t exist and couldn\'t be created.'), $resValuesName),
                'type' => 'danger',
            ];

            return $viewData;
        }

        $appResource->exchangeArray($form->getData());
        $appResource = $this->appResourceTable->saveAppResource($appResource);

        return $this->redirect()->toRoute('appresource', ['appId' => $app->Id, 'action' => 'index']);
    }

    /**
     * App resource delete action
     *
     * @return ViewModel
     */
    public function deleteAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        // Prevent deleting of resources, if default resource doesn't exist.
        if (!$this->getHasAppDefaultValues($app)) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->Id,
                'action' => 'index',
            ]);
        }

        $id = (int) $this->params()->fromRoute('resourceId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->Id,
                'action' => 'index',
            ]);
        }

        try {
            $appResource = $this->appResourceTable->getAppResource($id);
            if ($appResource->appId !== $app->Id) {
                return $this->redirect()->toRoute('appresource', [
                    'appId'  => $app->Id,
                    'action' => 'index'
                ]);
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->Id,
                'action' => 'index'
            ]);
        }

        // Prevent deletion of default resource, if other resources exist.
        if (($appResource->name == 'values') && ($app->resourceCount > 1)) {
            return $this->redirect()->toRoute('appresource', [
                'appId'  => $app->Id,
                'action' => 'index'
            ]);
        }

        $appResource->setDbAdapter($this->adapter);
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
            'app' => $app,
            'appResource' => $appResource,
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

        $form->setInputFilter($appResource->getInputFilter());
        $form->setData($request->getPost());

        if ($postDataInconsistent || !$form->isValid()) {
            $form->setData([
                'id' => $id,
                'app_id' => $app->Id,
            ]);
            return $viewData;
        }

        if ($request->getPost('del', 'false') === 'true') {
            $this->appResourceTable->deleteAppResource($postId);
        }

        return $this->redirect()->toRoute('appresource', [
            'appId' => $app->Id,
            'action' => 'index',
        ]);
    }

    /**
     * App resource edit action
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        // Prevent editing of resources, if default resource doesn't exist.
        if (!$this->getHasAppDefaultValues($app)) {
            return $this->redirect()->toRoute('appresource', [
                'appId' => $app->Id,
                'action' => 'index',
            ]);
        }

        $id = (int) $this->params()->fromRoute('resourceId', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('appresource', [
                'appId' => $app->Id,
                'action' => 'add',
            ]);
        }

        try {
            $appResource = $this->appResourceTable->getAppResource($id);
            if ($appResource->appId !== $app->Id) {
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

        $appResource->setDbAdapter($this->adapter);

        $form = new AppResourceForm();
        $form->get('name')->setAttribute('readonly', 'readonly');
        $form->get('locale')->setValueOptions($this->getLocaleNameArray($this->translator->getLocale()));

        if ($appResource->Name !== 'values') {
            $form->get('locale')->setOption('help-block', '');
        }

        $form->bind($appResource);

        $viewData = [
            'app' => $app,
            'appResource' => $appResource,
            'form' => $form,
        ];

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($appResource->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return $viewData;
        }

        $this->appResourceTable->saveAppResource($appResource);

        return $this->redirect()->toRoute('appresource', [
            'appId' => $app->Id,
            'action' => 'index',
        ]);
    }

    /**
     * App resource overview action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $appId = (int) $this->params()->fromRoute('appId', 0);
        $app = $this->getApp($appId);

        $appResources = $this->appResourceTable->fetchAll([
            'app_id' => $app->Id,
        ]);

        $localeNames = $this->getLocaleNameArray($this->translator->getLocale());

        return [
            'app' => $app,
            'appResources' => $appResources,
            'hasDefaultValues' => $this->getHasAppDefaultValues($app),
            'localeNames' => $this->getLocaleNameArray($this->translator->getLocale()),
        ];
    }
}
