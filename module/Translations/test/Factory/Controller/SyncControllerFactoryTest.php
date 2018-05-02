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
namespace TranslationsTest\Controller\Plugin;

use PHPUnit\Framework\TestCase;
use Translations\Controller\SyncController;
use Translations\Factory\Controller\SyncControllerFactory;
use Translations\Model\AppTable;
use Translations\Parser\ResXmlParser;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;

class SyncControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new SyncControllerFactory();

        $serviceManager = new ServiceManager();

        $appTable = $this->prophesize(AppTable::class);
        $serviceManager->setService(AppTable::class, $appTable->reveal());

        $resXmlParser = $this->prophesize(ResXmlParser::class);
        $serviceManager->setService(ResXmlParser::class, $resXmlParser->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $phpRenderer = $this->prophesize(PhpRenderer::class);
        $serviceManager->setService(PhpRenderer::class, $phpRenderer->reveal());

        $syncController = $factory($serviceManager, null);
        $this->assertInstanceOf(SyncController::class, $syncController);
    }
}
