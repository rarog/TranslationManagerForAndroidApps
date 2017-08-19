<?php
/**
 * @link      https://github.com/rarog/TranslationManagerForAndroidApps for the canonical source repository
 * @copyright Copyright (c) 2017 Andrej Sinicyn
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3 or later
 */
/**
 * Setup Configuration
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */
$settings = [
    /**
     * Languages available during setup
     */
    'available_languages' => [
        'de_DE' => 'German (Germany)/Deutsch (Deutschland)',
        'en_US' => 'English (USA)',
    ],

    /**
     * Supported databases
    *
    * Must be from https://zendframework.github.io/zend-db/adapter/ or
    * http://php.net/manual/de/pdo.drivers.php
    */
    'drivers' => [
        'Pdo_Mysql'  => 'MySQL/MariaDB',
        'Pdo_Sqlite' => 'SQLite',
    ],

    /**
     * Database schema path
    *
    * Minimal schemas needed for this module are in data/schema of this module and should be copied
    * to the application path defined in this configuration key.
    */
    'db_schema_path' => 'data/schema',

    /**
     * Database schema naming
    *
    * This array must have identical keys as 'drivers'.
    * Example for Pdo_Mysql: Setup module searching for a file "APP_ROOT/data/schema/schema.mysql.sql"
    * Schema updates files are called "update.{i}.mysql.sql" with {i} corresponding the db version in it.
    * When checking, it will allways be looked for existance of "update.{MAX(version) + 1}.mysql.sql",
    * So you have to provide all files correctly incremented.
    */
    'db_schema_naming' => [
        'Pdo_Mysql'  => 'mysql',
        'Pdo_Sqlite' => 'sqlite',
    ],

    /**
     * Database schema version table
     *
     * If this table is renamed, the schema files in the application path should be modified accordingly.
     * The table contains 3 columns:
     * 1) version - an incremented version as integer number where MAX(version) = current version in the db.
     * 2) setupid - 32 char long identifier, so multiple installations won't conflict and can be
     *    distinquished
     * 3) timestamp - unixtime value in UTC, when the db version was installed.
    */
    'db_schema_version_table' => 'database_schema_version',

    /**
     * Database schema init version
    *
    * Version, that is written into the table in db_schema_version_table, when schema is installed.
    */
    'db_schema_init_version' => 1,

    /**
     * Setup idendifier
     *
     * Put your own installation id in the configuration.
     */
    'setup_id' => 'cfe5de0b8a34a639690a5e95a5626ad3',

    /**
     * Setup session timeout
     *
     * Regulates how long the setup session is valid without regeneration and all other sessions will be
     * locked out.
     * Defaults to 900 seconds (15 minutes)
     */
    'setup_id' => 900,
];

/**
 * You do not need to edit below this line
 */
return [
    'setup' => $settings,
];
