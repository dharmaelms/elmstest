<?php

namespace App\Services\Post;

use Auth;
use App\Enums\User\UserEntity;
use App\Model\Post\IPostRepository;
use App\Model\Program\IProgramRepository;
use App\Model\User\Repository\IUserRepository;
use App\Enums\RolesAndPermissions\Contexts;
use App\Exceptions\Program\ProgramNotFoundException;
use App\Model\Package\Repository\IPackageRepository;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Model\RolesAndPermissions\Repository\IRoleRepository;
use App\Model\RolesAndPermissions\Repository\IContextRepository;
use App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository;
use App\Model\Dam;
use App\Model\Event;
use App\Model\FlashCard;
use App\Model\Quiz;

/**
 * Class ProgramService
 *
 * @package App\Services\Program
 */
class PostService implements IPostService
{
    /**
     * @var \App\Model\Post\IPostRepository
     */
    private $post_repository;

    /**
     * @var \App\Model\Program\IProgramRepository
     */
    private $program_repository;

    /**
     * @var App\Model\User\Entity\IUserRepository
     */
    private $user_repository;

    /**
     * @var /App\Model\Package\Repository\IPackageRepository
     */
    private $packageRepository;

    /**
     * @var App\Model\RolesAndPermissions\Repository\IRoleRepository
     */
    private $roleRepository;

    /**
     * @var App\Model\RolesAndPermissions\Repository\IContextRepository
     */
    private $contextRepository;

    private $channel_analytic_repo;

    public function __construct(
        IPostRepository $post_repository,
        IProgramRepository $program_repository,
        IUserRepository $user_repository,
        IPackageRepository $packageRepository,
        IRoleRepository $roleRepository,
        IContextRepository $contextRepository,
        IOverAllChannalAnalyticRepository $channel_analytic_repo
    ) {
    
        $this->post_repository = $post_repository;
        $this->program_repository = $program_repository;
        $this->user_repository = $user_repository;
        $this->packageRepository = $packageRepository;
        $this->roleRepository = $roleRepository;
        $this->contextRepository = $contextRepository;
        $this->channel_analytic_repo = $channel_analytic_repo;
    }

