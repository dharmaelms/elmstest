<?php

namespace App\Console\Commands;

use App\Model\Event\IEventRepository;
use Illuminate\Console\Command;
use Log;

class GDriveUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gdrive:upload {job=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to upload the WebEx ARF files to Google Drive';

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
        Log::info('Google drive upload command called');
        $client = new \Google_Client();
        $client->setLogger(\Log::getMonolog());
        $client->setAccessType('offline');
        $client->setApplicationName('demo');
        $client->addScope("https://www.googleapis.com/auth/drive");
        $client->setClientId(config("app.gdrive.client_id"));
        $client->setClientSecret(config("app.gdrive.client_secret"));
        $credentials = [
            "access_token" => config("app.gdrive.access_token"),
            "refresh_token" => config("app.gdrive.refresh_token"),
            "token_type" => "Bearer",
            "expires_in" => 3600,
        ];
        $client->setAccessToken($credentials);
        $drive = new \Google_Service_Drive($client);
        $job = $this->argument('job');
        switch ($job) {
            case 'all':
                foreach (new \RegexIterator(new \DirectoryIterator(base_path() . config('app.webex_recording_path')), "/\\.arf\$/i") as $file) {
                    if ($file->isFile()) {
                        Log::debug('Uploading '.$file->getFilename());
                        $drive_file = new \Google_Service_Drive_DriveFile([
                            'name' => $file->getFilename(),
                            'parents' => [config('app.gdrive.folder_id')],
                        ]);
                        $drive->files->create($drive_file,
                            [
                                'data' => file_get_contents(base_path() . config('app.webex_recording_path') . $drive_file->getName()),
                            ]
                        );
                        Log::debug('Uploaded '.$file->getFilename());
                        unlink(base_path() . config('app.webex_recording_path') . $file->getFilename());
                        Log::debug($drive_file->getName() . ' file removed from local');
                    }
                }
                break;
            default:
            $events = $this->eventRepository->getDownloadedEvents();
            if (!$events->isEmpty()) {
                $events->each(function ($event) use ($drive) {
                    Log::debug("Event $event->event_name starts uploading");
                    $error = false;
                    $recordings = $event->recordings;
                    foreach($event->recordings as $key => $recording) {
                        try {
                            $file = new \Google_Service_Drive_DriveFile([
                                'name' => str_replace(' ', '-', $recording['name']) . '.arf',
                                'parents' => [config('app.gdrive.folder_id')],
                            ]);
                            if (array_get($recording, 'g_drive_id')) {
                                unlink(base_path(). config('app.webex_recording_path') . $file->getName());
                                continue;
                            }
                            if(!file_exists(base_path(). config('app.webex_recording_path') . $file->getName())) {
                                continue;
                            }
                            $result = $drive->files->create($file,
                                [
                                    'data' => file_get_contents(base_path(). config('app.webex_recording_path') . $file->getName()),
                                ]
                            );
                            $recordings[$key]['g_drive_id'] = $result->id;
                            Log::debug($recording['recordingID'] . " uploaded");
                            unlink(base_path(). config('app.webex_recording_path') . $file->getName());
                            Log::debug($file->getName() . ' file removed from local');
                        } catch (\Exception $e) {
                            Log::debug($e->getMessage());
                            $error = true;
                            continue;
                        }
                    }
                    $event->recordings = $recordings;
                    if (!$error) {
                        $event->recording_uploaded = true;
                    }
                    $event->save();
                });
                Log::info('Google drive upload cron completed');
            } else {
                Log::info("No events found");
            }
        }
    }
}
