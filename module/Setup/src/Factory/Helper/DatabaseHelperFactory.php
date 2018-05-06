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

namespace Setup\Factory\Helper;

use Interop\Container\ContainerInterface;
use Setup\Helper\AdapterProviderHelper;
use Setup\Helper\DatabaseHelper;
use Zend\Config\Config;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\Factory\FactoryInterface;

class DatabaseHelperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DatabaseHelper(
            new Config($container->has('config') ? $container->get('config') : []),
            new AdapterProviderHelper(),
            $container->get(Translator::class),
            $container->get('zfcuser_module_options')
        );
    }
}
