<?php

namespace App\Model\Courses\Upcoming\Repository;

use App\Model\Courses\Upcoming\Entity\UpcomingCourses;
use App\Model\Program;
use App\Model\SiteSetting;
use Auth;

/**
 * Class UpcomingRepository
 * @package App\Model\Courses\Upcoming\Repository
 */
class UpcomingRepository implements IUpcomingRepository
{
    /**
     * {@inheritdoc}
     */
    public function getAutomatedCourses($upcoming_courses, $start, $limit)
    {
        $days = $upcoming_courses['duration_in_days'];
        $days = intval($days);

        $records = intval($upcoming_courses['records_per_course']);
        if ($start == 0 && $records < $limit) {
            $limit = $records;
        } elseif ($start > 0 && $records < ($start + 1) * $limit) {
            $limit = $records - ($start * $limit);
        }

        $type = $upcoming_courses['type'];
        $date = strtotime(date('Y-m-d', strtotime("+" . $days . " days")));
        $now = time();

        return Program::where('status', '=', 'ACTIVE')
            ->whereBetween('program_display_startdate', [$now, $date])
            ->CourseType($type)
            ->where('program_sellability', '=', 'yes')
            ->where('program_visibility', '=', 'yes')
            ->orderby('program_display_startdate', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getAutomatedCount($upcoming_courses)
    {
        $days = $upcoming_courses['duration_in_days'];
        $days = intval($days);

        $records = intval($upcoming_courses['records_per_course']);
        $type = $upcoming_courses['type'];
        $date = strtotime(date('Y-m-d', strtotime("+" . $days . " days")));
        $now = time();

        return Program::where('status', '=', 'ACTIVE')
            ->whereBetween('program_display_startdate', [$now, $date])
            ->CourseType($type)
            ->where('program_sellability', '=', 'yes')
            ->where('program_visibility', '=', 'yes')
            ->skip(0)
            ->take($records)
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getManualCourses($program_ids, $start, $limit)
    {
        $program_ids = array_map('intval', $program_ids);
        return Program::whereIn('program_id', $program_ids)
            ->orderby('program_display_startdate', 'asc')
            ->skip((int)$start)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getManualCount()
    {
        return UpcomingCourses::count();
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertedCourses()
    {
        return UpcomingCourses::lists('program_id')->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getSiteSettings()
    {
        $res = SiteSetting::module('Homepage');
        return $res['setting']['UpcomingCourses'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCourseCount($program_ids, $type = 'all', $search = null)
    {
        $now = time();
        return Program::ExceptProgramids($program_ids)
            ->GetType($type)
            ->where('program_sellability', '=', 'yes')
            ->where('program_visibility', '=', 'yes')
            ->FeedSearch($search)
            ->Where('program_enddate', '>=', $now)
            ->where('status', '=', 'ACTIVE')
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getCourseWithTypeAndPagination($program_ids, $type = 'channel', $start, $limit, $orderby, $search)
    {
        $now = time();
        return Program::ExceptProgramids($program_ids)
            ->GetType($type)
            ->where('program_sellability', '=', 'yes')
            ->where('program_visibility', '=', 'yes')
            ->FeedSearch($search)
            ->Where('program_enddate', '>=', $now)
            ->where('status', '=', 'ACTIVE')
            ->GetOrderBy($orderby)
            ->skip((int)$start)
            ->take((int)$limit)
            ->get()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertCourses($program_ids)
    {
        $program_ids = array_map('intval', $program_ids);
        $programs = Program::getSelectedPrograms($program_ids);
        foreach ($programs as $program) {
            if (isset($program['program_sub_type']) && !empty($program['program_sub_type'])) {
                $program_sub_type = $program['program_sub_type'];
            } else {
                $program_sub_type = '';
            }
            $array = [
                'program_id' => (int)$program['program_id'],
                'program_type' => $program['program_type'],
                'program_sub_type' => $program_sub_type,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            UpcomingCourses::insert($array);
            Program::where('program_id', '=', (int)$program['program_id'])->update(['is_upcoming' => "yes"]);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteCourse($program_id)
    {
        Program::where('program_id', '=', (int)$program_id)->unset('is_upcoming');
        return UpcomingCourses::where('program_id', '=', (int)$program_id)->delete();
    }
}