    /**
     * {@inheritdoc}
     * @throws ProgramNotFoundException
     */
    public function getPosts($page, $limit)
    {
        $data = [];
        $all_posts = $this->getAllPosts();
        $all_posts_id = array_unique($all_posts->lists('packet_id')->all());
        $start = ($page * $limit) - $limit;
        $columns = ['packet_id', 'packet_title', 'packet_slug', 'elements', 'feed_slug', 'packet_cover_media'];
        $posts = $this->post_repository->getUserPosts($all_posts_id, $start, $limit, $columns);
        $posts_id = $posts->lists('packet_id')->all();
        //Due to mongo load post status is hidden
        // $posts_status = $this->post_repository->getPostsStatus($posts_id);
        $data['results'] = $posts;
        /*if (!empty($posts_status)) {
            $data['statuses'] = $posts_status;
        }*/
        $posts_faq = $this->post_repository->getPostFaq($posts_id);
        if (!empty($posts_faq)) {
            $data['faqs'] = $posts_faq;
        }
        $titles = $this->program_repository->getProgramsByAttribute('program_slug', $posts->lists('feed_slug')->all(), $columns = ['program_slug', 'program_title', 'program_id', 'program_type', 'parent_id']);
        $data['programs'] = $titles;
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPosts()
    {
        $context = $this->contextRepository->findByAttribute("slug", Contexts::SYSTEM);
        $role_mapping = $this->roleRepository->findUserRoleMapping(Auth::user()->uid, $context->id);
        $role = $this->roleRepository->find($role_mapping->role_id);
        $channel_ids = [];
        if (!$role->is_admin_role) {
            $entities = $this->getAllProgramsAssignedToUser();
            $channel_ids = $entities['channel_ids'];
            if ($entities['package_ids']) {
                $package_channel_ids = array_collapse($this->packageRepository->getActivePackages(['in_ids' => $entities['package_ids']])->pluck('program_ids') ->all());
                $channel_ids = array_unique(array_merge($channel_ids, $package_channel_ids));
            }
        } else {
            $channel_ids = $this->program_repository->get()->pluck('program_id')->all();
        }
        $active_program= $this->program_repository->getProgramsData('program_id', $channel_ids, ['program_slug']);
        $posts = $this->post_repository->getAllPostsByProgramSlugs($active_program->lists('program_slug')->all(), ['packet_id']);
        if ($posts->isEmpty()) {
            throw new ProgramNotFoundException();
        }
        return $posts;
    }

    /**
     * {inheritdoc}
     */
    public function getAllProgramsAssignedToUser()
    {
        $entity = ['channel_ids' => [], 'package_ids' => [], 'package_channel_ids' => []];
        $enrollments = $this->user_repository->getUserEntities(Auth::user()->uid, ["entity_type" =>  [UserEntity::PROGRAM, UserEntity::PACKAGE] ]);
        if (!$enrollments->isEmpty()) {
            $entity['channel_ids'] = $enrollments->where('entity_type', UserEntity::PROGRAM)->pluck('entity_id')->all();
            $package_ids = $enrollments->where('entity_type', UserEntity::PACKAGE)->pluck('entity_id')->all();
            if (!empty($package_ids)) {
                $entity['package_ids'] = $package_ids;
                $packages = $this->packageRepository->get(['in_ids' => $package_ids]);
                if (!$packages->isEmpty()) {
                    $entity['packege_channel_ids'] = array_unique(array_collapse($packages->pluck('program_ids')->all()));
                }
            }
        } else {
            throw new NoProgramAssignedException();
        }
        return collect($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssessmentsCountInPostsBySlugs($slugs)
    {
        return $this->post_repository->getAssessmentsCount($slugs);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewPostsBySlug($slug)
    {
        return $this->post_repository->getNewPostsCount($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsBySlug($page, $limit, $slug)
    {
        $data = [];
        $all_posts = $this->post_repository->getAllPostsByProgramSlugs($slug, ['packet_id']);
        $all_posts_id = array_unique($all_posts->lists('packet_id')->all());
        $start = ($page * $limit) - $limit;
        $columns = ['packet_id', 'packet_title', 'packet_slug', 'elements', 'feed_slug'];
        $posts = $this->post_repository->getUserPosts($all_posts_id, $start, $limit, $columns);
        $posts_id = $posts->lists('packet_id')->all();
        if (!$posts->isEmpty()) {
            $data['results'] = $posts;
            foreach ($posts as $post) {
                $count = 0;
                if (isset($post->elements)) {
                    $count = $this->getElementsCountByType($post->elements, 'assessment');
                }
                $data['count'][$post->packet_slug] = $count;
                $data['completion'][$post->packet_slug] = $this->getPostCompletion($post->feed_slug, $post->packet_id);
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsForAll($page, $limit)
    {
        $data = [];
        $all_posts = $this->getAllPosts();
        $all_posts_id = array_unique($all_posts->lists('packet_id')->all());
        $start = ($page * $limit) - $limit;
        $columns = ['packet_id', 'packet_title', 'packet_slug', 'elements', 'feed_slug'];
        $posts = $this->post_repository->getUserPosts($all_posts_id, $start, $limit, $columns);
        $posts_id = $posts->lists('packet_id')->all();
        if (!$posts->isEmpty()) {
            $data['results'] = $posts;
            foreach ($posts as $post) {
                $count = 0;
                if (isset($post->elements)) {
                    $count = $this->getElementsCountByType($post->elements, 'assessment');
                }
                $data['count'][$post->packet_slug] = $count;
                $data['completion'][$post->packet_slug] = $this->getPostCompletion($post->feed_slug, $post->packet_id);
            }
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getElementsCountByType($elements, $type)
    {
        $count = 0;
        foreach ($elements as $element) {
            if ($element['type'] == $type) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCompletion($program_slug, $post_id)
    {
        $completion = 0;
        $program = $this->program_repository->getProgramDataByAttribute('program_slug', $program_slug, ['program_id']);
        $analytics = $this->program_repository->getProgramsAnalyticById($program[0]->program_id);
        if ($analytics->first()) {
            if (isset($analytics->first()->post_completion['p_' . $post_id])) { //if data exists
                $completion = $analytics->first()->post_completion['p_' . $post_id];
            }
        }
        return $completion;
    }

    /**
     * {@inheritdoc}
     */
    public function postDetailsBySlug($slug)
    {
        return $this->post_repository->getAllPostsByProgramSlugs($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsDataBySlug($page, $limit, $slug)
    {
        $data = [];
        $all_posts = $this->post_repository->getAllPostsByProgramSlugs($slug);
        $all_posts_id = array_unique($all_posts->lists('packet_id')->all());
        $start = ($page * $limit) - $limit;
        $columns = ['packet_id', 'packet_title', 'packet_slug', 'elements', 'feed_slug'];
        $posts = $this->post_repository->getUserPosts($all_posts_id, $start, $limit, $columns);
        $posts_id = $posts->lists('packet_id')->all();
        $posts_slug = $posts->lists('feed_slug')->all();
        $posts_status = $this->post_repository->getPostsStatus($posts_id);
        $data['results'] = $posts;
        if (!empty($posts_status)) {
            $data['statuses'] = $posts_status;
        }
        $posts_faq = $this->post_repository->getPostFaq($posts_id);
        if (!empty($posts_faq)) {
            $data['faqs'] = $posts_faq;
        }
        $titles = $this->program_repository->getProgramsByAttribute('program_slug', $posts->lists('feed_slug')->all(), $columns = ['program_slug', 'program_title']);
        $data['programs'] = $titles;
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getPacketsUsingIds($packet_ids)
    {
        if ($packet_ids > 0) {
            return $this->post_repository->getPacketsUsingIds($packet_ids);
        } else {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countNewPosts(array $program_ids, array $date)
    {
        $program_slugs = [];
        if (!empty($program_ids)) {
            $program_slugs = $this->program_repository->getSlugsByIds($program_ids);
        }
        return $this->post_repository->countNewPosts($program_slugs, $date);
    }
    
    public function getPacketsAssessement($slugs)
    {
        $packets_details = $this->post_repository->getPacketsAssessement($slugs);
        return $packets_details->groupBy('feed_slug');
    }

    /**
     * {@inheritdoc}
     */
    public function getFeedSlugsByPacketIds($packet_id)
    {
        return $this->post_repository->getFeedSlugsByPacketIds($packet_id);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePacketElementRelations(array $packet_details)
    {
        $items = array_get($packet_details, 'elements', []);
        if (!empty($items)) {
            $program = $this->program_repository->getProgramIdBySlug(array_get($packet_details, 'feed_slug', ''))->toArray();
            $this->post_repository->updatePacketElementRelations(
                $packet_details,
                $items,
                array_get($program, 'program_id', 0)
            );
        }
    }

    public function getPacketsUsingSlug($sub_program_slugs, $order_by)
    {
        return $this->post_repository->getPacketsUsingSlug($sub_program_slugs, $order_by);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewedElementsInPacket($user_id, $channel_id, $post_id)
    {
        $chennel_analytics = $this->channel_analytic_repo->getUserChannelCompletionDetails(
            [(int)$channel_id],
            (int)$user_id
        )->first();
        $result = [];
        if (!is_null($chennel_analytics)) {
            if (array_key_exists('p_'.$post_id, $chennel_analytics->item_details)) {
                foreach ($chennel_analytics->item_details['p_'.$post_id] as $element) {
                    $element_details = explode("_", $element);
                    $result[$element_details[0]][] = $element_details[1];
                }
            }
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsBySlugLimitedColumn($slugs, $columns)
    {
        return $this->post_repository->getAllPostsByProgramSlugs($slugs, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewPosts(array $program_ids, array $date, $start, $limit)
    {
        $program_slugs = [];
        if (!empty($program_ids)) {
            $program_slugs = $this->program_repository->getSlugsByIds($program_ids);
        }
        return $this->post_repository->getNewPosts($program_slugs, $date, $start, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewPostCount(array $program_ids, array $date)
    {
        $program_slugs = [];
        if (!empty($program_ids)) {
            $program_slugs = $this->program_repository->getSlugsByIds($program_ids);
        }
        return $this->post_repository->getNewPostCount($program_slugs, $date);
    }
    /**
     * @param  array $post_ids
     * @inheritdoc
     */
    public function countActivePosts($post_ids)
    {
        return $this->post_repository->countActivePosts($post_ids);
    }

    /**
     * @param  array $post_ids
     * @inheritdoc
     */
    public function countInActivePosts($post_ids)
    {
        return $this->post_repository->countInActivePosts($post_ids);
    }

    /**
     * @param  array $post_id
     * @return integer
     */
    public function getPacketByID($post_id)
    {
        return $this->post_repository->getPacketByID($post_id);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     */
    public function IncrementField($post_id, $field_name)
    {
        $this->post_repository->IncrementField($post_id, $field_name);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     */
    public function DecrementField($post_id, $field_name)
    {
        $this->post_repository->DecrementField($post_id, $field_name);
    }

    /**
     * @param  int $post_id
     * @param $field_name
     */
    public function getPostByID($id, $field_name)
    {
        return $this->post_repository->getPostByID($id, $field_name);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param int|array $input_ids
     */
    public function pushRelations($post_id, $field_name, $input_ids)
    {
        return $this->post_repository->pushRelations($post_id, $field_name, $input_ids);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param int|array $input_ids
     */
    public function pullRelations($post_id, $field_name, $input_ids)
    {
        return $this->post_repository->pullRelations($post_id, $field_name, $input_ids);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param array $data
     */
    public function updateRelationsByID($post_id, $field_name, $data)
    {
        return $this->post_repository->updateRelationsByID($post_id, $field_name, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPackets($slug)
    {
        return $this->post_repository->getAllPackets($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPacketsByFeedSlug($slug, $status)
    {
        return $this->post_repository->getAllPacketsByFeedSlug($slug, $status);
    }
}
