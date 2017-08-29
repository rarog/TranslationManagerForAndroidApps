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

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class TeamMemberControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new \Translations\Controller\TeamMemberController(
            $container->get(\Translations\Model\TeamMemberTable::class),
            $container->get(\Translations\Model\TeamTable::class),
            $container->get(\Application\Model\UserTable::class),
            $container->get(\Zend\Mvc\I18n\Translator::class),
            $container->get(\Zend\View\Renderer\PhpRenderer::class)
        );
    }
}
