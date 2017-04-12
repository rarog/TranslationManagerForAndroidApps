<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Controller;

use Zend\ModuleManager\Listener\ListenerOptions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\I18n\Translator;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer as Renderer;

class SetupController extends AbstractActionController
{
    protected $translator;
    protected $container;
    protected $setupConfig;
    protected $availableLanguages;
    protected $listenerOptions;
    protected $renderer;
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
        $lastStep = $this->getLastStep();//echo $lastStep.$currentStep;exit;
        if ($currentStep > $lastStep) {
            $action = [];
            if ($lastStep > 1) {
                $action['action'] = 'step' . $lastStep;
            }
            $this->redirect()->toRoute('setup', $action);
        }
    }

    public function __construct(Translator $translator, ListenerOptions $listenerOptions, Renderer $renderer)
    {
        $this->translator = $translator;
        $this->container = new \Zend\Session\Container('setup');
        $this->listenerOptions = $listenerOptions;
        $this->renderer = $renderer;
    }

    /**
     * Action for database connection test call via JSON
     */
    public function databaseconnectiontestAction()
    {
        $this->setCurrentLanguage();

        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() &&
            $request->isPost() &&
            ($postData = $request->getPost()->toArray())) {

            $dbCheck = new \Setup\Model\DatabaseChecks($postData, $this->translator);
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

        $request  = $this->getRequest();
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

        $request  = $this->getRequest();
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

        $databaseSchema = new \Setup\Model\DatabaseSchema();

        $formStep3 = new \Setup\Form\Step3Form();
        $formStep3->bind($databaseSchema);

        $request  = $this->getRequest();
        if ($request->isPost()) {
            // TODO: Initiating the db schema installation.
        } else {
            $nextButton = $formStep3->get('next');
            $nextButton->setAttribute('class', $nextButton->getAttribute('class') . ' disabled');
        }

    	return new ViewModel([
            'formStep3' => $formStep3,
    	]);
    }
}
