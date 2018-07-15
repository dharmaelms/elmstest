<?php namespace App\Services\Playlyfe;

use App\Exceptions\Playlyfe\PlayerNotFoundException;
use App\Model\Playlyfe\Entity\PlaylyfeUser;
use App\Model\Playlyfe\Repository\IPlaylyfeRepository;
use App\Model\User;
use Exception;
use Playlyfe\Sdk\PlaylyfeException;

/**
 * Class PlaylyfeService
 * @package App\Services\Playlyfe
 */
class PlaylyfeService implements IPlaylyfeService
{
    /**
     * @var IPlaylyfeRepository
     */
    private $playlyfe_repository;

    /**
     * @var
     */
    private $enabled;

    /**
     * @var
     */
    private $playlyfe;

    /**
     * PlaylyfeService constructor.
     * @param IPlaylyfeRepository $playlyfe_repository
     * @param bool $enabled
     */
    public function __construct(IPlaylyfeRepository $playlyfe_repository, $enabled = false)
    {
        $this->playlyfe_repository = $playlyfe_repository;

        $this->enabled = $playlyfe_repository->playlyfeEnabledFlag;

        if ($this->enabled) {
            $this->playlyfe = $playlyfe_repository->playlyfe;
        }
    }

    /**
     * @return mixed
     */
    public function isPlaylyfeEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param $event
     */
    public function processEvent($event)
    {
        if ($this->enabled) {
            switch ($event["type"]) {
                case "create-user":
                    $this->createUser($event["data"]);
                    break;

                case "action":
                    $this->triggerAction($event["data"]);

                    break;
            }
        }
    }

    /**
     * @param $data
     * @return mixed
     */
    public function createUser($data)
    {
        try {
            $response = $this->playlyfe->post("/admin/players", [], ["id" => $data["player_id"], "alias" => $data["player_alias"]]);
            return $this->playlyfe_repository->createPlayer([
                "user_id" => $data["user_id"],
                "player_id" => $response["id"],
                "player_alias" => $response["alias"],
                "status" => "active"
            ]);
        } catch (PlaylyfeException $e) {
            $this->playlyfe_repository->log([
                "user_id" => $data["user_id"],
                "event_type" => "create-user",
                "success" => false,
                "request" => [
                    "uri" => "/admin/players/",
                    "body" => $data
                ],
                "response" => [
                    "error_code" => $e->name,
                    "error_description" => $e->message
                ]
            ]);
        }
    }

    /**
     * @param $data
     */
    protected function triggerAction($data)
    {
        $userID = $data["user_id"];
        $actionID = $data["action_id"];
        $actionURI = "/runtime/actions/{$actionID}/play";
        $query = ["query" => false];
        $body = [];
        unset($data["user_id"], $data["action_id"]);
        if (!empty($data)) {
            $body["variables"] = $data;
        }
        $logData = [
            "user_id" => $userID,
            "event_type" => "action",
            "request" => [
                "uri" => $actionURI,
                "query" => $query,
                "body" => $body
            ]
        ];
        try {
            $player = $this->playlyfe_repository->getPlayer($userID);
            $query["player_id"] = $player->player_id;
            $apiResponse = $this->playlyfe->post($actionURI, $query, $body);
            $response = [];
            $response["user_id"] = $userID;
            $response["player_id"] = $player->player_id;
            $response["action_id"] = $actionID;
            $response = array_merge($response, $this->parseAPIResponse($apiResponse));
            if (isset($response["action_result"]) && !empty($response["action_result"])) {
                $this->playlyfe_repository->createActionSummary($response);
            }
        } catch (PlaylyfeException $playlyfeException) {
            $logData["success"] = false;
            $logData["response"] = [
                "error_code" => $playlyfeException->name,
                "error_description" => $playlyfeException->message
            ];
            $this->playlyfe_repository->log($logData);
        } catch (PlayerNotFoundException $playerNotFoundException) {
            $logData["success"] = false;
            $logData["response"] = [
                "error_code" => $playerNotFoundException->getCode(),
                "error_description" => $playerNotFoundException->getMessage()
            ];
            $this->playlyfe_repository->log($logData);
        } catch (Exception $exception) {
            $logData["success"] = false;
            $logData["response"] = [
                "error_code" => $exception->getCode(),
                "error_track_info" => "File path : {$exception->getFile()} : Line number - {$exception->getLine()}",
                "error_description" => $exception->getMessage()
            ];
            $this->playlyfe_repository->log($logData);
        }
    }

