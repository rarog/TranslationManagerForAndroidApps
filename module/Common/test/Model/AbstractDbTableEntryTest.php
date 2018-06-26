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
use RuntimeException;

class AbstractDbTableEntryTest extends TestCase
{
    /**
     * @var AbstractDbTableEntry
     */
    private $abstractDbTableEntry;

    /**
     * @var string
     */
    private $testValue = 'someValue';

    /**
     * @param array $data
     * @return anonymous class instance
     */
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

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->abstractDbTableEntry = $this->getNewAbstractDbTableEntry();
    }

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        unset($this->abstractDbTableEntry);
    }

    /**
     * @covers Common\Model\AbstractDbTableEntry::__construct
     */
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

    /**
     * @covers Common\Model\AbstractDbTableEntry::setInputFilter
     */
    public function testSetInputFilter()
    {
        $inputFilter = $this->prophesize(InputFilterInterface::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageRegExp('/\w+ does not allow injection of an alternate input filter./');

        $this->abstractDbTableEntry->setInputFilter($inputFilter->reveal());
        $this->abstractDbTableEntry->exchangeArray([]);
    }

    /**
     * @covers Common\Model\AbstractDbTableEntry::__get
     * @covers Common\Model\AbstractDbTableEntry::__set
     */
    public function testGetSetMagic()
    {
        $this->abstractDbTableEntry->SomeProperty = $this->testValue;
        $this->assertEquals($this->testValue, $this->abstractDbTableEntry->SomeProperty);
    }

    /**
     * @covers Common\Model\AbstractDbTableEntry::__get
     */
    public function testGetMagicException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid property');

        $gettingValue = $this->abstractDbTableEntry->NonexistantProperty;
    }

    /**
     * @covers Common\Model\AbstractDbTableEntry::__set
     */
    public function testSetMagicException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid property');

        $this->abstractDbTableEntry->NonexistantProperty = $this->testValue;
    }
}
