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
use Translations\Model\Suggestion;
use Zend\InputFilter\InputFilterInterface;
use ReflectionClass;

class SuggestionTest extends TestCase
{
    private $exampleArrayData = [
        'id' => 42,
        'entry_common_id' => 12,
        'user_id' => 11,
        'last_change' => 12345654,
    ];

    public function testConstructor()
    {
        $suggestion = new Suggestion();

        $this->assertNull($suggestion->getId());
        $this->assertNull($suggestion->getEntryCommonId());
        $this->assertNull($suggestion->getUserId());
        $this->assertNull($suggestion->getLastChange());

        $suggestion = new Suggestion($this->exampleArrayData);

        $this->assertEquals(
            $this->exampleArrayData['id'],
            $suggestion->getId()
        );
        $this->assertEquals(
            $this->exampleArrayData['entry_common_id'],
            $suggestion->getEntryCommonId()
        );
        $this->assertEquals(
            $this->exampleArrayData['user_id'],
            $suggestion->getUserId()
        );
        $this->assertEquals(
            $this->exampleArrayData['last_change'],
            $suggestion->getLastChange()
        );
    }

    public function testSetterAndGetters()
    {
        $suggestion = new Suggestion();
        $suggestion->setId($this->exampleArrayData['id']);
        $suggestion->setEntryCommonId($this->exampleArrayData['entry_common_id']);
        $suggestion->setUserId($this->exampleArrayData['user_id']);
        $suggestion->setLastChange($this->exampleArrayData['last_change']);

        $this->assertEquals(
            $this->exampleArrayData['id'],
            $suggestion->getId()
        );
        $this->assertEquals(
            $this->exampleArrayData['entry_common_id'],
            $suggestion->getEntryCommonId()
        );
        $this->assertEquals(
            $this->exampleArrayData['user_id'],
            $suggestion->getUserId()
        );
        $this->assertEquals(
            $this->exampleArrayData['last_change'],
            $suggestion->getLastChange()
        );
    }

    public function testGetInputFilter()
    {
        $suggestion = new Suggestion();

        $reflection = new ReflectionClass(Suggestion::class);
        $inputFilterProperty = $reflection->getProperty('inputFilter');
        $inputFilterProperty->setAccessible(true);

        $this->assertNull($inputFilterProperty->getValue($suggestion));

        $inputFilter = $suggestion->getInputFilter();
        $this->assertInstanceOf(InputFilterInterface::class, $inputFilter);
        $this->assertSame($inputFilterProperty->getValue($suggestion), $inputFilter);
        $this->assertSame($inputFilter, $suggestion->getInputFilter());
    }

    public function testArraySerializableImplementations()
    {
        $suggestion = new Suggestion();
        $suggestion->exchangeArray($this->exampleArrayData);

        $this->assertEquals($this->exampleArrayData, $suggestion->getArrayCopy());
    }
}
