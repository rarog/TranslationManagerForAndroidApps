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

class TranslationsControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new \Translations\Controller\TranslationsController(
            $container->get(\Translations\Model\AppTable::class),
            $container->get(\Translations\Model\AppResourceTable::class),
            $container->get(\Translations\Model\ResourceTypeTable::class),
            $container->get(\Translations\Model\ResourceFileEntryTable::class),
            $container->get(\Translations\Model\EntryStringTable::class),
            $container->get(\Translations\Model\SuggestionTable::class),
            $container->get(\Translations\Model\SuggestionStringTable::class),
            $container->get(\Translations\Model\SuggestionVoteTable::class),
            $container->get(\Zend\Mvc\I18n\Translator::class),
            $container->get(\Zend\View\Renderer\PhpRenderer::class)
        );
    }
}
