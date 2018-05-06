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

namespace Application\Factory\View\Helper;

use Application\View\Helper\UseMinifiedHelper;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UseMinifiedHelperFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ServiceManager\Factory\FactoryInterface::__invoke()
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $useMinified = false;

        $config = $container->has('config') ? $container->get('config') : [];
        if (array_key_exists('tmfaa', $config) &&
            array_key_exists('use_minified', $config['tmfaa']) &&
            is_bool($config['tmfaa']['use_minified'])) {
            $useMinified = $config['tmfaa']['use_minified'];
        }

        return new UseMinifiedHelper($useMinified);
    }
}
