<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Translations;

use Zend\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\AppController::class             => Factory\Controller\AppControllerFactory::class,
            Controller\AppResourceController::class     => Factory\Controller\AppResourceControllerFactory::class,
            Controller\AppResourceFileController::class => Factory\Controller\AppResourceFileControllerFactory::class,
            Controller\TeamController::class            => Factory\Controller\TeamControllerFactory::class,
            Controller\TeamMemberController::class      => Factory\Controller\TeamMemberControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'app'             => [
                'type'    => Segment::class,
                'options' => [
                    'route'       => '/app[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => Controller\AppController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'appresource'     => [
                'type'    => Segment::class,
                'options' => [
                    'route'       => '/appresource/app/:appId[/:action[/:resourceId]]',
                    'constraints' => [
                        'appId'      => '[0-9]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'resourceId' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => Controller\AppResourceController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'appresourcefile' => [
                'type'    => Segment::class,
                'options' => [
                    'route'       => '/appresourcefile/app/:appId[/:action[/:resourceFileId]]',
                    'constraints' => [
                        'appId'          => '[0-9]+',
                        'action'         => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'resourceFileId' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => Controller\AppResourceFileController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'team'            => [
                'type'    => Segment::class,
                'options' => [
                    'route'       => '/team[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => Controller\TeamController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'teammember'      => [
                'type'    => Segment::class,
                'options' => [
                    'route'       => '/teammember/team/:teamId[/:action[/:userId]]',
                    'constraints' => [
                        'teamId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'userId' => '[0-9]+',
                    ],
                    'defaults'    => [
                        'controller' => Controller\TeamMemberController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'translations' => __DIR__ . '/../view',
        ],
    ],
];
