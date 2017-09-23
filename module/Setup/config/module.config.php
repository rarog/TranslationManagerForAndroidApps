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
namespace Setup;

use Zend\Cache\Storage\Plugin;
use Zend\Router\Http\Segment;

return [
    'caches' => [
        'SetupCache' => [
            'adapter' => 'filesystem',
            'options' => [
                'cache_dir' => __DIR__ . '/../../../data/cache/',
                'ttl' => 900,
                'namespace' => 'setup:cache',
            ],
            'plugins' => [
                Plugin\ClearExpiredByFactor::class,
                Plugin\OptimizeByFactor::class,
                Plugin\Serializer::class,
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\SetupController::class => Factory\Controller\SetupControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'setup' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/setup[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\SetupController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            Command\PrintSchema::class => Factory\Command\PrintSchemaFactory::class,
            Command\UpdateSchema::class => Factory\Command\UpdateSchemaFactory::class,
            Model\DatabaseHelper::class => Factory\Model\DatabaseHelperFactory::class,
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
        'strategies' => [
            'ViewJsonStrategy',
        ],
        'template_path_stack' => [
            'setup' => __DIR__ . '/../view',
        ],
    ],
];
