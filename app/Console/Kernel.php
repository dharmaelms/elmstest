<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\PublishPortalFiles::class,
        \App\Console\Commands\Reports::class,
        \App\Console\Commands\QuizReminder::class,
        \App\Console\Commands\ElasticIndex::class,
        \App\Console\Commands\GDriveUpload::class,
        \App\Console\Commands\UpdateBulkImportPermissions::class,
        \App\Console\Commands\MigratePackages::class,
        \App\Console\Commands\MigrateRoles::class,
        \App\Console\Commands\MigrateUserRoles::class,
        \App\Console\Commands\MigrateChannelEntity::class,
        \App\Console\Commands\MigrateChannelSubscription::class,
        \App\Console\Commands\MigratePackageEntity::class,
        \App\Console\Commands\MigratePackageSubscription::class,
        \App\Console\Commands\MigrateUserGroupFeedRel::class,
        \App\Console\Commands\MigrateUserGroupParentRel::class,
        \App\Console\Commands\MigrateUserToUserGroupFeedRel::class,
        \App\Console\Commands\MigrateBatch::class,
        \App\Console\Commands\EnrollPackage::class,
        \App\Console\Commands\WebExDownload::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        // Reports
        $schedule->call('App\Http\Controllers\Admin\ReportTblPopulateController@cronDailyReportsTblPopulate')
            ->dailyAt('00:01');

        // Send Quiz reminder notifications
        $schedule->command('quiz:remind')
            ->dailyAt('2:00');
    
        // Move notification from notifications to archive
        $schedule->call('App\Http\Controllers\Admin\NotificationController@flushNotificationToArchive')
            ->dailyAt('3:00');

        // Notification push from notification log
        $schedule->call('App\Http\Controllers\Admin\NotificationController@pushNotificationByCron')
            ->name('notificationCron')
            ->hourly();

        // Cron for diplaying recorded webex
        $schedule->call('App\Http\Controllers\Portal\PortalController@getWebinarCron')
            ->name('webexRecordingCron')
            ->hourly();

        //CRON for WebEx reports
        $schedule->call('App\Http\Controllers\Admin\EventReportController@getCron')
            ->name('webexReportCron')
            ->cron('15 * * * *');

        $schedule->call('App\Http\Controllers\Admin\CertificatesController@getGenerateCertificate')
            ->name('certificates')
            ->cron('5,35 * * * *');

        // Dams
        $schedule->call('App\Http\Controllers\Admin\DamsController@getInitCron')
            ->name('damsVideo')
            ->cron('10,40 * * * *');

        // Announcement
        $schedule->call('App\Http\Controllers\Admin\AnnouncementController@getInitCron')
            ->name('SendAnnouncementMail')
            ->withoutOverlapping()
            ->everyTenMinutes();
    }
}
