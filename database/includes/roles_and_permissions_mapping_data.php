<?php

use App\Enums\ERP\ERPPermission;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Package\PackagePermission;
use App\Enums\Program\ChannelPermission;
use App\Enums\Course\CoursePermission;
use App\Enums\RolesAndPermissions\RolePermission;
use App\Enums\User\UserPermission;
use App\Enums\UserGroup\UserGroupPermission;
use App\Enums\Category\CategoryPermission;
use App\Enums\Event\EventPermission;
use App\Enums\ECommerce\ECommercePermission;
use App\Enums\DAMS\DAMSPermission;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\FlashCard\FlashCardPermission;
use App\Enums\ManageSite\ManageSitePermission;
use App\Enums\HomePage\HomePagePermission;
use App\Enums\Country\CountryPermission;
use App\Enums\Survey\SurveyPermission;
use App\Enums\Assignment\AssignmentPermission;
use App\Enums\Report\ReportPermission;

return [
    "super_admin" => [
        'admin_permissions' => [
            [
                'slug' => ModuleEnum::USER,
                'action' => [
                    ['slug' => UserPermission::LIST_USER],
                    ['slug' => UserPermission::VIEW_USER],
                    ['slug' => UserPermission::ADD_USER],
                    ['slug' => UserPermission::EDIT_USER],
                    ['slug' => UserPermission::DELETE_USER],
                    ['slug' => UserPermission::IMPORT_USERS],
                    ['slug' => UserPermission::EXPORT_USERS],
                ]
            ],
            [
                'slug' => ModuleEnum::USER_GROUP,
                'action' => [
                    ['slug' => UserGroupPermission::LIST_USER_GROUP],
                    ['slug' => UserGroupPermission::VIEW_USER_GROUP],
                    ['slug' => UserGroupPermission::ADD_USER_GROUP],
                    ['slug' => UserGroupPermission::EDIT_USER_GROUP],
                    ['slug' => UserGroupPermission::DELETE_USER_GROUP],
                    ['slug' => UserGroupPermission::USER_GROUP_ASSIGN_USER],
                ]
            ],
            [
                'slug' => ModuleEnum::DAMS,
                'action' => [
                    ['slug' => DAMSPermission::LIST_MEDIA],
                    ['slug' => DAMSPermission::VIEW_MEDIA],
                    ['slug' => DAMSPermission::ADD_MEDIA],
                    ['slug' => DAMSPermission::EDIT_MEDIA],
                    ['slug' => DAMSPermission::DELETE_MEDIA],
                ]
            ],
            [
                'slug' => ModuleEnum::COUNTRY,
                'action' => [
                    ['slug' => CountryPermission::LIST_COUNTRY],
                    ['slug' => CountryPermission::ADD_COUNTRY],
                    ['slug' => CountryPermission::EDIT_COUNTRY],
                ]
            ],
            [
                'slug' => ModuleEnum::CHANNEL,
                'action' => [
                    ['slug' => ChannelPermission::LIST_CHANNEL],
                    ['slug' => ChannelPermission::ADD_CHANNEL],
                    ['slug' => ChannelPermission::EDIT_CHANNEL],
                    ['slug' => ChannelPermission::DELETE_CHANNEL],
                    ['slug' => ChannelPermission::VIEW_CHANNEL],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_POST],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_USER],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_USER_GROUP],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_QUESTION],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST],
                    ['slug' => ChannelPermission::EXPORT_CHANNEL],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_CATEGORY],
                ]
            ],
            [
                'slug' => ModuleEnum::COURSE,
                'action' => [
                    ['slug' => CoursePermission::LIST_COURSE],
                    ['slug' => CoursePermission::VIEW_COURSE],
                    ['slug' => CoursePermission::ADD_COURSE],
                    ['slug' => CoursePermission::EDIT_COURSE],
                    ['slug' => CoursePermission::DELETE_COURSE],
                    ['slug' => CoursePermission::ASSIGN_CATEGORY],
                    ['slug' => CoursePermission::MANAGE_COURSE_POST],
                    ['slug' => CoursePermission::LIST_BATCH],
                    ['slug' => CoursePermission::VIEW_BATCH],
                    ['slug' => CoursePermission::ADD_BATCH],
                    ['slug' => CoursePermission::EDIT_BATCH],
                    ['slug' => CoursePermission::DELETE_BATCH],
                    ['slug' => CoursePermission::MANAGE_BATCH_POST],
                    ['slug' => CoursePermission::BATCH_ASSIGN_USER],
                    ['slug' => CoursePermission::BATCH_ASSIGN_USER_GROUP],
                    ['slug' => CoursePermission::EXPORT_COURSE],
                ]
            ],
            [
                'slug' => ModuleEnum::PACKAGE,
                'action' => [
                    ['slug' => PackagePermission::ADD_PACKAGE],
                    ['slug' => PackagePermission::LIST_PACKAGES],
                    ['slug' => PackagePermission::VIEW_PACKAGE_DETAILS],
                    ['slug' => PackagePermission::EDIT_PACKAGE],
                    ['slug' => PackagePermission::DELETE_PACKAGE],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_CHANNELS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_USERS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_USER_GROUPS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_CATEGORIES],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_TABS],
                    ['slug' => PackagePermission::EXPORT_PACKAGE_WITH_USERS],
                    ['slug' => PackagePermission::EXPORT_PACKAGE_WITH_USER_GROUPS],
                ]
            ],
            [
                'slug' => ModuleEnum::ROLE,
                'action' => [
                    ['slug' => RolePermission::LIST_ROLE],
                    ['slug' => RolePermission::ADD_ROLE],
                    ['slug' => RolePermission::EDIT_ROLE],
                    ['slug' => RolePermission::DELETE_ROLE]
                ]
            ],
            [
                'slug' => ModuleEnum::CATEGORY,
                'action' => [
                    ['slug' => CategoryPermission::LIST_CATEGORY],
                    ['slug' => CategoryPermission::VIEW_CATEGORY],
                    ['slug' => CategoryPermission::ADD_CATEGORY],
                    ['slug' => CategoryPermission::EDIT_CATEGORY],
                    ['slug' => CategoryPermission::DELETE_CATEGORY],
                    ['slug' => CategoryPermission::ASSIGN_CHANNEL]
                ]
            ],
            [
                'slug' => ModuleEnum::EVENT,
                'action' => [
                    ['slug' => EventPermission::LIST_EVENT],
                    ['slug' => EventPermission::VIEW_EVENT],
                    ['slug' => EventPermission::ADD_EVENT],
                    ['slug' => EventPermission::EDIT_EVENT],
                    ['slug' => EventPermission::DELETE_EVENT],
                    ['slug' => EventPermission::ASSIGN_CHANNEL],
                    ['slug' => EventPermission::ASSIGN_USER],
                    ['slug' => EventPermission::ASSIGN_USER_GROUP]
                ]
            ],
            [
                'slug' => ModuleEnum::ASSESSMENT,
                'action' => [
                    ['slug' => AssessmentPermission::LIST_QUIZ],
                    ['slug' => AssessmentPermission::VIEW_QUIZ],
                    ['slug' => AssessmentPermission::ADD_QUIZ],
                    ['slug' => AssessmentPermission::EDIT_QUIZ],
                    ['slug' => AssessmentPermission::DELETE_QUIZ],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_CHANNEL],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_USER],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_USER_GROUP],
                    ['slug' => AssessmentPermission::ADD_QUESTION_BANK],
                    ['slug' => AssessmentPermission::EDIT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::DELETE_QUESTION_BANK],
                    ['slug' => AssessmentPermission::ADD_QUESTION],
                    ['slug' => AssessmentPermission::EDIT_QUESTION],
                    ['slug' => AssessmentPermission::DELETE_QUESTION],
                    ['slug' => AssessmentPermission::IMPORT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::IMPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::LIST_QUESTION_BANK],
                ]
            ],
            [
                'slug' => ModuleEnum::FLASHCARD,
                'action' => [
                    ['slug' => FlashCardPermission::LIST_FLASHCARD],
                    ['slug' => FlashCardPermission::ADD_FLASHCARD],
                    ['slug' => FlashCardPermission::VIEW_FLASHCARD],
                    ['slug' => FlashCardPermission::EDIT_FLASHCARD],
                    ['slug' => FlashCardPermission::DELETE_FLASHCARD],
                    ['slug' => FlashCardPermission::IMPORT_FLASHCARD],
                ]
            ],
            [
                'slug' => ModuleEnum::ANNOUNCEMENT,
                'action' => [
                    ['slug' => AnnouncementPermission::LIST_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ADD_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::VIEW_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::EDIT_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ASSIGN_MEDIA],
                    ['slug' => AnnouncementPermission::DELETE_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ASSIGN_CHANNEL],
                    ['slug' => AnnouncementPermission::ASSIGN_USER],
                    ['slug' => AnnouncementPermission::ASSIGN_USERGROUP]
                ]
            ],
            [
                'slug' => ModuleEnum::MANAGE_SITE,
                'action' => [
                    ['slug' => ManageSitePermission::LIST_FAQ],
                    ['slug' => ManageSitePermission::ADD_FAQ],
                    ['slug' => ManageSitePermission::EDIT_FAQ],
                    ['slug' => ManageSitePermission::DELETE_FAQ],
                    ['slug' => ManageSitePermission::VIEW_FAQ],
                    ['slug' => ManageSitePermission::LIST_STATICPAGE],
                    ['slug' => ManageSitePermission::ADD_STATICPAGE],
                    ['slug' => ManageSitePermission::EDIT_STATICPAGE],
                    ['slug' => ManageSitePermission::DELETE_STATICPAGE],
                    ['slug' => ManageSitePermission::VIEW_STATICPAGE],
                    ['slug' => ManageSitePermission::SITE_CONFIGURATION],
                    ['slug' => ManageSitePermission::CUSTOM_FIELDS],
                    ['slug' => ManageSitePermission::MANAGE_ATTRIBUTE],
                    ['slug' => ManageSitePermission::LIST_NEWSLETTER],
                    ['slug' => ManageSitePermission::DELETE_NEWSLETTER],
                    ['slug' => ManageSitePermission::EXPORT_NEWSLETTER],
                    ['slug' => ManageSitePermission::CONFIGURATION],
                    ['slug' => ManageSitePermission::MANAGE_CACHE],
                    ['slug' => ManageSitePermission::LIST_SITESETTING],
                    ['slug' => ManageSitePermission::EDIT_SITESETTING]
                ]
            ],
            [
                'slug' => ModuleEnum::E_COMMERCE,
                'action' => [
                    ['slug' => ECommercePermission::LIST_ORDER],
                    ['slug' => ECommercePermission::LIST_PROMO_CODE],
                    ['slug' => ECommercePermission::ADD_PROMO_CODE],
                    ['slug' => ECommercePermission::EDIT_PROMO_CODE],
                    ['slug' => ECommercePermission::DELETE_PROMO_CODE],
                    ['slug' => ECommercePermission::EXPORT_PROMO_CODE],
                    ['slug' => ECommercePermission::VIEW_ORDER],
                    ['slug' => ECommercePermission::EDIT_ORDER],
                    ['slug' => ECommercePermission::EXPORT_ORDER]
                ]
            ],
            [
                'slug' => ModuleEnum::HOME_PAGE,
                'action' => [
                    ['slug' => HomePagePermission::LIST_BANNERS],
                    ['slug' => HomePagePermission::ADD_BANNERS],
                    ['slug' => HomePagePermission::EDIT_BANNERS],
                    ['slug' => HomePagePermission::DELETE_BANNERS],
                    ['slug' => HomePagePermission::LIST_PARTNER],
                    ['slug' => HomePagePermission::ADD_PARTNER],
                    ['slug' => HomePagePermission::EDIT_PARTNER],
                    ['slug' => HomePagePermission::DELETE_PARTNER],
                    ['slug' => HomePagePermission::LIST_UPCOMING_COURSES],
                    ['slug' => HomePagePermission::ADD_UPCOMING_COURSES],
                    ['slug' => HomePagePermission::DELETE_UPCOMING_COURSES],
                    ['slug' => HomePagePermission::LIST_POPULAR_COURSES],
                    ['slug' => HomePagePermission::ADD_POPULAR_COURSES],
                    ['slug' => HomePagePermission::DELETE_POPULAR_COURSES],
                    ['slug' => HomePagePermission::LIST_TESTIMONIALS],
                    ['slug' => HomePagePermission::ADD_TESTIMONIALS],
                    ['slug' => HomePagePermission::EDIT_TESTIMONIALS],
                    ['slug' => HomePagePermission::DELETE_TESTIMONIALS]
                ]
            ],
            [
                'slug' => ModuleEnum::ERP,
                'action' => [
                    ['slug' => ERPPermission::MANAGE_BULK_IMPORTS],
                ]
            ],
            [
                'slug' => ModuleEnum::REPORT,
                'action' => [
                    ['slug' => ReportPermission::VIEW_REPORT],
                    ['slug' => ReportPermission::EXPORT_REPORT],
                ]
            ],
            [
                'slug' => ModuleEnum::SURVEY,
                'action' => [
                    ['slug' => SurveyPermission::LIST_SURVEY],
                    ['slug' => SurveyPermission::ADD_SURVEY],
                    ['slug' => SurveyPermission::EDIT_SURVEY],
                    ['slug' => SurveyPermission::DELETE_SURVEY],
                    ['slug' => SurveyPermission::EXPORT_SURVEY],
                    ['slug' => SurveyPermission::REPORT_SURVEY],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER_GROUP],
                    ['slug' => SurveyPermission::LIST_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::ADD_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::EDIT_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::DELETE_SURVEY_QUESTION],
                ]
            ],
            [
                'slug' => ModuleEnum::ASSIGNMENT,
                'action' => [
                    ['slug' => AssignmentPermission::LIST_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ADD_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EDIT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::DELETE_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EXPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::REPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP],
                ]
            ],
        ],
    ],
    "site_admin" => [
        'admin_permissions' => [
            [
                'slug' => ModuleEnum::USER,
                'action' => [
                    ['slug' => UserPermission::LIST_USER],
                    ['slug' => UserPermission::VIEW_USER],
                    ['slug' => UserPermission::ADD_USER],
                    ['slug' => UserPermission::EDIT_USER],
                    ['slug' => UserPermission::DELETE_USER],
                    ['slug' => UserPermission::IMPORT_USERS],
                    ['slug' => UserPermission::EXPORT_USERS],
                ]
            ],
            [
                'slug' => ModuleEnum::USER_GROUP,
                'action' => [
                    ['slug' => UserGroupPermission::LIST_USER_GROUP],
                    ['slug' => UserGroupPermission::VIEW_USER_GROUP],
                    ['slug' => UserGroupPermission::ADD_USER_GROUP],
                    ['slug' => UserGroupPermission::EDIT_USER_GROUP],
                    ['slug' => UserGroupPermission::DELETE_USER_GROUP],
                    ['slug' => UserGroupPermission::USER_GROUP_ASSIGN_USER],
                ]
            ],
            [
                'slug' => ModuleEnum::DAMS,
                'action' => [
                    ['slug' => DAMSPermission::LIST_MEDIA],
                    ['slug' => DAMSPermission::VIEW_MEDIA],
                    ['slug' => DAMSPermission::ADD_MEDIA],
                    ['slug' => DAMSPermission::EDIT_MEDIA],
                    ['slug' => DAMSPermission::DELETE_MEDIA],
                ]
            ],
            [
                'slug' => ModuleEnum::COUNTRY,
                'action' => [
                    ['slug' => CountryPermission::LIST_COUNTRY],
                    ['slug' => CountryPermission::ADD_COUNTRY],
                    ['slug' => CountryPermission::EDIT_COUNTRY],
                ]
            ],
            [
                'slug' => ModuleEnum::CHANNEL,
                'action' => [
                    ['slug' => ChannelPermission::LIST_CHANNEL],
                    ['slug' => ChannelPermission::VIEW_CHANNEL],
                    ['slug' => ChannelPermission::ADD_CHANNEL],
                    ['slug' => ChannelPermission::EDIT_CHANNEL],
                    ['slug' => ChannelPermission::DELETE_CHANNEL],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_CATEGORY],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_USER],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_USER_GROUP],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_POST],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_QUESTION],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST],
                    ['slug' => ChannelPermission::EXPORT_CHANNEL],
                ]
            ],
            [
                'slug' => ModuleEnum::PACKAGE,
                'action' => [
                    ['slug' => PackagePermission::ADD_PACKAGE],
                    ['slug' => PackagePermission::LIST_PACKAGES],
                    ['slug' => PackagePermission::VIEW_PACKAGE_DETAILS],
                    ['slug' => PackagePermission::EDIT_PACKAGE],
                    ['slug' => PackagePermission::DELETE_PACKAGE],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_CHANNELS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_USERS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_USER_GROUPS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_CATEGORIES],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS],
                    ['slug' => PackagePermission::MANAGE_PACKAGE_TABS],
                    ['slug' => PackagePermission::EXPORT_PACKAGE_WITH_USERS],
                    ['slug' => PackagePermission::EXPORT_PACKAGE_WITH_USER_GROUPS],
                ]
            ],
            [
                'slug' => ModuleEnum::COURSE,
                'action' => [
                    ['slug' => CoursePermission::LIST_COURSE],
                    ['slug' => CoursePermission::VIEW_COURSE],
                    ['slug' => CoursePermission::ADD_COURSE],
                    ['slug' => CoursePermission::EDIT_COURSE],
                    ['slug' => CoursePermission::DELETE_COURSE],
                    ['slug' => CoursePermission::ASSIGN_CATEGORY],
                    ['slug' => CoursePermission::MANAGE_COURSE_POST],
                    ['slug' => CoursePermission::LIST_BATCH],
                    ['slug' => CoursePermission::VIEW_BATCH],
                    ['slug' => CoursePermission::ADD_BATCH],
                    ['slug' => CoursePermission::EDIT_BATCH],
                    ['slug' => CoursePermission::DELETE_BATCH],
                    ['slug' => CoursePermission::MANAGE_BATCH_POST],
                    ['slug' => CoursePermission::BATCH_ASSIGN_USER],
                    ['slug' => CoursePermission::BATCH_ASSIGN_USER_GROUP],
                    ['slug' => CoursePermission::EXPORT_COURSE],
                ]
            ],
            [
                'slug' => ModuleEnum::ROLE,
                'action' => [
                    ['slug' => RolePermission::LIST_ROLE],
                    ['slug' => RolePermission::ADD_ROLE],
                    ['slug' => RolePermission::EDIT_ROLE],
                    ['slug' => RolePermission::DELETE_ROLE]
                ]
            ],
            [
                'slug' => ModuleEnum::CATEGORY,
                'action' => [
                    ['slug' => CategoryPermission::LIST_CATEGORY],
                    ['slug' => CategoryPermission::VIEW_CATEGORY],
                    ['slug' => CategoryPermission::ADD_CATEGORY],
                    ['slug' => CategoryPermission::EDIT_CATEGORY],
                    ['slug' => CategoryPermission::DELETE_CATEGORY],
                    ['slug' => CategoryPermission::ASSIGN_CHANNEL]
                ]
            ],
            [
                'slug' => ModuleEnum::EVENT,
                'action' => [
                    ['slug' => EventPermission::LIST_EVENT],
                    ['slug' => EventPermission::VIEW_EVENT],
                    ['slug' => EventPermission::ADD_EVENT],
                    ['slug' => EventPermission::EDIT_EVENT],
                    ['slug' => EventPermission::DELETE_EVENT],
                    ['slug' => EventPermission::ASSIGN_CHANNEL],
                    ['slug' => EventPermission::ASSIGN_USER],
                    ['slug' => EventPermission::ASSIGN_USER_GROUP]
                ]
            ],
            [
                'slug' => ModuleEnum::ASSESSMENT,
                'action' => [
                    ['slug' => AssessmentPermission::LIST_QUIZ],
                    ['slug' => AssessmentPermission::VIEW_QUIZ],
                    ['slug' => AssessmentPermission::ADD_QUIZ],
                    ['slug' => AssessmentPermission::EDIT_QUIZ],
                    ['slug' => AssessmentPermission::DELETE_QUIZ],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_CHANNEL],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_USER],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_USER_GROUP],
                    ['slug' => AssessmentPermission::ADD_QUESTION_BANK],
                    ['slug' => AssessmentPermission::EDIT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::DELETE_QUESTION_BANK],
                    ['slug' => AssessmentPermission::ADD_QUESTION],
                    ['slug' => AssessmentPermission::EDIT_QUESTION],
                    ['slug' => AssessmentPermission::DELETE_QUESTION],
                    ['slug' => AssessmentPermission::IMPORT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::IMPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::LIST_QUESTION_BANK],
                ]
            ],
            [
                'slug' => ModuleEnum::FLASHCARD,
                'action' => [
                    ['slug' => FlashCardPermission::LIST_FLASHCARD],
                    ['slug' => FlashCardPermission::ADD_FLASHCARD],
                    ['slug' => FlashCardPermission::VIEW_FLASHCARD],
                    ['slug' => FlashCardPermission::EDIT_FLASHCARD],
                    ['slug' => FlashCardPermission::DELETE_FLASHCARD],
                    ['slug' => FlashCardPermission::IMPORT_FLASHCARD],
                ]
            ],
            [
                'slug' => ModuleEnum::ANNOUNCEMENT,
                'action' => [
                    ['slug' => AnnouncementPermission::LIST_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ADD_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::VIEW_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::EDIT_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ASSIGN_MEDIA],
                    ['slug' => AnnouncementPermission::DELETE_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ASSIGN_CHANNEL],
                    ['slug' => AnnouncementPermission::ASSIGN_USER],
                    ['slug' => AnnouncementPermission::ASSIGN_USERGROUP]
                ]
            ],
            [
                'slug' => ModuleEnum::MANAGE_SITE,
                'action' => [
                    ['slug' => ManageSitePermission::LIST_FAQ],
                    ['slug' => ManageSitePermission::ADD_FAQ],
                    ['slug' => ManageSitePermission::EDIT_FAQ],
                    ['slug' => ManageSitePermission::DELETE_FAQ],
                    ['slug' => ManageSitePermission::VIEW_FAQ],
                    ['slug' => ManageSitePermission::LIST_STATICPAGE],
                    ['slug' => ManageSitePermission::ADD_STATICPAGE],
                    ['slug' => ManageSitePermission::EDIT_STATICPAGE],
                    ['slug' => ManageSitePermission::DELETE_STATICPAGE],
                    ['slug' => ManageSitePermission::VIEW_STATICPAGE],
                    ['slug' => ManageSitePermission::SITE_CONFIGURATION],
                    ['slug' => ManageSitePermission::CUSTOM_FIELDS],
                    ['slug' => ManageSitePermission::MANAGE_ATTRIBUTE],
                    ['slug' => ManageSitePermission::LIST_NEWSLETTER],
                    ['slug' => ManageSitePermission::DELETE_NEWSLETTER],
                    ['slug' => ManageSitePermission::EXPORT_NEWSLETTER],
                    ['slug' => ManageSitePermission::CONFIGURATION],
                    ['slug' => ManageSitePermission::MANAGE_CACHE],
                    ['slug' => ManageSitePermission::LIST_SITESETTING],
                    ['slug' => ManageSitePermission::EDIT_SITESETTING]
                ]
            ],
            [
                'slug' => ModuleEnum::E_COMMERCE,
                'action' => [
                    ['slug' => ECommercePermission::LIST_ORDER],
                    ['slug' => ECommercePermission::LIST_PROMO_CODE],
                    ['slug' => ECommercePermission::ADD_PROMO_CODE],
                    ['slug' => ECommercePermission::EDIT_PROMO_CODE],
                    ['slug' => ECommercePermission::DELETE_PROMO_CODE],
                    ['slug' => ECommercePermission::EXPORT_PROMO_CODE],
                    ['slug' => ECommercePermission::VIEW_ORDER],
                    ['slug' => ECommercePermission::EDIT_ORDER],
                    ['slug' => ECommercePermission::EXPORT_ORDER]
                ]
            ],
            [
                'slug' => ModuleEnum::HOME_PAGE,
                'action' => [
                    ['slug' => HomePagePermission::LIST_BANNERS],
                    ['slug' => HomePagePermission::ADD_BANNERS],
                    ['slug' => HomePagePermission::EDIT_BANNERS],
                    ['slug' => HomePagePermission::DELETE_BANNERS],
                    ['slug' => HomePagePermission::LIST_PARTNER],
                    ['slug' => HomePagePermission::ADD_PARTNER],
                    ['slug' => HomePagePermission::EDIT_PARTNER],
                    ['slug' => HomePagePermission::DELETE_PARTNER],
                    ['slug' => HomePagePermission::LIST_UPCOMING_COURSES],
                    ['slug' => HomePagePermission::ADD_UPCOMING_COURSES],
                    ['slug' => HomePagePermission::DELETE_UPCOMING_COURSES],
                    ['slug' => HomePagePermission::LIST_POPULAR_COURSES],
                    ['slug' => HomePagePermission::ADD_POPULAR_COURSES],
                    ['slug' => HomePagePermission::DELETE_POPULAR_COURSES],
                    ['slug' => HomePagePermission::LIST_TESTIMONIALS],
                    ['slug' => HomePagePermission::ADD_TESTIMONIALS],
                    ['slug' => HomePagePermission::EDIT_TESTIMONIALS],
                    ['slug' => HomePagePermission::DELETE_TESTIMONIALS]
                ]
            ],
            [
                'slug' => ModuleEnum::REPORT,
                'action' => [
                    ['slug' => ReportPermission::VIEW_REPORT],
                    ['slug' => ReportPermission::EXPORT_REPORT],
                ]
            ],
            [
                'slug' => ModuleEnum::ERP,
                'action' => [
                    ['slug' => ERPPermission::MANAGE_BULK_IMPORTS],
                ]
            ],
            [
                'slug' => ModuleEnum::SURVEY,
                'action' => [
                    ['slug' => SurveyPermission::LIST_SURVEY],
                    ['slug' => SurveyPermission::ADD_SURVEY],
                    ['slug' => SurveyPermission::EDIT_SURVEY],
                    ['slug' => SurveyPermission::DELETE_SURVEY],
                    ['slug' => SurveyPermission::EXPORT_SURVEY],
                    ['slug' => SurveyPermission::REPORT_SURVEY],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER_GROUP],
                    ['slug' => SurveyPermission::LIST_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::ADD_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::EDIT_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::DELETE_SURVEY_QUESTION],
                ]
            ],
            [
                'slug' => ModuleEnum::ASSIGNMENT,
                'action' => [
                    ['slug' => AssignmentPermission::LIST_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ADD_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EDIT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::DELETE_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EXPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::REPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP],
                ]
            ],
        ],
    ],
    "channel-admin" => [
        'admin_permissions' => [
            [
                'slug' => ModuleEnum::DAMS,
                'action' => [
                    ['slug' => DAMSPermission::LIST_MEDIA],
                    ['slug' => DAMSPermission::VIEW_MEDIA],
                    ['slug' => DAMSPermission::ADD_MEDIA],
                    ['slug' => DAMSPermission::EDIT_MEDIA],
                    ['slug' => DAMSPermission::DELETE_MEDIA],
                ]
            ],
            [
                'slug' => ModuleEnum::CHANNEL,
                'action' => [
                    ['slug' => ChannelPermission::LIST_CHANNEL],
                    ['slug' => ChannelPermission::VIEW_CHANNEL],
                    ['slug' => ChannelPermission::EDIT_CHANNEL],
                    ['slug' => ChannelPermission::DELETE_CHANNEL],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_CATEGORY],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_USER],
                    ['slug' => ChannelPermission::CHANNEL_ASSIGN_USER_GROUP],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_POST],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_QUESTION],
                    ['slug' => ChannelPermission::EXPORT_CHANNEL],
                ]
            ],
            [
                'slug' => ModuleEnum::EVENT,
                'action' => [
                    ['slug' => EventPermission::LIST_EVENT],
                    ['slug' => EventPermission::VIEW_EVENT],
                    ['slug' => EventPermission::ADD_EVENT],
                    ['slug' => EventPermission::DELETE_EVENT],
                    ['slug' => EventPermission::EDIT_EVENT],
                    ['slug' => EventPermission::ASSIGN_CHANNEL],
                    ['slug' => EventPermission::ASSIGN_USER],
                    ['slug' => EventPermission::ASSIGN_USER_GROUP],
                ]
            ],
            [
                'slug' => ModuleEnum::ASSESSMENT,
                'action' => [
                    ['slug' => AssessmentPermission::LIST_QUIZ],
                    ['slug' => AssessmentPermission::VIEW_QUIZ],
                    ['slug' => AssessmentPermission::ADD_QUIZ],
                    ['slug' => AssessmentPermission::EDIT_QUIZ],
                    ['slug' => AssessmentPermission::DELETE_QUIZ],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_CHANNEL],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_USER],
                    ['slug' => AssessmentPermission::QUIZ_ASSIGN_USER_GROUP],
                    ['slug' => AssessmentPermission::ADD_QUESTION_BANK],
                    ['slug' => AssessmentPermission::DELETE_QUESTION_BANK],
                    ['slug' => AssessmentPermission::EDIT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::ADD_QUESTION],
                    ['slug' => AssessmentPermission::EDIT_QUESTION],
                    ['slug' => AssessmentPermission::IMPORT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::IMPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::LIST_QUESTION_BANK],
                ]
            ],
            [
                'slug' => ModuleEnum::FLASHCARD,
                'action' => [
                    ['slug' => FlashCardPermission::LIST_FLASHCARD],
                    ['slug' => FlashCardPermission::ADD_FLASHCARD],
                    ['slug' => FlashCardPermission::VIEW_FLASHCARD],
                    ['slug' => FlashCardPermission::EDIT_FLASHCARD],
                    ['slug' => FlashCardPermission::DELETE_FLASHCARD],
                    ['slug' => FlashCardPermission::IMPORT_FLASHCARD],
                ]
            ],
            [
                'slug' => ModuleEnum::ANNOUNCEMENT,
                'action' => [
                    ['slug' => AnnouncementPermission::LIST_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ADD_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::VIEW_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::EDIT_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ASSIGN_MEDIA],
                    ['slug' => AnnouncementPermission::DELETE_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ASSIGN_CHANNEL],
                    ['slug' => AnnouncementPermission::ASSIGN_USER],
                    ['slug' => AnnouncementPermission::ASSIGN_USERGROUP]
                ]
            ],
            [
                'slug' => ModuleEnum::REPORT,
                'action' => [
                    ['slug' => ReportPermission::VIEW_REPORT],
                    ['slug' => ReportPermission::EXPORT_REPORT],
                ]
            ],
            [
                'slug' => ModuleEnum::SURVEY,
                'action' => [
                    ['slug' => SurveyPermission::LIST_SURVEY],
                    ['slug' => SurveyPermission::ADD_SURVEY],
                    ['slug' => SurveyPermission::EDIT_SURVEY],
                    ['slug' => SurveyPermission::DELETE_SURVEY],
                    ['slug' => SurveyPermission::EXPORT_SURVEY],
                    ['slug' => SurveyPermission::REPORT_SURVEY],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER_GROUP],
                    ['slug' => SurveyPermission::LIST_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::ADD_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::EDIT_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::DELETE_SURVEY_QUESTION],
                ]
            ],
            [
                'slug' => ModuleEnum::ASSIGNMENT,
                'action' => [
                    ['slug' => AssignmentPermission::LIST_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ADD_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EDIT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::DELETE_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EXPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::REPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP],
                ]
            ],
        ],
    ],
    "content-author" => [
        'admin_permissions' => [
            [
                'slug' => ModuleEnum::DAMS,
                'action' => [
                    ['slug' => DAMSPermission::LIST_MEDIA],
                    ['slug' => DAMSPermission::VIEW_MEDIA],
                    ['slug' => DAMSPermission::ADD_MEDIA],
                    ['slug' => DAMSPermission::EDIT_MEDIA],
                    ['slug' => DAMSPermission::DELETE_MEDIA],
                ]
            ],
            [
                'slug' => ModuleEnum::CHANNEL,
                'action' => [
                    ['slug' => ChannelPermission::LIST_CHANNEL],
                    ['slug' => ChannelPermission::VIEW_CHANNEL],
                    ['slug' => ChannelPermission::MANAGE_CHANNEL_POST],
                    ['slug' => ChannelPermission::EXPORT_CHANNEL],
                ]
            ],
            [
                'slug' => ModuleEnum::EVENT,
                'action' => [
                    ['slug' => EventPermission::LIST_EVENT],
                    ['slug' => EventPermission::VIEW_EVENT],
                    ['slug' => EventPermission::ADD_EVENT],
                    ['slug' => EventPermission::EDIT_EVENT],
                    ['slug' => EventPermission::DELETE_EVENT],
                ]
            ],
            [
                'slug' => ModuleEnum::ASSESSMENT,
                'action' => [
                    ['slug' => AssessmentPermission::LIST_QUESTION_BANK],
                    ['slug' => AssessmentPermission::LIST_QUIZ],
                    ['slug' => AssessmentPermission::VIEW_QUIZ],
                    ['slug' => AssessmentPermission::ADD_QUIZ],
                    ['slug' => AssessmentPermission::EDIT_QUIZ],
                    ['slug' => AssessmentPermission::DELETE_QUIZ],
                    ['slug' => AssessmentPermission::ADD_QUESTION_BANK],
                    ['slug' => AssessmentPermission::DELETE_QUESTION_BANK],
                    ['slug' => AssessmentPermission::EDIT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::ADD_QUESTION],
                    ['slug' => AssessmentPermission::EDIT_QUESTION],
                    ['slug' => AssessmentPermission::IMPORT_QUESTION_BANK],
                    ['slug' => AssessmentPermission::IMPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUIZ],
                    ['slug' => AssessmentPermission::EXPORT_QUESTION_BANK]
                ]
            ],
            [
                'slug' => ModuleEnum::FLASHCARD,
                'action' => [
                    ['slug' => FlashCardPermission::LIST_FLASHCARD],
                    ['slug' => FlashCardPermission::ADD_FLASHCARD],
                    ['slug' => FlashCardPermission::VIEW_FLASHCARD],
                    ['slug' => FlashCardPermission::EDIT_FLASHCARD],
                    ['slug' => FlashCardPermission::DELETE_FLASHCARD],
                    ['slug' => FlashCardPermission::IMPORT_FLASHCARD],
                ]
            ],
            [
                'slug' => ModuleEnum::ANNOUNCEMENT,
                'action' => [
                    ['slug' => AnnouncementPermission::LIST_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ADD_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::VIEW_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::EDIT_ANNOUNCEMENT],
                    ['slug' => AnnouncementPermission::ASSIGN_MEDIA],
                    ['slug' => AnnouncementPermission::ASSIGN_CHANNEL],
                    ['slug' => AnnouncementPermission::DELETE_ANNOUNCEMENT]
                ]
            ],
            [
                'slug' => ModuleEnum::REPORT,
                'action' => [
                    ['slug' => ReportPermission::VIEW_REPORT],
                    ['slug' => ReportPermission::EXPORT_REPORT],
                ]
            ],
            [
                'slug' => ModuleEnum::SURVEY,
                'action' => [
                    ['slug' => SurveyPermission::LIST_SURVEY],
                    ['slug' => SurveyPermission::ADD_SURVEY],
                    ['slug' => SurveyPermission::EDIT_SURVEY],
                    ['slug' => SurveyPermission::DELETE_SURVEY],
                    ['slug' => SurveyPermission::EXPORT_SURVEY],
                    ['slug' => SurveyPermission::REPORT_SURVEY],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER],
                    ['slug' => SurveyPermission::SURVEY_ASSIGN_USER_GROUP],
                    ['slug' => SurveyPermission::LIST_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::ADD_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::EDIT_SURVEY_QUESTION],
                    ['slug' => SurveyPermission::DELETE_SURVEY_QUESTION],
                ]
            ],
            [
                'slug' => ModuleEnum::ASSIGNMENT,
                'action' => [
                    ['slug' => AssignmentPermission::LIST_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ADD_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EDIT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::DELETE_ASSIGNMENT],
                    ['slug' => AssignmentPermission::EXPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::REPORT_ASSIGNMENT],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER],
                    ['slug' => AssignmentPermission::ASSIGNMENT_ASSIGN_USER_GROUP],
                ]
            ],
        ],
    ],
];
