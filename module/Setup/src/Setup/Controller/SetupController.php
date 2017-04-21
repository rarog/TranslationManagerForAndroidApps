<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Controller;

use Setup\Model\DatabaseHelper;
use Zend\ModuleManager\Listener\ListenerOptions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer as Renderer;
use ZfcUser\Options\ModuleOptions as ZUModuleOptions;
use ZfcUser\Service\User as ZUUser;

class SetupController extends AbstractActionController
{
    protected $translator;
    protected $container;
    protected $setupConfig;
    protected $availableLanguages;
    protected $listenerOptions;
    protected $renderer;
    protected $zuUserService;
    protected $zuModuleOptions;
    protected $databaseCheck;
    protected $lastStep;

    protected function getSetupConfig()
    {
        if (is_null($this->setupConfig)) {
            $this->setupConfig = $this->configHelp('setup');
        }
        return $this->setupConfig;
    }


    protected function getAvailableLanguages()
    {
        if (is_null($this->availableLanguages)) {
            $this->availableLanguages = $this->getSetupConfig()->available_languages->toArray();
        }
        return $this->availableLanguages;
    }

    protected function getDatabaseCheck()
    {
        if (is_null($this->databaseCheck)) {
            $this->databaseCheck = new DatabaseHelper(
                ($this->configHelp()->db) ? $this->configHelp()->db->toArray() : [],
                $this->translator,
                $this->getSetupConfig()
            );
        }
        return $this->databaseCheck;
    }

    protected function setCurrentLanguage()
    {
        if (!is_null($this->container->currentLanguage) &&
            array_key_exists($this->container->currentLanguage, $this->getAvailableLanguages())) {
            $this->translator
                ->setLocale($this->container->currentLanguage)
                ->setFallbackLocale(\Locale::getPrimaryLanguage($this->container->currentLanguage));
        }
    }

    protected function getLastStep() {
        return (int) $this->container->lastStep;
    }

    protected function setLastStep(int $lastStep) {
        $this->container->lastStep = $lastStep;
    }

    protected function checkSetupStep(int $currentStep)
    {
        // TODO: Check if starting setup is allowed at all.
        $lastStep = $this->getLastStep();
        if ($currentStep > $lastStep) {
            $action = [];
            if ($lastStep > 1) {
                $action['action'] = 'step' . $lastStep;
            }
            $this->redirect()->toRoute('setup', $action);
        } else {
            $dbCheck = $this->getDatabaseCheck();

            if (!$dbCheck->canConnect()) {
                return $this->redirect()->toRoute('setup', ['action' => 'step2']);
            }
        }
    }

    /**
     * Adds Bootstrap disabled class to a form element
     *
     * @param \Zend\Form\Element $element
     */
    protected function disableFormElement(\Zend\Form\Element $element)
    {
        $element->setAttribute('class', $element->getAttribute('class') . ' disabled');
    }

    /**
     * Generates error 403 and returns view model with disabled layout
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function throw403()
    {
        $this->getResponse()->setStatusCode(403);
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function __construct(Translator $translator, ListenerOptions $listenerOptions, Renderer $renderer, ZUUser $zuUserService, ZUModuleOptions $zuModuleOptions)
    {
        $this->translator = $translator;
        $this->container = new \Zend\Session\Container('setup');
        $this->listenerOptions = $listenerOptions;
        $this->renderer = $renderer;
        $this->zuUserService = $zuUserService;
        $this->zuModuleOptions = $zuModuleOptions;
    }

    /**
     * Action for database connection test call via JSON
     */
    public function databaseconnectiontestAction()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $this->setCurrentLanguage();

            if ($request->isPost() &&
                ($postData = $request->getPost()->toArray())) {
                $dbCheck = new DatabaseHelper($postData, $this->translator);
                $type = ($dbCheck->canConnect()) ? 'success' : 'danger';
                $message = $dbCheck->getLastMessage();
            } else {
                $type = 'danger';
                $message = $this->translator->translate('<strong>Error:</strong> Invalid POST data was provided.');
            }

            $viewModel = new ViewModel([
                'type' => $type,
                'message' => $message,
            ]);
            $viewModel->setTemplate('partial/alert.phtml')
                ->setTerminal(true);
            $htmlOutput = $this->renderer->render($viewModel);

            $jsonModel = new JsonModel([
                'html' => $htmlOutput,
            ]);
            $jsonModel->setTerminal(true);

