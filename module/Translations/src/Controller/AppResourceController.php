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
     * @param AppTable $appTable
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

        try {
            $this->appResourceTable->getAppResourceByAppIdAndName(
                $app->id,
                'values');
            $hasDefaultValues = true;
        } catch (\Exception $e) {
            $hasDefaultValues = false;
        }

        $folderSelectButton = new \Zend\Form\Element\Button('name-selection-button',[
            'glyphicon' => 'folder-open',
        ]);
        $folderSelectButton->setAttribute('id', 'name-selection-button');

        $form = new AppResourceForm();
        $form->get('app_id')->setValue($app->id);
        if ($hasDefaultValues) {
            $form->get('locale')->setOption('help-block', '');
        } else {
            $form->get('name')->setAttribute('disabled', 'disabled')
                ->setValue('values');
            $folderSelectButton->setAttribute('disabled', 'disabled');
        }
        $form->get('name')->setOption('add-on-append', $folderSelectButton);
        $form->get('locale')->setValueOptions($this->getLocaleNameArray($this->translator->getLocale()));

        $request = $this->getRequest();
        $viewData = [
            'app'  => $app,
            'form' => $form,
        ];

        if (!$request->isPost()) {
            return $viewData;
        }

        $appResource = new AppResource();
        $form->setInputFilter($appResource->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
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

        if ($this->isGranted('app.viewAll')) {
            $appResources = $this->appResourceTable->fetchAll();
        } else {
            $appResources = $this->appResourceTable->fetchAllAllowedToUser($this->zfcUserAuthentication()->getIdentity()->getId());
        }

        $localeNames = $this->getLocaleNameArray($this->translator->getLocale());

        return [
            'app'          => $app,
            'appResources' => $appResources,
            'localeNames'  => $this->getLocaleNameArray($this->translator->getLocale()),
        ];
    }
}
