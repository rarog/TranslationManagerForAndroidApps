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

namespace TranslationsTest\Settings;

use Common\Model\Setting;
use Common\Model\SettingTable;
use PHPUnit\Framework\TestCase;
use Translations\Settings\TranslationSettings;
use Zend\InputFilter\InputFilterInterface;
use ReflectionClass;

class TranslationSettingsTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $settingTable;

    /**
     * @var TranslationSettings
     */
    private $translationSettings;

    /**
     * @param string $name
     * @return \ReflectionProperty
     */
    private function getTranslationSettingsProperty(string $name)
    {
        $reflection = new ReflectionClass(TranslationSettings::class);

        $property = $reflection->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->settingTable = $this->prophesize(SettingTable::class);

        $this->translationSettings = new TranslationSettings(
            $this->settingTable->reveal()
        );
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->translationSettings);
        unset($this->settingTable);
    }

    /**
     * @covers Translations\Settings\TranslationSettings::getMarkApprovedTranslationsGreen
     */
    public function testGetMarkApprovedTranslationsGreenReturnsFalseOnNullProperty()
    {
        $property = $this->getTranslationSettingsProperty('markApprovedTranslationsGreen');
        $this->assertNull($property->getValue($this->translationSettings));
        $this->assertEquals(false, $this->translationSettings->getMarkApprovedTranslationsGreen());
    }

    /**
     * @covers Translations\Settings\TranslationSettings::getMarkApprovedTranslationsGreen
     */
    public function testGetMarkApprovedTranslationsGreenReturnsExpectedBoolean()
    {
        $falseValue = '0';
        $trueValue = '1';

        $setting = new Setting();

        $property = $this->getTranslationSettingsProperty('markApprovedTranslationsGreen');
        $property->setValue($this->translationSettings, $setting);

        $this->assertNull($setting->getValue());
        $this->assertEquals(false, $this->translationSettings->getMarkApprovedTranslationsGreen());

        $setting->setValue($falseValue);
        $this->assertEquals($falseValue, $setting->getValue());
        $this->assertEquals(false, $this->translationSettings->getMarkApprovedTranslationsGreen());

        $setting->setValue($trueValue);
        $this->assertEquals($trueValue, $setting->getValue());
        $this->assertEquals(true, $this->translationSettings->getMarkApprovedTranslationsGreen());
    }

    /**
     * @covers Translations\Settings\TranslationSettings::setMarkApprovedTranslationsGreen
     */
    public function testSetMarkApprovedTranslationsGreenCreatesNewSettingReturnsItself()
    {
        $property = $this->getTranslationSettingsProperty('markApprovedTranslationsGreen');
        $this->assertNull($property->getValue($this->translationSettings));

        $this->assertSame(
            $this->translationSettings,
            $this->translationSettings->setMarkApprovedTranslationsGreen(false)
        );
        $setting = $property->getValue($this->translationSettings);
        $this->assertInstanceOf(Setting::class, $setting);

        $this->assertSame(
            $this->translationSettings,
            $this->translationSettings->setMarkApprovedTranslationsGreen(true)
        );
        $this->assertSame($setting, $property->getValue($this->translationSettings));
    }

    /**
     * @covers Translations\Settings\TranslationSettings::setMarkApprovedTranslationsGreen
     */
    public function testSetMarkApprovedTranslationsGreenUsesExistingSettingSetsFalse()
    {
        $setting = $this->prophesize(Setting::class);
        $setting->setValue('0')->shouldBeCalledTimes(1);
        $setting->setValue('1')->shouldNotBeCalled();

        $property = $this->getTranslationSettingsProperty('markApprovedTranslationsGreen');
        $property->setValue($this->translationSettings, $setting->reveal());

        $this->translationSettings->setMarkApprovedTranslationsGreen(false);
    }

    /**
     * @covers Translations\Settings\TranslationSettings::setMarkApprovedTranslationsGreen
     */
    public function testSetMarkApprovedTranslationsGreenUsesExistingSettingSetsTrue()
    {
        $setting = $this->prophesize(Setting::class);
        $setting->setValue('0')->shouldNotBeCalled();
        $setting->setValue('1')->shouldBeCalledTimes(1);

        $property = $this->getTranslationSettingsProperty('markApprovedTranslationsGreen');
        $property->setValue($this->translationSettings, $setting->reveal());

        $this->translationSettings->setMarkApprovedTranslationsGreen(true);
    }

    /**
     * @covers Translations\Settings\TranslationSettings::exchangeArray
     * @covers Translations\Settings\TranslationSettings::getArrayCopy
     */
    public function testArraySerializableInterface()
    {
        $emptyArray = [
            'mark_approved_translations_green' => false,
        ];
        $newArray = [
            'mark_approved_translations_green' => true,
        ];

        $property = $this->getTranslationSettingsProperty('markApprovedTranslationsGreen');
        $this->assertNull($property->getValue($this->translationSettings));
        $this->assertEquals($emptyArray, $this->translationSettings->getArrayCopy());

        $this->translationSettings->exchangeArray($newArray);
        $this->assertEquals($newArray, $this->translationSettings->getArrayCopy());
    }

    /**
     * @covers Translations\Settings\TranslationSettings::getInputFilter
     */
    public function testGetInputFilter()
    {
        $property = $this->getTranslationSettingsProperty('inputFilter');

        $this->assertNull($property->getValue($this->translationSettings));

        $inputFilter = $this->translationSettings->getInputFilter();
        $this->assertInstanceOf(InputFilterInterface::class, $inputFilter);
        $this->assertSame($property->getValue($this->translationSettings), $inputFilter);
        $this->assertSame($inputFilter, $this->translationSettings->getInputFilter());
    }

    /**
     * @covers Translations\Settings\TranslationSettings::load
     */
    public function testLoad()
    {
        $property = $this->getTranslationSettingsProperty('loaded');

        $this->assertEquals(false, $property->getValue($this->translationSettings));

        $this->settingTable
            ->getSettingByPath(TranslationSettings::PATH_MARKAPPROVEDTRANSLATIONSGREEN)
            ->willReturn('1')
            ->shouldBeCalledTimes(1);

        $this->translationSettings->load();
        $this->assertEquals(true, $property->getValue($this->translationSettings));

        $this->translationSettings->load();
    }
}
