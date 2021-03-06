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

namespace Application\Factory\Listener;

use Application\Listener\SetupListener;
use Application\Model\UserSettingsTable;
use Interop\Container\ContainerInterface;
use Translations\Model\TeamTable;
use UserRbac\Model\UserRoleLinkerTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class SetupListenerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SetupListener(
            $container->get('zfcuser_user_mapper'),
            $container->get(UserRoleLinkerTable::class),
            $container->get(TeamTable::class),
            $container->get(UserSettingsTable::class)
        );
    }
}
