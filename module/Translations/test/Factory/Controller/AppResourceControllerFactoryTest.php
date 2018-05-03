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
use Translations\Controller\AppResourceController;
use Translations\Factory\Controller\AppResourceControllerFactory;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class AppResourceControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new AppResourceControllerFactory();

        $serviceManager = new ServiceManager();

        $appResourceTable = $this->prophesize(AppResourceTable::class);
        $serviceManager->setService(AppResourceTable::class, $appResourceTable->reveal());

        $appTable = $this->prophesize(AppTable::class);
        $serviceManager->setService(AppTable::class, $appTable->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $adapter = $this->prophesize(Adapter::class);
        $serviceManager->setService(AdapterInterface::class, $adapter->reveal());

        $appResourceController = $factory($serviceManager, null);
        $this->assertInstanceOf(AppResourceController::class, $appResourceController);
    }
}
