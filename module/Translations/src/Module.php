<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

class Module implements ConfigProviderInterface, ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     * @see \Zend\ModuleManager\Feature\ConfigProviderInterface::getConfig()
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     * @see \Zend\ModuleManager\Feature\ServiceProviderInterface::getServiceConfig()
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\AppTable::class => function ($container) {
                    $tableGateway = $container->get(Model\AppTableGateway::class);
                    return new Model\AppTable($tableGateway);
                },
                Model\AppTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\App());
                    return new TableGateway('app', $dbAdapter, null, $resultSetPrototype);
                },
                Model\AppResourceTable::class => function ($container) {
                    $tableGateway = $container->get(Model\AppResourceTableGateway::class);
                    return new Model\AppResourceTable($tableGateway);
                },
                Model\AppResourceTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\AppResource());
                    return new TableGateway('app_resource', $dbAdapter, null, $resultSetPrototype);
                },
                Model\AppResourceFileTable::class => function ($container) {
                    $tableGateway = $container->get(Model\AppResourceFileTableGateway::class);
                    return new Model\AppResourceFileTable($tableGateway);
                },
                Model\AppResourceFileTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\AppResourceFile());
                    return new TableGateway('app_resource_file', $dbAdapter, null, $resultSetPrototype);
                },
                Model\ResourceTypeTable::class => function ($container) {
                    $tableGateway = $container->get(Model\ResourceTypeTableGateway::class);
                    return new Model\ResourceTypeTable($tableGateway);
                },
                Model\ResourceTypeTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\ResourceType());
                    return new TableGateway('team', $dbAdapter, null, $resultSetPrototype);
                },
                Model\TeamTable::class => function ($container) {
                    $tableGateway = $container->get(Model\TeamTableGateway::class);
                    return new Model\TeamTable($tableGateway);
                },
                Model\TeamTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Team());
                    return new TableGateway('team', $dbAdapter, null, $resultSetPrototype);
                },
                Model\TeamMemberTable::class => function ($container) {
                    $tableGateway = $container->get(Model\TeamMemberTableGateway::class);
                    return new Model\TeamMemberTable($tableGateway);
                },
                Model\TeamMemberTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\TeamMember());
                    return new TableGateway('team_member', $dbAdapter, null, $resultSetPrototype);
                },
                Model\UserTable::class => function ($container) {
                    $tableGateway = $container->get(Model\UserTableGateway::class);
                    $userMapper = $container->get('zfcuser_user_mapper');
                    return new Model\UserTable($tableGateway, $userMapper);
                },
                Model\UserTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\User);
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                Model\UserSettingsTable::class => function ($container) {
                    $tableGateway = $container->get(Model\UserSettingsTableGateway::class);
                    return new Model\UserSettingsTable($tableGateway);
                },
                Model\UserSettingsTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\UserSettings());
                    return new TableGateway('user_settings', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }
}
