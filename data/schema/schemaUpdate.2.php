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
    'user_role_linker' => [
        'commandName' => 'AlterTable',
        'tableName' => 'user_role_linker',
        'changeColumn' => [
            [
                'type' => 'Varchar',
                'name' => 'role_id',
                'length' => 255,
            ],
        ],
    ],
    'entry_common' => [
        'commandName' => 'AlterTable',
        'tableName' => 'entry_common',
        'addColumn' => [
            [
                'type' => 'Integer',
                'name' => 'notification_status',
            ],
        ],
    ],
    'setting' => [
        'commandName' => 'CreateTable',
        'tableName' => 'setting',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'Varchar',
                'name' => 'path',
                'length' => 255,
            ],
            [
                'type' => 'Text',
                'name' => 'value',
                'length' => 255,
                'nullable' => true,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'setting_pk',
            ],
            [
                'type' => 'UniqueKey',
                'column' => 'path',
                'name' => 'setting_uk1',
            ],
        ],
    ],
];
