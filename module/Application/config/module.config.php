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

namespace Application;

use Zend\Router\Http\Literal;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\SettingsController::class => Factory\Controller\SettingsControllerFactory::class,
        ],
    ],
    'listeners' => [
        Listener\SetupListener::class
    ],
    'navigation' => [
        'default' => [
            [
                'label' => _('Home'),
                'route' => 'home',
                'icon' => 'glyphicon glyphicon-home',
                'permission' => 'userBase',
            ],
            [
                'label' => _('Dashboard'),
                'route' => '#',
                'icon' => 'glyphicon glyphicon-dashboard',
                'permission' => 'userBase',
                'pages' => [
                    [
                        'label' => _('Teams'),
                        'route' => 'team',
                        'icon' => 'fa fa-users',
                        'permission' => 'team.view'
                    ],
                    [
                        'label' => _('Apps'),
                        'route' => 'app',
                        'icon' => 'glyphicon glyphicon-phone',
                        'permission' => 'app.view',
                    ],
                    [
                        'label' => _('Translations'),
                        'route' => 'translations',
                        'icon' => 'fa fa-language',
                        'permission' => 'translations.view',
                    ],
                ],
            ],
            [
                'label' => '',
                'route' => '#',
                'icon' => 'glyphicon glyphicon-question-sign',
                'permission' => 'userBase',
                'pages' => [
                    [
                        'label' => _('My user'),
                        'route' => 'zfcuser',
                        'icon' => 'glyphicon glyphicon-user',
                        'permission' => 'userBase',
                    ],
                    [
                        'label' => _('About...'),
                        'route' => 'application/about',
                        'icon' => 'glyphicon glyphicon-info-sign',
                        'permission' => 'userBase',
                    ],
                    [
                        'route' => '#',
                        'separator' => true,
                    ],
                    [
                        'label' => _('Sign out'),
                        'route' => 'zfcuser/logout',
                        'icon' => 'glyphicon glyphicon-off',
                        'permission' => 'userBase',
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'application' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/application',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'about' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/about',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action' => 'about',
                            ],
                        ],
                    ],
                ],
            ],
            'settings' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/settings',
                    'defaults' => [
                        'controller' => Controller\SettingsController::class,
                        'action' => 'index',
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'userlanguages' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/userlanguages',
                            'defaults' => [
                                'controller' => Controller\SettingsController::class,
                                'action' => 'userlanguages',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'delegators' => [
            \Zend\Mvc\I18n\Translator::class => [
                Delegator\TranslatorDelegator::class,
            ],
        ],
        'factories' => [
            'navigation' => \Zend\Navigation\Service\DefaultNavigationFactory::class,
            Command\CleanCache::class => Factory\Command\CleanCacheFactory::class,
            Listener\RbacListener::class => Factory\Listener\RbacListenerFactory::class,
            Listener\SetupListener::class => Factory\Listener\SetupListenerFactory::class,
            View\Strategy\SetupAwareRedirectStrategy::class => Factory\View\Strategy\SetupAwareRedirectStrategyFactory::class,
            \Zend\Log\Logger::class => Factory\Log\LoggerFactory::class,
        ],
    ],
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'useMinified' => Factory\View\Helper\UseMinifiedHelperFactory::class,
        ],
        'invokables' => [
            'bootstrapSelectHelper' => View\Helper\BootstrapSelectHelper::class,
            'dataTablesInitHelper' => View\Helper\DataTablesInitHelper::class,
            'multilevelNavigationMenu' => View\Helper\MultilevelNavigationMenu::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
