<?php

namespace App\Services\Post;

/**
 * Interface IPostService
 *
 * @package App\Services\Post
 */
interface IPostService
{

    /**
     * Helper to get posts with pagination
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getPosts($page, $limit);

    /**
     * Helper to get all posts for an user
     * @return array
     */
    public function getAllPosts();

    /**
     * Using this method system will return all the programs that are assigned
     * to this specific user
     */
    public function getAllProgramsAssignedToUser();

    /**
     * Helper to get assessments in posts by using program slugs
     *
     * @param string $slugs
     * @return int
     */
    public function getAssessmentsCountInPostsBySlugs($slugs);

    /**
     * Helper to get assessments in posts by using program slugs
     *
     * @param array $slugs
     * @return int
     */
    public function getNewPostsBySlug($slugs);

    /**
     * Helper to get posts by program slug
     * @param int $page
     * @param int $limit
     * @param string $slug program slug
     */
    public function getPostsBySlug($page, $limit, $slug);

    /**
     * Helper to get posts for all programs
     *
     * @param int $page
     * @param int $limit
     * @param array
     */
    public function getPostsForAll($page, $limit);

    /**
     * Helper to count elements in posts by type
     *
     * @param array $elements elements array
     * @param string $type type to count
     */
    public function getElementsCountByType($elements, $type);

    /**
     * Helper to get post completion
     *
     * @param string $program_slug
     * @param string $post_id
     * @return integer  completion percentage
     */
    public function getPostCompletion($program_slug, $post_id);

    /**
     * Helper to get all posts by program slug
     *
     * @param string $slug
     */
    public function postDetailsBySlug($slug);

    /**
     * Helper to get all posts by program slug
     *
     * @param string $slug
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function getPostsDataBySlug($page, $limit, $slug);

    /**
     * [getPacketsUsingIds get packets details by packet ids]
     * @param  [array] $packet_ids [array of unique packet ids]
     * @return [array]             [packet details array]
     */
    public function getPacketsUsingIds($packet_ids);

    /**
     * countNewPosts
     * @param  array  $program_ids
     * @param  array  $date
     * @return integer
     */
    public function countNewPosts(array $program_ids, array $date);

    /**
     * getPacketsAssessement
     * @param  array $slugs
     * @return array
     */
    public function getPacketsAssessement($slugs);

    /**
     * getFeedSlugsByPacketIds
     * @param  array $packet_id
     * @return array
     */
    public function getFeedSlugsByPacketIds($packet_id);

    /**
     * updatePacketElementRelations
     * @param  array  $packet_details
     * @return boolean
     */
    public function updatePacketElementRelations(array $packet_details);

    /**
     * getPacketsUsingSlug
     * @param  array  $sub_program_slugs
     * @return strin $order_by
     */
    public function getPacketsUsingSlug($sub_program_slugs, $order_by);

    /**
     * getViewedElementsInPacket
     * @param  integer $user_id
     * @param  integer $channel_id
     * @param  integer $post_id
     * @return array
     */
    public function getViewedElementsInPacket($user_id, $channel_id, $post_id);

    /**
     * getPostsBySlugLimitedColumn
     * @param  array  $slugs
     * @param  array  $columns
     * @return collection
     */
    public function getPostsBySlugLimitedColumn($slugs, $columns);

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
    * @param array date
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
