<?php
/**
 * Copy-paste this file to your config/autoload folder (don't forget to remove the .dist extension!)
 */

return [
    'zfc_rbac' => [
        /**
         * Key that is used to fetch the identity provider
         *
         * Please note that when an identity is found, it MUST implements the ZfcRbac\Identity\IdentityProviderInterface
         * interface, otherwise it will throw an exception.
         */
        // 'identity_provider' => 'ZfcRbac\Identity\AuthenticationIdentityProvider',

        /**
         * Set the guest role
         *
         * This role is used by the authorization service when the authentication service returns no identity
         */
        'guest_role' => 'guest',

        /**
         * Set the guards
         *
         * You must comply with the various options of guards. The format must be of the following format:
         *
         *      'guards' => [
         *          'ZfcRbac\Guard\RouteGuard' => [
         *              // options
         *          ]
         *      ]
         */
        'guards' => [
            'ZfcRbac\Guard\RoutePermissionsGuard' =>[
                'home' => [
                    'permissions' => [
                        'userBase',
                    ],
                ],
                'application*' => [
                    'permissions' => [
                        'userBase',
                    ],
                ],
                'settings*' => [
                    'permissions' => [
                        'userBase',
                    ],
                ],
                'zfcuser/login' => [
                    'permissions' => [
                        'guest',
                    ],
                ],
                'zfcuser/register' => [
                    'permissions' => [
                        'guest',
                    ],
                ],
                'zfcuser*' => [
                    'permissions' => [
                        'userBase',
                    ],
                ],
                'setup*' => [
                    'permissions' => [
                        'guest',
                    ],
                ],
                'users/add' => [
                    'permissions' => [
                        'users.add',
                    ],
                ],
                'users/delete' => [
                    'permissions' => [
                        'users.delete',
                    ],
                ],
                'users/edit' => [
                    'permissions' => [
                        'users.edit',
                    ],
                ],
                'users/userlanguages' => [
                    'permissions' => [
                        'users.edit',
                    ],
                ],
                'users*' => [
                    'permissions' => [
                        'users.view',
                    ],
                ],
                'team/add' => [
                    'permissions' => [
                        'team.view',
                        'team.add',
                    ],
                ],
                'team/edit' => [
                    'permissions' => [
                        'team.view',
                        'team.edit',
                    ],
                ],
                'team/delete' => [
                    'permissions' => [
                        'team.view',
                        'team.delete',
                    ],
                ],
                'team*' => [
                    'permissions' => [
                        'team.view',
                    ],
                ],
                'teammember/add' => [
                    'permissions' => [
                        'team.view',
                        'teammember.view',
                        'teammember.add',
                    ],
                ],
                'teammember/remove' => [
                    'permissions' => [
                        'team.view',
                        'teammember.view',
                        'teammember.remove',
                    ],
                ],
                'teammember*' => [
                    'permissions' => [
                        'team.view',
                        'teammember.view',
                    ],
                ],
                'app/add' => [
                    'permissions' => [
                        'app.view',
                        'app.add',
                    ],
                ],
                'app/edit' => [
                    'permissions' => [
                        'app.view',
                        'app.edit',
                    ],
                ],
                'app/delete' => [
                    'permissions' => [
                        'app.view',
                        'app.delete',
                    ],
                ],
                'app*' => [
                    'permissions' => [
                        'app.view',
                    ],
                ],
                'git*' => [
                    'permissions' => [
                        'app.view',
                        'git',
                    ],
                ],
                'sync*' => [
                    'permissions' => [
                        'app.view',
                        'sync',
                    ],
                ],
                'appresource/add' => [
                    'permissions' => [
                        'app.view',
                        'appresource.view',
                        'appresource.add',
                    ],
                ],
                'appresource/edit' => [
                    'permissions' => [
                        'app.view',
                        'appresource.view',
                        'appresource.edit',
                    ],
                ],
                'appresource/delete' => [
                    'permissions' => [
                        'app.view',
                        'appresource.view',
                        'appresource.delete',
                    ],
                ],
                'appresource*' => [
                    'permissions' => [
                        'app.view',
                        'appresource.view',
                    ],
                ],
                'appresourcefile/add' => [
                    'permissions' => [
                        'app.view',
                        'appresourcefile.view',
                        'appresourcefile.add',
                    ],
                ],
                'appresourcefile/edit' => [
                    'permissions' => [
                        'app.view',
                        'appresourcefile.view',
                        'appresourcefile.edit',
                    ],
                ],
                'appresourcefile/delete' => [
                    'permissions' => [
                        'app.view',
                        'appresourcefile.view',
                        'appresourcefile.delete',
                    ],
                ],
                'appresourcefile*' => [
                    'permissions' => [
                        'app.view',
                        'appresourcefile.view',
                    ],
                ],
                'translations/suggestionaccept' => [
                    'permissions' => [
                        'translations.view',
                        'suggestion.accept',
                    ],
                ],
                'translations/setnotificationstatus' => [
                    'permissions' => [
                        'translations.view',
                        'translations.setnotificationstatus',
                    ],
                ],
                'translations/suggestionaddedit' => [
                    'permissions' => [
                        'translations.view',
                        'suggestion.addeditdelete',
                    ],
                ],
                'translations/suggestiondelete' => [
                    'permissions' => [
                        'translations.view',
                        'suggestion.addeditdelete',
                    ],
                ],
                'translations/suggestionvote' => [
                    'permissions' => [
                        'translations.view',
                        'suggestion.vote',
                    ],
                ],
                'translations*' => [
                    'permissions' => [
                        'translations.view',
                    ],
                ],
            ]
        ],

        /**
         * As soon as one rule for either route or controller is specified, a guard will be automatically
         * created and will start to hook into the MVC loop.
         *
         * If the protection policy is set to DENY, then any route/controller will be denied by
         * default UNLESS it is explicitly added as a rule. On the other hand, if it is set to ALLOW, then
         * not specified route/controller will be implicitly approved.
         *
         * DENY is the most secure way, but it is more work for the developer
         */
        'protection_policy' => \ZfcRbac\Guard\GuardInterface::POLICY_DENY,

        /**
         * Configuration for role provider
         *
         * It must be an array that contains configuration for the role provider. The provider config
         * must follow the following format:
         *
         *      'ZfcRbac\Role\InMemoryRoleProvider' => [
         *          'role1' => [
         *              'children'    => ['children1', 'children2'], // OPTIONAL
         *              'permissions' => ['edit', 'read'] // OPTIONAL
         *          ]
         *      ]
         *
         * Supported options depend of the role provider, so please refer to the official documentation
         */
        'role_provider' => [
            'ZfcRbac\Role\InMemoryRoleProvider' => [
                'admin'     => [
                    'children'    => ['teamadmin'],
                    'permissions' => [
                        'app.viewAll',
                        'team.add',
                        'team.delete',
                        'team.viewAll',
                        'users.viewAll',
                    ],
                ],
                'teamadmin' => [
                    'children'    => ['superuser'],
                    'permissions' => [
                        'app.add',
                        'app.delete',
                        'app.edit',
                        'app.view',
                        'appresource.add',
                        'appresource.delete',
                        'appresource.edit',
                        'appresource.view',
                        'appresourcefile.add',
                        'appresourcefile.delete',
                        'appresourcefile.edit',
                        'appresourcefile.view',
                        'git',
                        'sync',
                        'team.edit',
                        'team.view',
                        'teammember.add',
                        'teammember.remove',
                        'teammember.view',
                        'translations.setnotificationstatus',
                        'users.add',
                        'users.delete',
                        'users.edit',
                        'users.view',
                    ],
                ],
                'superuser' => [
                    'children'    => ['user'],
                    'permissions' => [
                        'suggestion.accept',
                    ],
                ],
                'user'      => [
                    'permissions' => [
                        'suggestion.addeditdelete',
                        'suggestion.vote',
                        'translations.view',
                        'userBase',
                    ],
                ],
                'guest'     => [
                    'permissions' => [
                        'guest',
                    ],
                ],
            ]
        ],

        /**
         * Configure the unauthorized strategy. It is used to render a template whenever a user is unauthorized
         */
        'unauthorized_strategy' => [
            /**
             * Set the template name to render
             */
            // 'template' => 'error/403'
        ],

        /**
         * Configure the redirect strategy. It is used to redirect the user to another route when a user is
         * unauthorized
         */
        'redirect_strategy' => [
            /**
             * Enable redirection when the user is connected
             */
            'redirect_when_connected' => true,

            /**
             * Set the route to redirect when user is connected (of course, it must exist!)
             */
            'redirect_to_route_connected' => 'home',

            /**
             * Set the route to redirect when user is disconnected (of course, it must exist!)
             */
            'redirect_to_route_disconnected' => 'zfcuser/login',

            /**
             * If a user is unauthorized and redirected to another route (login, for instance), should we
             * append the previous URI (the one that was unauthorized) in the query params?
             */
            'append_previous_uri' => false,

            /**
             * If append_previous_uri option is set to true, this option set the query key to use when
             * the previous uri is appended
             */
            // 'previous_uri_query_key' => 'redirectTo'
        ],

        /**
         * Various plugin managers for guards and role providers. Each of them must follow a common
         * plugin manager config format, and can be used to create your custom objects
         */
        // 'guard_manager'               => [],
        // 'role_provider_manager'       => []
    ]
];
