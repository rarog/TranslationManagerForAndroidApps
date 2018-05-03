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

use Application\Model\UserTable;
use PHPUnit\Framework\TestCase;
use Translations\Controller\TeamMemberController;
use Translations\Factory\Controller\TeamMemberControllerFactory;
use Translations\Model\TeamMemberTable;
use Translations\Model\TeamTable;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;

class TeamMemberControllerFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new TeamMemberControllerFactory();

        $serviceManager = new ServiceManager();

        $teamMemberTable = $this->prophesize(TeamMemberTable::class);
        $serviceManager->setService(TeamMemberTable::class, $teamMemberTable->reveal());

        $teamTable = $this->prophesize(TeamTable::class);
        $serviceManager->setService(TeamTable::class, $teamTable->reveal());

        $userTable = $this->prophesize(UserTable::class);
        $serviceManager->setService(UserTable::class, $userTable->reveal());

        $translator = $this->prophesize(Translator::class);
        $serviceManager->setService(Translator::class, $translator->reveal());

        $phpRenderer = $this->prophesize(PhpRenderer::class);
        $serviceManager->setService(PhpRenderer::class, $phpRenderer->reveal());

        $teamMemberController = $factory($serviceManager, null);
        $this->assertInstanceOf(TeamMemberController::class, $teamMemberController);
    }
}
