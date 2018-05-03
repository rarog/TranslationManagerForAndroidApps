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

namespace TranslationsTest\Controller\Plugin;

use PHPUnit\Framework\TestCase;
use Translations\Controller\Plugin\GetAppIfAllowed;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use ZfcRbac\Service\AuthorizationService;

class GetAppIfAllowedTest extends TestCase
{
    private $appTable;

    private $appResourceTable;

    private $authorizationService;

    private $getAppIfAllowed;

    protected function setUp()
    {
        $this->appTable = $this->prophesize(AppTable::class);

        $this->appResourceTable = $this->prophesize(AppResourceTable::class);

        $this->authorizationService = $this->prophesize(AuthorizationService::class);

        $this->getAppIfAllowed = new GetAppIfAllowed(
            $this->appTable->reveal(),
            $this->appResourceTable->reveal(),
            $this->authorizationService->reveal()
        );
    }

    public function testInvokeAppEqualsZero()
    {
        $getAppIfAllowed = $this->getAppIfAllowed;
        $this->assertEquals(false, $getAppIfAllowed(0));
    }
}
