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

namespace Translations\Factory\Controller;

use Application\Model\UserSettingsTable;
use Interop\Container\ContainerInterface;
use Translations\Controller\AppController;
use Translations\Model\AppTable;
use Translations\Model\TeamTable;
use Translations\Model\Helper\EncryptionHelper;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\Factory\FactoryInterface;

class AppControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new AppController(
            $container->get(AppTable::class),
            $container->get(TeamTable::class),
            $container->get(UserSettingsTable::class),
            $container->get(Translator::class),
            $container->get(EncryptionHelper::class)
        );
    }
}
