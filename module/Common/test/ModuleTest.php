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

namespace CommonTest;

use Common\Module;
use Common\Model\SettingTable;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ServiceManager\ServiceManager;
use ReflectionClass;

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
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->module = new Module();
        $this->moduleReflection = new ReflectionClass(Module::class);
        $this->serviceManager = new ServiceManager();
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->serviceManager);
        unset($this->moduleReflection);
        unset($this->module);
    }

    /**
     * @covers Common\Module::getServiceConfig
     */
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

        $this->assertTrue($this->serviceManager->has(SettingTable::class));
        $this->assertInstanceOf(SettingTable::class, $this->serviceManager->get(SettingTable::class));
    }
}
