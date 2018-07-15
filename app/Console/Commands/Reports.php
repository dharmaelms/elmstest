<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\ReportTblPopulateController;
use Illuminate\Console\Command;
use Carbon;

/**
 * Class Reports
 * @package App\Console\Commands
 */
class Reports extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'reports:run
                                {start : Start date as Y-m-d}
                                {end : End date as Y-m-d}
                                {type : Type is a type as b: Base Table o: Over write all dimension table d: Dimension table, q: Questions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To run reports based on start, end date and type';

    /**
     * @var ReportTblPopulateController
     */
    private $report;

    /**
     * Table type
     * @var char
     */
    private $type;

    /**
     * Start date of the report range. (format: yyyy-mm-dd)
     * @var string
     */
    private $start_date;

    /**
     * End date of the report range. (format: yyyy-mm-dd)
     * @var [type]
     */
    private $end_date;

    /**
     * Create a new command instance.
     * @param ReportTblPopulateController $report
     */
    public function __construct(ReportTblPopulateController $report)
    {
        $this->report = $report;
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $this->type = $this->argument('type');
        $this->start_date =  Carbon::createFromFormat(
            'Y-m-d',
            $this->argument('start'),
            config('app.default_timezone')
        )->timestamp;
        $this->end_date =  Carbon::createFromFormat(
            'Y-m-d',
            $this->argument('end'),
            config('app.default_timezone')
        )->timestamp;
        $this->triggerTblPopulate();
    }

    /**
     * Method which populates the table based on type
     * @return void
     */
    public function triggerTblPopulate()
    {
        switch ($this->type) {
            case 'b':
                $this->line("Started populating `dim_user` table and `dim_channel` table.");
                $this->report->populateReportsBaseTbl(
                    $this->start_date,
                    $this->end_date
                );
                $this->line("Successfully populated `dim_user` table and `dim_channel` table");
                break;
            case 'o':
                $this->line("Started re-creating all dimension tables. (Old tables will be cleaned)");
                $this->report->overwriteDimensionTbl(
                    $this->start_date,
                    $this->end_date
                );
                $this->line("Successfully re-created all dimension tables.");
                break;
            case 'd':
                $this->line("Started populating all dimension tables. (new data will be appended to old data)");
                $this->report->populateReportsDimensionTbl(
                    $this->start_date,
                    $this->end_date
                );
                $this->line("Successfully populated all dimension tables.");
                break;
            case 'q':
                $this->line("Started populating question level report tables.");
                $this->report->populateReportsQuestionsTbl(
                    $this->start_date,
                    $this->end_date
                );
                $this->line("Successfully populated question level report tables.");
                break;
            default:
                $this->line("Invalid table type specified. Allowed types are b: Base Table o: Over write all dimension table d: Dimension table, q: Questions");
                $name = $this->anticipate(
                    'Invalid table type specified.  Allowed types are',
                    ['b: Base Table', 'o: Over write all dimension table', 'd: Dimension table',  'q: Questions']
                );
                $this->type = substr($name, 0, 1);
                $this->triggerTblPopulate();
                break;
        }
    }
}
