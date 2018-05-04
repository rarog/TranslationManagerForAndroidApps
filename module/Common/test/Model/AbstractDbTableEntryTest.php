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

    protected function setUp()
    {
        $this->abstractDbTableEntry = new class extends AbstractDbTableEntry {
            private $someProperty;

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

            }

            public function getArrayCopy()
            {

            }
        };
    }

    protected function tearDown()
    {
        unset($this->abstractDbTableEntry);
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
