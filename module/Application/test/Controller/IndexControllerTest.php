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
use Setup\Helper\DatabaseHelper;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZfcRbac\Guard\RoutePermissionsGuard;
use ZfcRbac\Service\AuthorizationService;
use ReflectionClass;
use Application\View\Strategy\SetupAwareRedirectStrategy;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    private $authorizationService;

    private function configureServiceManager(ServiceManager $services)
    {
        $authorizationService = $this->prophesize(AuthorizationService::class);
        $this->authorizationService = $authorizationService;

        $guards = $services->get('ZfcRbac\Guards');
        foreach ($guards as $guard) {
            if ($guard instanceof RoutePermissionsGuard) {
                $reflection = new ReflectionClass(RoutePermissionsGuard::class);
                $authorizationServiceProperty = $reflection->getProperty('authorizationService');
                $authorizationServiceProperty->setAccessible(true);
                $authorizationServiceProperty->setValue($guard, $authorizationService->reveal());
            }
        }

        $databaseHelper = $this->prophesize(DatabaseHelper::class);
        // Always simulating, that setup is complete.
        $databaseHelper->isSetupComplete()->willReturn(true);

        $setupAwareRedirectStrategy = $services->get(SetupAwareRedirectStrategy::class);

        $reflection = new ReflectionClass(SetupAwareRedirectStrategy::class);
        $databaseHelperProperty = $reflection->getProperty('setupDatabaseHelper');
        $databaseHelperProperty->setAccessible(true);
        $databaseHelperProperty->setValue($setupAwareRedirectStrategy, $databaseHelper->reveal());

        $services->setAllowOverride(true);

        $services->setService('config', $this->updateConfig($services->get('config')));
        $services->setService(AuthorizationService::class, $authorizationService->reveal());
        $services->setService(DatabaseHelper::class, $databaseHelper->reveal());

        $services->setAllowOverride(false);
    }

    private function updateConfig($config)
    {
        $config['db'] = [];
        return $config;
    }

    public function setUp()
    {
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();

        $this->configureServiceManager($this->getApplicationServiceLocator());
    }

    public function testAboutActionCantBeAccessedByNonUsers()
    {
        $this->authorizationService->isGranted('userBase')->willReturn(false);

        $this->dispatch('/application/about', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('application');
        $this->assertControllerName(IndexController::class);
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('application/about');
        $this->assertRedirectTo('/user/login');
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
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('application');
        $this->assertControllerName(IndexController::class);
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
        $this->assertRedirectTo('/user/login');
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
