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

namespace Common;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\SettingTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\SettingTableGateway::class);
                    $table = new Model\SettingTable($tableGateway);
                    return $table;
                },
                Model\SettingTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Setting());
                    return new TableGateway('setting', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }
}
