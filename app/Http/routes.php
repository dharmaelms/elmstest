<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Cache filter
// Route::filter('no-cache', function ($route, $request, $response) {
//     $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
//     $response->headers->set('Pragma', 'no-cache');
//     $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
// });

Route::any('upload', ['middleware' => 'auth', 'uses' => 'FileController@upload']);
Route::any('browse', ['middleware' => 'auth', 'uses' => 'FileController@browse']);
Route::get('media_image/{slug}', 'Admin\DamsController@getShowMedia');
Route::get('media/{id}', ['as' => 'media', 'uses' => 'Admin\Question\QuestionController@getMedia']);
Route::any('akamairesponse', 'Admin\DamsController@anyResponse');

Route::group(['prefix' => 'password', 'namespace' => 'Auth'], function () {
    Route::get('forgot', 'PasswordController@getEmail');
    Route::post('forgot', 'PasswordController@postEmail');
    Route::get('reset/{token}', 'PasswordController@getReset');
    Route::post('reset', 'PasswordController@postReset');
});

// Mobile API Section
Route::group(['prefix' => 'api', 'namespace' => 'API'], function () {
    Route::controller('quiz', 'QuizController');
    Route::post('security/generate-token', 'ProgramAPIController@postGenerateToken');
    Route::get('channels', 'ProgramAPIController@getAllChannels');
    Route::get('channels/{channel_id}', 'ProgramAPIController@getAllChannels');
    Route::get('users/{email_id}/channels', 'ProgramAPIController@getUserCertificatesDetails');
    Route::get('users/{email_id}/channels/{channel_id}', 'ProgramAPIController@getUserCertificatesDetails');
    Route::controller('/', 'APIController');
});

// Playlyfe Section
Route::controller('pl', 'PlaylyfeController');

//akamai
Route::any('regenerateakamaitoken/{key}', 'AkamaiController@anyRegenerateAkamaiToken');

//Payment activity.
Route::group(['middleware' => ['auth']], function () {

    if (config('app.ecommerce') === true) {
        Route::controller('cp/pricing', 'Admin\Catalog\PricingController');
        Route::controller('cp/order', 'Admin\Catalog\OrderController');
        Route::controller('cp/tab', 'Admin\Catalog\TabController');
        Route::get('payment/status', [
            'as' => 'payment.status',
            'uses' => 'Portal\CheckoutController@getPaymentStatus',
        ]);
    }
});

