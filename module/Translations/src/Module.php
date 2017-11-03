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

namespace Translations;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\SequenceFeature;
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
                Model\AppTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\AppTableGateway::class);
                    return new Model\AppTable($tableGateway);
                },
                Model\AppTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'app_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\App());
                    return new TableGateway('app', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\AppResourceTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\AppResourceTableGateway::class);
                    return new Model\AppResourceTable($tableGateway);
                },
                Model\AppResourceTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'app_resource_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\AppResource());
                    return new TableGateway('app_resource', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\AppResourceFileTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\AppResourceFileTableGateway::class);
                    return new Model\AppResourceFileTable($tableGateway);
                },
                Model\AppResourceFileTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'app_resource_file_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\AppResourceFile());
                    return new TableGateway('app_resource_file', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\EntryCommonTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\EntryCommonTableGateway::class);
                    return new Model\EntryCommonTable($tableGateway);
                },
                Model\EntryCommonTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'entry_common_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\EntryCommon());
                    return new TableGateway('entry_common', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\EntryStringTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\EntryStringTableGateway::class);
                    $appResourceTable = $container->get(Model\AppResourceTable::class);
                    return new Model\EntryStringTable($tableGateway, $appResourceTable);
                },
                Model\EntryStringTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\EntryString());
                    return new TableGateway('entry_string', $dbAdapter, null, $resultSetPrototype);
                },
                Model\ResourceFileEntryTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\ResourceFileEntryTableGateway::class);
                    return new Model\ResourceFileEntryTable($tableGateway);
                },
                Model\ResourceFileEntryTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'resource_file_entry_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\ResourceFileEntry());
                    return new TableGateway('resource_file_entry', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\ResourceTypeTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\ResourceTypeTableGateway::class);
                    return new Model\ResourceTypeTable($tableGateway);
                },
                Model\ResourceTypeTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'resource_type_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\ResourceType());
                    return new TableGateway('resource_type', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\SuggestionTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\SuggestionTableGateway::class);
                    return new Model\SuggestionTable($tableGateway);
                },
                Model\SuggestionTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'suggestion_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Suggestion());
                    return new TableGateway('suggestion', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\SuggestionStringTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\SuggestionStringTableGateway::class);
                    return new Model\SuggestionStringTable($tableGateway);
                },
                Model\SuggestionStringTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\SuggestionString());
                    return new TableGateway('suggestion_string', $dbAdapter, null, $resultSetPrototype);
                },
                Model\SuggestionVoteTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\SuggestionVoteTableGateway::class);
                    return new Model\SuggestionVoteTable($tableGateway);
                },
                Model\SuggestionVoteTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\SuggestionVote());
                    return new TableGateway('suggestion_vote', $dbAdapter, null, $resultSetPrototype);
                },
                Model\TeamTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\TeamTableGateway::class);
                    return new Model\TeamTable($tableGateway);
                },
                Model\TeamTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $feature = new SequenceFeature('id', 'team_id_seq');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Team());
                    return new TableGateway('team', $dbAdapter, $feature, $resultSetPrototype);
                },
                Model\TeamMemberTable::class => function (ContainerInterface $container) {
                    $tableGateway = $container->get(Model\TeamMemberTableGateway::class);
                    return new Model\TeamMemberTable($tableGateway);
                },
                Model\TeamMemberTableGateway::class => function (ContainerInterface $container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\TeamMember());
                    return new TableGateway('team_member', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }
}
