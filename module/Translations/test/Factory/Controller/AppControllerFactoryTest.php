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

use Application\Model\UserSettingsTable;
use PHPUnit\Framework\TestCase;
use Translations\Controller\AppController;
use Translations\Factory\Controller\AppControllerFactory;
use Translations\Model\AppTable;
use Translations\Model\TeamTable;
use Translations\Model\Helper\EncryptionHelper;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class AppControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new AppControllerFactory();

        $serviceManager = new ServiceManager();

        $appTable = $this->prophesize(AppTable::class);
        $serviceManager->setService(AppTable::class, $appTable->reveal());

        $teamTable = $this->prophesize(TeamTable::class);
        $serviceManager->setService(TeamTable::class, $teamTable->reveal());

        $userSettingsTable = $this->prophesize(UserSettingsTable::class);
        $serviceManager->setService(UserSettingsTable::class, $userSettingsTable->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $encryptionHelper = $this->prophesize(EncryptionHelper::class);
        $serviceManager->setService(EncryptionHelper::class, $encryptionHelper->reveal());

        $appController = $factory($serviceManager, null);
        $this->assertInstanceOf(AppController::class, $appController);
    }
}
