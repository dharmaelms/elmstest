<?php

use App\Enums\ERP\ERPPermission;
use App\Enums\Package\PackagePermission;
use App\Enums\Program\ChannelPermission;
use App\Enums\RolesAndPermissions\RolePermission;
use App\Enums\User\UserPermission;
use App\Enums\UserGroup\UserGroupPermission;
use App\Enums\Category\CategoryPermission;
use App\Enums\Event\EventPermission;
use App\Enums\DAMS\DAMSPermission;
use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\Course\CoursePermission;
use App\Enums\FlashCard\FlashCardPermission;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\ManageSite\ManageSitePermission;
use App\Enums\ECommerce\ECommercePermission;
use App\Enums\HomePage\HomePagePermission;
use App\Enums\Country\CountryPermission;
use App\Enums\Survey\SurveyPermission;
use App\Enums\Assignment\AssignmentPermission;

use Illuminate\Database\Seeder;
use App\Model\Module\Entity\Module;
use App\Model\RolesAndPermissions\Entity\Permission;
use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;

class PermissionSeeder extends Seeder
{
    /**
     * Define collection associated with the seeder
     *
     * @var string $collection
     */
    private $collection = "permissions";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Removing existing collection
        Schema::drop($this->collection);

        /**
         * Define module and permissions mapping data
         *
         * @var array $module_permissions_mapping
         */
        $module_permissions_mapping = [
            ModuleEnum::USER => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List User",
                        "slug"=> UserPermission::LIST_USER,
                        "description" => "User with list user permission can view list of users registered
                                            in the application",
                        "is_default"=> true,
                    ],
                    [
                        "name"=>"View User",
                        "slug" => UserPermission::VIEW_USER,
                        "description" => "User with view user permission will be able to see any user details",
                        "is_default"=> false,
                    ],
                    [
                        "name" => "Add User",
                        "slug" => UserPermission::ADD_USER,
                        "description "=> "User with add user permission will be able to add new user to the system",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit User",
                        "slug" => UserPermission::EDIT_USER,
                        "description" => "User with view user permission will be able to edit any user details",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete User",
                        "slug" => UserPermission::DELETE_USER,
                        "description" => "User with view user permission will be able to delete any user",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Bulk User Import",
                        "slug" => UserPermission::IMPORT_USERS,
                        "description" => "User with this permission will be able to import
                         multiple users in the system",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Bulk User Export",
                        "slug" => UserPermission::EXPORT_USERS,
                        "description" => "User with this permission will be able to export users from the system",
                        "is_default" => false,
                    ],
                ],
            ],
            ModuleEnum::USER_GROUP => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List User Group",
                        "slug" => UserGroupPermission::LIST_USER_GROUP,
                        "description" => "User with list user group permission can view user groups created in the
                                            application",
                        "is_default" => true,
                    ],
                    [
                        "name" => "View User Group",
                        "slug" => UserGroupPermission::VIEW_USER_GROUP,
                        "description" => "User with this permission will be able to view user group details",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Add User Group",
                        "slug" => UserGroupPermission::ADD_USER_GROUP,
                        "description" => "User with this permission will be able to add new user group",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit User Group",
                        "slug" => UserGroupPermission::EDIT_USER_GROUP,
                        "description" => "User with this permission will be able to edit user group",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete User Group",
                        "slug" => UserGroupPermission::DELETE_USER_GROUP,
                        "description" => "User with this permission will be able to delete user group",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Assign User",
                        "slug" => UserGroupPermission::USER_GROUP_ASSIGN_USER,
                        "description" => "",
                        "is_default" => false,
                    ],
                ]
            ],
            ModuleEnum::ROLE => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List Role",
                        "slug" => RolePermission::LIST_ROLE,
                        "description" => "User with list role permission can view available roles in application",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Add Role",
                        "slug" => RolePermission::ADD_ROLE,
                        "description" => "User with add role permission will be able to add new role in the system",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit Role",
                        "slug" => RolePermission::EDIT_ROLE,
                        "description" => "User with edit role permission will be able to edit custom roles
                                            in the system",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete Role",
                        "slug" => RolePermission::DELETE_ROLE,
                        "description" => "User with edit role permission will be able to delete custom roles
                                            in the system",
                        "is_default" => false,
                    ]
                ]
            ],
            ModuleEnum::CHANNEL => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'List Channels',
                        'slug' => ChannelPermission::LIST_CHANNEL,
                        'description' => 'User who has list channel permission will be able to view list of channels
                                          that are assigned to him. Site admin\'s can access all the channels created
                                          in the application',
                        'is_default' => true
                    ],
                    [
                        'name' => 'View Channel Details',
                        'slug' => ChannelPermission::VIEW_CHANNEL,
                        'description' => 'This permission allows the user to view channel details',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Add Channel',
                        'slug' => ChannelPermission::ADD_CHANNEL,
                        'description' => 'This permission allows user to add new channels',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Channel',
                        'slug' => ChannelPermission::EDIT_CHANNEL,
                        'description' => 'This permission allows user to edit channel details',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Channel',
                        'slug' => ChannelPermission::DELETE_CHANNEL,
                        'description' => 'This permission allows user to delete channel',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Assign Category',
                        'slug' => ChannelPermission::CHANNEL_ASSIGN_CATEGORY,
                        'description' => 'User with this permission will be able to assign category to channels',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Assign User',
                        'slug' => ChannelPermission::CHANNEL_ASSIGN_USER,
                        'description' => 'User with this permission will be able to assign user to channel',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Assign User Group',
                        'slug' => ChannelPermission::CHANNEL_ASSIGN_USER_GROUP,
                        'description' => 'User with this permission will be able to assign user groups to channel',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Manage Channel Questions',
                        'slug' => ChannelPermission::MANAGE_CHANNEL_QUESTION,
                        'description' => 'User with manage posts permission will be able to view, answer and hide
                                          questions inside a channel',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Manage Posts',
                        'slug' => ChannelPermission::MANAGE_CHANNEL_POST,
                        'description' => 'User with manage posts permission will be able to add, edit, delete posts
                                          inside a channel',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Manage access request for channels',
                        'slug' => ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST,
                        'description' => 'User with this permission will be able to manage access requests for his
                                          channels.',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Export Channel',
                        'slug' => ChannelPermission::EXPORT_CHANNEL,
                        'is_default' => true
                    ],
                ]
            ],
            ModuleEnum::PACKAGE => [
                PermissionType::ADMIN => [
                    [
                        "name" => "Add Package",
                        "slug" => PackagePermission::ADD_PACKAGE,
                        "description" => "User with this permission will be able to add new packages",
                        "is_default" => false,
                    ],
                    [
                        "name" => "List Packages",
                        "slug" => PackagePermission::LIST_PACKAGES,
                        "description" => "User with this permission will be able to view packages list",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Edit Package",
                        "slug" => PackagePermission::EDIT_PACKAGE,
                        "description" => "User with this permission will be able to edit package meta information",
                        "is_default" => false,
                    ],
                    [
                        "name" => "View Package Details",
                        "slug" => PackagePermission::VIEW_PACKAGE_DETAILS,
                        "description" => "User with this permission will be able to view package meta information",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Delete package",
                        "slug" => PackagePermission::DELETE_PACKAGE,
                        "description" => "User with this permission will be able to delete package",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Manage package channels",
                        "slug" => PackagePermission::MANAGE_PACKAGE_CHANNELS,
                        "description" => "User with this permission will be able to assign channels to package and
                                          un-assign channels from package",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Manage package users",
                        "slug" => PackagePermission::MANAGE_PACKAGE_USERS,
                        "description" => "User with this permission will be able to assign users to package and
                                          un-assign users from package",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Manage package user groups",
                        "slug" => PackagePermission::MANAGE_PACKAGE_USER_GROUPS,
                        "description" => "User with this permission will be able to assign user groups to package and
                                          un-assign user groups from package",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Manage package categories",
                        "slug" => PackagePermission::MANAGE_PACKAGE_CATEGORIES,
                        "description" => "User with this permission will be able to assign categories to package and
                                          un-assign categories from package",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Manage package subscriptions",
                        "slug" => PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS,
                        "description" => "User with this permission will be able to create, edit and delete package
                                          subscriptions",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Manage package tabs",
                        "slug" => PackagePermission::MANAGE_PACKAGE_TABS,
                        "description" => "User with this permission will be able to create, edit and delete package
                                          tabs",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Export package with users",
                        "slug" => PackagePermission::EXPORT_PACKAGE_WITH_USERS,
                        "description" => "User with this permission will be able to export packages with associated user
                                          details",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Export package with user groups",
                        "slug" => PackagePermission::EXPORT_PACKAGE_WITH_USER_GROUPS,
                        "description" => "User with this permission will be able to export packages with associated user
                                          group details",
                        "is_default" => false,
                    ],
                ],
            ],
            ModuleEnum::CATEGORY => [
                PermissionType::ADMIN => [
                    [
                        "name" => "List Category",
                        "slug" => CategoryPermission::LIST_CATEGORY,
                        "description" => "User with list category permission will be able to view list of categories
                                          available in the application",
                        "is_default" => true,
                    ],
                    [
                        "name" => "View Category",
                        "slug" => CategoryPermission::VIEW_CATEGORY,
                        "description"=>"User with view category permission will be able to view the categories available
                                        in the application",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Add Category",
                        "slug" => CategoryPermission::ADD_CATEGORY,
                        "description" => "User with add category permission user can able to add the new categories in application",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit Category",
                        "slug" => CategoryPermission::EDIT_CATEGORY,
                        "description" => "User with edit category permission will be able to edit the categories available
                                          in the application",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete Category",
                        "slug" => CategoryPermission::DELETE_CATEGORY,
                        "description" => "User with delete category permission will be able to delete the categories
                                          available in the application",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign Channel",
                        "slug"=> CategoryPermission::ASSIGN_CHANNEL,
                        "description"=>"User with this permission will be able to assign categories to channel",
                        "is_default" => false,
                    ]
                ],
            ],
            ModuleEnum::EVENT => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List Event",
                        "slug" => EventPermission::LIST_EVENT,
                        "description"=>"User with list event permission will be able to view list of events that are
                                        created by him or related programs he is assigned to.",
                        "is_default" => true,
                    ],
                    [
                        "name"=>"View Event",
                        "slug"=>EventPermission::VIEW_EVENT,
                        "description"=>"User with view event permissions will have the ability to view all the events
                                        created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Add Event",
                        "slug"=>EventPermission::ADD_EVENT,
                        "description"=>"User with add event permission will have the ability to create new events",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Edit Event",
                        "slug"=>EventPermission::EDIT_EVENT,
                        "description"=>"User with edit event permissons will have the ability to edit all the events
                                        created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Delete Event",
                        "slug"=>EventPermission::DELETE_EVENT,
                        "description"=>"User with delete event permissons will have the ability to delete all the
                         events created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign Channel",
                        "slug"=>EventPermission::ASSIGN_CHANNEL,
                        "description"=>"User with assign channel permissons will have the ability to assign event to
                                        channel that events created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign User",
                        "slug"=>EventPermission::ASSIGN_USER,
                        "description"=>"User with assign user permissons will have the ability to assign event to user
                                        that events created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign User Group",
                        "slug"=>EventPermission::ASSIGN_USER_GROUP,
                        "description"=>"User with assign usergroup permissons will have the ability to assign event to
                                        usergroup that events created by him or related programs he is assigned to",
                        "is_default" => false,
                    ]
                ],
            ],
            ModuleEnum::ASSESSMENT => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List Quiz",
                        "slug"=>AssessmentPermission::LIST_QUIZ,
                        "description"=>"User with list quiz permission will be able to view list of quiz that are
                                        created by him or related programs he is assigned to.",
                        "is_default" => true,
                    ],
                    [
                        "name"=>"View Quiz",
                        "slug"=>AssessmentPermission::VIEW_QUIZ,
                        "description"=>"User with view quiz permissions will have the ability to view all the quiz
                                        created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Add Quiz",
                        "slug"=>AssessmentPermission::ADD_QUIZ,
                        "description"=>"User with add quiz permission will have the ability to create new quiz",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Edit Quiz",
                        "slug"=>AssessmentPermission::EDIT_QUIZ,
                        "description"=>"User with edit quiz permissons will have the ability to view all the quiz
                                        created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Delete Quiz",
                        "slug"=>AssessmentPermission::DELETE_QUIZ,
                        "description"=>"User with delete event permissons will have the ability to delete all the
                                        events created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign Quiz To Channel",
                        "slug"=>AssessmentPermission::QUIZ_ASSIGN_CHANNEL,
                        "description"=>"User with assign quiz to channel permissons will have the ability to assign
                                        quiz to channel that quiz are created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign Quiz To User",
                        "slug"=>AssessmentPermission::QUIZ_ASSIGN_USER,
                        "description"=>"User with assign quiz to user permissons will have the ability to assign
                                        quiz to user that quiz are created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign Quiz To User Group",
                        "slug"=>AssessmentPermission::QUIZ_ASSIGN_USER_GROUP,
                        "description"=>"User with assign quiz to usergroup permissons will have the ability to assign
                                        quiz to usergroup that quiz are created by him or related programs he is
                                        assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"List Questionbank",
                        "slug"=>AssessmentPermission::LIST_QUESTION_BANK,
                        "description"=>"List questionbank permissions can list all the question bank",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Add Questionbank",
                        "slug"=>AssessmentPermission::ADD_QUESTION_BANK,
                        "description"=>"Add questionbank permissions can Add the new question bank",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Edit Questionbank",
                        "slug"=>AssessmentPermission::EDIT_QUESTION_BANK,
                        "description"=>"Edit questionbank permissions can Edit the question bank",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Delete Questionbank",
                        "slug"=>AssessmentPermission::DELETE_QUESTION_BANK,
                        "description"=>"Delete questionbank permissions can delete the question bank",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Add Question",
                        "slug"=>AssessmentPermission::ADD_QUESTION,
                        "description"=>"Add Question permissions can Add the new question",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Edit Question",
                        "slug"=>AssessmentPermission::EDIT_QUESTION,
                        "description"=>"Edit Question permissions can edit the question",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Delete Question",
                        "slug"=>AssessmentPermission::DELETE_QUESTION,
                        "description"=>"Delete Question permissions can delete the question",
                        "is_default" => false,
                    ],
                    [
                      "name"=>"Import Question Bank",
                      "slug"=>AssessmentPermission::IMPORT_QUESTION_BANK,
                      "description"=>"Delete Question permissions can delete the question",
                      "is_default" => false
                    ],
                    [
                      "name"=>"Import Quiz",
                      "slug"=>AssessmentPermission::IMPORT_QUIZ,
                      "description"=>"Import quiz permissions can import new quiz",
                      "is_default"=> false
                    ],
                    [
                      "name"=>"Export Quiz",
                      "slug"=>AssessmentPermission::EXPORT_QUIZ,
                      "description"=>"Export quiz permissions can Export all the quiz",
                      "is_default"=> true
                    ],
                    [
                      "name"=>"Export Question Bank",
                      "slug"=>AssessmentPermission::EXPORT_QUESTION_BANK,
                      "description"=>"Export question bank permissions can Export all the question bank",
                      "is_default"=> true
                    ]
                ],
            ],
            ModuleEnum::ANNOUNCEMENT => [
                PermissionType::ADMIN => [
                    [
                        "name" => "List Announcement",
                        "slug" => AnnouncementPermission::LIST_ANNOUNCEMENT,
                        "description" => "User with list announcement permission will be able to view list of announcements that are created by him or announcements that are related to programs the user is assigned to.",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Add Announcement",
                        "slug" => AnnouncementPermission::ADD_ANNOUNCEMENT,
                        "description"=> "User with this permission will have the ability to create new announcement",
                        "is_default" => false,
                    ],
                    [
                        "name" => "View Announcement",
                        "slug" => AnnouncementPermission::VIEW_ANNOUNCEMENT,
                        "description"=>"User with this permission will have the ability to view announcement",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit Announcement",
                        "slug" => AnnouncementPermission::EDIT_ANNOUNCEMENT,
                        "description" => "User with this permission will have the ability to edit announcement",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete Announcement",
                        "slug" => AnnouncementPermission::DELETE_ANNOUNCEMENT,
                        "description" => "User with this permission will have the ability to delete announcement",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Assign Media",
                        "slug" => AnnouncementPermission::ASSIGN_MEDIA,
                        "description" => "User with this permission will have the ability to assign media to an announcement",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Assign Channel",
                        "slug" => AnnouncementPermission::ASSIGN_CHANNEL,
                        "description" => "User with this permission will have the ability to assign channel to announcement",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Assign User",
                        "slug" => AnnouncementPermission::ASSIGN_USER,
                        "description" => "User with this permission will have the ability to assign user to announcement",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Assign User Group",
                        "slug" => AnnouncementPermission::ASSIGN_USERGROUP,
                        "description" => "User with this permission will have the ability to assign usergroup to announcement",
                        "is_default" => false,
                    ]
                ],
            ],
            ModuleEnum::DAMS => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'List Media',
                        'slug' => DAMSPermission::LIST_MEDIA,
                        'description' => 'User with list media permission will be able to view list of media\'s that
                                          are created by him or media related to programs the user is assigned to.',
                        'is_default' => true
                    ],
                    [
                        'name' => 'View Media',
                        'slug' => DAMSPermission::VIEW_MEDIA,
                        'description' => 'User with view media permission will be able to preview media details before
                                         assigning to any entity',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Add Media',
                        'slug' => DAMSPermission::ADD_MEDIA,
                        'description' => '',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Media',
                        'slug' => DAMSPermission::EDIT_MEDIA,
                        'description' => 'User with edit media permission will be able to edit media details',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Media',
                        'slug' => DAMSPermission::DELETE_MEDIA,
                        'description' => 'User with delete media permission will be able to delete media',
                        'is_default' => false
                    ],
                ]
            ],
            ModuleEnum::REPORT => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'View Report',
                        'slug' => ReportPermission::VIEW_REPORT,
                        'description' => 'Lists Channel completion and performance reports',
                        'is_default' => true
                    ],
                    [
                        'name' => 'Export Report',
                        'slug' => ReportPermission::EXPORT_REPORT,
                        'description' => 'Channel completion and performance reports exports reports',
                        'is_default' => true
                    ]
                ]
            ],
            ModuleEnum::COUNTRY => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'List Country',
                        'slug' => CountryPermission::LIST_COUNTRY,
                        'description' => 'User with list country permission will able to list existing countries',
                        'is_default' => true
                    ],
                    [
                        'name' => 'Add Country',
                        'slug' => CountryPermission::ADD_COUNTRY,
                        'description' => 'User with add country permission will able to add new country',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Country',
                        'slug' => CountryPermission::EDIT_COUNTRY,
                        'description' => 'User with edit country permission will able to edit existing countries',
                        'is_default' => true
                    ]
                ]
            ],
            ModuleEnum::E_COMMERCE => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'List Order',
                        'slug' => ECommercePermission::LIST_ORDER,
                        'description' => 'User with lists order permission will be able to list order ',
                        'is_default' => false
                    ],
                    [
                        'name' => 'List Promocode',
                        'slug' => ECommercePermission::LIST_PROMO_CODE,
                        'description' => 'User with list promocode permission will be able to list promocode',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Add Promocode',
                        'slug' => ECommercePermission::ADD_PROMO_CODE,
                        'description' => 'User with add promocode permission will be able to add promocode',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Promocode',
                        'slug' => ECommercePermission::EDIT_PROMO_CODE,
                        'description' => 'User with edit promocode permission will be able to edit promocode',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Promocode',
                        'slug' => ECommercePermission::DELETE_PROMO_CODE,
                        'description' => 'User with delete promocode permission will be able to delete promocode',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Export Promocode',
                        'slug' => ECommercePermission::EXPORT_PROMO_CODE,
                        'description' => 'User with export promocode permission will be able to export promocode',
                        'is_default' => false
                    ],
                    [
                        'name' => 'View Order',
                        'slug' => ECommercePermission::VIEW_ORDER,
                        'description' => 'User with view order permission will be able to view order',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Order',
                        'slug' => ECommercePermission::EDIT_ORDER,
                        'description' => 'User with edit order permission will be able to edit order',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Export order',
                        'slug' => ECommercePermission::EXPORT_ORDER,
                        'description' => 'User with export order permission will be able to export order',
                        'is_default' => false
                    ]

                ]
            ],
            ModuleEnum::COURSE => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'List Course',
                        'slug' => CoursePermission::LIST_COURSE,
                        'description' => 'User who has list course permission will be able to view list of course
                                          that are assigned to him. Site admin\'s can access all the course created
                                          in the application',
                        'is_default' => true
                    ],
                    [
                        'name' => 'View Course',
                        'slug' => CoursePermission::VIEW_COURSE,
                        'description' => 'This permission allows the user to view course details',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Add Course',
                        'slug' => CoursePermission::ADD_COURSE,
                        'description' => 'This permission allows user to add new course',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Course',
                        'slug' => CoursePermission::EDIT_COURSE,
                        'description' => 'This permission allows user to edit course details',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Course',
                        'slug' => CoursePermission::DELETE_COURSE,
                        'description' => 'This permission allows user to delete course',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Assign Category',
                        'slug' => CoursePermission::ASSIGN_CATEGORY,
                        'description' => 'User with this permission will be able to assign category to course',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Manage Course Post',
                        'slug' => CoursePermission::MANAGE_COURSE_POST,
                        'description' => 'User with manage posts permission will be able to add, edit, delete posts
                                          inside a course',
                        'is_default' => false
                    ],
                    [
                        'name' => 'List Batch',
                        'slug' => CoursePermission::LIST_BATCH,
                        'description' => 'User who has list batch permission will be able to view list of batches
                                          that are assigned to him. Site admin\'s can access all the batches created
                                          in the application',
                        'is_default' => true
                    ],
                    [
                        'name' => 'View Batch',
                        'slug' => CoursePermission::VIEW_BATCH,
                        'description' => 'This permission allows the user to view batch details',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Add Batch',
                        'slug' => CoursePermission::ADD_BATCH,
                        'description' => 'This permission allows user to add new batch',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Batch',
                        'slug' => CoursePermission::EDIT_BATCH,
                        'description' => 'This permission allows user to edit batch details',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Batch',
                        'slug' => CoursePermission::DELETE_BATCH,
                        'description' => 'This permission allows user to delete batch',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Manage Batch Post',
                        'slug' => CoursePermission::MANAGE_COURSE_POST,
                        'description' => 'User with manage batch posts permission will be able to add, edit, delete posts
                                          inside a batch',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Batch Assign User',
                        'slug' => CoursePermission::BATCH_ASSIGN_USER,
                        'description' => 'User with this permission will be able to assign user to batch',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Manage Batch Post',
                        'slug' => CoursePermission::MANAGE_BATCH_POST,
                        'description' => 'User with this permission will be able to manage batch post',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Batch Assign User Group',
                        'slug' => CoursePermission::BATCH_ASSIGN_USER_GROUP,
                        'description' => 'User with this permission will be able to assign usergroup to batch',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Export Course',
                        'slug' => CoursePermission::EXPORT_COURSE,
                        'description' => 'User with this permission will be able to export the courses',
                        'is_default' => false
                    ],
                ]
            ],
            ModuleEnum::FLASHCARD => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List FlashCard",
                        "slug"=> FlashCardPermission::LIST_FLASHCARD,
                        "description" => "User with list flashcard permission will be able to view list of flashcards that
                                          are created by him or flashcard related to programs the user is assigned to.",
                        "is_default"=> true,
                    ],
                    [
                        "name"=>"View FlashCard",
                        "slug" => FlashCardPermission::VIEW_FLASHCARD,
                        "description" => "User with view flashcard permission will be able to view flashcard details before
                                          assigning to any entity.",
                        "is_default"=> false,
                    ],
                    [
                        "name" => "Add FlashCard",
                        "slug" => FlashCardPermission::ADD_FLASHCARD,
                        "description "=> "User with add flashcard permission will be able to add new flashcard.",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit FlashCard",
                        "slug" => FlashCardPermission::EDIT_FLASHCARD,
                        "description" => "User with view flashcard permission will be able to view list of flashcards that
                                          are created by him or flashcard related to programs the user is assigned to.",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete FlashCard",
                        "slug" => FlashCardPermission::DELETE_FLASHCARD,
                        "description" => "User with delete flashcard permission will be able to delete flashcard.",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Bulk FlashCard Import",
                        "slug" => FlashCardPermission::IMPORT_FLASHCARD,
                        "description" => "User with import flashcard permission will be able to
                                          import multiple flashcards.",
                        "is_default" => false,
                    ],
                ],
            ],
            ModuleEnum::MANAGE_SITE => [
                PermissionType::ADMIN => [
                    [
                        "name" => "List Faq",
                        "slug" => ManageSitePermission::LIST_FAQ,
                        "description" => "User with list faq permission will be able list the faq",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Add Faq",
                        "slug" => ManageSitePermission::ADD_FAQ,
                        "description" => "User with add faq permission will be able add faq",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit Faq",
                        "slug" => ManageSitePermission::EDIT_FAQ,
                        "description" => "User with edit faq permission will be able edit faq",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete Faq",
                        "slug" => ManageSitePermission::DELETE_FAQ,
                        "description" => "User with delete faq permission will be able delete faq",
                        "is_default" => false,
                    ],
                    [
                        "name" => "View Faq",
                        "slug" => ManageSitePermission::VIEW_FAQ,
                        "description" => "User with view faq permission will be able view faq",
                        "is_default" => true,
                    ],
                    [
                        "name" => "List Staticpage",
                        "slug" => ManageSitePermission::LIST_STATICPAGE,
                        "description" => "User with list staticpage permission will be able list the staticpage",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Add Staticpage",
                        "slug" => ManageSitePermission::ADD_STATICPAGE,
                        "description" => "User with add staticpage permission will be able add staticpage",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Edit Staticpage",
                        "slug" => ManageSitePermission::EDIT_STATICPAGE,
                        "description" => "User with edit staticpage permission will be able edit staticpage",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete Staticpage",
                        "slug" => ManageSitePermission::DELETE_STATICPAGE,
                        "description" => "User with delete staticpage permission will be able delete staticpage",
                        "is_default" => false,
                    ],
                    [
                        "name" => "View Staticpage",
                        "slug" => ManageSitePermission::VIEW_STATICPAGE,
                        "description" => "User with view staticpage permission will be able view staticpage",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Site Configuration",
                        "slug" => ManageSitePermission::SITE_CONFIGURATION,
                        "description" => "User with site configuration permission will be able do all the site
                                          configurations",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Custom Fields",
                        "slug" => ManageSitePermission::CUSTOM_FIELDS,
                        "description" => "User with custom fields permission will be able mange custom fields",
                        "is_default" => true,
                    ],
                    [
                        "name" => "Manage Attribute",
                        "slug" => ManageSitePermission::MANAGE_ATTRIBUTE,
                        "description" => "User with manage attribute permission will be able mange attribute",
                        "is_default" => true,
                    ],
                    [
                        "name" => "List Newsletter",
                        "slug" => ManageSitePermission::LIST_NEWSLETTER,
                        "description" => "User with list newsletter permission will be able list newslette",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Delete Newsletter",
                        "slug" => ManageSitePermission::DELETE_NEWSLETTER,
                        "description" => "User with delete newsletter permission will be able delete newsletter",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Export Newsletter",
                        "slug" => ManageSitePermission::EXPORT_NEWSLETTER,
                        "description" => "User with export newsletter permission will be able export newsletter",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Configuration",
                        "slug" => ManageSitePermission::CONFIGURATION,
                        "description" => "User with configuration permission will be able to manage all the configuration",
                        "is_default" => false,
                    ],
                    [
                        "name" => "Manage Cache",
                        "slug" => ManageSitePermission::MANAGE_CACHE,
                        "description" => "User with manage cache permission will be able to manage cache",
                        "is_default" => false,
                    ],
                    [
                        "name" => "List Sitesetting",
                        "slug" => ManageSitePermission::LIST_SITESETTING,
                        "description" => "User with list sitesetting permission will be able to list sitesetting",
                        "is_default" => false,
                    ],
                    [
                        "name" => "EDIT Sitesetting",
                        "slug" => ManageSitePermission::EDIT_SITESETTING,
                        "description" => "User with Edit sitesetting permission will be able to Edit sitesetting",
                        "is_default" => false,
                    ],
                ],
            ],
            ModuleEnum::HOME_PAGE => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'List Banners',
                        'slug' => HomePagePermission::LIST_BANNERS,
                        'description' => 'user with list banners permission will be able list banners',
                        'is_default' => true
                    ],
                    [
                        'name' => 'Add Banners',
                        'slug' => HomePagePermission::ADD_BANNERS,
                        'description' => 'user with add banners permission will be able add banners',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Banners',
                        'slug' => HomePagePermission::EDIT_BANNERS,
                        'description' => 'user with edit banners permission will be able edit banners',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Banners',
                        'slug' => HomePagePermission::DELETE_BANNERS,
                        'description' => 'user with delet banners permission will be able delete banners',
                        'is_default' => false
                    ],
                    [
                        'name' => 'List Partner',
                        'slug' => HomePagePermission::LIST_PARTNER,
                        'description' => 'user with list partner permission will be able list partner',
                        'is_default' => true
                    ],
                    [
                        'name' => 'Add Partner',
                        'slug' => HomePagePermission::ADD_PARTNER,
                        'description' => 'user with add partner permission will be able add partner',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Partner',
                        'slug' => HomePagePermission::EDIT_PARTNER,
                        'description' => 'user with edit partner permission will be able edit partner',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Partner',
                        'slug' => HomePagePermission::DELETE_PARTNER,
                        'description' => 'user with delet partner permission will be able delete partner',
                        'is_default' => false
                    ],
                    [
                        'name' => 'List Upcoming Courses',
                        'slug' => HomePagePermission::LIST_UPCOMING_COURSES,
                        'description' => 'user with list upcoming courses permission will be able list upcoming courses',
                        'is_default' => true
                    ],
                    [
                        'name' => 'Add Upcoming Courses',
                        'slug' => HomePagePermission::ADD_UPCOMING_COURSES,
                        'description' => 'user with add upcoming courses permission will be able add upcoming courses',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Upcoming Courses',
                        'slug' => HomePagePermission::DELETE_UPCOMING_COURSES,
                        'description' => 'user with delet upcoming courses permission will be able delete upcoming courses',
                        'is_default' => false
                    ],
                    [
                        'name' => 'List Popular Courses',
                        'slug' => HomePagePermission::LIST_POPULAR_COURSES,
                        'description' => 'user with list popular courses permission will be able list popular courses',
                        'is_default' => true
                    ],
                    [
                        'name' => 'Add Popular Courses',
                        'slug' => HomePagePermission::ADD_POPULAR_COURSES,
                        'description' => 'user with add popular courses permission will be able add popular courses',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Popular Courses',
                        'slug' => HomePagePermission::DELETE_POPULAR_COURSES,
                        'description' => 'user with delet popular courses permission will be able delete popular courses',
                        'is_default' => false
                    ],
                    [
                        'name' => 'List Testimonials',
                        'slug' => HomePagePermission::LIST_TESTIMONIALS,
                        'description' => 'user with list testimonials permission will be able list testimonials',
                        'is_default' => true
                    ],
                    [
                        'name' => 'Add Testimonials',
                        'slug' => HomePagePermission::ADD_TESTIMONIALS,
                        'description' => 'user with add testimonials permission will be able add testimonials',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Edit Testimonials',
                        'slug' => HomePagePermission::EDIT_TESTIMONIALS,
                        'description' => 'user with edit testimonials permission will be able edit testimonials',
                        'is_default' => false
                    ],
                    [
                        'name' => 'Delete Testimonials',
                        'slug' => HomePagePermission::DELETE_TESTIMONIALS,
                        'description' => 'user with delete testimonials permission will be able delete testimonials',
                        'is_default' => false
                    ],
                ]
            ],
            ModuleEnum::ERP => [
                PermissionType::ADMIN => [
                    [
                        'name' => 'Manage bulk imports',
                        'slug' => ERPPermission::MANAGE_BULK_IMPORTS,
                        'description' => 'User with this permission will be able to import bulk data and see the reports
                        of it',
                        'is_default' => false
                    ]
                ]
            ],
            ModuleEnum::SURVEY => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List Survey",
                        "slug"=> SurveyPermission::LIST_SURVEY,
                        "description"=>"User with list survey permission will be able to view list of survey that are created by him or related programs he is assigned to.",
                        "is_default" => true,
                    ],
                    [
                        "name"=>"Add Survey",
                        "slug"=> SurveyPermission::ADD_SURVEY,
                        "description"=>"User with add survey permission will have the ability to create new survey",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Edit Survey",
                        "slug"=> SurveyPermission::EDIT_SURVEY,
                        "description"=>"User with edit survey permissons will have the ability to edit all the survey created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Delete Survey",
                        "slug"=> SurveyPermission::DELETE_SURVEY,
                        "description"=>"User with delete survey permissons will have the ability to delete all the survey created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                      "name"=>"Export Survey",
                      "slug"=> SurveyPermission::EXPORT_SURVEY,
                      "description"=>"Export survey permissions can Export all the survey",
                      "is_default"=> true
                    ],
                    [
                      "name"=>"Report Survey",
                      "slug"=> SurveyPermission::REPORT_SURVEY,
                      "description"=>"Report survey permissions can Export all the survey",
                      "is_default"=> true
                    ],
                    [
                        "name"=>"Assign Survey To User",
                        "slug"=> SurveyPermission::SURVEY_ASSIGN_USER,
                        "description"=>"User with assign survey to user permissons will have the ability to assign survey to user that survey are created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign Survey To User Group",
                        "slug"=> SurveyPermission::SURVEY_ASSIGN_USER_GROUP,
                        "description"=>"User with assign survey to usergroup permissons will have the ability to assign survey to usergroup that survey are created by him or related programs he is
                                        assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"List Survey Question",
                        "slug"=> SurveyPermission::LIST_SURVEY_QUESTION,
                        "description"=>"List Survey Question permissions can list all the survey questions",
                        "is_default" => true,
                    ],
                    [
                        "name"=>"Add Survey Question",
                        "slug"=> SurveyPermission::ADD_SURVEY_QUESTION,
                        "description"=>"Add Survey Question permissions can Add the new survey question",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Edit Survey Question",
                        "slug"=> SurveyPermission::EDIT_SURVEY_QUESTION,
                        "description"=>"Edit Survey Question permissions can edit the survey question",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Delete Survey Question",
                        "slug"=> SurveyPermission::DELETE_SURVEY_QUESTION,
                        "description"=>"Delete Survey Question permissions can delete the survey question",
                        "is_default" => false,
                    ],
                ],
            ],
            ModuleEnum::ASSIGNMENT => [
                PermissionType::ADMIN => [
                    [
                        "name"=>"List Assignment",
                        "slug"=> AssignmentPermission::LIST_ASSIGNMENT,
                        "description"=>"User with list assignment permission will be able to view list of assignment that are created by him or related programs he is assigned to.",
                        "is_default" => true,
                    ],
                    [
                        "name"=>"Add Assignment",
                        "slug"=> AssignmentPermission::ADD_ASSIGNMENT,
                        "description"=>"User with add assignment permission will have the ability to create new assignment",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Edit Assignment",
                        "slug"=> AssignmentPermission::EDIT_ASSIGNMENT,
                        "description"=>"User with edit assignment permissons will have the ability to edit all the assignment created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Delete Assignment",
                        "slug"=> AssignmentPermission::DELETE_ASSIGNMENT,
                        "description"=>"User with delete assignment permissons will have the ability to delete all the assignment created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Export Assignment",
                        "slug"=> AssignmentPermission::EXPORT_ASSIGNMENT,
                        "description"=>"Export assignment permissions can Export all the assignments",
                        "is_default"=> true
                    ],
                    [
                        "name"=>"Report Assignment",
                        "slug"=> AssignmentPermission::REPORT_ASSIGNMENT,
                        "description"=>"Report assignment permissions can Export all the survey",
                        "is_default"=> true
                    ],
                    [
                        "name"=>"Assign Assignment To User",
                        "slug"=> AssignmentPermission::ASSIGNMENT_ASSIGN_USER,
                        "description"=>"User with assign assignment to user permissons will have the ability to assign assignment to user that assignment are created by him or related programs he is assigned to",
                        "is_default" => false,
                    ],
                    [
                        "name"=>"Assign Assignment User Group",
                        "slug"=> AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP,
                        "description"=>"User with assign assignment to usergroup permissons will have the ability to assign assignment to usergroup that survey are created by him or related programs he is
                                        assigned to",
                        "is_default" => false,
                    ],
                ],
            ],
        ];

        collect($module_permissions_mapping)->each(
            function ($permissions_and_type_mapping, $module_slug) {
                try {
                    $module = Module::where("slug", $module_slug)->firstOrFail();
                    collect($permissions_and_type_mapping)->each(
                        function ($permissions, $type) use ($module) {
                            foreach ($permissions as $permission) {
                                $permission["id"] = Permission::getNextSequence();
                                $permission["type"] = $type;
                                $module->permissions()->save(new Permission($permission));
                            }
                        }
                    );
                } catch (ModelNotFoundException $e) {
                    Log::error("Couldn't find module with the slug \"{$module_slug}\"");
                }
            }
        );
    }
}
