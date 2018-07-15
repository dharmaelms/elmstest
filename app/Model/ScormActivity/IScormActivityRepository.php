<?php

namespace App\Model\ScormActivity;

interface IScormActivityRepository
{
    /**
     * create Scorm activities
     * @param  array $data
     * @return boolean
     */
    public function create(array $data);

    /**
     * update ScormActivities
     * @param  integer $user_id
     * @param  integer $scorm_id
     * @param  array $data
     * @return boolean
     */
    public function update($user_id, $scorm_id, $packet_id, array $data);

    /**
     * findScormByUser
     * @param  integer $user_id
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function findScormByUser($user_id, $start = null, $limit = null);

    /**
     * getAll
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAll();

    /**
     * getScormDetails
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getScormDetails($user_id, $packet_id, $scorm_id);

    /**
     * findScormIncompleteStatusByScormId
     * @param  integer $scorm_id
     * @return count
     */
    public function findScormIncompleteStatusByScormId($scorm_id);

    /**
     * findScormPassedStatusByScormId
     * @param  integer $scorm_id
     * @return count
     */
    public function findScormPassedStatusByScormId($scorm_id);

    /**
     * findScormTimeSpentByScormId
     * @param  integer $scorm_id
     * @return array
     */
    public function findScormTimeSpentByScormId($scorm_id);

    /**
     * findScormScoreByScormId
     * @param  integer $scorm_id
     * @return array
     */
    public function findScormScoreByScormId($scorm_id);

}
