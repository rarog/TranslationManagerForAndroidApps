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

namespace TranslationsTest\Controller;

use PHPUnit\Framework\TestCase;
use Translations\Controller\GitController;
use Translations\Factory\Controller\GitControllerFactory;
use Translations\Model\AppTable;
use Translations\Model\Helper\EncryptionHelper;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class GitControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new GitControllerFactory();

        $serviceManager = new ServiceManager();

        $appTable = $this->prophesize(AppTable::class);
        $serviceManager->setService(AppTable::class, $appTable->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $encryptionHelper = $this->prophesize(EncryptionHelper::class);
        $serviceManager->setService(EncryptionHelper::class, $encryptionHelper->reveal());

        $gitController = $factory($serviceManager, null);
        $this->assertInstanceOf(GitController::class, $gitController);
    }
}
