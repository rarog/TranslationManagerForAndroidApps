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

return [
    [
        'name' => 'cleancache',
        'description' => 'Cleans classmap, config and other application caches.',
        'short_description' => 'Cleans application caches',
        'handler' => \Application\Command\CleanCache::class,
    ],
    [
        'name' => 'printschema',
        'route' => '<schemafile> [--sql=]',
        'description' => 'Prints schema installation or update file in SQL format.',
        'short_description' => 'Prints schema installation or update file in SQL format.',
        'options_descriptions' => [
            '<schemafile>' => 'Schema filename',
            '--sql'  => 'Name of SQL platform, must be one of mysql, mariadb, pgsql, sqlite, sql92',
        ],
        'defaults' => [
            'sql' => 'sql92',
        ],
        'handler' => \Setup\Command\PrintSchema::class,
    ],
    [
        'name' => 'updateschema',
        'description' => 'Updates database schema version, if a valid datababse schema is installed already and schema updates are detected.',
        'short_description' => 'Updates database schema version',
        'handler' => \Setup\Command\UpdateSchema::class,
    ],
];
