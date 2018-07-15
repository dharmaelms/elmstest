<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;

class EmailsTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'emails';

        // Removing existing collection
        Schema::drop($collection);

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('id');
        });

        DB::collection($collection)->insert([
            'id' => 1,
            'name' => 'User Registration',
            'slug' => 'user-registration',
            'subject' => 'Your account with <SITE NAME> has been created',
            'body' => '<html><body>Hello <NAME>,<br><br>Welcome to <SITE NAME>!<br><br>Your account has been created successfully.<br><br>You can login either with username or email address.<br><br>Username: <USERNAME><br>Email: <EMAIL><br><br><LOGIN URL><br><br>In case you have any questions or need any help, you can get in touch with us at <SUPPORT EMAIL>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 2,
            'name' => 'Change Password',
            'slug' => 'change-password',
            'subject' => '<Website Name> Account Password Changed',
            'body' => '<html><body>Hello <NAME>,<br><br>The password for your account on <SITE NAME> is changed successfully.<br><br><LOGIN URL><br><br>In case you have any questions or need any help, you can get in touch with us at <SUPPORT EMAIL>.<br><br>Thank you,<br>Team <SITE NAME>.<br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 3,
            'name' => 'Channel Access Request From User',
            'slug' => 'channel-access-request-from-user',
            'subject' => 'Requested For Channel Access',
            'body' => '<html><body>Hello <USERNAME>,<br><br>Your request for channel<b> <CONTENT FEED NAME></b> will be processed shortly. <br><br>In case you have any questions or need any help, you can get in touch with us at <SUPPORT EMAIL>. <br><br>Thank you,<br>Team &nbsp;<SITE NAME>.<br><br>PS: Note that this is an auto-generated email, please do not reply.<br><br></body></html>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 4,
            'name' => 'Channel Access Request For Admin',
            'slug' => 'channel-access-request-for-admin',
            'subject' => 'Requested For Channel Access',
            'body' => '<html><body>Hello <ADMIN>,<br><br>User <NAME> has requested access for Channel <b><CONTENT FEED NAME>.</b><br><br> <LOGIN URL> <br><br>In case you have any questions or need any help, you can get in touch with us at  <SUPPORT EMAIL>.<br><br>Thank you,<br>Team &nbsp;<SITE NAME>.<br><br>PS: Note that this is an auto-generated email, please do not reply.<br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 5,
            'name' => 'Announcement',
            'slug' => 'announcement',
            'subject' => '[Announcement] <ANNOUNCEMENT TITLE> ',
            'body' => '<html><body>Hello <NAME>,<br><br>You have 1 new announcement from <SENDER NAME>.<br><br><ANNOUNCEMENT TITLE><br><DATE AND TIME><br><a href="<SITE URL>">Read More </a><br><br>In case you have any questions or need any help, you can get in touch with us at <SUPPORT EMAIL><br>Thank you,<br>Team &nbsp;<SITE NAME>.<br><br>PS: Note that this is an auto-generated email, please do not reply.<br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 6,
            'name' => 'User Register Admin Template',
            'slug' => 'user-register-admin-template',
            'subject' => 'User Registered with <SITE NAME>',
            'body' => '<html><body>Hello <SITE ADMIN NAME>,<br><br><USERNAME> has registered to <SITE NAME> on <DATETIME> successfully.<br><br><LOGIN URL><br><br>Thank you,<br>Team <SITE NAME>.<br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 7,
            'name' => 'Admin Register User Template',
            'slug' => 'admin-register-user-template',
            'subject' => 'Your account with <SITE NAME> has been created by Administrator',
            'body' => '<html><body>Hello <NAME>,<br><br>Administrator has created an account for you on <SITE NAME> with following details.<br>Username: <USERNAME><br>Email: <EMAIL><br><br><RESET PASSWORD>. You will be able to sign in with your new password.<br><br>In case you have any questions or need any help, you can get in touch with us at <SUPPORT EMAIL>.<br><br>Thank you,<br>Team <SITE NAME>.<br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 8,
            'name' => 'Admin Register Admin Template',
            'slug' => 'admin-register-admin-template',
            'subject' => 'User account with <SITE NAME> has been created by Administrator',
            'body' => '<html><body>Hello <SITE ADMIN NAME>,<br><br><CREATED BY> has created new user account on <DATETIME>.<br><br>Username: <USERNAME><br>Email: <EMAIL><br>Role: <ROLE><br>Status: <STATUS><br><br><LOGIN URL><br><br>Thank you,<br>Team <SITE NAME>.<br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 9,
            'name' => 'Admin Bulkimport Template',
            'slug' => 'admin-bulkimport-template',
            'subject' => 'Users account with <SITE NAME> has been created through bulkimport',
            'body' => '<html><body>Hello <SITE ADMIN NAME>,<br><br><CREATED BY> has created the users accounts through bulk import on <DATETIME>.<br><br><LOGIN URL><br><USERS><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 10,
            'name' => 'User Registration',
            'slug' => 'user-email-verification',
            'subject' => 'Your account with <SITE NAME> has been created',
            'body' => '<html><body>Hello <NAME>,<br><br>Welcome to <SITE NAME>!<br><br>Your account has been created successfully.<br><br>Email verification is required to activate your account..<br><br><VERIFY URL><br><br>In case you have any questions or need any help, you can get in touch with us at <SUPPORT EMAIL>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 11,
            'name' => 'Forgot Password',
            'slug' => 'forgot-password',
            'subject' => 'Your Password Reset Link',
            'body' => '<html><body>Hello <FIRSTNAME> <LASTNAME>,<br><br>You requested a password reset. <RESET URL> to reset your password or copy the link below in your browser. .<br><br><a href="<LINK>"><LINK></a><br><br> In case you have any questions or need any help, you can get in touch with us at <SUPPORT EMAIL>.<br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
         
        DB::collection($collection)->insert([
            'id' => 12,
            'name' => 'Erp User Import Success Template',
            'slug' => 'erp-user-import-success-template',
            'subject' => 'User import status:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File import was a successful.Following are today&apos;s details of user records which are created into the system from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 13,
            'name' => 'Erp User Import Failure Template',
            'slug' => 'erp-user-import-failure-template',
            'subject' => 'User import status:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file import.User import failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 14,
            'name' => 'Erp Package Import Success Template',
            'slug' => 'erp-package-import-success-template',
            'subject' => 'Package import status:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File import was a successful. Following are today&apos;s details of packages which are created into the system from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 15,
            'name' => 'Erp Package Import Failure Template',
            'slug' => 'erp-package-import-failure-template',
            'subject' => 'Package import status:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file import. Package import failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 16,
            'name' => 'Erp Channel Import Success Template',
            'slug' => 'erp-channel-import-success-template',
            'subject' => 'Channel import status:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File import was a successful. Following are today&apos;s details of channels which are created into the system from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 17,
            'name' => 'Erp Channel Import Failure Template',
            'slug' => 'erp-channel-import-failure-template',
            'subject' => 'Channel import status:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file import. Channel import failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 18,
            'name' => 'Erp Usergroup To Package Success Template',
            'slug' => 'erp-usergroup-to-package-success-template',
            'subject' => 'Enrolling user group to package:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File import was a successful. Following are today&apos;s details of enrolling user groups to package from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 19,
            'name' => 'Erp Usergroup To Package Failure Template',
            'slug' => 'erp-usergroup-to-package-failure-template',
            'subject' => 'Enrolling user group to package:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file import. Enrolling user group to package failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 20,
            'name' => 'Erp User To Channel Success Template',
            'slug' => 'erp-user-to-channel-success-template',
            'subject' => 'Enrolling user to channel:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File import was a successful. Following are today&apos;s details of enrolling users to channel from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 21,
            'name' => 'Erp User To Channel Failure Template',
            'slug' => 'erp-user-to-channel-failure-template',
            'subject' => 'Enrolling user to channel:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file import. Enrolling user to channel failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 22,
            'name' => 'Erp User Update Success Template',
            'slug' => 'erp-user-update-success-template',
            'subject' => 'User update status:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File update was a success.Following are today&apos;s details of user records which are updated into the system from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 23,
            'name' => 'Erp User Update Failure Template',
            'slug' => 'erp-user-update-failure-template',
            'subject' => 'User update status:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file update.Users update failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 24,
            'name' => 'Erp Package Update Success Template',
            'slug' => 'erp-package-update-success-template',
            'subject' => 'Package update status:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File update was a success. Following are today&apos;s details of packages which are updated into the system from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 25,
            'name' => 'Erp Package Update Failure Template',
            'slug' => 'erp-package-update-failure-template',
            'subject' => 'Package update status:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file update. Package update failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 26,
            'name' => 'Erp Channel Update Success Template',
            'slug' => 'erp-channel-update-success-template',
            'subject' => 'Channel update status:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File update was a success. Following are today&apos;s details of channels which are updated into the system from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 27,
            'name' => 'Erp Channel Update Failure Template',
            'slug' => 'erp-channel-update-failure-template',
            'subject' => 'Channel update status:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file update. Channel update failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 28,
            'name' => 'Erp Assign User To Usergroup Success Template',
            'slug' => 'erp-assign-user-usergroup-success-template',
            'subject' => 'Assigning user to usergroup:Successful',
            'body' => '<html><body>Hello <EMAIL>,<br><br>File import was a successful. Following are today&apos;s details of assigining user to usergroup from the import file.<br><br> Total no.of records processed : <TOTAL> <br><br> No.of successful records : <SUCCESS><br> <br> No.of failed records :<FAILURE><br> <br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 29,
            'name' => 'Erp Assign User To Usergroup Failure Template',
            'slug' => 'erp-assign-user-usergroup-failure-template',
            'subject' => 'Assigning user to usergroup:Failed',
            'body' => '<html><body>Hello <EMAIL>,<br><br>Unsuccessful file import. Assigning user to usergroup failed due to <REASON>.<br><br>Thank you,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 30,
            'name' => 'Reminder notification for quiz assigned directly to user',
            'slug' => 'reminder-notification-for-quiz-assigned-directly-to-user',
            'subject' => '<REMINDER NAME>: Quiz "<QUIZ NAME>" expiring soon in <SITE NAME>',
            'body' => '<html><body>Hello <FIRSTNAME> <LASTNAME>,<br><br>Your quiz "<QUIZ NAME>" in <SITE NAME> is ending soon.<br>It is expiring on <QUIZ END DATE>. Please attempt the quiz before it expires.<br><br>In case you have any questions or need any help, you can get in touch with us.<br><br>Thank You,<br>Team <SITE NAME>.<br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
        
        DB::collection($collection)->insert([
            'id' => 31,
            'name' => 'Reminder notification for question generator assigned directly to user',
            'slug' => 'reminder-notification-for-question-generator-directly-to-user',
            'subject' => '<REMINDER NAME>: Question generator "<QUIZ NAME>" expiring soon in <SITE NAME>',
            'body' => '<html><body>Hello <FIRSTNAME> <LASTNAME>,<br><br>Your question generator "<QUIZ NAME>"  in <SITE NAME> is ending soon.<br>It is expiring on <QUIZ END DATE>. Please attempt all the questions of the question generator before it expires.<br><br>In case you have any questions or need any help, you can get in touch with us.<br><br>Thank You,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);

        DB::collection($collection)->insert([
            'id' => 32,
            'name' => 'Reminder notification for quiz assigned through program',
            'slug' => 'reminder-notification-for-quiz-assigned-through-program',
            'subject' => '<REMINDER NAME>: Quizzes expiring soon in course "<PROGRAM NAME>" in <SITE NAME>',
            'body' => '<html><body>Hello <FIRSTNAME> <LASTNAME>,<br><br>Following quizzes of course "<PROGRAM NAME>" assigned to you in <SITE NAME> are expiring on <PROGRAM END DATE>. <QUIZ ASSIGNED TO POST LIST> <br>Please attempt all the above quizzes before they expire.<br>In case you have any questions or need any help, you can get in touch with us.<br><br>Thank You,<br>Team <SITE NAME><br><br></html></body>',
            'status' => 'ACTIVE',
        ]);
    }
}
