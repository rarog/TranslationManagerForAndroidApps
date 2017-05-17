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

use Zend\Session;

// Little helper function to generate list of locales.
function getLocaleNamesInLocale($inLocale)
{
    $locales = [];
    foreach (ResourceBundle::getLocales('') as $locale) {
        $locales[$locale] = $locale . ' - ' . Locale::getDisplayName($locale, $inLocale);
    }
    return $locales;
};

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
    'session' => [
        'config' => [
            'class' => Session\Config\SessionConfig::class,
            'options' => [
                'name' => 'tmfaa:session',
            ],
        ],
        'storage'    => Session\Storage\SessionArrayStorage::class,
        'validators' => [
            Session\Validator\RemoteAddr::class,
            Session\Validator\HttpUserAgent::class,
        ],
    ],
    'session_config' => [
        'gc_maxlifetime'      => 900,
        'name'                => 'tmfaa:session',
        'remember_me_seconds' => 900,
    ],
    'session_manager' => [
        'validators' => [
            Session\Validator\RemoteAddr::class,
            Session\Validator\HttpUserAgent::class,
        ]
    ],
    'session_storage' => [
        'type' => Session\Storage\SessionArrayStorage::class,
    ],
    'settings' => [
        'translator_cache' => 'Cache\Persistent',
        'session_cache'    => 'Cache\Persistent',
        'supported_languages' => [
            'de_DE' => 'German (Germany)/Deutsch (Deutschland)',
            'en_US' => 'English (USA)',
        ],
        'locale_names' => [
            'de_DE' => getLocaleNamesInLocale('de_DE'),
            'en_US' => getLocaleNamesInLocale('en_US'),
        ],
    ],
    'tmfaa' => [
        'app_dir' => __DIR__ . '/../../data/apps/',
    ],
];
