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
use Zend\Cache\Storage\Plugin;
use Zend\Cache\Storage\Adapter\Filesystem;

// Little helper function to generate list of locales.
if (! function_exists('getLocaleNamesInLocale')) {
    function getLocaleNamesInLocale($inLocale, $primaryOnly = false)
    {
        $locales = [];
        foreach (ResourceBundle::getLocales('') as $locale) {
            if ($primaryOnly && (strpos($locale, '_') !== false)) {
                continue;
            }

            $locales[$locale] = $locale . ' - ' . Locale::getDisplayName($locale, $inLocale);
        }
        return $locales;
    }
}

return [
    'caches' => [
        'Cache\Persistent' => [
            'adapter' => Filesystem::class,
            'options' => [
                'cache_dir' => __DIR__ . '/../../data/cache/',
                'ttl' => 86400,
                'namespace' => 'tmfaa:cache',
            ],
            'plugins' => [
                Plugin\ClearExpiredByFactor::class,
                Plugin\OptimizeByFactor::class,
                Plugin\Serializer::class,
            ],
        ],
    ],
    'db' => [
        'driver' => 'Pdo',
    ],
    'security' => [
        'master_key' => '',
    ],
    'session' => [
        'config' => [
            'class' => Session\Config\SessionConfig::class,
            'options' => [
                'name' => 'tmfaa:session',
            ],
        ],
        'storage' => Session\Storage\SessionArrayStorage::class,
        'validators' => [
            Session\Validator\RemoteAddr::class,
            Session\Validator\HttpUserAgent::class,
        ],
    ],
    'session_config' => [
        'gc_maxlifetime' => 900,
        'name' => 'tmfaa:session',
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
        'locale_names_primary' => [
            'de_DE' => getLocaleNamesInLocale('de_DE', true),
            'en_US' => getLocaleNamesInLocale('en_US', true),
        ],
    ],
    'tmfaa' => [
        'app_dir' => __DIR__ . '/../../data/apps/',
        'use_minified' => true,
    ],
];
