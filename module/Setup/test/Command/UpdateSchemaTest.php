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
use Prophecy\Argument;
use Setup\Command\UpdateSchema;
use Setup\Helper\DatabaseHelper;
use ZF\Console\Route;
use Zend\Console\ColorInterface;
use Zend\Console\Adapter\AdapterInterface;

class UpdateSchemaTest extends TestCase
{
    private $databaseHelper;

    private $route;

    private $console;

    private $consoleWriteLineCall;

    private $updateSchema;

    protected function setUp()
    {
        $this->databaseHelper = $this->prophesize(DatabaseHelper::class);

        $this->route = $this->prophesize(Route::class);

        $consoleWriteLineCall = [];
        $this->consoleWriteLineCall = &$consoleWriteLineCall;

        $this->console = $this->prophesize(AdapterInterface::class);
        $this->console->writeLine(Argument::cetera())->will(
            function ($args) use (&$consoleWriteLineCall) {
                $consoleWriteLineCall['message'] = $args[0];
                $consoleWriteLineCall['color'] = $args[1];
            }
        );

        $this->updateSchema = new UpdateSchema($this->databaseHelper->reveal());
    }

    public function testSetupIncompleteStatus()
    {
        $this->databaseHelper->updateSchema()->will(
            function () {
                $this->getLastStatus()->willReturn(DatabaseHelper::SETUPINCOMPLETE);
            }
        );

        $updateSchema = $this->updateSchema;
        $result = $updateSchema($this->route->reveal(), $this->console->reveal());

        $this->assertEquals(0, $result);
        $this->assertEquals(
            'Setup is incomplete.',
            $this->consoleWriteLineCall['message']
        );
        $this->assertEquals(ColorInterface::NORMAL, $this->consoleWriteLineCall['color']);
    }

    public function testCurrentSchemaLatest()
    {
        $this->databaseHelper->updateSchema()->will(
            function () {
                $this->getLastStatus()->willReturn(DatabaseHelper::CURRENTSCHEMAISLATEST);
            }
        );

        $updateSchema = $this->updateSchema;
        $result = $updateSchema($this->route->reveal(), $this->console->reveal());

        $this->assertEquals(0, $result);
        $this->assertEquals(
            'Latest schema is already installed in the database.',
            $this->consoleWriteLineCall['message']
        );
        $this->assertEquals(ColorInterface::NORMAL, $this->consoleWriteLineCall['color']);
    }

    public function testSchemaUpdated()
    {
        $this->databaseHelper->updateSchema()->will(
            function () {
                $this->getLastStatus()->willReturn(DatabaseHelper::SCHEMAUPDATED);
            }
        );

        $updateSchema = $this->updateSchema;
        $result = $updateSchema($this->route->reveal(), $this->console->reveal());

        $this->assertEquals(0, $result);
        $this->assertEquals(
            'Schema was updated.',
            $this->consoleWriteLineCall['message']
        );
        $this->assertEquals(ColorInterface::NORMAL, $this->consoleWriteLineCall['color']);
    }

    public function testUnknownStatus()
    {
        $this->databaseHelper->updateSchema()->will(
            function () {
                $this->getLastStatus()->willReturn(-1);
            }
        );

        $updateSchema = $this->updateSchema;
        $result = $updateSchema($this->route->reveal(), $this->console->reveal());

        $this->assertEquals(0, $result);
        $this->assertEquals(
            'Unknown status',
            $this->consoleWriteLineCall['message']
        );
        $this->assertEquals(ColorInterface::NORMAL, $this->consoleWriteLineCall['color']);
    }
}
