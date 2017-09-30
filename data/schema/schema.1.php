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
    'log' => [
        'commandName' => 'CreateTable',
        'tableName' => 'log',
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
                'name' => 'timestamp',
                'length' => 25,
            ],
            [
                'type' => 'Integer',
                'name' => 'priority',
            ],
            [
                'type' => 'Varchar',
                'name' => 'priority_name',
                'length' => 10,
            ],
            [
                'type' => 'Varchar',
                'name' => 'message',
                'length' => 4096,
            ],
            [
                'type' => 'Text',
                'name' => 'message_extended',
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'file',
                'length' => 1024,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'class',
                'length' => 1024,
                'nullable' => true,
            ],
            [
                'type' => 'BigInteger',
                'name' => 'line',
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'function',
                'length' => 1024,
                'nullable' => true,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'log_pk',
            ],
            [
                'type' => 'Index',
                'column' => 'priority',
                'name' => 'log_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'class',
                'name' => 'log_ik2',
            ],
            [
                'type' => 'Index',
                'column' => 'function',
                'name' => 'log_ik3',
            ],
        ],
    ],
    'user' => [
        'commandName' => 'CreateTable',
        'tableName' => 'user',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'user_id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'Varchar',
                'name' => 'username',
                'length' => 255,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'email',
                'length' => 255,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'display_name',
                'length' => 50,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'password',
                'length' => 128,
            ],
            [
                'type' => 'Integer',
                'name' => 'state',
                'nullable' => true,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'user_id',
                'name' => 'user_pk',
            ],
            [
                'type' => 'UniqueKey',
                'column' => 'username',
                'name' => 'user_uk1',
            ],
            [
                'type' => 'UniqueKey',
                'column' => 'email',
                'name' => 'user_uk2',
            ],
        ],
    ],
    'user_role_linker' => [
        'commandName' => 'CreateTable',
        'tableName' => 'user_role_linker',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'user_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'role_id',
                'length' => 45,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => [
                    'user_id',
                    'role_id',
                ],
                'name' => 'user_role_linker_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'user_id',
                'name' => 'user_role_linker_fk1',
                'referenceTable' => 'user',
                'referenceColumn' => 'user_id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'user_id',
                'name' => 'user_role_linker_ik1',
            ],
        ],
    ],
    'user_settings' => [
        'commandName' => 'CreateTable',
        'tableName' => 'user_settings',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'user_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'locale',
                'length' => 20,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'user_id',
                'name' => 'user_settings_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'user_id',
                'name' => 'user_settings_fk1',
                'referenceTable' => 'user',
                'referenceColumn' => 'user_id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
        ],
    ],
    'user_languages' => [
        'commandName' => 'CreateTable',
        'tableName' => 'user_languages',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'user_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'locale',
                'length' => 20,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => [
                    'user_id',
                    'locale',
                ],
                'name' => 'user_languages_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'user_id',
                'name' => 'user_languages_fk1',
                'referenceTable' => 'user',
                'referenceColumn' => 'user_id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'user_id',
                'name' => 'user_languages_ik1',
            ],
        ],
    ],
    'team' => [
        'commandName' => 'CreateTable',
        'tableName' => 'team',
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
                'name' => 'locale',
                'length' => 255,
                'nullable' => true,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'team_pk',
            ],
        ],
    ],
    'team_member' => [
        'commandName' => 'CreateTable',
        'tableName' => 'team_member',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'user_id',
            ],
            [
                'type' => 'BigInteger',
                'name' => 'team_id',
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => [
                    'user_id',
                    'team_id',
                ],
                'name' => 'team_member_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'user_id',
                'name' => 'team_member_fk1',
                'referenceTable' => 'user',
                'referenceColumn' => 'user_id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'team_id',
                'name' => 'team_member_fk2',
                'referenceTable' => 'team',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'user_id',
                'name' => 'team_member_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'team_id',
                'name' => 'team_member_ik1',
            ],
        ],
    ],
];
