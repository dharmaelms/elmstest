<?php

namespace App\Services\Courses\Upcoming;

use App\Model\Courses\Upcoming\Repository\IUpcomingRepository;

/**
 * Class UpcomingService
 * @package App\Services\Courses\Upcoming
 */
class UpcomingService implements IUpcomingService
{
    /**
     * @var IUpcomingRepository
     */
    private $upcoming_repository;

    /**
     * UpcomingService constructor.
     * @param IUpcomingRepository $upcoming_repository
     */
    public function __construct(IUpcomingRepository $upcoming_repository)
    {
        $this->upcoming_repository = $upcoming_repository;
    }

    /**
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function getUpcomingCourses($start, $limit)
    {
        $site_settings = $this->upcoming_repository->getSiteSettings();
        if ($site_settings['configuration'] == 'manual') {
            $program_ids = $this->upcoming_repository->getInsertedCourses();
            return $this->upcoming_repository->getManualCourses($program_ids, $start, $limit);
        } else {
            return $this->upcoming_repository->getAutomatedCourses($site_settings, $start, $limit);
        }
    }

    /**
     * @return mixed
     */
    public function getUpcomingCount()
    {
        $site_settings = $this->upcoming_repository->getSiteSettings();
        if ($site_settings['configuration'] == 'manual') {
            return $this->upcoming_repository->getManualCount();
        } else {
            return $this->upcoming_repository->getAutomatedCount($site_settings);
        }
    }

    /**
     * @param $program_ids
     * @param string $type
     * @param null $search
     * @return mixed
     */
    public function getCourseCount($program_ids, $type = 'all', $search = null)
    {
        return $this->upcoming_repository->getCourseCount($program_ids, $type, $search);
    }

    /**
     * @return mixed
     */
    public function getInsertedCourses()
    {
        return $this->upcoming_repository->getInsertedCourses();
    }

    /**
     * @param $program_ids
     * @param string $type
     * @param $start
     * @param $limit
     * @param array $order_by
     * @param null $search
     * @return mixed
     */
    public function getCourseWithTypeAndPagination($program_ids, $type = 'channel', $start, $limit, $order_by = ['created_at' => 'desc'], $search = null)
    {
        return $this->upcoming_repository->getCourseWithTypeAndPagination($program_ids, $type, $start, $limit, $order_by, $search);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function getInsertCourses($ids = [])
    {
        return $this->upcoming_repository->getInsertCourses($ids);
    }

    /**
     * @param $program_id
     * @return mixed
     */
    public function getDeleteCourse($program_id)
    {
        return $this->upcoming_repository->getDeleteCourse($program_id);
    }
}
