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

namespace Setup\Factory\Controller;

use Interop\Container\ContainerInterface;
use Setup\Controller\SetupController;
use Setup\Helper\DatabaseHelper;
use Zend\ModuleManager\Listener\ListenerOptions;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\View\Renderer\PhpRenderer;

class SetupControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configuration = $container->get('ApplicationConfig');

        return new SetupController(
            $container->get(Translator::class),
            new Container('setup'),
            new ListenerOptions($configuration['module_listener_options']),
            $container->get(PhpRenderer::class),
            $container->get('zfcuser_user_service'),
            $container->get('zfcuser_module_options'),
            $container->get(DatabaseHelper::class),
            $container->get(SessionManager::class),
            $container->get('SetupCache')
        );
    }
}
