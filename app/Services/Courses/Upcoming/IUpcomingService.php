<?php
namespace App\Services\Courses\Upcoming;

interface IUpcomingService
{
    public function getUpcomingCourses($start, $limit);

    public function getUpcomingCount();

    public function getCourseCount($program_ids, $type = 'all', $search = null);

    public function getInsertedCourses();

    public function getCourseWithTypeAndPagination($program_ids, $type = 'channel', $start, $limit, $order_by = ['created_at' => 'desc'], $search = null);

    public function getInsertCourses($ids = []);

    public function getDeleteCourse($program_id);

}
