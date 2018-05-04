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

namespace TranslationsTest\Controller;

use Application\View\Strategy\SetupAwareRedirectStrategy;
use Prophecy\Argument;
use Translations\Controller\TranslationsController;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\EntryCommonTable;
use Translations\Model\EntryStringTable;
use Translations\Model\ResourceFileEntryTable;
use Translations\Model\ResourceTypeTable;
use Translations\Model\SuggestionStringTable;
use Translations\Model\SuggestionTable;
use Translations\Model\SuggestionVoteTable;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use ZfcRbac\Guard\RoutePermissionsGuard;
use ZfcRbac\Service\AuthorizationService;
use ZfcUser\Entity\UserInterface;
use ReflectionClass;

class TranslationsControllerTest extends AbstractHttpControllerTestCase
{
    private $userId = 10;

    private $authorizationService;

    private $appTable;

    private $appResourceTable;

    private $resourceTypeTable;

    private $resourceFileEntryTable;

    private $entryCommonTable;

    private $entryStringTable;

    private $suggestionTable;

    private $suggestionStringTable;

    private $suggestionVoteTable;

    private function configureServiceManager(ServiceManager $services)
    {
        $this->authorizationService = $this->prophesize(AuthorizationService::class);

        $guards = $services->get('ZfcRbac\Guards');
        foreach ($guards as $guard) {
            if ($guard instanceof RoutePermissionsGuard) {
                $reflection = new ReflectionClass(RoutePermissionsGuard::class);
                $authorizationServiceProperty = $reflection->getProperty('authorizationService');
                $authorizationServiceProperty->setAccessible(true);
                $authorizationServiceProperty->setValue($guard, $this->authorizationService->reveal());
            }
        }

        $user = $this->prophesize(UserInterface::class);
        $user->getId()->willReturn($this->userId);

        $authenticationService = $this->prophesize(AuthenticationService::class);
        // Always simulating that user is logged in.
        $authenticationService->hasIdentity()->willReturn(true);
        $authenticationService->getIdentity()->willReturn($user->reveal());

        $setupAwareRedirectStrategy = $services->get(SetupAwareRedirectStrategy::class);

        $reflection = new ReflectionClass(SetupAwareRedirectStrategy::class);
        $authenticationServiceProperty = $reflection->getProperty('authenticationService');
        $authenticationServiceProperty->setAccessible(true);
        $authenticationServiceProperty->setValue($setupAwareRedirectStrategy, $authenticationService->reveal());

        $this->appTable = $this->prophesize(AppTable::class);
        $this->appResourceTable = $this->prophesize(AppResourceTable::class);
        $this->resourceTypeTable = $this->prophesize(ResourceTypeTable::class);
        $this->resourceFileEntryTable = $this->prophesize(ResourceFileEntryTable::class);
        $this->entryCommonTable = $this->prophesize(EntryCommonTable::class);
        $this->entryStringTable = $this->prophesize(EntryStringTable::class);
        $this->suggestionTable = $this->prophesize(SuggestionTable::class);
        $this->suggestionStringTable = $this->prophesize(SuggestionStringTable::class);
        $this->suggestionVoteTable = $this->prophesize(SuggestionVoteTable::class);

        $services->setAllowOverride(true);

        $services->setService('config', $this->updateConfig($services->get('config')));
        $services->setService('zfcuser_auth_service', $authenticationService->reveal());
        $services->setService(AuthorizationService::class, $this->authorizationService->reveal());
        $services->setService(AppTable::class, $this->appTable->reveal());
        $services->setService(AppResourceTable::class, $this->appResourceTable->reveal());
        $services->setService(ResourceTypeTable::class, $this->resourceTypeTable->reveal());
        $services->setService(ResourceFileEntryTable::class, $this->resourceFileEntryTable->reveal());
        $services->setService(EntryCommonTable::class, $this->entryCommonTable->reveal());
        $services->setService(EntryStringTable::class, $this->entryStringTable->reveal());
        $services->setService(SuggestionTable::class, $this->suggestionTable->reveal());
        $services->setService(SuggestionStringTable::class, $this->suggestionStringTable->reveal());
        $services->setService(SuggestionVoteTable::class, $this->suggestionVoteTable->reveal());

        $services->setAllowOverride(false);
    }

