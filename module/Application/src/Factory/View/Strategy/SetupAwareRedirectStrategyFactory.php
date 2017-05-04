<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application\Factory\View\Strategy;

use Application\View\Strategy\SetupAwareRedirectStrategy;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create a setup aware redirect strategy
 * Based on ZfcRbac\Factory\RedirectStrategyFactory
 */
class SetupAwareRedirectStrategyFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RedirectStrategy
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var \ZfcRbac\Options\ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('ZfcRbac\Options\ModuleOptions');
        /** @var \Zend\Authentication\AuthenticationService $authenticationService */
        $authenticationService = $container->get('Zend\Authentication\AuthenticationService');
        /** @var \Setup\Model\DatabaseHelper $databaseHelper */
        $databaseHelper = $container->get('Setup\Model\DatabaseHelper');

        return new SetupAwareRedirectStrategy($moduleOptions->getRedirectStrategy(), $authenticationService, $databaseHelper);
    }

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, RedirectStrategy::class);
    }
}
