<?php

namespace App\Model\Post;

/**
 * Interface IPostRepository
 *
 * @package App\Model\Post
 */
interface IPostRepository
{
    /**
     * Method to get user announcements
     *
     * @param array $post_ids
     * @param int $page
     * @param int $limit
     * @param array $columns (optional)
     * @return  array
     */
    public function getUserPosts($post_ids, $page, $limit, $columns = []);

    /**
     * Method to get all announcments assigned to user
     *
     * @param array $program_ids
     * @param array $columns (optional)
     * @return array
     */
    public function getAllPostsByProgramSlugs($program_ids, $columns = []);

    /**
     * @param array $slugs
     * @return array
     */
    public function getQuizIdByProgramSlugs($slugs);

    /**
     * Method to get specific element ids from posts
     *
     * @param collection App|Model\Post
     * @param string $type
     * @return array
     */

    public function getElementsFromPosts($posts, $type);
    /**
     * Method to get post/posts status
     *
     * @param array $post_ids
     * @return array
     */
    public function getPostsStatus($post_ids);

    /**
     * Method to get post/posts faq count
     *
     * @param  array $posts_id
     * @return array
     */
    public function getPostFaq($posts_id);

    /**
     * Method to get assessments count in posts by using slug
     *
     * @param array $slugs
     * @return int count
     */
    public function getAssessmentsCount($slugs);

    /**
     * Method to get unrear posts count
     * @param  string $slug
     * @return int
     */
    public function getNewPostsCount($slug);

    /**
     * Method to get posts by program slug
     *
     * @param int $page
     * @param int $limit
     * @param string $slug
     *
     * @return array
     */
    public function postsBySlug($page, $limit, $slug);

    /**
     * [getPacketsUsingIds get packets details by packet ids]
     * @param  [array] $packet_ids [array of unique packet ids]
     * @return [array]             [packet details array]
     */
    public function getPacketsUsingIds($packet_ids);

    /**
     * countNewPosts
     * @param  array  $program_slugs
     * @param  array  $date
     * @return integer
     */
    public function countNewPosts(array $program_slugs, array $date);

    /**
     * getPacketsAssessement
     * @param  array $slugs
     * @return array
     */
    public function getPacketsAssessement($slugs);

    /**
     * Get no of posts assigned to programs
     *
     * @param string $feed_slug
     * @return int
     */
    public function getActivePostsCount($feed_slug);
    /**
     * getFeedSlugsByPacketIds
     * @param  array $packet_id
     * @return array
     */
    public function getFeedSlugsByPacketIds($packet_id);

    /**
     * updatePacketElementRelations
     * @param  array  $packet_details
     * @param  array  $items
     * @param  integer $program_id
     * @return void
     */
    public function updatePacketElementRelations(array $packet_details, array $items, $program_id);


    /**
     * Method to get post details
     *
     * @param $field
     * @param $value
     * @return App|Model|Packet
     */
    public function getPostByAttribute($field, $value);

    /**
     * Method to get post details
     *
     * @param array $sub_program_slugs
     * @param string $order_by
     * @return array $packet
     */
    public function getPacketsUsingSlug($sub_program_slugs, $order_by);

    /**
     * getNewPosts
     * @param  array  $program_ids
     * @param  array  $date
     * @param  integer $start
     * @param  integer $limit
     * @return collection
     */
    public function getNewPosts(array $program_ids, array $date, $start, $limit);

    /**
     * getNewPostCount
     * @param array $program_ids
     * @param array $date
     * @return integer
     */
    public function getNewPostCount(array $program_ids, array $date);
    /**
     * @param  array $post_ids
     * @return integer
     */
    public function countActivePosts($post_ids);

    /**
     * @param  array $post_ids
     * @return integer
     */
    public function countInActivePosts($post_ids);

    /**
     * @param  array $post_id
     * @return integer
     */
    public function getPacketByID($post_id);

    /**
     * @param  int $post_id
     * @param string $field_name
     */
    public function IncrementField($post_id, $field_name);

    /**
     * @param  int $post_id
     * @param string $field_name
     */
    public function DecrementField($post_id, $field_name);

    /**
     * @param  int $post_id
     * @param $field_name
     */
    public function getPostByID($id, $field_name);

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param int|array $input_ids
     */
    public function pushRelations($post_id, $field_name, $input_ids);

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param int|array $input_ids
     */
    public function pullRelations($post_id, $field_name, $input_ids);

    /**
     * @param  int $post_id
     * @param string $field_name
     * @param array $data
     */
    public function updateRelationsByID($post_id, $field_name, $data);

    /**
     * @param array $program_slugs
     * @return collection
     */
    public function getSurveysRelatedPosts($program_slugs);

    /**
     * @param array $program_slugs
     * @return collection
     */
    public function getAssignmentsRelatedPosts($program_slugs);

    /**
     * @param string $slug
     * @return array
     */
    public function getAllPackets($slug);

    /**
     * @param string $slug
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPacketsByFeedSlug($slug, $status);
}
