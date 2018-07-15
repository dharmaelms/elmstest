<?php
namespace App\Services\Courses\Popular;

interface IPopularService
{
    public function getPopularCourses($start, $limit);

    public function getPopularCount();

    public function getInsertedCourses();

    public function getInsertCourses($ids = []);

    public function getDeleteCourse($program_id);
}
