<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class SettingsController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute('home');
    }

    public function userlanguagesAction()
    {
        return [];
    }
}
