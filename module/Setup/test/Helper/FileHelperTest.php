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
use Prophecy\Argument;
use Setup\Helper\FileHelper;
use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;
use ReflectionClass;

class FileHelperTest extends TestCase
{

    private $fileHelper;

    private $phpArray;

    private $phpArrayToFileConfigParam;

    private $configWriterProperty;

    private $getConfigWriterMethod;

    protected function setUp()
    {
        $this->fileHelper = new FileHelper();

        $phpArrayToFileConfigParam = null;
        $this->phpArrayToFileConfigParam = &$phpArrayToFileConfigParam;
        $this->phpArray = $this->prophesize(PhpArray::class);
        $this->phpArray->toFile(Argument::type('string'), Argument::type(Config::class), Argument::cetera())->will(
            function ($args) use (&$phpArrayToFileConfigParam) {
                $phpArrayToFileConfigParam = $args[1]->toArray(); // print_r($phpArrayToFileConfigParam);
            });

        $reflection = new ReflectionClass(FileHelper::class);

        $this->configWriterProperty = $reflection->getProperty('configWriter');
        $this->configWriterProperty->setAccessible(true);

        $this->getConfigWriterMethod = $reflection->getMethod('getConfigWriter');
        $this->getConfigWriterMethod->setAccessible(true);
    }

    public function testGetConfigWriter()
    {
        $this->assertNull($this->configWriterProperty->getValue($this->fileHelper));

        $this->assertInstanceOf(PhpArray::class, $this->getConfigWriterMethod->invokeArgs($this->fileHelper, []));
        $this->assertInstanceOf(PhpArray::class, $this->configWriterProperty->getValue($this->fileHelper));
    }

    public function testReplaceConfigInFile()
    {
        $this->configWriterProperty->setValue($this->fileHelper, $this->phpArray->reveal());

        $expectedConfig = [
            'newKey' => 'newValue',
            'someKey1' => 'someValue',
        ];

        $this->fileHelper->replaceConfigInFile(__DIR__ . '/nonexistingconfig.php', $expectedConfig);
        $this->assertEquals($expectedConfig, $this->phpArrayToFileConfigParam);
    }

    public function testReplaceConfigInFile2()
    {
        $this->configWriterProperty->setValue($this->fileHelper, $this->phpArray->reveal());

        $newConfig = [
            'newKey' => 'newValue',
            'someKey1' => 'changedValue',
        ];

        $expectedConfig = [
            'aKey' => 'aValue',
            'newKey' => 'newValue',
            'someKey1' => 'changedValue',
            'someKey2' => 'anotherValue',
        ];

        $this->fileHelper->replaceConfigInFile(__DIR__ . '/exampleconfig.php', $expectedConfig);
        $this->assertEquals($expectedConfig, $this->phpArrayToFileConfigParam);
    }
}
