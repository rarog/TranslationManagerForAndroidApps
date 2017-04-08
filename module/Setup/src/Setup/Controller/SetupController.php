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

    protected function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = $this->getServiceLocator()->get('MvcTranslator');
        }
        return $this->translator;
    }
    
    public function indexAction()
    {
        $setupConfig = $this->configHelp('setup');
        $languages = $setupConfig->available_languages->toArray();
        
        $step1 = new \Setup\Model\Step1(array(
            'setup_language' = $this->getTranslator()->getLocale(),
        ));

        $formStep1 = new \Setup\Form\Step1Form();
        $formStep1->get('setup_language')->setValueOptions($languages);
        $formStep1->bind($step1);

        return new ViewModel(array(
            'formStep1' => $formStep1,
        ));
    }
}
