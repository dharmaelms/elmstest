<?php namespace App\Model\Playlyfe\Repository;

use App\Exceptions\Playlyfe\AccessTokenNotFoundException;
use App\Exceptions\Playlyfe\PlayerNotFoundException;
use App\Model\Playlyfe\Entity\ActionSummary;
use App\Model\Playlyfe\Entity\Log;
use App\Model\Playlyfe\Entity\PlaylyfeUser;
use App\Model\SiteSetting;
use Config;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Playlyfe\Sdk\Playlyfe;

/**
 * Class PlaylyfeRepository
 * @package App\Model\Playlyfe\Repository
 */
class PlaylyfeRepository implements IPlaylyfeRepository
{
    /**
     * @var mixed
     */
    public $playlyfeEnabledFlag;

    /**
     * @var Playlyfe
     */
    public $playlyfe;

    /**
     * PlaylyfeRepository constructor.
     */
    public function __construct()
    {
        $this->playlyfeEnabledFlag = Config::get("app.playlyfe.enabled");

        if ($this->playlyfeEnabledFlag) {
            $playlyfeAPIConfig = [
                "version" => Config::get("app.playlyfe.version"),
                "client_id" => Config::get("app.playlyfe.client_id"),
                "client_secret" => Config::get("app.playlyfe.client_secret"),
                "type" => Config::get("app.playlyfe.type")
            ];

            $playlyfeAPIConfig["store"] = function ($access_token) {
                self::storeAPIAccessToken($access_token);
            };

            $playlyfeAPIConfig["load"] = function () {
                try {
                    return self::getAPIAccessToken();
                } catch (Exception $e) {
                    return null;
                }
            };

            $this->playlyfe = new Playlyfe($playlyfeAPIConfig);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function storeAPIAccessToken($access_token)
    {
        try {
            self::getAPIAccessToken();
        } catch (ModelNotFoundException $e) {
            $siteSetting = new SiteSetting();

            $siteSetting->module = "playlyfe";

            $siteSetting->save();
        } catch (AccessTokenNotFoundException $e) {
        } finally {
            SiteSetting::where("module", "playlyfe")->update(["setting.token" => $access_token]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getAPIAccessToken()
    {
        $query = SiteSetting::where("module", "playlyfe");
        $resultSet = $query->firstOrFail();
        if (!isset($resultSet->setting["token"])) {
            throw new AccessTokenNotFoundException();
        }
        return $resultSet->setting["token"];
    }

    /**
     * {@inheritdoc}
     */
    public function createPlayer($data)
    {
        $player = new PlaylyfeUser();

        $player->user_id = $data["user_id"];
        $player->player_id = $data["player_id"];
        $player->player_alias = $data["player_alias"];
        $player->status = $data["status"];

        $player->save();

        return $player;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlayer($userID)
    {
        $player = PlaylyfeUser::where("user_id", $userID)
            ->where("status", "!=", "deleted")
            ->get(["user_id", "player_id", "player_alias", "status"])
            ->first();
        if (is_null($player)) {
            throw new PlayerNotFoundException($userID);
        }
        return $player;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlayerByPattern($pattern, $start, $length)
    {
        $ids = [];
        $player_ids = PlaylyfeUser::where('status', '=', 'active')
            ->where('player_alias', 'like', '%' . $pattern . '%')
            ->skip((int)$start)->take($length)
            ->get(['player_id'])->toArray();
        if (isset($player_ids) && is_array($player_ids)) {
            foreach ($player_ids as $key => $value) {
                $ids[] = $value['player_id'];
            }
        }
        return $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getLeaderboardByPatternCount($pattern)
    {
        $users_count = PlaylyfeUser::where('status', '=', 'active')->where('player_alias', 'like', '%' . $pattern . '%')->count();
        return $users_count;
    }

    /**
     * {@inheritdoc}
     */
    public function createActionSummary($data)
    {
        $actionSummary = new ActionSummary();

        $actionSummary->fill($data);

        $actionSummary->save();

        return $actionSummary;
    }

    /**
     * {@inheritdoc}
     */
    public function log($data)
    {
        $log = new Log();

        $log->fill($data);

        $log->save();

        return $log;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaylyfePlayerProfile($playerID)
    {
        $playlyfePlayerProfile = [];
        //playlyfe api function accepts HTTP verb, route, query, body and raw parameters.
        $apiResponse = $this->playlyfe->api("GET", "/runtime/player", ["player_id" => $playerID], [], []);
        $points = 0;
        $level = [];
        $badges = [];
        foreach ($apiResponse["scores"] as $metricInfo) {
            if ($metricInfo["metric"]["type"] === "point" && $metricInfo["metric"]["id"] === "experience_points") {
                $points = $metricInfo["value"];
            } elseif ($metricInfo["metric"]["type"] === "state" && $metricInfo["metric"]["id"] === "knowledge_level") {
                $level = $this->getPlayerLevel([
                    "info" => $metricInfo["value"],
                    "metaInfo" => $metricInfo["meta"]
                ]);
            } elseif ($metricInfo["metric"]["type"] === "set") {
                $badges[] = $metricInfo;
            }
        }

        if (!empty($badges)) {
            $badges = $this->sortBadges($badges);
        }

        return [
            "points" => $points,
            "level" => $level,
            "badges" => $badges
        ];
    }

    /**
     * getPlayerLevel
     *
     * Parses the state metric raw response and returns the level information for player profile.
     *
     * @param  array $levelRawInfo holds the state metric raw response from playlyfe API.
     *
     * @return array $level that contains player current level, current level percentage and next level.
     */
    private function getPlayerLevel($levelRawInfo)
    {
        $level = [];
        $level["name"] = $levelRawInfo["info"]["name"];
        $levelPercentages = [
            "Beginner" => 10,
            "Intermediate" => 20,
            "Advanced" => 40,
            "Professional" => 60,
            "Expert" => 80,
            "Guru" => 100
        ];
        $level["percentage"] = $levelPercentages[$level["name"]];
        if (isset($levelRawInfo["metaInfo"]["next"])) {
            $level["next_level"] = $levelRawInfo["metaInfo"]["next"];
        }

        return $level;
    }

    /**
     * sortBadges
     *
     * Sorts based on the count of each type of badges gained by player
     *
     * @param  array $badges contains badge information
     *
     * @return array $badges contains sorted badge information
     */
    private function sortBadges($badges)
    {
        $tmp = [];
        foreach ($badges as $badge) {
            foreach ($badge["value"] as $badgeInfo) {
                $badgeInfo["type_info"] = $badge["metric"];
                if ($badgeInfo["count"] >= 1) {
                    array_push($tmp, $badgeInfo);
                }
            }
        }

        usort($tmp, function ($badge1, $badge2) {
            $badge1["count"] = intval($badge1["count"]);
            $badge2["count"] = intval($badge2["count"]);
            return ($badge2["count"] > $badge1["count"]) ? 1 : (($badge2["count"] === $badge1["count"]) ? 0 : -1);
        });

        return $tmp;
    }


    /**
     * {@inheritdoc}
     */
    public function getPlayerRankWithNextScore($playerID)
    {
        $playerRank = ["next_score" => -1, "rank" => -1];
        $apiResponse = $this->playlyfe->get("/runtime/leaderboards/top_learners", [
            "player_id" => $playerID,
            "ranking" => "relative",
            "cycle" => "alltime",
            "entity_id" => $playerID,
            "radius" => 1
        ]);
        foreach ($apiResponse["data"] as $index => $data) {
            if ($data["player"]["id"] == $playerID) {
                $playerRank["rank"] = $data["rank"];
            }
            if ($index == 1) {
                $playerRank["next_score"] = $apiResponse["data"][0]["score"];
            }
        }
        return $playerRank;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlayerRankLastWeekWithNextScore($playerID)
    {
        $playerRank = ["next_score" => -1, "rank" => -1];
        try {
            $apiResponse = $this->playlyfe->get("/runtime/leaderboards/top_learners", [
                "player_id" => $playerID,
                "ranking" => "relative",
                "cycle" => "weekly",
                "entity_id" => $playerID,
                "radius" => 1,
                "timestamp" => strtotime('last week')
            ]);
            foreach ($apiResponse["data"] as $index => $data) {
                if ($data["player"]["id"] == $playerID) {
                    $playerRank["rank"] = $data["rank"];
                }
                if ($index == 1) {
                    $playerRank["next_score"] = $apiResponse["data"][0]["score"];
                }
            }
        } catch (Exception $e) {
        }
        return $playerRank;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlayerActivity($playerID, $filter, $skip = 0, $take = 10)
    {
        $query = ActionSummary::where("player_id", $playerID);
        if (!is_null($filter)) {
            $query->whereIn("action_id", $filter);
        }
        $activityCount = $query->count();
        $activityCollection = $query->orderBy("created_at", "desc")
            ->skip((int)$skip)
            ->take(5)
            ->get();
        $parsedActivityList = $this->parsePlayerActivity($activityCollection);
        return [
            "activity_count" => $activityCount,
            "activity_collection" => $parsedActivityList
        ];
    }

    /**
     * Parses actual model collection object to required format
     * @param  Object $activityCollection Player activity collection object
     * @return array $parsedActivityList Contains parsed player activity data
     */
    private function parsePlayerActivity($activityCollection)
    {
        $parsedActivityList = [];
        if (!empty($activityCollection)) {
            foreach ($activityCollection as $tmp1) {
                $tmpActivityData["action_id"] = $tmp1->action_id;
                $tmpActivityData["action_data"] = $tmp1->action_data;
                $points = [];
                $points["value"] = 0;
                $badges = [];
                foreach ($tmp1["action_result"] as $tmp2) {
                    if ($tmp2["metric"]["type"] === "point" && $tmp2["metric"]["id"] === "experience_points") {
                        $oldVal = $tmp2["delta"]["old"];
                        $newVal = $tmp2["delta"]["new"];
                        if (!is_null($oldVal)) {
                            $points["value"] += $newVal - $oldVal;
                        } else {
                            $points["value"] += $newVal;
                        }
                        if (!isset($points["metric_info"])) {
                            $points["metric_info"] = $tmp2["metric"];
                        }
                    } elseif ($tmp2["metric"]["type"] === "set") {
                        foreach ($tmp2["delta"] as $key1 => $val1) {
                            $badges[] = ["metric_info" => $tmp2["metric"], "name" => $key1, "value" => (!is_null($val1["old"]) ? ($val1["new"] - $val1["old"]) : $val1["new"])];
                        }
                    }
                }
                $tmpActivityData["action_result"] = ["points" => $points, "badges" => $badges];
                $parsedActivityList[] = $tmpActivityData;
            }
        }

        return $parsedActivityList;
    }
}
