<?php

namespace App\Services\ScormActivity;

use App\Libraries\Helpers;
use App\Model\ScormActivity\IScormActivityRepository;
use App\Services\DAMS\IDAMsService;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\UserGroup\IUserGroupService;
use App\Model\Dam;
use App\Model\Packet;

class ScormActivityService implements IScormActivityService
{
    /**
     * @var IScormActivityRepository
     */
    private $scorm_repository;

    /**
     * @var IDAMsService
     */
    private $dams_service;

    /**
     * @var IProgramService
     */
    private $program_service;

    /**
     * @var usergroup_service
     */
    private $usergroup_service;

    /**
     * @var packet_service
     */
    private $packet_service;

    /**
     * ScormActivityService constructor.
     *
     * @param IScormActivityRepository $scorm_activity_repository
     */

    public function __construct(
        IScormActivityRepository $scorm_repository,
        IDAMsService $dams_service,
        IProgramService $program_service,
        IUserGroupService $usergroup_service,
        IPostService $packet_service
    ) {
    
        $this->scorm_repository = $scorm_repository;
        $this->dams_service = $dams_service;
        $this->program_service = $program_service;
        $this->usergroup_service = $usergroup_service;
        $this->packet_service = $packet_service;
    }

    public function getScormDetailsForAdmin($assigned_items, $no_set, $limit)
    {
        $start = $no_set * $limit;
        $scorm = $this->dams_service->getTypeScormRecords($assigned_items, 'scorm', $start, $limit)->toArray();
        $scorm_detail = $scorm_data = [];
        $scorm_packet_id = $feed_slug = $feed_relations =   $scorm_details =  [];
        foreach ($scorm as $value) {
            //get total number of users

            $scorm_id = array_get($value, 'id');
            $scorm_packet_id[$scorm_id] = array_get($value, 'relations.dams_packet_rel');
            //Scorm related program slugs
            $feed_slug = $this->packet_service->getFeedSlugsByPacketIds($scorm_packet_id[$scorm_id]);
            $feed_relations = $this->program_service->getUsergroupUserRelationBySlug($feed_slug);
            $active_user_feed_rel = $feed_relations["active_user_feed_rel"];
            $active_user_feed_rel = $this->arrayFlatten($feed_relations["active_user_feed_rel"]);
            $active_usergroup_feed_rel = $this->arrayFlatten($feed_relations["active_usergroup_feed_rel"]);
            $active_user_usergroup_rel = $this->usergroup_service->getUsersByUserGroupIds($active_usergroup_feed_rel);
            $indirect_users = $this->arrayFlatten($active_user_usergroup_rel);
            $no_of_users = array_unique(array_merge($active_user_feed_rel, $indirect_users));
            $total_no_of_users = count($no_of_users);

            //get scorm names
            $scorm_names = array_get($value, 'name');

            if (count($no_of_users) > 0) {
                //incomplete status
                $status_incomplete = $this->scorm_repository->findScormIncompleteStatusByScormId($scorm_id);
                $incomplete_status = round((($status_incomplete/(count($no_of_users))) * 100), 2);

                //completed status
                $status_passed = $this->scorm_repository->findScormPassedStatusByScormId($scorm_id);
                $passed_status = round((($status_passed/(count($no_of_users))) * 100), 2);

                //Not started status
                $not_started = round((((count($no_of_users)-($status_incomplete + $status_passed))/(count($no_of_users))) * 100), 2);

                //Total time spent
                $scorm_time = $this->scorm_repository->findScormTimeSpentByScormId($scorm_id);
                if (!is_null($scorm_time)) {
                    $scorm_time_spent = Helpers::secondsToString((int)$scorm_time); //floor($scorm_time / 3600).':'.(floor($scorm_time/60)).':'.$scorm_time % 60;
                } else {
                    $scorm_time_spent = "00:00:00";
                }

                //score
                $scorm_scores = $this->scorm_repository->findScormScoreByScormId($scorm_id);
                if (!is_null($scorm_scores)) {
                    $scorm_score = $scorm_scores;
                } else {
                    $scorm_score = 0;
                }
            } else {
                $passed_status = $incomplete_status = $not_started = $scorm_score = 0;
                $scorm_time_spent = "00:00:00";
            }

            $scorm_detail['scorm_names'] = $scorm_names;
            $scorm_detail['completed'] = $passed_status;
            $scorm_detail['inprogress'] = $incomplete_status;
            $scorm_detail['not_started'] = $not_started;
            $scorm_detail['avg_time_spent'] = $scorm_time_spent;
            $scorm_detail['avg_score'] = $scorm_score;
            $scorm_detail['number_of_users'] = $total_no_of_users;
            $scorm_data[] = $scorm_detail;

        }
        return $scorm_data;
    }

