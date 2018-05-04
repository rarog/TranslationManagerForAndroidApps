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
use Prophecy\Argument;
use Translations\Controller\Plugin\GetAppIfAllowed;
use Translations\Model\App;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Zend\Mvc\Controller\AbstractActionController;
use ZfcRbac\Service\AuthorizationService;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;
use ZfcUser\Entity\UserInterface;
use RuntimeException;

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

    protected function tearDown()
    {
        unset($this->getAppIfAllowed);
        unset($this->authorizationService);
        unset($this->appResourceTable);
        unset($this->appTable);
    }

    public function testInvokeAppEqualsZero()
    {
        $getAppIfAllowed = $this->getAppIfAllowed;
        $this->assertEquals(false, $getAppIfAllowed(0));
    }

    public function testInvokeAppTableThrowsException()
    {
        $appId = 42;

        $this->appTable->getApp($appId)->willThrow(new RuntimeException());

        $getAppIfAllowed = $this->getAppIfAllowed;
        $this->assertEquals(false, $getAppIfAllowed($appId));
    }

    public function testInvokeNoPermissionToViewAllApps()
    {
        $appId = 42;
        $userId = 10;

        $this->appTable->getApp($appId)->willReturn(new App([
            'id' => $appId,
        ]));
        $this->appTable->hasUserPermissionForApp($userId, $appId)->willReturn(false);
        $this->authorizationService->isGranted('app.viewAll')->willReturn(false);

        $user = $this->prophesize(UserInterface::class);
        $user->getId()->willReturn($userId);

        $zfcUserAuthentication = $this->prophesize(ZfcUserAuthentication::class);
        $zfcUserAuthentication->setController(Argument::any())->shouldBeCalled();
        $zfcUserAuthentication->getIdentity()->willReturn($user->reveal());

        $controller = new class() extends AbstractActionController {
        };
        $controller->getPluginManager()->setService('zfcUserAuthentication', $zfcUserAuthentication->reveal());

        $this->getAppIfAllowed->setController($controller);

        $getAppIfAllowed = $this->getAppIfAllowed;
        $this->assertEquals(false, $getAppIfAllowed($appId));
    }

    public function testInvokeReturnsApp()
    {
        $appId = 42;

        $app = new App([
            'id' => $appId,
        ]);

        $this->appTable->getApp($appId)->willReturn($app);
        $this->authorizationService->isGranted('app.viewAll')->willReturn(true);

        $getAppIfAllowed = $this->getAppIfAllowed;
        $this->assertEquals($app, $getAppIfAllowed($appId));
    }

    public function testInvokeCheckHasDefaultValuesEqualsTrueReturnsFalse()
    {
        $appId = 42;

        $this->appTable->getApp($appId)->willReturn(new App([
            'id' => $appId,
        ]));
        $this->authorizationService->isGranted('app.viewAll')->willReturn(true);
        $this->appResourceTable->getAppResourceByAppIdAndName($appId, 'values')->willThrow(new RuntimeException());

        $getAppIfAllowed = $this->getAppIfAllowed;
        $this->assertEquals(false, $getAppIfAllowed($appId, true));
    }
}