    /**
     * @param $response
     * @return array
     */
    protected function parseAPIResponse($response)
    {
        $tmpData1 = [];
        $tmpData1["action_data"] = [];
        $tmpData1["action_result"] = [];

        $tmpData2 = [];
        if (isset($response["events"]["global"])) {
            $tmpData2 = $response["events"]["global"];
        }
        if (isset($response["events"]["local"])) {
            $tmpData2 = array_merge($tmpData2, $response["events"]["local"]);
        }

        if (isset($tmpData2[0]["action"]["vars"])) {
            $tmpData1["action_data"] = $tmpData2[0]["action"]["vars"];
        }
        if (isset($tmpData2[0]["changes"])) {
            $tmpData1["action_result"] = $tmpData2[0]["changes"];
        }

        return $tmpData1;
    }

    /**
     * @param $size
     * @param $metric_id
     * @param $item
     * @return string
     */
    public function getImage($size, $metric_id, $item)
    {
        $item_txt = '';
        if ($item != null) {
            $item_txt .= '&item=' . $item;
        }
        return '&nbsp;&nbsp;<img src="/pl/image?size=' . $size . '&metric_id=' . $metric_id . $item_txt . '"></img>&nbsp;&nbsp;';
    }

    /**
     * @param $change
     * @param $size
     * @return string
     */
    public function renderChanges($change, $size)
    {
        $html = '<div class="activity-list-item-score">';
        if ($change["metric"]["type"] === "point") {
            $html = $html . '<img class="activity-badge" src="/pl/image?size=small&metric_id=' . $change['metric']['id'] . '"></img>';
            $html = $html . "+" . (intval($change["delta"]["new"]) - intval($change["delta"]["old"])) . ' ' . $change["metric"]["name"];
        }
        if ($change["metric"]["type"] === "set") {
            foreach ($change["delta"] as $key => $value) {
                $html = $html . '<img class="activity-badge" src="/pl/image?size=small&metric_id=' . $change['metric']['id'] . '&item=' . $key . '"></img>';
            }
        }
        return $html;
    }

    /**
     * @param $userID
     * @return mixed
     */
    public function getUserRank($userID)
    {
        $player = $this->playlyfe_repository->getPlayer($userID);
        $response = $this->playlyfe->get("/runtime/leaderboards/top_learners", [
            "player_id" => $player->player_id,
            "ranking" => "relative",
            "cycle" => "alltime",
            "entity_id" => $player->player_id,
            "radius" => 0
        ]);
        return $response;
    }

    /**
     * @param $userID
     * @return mixed
     */
    public function getUserThisWeekRank($userID)
    {
        $player = $this->playlyfe_repository->getPlayer($userID);
        $response = $this->playlyfe->get("/runtime/leaderboards/top_learners", [
            "player_id" => $player->player_id,
            "ranking" => "relative",
            "cycle" => "weekly",
            "entity_id" => $player->player_id,
            "radius" => 0,
            "timestamp" => strtotime('this week')
        ]);
        return $response;
    }

    /**
     * @param $userID
     * @return mixed
     */
    public function getUserLastWeekRank($userID)
    {
        $player = $this->playlyfe_repository->getPlayer($userID);
        $response = $this->playlyfe->get("/runtime/leaderboards/top_learners", [
            "player_id" => $player->player_id,
            "ranking" => "relative",
            "cycle" => "weekly",
            "entity_id" => $player->player_id,
            "radius" => 0,
            "timestamp" => strtotime('last week')
        ]);
        return $response;
    }

    /**
     * @param $player_id
     * @return mixed
     */
    public function getPlayerRank($player_id)
    {
        $response = $this->playlyfe->get("/runtime/leaderboards/top_learners", [
            "player_id" => $player_id,
            "ranking" => "relative",
            "cycle" => "alltime",
            "entity_id" => $player_id,
            "radius" => 0
        ]);
        return $response;
    }

    /**
     * @param $userID
     * @return null
     */
    public function getUserLevel($userID)
    {
        $player = $this->playlyfe_repository->getPlayer($userID);
        $response = $this->playlyfe->get("/runtime/player", [
            "player_id" => $player->player_id
        ]);
        foreach ($response["scores"] as $score) {
            if ($score["metric"]["type"] == "state") {
                $score["percent"] = "0";
                if ($score["value"]["name"] == "Beginner") {
                    $score["percent"] = "10";
                } elseif ($score["value"]["name"] == "Intermediate") {
                    $score["percent"] = "20";
                } elseif ($score["value"]["name"] == "Advanced") {
                    $score["percent"] = "40";
                } elseif ($score["value"]["name"] == "Professional") {
                    $score["percent"] = "60";
                } elseif ($score["value"]["name"] == "Expert") {
                    $score["percent"] = "80";
                } elseif ($score["value"]["name"] == "Guru") {
                    $score["percent"] = "100";
                }
                return $score;
            }
        }
        return null;
    }

