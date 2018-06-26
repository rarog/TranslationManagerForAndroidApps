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

namespace CommonTest\Model;

use Common\Model\Setting;
use PHPUnit\Framework\TestCase;
use Zend\InputFilter\InputFilterInterface;
use ReflectionClass;

class SettingTest extends TestCase
{
    /**
     * @var array
     */
    private $exampleArrayData = [
        'id' => 42,
        'path' => 'some/path',
        'value' => 'A value',
    ];

    /**
     * @covers Common\Model\Setting::__construct
     */
    public function testConstructor()
    {
        $setting = new Setting();

        $this->assertNull($setting->getId());
        $this->assertNull($setting->getPath());
        $this->assertNull($setting->getValue());

        $setting = new Setting($this->exampleArrayData);

        $this->assertEquals(
            $this->exampleArrayData['id'],
            $setting->getId()
        );
        $this->assertEquals(
            $this->exampleArrayData['path'],
            $setting->getPath()
        );
        $this->assertEquals(
            $this->exampleArrayData['value'],
            $setting->getValue()
        );
    }

    /**
     * @covers Common\Model\Setting::getId
     * @covers Common\Model\Setting::setId
     * @covers Common\Model\Setting::getPath
     * @covers Common\Model\Setting::setPath
     * @covers Common\Model\Setting::getValue
     * @covers Common\Model\Setting::setValue
     */
    public function testSetterAndGetters()
    {
        $setting = new Setting();
        $setting->setId($this->exampleArrayData['id']);
        $setting->setPath($this->exampleArrayData['path']);
        $setting->setValue($this->exampleArrayData['value']);

        $this->assertEquals(
            $this->exampleArrayData['id'],
            $setting->getId()
        );
        $this->assertEquals(
            $this->exampleArrayData['path'],
            $setting->getPath()
        );
        $this->assertEquals(
            $this->exampleArrayData['value'],
            $setting->getValue()
        );
    }

    /**
     * @covers Common\Model\Setting::getInputFilter
     */
    public function testGetInputFilter()
    {
        $setting = new Setting();

        $reflection = new ReflectionClass(Setting::class);
        $inputFilterProperty = $reflection->getProperty('inputFilter');
        $inputFilterProperty->setAccessible(true);

        $this->assertNull($inputFilterProperty->getValue($setting));

        $inputFilter = $setting->getInputFilter();
        $this->assertInstanceOf(InputFilterInterface::class, $inputFilter);
        $this->assertSame($inputFilterProperty->getValue($setting), $inputFilter);
        $this->assertSame($inputFilter, $setting->getInputFilter());
    }

    /**
     * @covers Common\Model\Setting::exchangeArray
     * @covers Common\Model\Setting::getArrayCopy
     */
    public function testArraySerializableImplementations()
    {
        $setting = new Setting();
        $setting->exchangeArray($this->exampleArrayData);

        $this->assertEquals($this->exampleArrayData, $setting->getArrayCopy());
    }
}
