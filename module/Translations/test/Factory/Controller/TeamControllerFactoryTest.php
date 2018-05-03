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
use Translations\Controller\TeamController;
use Translations\Factory\Controller\TeamControllerFactory;
use Translations\Model\TeamTable;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class TeamControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new TeamControllerFactory();

        $serviceManager = new ServiceManager();

        $teamTable = $this->prophesize(TeamTable::class);
        $serviceManager->setService(TeamTable::class, $teamTable->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $teamController = $factory($serviceManager, null);
        $this->assertInstanceOf(TeamController::class, $teamController);
    }
}
