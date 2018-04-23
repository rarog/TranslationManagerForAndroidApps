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
namespace SetupTest\Helper;

use PHPUnit\Framework\TestCase;
use Setup\Factory\Helper\DatabaseHelperFactory;
use Setup\Helper\DatabaseHelper;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use ZfcUser\Options\ModuleOptions as ZfcUserModuleOptions;

class DatabaseHelperFactoryTest extends TestCase
{

    public function testFactory()
    {
        $factory = new DatabaseHelperFactory();

        $setupConfig = [
            'db' => [
                'driver' => 'Pdo'
            ]
        ];

        $serviceManager = new ServiceManager();

        $serviceManager->setService('config', $setupConfig);
        $serviceManager->setService('zfcuser_module_options', new ZfcUserModuleOptions());

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService(Translator::class, $translator);

        $databaseHelper = $factory($serviceManager, null);
        $this->assertInstanceOf(DatabaseHelper::class, $databaseHelper);
    }
}