// Admin section (Post login)
Route::group(['prefix' => 'cp', 'namespace' => 'Admin', 'middleware' => ['auth', 'cp', 'quiz']], function () {
    //Question admin routes
    Route::group(['prefix' => 'question', 'namespace' => 'Question'], function () {
        Route::get('add', ['as' => 'get-add-question', 'uses' => 'QuestionController@getAddQuestion']);
        Route::post('add', ['as' => 'post-add-question', 'uses' => 'QuestionController@postAddQuestion']);
        Route::get('edit/{question_bank_id}/{question_id}', ['as' => 'get-edit-question', 'uses' => 'QuestionController@getEditQuestion']);
        Route::post('edit', ['as' => 'post-edit-question', 'uses' => 'QuestionController@postEditQuestion']);
        Route::get('delete/{question_bank_id}/{question_id}', ['as' => 'delete-question', 'uses' => 'QuestionController@getDeleteQuestion']);
        Route::get('import-question', ['as' => 'get-import-question', 'uses' => 'QuestionController@getImportQuestion']);
        Route::post('import-question/{question_type}', ['as' => 'post-import-question', 'uses' => 'QuestionController@postImportQuestion']);
        Route::get('export-error-report', ['as' => 'get-export-error-report', 'uses' => 'QuestionController@getExportErrorReport']);
    });

    Route::group(['prefix' => 'assignment'], function () {
        Route::get('list-assignment', ['as' => 'get-list', 'uses' => 'AssignmentController@getIndex']);
        Route::get('list', ['as' => 'list', 'uses' => 'AssignmentController@getListAssignmentAjax']);
        Route::get('add', ['as' => 'get-add-assignment', 'uses' => 'AssignmentController@getAddAssignment']);
        Route::post('add', ['as' => 'post-add-assignment', 'uses' => 'AssignmentController@upsertAssignment']);
        Route::get('edit/{assignment_id}', ['as' => 'get-edit-assignment', 'uses' => 'AssignmentController@getEditAssignment']);
        Route::put('edit', ['as' => 'put-edit-assignment', 'uses' => 'AssignmentController@upsertAssignment']);
        Route::get('delete/{assignment_id}', ['as' => 'get-delete-assignment', 'uses' => 'AssignmentController@deleteAssignment']);
        Route::get('grade-assignment/{assignment_id}/{submission_type}', ['as' => 'get-grade-assignment', 'uses' => 'AssignmentController@getGradeAssignment']);
        Route::get('grade-list', ['as' => 'grade-list', 'uses' => 'AssignmentController@getGradeListAjax']);
        Route::post('post-assign', ['as' => 'post-assign-assignment', 'uses' => 'AssignmentController@postAssignAssignments']);
        Route::get('unassign-post/{assignment_id}/{from}', ['as' => 'unassign-post', 'uses' => 'AssignmentController@getUnassignPost']);
        Route::get('review-assignment/{assignment_id}/{user_id}', ['as' => 'review-assignment', 'uses' => 'AssignmentController@reviewAssignment']);
        Route::get('review-comments/{assignment_id}/{user_id}', ['as' => 'review-comments', 'uses' => 'AssignmentController@getReviewComments']);
        Route::get('online-text/{assignment_id}/{user_id}', ['as' => 'online-text', 'uses' => 'AssignmentController@getOnlineText']);
        Route::post('post-review-assignment/{assignment_id}/{user_id}', ['as' => 'post-review-assignment', 'uses' => 'AssignmentController@postReviewAssignment']);
    });

    Route::get("packages/{package_id}/programs", "PackageController@getPackagePrograms");
    Route::get("packages/{package_id}/users-count", "PackageController@getPackageUsersCount");
    Route::post("packages/{package_id}/assign-programs", "PackageController@assignPrograms");
    Route::post("packages/{package_id}/un-assign-programs", "PackageController@unAssignPrograms");

    Route::controllers([
        'customfields' => 'CustomFields\CustomFieldsController',
        'upcomingcourses' => 'Courses\UpcomingCoursesController',
        'popularcourses' => 'Courses\PopularCoursesController',
        'promocode' => 'PromoCodeController',
        'assessment' => 'AssessmentController',
        'section' => 'SectionController',
        'event' => 'EventController',
        'categorymanagement' => 'CategoryManagementController',
        'rolemanagement' => 'RoleManagementController',
        'usergroupmanagement' => 'UserGroupController',
        'banners' => 'BannerManagementController',
        'partnerlogo' => 'PartnerLogoController',
        'testimonials' => 'TestimonialController',
        'dashboard' => 'DashboardController',
        '/dams' => 'DamsController',
        'contentfeedmanagement' => 'ContentFeedManagementController',
        'package' => 'PackageController',
        'cronbulkimport' => 'CronBulkImportController',
        'bulkimport' => 'BulkImportController',
        'exportreports' => 'ExportReportController',
        'reports' => 'ReportController',
        'reportstbl' => 'ReportTblPopulateController',
        'webex' => 'EventReportController',
        '/sitesetting' => 'SiteSettingController',
        '/manageattribute' => 'ManageAttributeController', //lms
        '/lmscoursemanagement' => 'ManageLmsProgramController', //lms
        '/announce' => 'AnnouncementController',
        '/manageweb' => 'ManageWebController',
        'flashcards' => 'FlashCardsController',
        'migration' => 'MigrationController',
        'country' => 'CountryController',
        'assessmentmapping' => 'AssessmentMappingController',
        'certificates' => 'CertificatesController',
        'survey' => 'SurveyController',
        '/' => 'AdminController'

    ]);
});
