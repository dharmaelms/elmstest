<?php namespace App\Model\Playlyfe\Repository;

use App\Exceptions\Playlyfe\PlayerNotFoundException;

/**
 * Interface IPlaylyfeRepository
 * @package App\Model\Playlyfe\Repository
 */
interface IPlaylyfeRepository
{

    /**
     * @param $access_token
     * @return mixed
     */
    public static function storeAPIAccessToken($access_token);

    /**
     * @return mixed
     */
    public static function getAPIAccessToken();

    /**
     * @param $data
     * @return mixed
     */
    public function createPlayer($data);

    /**
     * getPlayer
     *
     * Using unique id of the user gets the playlyfe player id.
     *
     * @param  Integer $userID unique id of user
     * @return Object $player
     *
     * @throws PlayerNotFoundException[If playlyfe user details are not found]
     */
    public function getPlayer($userID);


    /**
     * @param $pattern
     * @param $start
     * @param $length
     * @return mixed
     */
    public function getPlayerByPattern($pattern, $start, $length);

    /**
     * @param $pattern
     * @return mixed
     */
    public function getLeaderboardByPatternCount($pattern);

    /**
     * @param $data
     * @return mixed
     */
    public function createActionSummary($data);

    /**
     * @param $data
     * @return mixed
     */
    public function log($data);

    /**
     *  getPlaylyfePlayerProfile
     *
     *  gets the playlyfe player profile using playlyfe API.
     *
     * @param  integer $playerID playlyfe user id which is unique username of the application.
     *
     * @return array $playlyfePlayerProfile that contains points, level and badges for player profile.
     */
    public function getPlaylyfePlayerProfile($playerID);

    /**
     * Gets player rank for player profile
     *
     * @param  integer $playerID playlyfe player id which is unique username of the application.
     *
     * @return array $playerRank player rank in playlyfe leaderboard.
     */
    public function getPlayerRankWithNextScore($playerID);

    /**
     * Gets player rank for player profile
     *
     * @param  integer $playerID playlyfe player id which is unique username of the application.
     *
     * @return array $playerRank player rank in playlyfe leaderboard.
     */
    public function getPlayerRankLastWeekWithNextScore($playerID);

    /**
     * @param $playerID
     * @param $filter
     * @param int $skip
     * @param int $take
     * @return mixed
     */
    public function getPlayerActivity($playerID, $filter, $skip = 0, $take = 3);
}
