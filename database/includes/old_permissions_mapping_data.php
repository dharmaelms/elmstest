<?php
use App\Enums\ERP\ERPPermission;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\User\UserPermission;
use App\Enums\UserGroup\UserGroupPermission;
use App\Enums\Program\ChannelPermission;
use App\Enums\DAMS\DAMSPermission;
use App\Enums\Course\CoursePermission;
use App\Enums\RolesAndPermissions\RolePermission;
use App\Enums\Category\CategoryPermission;
use App\Enums\Event\EventPermission;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\FlashCard\FlashCardPermission;
use App\Enums\Country\CountryPermission;
use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\ManageSite\ManageSitePermission;
use App\Enums\ECommerce\ECommercePermission;
use App\Enums\HomePage\HomePagePermission;

return [
    "user" => [
        "list-user" => UserPermission::LIST_USER,
        "view-user" => UserPermission::VIEW_USER,
        "add-user" => UserPermission::ADD_USER,
        "edit-user" => UserPermission::EDIT_USER,
        "delete-user" => UserPermission::DELETE_USER,
        "bulk-user-delete" => UserPermission::DELETE_USER,
        "bulk-user-activate" => UserPermission::EDIT_USER,
        "bulk-user-inactivate" => UserPermission::EDIT_USER,
        "import-users" => UserPermission::IMPORT_USERS,
        "export-users" => UserPermission::EXPORT_USERS,
        "userimport-history" => UserPermission::IMPORT_USERS,
        "assign-usergroup" => [
            "module" => ModuleEnum::USER_GROUP,
            "permission" => UserGroupPermission::USER_GROUP_ASSIGN_USER
        ],
        "assign-contentfeed" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::CHANNEL_ASSIGN_USER
        ]
    ],
    "user-group" => [
        "listuser-group" => UserGroupPermission::LIST_USER_GROUP,
        "viewuser-group" => UserGroupPermission::VIEW_USER_GROUP,
        "adduser-group" => UserGroupPermission::ADD_USER_GROUP,
        "edituser-group" => UserGroupPermission::EDIT_USER_GROUP,
        "deleteuser-group" => UserGroupPermission::DELETE_USER_GROUP,
        "bulk-usergroup-delete" => UserGroupPermission::DELETE_USER_GROUP,
        "assign-user" => UserGroupPermission::USER_GROUP_ASSIGN_USER,
        "assign-contentfeed" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::CHANNEL_ASSIGN_USER_GROUP
        ]
    ],
    "dams" => [
        "list-media" => DAMSPermission::LIST_MEDIA,
        "view-media" => DAMSPermission::VIEW_MEDIA,
        "add-media" => DAMSPermission::ADD_MEDIA,
        "edit-media" => DAMSPermission::EDIT_MEDIA,
        "delete-media" => DAMSPermission::DELETE_MEDIA,
        "bulk-delete" => DAMSPermission::DELETE_MEDIA,
    ],
    "content-feeds" => [
        "list-content-feeds" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::LIST_CHANNEL
        ],
        "view-content-feeds" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::VIEW_CHANNEL,
        ],
        "add-content-feeds" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::ADD_CHANNEL
        ],
        "edit-content-feeds" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::EDIT_CHANNEL
        ],
        "delete-content-feeds" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::DELETE_CHANNEL
        ],
        "assign-categories" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::CHANNEL_ASSIGN_CATEGORY
        ],
        "assign-user" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::CHANNEL_ASSIGN_USER
        ],
        "assign-usergroup" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::CHANNEL_ASSIGN_USER_GROUP
        ],
        "manage-packets" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::MANAGE_CHANNEL_POST
        ],
        "access-request-content-feeds" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST
        ],
        "export-channel" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::EXPORT_CHANNEL
        ],
        "export-course" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::EXPORT_COURSE
        ]
    ],
    "courses" => [
        "list-courses" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::LIST_COURSE
        ],
        "view-course" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::VIEW_COURSE
        ],
        "add-course" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::ADD_COURSE
        ],
        "edit-course" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::EDIT_COURSE
        ],
        "delete-course" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::DELETE_COURSE
        ],
        "assign-categories" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::ASSIGN_CATEGORY
        ],
        "manage-packets" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::MANAGE_COURSE_POST
        ]
    ],
    "batches" => [
        "list-batches" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::LIST_BATCH
        ],
        "view-batch" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::VIEW_BATCH
        ],
        "add-batch" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::ADD_BATCH
        ],
        "edit-batch" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::EDIT_BATCH
        ],
        "delete-batch" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::DELETE_BATCH
        ],
        "manage-packets" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::MANAGE_BATCH_POST
        ],
        "assign-user" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::BATCH_ASSIGN_USER
        ],
        "assign-usergroup" => [
            "module" => ModuleEnum::COURSE,
            "permission" => CoursePermission::BATCH_ASSIGN_USER_GROUP
        ]
    ],
    "role" => [
        "list-role" => RolePermission::LIST_ROLE,
        "add-role" => RolePermission::ADD_ROLE,
        "edit-role" => RolePermission::EDIT_ROLE,
        "delete-role" => RolePermission::DELETE_ROLE
    ],
    "category" => [
        "list-category" => CategoryPermission::LIST_CATEGORY,
        "view-category" => CategoryPermission::VIEW_CATEGORY,
        "add-category" => CategoryPermission::ADD_CATEGORY,
        "edit-category" => CategoryPermission::EDIT_CATEGORY,
        "delete-category" => CategoryPermission::DELETE_CATEGORY,
        "assign-contentfeed" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::CHANNEL_ASSIGN_CATEGORY
        ]
    ],
    "event" => [
        "list-event" => EventPermission::LIST_EVENT,
        "view-event" => EventPermission::VIEW_EVENT,
        "add-event" => EventPermission::ADD_EVENT,
        "edit-event" => EventPermission::EDIT_EVENT,
        "delete-event" => EventPermission::DELETE_EVENT,
        "assign-user" => EventPermission::ASSIGN_USER,
        "assign-usergroup" => EventPermission::ASSIGN_USER_GROUP
    ],
    "assessment" => [
        "list-quiz" => AssessmentPermission::LIST_QUIZ,
        "view-quiz" => AssessmentPermission::VIEW_QUIZ,
        "add-quiz" => AssessmentPermission::ADD_QUIZ,
        "edit-quiz" => AssessmentPermission::EDIT_QUIZ,
        "delete-quiz" => AssessmentPermission::DELETE_QUIZ,
        "assign-quiz-user" => AssessmentPermission::QUIZ_ASSIGN_USER,
        "assign-quiz-usergroup" => AssessmentPermission::QUIZ_ASSIGN_USER_GROUP,
        "list-questionbank" => AssessmentPermission::LIST_QUESTION_BANK,
        "add-questionbank" => AssessmentPermission::ADD_QUESTION_BANK,
        "edit-questionbank" => AssessmentPermission::EDIT_QUESTION_BANK,
        "delete-questionbank" => AssessmentPermission::DELETE_QUESTION_BANK,
        "add-question" => AssessmentPermission::ADD_QUESTION,
        "edit-question" => AssessmentPermission::EDIT_QUESTION,
        "delete-question" => AssessmentPermission::DELETE_QUESTION,
        "import-questionbank" => AssessmentPermission::IMPORT_QUESTION_BANK,
        "export-questionbank" => AssessmentPermission::EXPORT_QUESTION_BANK,
        "import-quiz" => AssessmentPermission::IMPORT_QUIZ,
        "export-quiz" => AssessmentPermission::EXPORT_QUIZ,
    ],
    "flashcards" => [
        "list" => [
            "module" => ModuleEnum::FLASHCARD,
            "permission" => FlashCardPermission::LIST_FLASHCARD
        ],
        "add" => [
            "module" => ModuleEnum::FLASHCARD,
            "permission" => FlashCardPermission::ADD_FLASHCARD
        ],
        "view" => [
            "module" => ModuleEnum::FLASHCARD,
            "permission" => FlashCardPermission::VIEW_FLASHCARD
        ],
        "edit" => [
            "module" => ModuleEnum::FLASHCARD,
            "permission" => FlashCardPermission::EDIT_FLASHCARD
        ],
        "delete" => [
            "module" => ModuleEnum::FLASHCARD,
            "permission" => FlashCardPermission::DELETE_FLASHCARD
        ]
    ],
    "country" => [
        "list" => CountryPermission::LIST_COUNTRY,
        "add" => CountryPermission::ADD_COUNTRY,
        "edit" => CountryPermission::EDIT_COUNTRY
    ],
    "announcement" => [
        "list-announcement" => AnnouncementPermission::LIST_ANNOUNCEMENT,
        "add-announcement" => AnnouncementPermission::ADD_ANNOUNCEMENT,
        "view-announcement" => AnnouncementPermission::VIEW_ANNOUNCEMENT,
        "edit-announcement" => AnnouncementPermission::EDIT_ANNOUNCEMENT,
        "delete-announcement" => AnnouncementPermission::DELETE_ANNOUNCEMENT,
        "assign-contentfeed" => AnnouncementPermission::ASSIGN_CHANNEL,
        "assign-user" => AnnouncementPermission::ASSIGN_USER,
        "assign-usergroup" => AnnouncementPermission::ASSIGN_USERGROUP
    ],
    "manage-site" => [
        "list-faq" => ManageSitePermission::LIST_FAQ,
        "view-faq" => ManageSitePermission::VIEW_FAQ,
        "edit-faq" => ManageSitePermission::EDIT_FAQ,
        "add-faq" => ManageSitePermission::ADD_FAQ,
        "delete-faq" => ManageSitePermission::DELETE_FAQ,
        "list-staticpage" => ManageSitePermission::LIST_STATICPAGE,
        "edit-staticpage" => ManageSitePermission::EDIT_STATICPAGE,
        "add-staticpage" => ManageSitePermission::ADD_STATICPAGE,
        "view-staticpage" => ManageSitePermission::VIEW_STATICPAGE,
        "delete-staticpage" => ManageSitePermission::DELETE_STATICPAGE,
        "list-newsletter" => ManageSitePermission::LIST_NEWSLETTER,
        "delete-newsletter" => ManageSitePermission::DELETE_NEWSLETTER,
        "export-newsletter" => ManageSitePermission::EXPORT_NEWSLETTER
    ],
    "setting" => [
        "configuration" => [
            "module" => ModuleEnum::MANAGE_SITE,
            "permission" => ManageSitePermission::CONFIGURATION
        ],
        "manage-cache" => [
            "module" => ModuleEnum::MANAGE_SITE,
            "permission" => ManageSitePermission::MANAGE_CACHE
        ],
        "list-sitesetting" => [
            "module" => ModuleEnum::MANAGE_SITE,
            "permission" => ManageSitePermission::LIST_SITESETTING
        ],
        "edit-sitesetting" => [
            "module" => ModuleEnum::MANAGE_SITE,
            "permission" => ManageSitePermission::EDIT_SITESETTING
        ]
    ],
    "banner" =>  [
        "banners" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::LIST_BANNERS
        ],
        "add-banner" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::ADD_BANNERS
        ],
        "edit-banner" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::EDIT_BANNERS
        ],
        "delete-banner" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::DELETE_BANNERS
        ]
    ],
    "partner" => [
        "partners" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::LIST_PARTNER
        ],
        "add-partner" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::ADD_PARTNER
        ],
        "edit-partner" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::EDIT_PARTNER
        ],
        "delete-partner" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::DELETE_PARTNER
        ]
    ],
    "testimonial" => [
        "testimonials" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::LIST_TESTIMONIALS
        ],
        "add-testimonial" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::ADD_TESTIMONIALS
        ],
        "edit-testimonial" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::EDIT_TESTIMONIALS
        ],
        "delete-testimonial" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::DELETE_TESTIMONIALS
        ]
    ],
    "upcomingcourses" => [
        "upcomingcourses" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::LIST_UPCOMING_COURSES
        ],
        "add-program" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::ADD_UPCOMING_COURSES
        ],
        "delete-course" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::DELETE_UPCOMING_COURSES
        ]
    ],
    "popularcourses" => [
        "popularcourses" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::LIST_POPULAR_COURSES
        ],
        "add-program" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::ADD_POPULAR_COURSES
        ],
        "delete-course" => [
            "module" => ModuleEnum::HOME_PAGE,
            "permission" => HomePagePermission::DELETE_POPULAR_COURSES
        ]
    ],
    "promocode" => [
        "promocode" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::LIST_PROMO_CODE
        ],
        "add-promocode" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::ADD_PROMO_CODE
        ],
        "edit-promocode" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::EDIT_PROMO_CODE
        ],
        "delete-promocode" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::DELETE_PROMO_CODE
        ],
        "export-promocode" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::EXPORT_PROMO_CODE
        ],
    ],
    "order" => [
        "list-order" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::LIST_ORDER
        ],
        "view-order" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::VIEW_ORDER
        ],
        "edit-order" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::EDIT_ORDER
        ],
        "export-order" => [
            "module" => ModuleEnum::E_COMMERCE,
            "permission" => ECommercePermission::EXPORT_ORDER
        ],
    ],
    "channel-questions" => [
        "manage-questions" => [
            "module" => ModuleEnum::CHANNEL,
            "permission" => ChannelPermission::MANAGE_CHANNEL_QUESTION
        ]
    ],
    "bulkimportreports" => [
        "view-import-reports" => [
            "module" => ModuleEnum::ERP,
            "permission" => ERPPermission::MANAGE_BULK_IMPORTS
        ],
        "export-reports" => [
            "module" => ModuleEnum::ERP,
            "permission" => ERPPermission::MANAGE_BULK_IMPORTS
        ],
        "download-templates" => [
            "module" => ModuleEnum::ERP,
            "permission" => ERPPermission::MANAGE_BULK_IMPORTS
        ],
    ]
];
