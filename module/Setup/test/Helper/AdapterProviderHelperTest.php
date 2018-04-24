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
namespace SetupTest\Helper;

use PHPUnit\Framework\TestCase;
use Setup\Helper\AdapterProviderHelper;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Pdo\Pdo;

class AdapterProviderHelperTest extends TestCase
{

    public function testInvokeEmptyArray()
    {
        $adapterProviderHelper = new AdapterProviderHelper();

        $result = $adapterProviderHelper->getDbAdapter([]);
        $this->assertInstanceOf(Adapter::class, $result);
        $this->assertInstanceOf(Pdo::class, $result->getDriver());
    }

    public function testInvokeInvalidConfigArray()
    {
        $adapterProviderHelper = new AdapterProviderHelper();

        $result = $adapterProviderHelper->getDbAdapter(['driver' => new \stdClass()]);
        $this->assertInstanceOf(Adapter::class, $result);
        $this->assertInstanceOf(Pdo::class, $result->getDriver());
        $this->assertEmpty($result->getDriver()->getDatabasePlatformName());

        $result = $adapterProviderHelper->getDbAdapter(['driver' => 'unknownDriver']);
        $this->assertInstanceOf(Adapter::class, $result);
        $this->assertInstanceOf(Pdo::class, $result->getDriver());
        $this->assertEmpty($result->getDriver()->getDatabasePlatformName());
    }

    public function testInvokeValidConfigArray()
    {
        $adapterProviderHelper = new AdapterProviderHelper();

        $result = $adapterProviderHelper->getDbAdapter(['driver' => 'Pdo_Mysql']);
        $this->assertInstanceOf(Adapter::class, $result);
        $this->assertEquals('Mysql', $result->getDriver()->getDatabasePlatformName());

        $result = $adapterProviderHelper->getDbAdapter(['driver' => 'Pdo_Pgsql']);
        $this->assertInstanceOf(Adapter::class, $result);
        $this->assertEquals('Postgresql', $result->getDriver()->getDatabasePlatformName());

        $result = $adapterProviderHelper->getDbAdapter(['driver' => 'Pdo_Sqlite']);
        $this->assertInstanceOf(Adapter::class, $result);
        $this->assertEquals('Sqlite', $result->getDriver()->getDatabasePlatformName());
    }
}
