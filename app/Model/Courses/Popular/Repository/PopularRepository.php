<?php

namespace App\Model\Courses\Popular\Repository;

use App\Model\Courses\Popular\Entity\PopularCourses;
use App\Model\Program;
use Auth;

/**
 * Class PopularRepository
 * @package App\Model\Courses\Popular\Repository
 */
class PopularRepository implements IPopularRepository
{
    /**
     * {@inheritdoc}
     */
    public function getManualCourses($program_ids, $start, $limit)
    {
        $program_ids = array_map('intval', $program_ids);
        return Program::where('status', '!=', 'DELETED')
            ->whereIn('program_id', $program_ids)
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
        $program_ids = $this->getInsertedCourses();
        return Program::where('status', '!=', 'DELETED')
            ->whereIn('program_id', $program_ids)
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getInsertedCourses()
    {
        return PopularCourses::lists('program_id')->all();
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
            PopularCourses::insert($array);
            Program::where('program_id', '=', (int)$program['program_id'])->update(['is_popular' => "yes"]);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteCourse($program_id)
    {
        Program::where('program_id', '=', (int)$program_id)->unset('is_popular');
        return PopularCourses::where('program_id', '=', (int)$program_id)->delete();
    }
}
