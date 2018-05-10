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
use PHPUnit\Framework\TestCase;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use ReflectionClass;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Console\Console;
use Zend\EventManager\Event;
use Zend\EventManager\EventInterface;

class ModuleTest extends TestCase
{
    private $module;

    private $moduleReflection;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->module = new Module();
        $this->moduleReflection = new ReflectionClass(Module::class);
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
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
        $this->assertInternalType('array', $this->module->getServiceConfig());
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
        $event = new Event();

        $usedConsoleBackup = Console::isConsole();
        try {
            Console::overrideIsConsole(true);
            $moduleMock1->onBootstrap($event);
            $this->assertFalse($moduleMock1->bootstrapLateListenersCalled);
            $this->assertFalse($moduleMock1->bootstrapSessionCalled);
            $this->assertFalse($moduleMock1->bootstrapTranslatorCalled);
            $this->assertFalse($moduleMock1->bootstrapUserSettingsCalled);

            Console::overrideIsConsole(false);
            $moduleMock2->onBootstrap($event);
            $this->assertTrue($moduleMock2->bootstrapLateListenersCalled);
            $this->assertTrue($moduleMock2->bootstrapSessionCalled);
            $this->assertTrue($moduleMock2->bootstrapTranslatorCalled);
            $this->assertTrue($moduleMock2->bootstrapUserSettingsCalled);
        } finally {
            Console::overrideIsConsole($usedConsoleBackup);
        }
    }
}
