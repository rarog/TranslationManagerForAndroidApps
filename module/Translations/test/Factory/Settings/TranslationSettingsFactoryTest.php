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

namespace TranslationsTest\Factory\Settings;

use Common\Model\SettingTable;
use PHPUnit\Framework\TestCase;
use Translations\Factory\Settings\TranslationSettingsFactory;
use Translations\Settings\TranslationSettings;
use Zend\ServiceManager\ServiceManager;

class TranslationSettingsFactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new TranslationSettingsFactory();

        $serviceManager = new ServiceManager();

        $settingTable = $this->prophesize(SettingTable::class);
        $serviceManager->setService(SettingTable::class, $settingTable->reveal());

        $translationSettings = $factory($serviceManager, null);
        $this->assertInstanceOf(TranslationSettings::class, $translationSettings);
    }
}
