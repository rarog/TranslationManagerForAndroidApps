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
                'columns' => 'id',
            ],
            [
                'type' => 'Index',
                'columns' => 'priority',
                'name' => 'log_ik1',
            ],
            [
                'type' => 'Index',
                'columns' => 'class',
                'name' => 'log_ik2',
            ],
            [
                'type' => 'Index',
                'columns' => 'function',
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
                'columns' => 'user_id',
            ],
            [
                'type' => 'UniqueKey',
                'columns' => 'username',
            ],
            [
                'type' => 'UniqueKey',
                'columns' => 'email',
            ],
        ],
    ],
];
