<?php

namespace App\Http\Controllers\Portal;

use App\Enums\Elastic\Types as ET;
use App\Http\Controllers\PortalBaseController;
use App\Libraries\Elastic\Elastic;
use Auth;
use Illuminate\Http\Request;

class SearchController extends PortalBaseController
{
    public function __construct()
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }
    public function getIndex(Request $request)
    {
        $elastic = new Elastic;
        $limit = $request->input('limit', 10);
        $start = $request->input('from', 0);
        $query = $request->input('query', '');
        $params = [
            'index' => config('elastic.params.index'),
            'type' => [ET::PROGRAM, ET::PACKAGE, ET::POST, ET::ITEM, ET::ASSESSMENT, ET::EVENT],
            'body' => [
                "from" => $start,
                "size" => $limit,
                "query" => [
                    "bool" => [
                        "must" => [
                            "multi_match" => [
                                "query" => trim(strtolower($query)),
                                "type" => 'best_fields',
                                "fields" => [
                                    "title",
                                    "description",
                                    "short_title",
                                    "keywords",
                                ],
                            ]
                        ],
                        
                    ]
                ]
            ]
        ];
        if (!is_admin_role(Auth::user()->role)) {
            $params['body']['query']['bool']["filter"]["term"]["user_ids"] = Auth::user()->uid;
        }
        $results = $elastic->search($params);
        if ($request->ajax()) {
            if ($results['hits']['total'] == 0 || empty($results['hits']['hits'])) {
                return ['status' => false, 'message' => 'No Result found'];
            } else {
                return response()->json(['status' => true, 'data' => view($this->theme_path.'.search.listing', ['query' => $query, 'limit' => $limit, 'results' => $results['hits']['hits']])->render(), 'count' => $start+count($results['hits']['hits'])]);
            }
        }
        $this->layout->theme = 'portal/theme/'.$this->theme;
        $this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
        $this->layout->header = view($this->theme_path.'.common.header');
        $this->layout->footer = view($this->theme_path.'.common.footer');
        $this->layout->content = view($this->theme_path.'.search.searchresults', ['query' => $query, 'limit' => $limit, 'results' => $results]);
    }
}
