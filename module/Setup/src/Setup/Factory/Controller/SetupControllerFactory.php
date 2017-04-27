<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup\Factory\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class SetupControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configuration = $container->get('ApplicationConfig');
        $listenerOptions  = new \Zend\ModuleManager\Listener\ListenerOptions($configuration['module_listener_options']);
        return new \Setup\Controller\SetupController(
            $container->get(\Zend\Mvc\I18n\Translator::class),
            new \Zend\ModuleManager\Listener\ListenerOptions($configuration['module_listener_options']),
            $container->get('ViewRenderer'),
            $container->get('zfcuser_user_service'),
            $container->get('zfcuser_module_options')
        );
    }
}
