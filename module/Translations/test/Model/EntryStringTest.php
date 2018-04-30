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

namespace TranslationsTest\Model;

use PHPUnit\Framework\TestCase;
use Translations\Model\EntryString;
use Zend\InputFilter\InputFilterInterface;
use ReflectionClass;

class EntryStringTest extends TestCase
{
    private $exampleArrayData = [
        'entry_common_id' => 42,
        'value' => 'A string value',
    ];

    public function testConstructor()
    {
        $entryString = new EntryString();

        $this->assertNull($entryString->getEntryCommonId());
        $this->assertNull($entryString->getValue());

        $entryString = new EntryString($this->exampleArrayData);

        $this->assertEquals(
            $this->exampleArrayData['entry_common_id'],
            $entryString->getEntryCommonId()
        );
        $this->assertEquals(
            $this->exampleArrayData['value'],
            $entryString->getValue()
        );
    }

    public function testSetterAndGetters()
    {
        $entryString = new EntryString();
        $entryString->setEntryCommonId($this->exampleArrayData['entry_common_id']);
        $entryString->setValue($this->exampleArrayData['value']);

        $this->assertEquals(
            $this->exampleArrayData['entry_common_id'],
            $entryString->getEntryCommonId()
        );
        $this->assertEquals(
            $this->exampleArrayData['value'],
            $entryString->getValue()
        );
    }

    public function testGetInputFilter()
    {
        $entryString = new EntryString();

        $reflection = new ReflectionClass(EntryString::class);
        $inputFilterProperty = $reflection->getProperty('inputFilter');
        $inputFilterProperty->setAccessible(true);

        $this->assertNull($inputFilterProperty->getValue($entryString));

        $inputFilter = $entryString->getInputFilter();
        $this->assertInstanceOf(InputFilterInterface::class, $inputFilter);
        $this->assertSame($inputFilterProperty->getValue($entryString), $inputFilter);
        $this->assertSame($inputFilter, $entryString->getInputFilter());
    }

    public function testArraySerializableImplementations()
    {
        $entryString = new EntryString();
        $entryString->exchangeArray($this->exampleArrayData);

        $this->assertEquals($this->exampleArrayData, $entryString->getArrayCopy());
    }
}
