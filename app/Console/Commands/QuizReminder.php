<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\NotificationController;
use Illuminate\Console\Command;
use Carbon;
use Log;
/**
 * Class QuizReminder
 * @package App\Console\Commands
 * reminder
 */

class QuizReminder extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'quiz:remind {date?}';

     /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To run quiz reminder based on specific day (optional)';

    /**
     *  @var NotificationController
     */
    private $notification_ctrl;

    /**
     * Expire date of the report range. (format: yyyy-mm-dd)
     * @var array
     */
    private $date;

    /**
     * Create a new command instance.
     * @param NotificationController $report
     */
    public function __construct(NotificationController $notification_ctrl)
    {
        $this->notification_ctrl = $notification_ctrl;
        parent::__construct();
    }

    public function fire()
    {
        Log::info('Started to excecute quiz:remind command');
        if (!is_null($this->argument('date'))) {
            $carbon_date_obj = Carbon::createFromFormat(
                'Y-m-d H',
                $this->argument('date')." 0",
                config('app.default_timezone')
            );
            $this->date['start'] = $carbon_date_obj->getTimestamp();
            $this->date['end'] = $carbon_date_obj->endOfDay()->getTimestamp();
            $today = Carbon::today(config('app.default_timezone'))->getTimestamp();

            if ($this->date['start'] < $today) {
                $this->error("Entered date should be not less than current date");
            } else {
                $this->notification_ctrl->sendQuizReminder($this->date);
            }
        } else {
            $this->notification_ctrl->sendQuizReminder(null);
        }
        Log::info('Ended quiz:remind command');
    }
}
