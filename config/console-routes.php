<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */

return [
    [
        'name'              => 'cleancache',
        'description'       => 'Cleans classmap, config and other application caches.',
        'short_description' => 'Cleans application caches',
        'handler'           => \Application\Command\CleanCache::class,
    ],
    [
        'name'              => 'updateschema',
        'description'       => 'Updates database schema version, if a valid datababse schema is installed already and schema updates are detected.',
        'short_description' => 'Updates database schema version',
        'handler'           => \Setup\Command\UpdateSchema::class,
    ],
];
