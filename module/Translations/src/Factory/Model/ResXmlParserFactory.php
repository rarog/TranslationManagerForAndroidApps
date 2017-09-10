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

namespace Translations\Factory\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ResXmlParserFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $resXmlParser = new \Translations\Model\ResXmlParser(
            $container->get(\Translations\Model\AppResourceTable::class),
            $container->get(\Translations\Model\AppResourceFileTable::class),
            $container->get(\Translations\Model\ResourceTypeTable::class),
            $container->get(\Translations\Model\ResourceFileEntryTable::class),
            $container->get(\Translations\Model\EntryCommonTable::class),
            $container->get(\Translations\Model\EntryStringTable::class),
            $container->get(\Zend\Log\Logger::class)
        );

        $config = $container->get('config');
        if (isset($config['tmfaa']) && isset($config['tmfaa']['app_dir'])) {
            $resXmlParser->setAppDirectory($config['tmfaa']['app_dir']);
        }

        return $resXmlParser;
    }
}
