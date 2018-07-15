<?php
namespace App\Model\Elastic\Repository;

/**
 * interface IElasticRepository
 * @package App\Elastic\Elastic\Repository
 */
interface IElasticRepository
{
    /**
     * Add program data to elasticsearch index
     *
     * @param array $data - program details
     * @return Response
     */
    public function addProgram($data);

    /**
     * Update program data in elasticsearch index
     *
     * @param array $data - program details
     * @return Response
     */
    public function updateProgram($data);

     /**
     * Remove program from elasticsearch index
     *
     * @param  collection $program
     * @return Response
     */
    public function removeProgram($program);

    /**
     * Add package data to elasticsearch index
     *
     * @param array $data - package details
     * @return Response
     */
    public function addPackage($data);

    /**
     * Update package data in elasticsearch index
     *
     * @param array $data - package details
     * @return Response
     */
    public function updatePackage($data);

     /**
     * Remove package from elasticsearch index
     *
     * @param  int $package_id
     * @return Response
     */
    public function removePackage($package_id);

    /**
     * Update program_slug of posts and items in elasticsearch
     *
     * @param int $program_id
     * @param string $program_slug
     * @return Response
     */
    public function updateProgramSlug($program_id, $program_slug);

    /**
     * Add post data to elasticsearch index
     *
     * @param array $data - post details
     * @return Response
     */
    public function addPost($data);

    /**
     * update post data to elasticsearch index
     *
     * @param array $data - post details
     * @return Response
     */
    public function updatePost($data);

    /**
     * Remove post from elasticsearch index
     * @param  int $post_id
     * @return Response
     */
    public function removePost($post_id);

    /**
     * Update packet_slug for items in elasticsearch
     *
     * @param int $post_id
     * @param string $post_slug
     * @param boolean $sequential_access
     * @return Response
     */
    public function updatePostSlug($post_id, $post_slug, $sequential_access);

    /**
     * Add elements to elasticsearch index
     *
     * @param  array $post
     * @param  array $elements
     * @param  collection $program
     * @return Response
     */
    public function addItems($post, $elements, $program);

    /**
     * Helper method for deleteByQuery
     *
     * @param  string $field
     * @param  $value
     * @param  string/array $types
     * @return Response
     */
    public function deleteByQuery($field, $value, $types);

    /**
     * Assign users to program, posts and items in elasticsearch index
     *
     * @param collection $program
     * @return Response
     */
    public function updateProgramUsers($program);

    /**
     * Assign users to package, programs, posts and items in elasticsearch index
     *
     * @param collection $package
     * @return Response
     */
    public function updatePackageUsers($package);

    /**
     * Method to add quiz to elasticsearch index
     *
     * @param array $data
     * @return Response
     */
    public function addQuiz($data);

    /**
     * Method to update quiz data in elasticsearch index
     *
     * @param array $data
     * @return Response
     */
    public function updateQuiz($data);

    /**
     * Method to delete quiz in elasticsearch index
     *
     * @param string $_id
     * @return Response
     */
    public function removeQuiz($_id);

    /**
     * Method to assign users to quiz
     *
     * @param collection $quiz
     * @return Response
     */
    public function quizUsers($quiz);

    /**
     * Method to add event to elasticsearch index
     *
     * @param array $data
     * @return Response
     */
    public function addEvent($data);

    /**
     * Method to update event data in elasticsearch index
     *
     * @param array $data
     * @return Response
     */
    public function updateEvent($data);

    /**
     * Method to delete event in elasticsearch index
     *
     * @param string $_id
     * @return Response
     */
    public function removeEvent($_id);

    /**
     * Method to assign users to event
     *
     * @param collection $event
     * @return Response
     */
    public function eventUsers($event);

    /**
     * Method to assign users to assignment
     *
     * @param collection $assignment
     * @return Response
     */
    public function assignmentUsers($assignment);
}
