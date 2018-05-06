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

namespace Translations\Factory\Parser;

use Interop\Container\ContainerInterface;
use Translations\Model\AppResourceFileTable;
use Translations\Model\AppResourceTable;
use Translations\Model\EntryCommonTable;
use Translations\Model\EntryStringTable;
use Translations\Model\ResourceFileEntryTable;
use Translations\Model\ResourceTypeTable;
use Translations\Parser\ResXmlParser;
use Zend\Log\Logger;
use Zend\ServiceManager\Factory\FactoryInterface;

class ResXmlParserFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $resXmlParser = new ResXmlParser(
            $container->get(AppResourceTable::class),
            $container->get(AppResourceFileTable::class),
            $container->get(ResourceTypeTable::class),
            $container->get(ResourceFileEntryTable::class),
            $container->get(EntryCommonTable::class),
            $container->get(EntryStringTable::class),
            $container->get(Logger::class)
        );

        $config = $container->has('config') ? $container->get('config') : [];
        if (isset($config['tmfaa']) && isset($config['tmfaa']['app_dir'])) {
            $resXmlParser->setAppDirectory($config['tmfaa']['app_dir']);
        }

        return $resXmlParser;
    }
}
