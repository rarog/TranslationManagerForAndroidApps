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
use Zend\View\Model\ViewModel;

class SetupController extends AbstractActionController
{
    protected $translator;
    protected $container;
    protected $setupConfig;
    protected $availableLanguages;
    protected $listenerOptions;

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

    public function __construct(Translator $translator, ListenerOptions $listenerOptions)
    {
        $this->translator = $translator;
        $this->container = new \Zend\Session\Container('setup');
        $this->listenerOptions = $listenerOptions;
    }

    /**
     * Action for step 1 - welcome screen and setup language selection
     */
    public function indexAction()
    {
        $this->setCurrentLanguage();

        $step1 = new \Setup\Model\Step1([
            'setup_language' => $this->translator->getLocale(),
        ]);

        $formStep1 = new \Setup\Form\Step1Form();
        $formStep1->get('setup_language')->setValueOptions($this->getAvailableLanguages());
        $formStep1->bind($step1);

        $request  = $this->getRequest();
        if ($request->isPost()) {
            $formStep1->setInputFilter($step1->getInputFilter());
            $formStep1->setData($request->getPost());
             if ($formStep1->isValid() &&
                 array_key_exists($step1->SetupLanguage, $this->getAvailableLanguages())) {
                 $this->container->currentLanguage = $step1->SetupLanguage;

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

        $step2 = new \Setup\Model\Step2(
            ($this->configHelp()->db) ? $this->configHelp()->db->toArray() : []
        );

        $formStep2 = new \Setup\Form\Step2Form();
        $formStep2->get('driver')->setValueOptions($this->getSetupConfig()->drivers->toArray());
        $formStep2->bind($step2);

        $request  = $this->getRequest();
        if ($request->isPost()) {
            $formStep2->setInputFilter($step2->getInputFilter());
            $formStep2->setData($request->getPost());
             if ($formStep2->isValid()) {
                 // Reading current local.php config file
                 $localPhpSettings = include('config/autoload/local.php');
                 if (!is_array($localPhpSettings)) {
                     $localPhpSettings = [];
                 }

                 // Replacing old db config with new
                 $localPhpSettings['db'] = $step2->getArrayCopy();
                 $configWriter = new \Zend\Config\Writer\PhpArray();
                 $configWriter->setUseBracketArraySyntax(true)
                              ->toFile('config/autoload/local.php', new \Zend\Config\Config($localPhpSettings, false));

                 // Clearing config cache if enabled
                 if ($this->listenerOptions->getConfigCacheEnabled()) {
                     unlink($this->listenerOptions->getConfigCacheFile());
                 }
             }
        }

    	return new ViewModel([
            'formStep2' => $formStep2,
    	]);
    }
}