    private function updateConfig($config)
    {
        $config['db'] = [];
        return $config;
    }

    private function setAllAppsAndResourcesAllowedToUserPromise(bool $userIdEqualsZero = false)
    {
        $returnArray = [
            [
                'app_id' => 1,
                'app_name' => 'App 1',
                'app_resource_id' => '1',
                'app_resource_name' => 'values',
                'locale' => 'en',
            ],
            [
                'app_id' => 1,
                'app_name' => 'App 1',
                'app_resource_id' => '2',
                'app_resource_name' => 'values_de',
                'locale' => 'de',
            ],
            [
                'app_id' => 2,
                'app_name' => 'App 2',
                'app_resource_id' => '3',
                'app_resource_name' => 'values',
                'locale' => 'en',
            ],
        ];

        if ($userIdEqualsZero) {
            $userId = 0;
            $returnArray[] = [
                'app_id' => 42,
                'app_name' => 'AnswerToLifeUniverseAndEverything',
                'app_resource_id' => '99',
                'app_resource_name' => 'values',
                'locale' => 'en',
            ];
        } else {
            $userId = $this->userId;
        }

        $this->appTable->getAllAppsAndResourcesAllowedToUser($userId)->willReturn($returnArray);
    }

    protected function setUp()
    {
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();

        $this->configureServiceManager($this->getApplicationServiceLocator());
    }

    protected function tearDown()
    {
        unset($this->suggestionVoteTable);
        unset($this->appTable);
        unset($this->appResourceTable);
        unset($this->resourceTypeTable);
        unset($this->resourceFileEntryTable);
        unset($this->entryCommonTable);
        unset($this->entryStringTable);
        unset($this->suggestionTable);
        unset($this->suggestionStringTable);
        unset($this->authorizationService);
    }

    public function testIndexActionCantBeAccessedWithoutPermissions()
    {
        $this->authorizationService->isGranted('translations.view')->willReturn(false);

        $this->dispatch('/translations', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('translations');
        $this->assertControllerName(TranslationsController::class);
        $this->assertControllerClass('TranslationsController');
        $this->assertMatchedRouteName('translations');
        $this->assertRedirectTo('/');
    }

    public function testIndexActionCanBeAccessedNoTeamViewAllPermission()
    {
        $this->authorizationService->isGranted('translations.view')->willReturn(true);
        $this->authorizationService->isGranted('team.viewAll', Argument::cetera())->willReturn(false);

        $this->setAllAppsAndResourcesAllowedToUserPromise();

        $this->dispatch('/translations', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('translations');
        $this->assertControllerName(TranslationsController::class);
        $this->assertControllerClass('TranslationsController');
        $this->assertMatchedRouteName('translations');
        $this->assertQuery('.container div#header');
        $this->assertNotQuery('.container input#showAll');
        $this->assertQueryCount('.container div#selection select#app option', 2);
        $this->assertQuery('.container div#selection select#resource');
        $this->assertQuery('.container div#selectionHint');
        $this->assertQuery('.container div#spinner');
        $this->assertQuery('.container div#translationRow table#translations');
    }

    public function testIndexActionCanBeAccessedTeamViewAllPermission()
    {
        $this->authorizationService->isGranted('translations.view')->willReturn(true);
        $this->authorizationService->isGranted('team.viewAll', Argument::cetera())->willReturn(true);

        $this->setAllAppsAndResourcesAllowedToUserPromise();
        $this->setAllAppsAndResourcesAllowedToUserPromise(true);

        $this->dispatch('/translations', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('translations');
        $this->assertControllerName(TranslationsController::class);
        $this->assertControllerClass('TranslationsController');
        $this->assertMatchedRouteName('translations');
        $this->assertQuery('.container div#header');
        $this->assertQuery('.container input#showAll');
        $this->assertQueryCount('.container div#selection select#app option', 2);
        $this->assertQuery('.container div#selection select#resource');
        $this->assertQuery('.container div#selectionHint');
        $this->assertQuery('.container div#spinner');
        $this->assertQuery('.container div#translationRow table#translations');
    }
}
