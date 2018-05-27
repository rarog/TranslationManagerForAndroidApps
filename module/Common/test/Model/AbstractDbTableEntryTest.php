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

use Common\Model\AbstractDbTableEntry;
use PHPUnit\Framework\TestCase;
use Zend\InputFilter\InputFilterInterface;
use DomainException;

class AbstractDbTableEntryTest extends TestCase
{
    private $abstractDbTableEntry;

    private $testValue = 'someValue';

    private function getNewAbstractDbTableEntry(array $data = null)
    {
        return new class($data) extends AbstractDbTableEntry {
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
        };
    }

    protected function setUp()
    {
        $this->abstractDbTableEntry = $this->getNewAbstractDbTableEntry();
    }

    protected function tearDown()
    {
        unset($this->abstractDbTableEntry);
    }

    public function testConstructor()
    {
        $abstractDbTableEntry1 = $this->getNewAbstractDbTableEntry();
        $this->assertNull($abstractDbTableEntry1->exchangeArrayParameter);

        $array1 = [
            'someProperty' => 'someValue',
        ];
        $abstractDbTableEntry2 = $this->getNewAbstractDbTableEntry($array1);
        $this->assertEquals($array1, $abstractDbTableEntry2->exchangeArrayParameter);

        $array2 = [
            'anotherProperty' => 'anotherValue',
        ];
        $abstractDbTableEntry3 = $this->getNewAbstractDbTableEntry($array2);
        $this->assertEquals($array2, $abstractDbTableEntry3->exchangeArrayParameter);
    }

    public function testSetInputFilter()
    {
        $inputFilter = $this->prophesize(InputFilterInterface::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageRegExp('/\w+ does not allow injection of an alternate input filter./');

        $this->abstractDbTableEntry->setInputFilter($inputFilter->reveal());
        $this->abstractDbTableEntry->exchangeArray([]);
    }

    public function testGetSetMagic()
    {
        $this->abstractDbTableEntry->SomeProperty = $this->testValue;
        $this->assertEquals($this->testValue, $this->abstractDbTableEntry->SomeProperty);
    }

    public function testGetMagicException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid property');

        $gettingValue = $this->abstractDbTableEntry->NonexistantProperty;
    }

    public function testSetMagicException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid property');

        $this->abstractDbTableEntry->NonexistantProperty = $this->testValue;
    }
}
