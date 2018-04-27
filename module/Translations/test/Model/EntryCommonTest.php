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
use Translations\Model\EntryCommon;
use Zend\InputFilter\InputFilterInterface;
use DomainException;
use ReflectionClass;

class EntryCommonTest extends TestCase
{
    private $exampleArrayData = [
        'id' => '42',
        'app_resource_id' => 12,
        'resource_file_entry_id' => 11,
        'last_change' => 12345654,
        'notification_status' => 1,
    ];

    public function testConstructor()
    {
        $entryCommon = new EntryCommon();

        $this->assertNull($entryCommon->getId());
        $this->assertNull($entryCommon->getAppResourceId());
        $this->assertNull($entryCommon->getResourceFileEntryId());
        $this->assertNull($entryCommon->getLastChange());
        $this->assertNull($entryCommon->getNotificationStatus());

        $entryCommon = new EntryCommon($this->exampleArrayData);

        $this->assertEquals(42, $entryCommon->getId());
        $this->assertEquals(12, $entryCommon->getAppResourceId());
        $this->assertEquals(11, $entryCommon->getResourceFileEntryId());
        $this->assertEquals(12345654, $entryCommon->getLastChange());
        $this->assertEquals(1, $entryCommon->getNotificationStatus());
    }

    public function testSetterAndGetters()
    {
        $entryCommon = new EntryCommon();
        $entryCommon->setId(42);
        $entryCommon->setAppResourceId(12);
        $entryCommon->setResourceFileEntryId(11);
        $entryCommon->setLastChange(12345654);
        $entryCommon->setNotificationStatus(1);

        $this->assertEquals(42, $entryCommon->getId());
        $this->assertEquals(12, $entryCommon->getAppResourceId());
        $this->assertEquals(11, $entryCommon->getResourceFileEntryId());
        $this->assertEquals(12345654, $entryCommon->getLastChange());
        $this->assertEquals(1, $entryCommon->getNotificationStatus());
    }

    public function testGetInputFilter()
    {
        $entryCommon = new EntryCommon();

        $reflection = new ReflectionClass(EntryCommon::class);
        $inputFilterProperty = $reflection->getProperty('inputFilter');
        $inputFilterProperty->setAccessible(true);

        $this->assertNull($inputFilterProperty->getValue($entryCommon));

        $inputFilter = $entryCommon->getInputFilter();
        $this->assertInstanceOf(InputFilterInterface::class, $inputFilter);
        $this->assertSame($inputFilterProperty->getValue($entryCommon), $inputFilter);
        $this->assertSame($inputFilter, $entryCommon->getInputFilter());
    }

    public function testSetInputFilter()
    {
        $entryCommon = new EntryCommon();
        $inputFilter = $this->prophesize(InputFilterInterface::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageRegExp('/\w+ does not allow injection of an alternate input filter./');

        $entryCommon->setInputFilter($inputFilter->reveal());
    }

    public function testArraySerializableImplementations()
    {
        $entryCommon = new EntryCommon();
        $entryCommon->exchangeArray($this->exampleArrayData);

        $this->assertEquals($this->exampleArrayData, $entryCommon->getArrayCopy());
    }
}
