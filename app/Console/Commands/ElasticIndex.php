<?php

namespace App\Console\Commands;

use App\Enums\Elastic\Types as ET;
use App\Libraries\Elastic\Elastic;
use App\Model\Category;
use App\Model\Event;
use App\Model\Package\Entity\Package;
use App\Model\Packet;
use App\Model\Program;
use App\Model\Quiz;
use App\Model\UserGroup;
use Illuminate\Console\Command;
use Log;

class ElasticIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:index {index=program} {limit=500} {start=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add documents(Index) to elastic search for portal search';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $index = config('elastic.params.index');
        if ($index) {
            $elastic = new Elastic();
            switch ($this->argument('index')) {
                case 'program':
                    $this->info('indexing programs starts');
                    $data = Program::where('status', 'ACTIVE')
                                   ->whereIn('program_type', ["content_feed", "course"])
                                   ->where('program_sub_type', 'single')
                                   ->skip((int)$this->argument('start'))
                                   ->limit((int)$this->argument('limit'))
                                   ->orderBy('program_id')
                                   ->get();
                    $bar = $this->output->createProgressBar($data->count());
                    $data->each(function ($program) use ($elastic, &$bar, $index) {
                        Log::info('Starts program(' . $program->program_id . ')');
                        $program_data = [
                            'id' => $program->program_id,
                            'title' => $program->program_title,
                            'description' => $program->program_description,
                            'short_title' => $program->program_shortname,
                            'categories' => '',
                            'keywords' => $program->program_keywords,
                            'type' => $program->program_type,
                            'sub_type' => $program->program_sub_type,
                            'slug' => $program->program_slug,
                            'cover_image' => $program->program_cover_media,
                        ];
                        if (!empty($program->program_categories)) {
                            $program_data['categories'] = Category::whereIn('category_id',
                                $program->program_categories)
                                                                  ->where('status', 'ACTIVE')
                                                                  ->pluck('category_name')
                                                                  ->all();
                        }
                        $users = array_get($program, 'attributes.relations.active_user_feed_rel', []);
                        $usergroups = array_get($program,
                            'attributes.relations.active_usergroup_feed_rel', []);
                        $groups = [];
                        if ($program->program_type == 'content_feed' && $program->program_sub_type == 'single') {
                            if (!empty(array_get($program, 'parent_relations.active_parent_rel',
                                []))
                            ) {
                                $packages = Package::whereIn('package_id',
                                    array_get($program, 'parent_relations.active_parent_rel'))->get();
                                $users = array_unique(array_merge($users,
                                    array_flatten($packages->pluck('user_ids')->all())));
                                $usergroups = array_unique(array_flatten(array_merge($usergroups,
                                    array_flatten($packages->pluck('user_group_ids')->all()))));
                            }
                        }
                        if (!empty($usergroups)) {
                            $groups = UserGroup::where('status', 'ACTIVE')
                                               ->whereIn('ugid', $usergroups)
                                               ->get()
                                               ->pluck('attributes.relations.active_user_usergroup_rel')
                                               ->all();
                        }
                        $users = array_filter(array_map('intval',
                            array_flatten(array_unique(array_merge(array_flatten($users),
                                array_flatten($groups))))));
                        $users = array_map('intval', array_flatten($users));
                        $program_data['user_ids'] = $users;
                        $elastic->index([
                            'index' => $index,
                            'type' => ET::PROGRAM,
                            'id' => $program->_id,
                            'body' => $program_data,
                        ]);
                        Log::info($program->program_title . ' indexed');
                        $program_id = $program->program_id;
                        $posts = Packet::where('status', 'ACTIVE')
                                       ->where('feed_slug', $program->program_slug)
                                       ->get();
                        $posts->each(function ($post) use ($elastic, $index, $users, $program_id) {
                            $post_data = [
                                'program_id' => $program_id,
                                'id' => $post->packet_id,
                                'title' => $post->packet_title,
                                'description' => $post->packet_description,
                                'slug' => $post->packet_slug,
                                'program_slug' => $post->feed_slug,
                                'no_of_elements' => count($post->elements),
                                'cover_image' => $post->packet_cover_media,
                                'user_ids' => $users,
                            ];
                            $sequential = $post_data['sequential'] = array_get($post,
                                'sequential_access', 'no') == 'yes' ? true : false;
                            $elastic->index([
                                'index' => $index,
                                'type' => ET::POST,
                                'id' => $post->_id,
                                'body' => $post_data,
                            ]);
                            Log::info($post->packet_title . ' indexed');
                            if (!empty($post->elements)) {
                                $parameters = [];
                                foreach ($post->elements as $element) {
                                    $parameters['body'][] = [
                                        "index" => [
                                            '_index' => $index,
                                            '_type' => ET::ITEM,
                                        ],
                                    ];

                                    $parameters['body'][] = [
                                        'id' => $element['id'],
                                        'program_id' => $program_id,
                                        'post_id' => $post->packet_id,
                                        'title' => $element['name'],
                                        'description' => array_get($element, 'display_name', ''),
                                        'type' => $element['type'],
                                        'sequential' => $sequential,
                                        'slug' => $post->packet_slug,
                                        'program_slug' => $post->feed_slug,
                                        'user_ids' => $users,
                                    ];
                                }
                                $elastic->bulk($parameters);
                            }
                        });
                        Log::debug("Program " . $program->program_title . "(" . $program->program_id . ")");
                        $bar->advance();
                    });
                    $bar->finish();
                    break;
                case 'package':
                    $this->info('indexing packages starts');
                    $packages = Package::where('status', 'ACTIVE')->count();
                    $bar = $this->output->createProgressBar($packages);
                    Package::where('status', 'ACTIVE')
                    ->orderBy('package_id')
                    ->chunk(100, function ($packages) use ($elastic, &$bar, $index) {
                        $packages->each(function ($package) use ($elastic, &$bar, $index) {
                            Log::info('Starts package(' . $package->package_id . ')');
                            $package_data = [
                                'id' => $package->package_id,
                                'title' => $package->package_title,
                                'description' => $package->package_description,
                                'short_title' => $package->package_shortname,
                                'categories' => '',
                                'keywords' => $package->package_keywords,
                                'slug' => $package->package_slug,
                                'cover_image' => $package->package_cover_media,
                            ];
                            if (!empty($package->package_categories)) {
                                $package_data['categories'] = Category::whereIn('category_id', $package->package_categories)->where('status', 'ACTIVE')->pluck('category_name')->all();
                            }
                            $users = array_get($package, 'user_ids', []);
                            $usergroups = array_get($package, 'user_group_ids', []);
                            $groups = [];
                            if (!empty($usergroups)) {
                                $groups = UserGroup::where('status', 'ACTIVE')
                                    ->whereIn('ugid', $usergroups)
                                    ->get()
                                    ->pluck('attributes.relations.active_user_usergroup_rel')
                                    ->all();
                            }
                            $users = array_filter(array_map('intval', array_flatten(array_unique(array_merge(array_flatten($users), array_flatten($groups))))));
                            $package_data['user_ids'] = array_map('intval', array_flatten($users));
                            $elastic->index([
                                'index' => $index,
                                'type' => ET::PACKAGE,
                                'id' => $package->_id,
                                'body' => $package_data
                            ]);
                            Log::info($package->package_title . ' indexed');
                            Log::debug("Package " . $package->package_title . "(" . $package->package_id . ")");
                            $bar->advance();
                        });
                    });
                    $bar->finish();
                    break;
                case 'assessment':
                    $this->info('indexing assessment starts');
                    $quiz = Quiz::where('status', 'ACTIVE')->count();
                    $bar = $this->output->createProgressBar($quiz);
                    Quiz::where('status', 'ACTIVE')
                        ->orderBy('quiz_id')
                        ->chunk(100, function ($quizzes) use ($elastic, &$bar, $index) {
                            $quizzes->each(function ($quiz) use ($elastic, &$bar, $index) {
                                Log::info('Quiz '.$quiz->quiz_name . ' started');
                                $quiz_data = [
                                    'id' => $quiz->quiz_id,
                                    'title' => $quiz->quiz_name,
                                    'description' => $quiz->quiz_description,
                                    'keywords' => $quiz->keywords,
                                ];
                                $users = array_get($quiz, 'attributes.relations.active_user_quiz_rel', []);
                                $usergroups = array_get($quiz, 'attributes.relations.active_usergroup_quiz_rel', []);
                                $groups = [];
                                if (!empty($usergroups)) {
                                    $groups = UserGroup::where('status', 'ACTIVE')
                                        ->whereIn('ugid', $usergroups)
                                        ->get()
                                        ->pluck('attributes.relations.active_user_usergroup_rel')
                                        ->all();
                                }
                                $users = array_filter(array_map('intval', array_flatten(array_unique(array_merge(array_flatten($users), array_flatten($groups))))));
                                $quiz_data['user_ids'] = array_map('intval', array_flatten($users));
                                $elastic->index([
                                    'index' => $index,
                                    'type' => ET::ASSESSMENT,
                                    'id' => $quiz->_id,
                                    'body' => $quiz_data
                                ]);
                                Log::info('Quiz '.$quiz->quiz_name . ' indexed(' . $quiz->quiz_id . ')');
                            });
                            $bar->advance();
                        });
                    $bar->finish();
                    break;
                case 'event':
                    $this->info('indexing event starts');
                    $event = Event::where('status', 'ACTIVE')->count();
                    $bar = $this->output->createProgressBar($event);
                    Event::where('status', 'ACTIVE')
                        ->orderBy('event_id')
                        ->chunk(100, function ($events) use ($elastic, &$bar, $index) {
                            $events->each(function ($event) use ($elastic, &$bar, $index) {
                                Log::info('Event '.$event->event_name . ' started');
                                $event_data = [
                                    'id' => $event->event_id,
                                    'title' => $event->event_name,
                                    'description' => $event->event_description,
                                    'type' => $event->event_type,
                                    'keywords' => $event->keywords,
                                    'start_time' => (int)$event->start_time->timestamp,
                                ];
                                $users = array_get($event, 'attributes.relations.active_user_event_rel', []);
                                $usergroups = array_get($event, 'attributes.relations.active_usergroup_event_rel', []);
                                $groups = [];
                                if (!empty($usergroups)) {
                                    $groups = UserGroup::where('status', 'ACTIVE')->whereIn('ugid', $usergroups)
                                        ->get()
                                        ->pluck('attributes.relations.active_user_usergroup_rel')
                                        ->all();
                                }
                                $users = array_filter(array_map('intval', array_flatten(array_unique(array_merge(array_flatten($users), array_flatten($groups))))));
                                $users = array_merge($users, [$event->event_host_id]);
                                $event_data['user_ids'] = array_map('intval', array_flatten($users));
                                $elastic->index([
                                    'index' => $index,
                                    'type' => ET::EVENT,
                                    'id' => $event->_id,
                                    'body' => $event_data
                                ]);
                                Log::info('Event '.$event->event_name . ' indexed(' . $event->event_id . ')');
                            });
                            $bar->advance();
                    });
                    $bar->finish();
                    break;
                case 'delete':
                    $index_name = $this->ask('Please enter the index name you want to delete?');
                    if ($index_name == $index) {
                        if ($this->confirm('Are you sure to delete the "' . $index . '" index?')) {
                            $elastic->deleteIndex($index);
                            $this->comment('Index deleted');
                        }
                    } else {
                        $this->line('Enter valid index name..');
                    }
                    break;
            }
        } else {
            $this->warning('Please specify index');
        }
    }
}
