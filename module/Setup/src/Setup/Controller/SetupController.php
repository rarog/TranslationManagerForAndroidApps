<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SetupController extends AbstractActionController
{
    protected $translator;
    protected $container;
    protected $setupConfig;
    protected $availableLanguages;
    protected $fallbackLocale;

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
                 ->setFallbackLocale($this->fallbackLocale)
                 ->setLocale($this->container->currentLanguage);
        }
    }

    public function __construct(\Zend\Mvc\I18n\Translator $translator)
    {
        $this->translator = $translator;
        $this->container = new \Zend\Session\Container('setup');
        $this->fallbackLocale = $translator->getLocale();
    }

    public function indexAction()
    {
        $this->setCurrentLanguage();
        
        $step1 = new \Setup\Model\Step1(array(
            'setup_language' => $this->translator->getLocale(),
        ));

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
                 $this->setCurrentLanguage();
             }
        }

        return new ViewModel(array(
            'formStep1' => $formStep1,
        ));
    }
}
