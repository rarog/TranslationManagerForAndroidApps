<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class MultilevelNavigationMenu extends AbstractHelper
{
    public function __invoke($container, $partial = '')
    {
        if (is_string($container) && !empty($container)){
            $menu = $this->view->navigation($container)
                ->menu()
                ->setMinDepth(0)
                ->setUlClass('nav navbar-nav');

            if (is_string($partial) && !empty($partial)){
                $menu->setPartial($partial);
            }

            return $menu->render();
        }
    }
}