            return $jsonModel;
        } else {
            return $this->throw403();
        }
    }

    /**
     * Action for database connection test call via JSON
     */
    public function databaseschemainstallationAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $dbCheck = $this->getDatabaseCheck();
            $dbCheck->installSchema();
            $dbCheck->isInstalled();

            $nextEnabled = true;
            $installSchemaEnabled = true;
            // Disable buttons if needed.
            if (!$dbCheck->isInstalled()) {
                $nextEnabled = false;
            }
            // This code works properly only, because isInstalled() was called above.
            if ($dbCheck->getLastStatus() != $dbCheck::DBNOTINSTALLEDORTABLENOTPRESENT) {
                $installSchemaEnabled = false;
            }

            $jsonModel = new JsonModel([
                'html' => $dbCheck->getLastMessage(),
                'nextEnabled' => $nextEnabled,
                'installSchemaEnabled' => $installSchemaEnabled,
            ]);
            $jsonModel->setTerminal(true);

            return $jsonModel;
        } else {
            return $this->throw403();
        }
    }

    /**
     * Action for step 1 - welcome screen and setup language selection
     */
    public function indexAction()
    {
        $this->setCurrentLanguage();
        $this->setLastStep(1);

        $setupLanguage = new \Setup\Model\SetupLanguage([
            'setup_language' => $this->translator->getLocale(),
        ]);

        $formStep1 = new \Setup\Form\Step1Form();
        $formStep1->get('setup_language')->setValueOptions($this->getAvailableLanguages());
        $formStep1->bind($setupLanguage);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $formStep1->setInputFilter($setupLanguage->getInputFilter());
            $formStep1->setData($request->getPost());
            if ($formStep1->isValid() &&
                array_key_exists($setupLanguage->SetupLanguage, $this->getAvailableLanguages())) {
                $this->container->currentLanguage = $setupLanguage->SetupLanguage;

                $this->setLastStep(2);
                return $this->redirect()->toRoute('setup', ['action' => 'step2']);
            }
        }

        return new ViewModel([
            'formStep1' => $formStep1,
        ]);
    }

    /**
     * Action for step 2 - setup of the database connection
     */
    public function step2Action()
    {
        $this->setCurrentLanguage();
        $this->checkSetupStep(2);

        $database = new \Setup\Model\Database(
            ($this->configHelp()->db) ? $this->configHelp()->db->toArray() : []
        );

        $formStep2 = new \Setup\Form\Step2Form();
        $formStep2->get('driver')->setValueOptions($this->getSetupConfig()->drivers->toArray());
        $formStep2->bind($database);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $formStep2->setInputFilter($database->getInputFilter());
            $formStep2->setData($request->getPost());
            if ($formStep2->isValid()) {
                // Reading current local.php config file
                $localPhpSettings = include('config/autoload/local.php');
                if (!is_array($localPhpSettings)) {
                    $localPhpSettings = [];
                }

                // Replacing old db config with new
                $localPhpSettings['db'] = $database->getArrayCopy();
                $configWriter = new \Zend\Config\Writer\PhpArray();
                $configWriter->setUseBracketArraySyntax(true)
                    ->toFile('config/autoload/local.php', new \Zend\Config\Config($localPhpSettings, false));

                // Clearing config cache if enabled
                if ($this->listenerOptions->getConfigCacheEnabled()) {
                    unlink($this->listenerOptions->getConfigCacheFile());
                }

                $this->setLastStep(3);
                return $this->redirect()->toRoute('setup', ['action' => 'step3']);
            }
        }

    	return new ViewModel([
            'formStep2' => $formStep2,
    	]);
    }

    /**
     * Action for step 3 - setup of the database schema
     */
    public function step3Action()
    {
        $this->setCurrentLanguage();
        $this->checkSetupStep(3);

        $dbCheck = $this->getDatabaseCheck();
        $dbCheck->isInstalled();

        $databaseSchema = new \Setup\Model\DatabaseSchema([
            'output' => $dbCheck->getLastMessage(),
        ]);

        $formStep3 = new \Setup\Form\Step3Form();
        $formStep3->bind($databaseSchema);

        $request = $this->getRequest();
        if ($request->isPost()) {
             $this->setLastStep(4);
             return $this->redirect()->toRoute('setup', ['action' => 'step4']);
        }

        // Disable buttons if needed.
        if (!$dbCheck->isInstalled()) {
            $this->disableFormElement($formStep3->get('next'));
        }
        // This code works properly only, because isInstalled() was called above.
        if ($dbCheck->getLastStatus() != $dbCheck::DBNOTINSTALLEDORTABLENOTPRESENT) {
            $this->disableFormElement($formStep3->get('install_schema'));
        }

    	return new ViewModel([
            'formStep3' => $formStep3,
    	]);
    }

    /**
     * Action for step 4 - setup of the database schema
     */
    public function step4Action()
    {
        $this->setCurrentLanguage();
        $this->checkSetupStep(4);

        $dbCheck = $this->getDatabaseCheck();

        $service = $this->zuUserService;

        $userCreation = new \Setup\Model\UserCreation($this->zuModuleOptions);

        $formStep4 = new \Setup\Form\Step4Form();
        if (!$this->zuModuleOptions->getEnableUsername()) {
            $formStep4->remove('username');
        }
        if (!$this->zuModuleOptions->getEnableDisplayName()) {
            $formStep4->remove('display_name');
        }
        $formStep4->setHydrator(new \Zend\Hydrator\ClassMethods);
        $formStep4->bind($userCreation);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $formStep4->setInputFilter($userCreation->getInputFilter());
            $formStep4->setData($request->getPost());
            if ($formStep4->isValid() && ($zuUser = $service->register($userCreation->getArrayCopy()))) {
                $this->getEventManager()->trigger('userCreated', null, ['user' => $zuUser]);
            }
            // TODO: Redirect to step 5
        }

    	return new ViewModel([
            'formStep4' => $formStep4,
    	]);
    }
}