    /**
    * @param $active_usergroup_feed_rel
    * @return array
    */
    public function arrayFlatten(array $active_usergroup_feed_rel) {
        $flatten = array();
        array_walk_recursive($active_usergroup_feed_rel, function($value) use(&$flatten) {
            $flatten[] = $value;
        });

        return $flatten;
    }


    /**
    * @param $user_id
    * @return array
    */
    public function getScormDetailsForPortal($user_id, $no_set = null, $limit = null)
    {
        $start = $no_set * (int)$limit;
        $scorm_details = $this->scorm_repository->findScormByUser($user_id, $start, $limit);
        $scorm_detail = $scorm = [];
        foreach ($scorm_details as $value) {
            $scorm_detail['scorm_name'] = array_get($value, 'scorm_name');
            $scorm_detail['scorm_status'] = ( (array_get($value, 'lesson_status') == 'passed') || (array_get($value, 'lesson_status') == 'completed') ) ? trans('reports.completed') : trans('reports.in_progress');
            $scorm_detail['total_time_spent'] = Helpers::secondsToString((int)array_get($value, 'total_time_spent'));//floor(array_get($value, 'total_time_spent') / 3600).':'.(floor(array_get($value, 'total_time_spent')/60)).':'.(array_get($value, 'total_time_spent') % 60);
            $scorm_detail['score'] = (!empty(array_get($value, 'score_raw')) ) ? array_get($value, 'score_raw') : 'NA';
            $scorm[] = $scorm_detail;
        }
        return $scorm;
    }

    /**
     * create Scorm activities
     * @param  array $data
     * @return boolean
     */
    public function create(array $data)
    {
       return $this->scorm_repository->create($data); 
    }

    /**
     * update ScormActivities
     * @param  integer $user_id
     * @param  integer $scorm_id
     * @param  array $data
     * @return boolean
     */
    public function update($user_id, $scorm_id, $packet_id, array $data)
    {
        return $this->scorm_repository->update($user_id, $scorm_id, $packet_id, $data); 
    }

    /**
     * findScormByUser
     * @param  integer $user_id
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function findScormByUser($user_id)
    {
        return $this->scorm_repository->findScormByUser($user_id);
    }

    /**
     * getAll
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return $this->scorm_repository->getAll();
    }

    /**
     * getScormDetails
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getScormDetails($user_id, $packet_id, $scorm_id)
    {
        return $this->scorm_repository->getScormDetails($user_id, $packet_id, $scorm_id);
    }

    /**
     * findScormIncompleteStatusByScormId
     * @param  integer $scorm_id
     * @return count
     */
    public function findScormIncompleteStatusByScormId($scorm_id)
    {
        return $this->scorm_repository->findScormIncompleteStatusByScormId($scorm_id);
    }

    /**
     * findScormPassedStatusByScormId
     * @param  integer $scorm_id
     * @return count
     */
    public function findScormPassedStatusByScormId($scorm_id)
    {
        return $this->scorm_repository->findScormPassedStatusByScormId($scorm_id);
    }

    /**
     * findScormTimeSpentByScormId
     * @param  integer $scorm_id
     * @return array
     */
    public function findScormTimeSpentByScormId($scorm_id)
    {
        return $this->scorm_repository->findScormTimeSpentByScormId($scorm_id);
    }

    /**
     * findScormScoreByScormId
     * @param  integer $scorm_id
     * @return array
     */
    public function findScormScoreByScormId($scorm_id)
    {
        return $this->scorm_repository->findScormScoreByScormId($scorm_id);
    }

}
