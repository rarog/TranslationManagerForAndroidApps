<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

namespace Setup;

use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return array(
    'controllers' => array(
        'factories' => [
            Controller\SetupController::class => InvokableFactory::class,
        ],
    ),
    'router' => array(
        'routes' => array(
            'setup' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/setup[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => Controller\SetupController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'setup' => __DIR__ . '/../view',
        ),
    ),
 );