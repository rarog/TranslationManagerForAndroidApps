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

namespace ApplicationTest\Controller;

use Application\Module;
use Application\Controller\IndexController;
use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZfcRbac\Guard\RoutePermissionsGuard;
use ZfcRbac\Service\AuthorizationService;
use ReflectionClass;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    protected $authorizationService;

    public function setUp()
    {
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();

        // Tricking: on console requests application doesn't run through most onBootstrap events,
        // thus not trying to instantiate session and other things not needed for testing
        $this->setUseConsoleRequest(true);
        $serviceManager = $this->getApplicationServiceLocator();
        $this->setUseConsoleRequest(false);

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $this->authorizationService = $authorizationService;

        $guards = $serviceManager->get('ZfcRbac\Guards');
        foreach ($guards as $guard) {
            if ($guard instanceof RoutePermissionsGuard) {
                $reflection = new ReflectionClass(RoutePermissionsGuard::class);
                $authorizationServiceProperty = $reflection->getProperty('authorizationService');
                $authorizationServiceProperty->setAccessible(true);
                $authorizationServiceProperty->setValue($guard, $authorizationService->reveal());
            }
        }
    }

    public function testAboutActionCantBeAccessedByNonUsers()
    {
        $this->authorizationService->isGranted('userBase')->willReturn(false);

        $this->dispatch('/application/about', 'GET');
        $this->assertResponseStatusCode(500);
        $this->assertModuleName('application');
        $this->assertControllerName(IndexController::class);
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('application/about');
    }

    public function testAboutActionCanBeAccessedByUsers()
    {
        $this->authorizationService->isGranted('userBase')->willReturn(true);

        $this->dispatch('/application/about', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName(IndexController::class);
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('application/about');

        // ViewModel template containing application title an version
        $this->assertQueryContentContains(
            '.container .row .col-md-12 h1',
            sprintf('Translation Manager for Android Apps v%s', Module::VERSION)
        );
    }

    public function testIndexActionCantBeAccessedByNonUsers()
    {
        $this->authorizationService->isGranted('userBase')->willReturn(false);

        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(500);
        $this->assertModuleName('application');
        $this->assertControllerName(IndexController::class);
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }

    public function testIndexActionCanBeAccessedByUsers()
    {
        $this->authorizationService->isGranted('userBase')->willReturn(true);

        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName(IndexController::class);
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');

        // ViewModel template rendered within layout
        $this->assertQuery('.container .jumbotron');
    }

    public function testInvalidRouteDoesNotCrash()
    {
        $this->dispatch('/invalid/route', 'GET');
        $this->assertResponseStatusCode(404);
    }
}
