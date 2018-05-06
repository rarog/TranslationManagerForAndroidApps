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

use Application\Factory\View\Helper\UseMinifiedHelperFactory;
use Application\View\Helper\UseMinifiedHelper;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class UseMinifiedHelperFactoryTest extends TestCase
{
    public function testFactory()
    {
        $configInvalidType = true;
        $configEmpty = [];
        $configMinifiedFalse = [
            'tmfaa' => [
                'use_minified' => false,
            ],
        ];
        $configMinifiedTrue = [
            'tmfaa' => [
                'use_minified' => true,
            ],
        ];

        $factory = new UseMinifiedHelperFactory();

        $serviceManager = new ServiceManager();
        $serviceManager->setAllowOverride(true);

        // Testing with no config.
        $useMinifiedHelper = $factory($serviceManager, null);
        $this->assertInstanceOf(UseMinifiedHelper::class, $useMinifiedHelper);
        $this->assertEquals(false, $useMinifiedHelper());

        // Testing with invalid type.
        $serviceManager->setService('config', $configInvalidType);
        $useMinifiedHelper = $factory($serviceManager, null);
        $this->assertInstanceOf(UseMinifiedHelper::class, $useMinifiedHelper);
        $this->assertEquals(false, $useMinifiedHelper());

        // Testing with empty array.
        $serviceManager->setService('config', $configEmpty);
        $useMinifiedHelper = $factory($serviceManager, null);
        $this->assertInstanceOf(UseMinifiedHelper::class, $useMinifiedHelper);
        $this->assertEquals(false, $useMinifiedHelper());

        // Testing with minified set to false.
        $serviceManager->setService('config', $configMinifiedFalse);
        $useMinifiedHelper = $factory($serviceManager, null);
        $this->assertInstanceOf(UseMinifiedHelper::class, $useMinifiedHelper);
        $this->assertEquals(false, $useMinifiedHelper());

        // Testing with minified set to true.
        $serviceManager->setService('config', $configMinifiedTrue);
        $useMinifiedHelper = $factory($serviceManager, null);
        $this->assertInstanceOf(UseMinifiedHelper::class, $useMinifiedHelper);
        $this->assertEquals(true, $useMinifiedHelper());

    }
}
