<?php

namespace App\Model\ScormActivity;

use App\Model\ScormActivity;

class ScormActivityRepository implements IScormActivityRepository
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        ScormActivity::insert($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update($user_id, $scorm_id, $packet_id, array $data)
    {
        $scormActivity =  ScormActivity::where("user_id", $user_id)
                        ->where("packet_id", $packet_id)
                        ->where("scorm_id", $scorm_id)
                        ->update(['user_full_name' => $data["user_full_name"],
                            'total_time_spent' => $data["total_time_spent"],
                            'entry' => $data["entry"],
                            'lesson_location' => $data["lesson_location"],
                            'lesson_status' => $data["lesson_status"],
                            'score_raw' => array_get($data, 'score_raw', ''),
                            'suspend_data' => $data["suspend_data"],
                            ]);
        return $scormActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function findScormByUser($user_id, $start = null, $limit = null)
    {
          $scormActivity =  ScormActivity::where("user_id", $user_id)
                ->skip((int)$start)
                ->take((int)$limit)
                ->get()->toArray();
        return $scormActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $scormActivity =  ScormActivity::get()->toArray();
        return $scormActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function getScormDetails($user_id, $packet_id, $scorm_id)
    {
        $scormActivity =  ScormActivity::where("user_id", $user_id)
                ->where("packet_id", $packet_id)
                ->where("scorm_id", $scorm_id)
                ->get()->toArray();
        return $scormActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function findScormIncompleteStatusByScormId($scorm_id)
    {
        $scormActivity =  ScormActivity::where("scorm_id", $scorm_id)
                ->where('lesson_status', 'incomplete')
                ->count();
        return $scormActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function findScormPassedStatusByScormId($scorm_id)
    {
        $scormActivity =  ScormActivity::where("scorm_id", $scorm_id)
                ->whereIn('lesson_status', ['passed', 'completed'])
                ->count();
        return $scormActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function findScormTimeSpentByScormId($scorm_id)
    {
        $scormActivity =  ScormActivity::where("scorm_id", $scorm_id)
                        ->avg('total_time_spent');
        return $scormActivity;
    }

    /**
     * {@inheritdoc}
     */
    public function findScormScoreByScormId($scorm_id)
    {
        $scormActivity =  ScormActivity::where("scorm_id", $scorm_id)
                        ->avg('score_raw');
        return $scormActivity;
    }
}
