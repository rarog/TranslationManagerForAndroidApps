<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return [
    'caches' => [
        'Cache\Persistent' => [
            'adapter' => 'filesystem',
            'options' => [
                'cache_dir' => __DIR__ . '/../../data/cache/',
                'ttl' => 86400,
                'namespace' => 'tmfaa:cache',
            ],
            'plugins' => [
                'serializer',
            ],
        ],
    ],
    'session_config' => [
        'name' => 'tmfaa:session',
        'gc_maxlifetime' => 900,
        'remember_me_seconds' => 900,
    ],
    'session_manager' => [
        'validators' => [
            \Zend\Session\Validator\RemoteAddr::class,
            \Zend\Session\Validator\HttpUserAgent::class,
        ]
    ],
    'session_storage' => [
        'type' => \Zend\Session\Storage\SessionArrayStorage::class
    ],
    'settings' => [
        'translator_cache' => 'Cache\Persistent',
        'session_cache' => 'Cache\Persistent',
        'supported_languages' => [
            'de_DE' => 'German (Germany)/Deutsch (Deutschland)',
            'en_US' => 'English (USA)',
        ],
    ],
];
