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
use Translations\Controller\Plugin\GetAppIfAllowed;
use Translations\Factory\Controller\Plugin\GetAppIfAllowedFactory;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Service\AuthorizationService;

class GetAppIfAllowedFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new GetAppIfAllowedFactory();

        $serviceManager = new ServiceManager();

        $appTable = $this->prophesize(AppTable::class);
        $serviceManager->setService(AppTable::class, $appTable->reveal());

        $appResourceTable = $this->prophesize(AppResourceTable::class);
        $serviceManager->setService(AppResourceTable::class, $appResourceTable->reveal());

        $authorizationService = $this->prophesize(AuthorizationService::class);
        $serviceManager->setService(AuthorizationService::class, $authorizationService->reveal());

        $getAppIfAllowed = $factory($serviceManager, null);
        $this->assertInstanceOf(GetAppIfAllowed::class, $getAppIfAllowed);
    }
}
