<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

use Application\Module;
use Setup\Model\DatabaseHelper;
use ZF\Console\Application;
use ZF\Console\Dispatcher;
use Zend\Console\Console;

chdir(dirname(__DIR__));

include 'vendor/autoload.php';

$mvcApplication = \Zend\Mvc\Application::init(require 'config/application.config.php');
$serviceManager = $mvcApplication->getServiceManager();

$databaseHelper = $serviceManager->get(DatabaseHelper::class);

$dispatcher = new Dispatcher();
$dispatcher->map('updateschema', function ($route, $console) use ($databaseHelper) {
    return $databaseHelper->updateSchema($console);
});

$application = new Application(
    'TranslationManagerForAndroidApps',
    Module::VERSION,
    include 'config/console-routes.php',
    Console::getInstance(),
    $dispatcher
);

$exit = $application->run();
exit($exit);
