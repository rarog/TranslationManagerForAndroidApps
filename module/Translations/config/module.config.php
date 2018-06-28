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

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'controller_plugins' => [
        'aliases' => [
            'getAppIfAllowed' => Controller\Plugin\GetAppIfAllowed::class,
        ],
        'factories' => [
            Controller\Plugin\GetAppIfAllowed::class => Factory\Controller\Plugin\GetAppIfAllowedFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AppController::class => Factory\Controller\AppControllerFactory::class,
            Controller\AppResourceController::class => Factory\Controller\AppResourceControllerFactory::class,
            Controller\AppResourceFileController::class => Factory\Controller\AppResourceFileControllerFactory::class,
            Controller\GitController::class => Factory\Controller\GitControllerFactory::class,
            Controller\SyncController::class => Factory\Controller\SyncControllerFactory::class,
            Controller\TeamController::class => Factory\Controller\TeamControllerFactory::class,
            Controller\TeamMemberController::class => Factory\Controller\TeamMemberControllerFactory::class,
            Controller\TranslationsController::class => Factory\Controller\TranslationsControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'app' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/app[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AppController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'appresource' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/appresource/app/:appId[/:action[/:resourceId]]',
                    'constraints' => [
                        'appId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'resourceId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AppResourceController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'appresourcefile' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/appresourcefile/app/:appId[/:action[/:resourceFileId]]',
                    'constraints' => [
                        'appId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'resourceFileId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AppResourceFileController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'git' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/git/app/:appId[/:action]',
                    'constraints' => [
                        'appId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\GitController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'sync' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/sync/app/:appId[/:action]',
                    'constraints' => [
                        'appId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\SyncController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'team' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/team[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\TeamController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'teammember' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/teammember/team/:teamId[/:action[/:userId]]',
                    'constraints' => [
                        'teamId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'userId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\TeamMemberController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'translations' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/translations',
                    'defaults' => [
                        'controller' => Controller\TranslationsController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'details' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/details[/app/:appId/resource/:resourceId/entry/:entryId]',
                            'constraints' => [
                                'appId' => '[0-9]+',
                                'resourceId' => '[0-9]+',
                                'entryId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'details',
                            ],
                        ],
                    ],
                    'listtranslations' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/listtranslations[/app/:appId/resource/:resourceId[/entry/:entryId]]',
                            'constraints' => [
                                'appId' => '[0-9]+',
                                'resourceId' => '[0-9]+',
                                'entryId'  => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'listtranslations',
                            ],
                        ],
                    ],
                    'setnotificationstatus' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/setnotificationstatus[/app/:appId/resource/:resourceId/entry/:entryId/' .
                                'notificationstatus/:notificationStatus]',
                            'constraints' => [
                                'appId' => '[0-9]+',
                                'resourceId' => '[0-9]+',
                                'entryId' => '[0-9]+',
                                'notificationStatus' => '[01]',
                            ],
                            'defaults' => [
                                'action' => 'setnotificationstatus',
                            ],
                        ],
                    ],
                    'suggestionaccept' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/suggestionaccept[/app/:appId/resource/:resourceId/entry/:entryId/' .
                                'suggestion/:suggestionId]',
                            'constraints' => [
                                'appId' => '[0-9]+',
                                'resourceId' => '[0-9]+',
                                'entryId' => '[0-9]+',
                                'suggestionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'suggestionaccept',
                            ],
                        ],
                    ],
                    'suggestionaddedit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/suggestionaddedit[/app/:appId/resource/:resourceId/entry/:entryId/' .
                                'suggestion/:suggestionId]',
                            'constraints' => [
                                'appId' => '[0-9]+',
                                'resourceId' => '[0-9]+',
                                'entryId' => '[0-9]+',
                                'suggestionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'suggestionaddedit',
                            ],
                        ],
                    ],
                    'suggestiondelete' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/suggestiondelete[/app/:appId/resource/:resourceId/entry/:entryId/' .
                                'suggestion/:suggestionId]',
                            'constraints' => [
                                'appId' => '[0-9]+',
                                'resourceId' => '[0-9]+',
                                'entryId' => '[0-9]+',
                                'suggestionId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'suggestiondelete',
                            ],
                        ],
                    ],
                    'suggestionvote' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/suggestionvote[/app/:appId/resource/:resourceId/entry/:entryId/' .
                                'suggestion/:suggestionId/vote/:vote]',
                            'constraints' => [
                                'appId' => '[0-9]+',
                                'resourceId' => '[0-9]+',
                                'entryId' => '[0-9]+',
                                'suggestionId' => '[0-9]+',
                                'vote' => '[01]',
                            ],
                            'defaults' => [
                                'action' => 'suggestionvote',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            Model\Helper\EncryptionHelper::class => Factory\Model\EncryptionHelperFactory::class,
            Parser\ResXmlParser::class => Factory\Parser\ResXmlParserFactory::class,
            Settings\TranslationSettings::class => Factory\Settings\TranslationSettingsFactory::class,
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'translations' => __DIR__ . '/../view',
        ],
    ],
];
