<?php

namespace App\Console\Commands;

use App\Enums\WebEx\NBRServices;
use App\Model\Event\IEventRepository;
use Illuminate\Console\Command;
use App\Libraries\webex\WebExNBR;
use Log;

class WebExDownload extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webex:download {job=""} {limit=10} {start=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to download the WebEx recording from WebEx server using NBR API';

    /**
     * @var \App\Model\Event\IEventRepository
     */
    protected $eventRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IEventRepository $eventRepository)
    {
        parent::__construct();
        $this->eventRepository = $eventRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('Webex download CRON called');
        $nbr = new WebExNBR(config('app.webex_site_id'), config('app.webex_admin_username'), config('app.webex_admin_password'));
        $nbr->setRequestUrl(config('app.webex_nbr_url'));
        $nbr->generateTicket();
        $job = $this->argument('job');
        switch ($job) {
            case 'all':
                $xml = $nbr->getRecordingsList();
                $doc = new \DOMDocument();
                $doc->loadXML($xml);
                $nodes = $doc->getElementsByTagName('RecordId');
                $this->info("Total recordings $nodes->length");
                $this->info("Starts with ".$this->argument('start'));
                $bar = $this->output->createProgressBar($this->argument('limit'));
                for($i = $this->argument('start'); $i<($this->argument('limit')+$this->argument('start')); $i++) {
                    Log::debug($nodes->item($i)->nodeValue ." starts downloading");
                    try {
                        $data = $nbr->getRecordingData($nodes->item($i)->nodeValue);
                        file_put_contents(base_path() . config('app.webex_recording_path') . $data['name'], $data['body']);
                    } catch (\Exception $e) {
                        Log::debug($e->getTraceAsString());
                        $error = true;
                        continue;
                    }
                    Log::debug($nodes->item($i)->nodeValue ." - ". $data['name'] ." downloaded");
                    $bar->advance();
                }
                $bar->finish();
                $this->info("Ends with ".$this->argument('limit'));
                break;
            default:
                $events = $this->eventRepository->getEventsWithRecordings();
                if (!$events->isEmpty()) {
                    $events->each(function ($event) use($nbr) {
                        $error = false;
                        foreach($event->recordings as $recording) {
                            if (!isset($recording['recordingID'])) {
                                Log::info('recording id not exist');
                                continue;
                            }
                            Log::debug("Event $event->event_name - ($event->event_id) starts downloading");
                            try {
                                $data = $nbr->getRecordingData($recording['recordingID']);
                                $file = fopen(base_path() . config('app.webex_recording_path') . $data['name'], 'w');
                                fwrite($file, $data['body']);
                                fclose($file);
                            } catch (\Exception $e) {
                                Log::debug($e->getMessage());
                                $error = true;
                                continue;
                            }
                            Log::debug($recording['recordingID'] ." - ". $data['name'] ." downloaded");
                        }
                        if (!$error) {
                            $event->recording_downloaded = true;
                        }
                        $event->recording_uploaded = false;
                        $event->save();
                    });
                } else {
                    Log::info("No events found");
                }
                break;
        }
    }
}
