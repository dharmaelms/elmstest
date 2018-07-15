<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;

class PermissionMasterTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'permissions_master';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('id');
        });

        //VISITOR
        DB::collection($collection)->insert([
            'id' => 1,
            'module' => 'Visitor',
            'slug' => 'visitor',
            'portal' => [
                [
                    'name' => 'Browse Site',
                    'slug' => 'browse-site',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Ecommerce',
                    'slug' => 'ecommerce',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //USER
        DB::collection($collection)->insert([
            'module' => 'User',
            'slug' => 'user',
            'id' => 2,
            'portal' => [
                [
                    'name' => 'Login',
                    'slug' => 'login',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Register',
                    'slug' => 'register',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Forgot Password',
                    'slug' => 'forgot-password',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'View Profile',
                    'slug' => 'view-profile',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Profile',
                    'slug' => 'edit-profile',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Change Password',
                    'slug' => 'change-password',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'My Activity',
                    'slug' => 'my_activity',
                    'description' => '',
                    'is_default' => false
                ]

            ],
            'admin' => [
                [
                    'name' => 'List User',
                    'slug' => 'list-user',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View User',
                    'slug' => 'view-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add User',
                    'slug' => 'add-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit User',
                    'slug' => 'edit-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete User',
                    'slug' => 'delete-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk User Delete',
                    'slug' => 'bulk-user-delete',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk User Activate',
                    'slug' => 'bulk-user-activate',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk User Inactivate',
                    'slug' => 'bulk-user-inactivate',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk User Import',
                    'slug' => 'import-users',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk User Export',
                    'slug' => 'export-users',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'User Import History',
                    'slug' => 'userimport-history',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User Goup',
                    'slug' => 'assign-usergroup',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Channel',
                    'slug' => 'assign-contentfeed',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'View Profile',
                    'slug' => 'view-profile',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Profile',
                    'slug' => 'edit-profile',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //USER GROUP
        DB::collection($collection)->insert([
            'module' => 'User Group',
            'slug' => 'user-group',
            'id' => 3,
            'admin' => [
                [
                    'name' => 'List User Group',
                    'slug' => 'listuser-group',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View User Group',
                    'slug' => 'viewuser-group',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add User Group',
                    'slug' => 'adduser-group',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit User Group',
                    'slug' => 'edituser-group',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete User Group',
                    'slug' => 'deleteuser-group',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk User Group Delete',
                    'slug' => 'bulk-usergroup-delete',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User',
                    'slug' => 'assign-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Channel',
                    'slug' => 'assign-contentfeed',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //ROLE
        DB::collection($collection)->insert([
            'module' => 'Role',
            'slug' => 'role',
            'id' => 4,
            'admin' => [
                [
                    'name' => 'List Role',
                    'slug' => 'list-role',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Role',
                    'slug' => 'view-role',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Role',
                    'slug' => 'add-role',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Role',
                    'slug' => 'edit-role',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Role',
                    'slug' => 'delete-role',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //CATEGORY
        DB::collection($collection)->insert([
            'module' => 'Category',
            'slug' => 'category',
            'id' => 5,
            'portal' => [
                [
                    'name' => 'Catalog',
                    'slug' => 'catalog',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Filter',
                    'slug' => 'filter',
                    'description' => '',
                    'is_default' => false
                ]
            ],
            'admin' => [
                [
                    'name' => 'List Category',
                    'slug' => 'list-category',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Category',
                    'slug' => 'view-category',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Category',
                    'slug' => 'add-category',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Category',
                    'slug' => 'edit-category',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Category',
                    'slug' => 'delete-category',
                    'description' => '',
                    'is_default' => false
                ], [
                    'name' => 'Assign Channel',
                    'slug' => 'assign-contentfeed',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //SEARCH
        DB::collection($collection)->insert([
            'module' => 'Search',
            'slug' => 'search',
            'id' => 6,
            'portal' => [
                [
                    'name' => 'Basic Search',
                    'slug' => 'basic-search',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Advanced Search',
                    'slug' => 'advanced-search',
                    'description' => '',
                    'is_default' => true
                ]
            ]
        ]);

        //PROGRAM
        DB::collection($collection)->insert([
            'module' => 'Program',
            'slug' => 'program',
            'id' => 7,
            'portal' => [
                [
                    'name' => 'Watch Now',
                    'slug' => 'watch-now',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'My Channels',
                    'slug' => 'my-feeds',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Favourites',
                    'slug' => 'favourites',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Other Channels',
                    'slug' => 'other-feeds',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //EVENT
        DB::collection($collection)->insert([
            'module' => 'Event',
            'slug' => 'event',
            'id' => 8,
            'portal' => [
                [
                    'name' => 'Access Event',
                    'slug' => 'access-event',
                    'description' => '',
                    'is_default' => true
                ], [
                    'name' => 'Access Recorded Event',
                    'slug' => 'access-recorded-event',
                    'description' => '',
                    'is_default' => false
                ]
            ],
            'admin' => [
                [
                    'name' => 'List Event',
                    'slug' => 'list-event',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Event',
                    'slug' => 'view-event',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Event',
                    'slug' => 'add-event',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Event',
                    'slug' => 'edit-event',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Event',
                    'slug' => 'delete-event',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Channel',
                    'slug' => 'assign-content-feed',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User',
                    'slug' => 'assign-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User Group',
                    'slug' => 'assign-usergroup',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //ASSESSMENT
        DB::collection($collection)->insert([
            'module' => 'Assessment',
            'slug' => 'assessment',
            'id' => 9,
            'portal' => [
                [
                    'name' => 'Access Quiz',
                    'slug' => 'access-quiz',
                    'description' => '',
                    'is_default' => true
                ]
            ],
            'admin' => [
                [
                    'name' => 'List Quiz',
                    'slug' => 'list-quiz',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Quiz',
                    'slug' => 'view-quiz',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Quiz',
                    'slug' => 'add-quiz',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Quiz',
                    'slug' => 'edit-quiz',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Quiz',
                    'slug' => 'delete-quiz',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Quiz To Channel',
                    'slug' => 'assign-quiz-content-feed',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Quiz To User',
                    'slug' => 'assign-quiz-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Quiz To User Group',
                    'slug' => 'assign-quiz-usergroup',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'List Questionbank',
                    'slug' => 'list-questionbank',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'View Questionbank',
                    'slug' => 'view-questionbank',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Questionbank',
                    'slug' => 'add-questionbank',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Questionbank',
                    'slug' => 'edit-questionbank',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Questionbank',
                    'slug' => 'delete-questionbank',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Questionbank To User',
                    'slug' => 'assign-questionbank-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign questionbank To User Group',
                    'slug' => 'assign-questionbank-usergroup',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Question',
                    'slug' => 'add-question',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Question',
                    'slug' => 'edit-question',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Question',
                    'slug' => 'delete-question',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //ANNOUNCEMENTS
        DB::collection($collection)->insert([
            'module' => 'Announcements',
            'slug' => 'announcements',
            'id' => 10,
            'portal' => [
                [
                    'name' => 'View Announcement',
                    'slug' => 'view-announcement',
                    'description' => '',
                    'is_default' => true
                ]
            ],
            'admin' => [
                [
                    'name' => 'List Announcement',
                    'slug' => 'list-announcement',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Add Announcement',
                    'slug' => 'add-announcement',
                    'description' => '',
                    'is_default' => false
                ], [
                    'name' => 'View Announcement',
                    'slug' => 'view-announcement',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Announcement',
                    'slug' => 'edit-announcement',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Announcement',
                    'slug' => 'delete-announcement',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Media',
                    'slug' => 'assign-media',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Channel',
                    'slug' => 'assign-contentfeed',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User',
                    'slug' => 'assign-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User Group',
                    'slug' => 'assign-usergroup',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Event',
                    'slug' => 'assign-event',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //PAGES
        DB::collection($collection)->insert([
            'module' => 'Pages',
            'slug' => 'pages',
            'id' => 11,
            'portal' => [
                [
                    'name' => 'Access Page',
                    'slug' => 'access-page',
                    'description' => '',
                    'is_default' => true
                ]
            ],
            'admin' => [
                [
                    'name' => 'List Page',
                    'slug' => 'list-page',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Page',
                    'slug' => 'view-page',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Page',
                    'slug' => 'edit-page',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Publish Page',
                    'slug' => 'publish-page',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Page',
                    'slug' => 'delete-page',
                    'description' => '',
                    'is_default' => false
                ]
            ]

        ]);

        //REPORTS
        DB::collection($collection)->insert([
            'module' => 'Report',
            'slug' => 'report',
            'id' => 12,
            'portal' => [
                [
                    'name' => 'View Reports',
                    'slug' => 'view-reports',
                    'description' => '',
                    'is_default' => true
                ]
            ],
            'admin' => [
                [
                    'name' => 'Channel Comparison',
                    'slug' => 'feed-comparison',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Post Split',
                    'slug' => 'packet-split',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Most Viewed Channels',
                    'slug' => 'most-viewed-feeds',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Most Viewed Posts By Channels',
                    'slug' => 'most-viewed-packets-by-feeds',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'View Stats Of Announcements',
                    'slug' => 'view-stats-of-announcements',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Most Liked Posts By Channel',
                    'slug' => 'most-liked-packets-by-feed',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Access Request For Channels',
                    'slug' => 'access-request-for-feeds',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //SETTINGS
        DB::collection($collection)->insert([
            'module' => 'Setting',
            'slug' => 'setting',
            'id' => 13,
            'admin' => [
                [
                    'name' => 'Configuration',
                    'slug' => 'configuration',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Manage Cache',
                    'slug' => 'manage-cache',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'List Site Setting',
                    'slug' => 'list-sitesetting',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Edit Site Setting',
                    'slug' => 'edit-sitesetting',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //DAMS
        DB::collection($collection)->insert([
            'module' => 'DAMs',
            'slug' => 'dams',
            'id' => 14,
            'admin' => [
                [
                    'name' => 'List Media',
                    'slug' => 'list-media',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Media',
                    'slug' => 'view-media',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Media',
                    'slug' => 'add-media',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Media',
                    'slug' => 'edit-media',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Media',
                    'slug' => 'delete-media',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk Import',
                    'slug' => 'bulk-import',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User',
                    'slug' => 'assign-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User Group',
                    'slug' => 'assign-usergroup',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Bulk Delete',
                    'slug' => 'bulk-delete',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //CHANNELS
        DB::collection($collection)->insert([
            'module' => 'Channels',
            'slug' => 'content-feeds',
            'id' => 15,
            'admin' => [
                [
                    'name' => 'List Channels',
                    'slug' => 'list-content-feeds',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Channels',
                    'slug' => 'view-content-feeds',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Channels',
                    'slug' => 'add-content-feeds',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Channels',
                    'slug' => 'edit-content-feeds',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Channels',
                    'slug' => 'delete-content-feeds',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign Categories',
                    'slug' => 'assign-categories',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User',
                    'slug' => 'assign-user',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Assign User Group',
                    'slug' => 'assign-usergroup',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Manage Posts',
                    'slug' => 'manage-packets',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Access Request Channels',
                    'slug' => 'access-request-content-feeds',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);

        //MANAGE SITE
        DB::collection($collection)->insert([
            'module' => 'Manage Site',
            'slug' => 'manage-site',
            'id' => 16,
            'portal' => [
                [
                    'name' => 'View Faq',
                    'slug' => 'view-faq',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Static Page',
                    'slug' => 'view-staticpage',
                    'description' => '',
                    'is_default' => true
                ]
            ],
            'admin' => [
                [
                    'name' => 'List Faq',
                    'slug' => 'list-faq',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'View Faq',
                    'slug' => 'view-faq',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Edit Faq',
                    'slug' => 'edit-faq',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Faq',
                    'slug' => 'add-faq',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Faq',
                    'slug' => 'delete-faq',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'List Staticpage',
                    'slug' => 'list-staticpage',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Edit Staticpage',
                    'slug' => 'edit-staticpage',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Add Staticpage',
                    'slug' => 'add-staticpage',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'View Staticpage',
                    'slug' => 'view-staticpage',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Delete Staticpage',
                    'slug' => 'delete-staticpage',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'List Newsletter',
                    'slug' => 'list-newsletter',
                    'description' => '',
                    'is_default' => true
                ],
                [
                    'name' => 'Delete Newsletter',
                    'slug' => 'delete-newsletter',
                    'description' => '',
                    'is_default' => false
                ],
                [
                    'name' => 'Export Newsletter',
                    'slug' => 'export-newsletter',
                    'description' => '',
                    'is_default' => false
                ]
            ]
        ]);
    }
}
