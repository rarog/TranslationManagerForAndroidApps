<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Controller;

use Setup\Model\DatabaseHelper;
use Zend\Math\Rand;
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
    private $translator;
    private $container;
    private $setupConfig;
    private $availableLanguages;
    private $listenerOptions;
    private $renderer;
    private $zuUserService;
    private $zuModuleOptions;
    private $databaseHelper;
    private $configWriter;
    private $lastStep;

    /**
     * Returns config of Setup module
     *
     * @return \Zend\Config\Config
     */
    private function getSetupConfig()
    {
        if (is_null($this->setupConfig)) {
            $this->setupConfig = $this->configHelp('setup');
        }
        return $this->setupConfig;
    }


    /**
     * Returns array with languages availabe during setup
     *
     * @return array
     */
    private function getAvailableLanguages()
    {
        if (is_null($this->availableLanguages)) {
            $this->availableLanguages = $this->getSetupConfig()->available_languages->toArray();
        }
        return $this->availableLanguages;
    }

    /**
     * Gets an instance of PhpArray config writer
     *
     * @return \Zend\Config\Writer\PhpArray
     */
    private function getConfigWriter()
    {
        if (is_null($this->configWriter)) {
            $this->configWriter= new \Zend\Config\Writer\PhpArray();
            $this->configWriter->setUseBracketArraySyntax(true);
        }
        return $this->configWriter;
    }

    /**
     * Sets the translator lange to the current internal variable content
     */
    private function setCurrentLanguage()
    {
        if (!is_null($this->container->currentLanguage) &&
            array_key_exists($this->container->currentLanguage, $this->getAvailableLanguages())) {
            $this->translator
                ->setLocale($this->container->currentLanguage)
                ->setFallbackLocale(\Locale::getPrimaryLanguage($this->container->currentLanguage));
        }
    }

    /**
     * Get the current value of last step variable
     *
     * @return int
     */
    private function getLastStep() {
        return (int) $this->container->lastStep;
    }

    /**
     * Sets the current value of last step variable
     *
     * @param int $lastStep
     */
    private function setLastStep($lastStep) {
        $this->container->lastStep = (int) $lastStep;
    }

    /**
     * Checks, if the the call to the controller is allowed
     *
     * @param int $currentStep
     * @return \Zend\Http\Response
     */
    private function checkSetupStep($currentStep)
    {
        $currentStep = (int) $currentStep;
        // TODO: Check if starting setup is allowed at all.
        $lastStep = $this->getLastStep();
        if ($currentStep > $lastStep) {
            $action = [];
            if ($lastStep > 1) {
                $action['action'] = 'step' . $lastStep;
            }
            return $this->redirect()->toRoute('setup', $action);
        } else {
            $dbHelper = $this->databaseHelper;

            if (!$dbHelper->canConnect() && ($currentStep > 2)) {
                return $this->redirect()->toRoute('setup', ['action' => 'step2']);
            }
        }
    }

    /**
     * Adds Bootstrap disabled class to a form element
     *
     * @param \Zend\Form\Element $element
     */
    private function disableFormElement(\Zend\Form\Element $element)
    {
        if ($element) {
            $element->setAttribute('disabled', 'disabled');
        }
    }

    /**
     * Helper function to replace config blocks in file
     *
     * @param string $file
     * @param array $configArray
     */
    private function replaceConfigInFile($file, $configArray)
    {
        // Reading current content of config file
        $config = include($file);
        if (!is_array($config)) {
            $config = [];
        }

        // Replacing old config with new
        foreach ($configArray as $key => $value) {
            $config[$key] = $value;
        }
        $this->getConfigWriter()
            ->toFile($file, new \Zend\Config\Config($config, false));
    }

    /**
     * Generates error 403 and returns view model with disabled layout
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function throw403()
    {
        $this->getResponse()->setStatusCode(403);
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    /**
     * Constructor
     *
     * @param Translator $translator
     * @param ListenerOptions $listenerOptions
     * @param Renderer $renderer
     * @param ZUUser $zuUserService
     * @param ZUModuleOptions $zuModuleOptions
     * @param DatabaseHelper $databaseHelper
     */
    public function __construct(Translator $translator, ListenerOptions $listenerOptions, Renderer $renderer, ZUUser $zuUserService, ZUModuleOptions $zuModuleOptions, DatabaseHelper $databaseHelper)
    {
        $this->translator = $translator;
        $this->container = new \Zend\Session\Container('setup');
        $this->listenerOptions = $listenerOptions;
        $this->renderer = $renderer;
        $this->zuUserService = $zuUserService;
        $this->zuModuleOptions = $zuModuleOptions;
        $this->databaseHelper = $databaseHelper;
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
                $dbHelper = $this->databaseHelper;
                $dbHelper->setDbConfigArray($postData);
                $type = ($dbHelper->canConnect()) ? 'success' : 'danger';
                $message = $dbHelper->getLastMessage();
            } else {
                $type = 'danger';
                $message = $this->translator->translate('<strong>Error:</strong> Invalid POST data was provided.');
            }

            $viewModel = new ViewModel([
                'type'     => $type,
                'message'  => $message,
                'canClose' => true,
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
            $dbHelper = $this->databaseHelper;
            $dbHelper->installSchema();

            $nextEnabled = true;
            $installSchemaEnabled = true;
            // Disable buttons if needed.
            if (!$dbHelper->isSchemaInstalled()) {
                $nextEnabled = false;
            }
            // This code works properly only, because isSchemaInstalled() was called above.
            if ($dbHelper->getLastStatus() != $dbHelper::DBNOTINSTALLEDORTABLENOTPRESENT) {
                $installSchemaEnabled = false;
            }

            $jsonModel = new JsonModel([
                'html'                 => $dbHelper->getLastMessage(),
                'nextEnabled'          => $nextEnabled,
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
        $security = ($this->configHelp()->security) ? $this->configHelp()->security->toArray(): '';
        $masterKey = (is_array($security)) ? (string) $security['master_key'] : '';
        if ($masterKey == '') {
            $masterKey = Rand::getString(64);
        }

        $formStep2 = new \Setup\Form\Step2Form();
        $formStep2->get('driver')->setValueOptions($this->getSetupConfig()->drivers->toArray());
        $formStep2->bind($database);
        $formStep2->get('master_key')->setValue($masterKey);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $formStep2->setInputFilter($database->getInputFilter());
            $formStep2->setData($request->getPost());
            if ($formStep2->isValid()) {
                $security['master_key'] = $masterKey;

                $config = [];
                $config['db'] = $database->getArrayCopy();
                $config['security'] = $security;

                // Replacing content of local.php config file
                $this->replaceConfigInFile('config/autoload/local.php', $config);

                // Replacing content of config cache if enabled
                if ($this->listenerOptions->getConfigCacheEnabled() &&
                    file_exists($this->listenerOptions->getConfigCacheFile())) {
                    $this->replaceConfigInFile($this->listenerOptions->getConfigCacheFile(), $config);
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

        $dbHelper = $this->databaseHelper;
        $dbHelper->isSchemaInstalled();

        $databaseSchema = new \Setup\Model\DatabaseSchema([
            'output' => $dbHelper->getLastMessage(),
        ]);

        $formStep3 = new \Setup\Form\Step3Form();
        $formStep3->bind($databaseSchema);

        $request = $this->getRequest();
        if ($request->isPost()) {
             $this->setLastStep(4);
             return $this->redirect()->toRoute('setup', ['action' => 'step4']);
        }

        // Disable buttons if needed.
        if (!$dbHelper->isSchemaInstalled()) {
            $this->disableFormElement($formStep3->get('next'));
        }
        // This code works properly only, because isSchemaInstalled() was called above.
        if ($dbHelper->getLastStatus() != $dbHelper::DBNOTINSTALLEDORTABLENOTPRESENT) {
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

        $userExists = $this->databaseHelper
            ->isSetupComplete();
        if ($userExists) {
            $type = 'success';
            $message = $this->translator->translate('A user is already in the database. This step will be skipped.');
        } else {
            $type = 'info';
            $message = $this->translator->translate('Please create your user.');
        }

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
            if ($userExists) {
                return $this->redirect()->toRoute('application', ['action' => 'index']);
            } else {
                $formStep4->setInputFilter($userCreation->getInputFilter());
                $formStep4->setData($request->getPost());
                if ($formStep4->isValid() && ($zuUser = $service->register($userCreation->getArrayCopy()))) {
                    $this->getEventManager()->trigger('userCreated', null, ['user' => $zuUser]);
                    $userExists = true;

                    $type = 'success';
                    $message = $this->translator->translate('The user has been sucessfully created.');
                } else {
                    $type = 'danger';
                    $message = $this->translator->translate('The user couldn\'t be created. Please check the entries.');
                }
            }
        }

        $formElement = $userExists ? 'create_user' : 'next';
        $this->disableFormElement($formStep4->get($formElement));
        if ($userExists) {
            $this->disableFormElement($formStep4->get('username'));
            $this->disableFormElement($formStep4->get('email'));
            $this->disableFormElement($formStep4->get('display_name'));
            $this->disableFormElement($formStep4->get('password'));
            $this->disableFormElement($formStep4->get('passwordVerify'));
        }

        $viewModel = new ViewModel([
            'type'     => $type,
            'message'  => $message,
            'canClose' => false,
        ]);
        $viewModel->setTemplate('partial/alert.phtml')
            ->setTerminal(true);
        $infoArea = $this->renderer->render($viewModel);

    	return new ViewModel([
    	    'infoArea'  => $infoArea,
            'formStep4' => $formStep4,
    	]);
    }
}