    /**
     * @param $userID
     * @param $skip
     * @param $limit
     * @param $cycle
     * @param $timestamp
     * @param $relative
     * @return mixed
     */
    public function getLeaderboard($userID, $skip, $limit, $cycle, $timestamp, $relative)
    {
        if ($timestamp == null) {
            $timestamp = strtotime('this week') * 1000;
        }
        $player = $this->playlyfe_repository->getPlayer($userID);
        $query = [
            "skip" => $skip, "limit" => $limit, "cycle" => $cycle, "player_id" => $player->player_id,
            "entity_id" => $player->player_id, "timestamp" => $timestamp, "radius" => $limit,
        ];
        if ($relative == true) {
            $query["ranking"] = "relative";
        }
        $leaderboard = $this->playlyfe->get("/runtime/leaderboards/top_learners", $query);
        foreach ($leaderboard["data"] as $index => $value) {
            if ($value["player"]["id"] === $player->player_id) {
                $value["is_primary"] = true;
                $leaderboard["data"][$index] = $value;
            }
        }
        return $leaderboard;
    }

    /**
     * @param $player_id
     * @return mixed
     */
    public function getPlayer($player_id)
    {
        return $this->playlyfe->get("/admin/players/" . $player_id);
    }

    /**
     * @param $userID
     * @param $method
     * @param $route
     * @param array $query
     * @param array $body
     * @param bool $raw
     * @return mixed
     */
    public function api($userID, $method, $route, $query = [], $body = [], $raw = false)
    {
        $player = $this->playlyfe_repository->getPlayer($userID);
        $query["player_id"] = $player->player_id;
        $response = $this->playlyfe->api($method, $route, $query, $body, $raw);
        return $response;
    }

    /**
     *   getPlayerProfile
     *
     *   Gets the player profile from playlyfe
     *
     * @param integer $userID unique id of the user
     *
     * @return array $playerProfile or $errorInfo $playerProfile which will contain player points, leaderboard rank, level and
     *   badges or $errorInfo which contains error code and message if any exception is thrown from playlyfe repository.
     */


    public function getPlayerProfile($userID)
    {
        $playerProfileFlag = true;
        $playerProfile = [];
        $errorInfo = [];
        try {
            $player = $this->playlyfe_repository->getPlayer($userID);
            $playerProfile["player_info"] = $player;
            $playerProfile["profile_info"] = [
                "playlyfe_player_profile" => $this->playlyfe_repository->getPlaylyfePlayerProfile($player->player_id),
                "alltime" => $this->playlyfe_repository->getPlayerRankWithNextScore($player->player_id),
                "lastweek" => $this->playlyfe_repository->getPlayerRankLastWeekWithNextScore($player->player_id),
            ];
        } catch (Exception $e) {
            $playerProfileFlag = false;
            $errorCode = $e->getCode();
            $errorInfo = [
                "code" => $errorCode,
                "message" => trans("admin/exception.{$errorCode}")
            ];
            $playerProfile["error_info"] = $errorInfo;
        } finally {
            $playerProfile["player_profile_flag"] = $playerProfileFlag;
        }
        return $playerProfile;
    }

    /**
     * Gets the player activity which includes information about points, badges and level gained by the player.
     * @param  integer $userID User unique id in the application
     * @param  string $filter activity type(ex: login and signup - general, question asked and faq - QA etc..)
     * @param  integer $skip Number of activities to skip
     * @param  integer $take Number of activities to get
     * @return array $playerActivity Player activity
     */
    public function getActivity($userID, $filter = "all", $skip = 0, $take = 3)
    {
        $activityFlag = true;
        //$playerActivity = [];
        $playerActivity = null;
        $filterOptions = ["general", "channel", "assessment", "QA_FAQ"];
        $actions = [
            "general" => ["signup", "login", "logout"],
            "channel" => ["content_viewed"],
            "assessment" => ["quiz_completed"],
            "QA_FAQ" => ["question_asked", "question_marked_as_faq"]
        ];

        if ($filter === "all") {
            $filter = null;
        } else {
            $filter = $actions[$filter];
        }
        try {
            $player = $this->playlyfe_repository->getPlayer($userID);
            //$playerActivity["data"] = $this->playlyfeRepository->getPlayerActivity($player->player_id, $filter, $skip, $take);
            $playerActivity = $this->playlyfe_repository->getPlayerActivity($player->player_id, $filter, $skip, $take);
        } catch (Exception $e) {
            $activityFlag = false;
            $errorCode = $e->getCode();
            $playerActivity["error_info"] = [
                "code" => $errorCode,
                "message" => trans("admin/exception.{$errorCode}")
            ];
        } finally {
            //$playerActivity["activity_flag"] = $activityFlag;
        }

        return $playerActivity;
    }

