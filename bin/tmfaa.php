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

use Application\Module;
use Zend\Console\Console;
use ZF\Console\Application;
use ZF\Console\Dispatcher;

chdir(dirname(__DIR__));

include 'vendor/autoload.php';

$appConfig = require 'config/application.config.php';

// Disable module and config cache usage for console apps
if (is_array($appConfig) && is_array($appConfig['module_listener_options'])) {
    if (array_key_exists('config_cache_enabled', $appConfig['module_listener_options'])) {
        $appConfig['module_listener_options']['config_cache_enabled'] = false;
    }
    if (array_key_exists('module_map_cache_enabled', $appConfig['module_listener_options'])) {
        $appConfig['module_listener_options']['module_map_cache_enabled'] = false;
    }
}

$mvcApplication = \Zend\Mvc\Application::init($appConfig);
$serviceManager = $mvcApplication->getServiceManager();
$dispatcher = new Dispatcher($serviceManager);

$application = new Application('TranslationManagerForAndroidApps', Module::VERSION, include 'config/console-routes.php', Console::getInstance(), $dispatcher);

$exit = $application->run();
exit($exit);
