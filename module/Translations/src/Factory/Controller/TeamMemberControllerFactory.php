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

use Application\Model\UserTable;
use Interop\Container\ContainerInterface;
use Translations\Controller\TeamMemberController;
use Translations\Model\TeamMemberTable;
use Translations\Model\TeamTable;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\Renderer\PhpRenderer;

class TeamMemberControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TeamMemberController(
            $container->get(TeamMemberTable::class),
            $container->get(TeamTable::class),
            $container->get(UserTable::class),
            $container->get(Translator::class),
            $container->get(PhpRenderer::class)
        );
    }
}
