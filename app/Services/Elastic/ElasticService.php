<?php
namespace App\Services\Elastic;

use App\Enums\Elastic\Types as ET;
use App\Model\Assignment\Repository\IAssignmentRepository;
use App\Model\Elastic\Repository\IElasticRepository;
use App\Model\Event\IEventRepository;
use App\Model\Package\Repository\IPackageRepository;
use App\Model\Post\IPostRepository;
use App\Model\Program\IProgramRepository;
use App\Model\Quiz\IQuizRepository;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use Log;

/**
 * class ElasticService
 * @package App\Services\Elastic
 */
class ElasticService implements IElasticService
{
    /**
     * @var App\Model\Elastic\Repository\IElasticRepository
     */
    protected $elastic_repository;

    /**
     * @var [type]
     */
    protected $package_repository;

    /**
     * @var App\Model\Post\IPostRepositiry
     */
    protected $post_repository;

    /**
     * @var App\Model\Program\IprogramRepository
     */
    protected $program_repository;

    /**
     * @var App\Model\UserGroup\Repository\IUserGroupRepository
     */
    protected $usergroup_repository;

    /**
     * @var App\Model\Quiz\IQuizRepository
     */
    protected $quiz_repository;

    /**
     * @var App\Model\Event\IEventRepository
     */
    protected $event_repository;

    /**
     * @var App\Model\Assignment\IAssignmentRepository
     */
    protected $assignment_repository;

