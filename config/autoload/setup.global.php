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
$settings = array(
    'available_languages' => [
        'de_DE' => 'German (Germany)/Deutsch (Deutschland)',
        'en_US' => 'English (USA)',
    ],
        
    /**
     * User table name
     */
    'user_table_name' => 'user',

    /**
     * User table id column name
     */
    'user_table_id_column_name' => 'user_id',

    /**
     * User table username column name
     */
    'user_table_username_column_name' => 'username',

    /**
     * User table password column name
     */
    'user_table_password_column_name' => 'password',
);

/**
 * You do not need to edit below this line
 */
return [
    'setup' => $settings,
];