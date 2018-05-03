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
use Translations\Controller\TranslationsController;
use Translations\Model\AppResourceTable;
use Translations\Model\AppTable;
use Translations\Model\EntryCommonTable;
use Translations\Model\EntryStringTable;
use Translations\Model\ResourceFileEntryTable;
use Translations\Model\ResourceTypeTable;
use Translations\Model\SuggestionStringTable;
use Translations\Model\SuggestionTable;
use Translations\Model\SuggestionVoteTable;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\View\Renderer\PhpRenderer;

class TranslationsControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TranslationsController(
            $container->get(AppTable::class),
            $container->get(AppResourceTable::class),
            $container->get(ResourceTypeTable::class),
            $container->get(ResourceFileEntryTable::class),
            $container->get(EntryCommonTable::class),
            $container->get(EntryStringTable::class),
            $container->get(SuggestionTable::class),
            $container->get(SuggestionStringTable::class),
            $container->get(SuggestionVoteTable::class),
            $container->get(Translator::class),
            $container->get(PhpRenderer::class)
        );
    }
}
