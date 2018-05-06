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

namespace ApplicationTest\View\Helper;

use Application\View\Helper\UseMinifiedHelper;
use PHPUnit\Framework\TestCase;

class UseMinifiedHelperTest extends TestCase
{
    public function testConstructInvoke()
    {
        $helper = new UseMinifiedHelper(false);
        $this->assertEquals(false, $helper());

        $helper = new UseMinifiedHelper(true);
        $this->assertEquals(true, $helper());
    }
}
