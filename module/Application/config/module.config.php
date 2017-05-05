<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
    'listeners' => [
        'SetupListener',
    ],
    'navigation' => [
        'default' => [
            [
                'label'      => _('Home'),
                'route'      => 'home',
                'icon'       => 'glyphicon glyphicon-home',
                'permission' => 'userBase',
            ],
            [
                'label'      => _('Translations'),
                'route'      => '#',
                'icon'       => 'glyphicon glyphicon-dashboard',
                'permission' => 'userBase',
                'pages' => [
                    [
                        'label'      => _('Apps'),
                        'route'      => 'app',
                        'icon'       => 'glyphicon glyphicon-phone',
                        'permission' => 'app',
                    ],
                ],
            ],
            [
                'label'      => '',
                'route'      => '#',
                'icon'       => 'glyphicon glyphicon-question-sign',
                'permission' => 'userBase',
                'pages' => [
                    [
                        'label'      => _('My user'),
                        'route'      => 'zfcuser',
                        'icon'       => 'glyphicon glyphicon-user',
                        'permission' => 'userBase',
                    ],
                    [
                        'route'     => '#',
                        'separator' => true,
                    ],
                    [
                        'label'      => _('Sign out'),
                        'route'      => 'zfcuser/logout',
                        'icon'       => 'glyphicon glyphicon-off',
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
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
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
            'navigation'                 => \Zend\Navigation\Service\DefaultNavigationFactory::class,
            'RbacListener'               => Factory\Listener\RbacListenerFactory::class,
            'SetupListener'              => Factory\Listener\SetupListenerFactory::class,
            'SetupAwareRedirectStrategy' => Factory\View\Strategy\SetupAwareRedirectStrategyFactory::class,
        ],
    ],
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'view_helpers' => [
        'invokables'=> [
            'multilevelNavigationMenu' => View\Helper\MultilevelNavigationMenu::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
