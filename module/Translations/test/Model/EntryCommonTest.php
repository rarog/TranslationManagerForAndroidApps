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
use ReflectionClass;

class EntryCommonTest extends TestCase
{
    private $exampleArrayData = [
        'id' => 42,
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

        $this->assertEquals(
            $this->exampleArrayData['id'],
            $entryCommon->getId()
        );
        $this->assertEquals(
            $this->exampleArrayData['app_resource_id'],
            $entryCommon->getAppResourceId()
        );
        $this->assertEquals(
            $this->exampleArrayData['resource_file_entry_id'],
            $entryCommon->getResourceFileEntryId()
        );
        $this->assertEquals(
            $this->exampleArrayData['last_change'],
            $entryCommon->getLastChange()
        );
        $this->assertEquals(
            $this->exampleArrayData['notification_status'],
            $entryCommon->getNotificationStatus()
        );
    }

    public function testSetterAndGetters()
    {
        $entryCommon = new EntryCommon();
        $entryCommon->setId($this->exampleArrayData['id']);
        $entryCommon->setAppResourceId($this->exampleArrayData['app_resource_id']);
        $entryCommon->setResourceFileEntryId($this->exampleArrayData['resource_file_entry_id']);
        $entryCommon->setLastChange($this->exampleArrayData['last_change']);
        $entryCommon->setNotificationStatus($this->exampleArrayData['notification_status']);

        $this->assertEquals(
            $this->exampleArrayData['id'],
            $entryCommon->getId()
        );
        $this->assertEquals(
            $this->exampleArrayData['app_resource_id'],
            $entryCommon->getAppResourceId()
        );
        $this->assertEquals(
            $this->exampleArrayData['resource_file_entry_id'],
            $entryCommon->getResourceFileEntryId()
        );
        $this->assertEquals(
            $this->exampleArrayData['last_change'],
            $entryCommon->getLastChange()
        );
        $this->assertEquals(
            $this->exampleArrayData['notification_status'],
            $entryCommon->getNotificationStatus()
        );
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

    public function testArraySerializableImplementations()
    {
        $entryCommon = new EntryCommon();
        $entryCommon->exchangeArray($this->exampleArrayData);

        $this->assertEquals($this->exampleArrayData, $entryCommon->getArrayCopy());
    }
}