    /**
     * ElasticService constructor
     *
     * @param IElasticRepository $elastic_repository
     * @param IPostRepository    $post_repository
     * @param IProgramRepository $program_repository
     */
    public function __construct(
        IElasticRepository $elastic_repository,
        IPackageRepository $package_repository,
        IPostRepository $post_repository,
        IProgramRepository $program_repository,
        IUserGroupRepository $usergroup_repository,
        IQuizRepository $quiz_repository,
        IEventRepository $event_repository,
        IAssignmentRepository $assignment_repository
    ) {
        $this->elastic_repository = $elastic_repository;
        $this->package_repository = $package_repository;
        $this->post_repository = $post_repository;
        $this->program_repository = $program_repository;
        $this->usergroup_repository = $usergroup_repository;
        $this->quiz_repository = $quiz_repository;
        $this->event_repository = $event_repository;
        $this->assignment_repository = $assignment_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function indexProgram($program_id, $slug_changed = false, $new_program = true)
    {
        $program = $this->program_repository->getProgramById((int)$program_id);
        if ($new_program) {
            $program_data = [
                '_id' => $program->_id,
                'body' => [
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
                ]
            ];
            $this->elastic_repository->addProgram($program_data);
        } else {
            $program_data = [
                '_id' => $program->_id,
                'body' => [
                    'id' => $program->program_id,
                    'title' => $program->program_title,
                    'description' => $program->program_description,
                    'short_title' => $program->program_shortname,
                    'keywords' => $program->program_keywords,
                    'type' => $program->program_type,
                    'sub_type' => $program->program_sub_type,
                    'slug' => $program->program_slug,
                    'cover_image' => $program->program_cover_media,
                ]
            ];
            try {
                $this->elastic_repository->updateProgram($program_data);
                if ($slug_changed) {
                    $this->elastic_repository->updateProgramSlug($program->program_id, $program->program_slug);
                }
            } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                $this->indexProgram($program_id, false, true);
            } catch (\Elasticsearch\Common\Exceptions\Conflict409Exception $e) {
                Log::info('Conflict in updating program (' . $program->program_title .')');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteProgram($program_id)
    {
        $program = $this->program_repository->getProgramById((int)$program_id);
        $this->elastic_repository->removeProgram($program);
    }

    /**
     * {@inheritdoc}
     */
    public function indexPackage($package_id, $slug_changed = false, $new_package = true)
    {
        $package = $this->package_repository->find((int)$package_id);
        if ($new_package) {
            $package_data = [
                '_id' => $package->_id,
                'body' => [
                    'id' => $package->package_id,
                    'title' => $package->package_title,
                    'description' => $package->package_description,
                    'short_title' => $package->package_shortname,
                    'categories' => '',
                    'keywords' => $package->package_keywords,
                    'slug' => $package->package_slug,
                    'cover_image' => $package->package_cover_media,
                ]
            ];
            $this->elastic_repository->addPackage($package_data);
        } else {
            $package_data = [
                '_id' => $package->_id,
                'body' => [
                    'id' => $package->package_id,
                    'title' => $package->package_title,
                    'description' => $package->package_description,
                    'short_title' => $package->package_shortname,
                    'keywords' => $package->package_keywords,
                    'slug' => $package->package_slug,
                    'cover_image' => $package->package_cover_media,
                ]
            ];
            try {
                $this->elastic_repository->updatePackage($package_data);
            } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                $this->indexPackage($package_id, false, true);
            } catch (\Elasticsearch\Common\Exceptions\Conflict409Exception $e) {
                Log::info('Conflict in updating program (' . $package->package_title .')');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletePackage($package_id)
    {
        $package = $this->package_repository->find((int)$package_id);
        $this->elastic_repository->removePackage($package->_id);
    }

    /**
     * {@inheritdoc}
     */
    public function indexPost($post_id, $slug_changed, $is_new)
    {
        $post = $this->post_repository->getPostByAttribute('packet_id', (int)$post_id);
        $sequential = array_get($post, 'sequential_access', 'no') == 'yes' ? true : false;
        if ($is_new) {
            $program = $this->getAssignedUsers('program_slug', $post->feed_slug);
            $post_data = [
                '_id' => $post->_id,
                'body' => [
                    'id' => $post->packet_id,
                    'program_id' => $program->program_id,
                    'title' => $post->packet_title,
                    'description' => $post->packet_description,
                    'sequential' => $sequential,
                    'slug' => $post->packet_slug,
                    'program_slug' => $post->feed_slug,
                    'no_of_elements' => count($post->elements),
                    'cover_image' => $post->packet_cover_media,
                    'user_ids' => $program->users,
                ]
            ];
            $this->elastic_repository->addPost($post_data);
            if (!empty(array_get($post, 'elements', []))) {
                $this->elastic_repository->addItems($post, $post->elements, $program);
            }
        } else {
            $post_data = [
                '_id' => $post->_id,
                'body' => [
                    'id' => $post->packet_id,
                    'title' => $post->packet_title,
                    'description' => $post->packet_description,
                    'sequential' => $sequential,
                    'slug' => $post->packet_slug,
                    'program_slug' => $post->feed_slug,
                    'no_of_elements' => count($post->elements),
                    'cover_image' => $post->packet_cover_media,
                ]
            ];
            try {
                $this->elastic_repository->updatePost($post_data);
                if ($slug_changed) {
                    $this->elastic_repository->updatePostSlug($post->packet_id, $post->packet_slug, $sequential);
                }
            } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                $this->indexPost($post_id, false, true);
            } catch (\Elasticsearch\Common\Exceptions\Conflict409Exception $e) {
                Log::ingo('Conflict in updating program (' . $post->packet_title .')');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deletePost($post_id)
    {
        $post = $this->post_repository->getPostByAttribute('packet_id', (int)$post_id);
        $this->elastic_repository->removePost($post);
        $this->elastic_repository->deleteByQuery('post_id', $post_id, [ET::ITEM]);
    }

    /**
     * {@inheritdoc}
     */
    public function indexItems($post_id)
    {
        $post = $this->post_repository->getPostByAttribute('packet_id', (int)$post_id);
        $program = $this->getAssignedUsers('program_slug', $post->feed_slug);
        $this->elastic_repository->addItems($post, $post->elements, $program);
    }

    /**
     * {@inheritdoc}
     */
    public function assignUsers($program_id)
    {
        $program = $this->getAssignedUsers('program_id', (int)$program_id);
        $this->elastic_repository->updateProgramUsers($program);
    }

    /**
     * {@inheritdoc}
     */
    public function assignUserGroup($user_group_id)
    {
        $usergroup = $this->usergroup_repository->getUserGroupsByIds([$user_group_id])->first();
        if (!empty(array_get($usergroup, 'attributes.relations.usergroup_feed_rel', []))) {
            foreach ($usergroup->relations['usergroup_feed_rel'] as $program_id) {
                $this->assignUsers($program_id);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function assignPackage($package_id)
    {
        $package = $this->package_repository->find($package_id);
        $users = array_get($package, 'user_ids', []);
        $usergroups = array_get($package, 'user_group_ids', []);
        if (!empty($usergroups)) {
            $usergroup_users = $this->usergroup_repository->getUsersByUserGroupIds($usergroups);
            $users = array_filter(array_map('intval', array_flatten(array_unique(array_merge($users, array_flatten($usergroup_users))))));
        }
        $package->users = array_map('intval', array_flatten($users));
        $this->elastic_repository->updatePackageUsers($package);
        if (!empty(array_get($package, 'program_ids', []))) {
            $this->assignProgram($package->program_ids);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function getAssignedUsers($column, $value)
    {
        $program = $this->program_repository->getProgramDataByAttribute($column, $value)->first();
        $users = array_get($program, 'attributes.relations.active_user_feed_rel', []);
        $usergroups = array_get($program, 'attributes.relations.active_usergroup_feed_rel', []);
        if (array_get($program, 'program_type') == 'content_feed' && array_get($program, 'program_sub_type') == 'single') {
            if (!empty(array_get($program, 'package_ids', []))) {
                $packages = $this->package_repository->get(['in_ids' => $program->package_ids]);
                $users = array_unique(array_merge($users, array_flatten($packages->pluck('user_ids')->all())));
                $usergroups = array_unique(array_flatten(array_merge($usergroups, array_flatten($packages->pluck('user_group_ids')->all()))));
            }
        }
        if (!empty($usergroups)) {
            $usergroup_users = $this->usergroup_repository->getUsersByUserGroupIds($usergroups);
            $users = array_filter(array_map('intval', array_flatten(array_unique(array_merge($users, array_flatten($usergroup_users))))));
        }
        $program->users = array_map('intval', array_flatten($users));
        return $program;
    }

    /**
     * {@inheritdoc}
     */
    public function assignProgram($program_ids)
    {
        foreach ($program_ids as $program) {
            $this->assignUsers($program);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function indexQuiz($quiz_id, $is_new)
    {
        try {
            $quiz = $this->quiz_repository->find($quiz_id);
            if ($is_new) {
                $quiz_data = [
                    '_id' => $quiz->_id,
                    'body' => [
                        'id' => $quiz->quiz_id,
                        'title' => $quiz->quiz_name,
                        'description' => $quiz->quiz_description,
                        'keywords' => $quiz->keywords,
                    ]
                ];
                $this->elastic_repository->addQuiz($quiz_data);
            } else {
                $quiz_data = [
                    '_id' => $quiz->_id,
                    'body' => [
                        'id' => $quiz->quiz_id,
                        'title' => $quiz->quiz_name,
                        'description' => $quiz->quiz_description,
                        'keywords' => $quiz->keywords,
                    ]
                ];
                $this->elastic_repository->updateQuiz($quiz_data);
            }
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            $this->elastic_repository->addQuiz($quiz_data);
            $this->assignQuiz($quiz->quiz_id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteQuiz($quiz_id)
    {
        $quiz = $this->quiz_repository->findByAttribute('quiz_id', (int)$quiz_id);
        $this->elastic_repository->removeQuiz($quiz->_id);
    }

    /**
     * {@inheritdoc}
     */
    public function assignQuiz($quiz_id)
    {
        $quiz = $this->quiz_repository->find($quiz_id);
        $users = array_get($quiz, 'attributes.relations.active_user_quiz_rel', []);
        $usergroups = array_get($quiz, 'attributes.relations.active_usergroup_quiz_rel', []);
        if (!empty($usergroups)) {
            $usergroup_users = $this->usergroup_repository->getUsersByUserGroupIds($usergroups);
            $users = array_filter(array_map('intval', array_flatten(array_unique(array_merge($users, array_flatten($usergroup_users))))));
        }
        $quiz->users = array_map('intval', $users);
        $this->elastic_repository->quizUsers($quiz);
    }

    /**
     * {@inheritdoc}
     */
    public function indexEvent($event_id, $is_new)
    {
        try {
            $event = $this->event_repository->find($event_id);
            if ($is_new) {
                $event_data = [
                    '_id' => $event->_id,
                    'body' => [
                        'id' => $event->event_id,
                        'title' => $event->event_name,
                        'description' => $event->event_description,
                        'type' => $event->event_type,
                        'keywords' => $event->keywords,
                        'start_time' => (int)$event->start_time->timestamp,
                    ]
                ];
                $this->elastic_repository->addEvent($event_data);
            } else {
                $event_data = [
                    '_id' => $event->_id,
                    'body' => [
                        'id' => $event->event_id,
                        'title' => $event->event_name,
                        'description' => $event->event_description,
                        'type' => $event->event_type,
                        'keywords' => $event->keywords,
                        'start_time' => (int)$event->start_time->timestamp,
                    ]
                ];
                $this->elastic_repository->updateEvent($event_data);
            }
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            $this->elastic_repository->addEvent($event_data);
            $this->assignEvent($event->event_id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEvent($event_id)
    {
        $event = $this->event_repository->find($event_id);
        $this->elastic_repository->removeEvent($event->_id);
    }

    /**
     * {@inheritdoc}
     */
    public function assignEvent($event_id)
    {
        $event = $this->event_repository->find($event_id);
        $users = array_get($event, 'attributes.relations.active_user_event_rel', []);
        $usergroups = array_get($event, 'attributes.relations.active_usergroup_event_rel', []);
        if (!empty($usergroups)) {
            $usergroup_users = $this->usergroup_repository->getUsersByUserGroupIds($usergroups);
            $users = array_filter(array_map('intval', array_flatten(array_unique(array_merge($users, array_flatten($usergroup_users))))));
        }
        $users = array_merge($users, [$event->event_host_id]);
        $event->users = array_map('intval', $users);
        $this->elastic_repository->eventUsers($event);
    }

    /**
     * {@inheritdoc}
     */
    public function assignAssignment($assignment_id)
    {
        $assignment = $this->assignment_repository->find($assignment_id);
        $users = array_get($assignment, 'attributes.users', []);
        $usergroups = array_get($assignment, 'attributes.usergroups', []);
        if (!empty($usergroups)) {
            $usergroup_users = $this->usergroup_repository->getUsersByUserGroupIds($usergroups);
            $users = array_filter(array_map('intval', array_flatten(arraykb_unique(array_merge($users, array_flatten($usergroup_users))))));
        }
        $assignment->users = array_map('intval', $users);
        $this->elastic_repository->AssignmentUsers($assignment);
    }
}
