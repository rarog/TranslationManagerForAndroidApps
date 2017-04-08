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
    public function indexAction()
    {
        $setupConfig = $this->configHelp('setup');
        $languages = $setupConfig->available_languages;

        $formStep1 = new OrderAddFollowupForm();
        $formStep1->get('setup_language')->setValueOptions($languages);

        return new ViewModel(array(
            'formStep1' => $formStep1,
        ));
    }
}