    /**
     * Gets the player activity which includes information about points, badges and level gained by the player.
     * @param  integer $userID User unique id in the application
     * @param  string $filter activity type(ex: login and signup - general, question asked and faq - QA etc..)
     * @param  integer $skip Number of activities to skip
     * @param  integer $take Number of activities to get
     * @return array $playerActivity Player activity
     */
    public function getPlayerActivity($userID, $filter = "all", $skip = 0, $take = 3)
    {
        $activityFlag = true;
        $playerActivity = [];
        $filterOptions = ["general", "channel", "assessment", "QA_FAQ"];
        $actions = [
            "general" => ["signup", "login", "logout"],
            "channel" => ["content_viewed"],
            "assessment" => ["quiz_completed"],
            "QA_FAQ" => ["question_asked", "question_marked_as_faq"]
        ];

        if ($filter === "all") {
            $filter = null;
        } else {
            $filter = $actions[$filter];
        }
        try {
            $player = $this->playlyfe_repository->getPlayer($userID);
            $playerActivity["data"] = $this->playlyfe_repository->getPlayerActivity($player->player_id, $filter, $skip, $take);
        } catch (Exception $e) {
            $activityFlag = false;
            $errorCode = $e->getCode();
            $playerActivity["error_info"] = [
                "code" => $errorCode,
                "message" => trans("admin/exception.{$errorCode}"),
                "file" => $e->getFile(),
                "line" => $e->getLine()
            ];
        } finally {
            $playerActivity["activity_flag"] = $activityFlag;
        }

        return $playerActivity;
    }

    /**
     * Resets the player scores on playlyfe and his activity on ultron
     * @return array $playerActivity Player activity
     */
    public function resetAllPlayers()
    {
        $collection = $this->playlyfe->get('/admin/players', ['skip' => 0, 'limit' => 1]);
        $total = $collection["total"];
        for ($skip = 0; $skip < $total + 10; $skip += 10) {
            $collection = $this->playlyfe->get('/admin/players', ['fields' => 'id', 'skip' => $skip, 'limit' => 10]);
            foreach ($collection["data"] as $player) {
                $this->playlyfe->post('/admin/players/' . $player["id"] . '/reset');
            }
        }
        // ActionSummary::remove()
        // ActionSummary::where("player_id", $playerID);
    }

    /**
     * Export all users who are not on playlyfe to playlyfe
     * @return array $playerActivity Player activity
     */
    public function exportUsers()
    {
        $existingUsers = PlaylyfeUser::all();
        $existingIds = [];
        foreach ($existingUsers as $player) {
            array_push($existingIds, $player->player_id);
        }
        $exportUsers = User::where("status", "ACTIVE")->whereNotIn("username", $existingIds)->get();
        foreach ($exportUsers as $user) {
            $playlyfeEvent = [
                "type" => "create-user",
                "data" => [
                    "user_id" => $user->uid,
                    "player_id" => $user->username,
                    "player_alias" => $user->firstname
                ]
            ];

            $this->processEvent($playlyfeEvent);
        }
    }

    /**
     * Get All actions for playlyfe
     * @return array $actions
     */
    public function getActions()
    {
        $actions = $this->playlyfe->get('/design/versions/latest/actions', ['skip' => 0, 'limit' => 11, 'fields' => 'id,name,rules']);
        return $actions;
    }

    /**
     * Get the points for a given action
     * @param $action
     * @return array $actions
     */
    public function getPoints($action)
    {
        foreach ($action['rules'] as $rule) {
            foreach ($rule['rewards'] as $reward) {
                if ($reward['metric']['id'] == 'experience_points') {
                    return $reward['value'];
                }
            }
        }
    }

