<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;

class SiteSettingsTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'site_settings';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('id');
        });

        // General settings
        DB::collection($collection)->insert([
            'id' => 1,
            'module' => 'General',
            'setting' => [
                'products_per_page' => 15,
                'faq' => 'on',
                'static_pages' => 'on',
                'edit_quiz_till' => 240, // quiz can be edited before 240 minutes of start time.
                'notification' => 'off',
                'email' => 'off',
                'watch_now' => 'on',
                'package' => 'off',
                'posts' => 'on',
                'favorites' => 'on',
                'sort_by' => 'updated_at',
                'more_feeds' => 'on',
                'general_category_feeds' => 'off',
                'default_page_on_login' => '',
                'site_Type' => '',
                'ecommerce' => '',
                'language' => '',
                'my_activities' => 20,
                'area_improve' => 'off',
                'quiz_marics' => [
                    'quiz_speed' => 'on',
                    'quiz_accuracy' => 'on',
                    'quiz_score' => 'on',
                    'channel_completion' => 'on'
                ],
                'events' => 'on',
                'assessments' => 'on',
                'moodle_courses' => 'off',
                'email_for_add_user' => 'off',
                'scorm_reports' => 'on',
            ]
        ]);

        // Category settings
        DB::collection($collection)->insert([
            'id' => 2,
            'module' => 'Category',
            'setting' => [
                'categories_or_feeds' => 20
            ]
        ]);

        // Search settings
        DB::collection($collection)->insert([
            'id' => 3,
            'module' => 'Search',
            'setting' => [
                'results_per_page' => 10,
                'simple' => 'on',
                'advanced' => 'on',
                'facet' => 'off'
            ]
        ]);

        // Notification & announcement settings
        DB::collection($collection)->insert([
            'id' => 4,
            'module' => 'Notifications and Announcements',
            'setting' => [
                'displayed_in_popup' => 10,
                'chars_announcment_list_page' => 150,
                'ann_expire_date' => 7
            ]
        ]);

        // Assessment settings
        DB::collection($collection)->insert([
            'id' => 5,
            'module' => 'Assessment',
            'setting' => [
                'assessment_key' => 'on',
                'assessment_assign_quz_to_cf' => 'on',
                'assessment_min_no_of_qus' => 2,
                'assessment_allow_attempts' => 3,
                'assessment_defult_marks_for_qus' => 2
            ]
        ]);

        // Event settings
        DB::collection($collection)->insert([
            'id' => 6,
            'module' => 'Event',
            'setting' => [
                'event_key' => 'on',
                'event_assign_to_cf' => 'on',
                'event_service_lay_url' => '',
                'event_username' => '',
                'event_password' => '',
                'event_app_key' => '',
                'event_open_time' => '15'
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 7,
            'module' => 'Socialite',
            'setting' => [
                'enabled' => 'on',
                'register' => 'on',
                'login' => 'on',
                'facebook' => 'off',
                'google' => 'off',
                'mobile_app' => 'off',
                'landing_page' => 'off'
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 8,
            'module' => 'MathML',
            'setting' => [
                'mathml_editor' => 'off',
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 9,
            'module' => 'PaymentGateway',
            'setting' => [
                'paypal' => [
                    'name' => 'PayPal',
                    'status' => 'ACTIVE',
                    'slug' => 'PayPal'
                ],
                'PayUMoney' => [
                    'name' => 'PayUMoney',
                    'status' => 'ACTIVE',
                    'slug' => 'PayUMoney'
                ],
                'CashOnDelivery' => [
                    'name' => 'Cash On Delivery',
                    'status' => 'ACTIVE',
                    'slug' => 'COD'
                ],
                'BankTransfer' => [
                    'name' => 'Bank Transfer',
                    'status' => 'ACTIVE',
                    'slug' => 'BANK TRANSFER'
                ]
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 10,
            'module' => 'Homepage',
            'setting' => [
                'UpcomingCourses' => [
                    'enabled' => 'on',
                    'display_name' => 'Upcoming Courses',
                    'records_per_course' => 8,
                    'configuration' => 'automated',
                    'duration_in_days' => '30',
                    'type' => ['channels', 'products', 'packages', 'course'],
                ],
                'PopularCourses' => [
                    'enabled' => 'on',
                    'display_name' => 'Popular Courses',
                    'records_per_course' => 8,
                    'configuration' => 'manual'
                ],
                'Quotes' => [
                    'label' => 'Quotes',
                    'number_of_quotes_display' => 4,
                    'description_chars' => 500,
                    'quotes_enable' => 'on'
                ]
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 11,
            'module' => 'playlyfe',
            'setting' => [
                'token' => [
                    'access_token' => '',
                    'token_type' => '',
                    'expires_at' => 0
                ]
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 12,
            'module' => 'Contact Us',
            'setting' => [
                'site_logo' => '',
                'logo_original_name' => '',
                'mobile_logo' => '',
                'mobile_original_name' => '',
                'homepage' => '',
                'company_name' => '',
                'email' => '',
                'address' => '',
                'lat' => '',
                'lng' => '',
                'phone' => '',
                'mobile_no' => '',
                'social_media' => []
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 13,
            'module' => 'Viewer',
            'setting' => [
                'box_view' => 'off',
                'file_download' => false,
                'text_selectable' => false,
                'session_expires_at' => 60,
                'box_failure' => 'download_link'
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 14,
            'module' => 'certificates',
            'setting' => [
                'visibility' => true
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 15,
            'module' => 'Lmsprogram',
            'setting' => [
                'site_url' => '',
                'wstoken' => '',
                'categoryid' => 0,
                'more_batches' => 'off',
            ]
        ]);

        DB::collection($collection)->insert([
            'id' => 16,
            'module' => 'BankDetails',
            'setting' => [
                'bank_details' => ''
            ]
        ]);

         DB::collection($collection)->insert([
            'id' => 17,
            'module' => 'UserSetting',
            'setting' => [
                'nda_acceptance' => 'off'
            ]
         ]);

         DB::collection($collection)->insert([
          'id' => 18,
          'module' => 'QuizReminders',
          'setting' => [
             'Reminder1' => [
                'reminder_status' => 'on',
                'reminder_day' => 3,
                'quiz_type' => [
                    'general' => 'on',
                    'general_practice' => 'off',
                    'question_generator' => 'on',
                ],
                'notify_by_mail' => 'off'
             ],
             'Reminder2' => [
                'reminder_status' => 'off',
                'reminder_day' => 1,
                'quiz_type' => [
                    'general' => 'on',
                    'general_practice' => 'off',
                    'question_generator' => 'on',
                ],
                'notify_by_mail' => 'off'
             ]
          ]
         ]);

        DB::collection($collection)->insert([
            'id' => 19,
            'module' => 'LHSMenuSettings',
            'setting' => [
                'programs' => 'on',
                'my_activity' => 'on'
            ]
        ]); 
    }
}
