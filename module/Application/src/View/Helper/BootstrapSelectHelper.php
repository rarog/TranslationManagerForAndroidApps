<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class BootstrapSelectHelper extends AbstractHelper
{
    public function __invoke()
    {
        $this->view->headScript()->appendFile($this->view->basePath('/js/bootstrap-select.min.js'));
        $this->view->headScript()->appendFile($this->view->basePath('/js/i18n/defaults-' . $this->view->plugin('translate')->getTranslator()->getLocale() . '.min.js'));
        $this->view->headLink()->prependStylesheet($this->view->basePath('/css/bootstrap-select.min.css'));
    }
}
