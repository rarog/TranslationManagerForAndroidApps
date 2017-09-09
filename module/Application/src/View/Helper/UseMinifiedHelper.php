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

class UseMinifiedHelper extends AbstractHelper
{
    /**
     * @var bool
     */
    private $useMinified;

    /**
     * Constructor
     *
     * @param bool $useMinified
     */
    public function __construct(bool $useMinified)
    {
        $this->useMinified = $useMinified;
    }

    /**
     * Returns the setting
     *
     * @return boolean
     */
    public function __invoke()
    {
        return $this->useMinified;
    }
}
