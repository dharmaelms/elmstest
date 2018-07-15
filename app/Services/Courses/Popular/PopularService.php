<?php

namespace App\Services\Courses\Popular;

use App\Model\Courses\Popular\Repository\IPopularRepository;

/**
 * Class PopularService
 * @package App\Services\Courses\Popular
 */
class PopularService implements IPopularService
{
    /**
     * @var IPopularRepository
     */
    private $popular_repository;

    /**
     * PopularService constructor.
     * @param IPopularRepository $popular_repository
     */
    public function __construct(IPopularRepository $popular_repository)
    {
        $this->popular_repository = $popular_repository;
    }

    /**
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getPopularCourses($start, $limit)
    {
        $program_ids = $this->popular_repository->getInsertedCourses();
        return $this->popular_repository->getManualCourses($program_ids, $start, $limit);
    }

    /**
     * @return mixed
     */
    public function getPopularCount()
    {
        return $this->popular_repository->getManualCount();
    }

    /**
     * @return mixed
     */
    public function getInsertedCourses()
    {
        return $this->popular_repository->getInsertedCourses();
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function getInsertCourses($ids = [])
    {
        return $this->popular_repository->getInsertCourses($ids);
    }

    /**
     * @param $program_id
     * @return mixed
     */
    public function getDeleteCourse($program_id)
    {
        return $this->popular_repository->getDeleteCourse($program_id);
    }
}
