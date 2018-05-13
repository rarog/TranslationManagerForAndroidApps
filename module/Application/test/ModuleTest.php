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

use Application\Module;
use Application\Listener\RbacListener;
use Application\Model\UserLanguagesTable;
use Application\Model\UserSettings;
use Application\Model\UserSettingsTable;
use Application\Model\UserTable;
use Application\View\Strategy\SetupAwareRedirectStrategy;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Authentication\AuthenticationService;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Console\Console;
use Zend\Db\Adapter\AdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManager;
use Zend\Http\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\I18n\Translator\Translator as I18nTranslator;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\ValidatorChain;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Exception\RuntimeException as SessionRuntimeException;
use Zend\Session\SaveHandler\Cache;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\Id;
use Zend\Session\Validator\RemoteAddr;
use Zend\Validator\AbstractValidator;
use ZfcUser\Authentication\Adapter\AdapterChain;
use ZfcUser\Entity\UserInterface;
use ZfcUser\Mapper\User as UserMapper;
use ReflectionClass;
use RuntimeException;
use stdClass;
use phpmock\MockBuilder;

class ModuleTest extends TestCase
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var ReflectionClass
     */
    private $moduleReflection;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @var MvcEvent
     */
    private $event;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->module = new Module();
        $this->moduleReflection = new ReflectionClass(Module::class);
        $this->serviceManager = new ServiceManager();

        $application = new Application($this->serviceManager, new EventManager(), new Request(), new Response());

        $this->event = new MvcEvent();
        $this->event->setApplication($application);
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->event);
        unset($this->serviceManager);
        unset($this->moduleReflection);
        unset($this->module);
    }

    public function testImplementsBootstrapListenerInterface()
    {
        $this->assertInstanceOf(BootstrapListenerInterface::class, $this->module);
        $this->assertTrue($this->moduleReflection->hasMethod('onBootstrap'));
    }

    public function testImplementsConfigProviderInterface()
    {
        $this->assertInstanceOf(ConfigProviderInterface::class, $this->module);
        $this->assertTrue($this->moduleReflection->hasMethod('getConfig'));
        $this->assertInternalType('array', $this->module->getConfig());
    }

    public function testImplementsServiceProviderInterface()
    {
        $this->assertInstanceOf(ServiceProviderInterface::class, $this->module);
        $this->assertTrue($this->moduleReflection->hasMethod('getServiceConfig'));

        $serviceConfig = $this->module->getServiceConfig();
        $this->assertInternalType('array', $serviceConfig);

        $this->serviceManager->configure($serviceConfig);
        $this->serviceManager->setService(
            AdapterInterface::class,
            $this->prophesize(AdapterInterface::class)->reveal()
        );
        $this->serviceManager->setService(
            'zfcuser_user_mapper',
            $this->prophesize(UserMapper::class)->reveal()
        );

        $this->assertTrue($this->serviceManager->has(UserTable::class));
        $this->assertInstanceOf(UserTable::class, $this->serviceManager->get(UserTable::class));

        $this->assertTrue($this->serviceManager->has(UserLanguagesTable::class));
        $this->assertInstanceOf(UserLanguagesTable::class, $this->serviceManager->get(UserLanguagesTable::class));

        $this->assertTrue($this->serviceManager->has(UserSettingsTable::class));
        $this->assertInstanceOf(UserSettingsTable::class, $this->serviceManager->get(UserSettingsTable::class));

        // Testing SessionManager with empty configuration, rest is done in separate function.
        $this->assertTrue($this->serviceManager->has(SessionManager::class));

        $sessionManager = $this->serviceManager->get(SessionManager::class);

        $this->assertInstanceOf(SessionManager::class, $sessionManager);
        $this->assertNull($sessionManager->getSaveHandler());
    }

    public function testImplementsServiceProviderInterfaceSessionManager()
    {
        $sessionConfig = [
            'session' => [
                'config' => [
                    'class' => SessionConfig::class,
                    'options' => [
                        'name' => 'tmfaa:session',
                    ],
                ],
                'save_handler' => Cache::class,
                'storage' => SessionArrayStorage::class,
                'validators' => [
                    RemoteAddr::class,
                    HttpUserAgent::class,
                ],
            ],
        ];

        $this->serviceManager->configure($this->module->getServiceConfig());
        $this->serviceManager->setService('config', $sessionConfig);

        $saveHandler = $this->prophesize(Cache::class)->reveal();
        $this->serviceManager->setService(Cache::class, $saveHandler);

        $this->assertTrue($this->serviceManager->has(SessionManager::class));

        $sessionManager = $this->serviceManager->get(SessionManager::class);

        $this->assertInstanceOf(SessionManager::class, $sessionManager);
        $this->assertSame($saveHandler, $sessionManager->getSaveHandler());
    }

    public function testOnBootstrap()
    {
        $moduleMock1 = new class() extends Module {
            public $bootstrapLateListenersCalled = false;
            public $bootstrapSessionCalled = false;
            public $bootstrapTranslatorCalled = false;
            public $bootstrapUserSettingsCalled = false;

            protected function bootstrapLateListeners(EventInterface $e)
            {
                $this->bootstrapLateListenersCalled = true;
            }

            protected function bootstrapSession(EventInterface $e)
            {
                $this->bootstrapSessionCalled = true;
            }

            protected function bootstrapTranslator(EventInterface $e)
            {
                $this->bootstrapTranslatorCalled = true;
            }

            protected function bootstrapUserSettings(EventInterface $e)
            {
                $this->bootstrapUserSettingsCalled = true;
            }
        };
        $moduleMock2 = clone $moduleMock1;

        $usedConsoleBackup = Console::isConsole();
        try {
            Console::overrideIsConsole(true);
            $moduleMock1->onBootstrap($this->event);
            $this->assertFalse($moduleMock1->bootstrapLateListenersCalled);
            $this->assertFalse($moduleMock1->bootstrapSessionCalled);
            $this->assertFalse($moduleMock1->bootstrapTranslatorCalled);
            $this->assertFalse($moduleMock1->bootstrapUserSettingsCalled);

            Console::overrideIsConsole(false);
            $moduleMock2->onBootstrap($this->event);
            $this->assertTrue($moduleMock2->bootstrapLateListenersCalled);
            $this->assertTrue($moduleMock2->bootstrapSessionCalled);
            $this->assertTrue($moduleMock2->bootstrapTranslatorCalled);
            $this->assertTrue($moduleMock2->bootstrapUserSettingsCalled);
        } finally {
            Console::overrideIsConsole($usedConsoleBackup);
        }
    }

    public function testBootstrapLateListeners()
    {
        $eventManager = $this->event->getApplication()->getEventManager();

        $setupAwareRedirectStrategy = $this->prophesize(SetupAwareRedirectStrategy::class);
        $setupAwareRedirectStrategy->attach($eventManager)->shouldBeCalledTimes(1);
        $this->serviceManager->setService(SetupAwareRedirectStrategy::class, $setupAwareRedirectStrategy->reveal());

        $rbacListener = $this->prophesize(RbacListener::class);
        $rbacListener->attach($eventManager)->shouldBeCalledTimes(1);
        $this->serviceManager->setService(RbacListener::class, $rbacListener->reveal());

        $bootstrapLateListenersMethod = $this->moduleReflection->getMethod('bootstrapLateListeners');
        $bootstrapLateListenersMethod->setAccessible(true);
        $bootstrapLateListenersMethod->invokeArgs($this->module, [
            $this->event,
        ]);
    }

    public function testBootstrapSessionThrowsExceptionIsInitedAndReactsToAuthenticateSuccessEvent()
    {
        $sessionStartExceptionThrown = false;
        $sessionUnsetCalledTimes = 0;

        $container = new Container('initialized');
        $container->exchangeArray([
            'init' => 1,
        ]);

        $sessionManager = $this->prophesize(SessionManager::class);
        $sessionManager->start()->will(function () use (&$sessionStartExceptionThrown) {
            if (! $sessionStartExceptionThrown) {
                $sessionStartExceptionThrown = true;
                throw new SessionRuntimeException();
            }
            return true;
        })->shouldBeCalledTimes(2);
        $sessionManager->regenerateId(true)->shouldNotBeCalled();
        $sessionManager->getValidatorChain()->shouldNotBeCalled();

        $builder = new MockBuilder();
        $builder->setNamespace($this->moduleReflection->getNamespaceName())
            ->setName('session_unset')
            ->setFunction(
                function () use (&$sessionUnsetCalledTimes) {
                    $sessionUnsetCalledTimes++;
                    return true;
                }
            );
        $mockSessionUnset = $builder->build();

        $adapterChain = new AdapterChain();

        $this->serviceManager->setService('ZfcUser\Authentication\Adapter\AdapterChain', $adapterChain);
        $this->serviceManager->setService(SessionManager::class, $sessionManager->reveal());

        try {
            $mockSessionUnset->enable();

            $bootstrapSession = $this->moduleReflection->getMethod('bootstrapSession');
            $bootstrapSession->setAccessible(true);
            $bootstrapSession->invokeArgs($this->module, [
                $this->event,
            ]);
        } finally {
            $mockSessionUnset->disable();
        }

        $this->assertTrue($sessionStartExceptionThrown);
        $this->assertEquals(1, $sessionUnsetCalledTimes);

        $storage = $this->prophesize(SessionArrayStorage::class);
        $storage->clear('userSettings')->shouldBeCalledTimes(1);

        $container = new Container();
        $usedStorage = $container->getManager()->getStorage();
        try {
            $container->getManager()->setStorage($storage->reveal());
            $adapterChain->getEventManager()->trigger('authenticate.success', $adapterChain->getEvent());
        } finally {
            $container->getManager()->setStorage($usedStorage);
        }
    }

    public function testBootstrapSessionThrowsExceptionNotInitedNoConfigSet()
    {
        $remoteAddr = '127.0.0.1';
        $httpUserAgent = 'Mozilla/4.5 [en] (X11; U; Linux 2.2.9 i586)';
        $expectedContainer = [
            'init' => 1,
            'remoteAddr' => $remoteAddr,
            'httpUserAgent' => $httpUserAgent,
        ];

        $container = new Container('initialized');
        $container->exchangeArray([]);

        $sessionManager = $this->prophesize(SessionManager::class);
        $sessionManager->start()->shouldBeCalledTimes(1);
        $sessionManager->regenerateId(true)->shouldBeCalledTimes(1);
        $sessionManager->getValidatorChain()->shouldNotBeCalled();

        $request = new Request();
        $request->getServer()->set('REMOTE_ADDR', $remoteAddr);
        $request->getServer()->set('HTTP_USER_AGENT', $httpUserAgent);

        $this->serviceManager->setService('ZfcUser\Authentication\Adapter\AdapterChain', new AdapterChain());
        $this->serviceManager->setService('Request', $request);
        $this->serviceManager->setService(SessionManager::class, $sessionManager->reveal());

        $bootstrapSession = $this->moduleReflection->getMethod('bootstrapSession');
        $bootstrapSession->setAccessible(true);
        $bootstrapSession->invokeArgs($this->module, [
            $this->event,
        ]);

        $this->assertEquals($expectedContainer, $container->getArrayCopy());
    }

    public function testBootstrapSessionConfigSetAttachingValidators()
    {
        $config = [
            'session' => [
                'validators' => [
                    RemoteAddr::class,
                    HttpUserAgent::class,
                    Id::class,
                    'AnInvalidClassName',
                ],
            ],
        ];

        $container = new Container('initialized');
        $container->exchangeArray([]);

        $validatorChain = $this->prophesize(ValidatorChain::class);
        $validatorChain->attach('session.validate', Argument::type('array'))->shouldBeCalledTimes(3);

        $sessionManager = $this->prophesize(SessionManager::class);
        $sessionManager->start()->shouldBeCalledTimes(1);
        $sessionManager->regenerateId(true)->shouldBeCalledTimes(1);
        $sessionManager->getValidatorChain()->willReturn($validatorChain->reveal())->shouldBeCalledTimes(1);

        $this->serviceManager->setService('config', $config);
        $this->serviceManager->setService('ZfcUser\Authentication\Adapter\AdapterChain', new AdapterChain());
        $this->serviceManager->setService('Request', new Request());
        $this->serviceManager->setService(SessionManager::class, $sessionManager->reveal());


        $bootstrapSession = $this->moduleReflection->getMethod('bootstrapSession');
        $bootstrapSession->setAccessible(true);
        $bootstrapSession->invokeArgs($this->module, [
            $this->event,
        ]);
    }

    public function testBootstrapTranslatorServiceManagerThrowsException()
    {
        $config = [
            'settings' => [
                'translator_cache' => 'translatorCache',
            ],
        ];

        $i18ntranslator = $this->prophesize(I18nTranslator::class);
        $i18ntranslator->getLocale()->willReturn('en_US')->shouldBeCalledTimes(1);
        $i18ntranslator->setLocale(Argument::type('string'))->shouldNotBeCalled();
        $i18ntranslator->setFallbackLocale('en')->shouldBeCalledTimes(1);
        $i18ntranslator->setCache(Argument::type(AbstractAdapter::class))->shouldNotBeCalled();

        $this->serviceManager->setService('config', $config);
        $this->serviceManager->setAlias('MvcTranslator', Translator::class);
        $this->serviceManager->setService(Translator::class, new Translator($i18ntranslator->reveal()));
        $this->serviceManager->setFactory('translatorCache', stdClass::class);

        $bootstrapTranslator = $this->moduleReflection->getMethod('bootstrapTranslator');
        $bootstrapTranslator->setAccessible(true);
        $bootstrapTranslator->invokeArgs($this->module, [
            $this->event,
        ]);

        $this->assertSame($i18ntranslator->reveal(), AbstractValidator::getDefaultTranslator()->getTranslator());
    }

    public function testBootstrapTranslatorServiceManagerReturnsInvalidCache()
    {
        $config = [
            'settings' => [
                'translator_cache' => 'translatorCache',
            ],
        ];

        $i18ntranslator = $this->prophesize(I18nTranslator::class);
        $i18ntranslator->getLocale()->willReturn('en_US')->shouldBeCalledTimes(1);
        $i18ntranslator->setLocale(Argument::type('string'))->shouldNotBeCalled();
        $i18ntranslator->setFallbackLocale('en')->shouldBeCalledTimes(1);
        $i18ntranslator->setCache(Argument::type(AbstractAdapter::class))->shouldNotBeCalled();

        $this->serviceManager->setService('config', $config);
        $this->serviceManager->setAlias('MvcTranslator', Translator::class);
        $this->serviceManager->setService(Translator::class, new Translator($i18ntranslator->reveal()));
        $this->serviceManager->setService('translatorCache', stdClass::class);

        $bootstrapTranslator = $this->moduleReflection->getMethod('bootstrapTranslator');
        $bootstrapTranslator->setAccessible(true);
        $bootstrapTranslator->invokeArgs($this->module, [
            $this->event,
        ]);

        $this->assertSame($i18ntranslator->reveal(), AbstractValidator::getDefaultTranslator()->getTranslator());
    }

    public function testBootstrapTranslatorServiceManagerSetsLocaleAndCache()
    {
        $config = [
            'settings' => [
                'translator_cache' => 'translatorCache',
            ],
        ];
        $locale = 'de_DE';
        $fallbackLocale = 'de';

        $translatorCache = $this->prophesize(AbstractAdapter::class);

        $i18ntranslator = $this->prophesize(I18nTranslator::class);
        $i18ntranslator->getLocale()->willReturn('en_US')->shouldBeCalledTimes(1);
        $i18ntranslator->setLocale($locale)->shouldBeCalledTimes(1)->will(function () use ($locale) {
            $this->getLocale()->willReturn($locale);
        });
        $i18ntranslator->setFallbackLocale($fallbackLocale)->shouldBeCalledTimes(1);
        $i18ntranslator->setCache($translatorCache->reveal())->shouldBeCalledTimes(1);

        $this->serviceManager->setService('config', $config);
        $this->serviceManager->setAlias('MvcTranslator', Translator::class);
        $this->serviceManager->setService(Translator::class, new Translator($i18ntranslator->reveal()));
        $this->serviceManager->setService('translatorCache', $translatorCache->reveal());

        $userSettings = new Container('userSettings');
        $userSettings->locale = $locale;

        $userSettingsProperty = $this->moduleReflection->getProperty('userSettings');
        $userSettingsProperty->setAccessible(true);
        $userSettingsProperty->setValue($this->module, $userSettings);

        $bootstrapTranslator = $this->moduleReflection->getMethod('bootstrapTranslator');
        $bootstrapTranslator->setAccessible(true);
        $bootstrapTranslator->invokeArgs($this->module, [
            $this->event,
        ]);

        $this->assertSame($i18ntranslator->reveal(), AbstractValidator::getDefaultTranslator()->getTranslator());
    }

    public function testBootstrapUserSettings()
    {
        $userId = 12;

        $userSettings = [
            'user_id' => $userId,
            'locale' => 'de_DE',
        ];

        $user = $this->prophesize(UserInterface::class);
        $user->getId()->willReturn($userId)->shouldBeCalledTimes(1);

        $authenticationService = $this->prophesize(AuthenticationService::class);
        $authenticationService->hasIdentity()
            ->willReturn(true)
            ->shouldBeCalledTimes(2);
        $authenticationService->getIdentity()
            ->willReturn($user->reveal())
            ->shouldBeCalledTimes(1);

        $userSettingsTable = $this->prophesize(UserSettingsTable::class);
        $userSettingsTable->getUserSettings($userId)
            ->willReturn(new UserSettings($userSettings))
            ->shouldBeCalledTimes(1);

        $this->serviceManager->setService('zfcuser_auth_service', $authenticationService->reveal());
        $this->serviceManager->setService(UserSettingsTable::class, $userSettingsTable->reveal());

        $userSettingsProperty = $this->moduleReflection->getProperty('userSettings');
        $userSettingsProperty->setAccessible(true);
        $this->assertNull($userSettingsProperty->getValue($this->module));

        $bootstrapUserSettings = $this->moduleReflection->getMethod('bootstrapUserSettings');
        $bootstrapUserSettings->setAccessible(true);

        // 1st call
        $bootstrapUserSettings->invokeArgs($this->module, [
            $this->event,
        ]);

        $userSettingsContainer = $userSettingsProperty->getValue($this->module);
        $this->assertInstanceOf(Container::class, $userSettingsContainer);
        $this->assertEquals(array_merge([
            'init' => 1
        ], $userSettings), $userSettingsContainer->getArrayCopy());

        // 2nd call
        $bootstrapUserSettings->invokeArgs($this->module, [
            $this->event,
        ]);
    }

    public function testBootstrapUserSettingsUserSettingsTableThrowsException()
    {
        $userId = 12;

        $user = $this->prophesize(UserInterface::class);
        $user->getId()->willReturn($userId)->shouldBeCalledTimes(1);

        $authenticationService = $this->prophesize(AuthenticationService::class);
        $authenticationService->hasIdentity()
            ->willReturn(true)
            ->shouldBeCalledTimes(1);
        $authenticationService->getIdentity()
            ->willReturn($user->reveal())
            ->shouldBeCalledTimes(1);

        $userSettingsTable = $this->prophesize(UserSettingsTable::class);
        $userSettingsTable->getUserSettings($userId)
            ->willThrow(new RuntimeException())
            ->shouldBeCalledTimes(1);

        $this->serviceManager->setService('zfcuser_auth_service', $authenticationService->reveal());
        $this->serviceManager->setService(UserSettingsTable::class, $userSettingsTable->reveal());

        $userSettingsProperty = $this->moduleReflection->getProperty('userSettings');
        $userSettingsProperty->setAccessible(true);
        $this->assertNull($userSettingsProperty->getValue($this->module));

        $userSettings = new Container('userSettings');
        $userSettings->exchangeArray([]);

        $bootstrapUserSettings = $this->moduleReflection->getMethod('bootstrapUserSettings');
        $bootstrapUserSettings->setAccessible(true);
        $bootstrapUserSettings->invokeArgs($this->module, [
            $this->event,
        ]);

        $this->assertNull($userSettingsProperty->getValue($this->module));
    }
}
