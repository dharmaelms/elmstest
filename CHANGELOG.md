# Change Log
------------

**Note** : All notable changes to this project will be documented in this file.
This project adheres to * Semantic Versioning(http://semver.org/).
## Unpublished app version (Date)
--------------------

### Add

### Change

### Fix

### Remove
## 5.6.3 (22/02/2017)
### Fix
    * BUGFIX #3316 : Advanced search in manage course, in the export file it showing incorrect batch count
    * BUGFIX #3325 : Slug is not getting update when admin edit program title and short name in manage channel and package
    * BUGFIX : User Registration - different users getting created with same username
    * BUGFIX : Wrong slug created when channel and package shortname contains space after special characters

## 5.7.0 (17/02/2017)
### Add
    * TASK: Allow max upload size for dams assets to be configurable
    * TASK: Allow upload of .r and .csv file extensions
### Fix
    * BUGFIX #2470: No provision to edit program sub type, it should not be considered while updating
    * BUGFIX #2469: Updating only fields which are allow to edit,, instead of all the form field
## 5.6.2 (16/02/2017)
### Fix
    * BUGFIX #3257 : In manage course, general info columns mismatch in the exported file
    * BUGFIX #3256 : Channel name field in advanced search special character " &" it showing as "&amp;"
    * BUGFIX #3273 : "test & test" search this channel in channel's advance search - results with 1 channel will come - Export channels - 31 channels are getting exported
    * BUGFIX #3271 : While uploading the updated file it showing the error status failed without any reason
    * BUGFIX #3257 : Access field is require in channel and package but not in course
    * BUGFIX #3272 : UI issues in advance search option's - Select field [custom field] - if the custom field name is lengther one - both text box and field name are getting merged
    * BUGFIX #3270 : error import report, if uploaded any file without deleting the error status, it showing exception error
    * BUGFIX #3281 : Replaced  unneccesary validation messages with valid messages

## 5.6.1 (09/02/2017)
### Change
    * TASK: Default value made as false for enable_registration_redirect
### Fix
    * BUGFIX: Fatal error: Cannot use App\Model\States as States because the name is already in use
    * BUGFIX #3249: In manage channels, package, course- multiple word search is available or not
    * BUGFIX #3253: While editing the manage channel or package or course but giving the keyword, it gets filter automatically
    * BUGFIX #3254: Editing in Other Details tab of Course level is not allowing

## 5.6.0 (07/02/2017)
### Add
    * TASK: Allow User group name to 60 chars
    * TASK: Short name display for reports (iNurture)
    * TASK: Export of programs (Channel, Package, Course) as a CSV file
    * TASK: Redirection flow after registration of user

## 5.5.6 (08/02/2017)
### Fix
    * BUGFIX #3254 - Editing in Other Details tab of Course level is not allowing
## 5.5.5 (07/02/2017)
### Fix
    * BUGFIX: Handle ftp connection when there is wrong hostname
    * BUGFIX: Handle cron when ftp enable is false
    * BUGFIX #3231:  In user group package mapping table, invalid and mismatch data occurs.
    * BUGFIX #2902:  iNurture - Bulk Import - Add Users - Address Fields not populated
    * BUGFIX #3198: User - User group mapping, while upload the file in FTP, fatal error occurs.
    * BUGFIX: Mongo load issue beacuse of myactivity aggregation

## 5.5.4 (01/02/2017)
### Fix
    * BUGFIX: Events - Webex parameter startDate taking null value and events getting created at current time issue is fixed
## 5.5.3 (25/01/2017)
### Fix
    * BUGFIX: Invalid date issue in mobile api (iNurture)
## 5.5.2 (24/01/2017)
### Fix
    * BUGFIX: Define ADMIN_ORDER_EMAIL environment variable
    * BUGFIX #3154 - Dashboard - Course completion percentage is coming as Nan% and feed listing page
## 5.5.1 (19/01/2017)
### Add
    * Program redirect (BQ) functionality
### Change
    * Define fallback for import email if missing in env file
### Fix
    * BUGFIX #3034: Getting option to purchase a Program which is already purchased
    * BUGFIX #3043: After added the new media while editing a question the media list is not listed.
    * BUGFIX #3080: This is regarding the reports that we are generating to evaluate the course completion status of the users
    * BUGFIX #3057: While editing the banner instead of "select file"option "change "option is displayed.
    * BUGFIX #3056: The description field in adding banner, shows the error " The description format is invalid.
    * BUGFIX #3055: While adding the new banner, in the Moblie banner landscape field, the select file tab has incorrectly mentioned twice. 
    * BUGFIX #3054: While updating banners no validation is done, allowing all media types like pdf, doc it should allow only image type.
    * BUGFIX #3132: Creating free subscription when given invalid subscription slug issue is fixed.

## 5.5.0 (11/01/2017)
--------------------
### Add

    * FEATURE: Program redirection [BQ]
    * FEATURE: Quiz flow changes in packet detail page [BQ]
### Change

    * TASK: Set timelimit for export channel report
    * TASK: Alter fact table creation logic
    * Task - Removed updated quiz attempt data
    * Task - Removed unused blade files
    * Task - User list reports sort order by performance high to low
    * Task - Question level performance total count issue fixed
    * Task - array get function useed instead of directly iuse index
    * Task - Table population is optimised
    * Task - edit questions media types add as video, audio and image
### Fix
    * BUGFIX #2991: Subscribed channels date overrides quizzes dates
    * BUGFIX #3050: Concepts is not appearing for question generator quizzes.
    * BUGFIX #2991: In user portal--> click on Dash board, Click on Assessments for normal quiz which was already assigned for a channel
    * BUGFIX #2636: Date getting disappered when clicked on the announcement
    * BUGFIX #2733: Actual duration logic changed 
    * BUGFIX #3019: Order and view order page throws error after logout when opened in the other tab issue is fixed.
    * BUGFIX #2830: Cyient Export Report - Completion Status and Certificate Issued Status mismatch
    * BUGFIX #2967: colon removed
    * BUGFIX: integration folder for mathml
    * BUGFIX #2830: Cyient Export Report - Completion Status and Certificate Issued Status mismatch
    * BUGFIX #2976: For older(existing) quiz report, throws fatal error
    * BUGFIX #3005: undefined variable media_types - fatal error
    * BUGFIX #2974: quiz report page undefined $user variable
    * BUGFIX #2977: attempts always showing 1
    * BUGFIX #2937 The load is because of the mongo queries(my_activity and notifications)
    * BUGFIX: check for attempt review before redirect
    * BUGFIX #2881: Label change for Select file, currently it says "Visibility" as label

## 5.4.6 (01/02/2017)
### Fix
    * BUGFIX: Define ADMIN_ORDER_EMAIL environment variable
    * BUGFIX: Events - Webex parameter startDate taking null value and events getting created at current time issue is fixed.
## 5.4.5 (19/01/2017)
### Fix
    * BUGFIX #3132: Creating free subscription when given invalid subscription slug issue is fixed
## 5.4.4 (30/12/2016)
--------------------
### Change
    * TASK: Split reports table populate command with table types
    * TASK: Refactor Reports methods
### Fix
    * BUGFIX #2663: Fetch and load more data as per channel id
    * BUGFIX #2819: Inurture flashcard issue
    * BUGFIX #2360: Overriding the old rationale with the edits
    * BUGFIX #2774: When view rationale icon is clicked from admin side question bank's questions.
    * BUGFIX #2639: Search more than one words in manage channel and course 
    * BUGFIX #2898: Restrict adding any media types other than image's for Channels, Courses, Package cover images
    * BUGFIX #2496: Manage Course pagination is working 
    * BUGFIX #2733: Cyient - Export Reports Duration Field Logic fix
    
### Remove
    * TASK: Remove Unused methods from report
## 5.4.3 (23/12/2016)
--------------------
### Fix
    * BUGFIX #2686 : Not able to assign Cover Image for Channels, Courses, Package etc
    * BUGFIX #2786 : Refer 2711 bug - Posts detail pages are working, but In course details pages no post lists are shown

## 5.4.2 (23/12/2016)
--------------------
### Fix
    * BUGFIX #2885: Add program_access field in progarm collection (iNurture)

## 5.4.1 (21/12/2016)
--------------------

### Change
    * Updated box API by integrating version 2

### Fix
    * BUGFIX #2686: BUGFIX # 2686 - Not able to assign Cover Image for Channels, Courses, Package etc

## 5.4.0 (19/12/2016)
--------------------
### Add
    * FEATURE: Update user, user group information via bulk upload (ERP update)

### Change
    * Task- Removed custom dates from user list report

### Fix
    * BUGFIX #2828: Edit channel other details issue

## 5.3.10 (01/02/2017)
### Fix
    * BUGFIX: Define ADMIN_ORDER_EMAIL environment variable
    * BUGFIX: Events - Webex parameter startDate taking null value and events getting created at current time issue is fixed
## 5.3.9 (19/01/2017)
### Fix
    * BUGFIX #3132 -Creating free subscription when given invalid subscription slug issue is fixed
## 5.3.9 (24/12/2016)
### Fix
    * BUGFIX #2786 - Refer 2711 bug - Posts detail pages are working, but In course details pages no post lists are shown
## 5.3.7 (15/12/2016)
--------------------
### Change
    * TASK: Remove http: or https: from DAMS URL

### Fix
    * BUGFIX SiteSettingSeeder - Resolved issue, Non-static method update() called statically.
    * BUGFIX #2831 Quiz report issue in BQ-Exam report of users required
    * BUGFIX #2841 Mongodb slow query/more number of hit(read from dim_my_activity table), So that mongodb load is heavy

## 5.3.6 (13/12/2016)
--------------------
### Change
    * TASK: Refactor Api controller
    * TASK: Refactor ERP implementation
### Fix
    * BUGFIX #1581 : Firefox plugin comment (Change the URL for download link)

## 5.3.5 (06/12/2016)
--------------------
### Change
    * Task: Change encoding mechanism to encode only if the data is in non utf8 format.
### Fix
    * TASK: Fix expression result unused

## 5.3.4 (02/12/2016)
--------------------
### Change
    * Task: Change encoding mechanism to encode only if the data is in non utf8 format
### Fix
    * BUGFIX #2762: Able to see All Dropdown and assign other media type after we add video through Add media option
    * BUGFIX #2766: Default image says "No image available"
    * BUGFIX #2714: Start date and end date are showing as NaN undefined NaN

## 5.3.3 (02/12/2016)
--------------------
### Added
    * TASK: Add bulk import report permissions in role seeder
### Change
    * TASK: Reformat Content Feed Management controller

## 5.3.2 (02/12/2016)
--------------------
### Add
    * TASK: Save NDA response time while capturing user response for NDA
### Change
    * TASK: Refactor NDA implementation
### Fix
    * BUGFIX #2764: Assigning videos not appearing

## 5.3.0 AKA 5.3.1 (01/12/2016)
--------------------
### Added
    * NDA popup feature
    * IBPS template for quiz buttons
    * ERP integration for importing users, user groups
    * Video support in program tab content

### Fix
    * BUGFIX #2729: Login from gmail/ facebook redirecting to homepage issue is fixed.
    * BUGFIX #1678: Date issue events
    * BUGFIX #2553: Adding flashcard to a Post: Only one flashcard is shown
    * BUGFIX #2598: Throws page not found error for a particular quiz export
    * BUGFIX #2635: Even if batch is there, batches are not appearing in the course dropdown issue is fixed
    * BUGFIX #2662: Product end date and display end date validation issue is fixed.
    * BUGFIX #2667: For a Quiz having Quiz and Section cut off, The results are showing as NA
    * BUGFIX #2678: Not all channels shown in dashboard when assign through user group.
    * BUGFIX #2709: Clear Response showing improper results
    * BUGFIX #2727: fixed
    * BUGFIX #2734: While creating live event, duration field is treated at start time, it's not allowing to specific duration in that field
    * BUGFIX #2754: Description validation message shows under Add media from library
    * BUGFIX #2758: Able to add images, documents, SCORM also
    * BUGFIX #2730: ErrorException in NDA accept and decline
    * BUGFIX: Certificate CRON issue
    * BUGFIX: Certificate moved above dams cron

## 5.2.5 (01/02/2017)
### Fix
    * BUGFIX: Define ADMIN_ORDER_EMAIL environment variable
    * BUGFIX: Events - Webex parameter startDate taking null value and events getting created at current time issue is fixed.
## 5.2.4 (19/01/2017)
### Fix
    * BUGFIX #3132: Creating free subscription when given invalid subscription slug issue is fixed.
## 5.2.3 (15/12/2016)
### Fix
    * BUGFIX #3132: Creating free subscription when given invalid subscription slug issue is fixed.
## 5.2.2 (15/12/2016)
### Fix
    * BUGFIX #2831: Quiz report issue in BQ-Exam report of users required
    * BUGFIX #2841: Mongodb slow query/more number of hit(read from dim_my_activity table), So that mongodb load is heavy
## 5.2.2 (05/12/2016)
### Fix
    * Bugfix #2714: Start date and end date are showing as NaN undefined NaN

## 5.2.1 (23/11/2016)
--------------------

### Add

### Change

### Fix
    * FIX: Certificate moved above dams cron
    * BUGFIX #2678: Not all channels shown in dashboard when assign through user group.

### Remove


## 5.2.0 (17/11/2016)
--------------------

### Add

    * FEATURE: Export reports
    * Added scope methods and attribute mutators to get youtube embed code

### Change

    * DOC: Bump project version for sonar analysis
    * TASK: Reformat code
    * Called render method from view object instead of echoing the object itself
    * TASK: Refactor video player
    * Forced youtube to be loaded in https
    * REFACTOR: Storing of document viewer configuration values, from string to boolean
    * TASK: Base enum class updated
    * Bugfix #2550: Move cut-off format types from controller to app/Enum folder.
    * TASK: Changed all static methods to class methods
    * TASK: Made the repository to take the embed code from media object itself (from its scope method)
    * TASK: Changed embed_url to embed_code as this makes more sense
    * TASK: Renamed the variables which were missed in previous commit
    * TASK: Updated ultron/cli package from 1.0.1 to 1.2.1

### Fix

    * BUGFIX #2577: Announcements appearing in some random date order issue is fixed
    * Added validation for view instance to avoid call of empty variable
    * Added extra bracket to shorthand if
    * Added a comment - link to download flashplayer plugin has to be https
    * BUGFIX #2566: In Attempt details tab, the cut off result should be in %
    * $media - initialised to empty string in programController
    * BUGFIX #2609 Provide mandatory star for all the fields when the field is required
    * BUGFIX #2604 Batch list is coming up without selecting courses
    * BUGFIX #2602 404 page when custom dates field is deleted manually and submitted
    * BUGFIX #2608 Error message should hidden after 5-10 seconds
    * BUGFIX #2606 For program type courses, remove and drop down link symbol is not working 
    * BUGFIX #2626 Reports - Detailed by group - INACTIVE users are not getting exported
    * BUGFIX #2627  Export Reports - Search not working properly
    * TASK: Fix  'Laravel\Socialite\Two\InvalidStateException' in /var/www/html/bq.training/vendor/laravel/socialite/src/Two/AbstractProvider.php:200
    * TASK: Fix exception 'ErrorException' with message 'Undefined index: cat_details' in /var/www/html/docketip.com/app/Http/Controllers/Portal/CatalogManagementController.php:135
    * BUGFIX #2632 Detailed by usergroup - column Date of Enrollment is missing in export reports
    * BUGFIX #2632 Export options - Mandatory * is not aligned properly
    * BUGFIX #2633: Quiz Reports gives ErrorException in Carbon.php
    * BUGFIX #2561:  Download file name should contain the actual file name. instead for doc.pdf or doc.doc
    * BUGFIX #2612  In Course Activity by User report, the option should be username not First Name and Last Name
    * BUGFIX #2514 Display name for items
    * BUGFIX #2612 User list attached first name and last name
    * BUGFIX #2612 bracket changes for full name
    * BUGFIX #2550: Migration script
    * BUGFIX #2342: Capture SAML login in my_activity collection
    * BUGFIX #2641 - Certificate column is showing status as NOT ISSUED always
    * BUGFIX #2226 - Not able to add Document or SCORM in Assessments
    * BUGFIX #2643 Changed the logic of youtube time conversion
    * BUGFIX #2644 - Special characters are shown as words in export filter's table
    * BUGFIX #2642 - PFA Language file is not pushed properly for excel reports
    * BUGFIX #2646 - For BQ question level reports need question text as a title 
    * Fix for fatal error in dams module when youtube url is taken from `share` option of youtube
    * BUGFIX #632 Reports - Direct Quizzes to Users - Why there are two score in reports
    * BUGFIX #522 User Channel Completion - Clicking on the score in the graph, Reduced width of chart container for need to show detail table

### Remove

    * TASK: Remove http reference in third party URL reference
    * TASK: Removed unwanted methods

## 5.0.3 (15/11/2016)
----------------------------

### Fix

    * BUGFIX #2591 cron is not running, means it starts the cron everyday, but it doesn't stop
    * BUGFIX #2598 Throws page not found error for a particular quiz export

## 5.1.1 (11/11/2016)
--------------------

### Add

    * FEATURE: Attempt template enhancement (BQ)

## 5.1.0 (03/11/2016)
--------------------

### Add

    * FEATURE: Ability to define cut off by percentage
    * FEATURE: Disable right click in flash cards
    * FEATURE: Hide public announcements post login
    * TASK: Created enums folder and added base enum class
    * FEATURE: Certificate label position and font-size updated

### Change
    * TASK: Use "static" keyworkd instead of "self"
    * TASK: Certificate CRON timing changed from every 30 minutes to every 5 minutes

### Fix

    * DOC: Fix PHP doc issue
    * BUGFIX #2568: Announcement date appears as 01-01-1970
    * TASK: Fix issue reported by Sonar
    * BUGFIX #2136: We are not following sentence case in most of places in Admin. Many places it is showing the whole text in lowercase, which is not at all looking good
    * BUGFIX #2563: Detailed Analytics - Cut off cleared column for section is not showing right data 
    * BUGFIX #2427: Section duration is not retained, when questions are added to sections 
    * BUGFIX #2562: When we click on Attempted in Assessment it gives ErrorException
    * BUGFIX #2558: Timezone issue - Converting date string to timestamp
    * BUGFIX #2552: Data missing error in quiz reports
    * BUGFIX #2570: When searching it gives pagination where it is not necessary
    * BUGFIX #2564: Cannot move to next section, in a quiz where it have timed sections within it

### Remove

    * TASK: Remove the useless trailing whitespaces at the end of this line

## 5.0.2 (02/11/2016)
----------------------------

### Change

    * Changed dependency package name from `linkstreetlearning/ultron-cli` to `ultron/cli`

## 5.0.1 (27/10/2016)
----------------------------

### Change

    * Label reference for my address update success message

## 5.0.0-beta13 AKA 5.0.0 (26/10/2016)
----------------------------

### Add

    * Added repository reference for ultron-cli package
    * Added code to resolve dependencies
    * Added document for resolveDependencies method

### Change

    * DOC: Update PHP doc
    * TASK: Replace empty check with array check since text is returned in case of exception
    * TASK: Format code
    * TASK: Clean Akamai Token Trait implementation
    * Installed package `linkstreetlearning/ultron-cli`

### Fix

    * TASK: Fix 'ErrorException' with message 'Missing argument 3 for App\Model\QuizReport::calAttemptScore()
    * DOC: Fix PHP doc issue
    * TASK: Fix 'ErrorException' with message 'Undefined offset: 0'
    * TASK: Fix 'ErrorException' with message 'Undefined index: tabs'
    * BUGFIX #2380: Not validating min order amount w.r.t discount value, if discount type is percentage
    * TASK: Fix  'ErrorException' with message 'Undefined index: buy_status'
    * TASK: Remove unused variable declaration
    * TASK: Fix sonar-lint issue on line length
    * BUGFIX #2532: Fatal error in lms course, Undefined offset: 0
    * TASK: Fix 'ErrorException' with message 'Undefined index: history'
    * BUGFIX #2289: Use case 1 Listing channels, packages which are all assigned through manage usergroups

### Remove

    * TASK: Remove unwanted {{ }} in view announcement page for tran function
    * TASK: Remove unused report blade and controller method reference

## 5.0.0-beta12 (20/10/2016)
----------------------------

### Add

    * TASK: Add missing interface declaration for patchActions method
    * TASK: Add portal package validation and Code to update submodule (if the repo has any)

### Change

    * DOC: Bump project version for sonar analysis
    * TASK: Arrange labels in alphabetical order of key

### Fix

    * BUGFIX: Replace array access with fail safe array_get
    * BUGFIX #2514 : Display name for items
    * TASK: Fix PSR issues
    * TASK: Rename error_messages to exception and cleanup exceptions
    * TASK: Remove the useless trailing whitespaces at the end of this line
    * TASK: Split long line which is greater than 120 authorized
    * TASK: Rename function to match the regular expression ^[a-z][a-zA-Z0-9]*$
    * TASK: Either split this list into multiple lines or put all arguments on one line
    * TASK: Put exactly one space before and after the "use" keyword
    * BUGFIX: Type-o error
    * BUGFIX #2527 : Confirmation alert box - YES label is mentioned as Cancel
    * BUGFIX #2528 : Cancel button url changed
    * BUGFIX #2509 : Partner sort order not reflecting in the EUP
    * TASK: Fix 'ErrorException' with message 'Division by zero'

## 5.0.0-beta11 (18/10/2016)
----------------------------

### Add

    * TASK: Commit message format helper
    * TASK: Add change log file to aid release notes

### Change

    * TASK: Faq and Static page data insertion, using insertion instead of forceCreate.
    * TASK: Ckeditor file path changed from assets/ckeditor to assets/flashcards/ckeditor
    * TASK: Renamed  `_manage_question_bank` to  `manage_question_bank`
    * TASK: Displaying changed to blade format.

### Fix

    * BUGFIX #2186: The validation message doesn't match the field name
    * BUGFIX #2479: Refactor mathml site setting setup
    * BUGFIX #2498: When manage Attributes is clicked from breadcrumbs throws 404 page not found
    * BUGFIX #2501: review page pagination without sections
    * BUGFIX #2505: Hide miscellaneous for empty products
    * BUGFIX #2513: Date was displaying in status column, its fixed
    * BUGFIX #2501: Review page pagination based on question per block
    * BUGFIX #2516: Null value issue in assigning questions to quiz page

## 5.0.0-beta10 (14/10/2016)
----------------------------

### Add

    * TASK: Cache and formula directory variable in ini example file

### Change

    * TASK: Arrange admin labels in ascending order

### Fix

    * BUGFIX:#2482 The usergroup name format is invalid
    * BUGFIX:#2483 First name format is invalid
    * BUGFIX:#2489 The role name format is invalid
    * BUGFIX:#2500 In content reports, performance by question report is not showing for all the users who have attempted the test
    * BUGFIX:#2501 Fix review page pagination
    * BUGFIX:#2505 Hide miscellaneous for empty products
    * BUGFIX:#2504 Add validation before updating `program_access` in `edit-feed` method
    * BUGFIX:#2506 Remove 0 in discount price column

## 5.0.0-beta9 (14/10/2016)
---------------------------

### Change

    * TASK: Admin blades label reference from language file
    * TASK: Data table not refreshed upon changing filters

### Fix

    * BUGFIX:#2379 Validation message for invalid audio file codec format
    * BUGFIX:#2211 Success message should display on same tab
    * BUGFIX:#2432 In chrome browser drop-down come in left hand side
    * BUGFIX:#2486 Added breadcrumbs and back button to order listing from order summary.
    * BUGFIX:#535 Reports - Announcements Viewed - Pagination is needed
    * BUGFIX:#2480 Label `minimun_order_amount` should be `minimum_order_amount`
    * BUGFIX:#2481 Label should be changed when editing promocode
    * BUGFIX:#2490 While creating & editing channel, select sellability as YES, then the field "Access is mentioned as 'admin/program.access*
    * BUGFIX:#535 Export announcement report set limit 
    * BUGFIX:#2491 admin/program.access : admin/category.Category : admin/program.Category : ADVANCED SEARCH
    * BUGFIX:#2492 While uploading media to posts admin/program.select_file
    * BUGFIX:#2493 From announcement list page, mouse over the bulk delete icon, tool tip message should get changed
    * BUGFIX:#2497 Active user report 12/10/2016
    * BUGFIX:#2495 In site settings - attributes - while creating and editing attributes - last field's label and content should get changed
    * BUGFIX:#2494 In Homepage - Manage banners - While adding and editing banners - description label should changed
    * BUGFIX Added date format using to display in catalog listing to config/app file.

### Remove

    * TASK: Unwanted scripts link removed 


## 5.0.0-beta8 (14/10/2016)
---------------------------

### Fix

    * BUGFIX : #2473 Login from course page gives Error Exception

## 5.0.0-beta7 (14/10/2016)
---------------------------

### Change

    * TASK: Case insensitive method call
    * TASK: Add missing return statement in PHP DOC.
    * TASK: Fix unreachable code
    * DOC: Suppress PHPMD unused method warning. To suppress the warning for dynamically called unused methods

### Fix

    * BUGFIX:#1581 adding flash player msg when flash player is not installed
    * BUGFIX:#2458  Admin - Dashboard - Channel Overview graph is not working is fixed
    * BUGFIX:#2432 Courses drop-down: Listing all the channels on the platform. Should list packages also. List should be in alphabetical order
    * BUGFIX:#2396 Default mark regex validation rule updated
    * CLEANUP Removed unused private variable declaration


