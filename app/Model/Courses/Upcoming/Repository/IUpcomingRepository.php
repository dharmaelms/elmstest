<?php
namespace App\Model\Courses\Upcoming\Repository;

/**
 * Interface IUpcomingRepository
 * @package App\Model\Courses\Upcoming\Repository
 */
interface IUpcomingRepository
{
    /**
     * @param $upcoming_courses
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getAutomatedCourses($upcoming_courses, $start, $limit);

    /**
     * @param $upcoming_courses
     * @return mixed
     */
    public function getAutomatedCount($upcoming_courses);

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
     * @return mixed
     */
    public function getSiteSettings();

    /**
     * @param $program_ids
     * @param string $type
     * @param null $search
     * @return mixed
     */
    public function getCourseCount($program_ids, $type = 'all', $search = null);

    /**
     * @param $program_ids
     * @param string $type
     * @param $start
     * @param $limit
     * @param $orderby
     * @param $search
     * @return mixed
     */
    public function getCourseWithTypeAndPagination($program_ids, $type = 'channel', $start, $limit, $orderby, $search);

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
