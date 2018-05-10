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
use Application\Model\UserSettingsTable;
use Application\Model\UserTable;
use Application\View\Strategy\SetupAwareRedirectStrategy;
use PHPUnit\Framework\TestCase;
use Zend\Console\Console;
use Zend\Db\Adapter\AdapterInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Exception\RuntimeException as SessionRuntimeException;
use Zend\Session\SaveHandler\Cache;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;
use ZfcUser\Authentication\Adapter\AdapterChain;
use ZfcUser\Mapper\User as UserMapper;
use ReflectionClass;
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
}
