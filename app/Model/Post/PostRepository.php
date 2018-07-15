<?php

namespace App\Model\Post;

use App\Exceptions\Post\NoPostAssignedException;
use App\Exceptions\Post\PostNotFoundException;
use App\Model\Dam;
use App\Model\Event;
use App\Model\FlashCard;
use App\Model\MyActivity;
use App\Model\Packet;
use App\Model\PacketFaq;
use App\Model\Quiz;

use Auth;

/**
 * Class AnnouncementRepository
 *
 * @package App\Model\Announcement
 */
class PostRepository implements IPostRepository
{
    /**
     * {@inheritdoc}
     */
    public function getUserPosts($post_ids, $start, $limit, $columns = [])
    {
        $posts = Packet::whereIn('packet_id', $post_ids)
            ->where('status', 'ACTIVE')
            ->orderBy('updated_at', 'DESC')
            ->skip((int)$start)
            ->limit((int)$limit)
            ->get($columns);
        if ($posts->isEmpty()) {
            throw new PostNotFoundException();
        } else {
            return $posts;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPostsByProgramSlugs($program_slugs, $columns = [])
    {
        $posts = Packet::whereIn('feed_slug', $program_slugs)
            ->where('status', 'ACTIVE')
            ->get($columns);
        if ($posts->isEmpty()) {
            throw new NoPostAssignedException();
        } else {
            return $posts;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizIdByProgramSlugs($slugs)
    {
        return Packet::raw(function ($c) use ($slugs) {
            return $c->aggregate([
                [
                    '$match' => [
                        'feed_slug' => ['$in' => $slugs],
                        'status' => 'ACTIVE'
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'packet_id' => '$packet_id',
                        ],
                        'post_ids' => ['$addToSet' => '$elements.id'],
                        'elements' => ['$addToSet' => '$elements'],
                        'packet_name' => ['$addToSet' => '$packet_title'],
                        'packet_slug' => ['$addToSet' => '$packet_slug'],
                        'feed_slug' => ['$addToSet' => '$feed_slug'],
                        'sequential_access' => ['$addToSet' => '$sequential_access']
                    ]
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$match' => [
                        'elements.type' => 'assessment'
                    ]
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$match' => [
                        'elements.type' => 'assessment'
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'packet_id' => '$_id.packet_id',
                        ],
                        'quiz_ids' => ['$addToSet' => '$elements.id'],
                        'packet_name' => ['$addToSet' => '$packet_name'],
                        'feed_slug' => ['$addToSet' => '$feed_slug'],
                        'packet_slug' => ['$addToSet' => '$packet_slug'],
                        'sequential_access' => ['$addToSet' => '$sequential_access']
                    ]
                ],
                [
                    '$project' => [
                        'packet_id' => '$_id.packet_id',
                        'quiz_ids' => 1,
                        'packet_name' => 1,
                        'packet_slug' => 1,
                        'feed_slug' => 1,
                        'sequential_access' => 1,
                        '_id' => 0
                    ]
                ],
                [
                    '$unwind' => '$feed_slug'
                ],
                [
                    '$unwind' => '$feed_slug'
                ],
                [
                    '$unwind' => '$packet_slug'
                ],
                [
                    '$unwind' => '$packet_slug'
                ],
                [
                    '$unwind' => '$packet_name'
                ],
                [
                    '$unwind' => '$packet_name'
                ],
                [
                    '$unwind' => '$sequential_access'
                ],
                [
                    '$unwind' => '$sequential_access'
                ]
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getElementsFromPosts($posts, $type)
    {
        $feed_quiz_list = [];
        foreach ($posts as $post) {
            foreach ($post->elements as $value) {
                if ($value['type'] == $type) {
                    $feed_quiz_list[$post->feed_slug][] = (int)$value['id'];
                }
            }
        }
        return $feed_quiz_list;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostsStatus($post_ids)
    {
        $posts = Packet::whereIn('packet_id', $post_ids)
            ->where('status', 'ACTIVE')
            ->get(['packet_id', 'elements']);
        $posts_status = [];
        $status = '';
        if (!$posts->isEmpty()) {
            foreach ($posts as $post) {
                if (!empty($post->elements)) {
                    $total = count($post->elements);
                    $activity_count = count(MyActivity::getPacketElementDetails(Auth::user()->uid, $post->packet_id));
                    if ($activity_count == $total) {
                        $status = 'completed';
                    } elseif ($activity_count > 0 && $activity_count < $total) {
                        $status = 'pending';
                    } else {
                        $status = 'new';
                    }
                }
                $posts_status[$post->packet_id] = $status;
            }
        }
        return $posts_status;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostFaq($posts_id)
    {
        $faqs = [];
        foreach ($posts_id as $id) {
            $faqs[$id] = PacketFaq::whereIn('packet_id', [$id])
                ->where('status', 'UNANSWERED')
                ->where('access', '=', 'public')
                ->count();
        }

        return $faqs;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssessmentsCount($slugs)
    {
        $posts = Packet::where('status', '=', 'ACTIVE')
            ->whereIn('feed_slug', $slugs)
            ->get(['elements']);
        $count = 0;
        foreach ($posts as $post) {
            foreach ($post->elements as $element) {
                if ($element['type'] == 'assessment') {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewPostsCount($slug)
    {
        $posts = Packet::whereIn('feed_slug', $slug)
            ->where('status', 'ACTIVE')
            ->get(['packet_id', 'elements']);
        $posts_status = [];
        $count = 0;
        if (!$posts->isEmpty()) {
            foreach ($posts as $post) {
                if (!empty($post->elements)) {
                    $total = count($post->elements);
                    $activity_count = 0;
                    foreach ($post->elements as $element) {
                        $activity = MyActivity::pluckElementActivity($post->packet_id, $element['id'], $element['type']); //checking user viewed or not
                        if (!empty($activity)) {
                            $activity_count = $activity_count + 1; //if user viewed increase count
                        }
                    }
                    if ($activity_count == 0) {
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function postsBySlug($page, $limit, $slug)
    {
        $posts = Packet::whereIn('feed_slug', $slug)
            ->where('status', 'ACTIVE')
            ->skip((int)$page)
            ->take((int)$limit)
            ->get(['packet_slug']);
        return $posts;
    }

    /**
     * {@inheritdoc}
     */
    public function getPacketsUsingIds($packet_ids)
    {
        return Packet::whereIn('packet_id', $packet_ids)
            ->where('status', '=', 'ACTIVE')
            ->where('packet_publish_date', '<=', time())
            ->orderby('updated_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function countNewPosts(array $program_slugs, array $date)
    {
        $query = Packet::whereBetween('created_at', $date);
        if (!empty($program_slugs)) {
            $query->whereIn('feed_slug', $program_slugs);
        }
        return $query->count();
    }

    public function getPacketsAssessement($slugs)
    {
        return Packet::raw(function ($c) use ($slugs) {
            return $c->aggregate([
                [
                    '$match' => [
                        'feed_slug' => ['$in' => $slugs]
                    ]
                ],
                [
                    '$match' => [
                        'elements.type' => 'assessment'
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'packet_id' => '$packet_id',
                        ],
                        'elements' => ['$addToSet' => '$elements'],
                        'packet_name' => ['$addToSet' => '$packet_title'],
                        'packet_slug' => ['$addToSet' => '$packet_slug'],
                        'feed_slug' => ['$addToSet' => '$feed_slug']
                    ]
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$unwind' => '$elements'
                ],
                [
                    '$match' => [
                        'elements.type' => 'assessment'
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            'packet_id' => '$_id.packet_id',
                        ],
                        'quiz_ids' => ['$addToSet' => '$elements.id'],
                        'packet_name' => ['$addToSet' => '$packet_name'],
                        'feed_slug' => ['$addToSet' => '$feed_slug'],
                        'packet_slug' => ['$addToSet' => '$packet_slug']
                    ]
                ],
                [
                    '$project' => [
                        'packet_id' => '$_id.packet_id',
                        'quiz_ids' => 1,
                        'packet_name' => 1,
                        'packet_slug' => 1,
                        'feed_slug' => 1,
                        '_id' => 0
                    ]
                ],
                [
                    '$unwind' => '$feed_slug'
                ],
                [
                    '$unwind' => '$feed_slug'
                ],
                [
                    '$unwind' => '$packet_slug'
                ],
                [
                    '$unwind' => '$packet_slug'
                ],
                [
                    '$unwind' => '$packet_name'
                ],
                [
                    '$unwind' => '$packet_name'
                ]
            ]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getActivePostsCount($feed_slug)
    {
        return Packet::where('feed_slug', $feed_slug)->active()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getFeedSlugsByPacketIds($packet_id)
    {
        return Packet::whereIn('packet_id', $packet_id)->pluck('feed_slug')->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getPostByAttribute($field, $value)
    {
        return Packet::where($field, $value)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function updatePacketElementRelations(array $packet_details, array $items, $program_id)
    {
        if (!empty($items) && !empty($packet_details) && $program_id >= 1) {
            foreach ($items as $item) {
                switch (array_get($item, 'type', 'default')) {
                    case "media":
                        Dam::updateDAMSRelationUsingID(
                            (int)$item['id'],
                            'dams_packet_rel',
                            $packet_details['packet_id']
                        );
                        break;
                    case "assessment":
                        Quiz::addQuizRelationForFeed(
                            (int)$item['id'],
                            (string)$program_id,
                            $packet_details['packet_id']
                        );
                        break;
                    case "event":
                        Event::where("event_id", (int)$item['id'])->push("relations.feed_event_rel.{$program_id}", [$packet_details['packet_id']]);
                        break;
                    case "flashcard":
                        FlashCard::addFlashcardRelation((int)$item['id'], ['flashcard_packet_rel'], $packet_details['packet_id']);
                        break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPacketsUsingSlug($sub_program_slugs, $order_by)
    {
        return Packet::getPacketsUsingSlug($sub_program_slugs, $order_by);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewPosts(array $program_slugs, array $date, $start, $limit)
    {
        $query = Packet::whereBetween('created_at', $date)
                        ->where('status', '=', 'ACTIVE');
        if (!empty($program_slugs)) {
            $query->whereIn('feed_slug', $program_slugs);
        }
        return $query->skip((int)$start)
                    ->take((int)$limit)
                    ->orderBy('created_at', 'desc')
                    ->get(['packet_title', 'feed_slug']);//->keyBy('feed_slug');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewPostCount(array $program_slugs, array $date)
    {
        $query = Packet::whereBetween('created_at', $date)
                            ->where('status', '=', 'ACTIVE');
        if (!empty($program_slugs)) {
            $query->whereIn('feed_slug', $program_slugs);
        }
        return $query->count();
    }

    /**
     * @param  array $post_ids
     * @return integer
     */
    public function countActivePosts($post_ids)
    {
        if (!empty($post_ids)) {
            return Packet::whereIn('packet_id', $post_ids)->where('status', 'ACTIVE')->count();
        } else {
            return Packet::where('status', 'ACTIVE')->count();
        }
    }

    /**
     * @param  array $post_ids
     * @return integer
     */
    public function countInActivePosts($post_ids)
    {
        if (!empty($post_ids)) {
            return Packet::whereIn('packet_id', $post_ids)->where('status', 'IN-ACTIVE')->count();
        } else {
            return Packet::where('status', 'IN-ACTIVE')->count();
        }
    }

    /**
     * @param  array $post_id
     * @return integer
     */
    public function getPacketByID($post_id)
    {
        $packet = Packet::getPacketByID($post_id);
        return array_get($packet, '0');
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     */
    public function IncrementField($post_id, $field_name)
    {
        Packet::IncrementField($post_id, $field_name);
    }

     /**
     * @param  int $post_id
     * @param string $field_name
     */
    public function DecrementField($post_id, $field_name)
    {
        Packet::DecrementField($post_id, $field_name);
    }

    /**
     * @param  int $id
     * @param $field_name
     */
    public function getPostByID($id, $field_name)
    {
        return Packet::getPostByID($id, $field_name);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param int|array $input_ids
     */
    public function pushRelations($post_id, $field_name, $input_ids)
    {
        return Packet::pushRelations($post_id, $field_name, $input_ids);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param int|array $input_ids
     */
    public function pullRelations($post_id, $field_name, $input_ids)
    {
        return Packet::pullRelations($post_id, $field_name, $input_ids);
    }

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param array $input_ids
     */
    public function updateRelationsByID($post_id, $field_name, $data)
    {
        return Packet::updateRelationsByID($post_id, $field_name, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveysRelatedPosts($program_slugs)
    {
        $posts = Packet::whereIn('feed_slug', $program_slugs)
            ->where('survey_ids', 'exists', true)
            ->where('status', 'ACTIVE')
            ->get();
        if ($posts->isEmpty()) {
            throw new NoPostAssignedException();
        } else {
            return $posts;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentsRelatedPosts($program_slugs)
    {
        $posts = Packet::whereIn('feed_slug', $program_slugs)
            ->where('assignment_ids', 'exists', true)
            ->where('status', 'ACTIVE')
            ->get();
        if ($posts->isEmpty()) {
            throw new NoPostAssignedException();
        } else {
            return $posts;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPackets($slug)
    {
        return Packet::getAllPackets($slug);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPacketsByFeedSlug($slug, $status = "ACTIVE")
    {
        $returndata = collect([]);
        if (!empty($slug)) {
            $returndata = Packet::where('feed_slug', '=', $slug)
                ->where('status', '=', $status)
                ->get();
        }
        return $returndata;
    }
}
