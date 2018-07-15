<?php
namespace App\Model\MyActivity\Repository;

/**
 * Interface IMyActivityRepository
 * @package  App\Model\MyActivity\Repository
 */
interface IMyActivityRepository
{
    /**
     * Method to get sections by quiz id
     *
     * @param string $quiz_name
     * @param int $quiz_id
     * @param string $return_url
     * @return App\Model\MyActivity
     */
    public function addAttemptStatus($quiz_name, $quiz_id, $return_url);

    /**
     * Method to add user activity
     *
     * @param mixed $data
     */
    public function addActivity($data);
}
