<?php
namespace App\Services\Elastic;

/**
 * interface IElasticService
 * @package App\Services\Elastic
 */
interface IElasticService
{
    /**
     * Method to index program data to elasticsearch
     *
     * @param int $program_id
     * @param boolean $slug_changed
     * @param boolean $is_new
     *
     * @return Response
     */
    public function indexProgram($program_id, $slug_changed, $is_new);

    /**
     * Method to remove program and its posts, items from elasticsearch index
     *
     * @param  int $program_id
     * @return Response
     */
    public function deleteProgram($program_id);

    /**
     * Method to index package data to elasticsearch
     *
     * @param int $package_id
     * @param boolean $slug_changed
     * @param boolean $is_new
     *
     * @return Response
     */
    public function indexPackage($package_id, $slug_changed, $is_new);

    /**
     * Method to remove package from elasticsearch index
     *
     * @param  int $package_id
     * @return Response
     */
    public function deletePackage($package_id);

    /**
     * Method to index post data to elasticsearch
     *
     * @param int $post_id
     * @param boolean $slug_changed
     * @param boolean $is_new
     *
     * @return Response
     */
    public function indexPost($post_id, $slug_changed, $is_new);

    /**
     * Method to remove post and its items from elasticsearch index
     *
     * @param  int $post_id
     * @return Response
     */
    public function deletePost($post_id);

    /**
     * Method to index post elements to elasticsearch
     *
     * @param  int $post_id
     * @return Response
     */
    public function indexItems($post_id);

    /**
     * Method to index assigned user to program, post and items
     *
     * @param int $program_id
     * @return Response
     */
    public function assignUsers($program_id);

    /**
     * Method to index users of usergroup to program
     *
     * @param int $user_group_id
     * @return Response
     */
    public function assignUserGroup($user_group_id);

    /**
     * Method to index users to package and its program
     *
     * @param int $package_id
     * @return Response
     */
    public function assignPackage($package_id);
    
    /**
     * Get assigned users for program
     *
     * @param string $column
     * @param $value
     * @return collection
     */
    public function getAssignedUsers($column, $value);

    /**
     * Method to assign package users to channels
     * when admin add channel to package
     *
     * @param  int $program_id
     * @return Response
     */
    public function assignProgram($program_id);

    /**
     * Method to index quiz
     *
     * @param int $quiz_id
     * @param boolean $is_new
     * @return Response
     */
    public function indexQuiz($quiz_id, $is_new);

    /**
     * Method to remove quiz
     *
     * @param int $quiz_id
     * @return Response
     */
    public function deleteQuiz($quiz_id);

    /**
     * Method to assign users to event
     *
     * @param int $quiz_id
     * @return Response
     */
    public function assignQuiz($quiz_id);

    /**
     * Method to index event
     *
     * @param int $event_id
     * @param boolean $is_new
     * @return Response
     */
    public function indexEvent($event_id, $is_new);

    /**
     * Method to remove quiz
     *
     * @param int $event_id
     * @return Response
     */
    public function deleteEvent($event_id);

    /**
     * Method to assign users to event
     *
     * @param int $event_id
     * @return Response
     */
    public function assignEvent($event_id);

    /**
     * Method to assign users to assignment
     *
     * @param int $assignment_id
     * @return Response
     */
    public function assignAssignment($assignment_id);
}
