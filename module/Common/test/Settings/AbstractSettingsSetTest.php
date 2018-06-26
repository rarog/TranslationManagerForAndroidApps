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

namespace CommonTest\Settings;

use Common\Model\SettingTable;
use Common\Settings\AbstractSettingsSet;
use PHPUnit\Framework\TestCase;
use Zend\InputFilter\InputFilterInterface;
use DomainException;
use RuntimeException;
use ReflectionClass;

class AbstractSettingsSetTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $settingsTable;

    /**
     * @var AbstractSettingsSet
     */
    private $abstractSettingsSet;

    /**
     * @var string
     */
    private $testValue = 'someValue';

    /**
     * @param array $data
     * @return anonymous class instance
     */
    private function getNewAbstractSettingsSet(SettingTable $settingTable)
    {
        return new class($settingTable) extends AbstractSettingsSet {
            private $someProperty;
            public $exchangeArrayParameter;

            public function getSomeProperty()
            {
                return $this->someProperty;
            }

            public function setSomeProperty($someProperty)
            {
                $this->someProperty = $someProperty;
            }

            public function getInputFilter()
            {
            }

            public function exchangeArray(array $array)
            {
                $this->exchangeArrayParameter = $array;
            }

            public function getArrayCopy()
            {
            }

            public function load()
            {
            }

            public function save()
            {
            }
        };
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->settingsTable = $this->prophesize(SettingTable::class);

        $this->abstractSettingsSet = $this->getNewAbstractSettingsSet($this->settingsTable->reveal());
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->abstractSettingsSet);
    }

    /**
     * @covers Common\Settings\AbstractSettingsSet::__construct
     */
    public function testConstructor()
    {
        $this->assertNull($this->abstractSettingsSet->exchangeArrayParameter);

        $reflection = new ReflectionClass(AbstractSettingsSet::class);
        $settingsTableProperty = $reflection->getProperty('settingTable');
        $settingsTableProperty->setAccessible(true);
        $this->assertSame(
            $this->settingsTable->reveal(),
            $settingsTableProperty->getValue($this->abstractSettingsSet)
        );
    }

    /**
     * @covers Common\Settings\AbstractSettingsSet::setInputFilter
     */
    public function testSetInputFilter()
    {
        $inputFilter = $this->prophesize(InputFilterInterface::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageRegExp('/\w+ does not allow injection of an alternate input filter./');

        $this->abstractSettingsSet->setInputFilter($inputFilter->reveal());
        $this->abstractSettingsSet->exchangeArray([]);
    }

    /**
     * @covers Common\Settings\AbstractSettingsSet::__get
     * @covers Common\Settings\AbstractSettingsSet::__set
     */
    public function testGetSetMagic()
    {
        $this->abstractSettingsSet->SomeProperty = $this->testValue;
        $this->assertEquals($this->testValue, $this->abstractSettingsSet->SomeProperty);
    }

    /**
     * @covers Common\Settings\AbstractSettingsSet::__get
     */
    public function testGetMagicException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid property');

        $gettingValue = $this->abstractSettingsSet->NonexistantProperty;
    }

    /**
     * @covers Common\Settings\AbstractSettingsSet::__set
     */
    public function testSetMagicException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid property');

        $this->abstractSettingsSet->NonexistantProperty = $this->testValue;
    }
}
