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

namespace TranslationsTest\Factory\Controller;

use PHPUnit\Framework\TestCase;
use Translations\Controller\AppResourceFileController;
use Translations\Factory\Controller\AppResourceFileControllerFactory;
use Translations\Model\AppResourceFileTable;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class AppResourceFileControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new AppResourceFileControllerFactory();

        $serviceManager = new ServiceManager();

        $appResourceFileTable = $this->prophesize(AppResourceFileTable::class);
        $serviceManager->setService(AppResourceFileTable::class, $appResourceFileTable->reveal());

        $appTable = $this->prophesize(AppTable::class);
        $serviceManager->setService(AppTable::class, $appTable->reveal());

        $appResourceTable = $this->prophesize(AppResourceTable::class);
        $serviceManager->setService(AppResourceTable::class, $appResourceTable->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $adapter = $this->prophesize(Adapter::class);
        $serviceManager->setService(AdapterInterface::class, $adapter->reveal());

        $appResourceFileController = $factory($serviceManager, null);
        $this->assertInstanceOf(AppResourceFileController::class, $appResourceFileController);
    }
}
