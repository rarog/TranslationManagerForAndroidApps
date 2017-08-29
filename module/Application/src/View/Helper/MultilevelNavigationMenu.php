<?php
/**
 * Translation Manager for Android Apps
 *
 * PHP version 7
 *
 * @category  PHP
 * @package   TranslationManagerForAndroidApps
 * @author    Andrej Sinicyn <rarogit@gmail.com>
 * @copyright 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps
 */

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class MultilevelNavigationMenu extends AbstractHelper
{
    /**
     * Return rendered multilevel navigation menu HTML.
     *
     * @param string $container
     * @param string $partial
     * @return string
     */
    public function __invoke(string $container, string $partial = '')
    {
        if (empty($container)){
            return '';
        }

        $menu = $this->view->navigation($container)
            ->menu()
            ->setMinDepth(0)
            ->setUlClass('nav navbar-nav');

        if (!empty($partial)){
            $menu->setPartial($partial);
        }

        return $menu->render();
    }
}
