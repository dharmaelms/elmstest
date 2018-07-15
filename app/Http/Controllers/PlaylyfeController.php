<?php

namespace App\Http\Controllers;

use App;
use App\Services\Playlyfe\IPlaylyfeService;
use Auth;
use Illuminate\Http\Request;
use Input;

//Facade classes

class PlaylyfeController extends Controller
{
    //Holds playlyfe service instance.
    private $playlyfe;

    public function __construct(IPlaylyfeService $playlyfe)
    {
        $this->playlyfe = $playlyfe;
    }

    public function getLeaderboard(Request $req)
    {
        $start = $req->query("start");
        $length = $req->query("length");
        $cycle = $req->query("cycle");
        $type = $req->query("type");
        $search_text = '';
        $search = Input::get('search');
        if (isset($search['value'])) {
            $search_text = trim($search['value']);
        }
        $timestamp = strtotime('this week') * 1000;
        if ($type != null) {
            if ($type == "this_week") {
                $cycle = "weekly";
            } elseif ($type == "last_week") {
                $cycle = "weekly";
                $timestamp = strtotime('last week') * 1000;
            } else {
                $cycle = "alltime";
            }
        }
        $order_by = Input::get('order');
        $orderByArray = ['rank' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '0') {
                $orderByArray = ['rank' => $order_by[0]['dir']];
            }
        }
        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $length = Input::get('length');
        }
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);


        if ($search_text != '') {
            $player_ids = $pl->getLeaderboardByPattern($search_text, $start, $length);
            $playersTotal = $pl->getLeaderboardByPatternCount($search_text);
            $userId = Auth::user()->uid;
            $data = $pl->getLeaderboardByPlayerIds($player_ids, $userId);
            $data["recordsTotal"] = $playersTotal;
            $data["recordsFiltered"] = $playersTotal;
            echo json_encode($data);
            exit;
        } else {
            $data = $pl->getLeaderboard(Auth::user()->uid, $start, $length, $cycle, $timestamp, false);
            $data["recordsTotal"] = $data["total"];
            $data["recordsFiltered"] = $data["total"];
            echo json_encode($data);
            exit;
        }
    }


    public function getProfile(Request $req)
    {
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
        $data['pl_details'] = $pl->getPlayer($req->query("id"));
        $data['pl_score'] = $this->playlyfe->getUserLevelByPlayerId($req->query("id"));
        echo json_encode($data);
        exit;
    }

    public function getActivity()
    {
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
        $data = $pl->getActivity(Auth::user()->uid);
        echo json_encode($data);
    }

    public function getRank(Request $req)
    {
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
        return $pl->getPlayerRank($req->query("id"));
    }

    public function getImage(Request $req)
    {
        $metricID = $req->query("metric_id");
        $query = [];
        $query["size"] = $req->query("size");
        if ($query["size"] === null) {
            $query["size"] = "small";
        }
        if ($req->query("item") !== null) {
            $query["item"] = $req->query("item");
        }
        if ($req->query("state") !== null) {
            $query["state"] = $req->query("state");
        }
        header('Content-Type: image/png');
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
        $data = $pl->api(Auth::user()->uid, "GET", "/runtime/assets/definitions/metrics/" . $metricID, $query, [], true);
        echo $data;
        exit;
    }

    /**
     * postReset
     *
     * Resets all the players
     *
     * @return Object View instance Loads player profile view
     */
    public function postReset()
    {
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
        $pl->resetAllPlayers();
    }

    /**
     * postExport
     *
     * Exports all the non-existing users of ultron to playlyfe
     *
     */
    public function postExport()
    {
        $pl = App::make(\App\Services\Playlyfe\IPlaylyfeService::class);
        $pl->exportUsers();
        exit;
    }

    /**
     * getPlayerProfile
     *
     * Gets the player profile from the playlyfe service by passing the User ID
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getPlayerProfile()
    {
        $userID = Auth::user()->uid;
        $playerProfile = $this->playlyfe->getPlayerProfile($userID);
        $pl_score = $this->playlyfe->getUserLevel(Auth::user()->uid);
        return view("playlyfe.player_profile")
            ->with("playerProfile", $playerProfile)
            ->with("pl_score", $pl_score);
    }

    /**
     * Gets the playlyfe player activity using playlyfe service
     * @param  Object $request request object to get filter options
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function getPlayerActivity(Request $request)
    {
        $filter = $request->input("filter", "all");
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 5);
        $start = ($page > 1) ? (($page - 1) * 5) : 0;
        $contentOnly = $request->input("contentOnly", false);
        $userID = Auth::user()->uid;
        $playerActivity = $this->playlyfe->getPlayerActivity($userID, $filter, $start, $limit);
        if ($contentOnly === "true") {
            return view("playlyfe.player_activity_content")->with("playerActivity", $playerActivity);
        } else {
            $playerActivity["enable_filter"] = true;
            $playerActivity["enable_paginator"] = true;
            $playerActivity["filter"] = $filter;
            $playerActivity["current_page"] = $page;
            $playerActivity["total_page"] = ceil($playerActivity["data"]["activity_count"] / $limit);
            return view("playlyfe.player_activity")->with("playerActivity", $playerActivity);
        }
    }
}