    /**
     * Patch all actions
     * @param $input
     */
    public function patchActions($input)
    {
        //TODO: Muni: What does this function do?
        $actions = $this->getActions();
        function getAction($actions, $id)
        {
            foreach ($actions as $action) {
                if ($action['id'] == $id) {
                    return $action;
                }
            }
        }

        $quiz_changed = false;
        $quiz_completed = getAction($actions, 'quiz_completed');
        unset($quiz_completed['id']);
        foreach ($input as $key => $value) {
            if (in_array($key, ['quiz_100', 'quiz_90', 'quiz_80', 'quiz_70', 'quiz_60'])) {
                foreach ($quiz_completed['rules'] as $index => $rule) {
                    if ($rule['requires']['type'] == 'and') {
                        switch ($rule['requires']['expression'][0]['context']['rhs']) {
                            case '60':
                                if ($key == 'quiz_60' && $value != $rule['rewards'][0]['value']) {
                                    $quiz_completed['rules'][$index]['rewards'][0]['value'] = $value;
                                    $quiz_changed = true;
                                }
                                break;
                            case '70':
                                if ($key == 'quiz_70' && $value != $rule['rewards'][0]['value']) {
                                    $quiz_completed['rules'][$index]['rewards'][0]['value'] = $value;
                                    $quiz_changed = true;
                                }
                                break;
                            case '80':
                                if ($key == 'quiz_80' && $value != $rule['rewards'][0]['value']) {
                                    $quiz_completed['rules'][$index]['rewards'][0]['value'] = $value;
                                    $quiz_changed = true;
                                }
                                break;
                            case '90':
                                if ($key == 'quiz_90' && $value != $rule['rewards'][0]['value']) {
                                    $quiz_completed['rules'][$index]['rewards'][0]['value'] = $value;
                                    $quiz_changed = true;
                                }
                                break;
                        }
                    }
                    if ($key == 'quiz_100' && $rule['requires']['type'] == 'var') {
                        if ($rule['requires']['context']['rhs'] == '100' && $value != $rule['rewards'][0]['value']) {
                            $quiz_completed['rules'][$index]['rewards'][0]['value'] = $value;
                            $quiz_changed = true;
                        }
                    }
                }
            } else {
                $action = getAction($actions, $key);
                if ($this->getPoints($action) != $value) {
                    unset($action['id']);
                    unset($action['name']);
                    foreach ($action['rules'] as $index => $rule) {
                        if (isset($rule['requires'])) {
                            $action['rules'][$index]['requires'] = (object)$action['rules'][$index]['requires'];
                        }
                        foreach ($rule['rewards'] as $index2 => $reward) {
                            if ($reward['metric']['id'] == 'experience_points') {
                                $action['rules'][$index]['rewards'][$index2]['value'] = $value;
                                break;
                            }
                        }
                    }
                    $this->playlyfe->patch('/design/versions/latest/actions/' . $key, [], $action);
                }
            }
        }
        if ($quiz_changed) {
            $this->playlyfe->patch('/design/versions/latest/actions/quiz_completed', [], $quiz_completed);
        }
        $this->playlyfe->post('/design/versions/latest/deploy');
    }

    /**
     * @param $player_id
     * @return null
     */
    public function getUserLevelByPlayerId($player_id)
    {
        $response = $this->playlyfe->get("/runtime/player", [
            "player_id" => $player_id
        ]);
        foreach ($response["scores"] as $score) {
            if ($score["metric"]["type"] == "state") {
                $score["percent"] = "0";
                if ($score["value"]["name"] == "Beginner") {
                    $score["percent"] = "10";
                } elseif ($score["value"]["name"] == "Intermediate") {
                    $score["percent"] = "20";
                } elseif ($score["value"]["name"] == "Advanced") {
                    $score["percent"] = "40";
                } elseif ($score["value"]["name"] == "Professional") {
                    $score["percent"] = "60";
                } elseif ($score["value"]["name"] == "Expert") {
                    $score["percent"] = "80";
                } elseif ($score["value"]["name"] == "Guru") {
                    $score["percent"] = "100";
                }
                return $score;
            }
        }
        return null;
    }

    /**
     * @param $pattern
     * @param $start
     * @param $length
     * @return mixed
     */
    public function getLeaderboardByPattern($pattern, $start, $length)
    {
        $player_ids = $this->playlyfe_repository->getPlayerByPattern($pattern, $start, $length);
        return $player_ids;
    }

    /**
     * @param $pattern
     * @return mixed
     */
    public function getLeaderboardByPatternCount($pattern)
    {
        $player_ids_count = $this->playlyfe_repository->getLeaderboardByPatternCount($pattern);
        return $player_ids_count;
    }

    /**
     * @param $player_ids
     * @param $userId
     * @return mixed
     */
    public function getLeaderboardByPlayerIds($player_ids, $userId)
    {
        $player = $this->playlyfe_repository->getPlayer($userId);

        $response = $this->playlyfe->post("/runtime/leaderboards/top_learners/search", ["player_id" => $player->player_id, "cycle" => "alltime"], [
            "player_ids" => $player_ids
        ]);

        return $response;
    }
}
