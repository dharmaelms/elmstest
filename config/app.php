<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services your application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY', 'SomeRandomString'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
    */

    'log' => 'daily',

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Debugbar Service Provider...
         */
        Barryvdh\Debugbar\ServiceProvider::class,

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Jenssegers\Mongodb\MongodbServiceProvider::class,
        Jenssegers\Mongodb\Auth\PasswordResetServiceProvider::class,
        Laravel\Socialite\SocialiteServiceProvider::class,
        DaveJamesMiller\Breadcrumbs\ServiceProvider::class,

        /*
         * Model Service Providers...
         */
        App\Providers\Model\Announcement\AnnouncementServiceProvider::class,
        App\Providers\Model\Catalog\AccessControlServiceProvider::class,
        App\Providers\Model\Catalog\CatalogServiceProvider::class,
        App\Providers\Model\Catalog\OrderServiceProvider::class,
        App\Providers\Model\Catalog\PricingServiceProvider::class,
        App\Providers\Model\Catalog\PromoCodeServiceProvider::class,
        App\Providers\Model\Category\CategoryRepositoryProvider::class,
        App\Providers\Model\ChannelCompletionTillDate\ChannelCompletionTillDateServiceProvider::class,
        App\Providers\Model\ContactUs\ContactUsRepositoryProvider::class,
        App\Providers\Model\Country\CountryServiceProvider::class,
        App\Providers\Model\Courses\PopularServiceProvider::class,
        App\Providers\Model\Courses\UpcomingServiceProvider::class,
        App\Providers\Model\CustomFields\CustomServiceProvider::class,
        App\Providers\Model\DimensionChannel\DimensionChannelServiceProvider::class,
        App\Providers\Model\Elastic\ElasticRepositoryProvider::class,
        App\Providers\Model\Event\EventServiceProvider::class,
        App\Providers\Model\EventReport\EventsAttendeeHistoryRepositoryProvider::class,
        App\Providers\Model\EventReport\EventsHistoryRepositoryProvider::class,
        App\Providers\Model\Leadsquared\LeadsquaredServiceProvider::class,
        App\Providers\Model\MyActivity\MyActivityRepositoryProvider::class,
        App\Providers\Model\Playlyfe\PlaylyfeServiceProvider::class,
        App\Providers\Model\Post\PostServiceProvider::class,
        App\Providers\Model\Program\ProgramServiceProvider::class,
        App\Providers\Model\QuizAttempt\QuizAttemptRepositoryProvider::class,
        App\Providers\Model\QuizAttemptData\QuizAttemptDataRepositoryProvider::class,
        App\Providers\Model\UserCertificate\UserCertificateServiceProvider::class,
        App\Providers\Model\Question\QuestionServiceProvider::class,
        App\Providers\Model\Quiz\QuizServiceProvider::class,
        App\Providers\Model\ScormActivity\ScormActivityServiceProvider::class,
        App\Providers\Model\Section\SectionRepositoryProvider::class,
        App\Providers\Model\SSO\SSOLogRepositoryProvider::class,
        App\Providers\Model\Tabs\TabServiceProvider::class,
        App\Providers\Model\Testimonial\TestimonialServiceProvider::class,
        App\Providers\Model\TransactionDetail\TransactionDetailServiceProvider::class,
        App\Providers\Model\Dams\DamsServiceProvider::class,
        App\Providers\Model\User\UserServiceProvider::class,
        App\Providers\Model\UserGroup\UserGroupServiceProvider::class,
        App\Providers\Model\Report\ReportRepositoryProvider::class,
        App\Providers\Model\AccessRequest\AccessRequestServiceProvider::class,
        App\Providers\Model\MyActivity\MyActivityServiceProvider::class,
        App\Providers\Model\PostFaq\PostFaqServiceProvider::class,
        App\Providers\Model\ProgramFaq\ProgramFaqServiceProvider::class,
        App\Providers\Model\QuizPerformance\QuizPerformanceRepositoryProvider::class,
        App\Providers\Model\ChannelAnalytic\OverAllChannalAnalyticRepositoryProvider::class,
        App\Providers\Model\WebExHost\WebExHostRepositoryProvider::class,

        /*
         * Service layer Service Providers...
         */
        App\Providers\Service\Announcement\AnnouncementServiceProvider::class,
        App\Providers\Service\Catalog\AccessControlServiceProvider::class,
        App\Providers\Service\Catalog\CatalogServiceProvider::class,
        App\Providers\Service\Catalog\OrderServiceProvider::class,
        App\Providers\Service\Catalog\PaymentServiceProvider::class,
        App\Providers\Service\Catalog\PricingServiceProvider::class,
        App\Providers\Service\Catalog\PromoCodeServiceProvider::class,
        App\Providers\Service\ChannelCompletionTillDate\ChannelCompletionTillDateServiceProvider::class,
        App\Providers\Service\Country\CountryServiceProvider::class,
        App\Providers\Service\Courses\PopularServiceProvider::class,
        App\Providers\Service\Courses\UpcomingServiceProvider::class,
        App\Providers\Service\CustomFields\CustomServiceProvider::class,
        App\Providers\Service\DimensionChannel\DimensionChannelServiceProvider::class,
        App\Providers\Service\Elastic\ElasticServiceProvider::class,
        App\Providers\Service\Event\EventServiceProvider::class,
        App\Providers\Service\EventReport\EventReportServiceProvider::class,
        App\Providers\Service\Leadsquared\LeadsquaredServiceProvider::class,
        App\Providers\Service\Playlyfe\PlaylyfeServiceProvider::class,
        App\Providers\Service\Post\PostServiceProvider::class,
        App\Providers\Service\Program\ProgramServiceProvider::class,
        App\Providers\Service\QuizAttempt\QuizAttemptServiceProvider::class,
        App\Providers\Service\QuizAttemptData\QuizAttemptDataServiceProvider::class,
        App\Providers\Service\UserCertificate\UserCertificateServiceProvider::class,
        App\Providers\Service\Question\QuestionServiceProvider::class,
        App\Providers\Service\Quiz\QuizServiceProvider::class,
        App\Providers\Service\Report\ReportServiceProvider::class,
        App\Providers\Service\ScormActivity\ScormActivityServiceProvider::class,
        App\Providers\Service\SSO\SSOServiceProvider::class,
        App\Providers\Service\Tabs\TabServiceProvider::class,
        App\Providers\Service\Testimonial\TestimonialServiceProvider::class,
        App\Providers\Service\DAMS\DAMsServiceProvider::class,
        App\Providers\Service\FlashCard\FlashCardServiceProvider::class,

        App\Providers\Service\TransactionDetail\TransactionDetailServiceProvider::class,
        App\Providers\Service\User\UserServiceProvider::class,
        App\Providers\Service\UserGroup\UserGroupServiceProvider::class,
        App\Providers\Service\Box\BoxServiceProvider::class,
        
        Portal\Providers\PortalServiceProvider::class,

        App\Providers\Service\AccessRequest\AccessRequestServiceProvider::class,
        App\Providers\Service\MyActivity\MyActivityServiceProvider::class,
        App\Providers\Service\PostFaq\PostFaqServiceProvider::class,
        App\Providers\Service\ProgramFaq\ProgramFaqServiceProvider::class,

        //Roles and permissions module repository and service providers
        App\Providers\Model\RolesAndPermissions\ContextRepositoryProvider::class,
        App\Providers\Model\RolesAndPermissions\ModuleRepositoryProvider::class,
        App\Providers\Model\RolesAndPermissions\RoleRepositoryProvider::class,
        App\Providers\Model\RolesAndPermissions\PermissionRepositoryProvider::class,
        App\Providers\Service\Role\RoleServiceProvider::class,
        App\Providers\Service\Module\ModuleServiceProvider::class,

        App\Providers\Model\User\UserRepositoryProvider::class,
        App\Providers\Model\FlashCard\FlashCardRepositoryProvider::class,

        //UserGroup repository and service providers
        App\Providers\Service\UserGroup\UserGroupServiceProvider::class,
        App\Providers\Model\UserGroup\UserGroupRepositoryProvider::class,

        //Package repository and service provider
        App\Providers\Service\Package\PackageServiceProvider::class,
        App\Providers\Model\Package\PackageRepositoryProvider::class,

        //PostFaqAnswer repository and service provider
        App\Providers\Model\PostFaqAnswer\PostFaqAnswerRepositoryProvider::class,
        App\Providers\Service\PostFaqAnswer\PostFaqAnswerServiceProvider::class,

        //Survey repository and service provider
        App\Providers\Model\Survey\SurveyRepositoryProvider::class,
        App\Providers\Service\Survey\SurveyServiceProvider::class,

        //DeletedEventsRecordings repository and service provider
        App\Providers\Model\DeletedEventsRecordings\DeletedEventsRecordingsRepositoryProvider::class,
        App\Providers\Service\DeletedEventsRecordings\DeletedEventsRecordingsServiceProvider::class,

        //Client security
        App\Providers\Model\ClientSecurity\ClientSecurityRepositoryProvider::class,
        App\Providers\Service\ClientSecurity\ClientSecurityServiceProvider::class,

        //Assignment repository and service provider
        App\Providers\Model\Assignment\AssignmentRepositoryProvider::class,
        App\Providers\Service\Assignment\AssignmentServiceProvider::class

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Input' => Illuminate\Support\Facades\Input::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

        //sitesetting model
        'SiteSetting' => App\Model\SiteSetting::class,
        /*
         * Custom Aliases
         */
        'Moloquent' => Jenssegers\Mongodb\Eloquent\Model::class,
        'Common' => App\Model\Common::class,
        'Carbon' => Carbon\Carbon::class,
        'Timezone' => App\Libraries\Timezone::class,
        'Debugbar' => Barryvdh\Debugbar\Facade::class,
        'Socialite' => Laravel\Socialite\Facades\Socialite::class,
        'Helpers' => App\Libraries\Helpers::class,
        'Breadcrumbs' => DaveJamesMiller\Breadcrumbs\Facade::class,

        'ContextsEnum' => App\Enums\RolesAndPermissions\Contexts::class,
        'ModuleEnum' => App\Enums\Module\Module::class,
        'ElasticType' => App\Enums\Elastic\Types::class,
        'ElementType' => \App\Enums\Program\ElementType::class,
        'UserPermission' => App\Enums\User\UserPermission::class,
        'PermissionType' => App\Enums\RolesAndPermissions\PermissionType::class,
        'UserGroupPermission' => App\Enums\UserGroup\UserGroupPermission::class,
        'RolePermission' => App\Enums\RolesAndPermissions\RolePermission::class,
        'ChannelPermission' => App\Enums\Program\ChannelPermission::class,
        'CoursePermission' => App\Enums\Course\CoursePermission::class,
        'PackagePermission' => App\Enums\Package\PackagePermission::class,
        'ERPPermission' => App\Enums\ERP\ERPPermission::class,

        'CategoryPermission' => App\Enums\Category\CategoryPermission::class,
        'EventPermission' => App\Enums\Event\EventPermission::class,
        'DAMSPermission' => \App\Enums\DAMS\DAMSPermission::class,
        'AssessmentPermission' => App\Enums\Assessment\AssessmentPermission::class,
        'AnnouncementPermission' => App\Enums\Announcement\AnnouncementPermission::class,
        'ECommercePermission' => App\Enums\ECommerce\ECommercePermission::class,
        'FlashCardPermission' => App\Enums\FlashCard\FlashCardPermission::class,
        'ManageSitePermission' => App\Enums\ManageSite\ManageSitePermission::class,
        'HomePagePermission' => App\Enums\HomePage\HomePagePermission::class,
        "CountryPermission" =>  App\Enums\Country\CountryPermission::class,
        'AttemptHelper' => App\Helpers\Quiz\AttemptHelper::class,
        'QCFT' => App\Enums\Quiz\CutoffFormatType::class,
        'NDA' => App\Enums\User\NDAStatus::class,
        'ReportPermission' => App\Enums\Report\ReportPermission::class,
        'QuizType' => App\Enums\Quiz\QuizType::class,
        'PostChannels' => App\Enums\Post\PostChannels::class,
        'SurveyPermission' => App\Enums\Survey\SurveyPermission::class,
        'AssignmentPermission' => App\Enums\Assignment\AssignmentPermission::class

    ],

    //variable initialisation
    'user_bulkimport_path' => storage_path().'/framework/user_bulkimport/',
    'thumb_resolutions' => ['160x160','180x180','400x400','1024x1024','178x114'],
    'private_dams_images_path' => '../dams/images/',
    'public_dams_images_path' => 'dams/images/',
    'private_dams_documents_path' => '../dams/documents/',
    'public_dams_documents_path' => 'dams/documents/',
    'private_dams_scorm_path' => '../dams/scorms/',
    'public_dams_scorm_path' => 'dams/scorms/',
    'scorm_file_name' => '/index.html',
    'private_dams_audio_path' => '../dams/audio/',
    'public_dams_audio_path' => 'dams/audio/',
    'dams_temp_video_path' => '../dams/videotemp/',
    'dams_video_thumb_path' => '../dams/videothumb/',
    'dams_srt_path' => '../dams/videosrt/',
    'dams_bulkimport_path' => '../dams/bulkimport/',
    'dams_audio_extensions' => ['mp3'],
    'dams_audio_mime_types' => ['audio/mp3', 'audio/mpeg', 'audio/mpeg3'],
    'dams_document_extensions' => ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'txt', 'rtf', 'R', 'tsv', 'csv', 'c', 'cpp', 'py', 'whl'],
    'dams_document_mime_types' => [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.ms-excel',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/html',
        'text/rtf',
        'text/csv',
        'application/octet-stream',
        'application/zip',
        'application/vnd.ms-office',
        'text/plain',
        'text/tab-separated-values',
        'text/x-c',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-powerpoint',
        'text/x-python',
    ],
    'dams_image_extensions' => ['jpeg', 'png', 'jpg'],
    'dams_max_upload_size' => env('DAMS_MAX_UPLOAD_SIZE', 512),
    'dams_media_library_transcoding' => env('MEDIA_LIBRARY_TRANSCODING', false),
    'dams_video_extensions' => ['mp4', 'avi', 'wmv'],
    'default_document_image' => './admin/img/icons/document.png',
    'default_audio_image' => './admin/img/icons/audio-icon.png',
    'default_link_image' => './admin/img/icons/link.png',
    'default_quiz' => './admin/img/icons/quiz.png',
    'default_event' => './admin/img/icons/intro_events.png',
    'default_user' => '/admin/img/icons/user.png',
    'site_logo_path' => 'portal/theme/default/img/logo/',
    'default_logo_path' => 'portal/theme/default/img/default_logo.png',
    'site_banners_path' => 'uploads/revolutionslider/',
    'mobile_banner_resolution'=>'1024x400',
    'mobile_banner_portrait_resolution'=>'640x240',
    'mobile_banner_landscape_resolution'=>'1024x400',
    'default_banner_path' => 'portal/theme/default/img/default_banner.jpg',
    'no_image' => 'portal/theme/default/img/noimage.png',
    'solid_black' => 'portal/theme/default/img/solid_black.png',
    'partner_logo_path' => 'uploads/partnerlogo/',
    'partner_logo_resolution' => '210x100',
    'user_profile_pic_resolution' => '109x109',
    'profile_pic_path' => env('PROFILE_PIC_PATH'), // TODO: This entry has to be removed.
    'testimonials_path' => env('TESTIMONIALS_PATH'),
    'bulk_upload' => [
        'csv_path' => '../dams/bulkimport/csv/',
        'csv_processed_path' => '../dams/bulkimport/csv_processed/',
        'files_path' => '../dams/bulkimport/files/',
    ],
    'user_pic_width' => '109',
    'user_pic_height' => '109',
    'user_profile_pic' => env('PROFILE_PIC_PATH'),
    'no_profile_pic' => 'portal/theme/default/img/profilepic.png',

    'banner_type' => [
        'home_banner_image' => 'home',
        'category_banner_image' => 'category',
    ],

    //Scorm incompatible files
    'scorm_incompatible_files' => ['php', 'jsp', 'asp', 'exe'],

    // Kaltura Configs
    'uniconf_id' => '23448134',
    'kaltura_url' => 'http://kaltura/',
    'partnerId' => '101',
    'admin_secret' => '43c2243a30839fd09eb1f84ad538cd6c',

    // Date format
    'date_format' => 'd-m-Y',
    'reports_date_format' => 'd/m/Y',
    'date_time_format' => 'd-m-Y g:i A',
    'date_ymd_his' => 'Y-m-d H:i:s',
    'default_timezone' => env('APP_SITE_TIMEZONE'),
    'site_name' => env('APP_SITE_NAME'),
    'admin_role_id' => 1,
    'learner_role_id' => 3,
    'register_role_id' => 9,
    'default_records_per_page' => 9,
    'application_version'=>env('APP_SITE_VERSION', 'Master'),
    'verify_url' => env('APP_VERIFY_URL'), // For email verification- bindhya
    'admin_support_email' => env('ADMIN_SUPPORT_EMAIL'),
    'program_date_format' => 'F j, Y', // Date format using to display in catalog listing.
    // Webex configuration
    'webex_servicelayer_url' => env('WEBEX_SERVICELAYER_URL'),
    'webex_appkey' => env('WEBEX_APPKEY'),
    'webex_username' => env('WEBEX_USERNAME'),
    'webex_password' => env('WEBEX_PASSWORD'),
    'webex_nbr_url' => env('WEBEX_NBR_SERVER_URL'),
    'webex_site_id' => env('WEBEX_SITE_ID'),
    'webex_admin_username' => env('WEBEX_ADMIN_USERNAME'),
    'webex_admin_password' => env('WEBEX_ADMIN_PASSWORD'),
    'webex_record_download' => env('WEBEX_RECORD_DOWNLOAD', false),
    'webex_recording_path' => '/dams/recordings/',
    'gdrive' => [
        'service' => env('GOOGLE_DRIVE_UPLOAD'),
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_SECRET_KEY'),
        'access_token' => env('GOOGLE_DRIVE_ACCESS_TOKEN'),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID')
    ],

    //notification and announcement Config
    'notification_delay_read' => 5000,
    'announcenet_no_display_home' => 3,
    'content_max_char_display' => 100,
    //Theme
    'portal_theme_name' => 'default',
    //language
    'language' => ['English'],
    //Home page testimonial display charcters
    'testimonial_pagination' => 10,
    // Notifications
    'notifications' => [
        'user' => [
            'assign_usergroup' => true,
            'assign_feed' => true,
            'unassign_usergroup' => true,
            'unassign_feed' => true,
        ],
        'usergroup' => [
            'assign_user' => true,
            'assign_feed' => true,
            'unassign_user' => true,
            'unassign_feed' => true,
        ],
        'dams' => [
            'assign_user' => true, // if a media is assigned to a user
            'assign_usergroup' => true, // if a media is assigned to a user group
            'mediarevoke' => true, // when a media permission is revoked.
        ],
        'packetsfaq' => [
            'answered' => true, // if the user's question get an answer
            'addtofaq' => true, // if the user's question is marked as FAQ
        ],
        'contentfeed' => [
            'metadatachange' => true, // If a content feed's meta data is been changed
            'packetmetadatachange' => true, // If a packet's meta data is been changed
            'packetadd' => true, // when a new packet is added.
            'feedadd' => true, // when a new feed is added.
            'feedrevoke' => true, // when a feed permission is revoked.
        ],
        'assessment' => [
            'assign_user' => true, // if a assessment is assigned to a user
            'assign_usergroup' => true, // if a assessment is assigned to a user group
            'unassign_user' => true, // if a assessment is assigned to a user
            'unassign_usergroup' => true, // if a assessment is assigned to a user group
        ],
        'event' => [
            'assign_user' => true, // if a assessment is assigned to a user
            'assign_usergroup' => true, // if a assessment is assigned to a user group
            'unassign_user' => true, // if a assessment is assigned to a user
            'unassign_usergroup' => true, // if a assessment is assigned to a user group
        ],
        'images' => [
            'assessment' => '/portal/theme/default/img/announce/notifications_assessments.png',
            'event' => '/portal/theme/default/img/announce/notifications_event.png',
            'packet' => '/portal/theme/default/img/announce/notifications_post.png',
            'program' => '/portal/theme/default/img/announce/notifications_packet.png',
            'dams' => '/portal/theme/default/img/announce/notifications_library.png',
            'default' => '/portal/theme/default/img/announce/notifications_general.png',
        ],
        'survey' => [
            'assign_user' => true, // if a assessment is assigned to a user
            'assign_usergroup' => true, // if a assessment is assigned to a user group
            'unassign_user' => true, // if a assessment is assigned to a user
            'unassign_usergroup' => true, // if a assessment is assigned to a user group
        ],

    ],
    'email' => [
        'contentfeed' => [
            'feedadd' => true, // when a new feed is added.
        ],
    ],

    // Solarium
    'config' => [
        'endpoint' => [
            'localhost' => [
                'host' => env('SOLR_HOST'),
                'port' => env('SOLR_PORT'),
                'path' => env('SOLR_PATH'),
            ],
        ],
    ],

    // Box Settings
    'box' => [
        'sdk_info' => [
            'client_id' => env('BOX_CLIENT_ID'),
            'client_secret' => env('BOX_CLIENT_SECRET')
        ],
        'app_auth_info' => [
            'key_id' => env('BOX_KEY_ID'),
            'private_key' => env('BOX_KEY_PATH', ''),
            'pass_phrase' => env('BOX_PASS_PHRASE'),
            'id' => env('BOX_OWNER_ID'),
            'subscription_type' => env('BOX_SUBSCRIPTION_TYPE', \Linkstreet\Box\Enums\SubscriptionType::ENTERPRISE),
        ],
        'others' => [
            'folder_name' => env('BOX_FOLDER_NAME', false)
        ]
    ],

    // Akamai
    'akamai' => [
        // FTP configs
        'default_driver' => env('AKA_FTP_DRIVER'),
        'host' => env('AKA_FTP_HOST'),
        'key' => env('AKA_FTP_KEY'),
        'key_name' => env('AKA_FTP_KEYNAME'),
        'base_url' => env('AKA_FTP_BASIC_URL'),
        'folder_name' => env('AKA_FTP_FOLDER'),
        'version' => (int) env('AKA_VERSION'),

        // FTP locations
        'ftp_base_loc' => env('AKA_FTP_BASE_LOC'),
        'ftp_no_transcoding_loc' => env('AKA_FTP_NT_LOC'),
        'ftp_image_loc' => env('AKA_FTP_IMAGE_LOC'),
        'ftp_success_loc' => env('AKA_FTP_SUCCESS_LOC'),
        'ftp_failure_loc' => env('AKA_FTP_FAILURE_LOC'),
        'ftp_delivery_loc' => env('AKA_FTP_DELIVERY_LOC'),

        // Global Settings
        'transcoding' => env('AKA_TRANSCODING'), // This setting should be changed only if akamai watch location is changed
        'video_thumbnail' => env('AKA_THUMBNAIL'), // This setting enabled if image generation is configured in akamai

        // Streaming url locations
        'ftp_no_transcoding_url' => env('AKA_FTP_NT_URL'),
        'ftp_success_url' => env('AKA_FTP_SUCCESS_URL'),
        'ftp_delivery_url' => env('AKA_FTP_DELIVERY_URL'),
        'streaming_url_flash' => env('AKA_STREAM_URL_FLASH'),
        'streaming_url_html' => env('AKA_STREAM_URL_HTML'),
        'delivery_streaming_url_flash' => env('AKA_DELIVERY_STREAM_URL_FLASH'),
        'delivery_streaming_url_html' => env('AKA_DELIVERY_STREAM_URL_HTML'),
    ],

    'jwplayer' => [
        'key' => env('JWPLAYER_KEY'),
    ],

    'sso' => [
        'client_secret' => env('SSO_CLIENT_SECRET'),
        'client_id' => env('SSO_CLIENT_ID'),
        'usergroup_id' => env('SSO_USERGROUP_ID'),
    ],

    'akamai_analytics' => [
        'html5' => [
            'config_file' => env('HTML5_CONFIG_FILE'),
        ],
    ],

    'super_admin' => 1,
    'site_admin' => 2,

    // Mobile Application configurations.
    'mobile' => [
        'announcement_dashboard_number' => 2,
        'posts_dashboard_number' => 10,
        'assessments_per_page' => 10,
        'announcements_per_page' => 10,
        'notifications_per_page' => 10,
        'posts_per_page' => 10,
        'programs_per_page' => 10,
    ],

    // Site Admin Info

    'site_admin_name' => env('SITE_ADMIN_NAME'),
    'site_admin_email' => env('SITE_ADMIN_EMAIL'),
    'contact_us_email' => env('CONTACT_US_EMAIL'),
    'captcha_site_key' => env('CAPTCHA_SITE_KEY'),

    //Playlyfe credentials
    'playlyfe' => [
        'version' => env('VERSION'),
        'client_id' => env('CLIENT_ID'),
        'client_secret' => env('CLIENT_SECRET'),
        'type' => env('TYPE'),
        'enabled' => env('ENABLED')
    ],

    //Pricing Module
    'pricing' => [
        'enabled' => env('pricing')
    ],

    //Pricing Module
    'payment' => [
        'product_payumoney' => env('PRODUCT_PAYUMONEY', 'true'),
        'channel_payumoney' => env('CHANNEL_PAYUMONEY', 'true'),
        'product_cod' => env('PRODUCT_COD', 'true'),
        'channel_cod' => env('CHANNEL_COD', 'false'),
    ],

    //Payment Configuration
    'payment_options' => [
        'paypal' => [
            'payment_id' => 1,
            'payment_name' => 'Paypal',
            'status' => 'ACTIVE'
        ],
        'payumoney' => [
            'payment_id' => 2,
            'payment_name' => 'Pay U Money',
            'status' => 'ACTIVE'
        ],
        'banktransfer' => [
            'payment_id' => 3,
            'payment_name' => 'Bank Transfer',
            'status' => 'ACTIVE'
        ],
        'cashondelivery' => [
            'payment_id' => 4,
            'payment_name' => 'Cash On Delivery',
            'status' => 'ACTIVE'
        ]
    ],

    //Element title get from repective module(dams, event , quiz, flashcard)
    'get_ele_live' => true,
    //Notification bulk insert limit
    'bulk_insert_limit' => 100,
    'user_report_limit' => 500,
    'question_per_block' => env('QUESTION_PER_BLOCK', 26),
    'assessment_template' => env('ASSESSMENT_TEMPLATE', 'DEFAULT'),

    #MathMl settings
    'cache_path' => env('CACHE_PATH'),
    'formulas_path' => env('FORMULAS_PATH'),

    #Ecommerce
    'ecommerce' => env('ECOMMERCE', false),
    'promocode_user_enabled' => env('PROMOCODE_USER_ENABLED', false),
    'site_currency' => env('SITE_CURRENCY', 'INR'),
    
    #FTP Credentials
    'ftp_enabled' => env('FTP_ENABLED'),
    'ftp_host' => env('FTP_HOST'),
    'ftp_username' => env('FTP_USERNAME'),
    'ftp_password' => env('FTP_PASSWORD'),
    'ftp_port' => env('FTP_PORT'),
    'file_path' => env('FILE_PATH'),
    'import_email' => env('IMPORT_EMAIL', env('SITE_ADMIN_EMAIL')),
    
    #Importing file names
    'user_import_file' => env('USER_FILE'),
    'package_import_file' => env('PACKAGE_FILE'),
    'channel_import_file' => env('CHANNEL_FILE'),
    'package_usergroup_import_file' => env('PACKAGE_USERGROUP_FILE'),
    'channel_user_import_file' => env('CHANNEL_USER_FILE'),
    'user_usergroup_import_file' => env('USER_USERGROUP_FILE'),

    'socialite_redirect' => env('SOCIALITE_REDIRECT', '/dashboard'),

    //LeadSquared credentials
    'leadsquared' => [
        'LSQ_ACCESSKEY' => env('LSQ_ACCESSKEY'),
        'LSQ_SECRETKEY' => env('LSQ_SECRETKEY'),
        'enabled' => env('LEADSQUARED'),
        'apisite' => env('API_URL'),
        'log' => env('LOG_FILE'), // storage/logs
        'cookiename' => env('LEAD_COOKIE_NAME'),
        'pid_tracker' => env('PIT_TRACKER'),
    ],
    
    

    /* Admin Side Export meta-data's*/
    'ChannelExportUserFields' => ['firstname', 'lastname', 'username', 'email'],
    'ChannelExportChannelFields' => ['program_title','relations','updated_at', 'updated_by_name', 'created_by_name', 'status', 'program_shortname'],
    'QuizExportQuizFields' => ['questions', 'quiz_name', 'updated_at', 'created_at', 'updated_by', 'created_by'],
    'QuizExportforChannel' => ['quiz_name', 'updated_at', 'updated_by', 'relations', 'created_by', 'created_at'],
    'QuestionBankFields' => ['question_bank_id','question_bank_name', 'questions','updated_at','updated_by'],
   
    //Over all channel analytic config
    'channelAnalytic' => 'on',

    'dashboard_template' => env('DASHBOARD_TEMPLATE', 3),


    /* Portal side type filter ON/OFF settings*/
   
    'showTypeFilterInChannelContent' => 'on',

    'email_verification' => env('EMAIL_VERIFICATION', 'ACTIVE'),
   
    'redirect_default_login' => env('REDIRECT_DEFAULT_LOGIN', '/dashboard'),

    'default_system_user_ids' => [1, 2],
    'enable_video_token_encryption' => env('ENABLE_VIDEO_TOKEN_ENCRYPTION'), /* on / off */
    'video_token_buffer_mins'       => env('VIDEO_TOKEN_BUFFER_IN_MINS'), /* in Mins */
    
    'video_token_auth_key' => env('VIDEO_TOKEN_AUTH_KEY'),
    
    'admin_order_email' => env('ADMIN_ORDER_EMAIL', env('SITE_ADMIN_EMAIL')),

    'max_addmedia_items' => 10,

    'role_and_permission' => env('ROLE_AND_PERMISSION', 'INACTIVE'),

    'enable_saml' => env('ENABLE_SAML', 'off'), /* on / off */

    /* Paths to download sample import templates */
    'upload_templates' => [
        'questionbanks_bulk_import' => resource_path('/assets/upload_templates/questionbanks_bulk_import.csv'),
    ],
    //Google analytics
    'ganalytic' => [
        'key' => env('GOOGLE_ANALYTICS_KEY'),
    ],

    'color' => [
        'dashboard_category' => env('CATEGORY_COLOR_CODE', '#297076'),
    ],

    'download_flash_player' => '//support.mozilla.org/en-US/kb/install-flash-plugin-view-videos-animations-games',

    /* For Reports*/
    'default_date_range_selected' => 30,
    'max_date_range_selected' => 92,
    'char_limit_dropdown' => 75,
    'limit_items_dropdown' => 500,
    'limit_bars_chart' => 20,
    /* tabs title char limits*/
    'tab_char_limit' => 20,

    'program_auto_redirect' => env('PROGRAM_AUTO_REDIRECT', '/dashboard'),

    'enable_registration_redirect' => env('ENABLE_REGISTRATION_REDIRECT', false),
    'registration_default_redirect' => env('REGISTRATION_DEFAULT_REDIRECT'),
    
    'show_complete_functionalities' => env('SHOW_COMPLETE_FUNCTIONALITIES', false),

    /* Certificate template */
    'certificate_template' => env('CERTIFICATE_TEMPLATE'),
    'second_logo' => 'portal/theme/default/img/certificate_logos/Somany Learning logo (R).png',
    'signature_image' => 'portal/theme/default/img/certificate_logos/Sign for Certificate.jpg',
    'signature_name' => env('SIGNATURE_NAME', ''),
    'list_certificate' => env('LIST_CERTIFICATES', false),

    /* Validation for general events to schedule the event within the below mention number of day*/
    'general_event_max_days' => env('GENERAL_EVENT_MAX_DAYS', 90),
    'dashboard_cat_display' => env('DASHBOARD_CATEGORY_DISPLAY', false),

    /*Show Deleted events recordings */
    'show_deleted_events_recordings' => env('SHOW_DELETED_EVENTS_RECORDINGS', 'false'),

    //Client security
    'client_security' => [
        'client_secret' => env('SECURITY_CLIENT_SECRET'),
        'client_id' => env('SECURITY_CLIENT_ID'),
    ],

    //Assignments file details
    'assignments_document_extensions' => ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'tar', 'csv'],
    'assignments_document_mime_types' => [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/pdf',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'application/vnd.ms-office',
        'application/zip',
        'application/x-rar-compressed',
        'application/octet-stream.zip',
        'application/octet-stream',
        'application/x-tar',
        'text/csv',
    ],
    'public_assignments_documents_path' => 'assignments/',
    'assignments_max_upload_size' => env('ASSIGNMENTS_MAX_UPLOAD_SIZE', 512),
    'assignment_max_upload_size' => 25,
    'assignment_resubmission' => env('ASSIGNMENT_RESUBMISSION', false),

    // dispaly q&a in portal
    'display_portal_q&a' =>env('DISPLAY_PORTAL_Q&A', true),

];
