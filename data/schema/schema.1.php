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
                // Currently known max length is 11 char.
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
                // Currently known max length for primary locale is 3 char.
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
    'app' => [
        'commandName' => 'CreateTable',
        'tableName' => 'app',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'BigInteger',
                'name' => 'team_id',
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'name',
                'length' => 255,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'path_to_res_folder',
                'length' => 4096,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'git_repository',
                'length' => 4096,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'git_username',
                'length' => 255,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'git_password',
                'length' => 1024,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'git_user',
                'length' => 255,
                'nullable' => true,
            ],
            [
                'type' => 'Varchar',
                'name' => 'git_email',
                'length' => 255,
                'nullable' => true,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'app_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'team_id',
                'name' => 'app_fk1',
                'referenceTable' => 'team',
                'referenceColumn' => 'id',
                'onDelete' => 'setNull',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'team_id',
                'name' => 'app_ik1',
            ],
        ],
    ],
    'app_resource' => [
        'commandName' => 'CreateTable',
        'tableName' => 'app_resource',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'BigInteger',
                'name' => 'app_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'name',
                'length' => 255,
            ],
            [
                'type' => 'Varchar',
                'name' => 'locale',
                'length' => 20,
            ],
            [
                // Currently known max length for primary locale is 3 char. Field isn't available in model.
                'type' => 'Varchar',
                'name' => 'primary_locale',
                'length' => 20,
            ],
            [
                'type' => 'Varchar',
                'name' => 'description',
                'length' => 255,
                'nullable' => true,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'app_resource_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'app_id',
                'name' => 'app_resource_fk1',
                'referenceTable' => 'app',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'UniqueKey',
                'column' => [
                    'app_id',
                    'name',
                ],
                'name' => 'app_resource_uk1',
            ],
            [
                'type' => 'Index',
                'column' => 'app_id',
                'name' => 'app_resource_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'primary_locale',
                'name' => 'app_resource_ik2',
            ],
        ],
    ],
    'app_resource_file' => [
        'commandName' => 'CreateTable',
        'tableName' => 'app_resource_file',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'BigInteger',
                'name' => 'app_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'name',
                'length' => 255,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'app_resource_file_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'app_id',
                'name' => 'app_resource_file_fk1',
                'referenceTable' => 'app',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'UniqueKey',
                'column' => [
                    'app_id',
                    'name',
                ],
                'name' => 'app_resource_file_uk1',
            ],
            [
                'type' => 'Index',
                'column' => 'app_id',
                'name' => 'app_resource_file_ik1',
            ],
        ],
    ],
    'resource_type' => [
        'commandName' => 'CreateTable',
        'tableName' => 'resource_type',
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
                'name' => 'name',
                'length' => 255,
            ],
            [
                'type' => 'Varchar',
                'name' => 'node_name',
                'length' => 255,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'resource_type_pk',
            ],
            [
                'type' => 'Index',
                'column' => 'name',
                'name' => 'resource_type_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'node_name',
                'name' => 'resource_type_ik2',
            ],
        ],
    ],
    'resource_type-insert1' => [
        'commandName' => 'Insert',
        'tableName' => 'resource_type',
        'columns' => [
            'id',
            'name',
            'node_name',
        ],
        'values' =>[
            1,
            'String',
            'string',
        ],
    ],
    'resource_file_entry' => [
        'commandName' => 'CreateTable',
        'tableName' => 'resource_file_entry',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'BigInteger',
                'name' => 'app_resource_file_id',
            ],
            [
                'type' => 'BigInteger',
                'name' => 'resource_type_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'name',
                'length' => 255,
            ],
            [
                'type' => 'Varchar',
                'name' => 'product',
                'length' => 255,
            ],
            [
                'type' => 'Varchar',
                'name' => 'description',
                'length' => 4096,
                'nullable' => true,
            ],
            [
                'type' => 'Integer',
                'name' => 'deleted',
            ],
            [
                'type' => 'Integer',
                'name' => 'translatable',
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'resource_file_entry_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'app_resource_file_id',
                'name' => 'resource_file_entry_fk1',
                'referenceTable' => 'app_resource_file',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'resource_type_id',
                'name' => 'resource_file_entry_fk2',
                'referenceTable' => 'resource_type',
                'referenceColumn' => 'id',
                'onDelete' => 'restrict',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'app_resource_file_id',
                'name' => 'resource_file_entry_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'resource_type_id',
                'name' => 'resource_file_entry_ik2',
            ],
            [
                'type' => 'Index',
                'column' => 'deleted',
                'name' => 'resource_file_entry_ik3',
            ],
            [
                'type' => 'Index',
                'column' => 'translatable',
                'name' => 'resource_file_entry_ik4',
            ],
        ],
    ],
    'entry_common' => [
        'commandName' => 'CreateTable',
        'tableName' => 'entry_common',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'BigInteger',
                'name' => 'app_resource_id',
            ],
            [
                'type' => 'BigInteger',
                'name' => 'resource_file_entry_id',
            ],
            [
                'type' => 'BigInteger',
                'name' => 'last_change',
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'entry_common_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'app_resource_id',
                'name' => 'entry_common_fk1',
                'referenceTable' => 'app_resource',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'resource_file_entry_id',
                'name' => 'entry_common_fk2',
                'referenceTable' => 'resource_file_entry',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'app_resource_id',
                'name' => 'entry_common_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'resource_file_entry_id',
                'name' => 'entry_common_ik2',
            ],
            [
                'type' => 'Index',
                'column' => 'last_change',
                'name' => 'entry_common_ik3',
            ],
        ],
    ],
    'entry_string' => [
        'commandName' => 'CreateTable',
        'tableName' => 'entry_string',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'entry_common_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'value',
                'length' => 20480,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'entry_common_id',
                'name' => 'entry_string_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'entry_common_id',
                'name' => 'entry_string_fk1',
                'referenceTable' => 'entry_common',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
        ],
    ],
    'suggestion' => [
        'commandName' => 'CreateTable',
        'tableName' => 'suggestion',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'id',
                'options' => [
                    'autoincrement' => true,
                ],
            ],
            [
                'type' => 'BigInteger',
                'name' => 'entry_common_id',
            ],
            [
                'type' => 'BigInteger',
                'name' => 'user_id',
            ],
            [
                'type' => 'BigInteger',
                'name' => 'last_change',
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'id',
                'name' => 'suggestion_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'entry_common_id',
                'name' => 'suggestion_fk1',
                'referenceTable' => 'entry_common',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'user_id',
                'name' => 'suggestion_fk2',
                'referenceTable' => 'user',
                'referenceColumn' => 'user_id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'entry_common_id',
                'name' => 'suggestion_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'user_id',
                'name' => 'suggestion_ik2',
            ],
            [
                'type' => 'Index',
                'column' => 'last_change',
                'name' => 'suggestion_ik3',
            ],
        ],
    ],
    'suggestion_string' => [
        'commandName' => 'CreateTable',
        'tableName' => 'suggestion_string',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'suggestion_id',
            ],
            [
                'type' => 'Varchar',
                'name' => 'value',
                'length' => 20480,
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => 'suggestion_id',
                'name' => 'suggestion_string_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'suggestion_id',
                'name' => 'suggestion_string_fk1',
                'referenceTable' => 'suggestion',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
        ],
    ],
    'suggestion_vote' => [
        'commandName' => 'CreateTable',
        'tableName' => 'suggestion_vote',
        'addColumn' => [
            [
                'type' => 'BigInteger',
                'name' => 'suggestion_id',
            ],
            [
                'type' => 'BigInteger',
                'name' => 'user_id',
            ],
        ],
        'addConstraint' =>[
            [
                'type' => 'PrimaryKey',
                'column' => [
                    'suggestion_id',
                    'user_id',
                ],
                'name' => 'suggestion_vote_pk',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'suggestion_id',
                'name' => 'suggestion_vote_fk1',
                'referenceTable' => 'suggestion',
                'referenceColumn' => 'id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'ForeignKey',
                'column' => 'user_id',
                'name' => 'suggestion_vote_fk2',
                'referenceTable' => 'user',
                'referenceColumn' => 'user_id',
                'onDelete' => 'cascade',
                'onUpdate' => 'cascade',
            ],
            [
                'type' => 'Index',
                'column' => 'suggestion_id',
                'name' => 'suggestion_vote_ik1',
            ],
            [
                'type' => 'Index',
                'column' => 'user_id',
                'name' => 'suggestion_vote_ik2',
            ],
        ],
    ],
];
