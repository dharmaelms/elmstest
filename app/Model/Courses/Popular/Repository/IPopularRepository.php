<?php
namespace App\Model\Courses\Popular\Repository;

/**
 * Interface IPopularRepository
 * @package App\Model\Courses\Popular\Repository
 */
interface IPopularRepository
{
    /**
     * @param $program_ids
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getManualCourses($program_ids, $start, $limit);

    /**
     * @return mixed
     */
    public function getManualCount();

    /**
     * @return mixed
     */
    public function getInsertedCourses();

    /**
     * @param $program_ids
     * @return mixed
     */
    public function getInsertCourses($program_ids);

    /**
     * @param $program_id
     * @return mixed
     */
    public function getDeleteCourse($program_id);

}
