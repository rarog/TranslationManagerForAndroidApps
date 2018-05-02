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

namespace Translations\Factory\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Translations\Controller\Plugin\GetAppIfAllowed;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfcRbac\Service\AuthorizationService;

class GetAppIfAllowedFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new GetAppIfAllowed(
            $container->get(AppTable::class),
            $container->get(AppResourceTable::class),
            $container->get(AuthorizationService::class)
        );
    }
}
