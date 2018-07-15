<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Tabs\ITabService;
use Input;
use URL;
use Validator;

class TabController extends Controller
{
    protected $pricingSer;
    protected $tabServ;
    protected $pay_currency = "INR";

    /*protected $layout = 'admin.theme.layout.master_layout';*/

    public function __construct(
        IPricingService $priceService,
        ITabService $tabs
    )
    {


        $this->pricingSer = $priceService;
        $this->tabServ = $tabs;
    }

    public function postSaveTab()
    {
        $data = Input::all();
        $rules = [
            'title' => 'Required|regex:/^[a-zA-Z0-9-_ ]+$/|subdublicate',
            'description' => 'Required'
        ];
        Validator::extend('subdublicate', function ($attribute, $value, $parameters) {
            $data = Input::all();
            return $this->tabServ->cDuplicate($data['p_id'], $data['p_type'], $data['title']);
        });


        $slug = Input::get('program_slug');
        $messages = [
            'subdublicate' => 'The title field is duplicate.',
            'title.regex' => 'The title allows only alphanumeric, space and hyphen.'
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        $json = [];
        foreach ($data as $key => $value) {
            $json[$key] = '';
        }
        foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
            $json = array_merge($json, [$key => $eachError[0]]);
        }
        if ($validation->fails()) {
            $json = array_merge($json, ['success' => 'error']);
            return response()->json($json);
        } elseif ($validation->passes()) {
            $this->tabServ->saveTab($data);
            $json = ['success' => URL::to("cp/tab/route-to/$slug/".array_get($data, 'p_type', ''))];
            return response()->json($json);
        }
    }

    public function getRouteTo($slug, $p_type = null)
    {
        if (!empty($p_type) && $p_type === "product") {
            return redirect('cp/contentfeedmanagement/edit-product/' . $slug)
                ->with('success_tab', "Tab added successfully")
                ->with('tab', 'enabled');
        } elseif (!empty($p_type) && $p_type === "course") {
            return redirect('cp/contentfeedmanagement/edit-course/' . $slug)
                ->with('success_tab', "Tab added successfully")
                ->with('tab', 'enabled');
        }
        return redirect('cp/contentfeedmanagement/edit-feed/' . $slug)
            ->with('success_tab', "Tab added successfully")
            ->with('tab', 'enabled');
    }

    public function getRouteToEdit($slug, $p_type = null)
    {
        if (!empty($p_type) && $p_type == "product") {
            return redirect('cp/contentfeedmanagement/edit-product/' . $slug)
                ->with('success_tab', "Tab edited successfully")
                ->with('tab', 'enabled');
        } elseif (!empty($p_type) && $p_type == "course") {
            return redirect('cp/contentfeedmanagement/edit-course/' . $slug)
                ->with('success_tab', "Tab edited successfully")
                ->with('tab', 'enabled');
        }
        return redirect('cp/contentfeedmanagement/edit-feed/' . $slug)
            ->with('success_tab', "Tab edited successfully")
            ->with('tab', 'enabled');
    }

    public function getDelete($p_id, $slug, $p_type = null)
    {
        $p_slug = $this->tabServ->deleteTab($p_id, $slug);
        if (!empty($p_type) && $p_type == "product") {
            return redirect('cp/contentfeedmanagement/edit-product/' . $p_slug)
                ->with('success_tab', "Tab deleted successfully.")
                ->with('tab', 'enabled');
        } elseif (!empty($p_type) && $p_type == "course") {
            return redirect('cp/contentfeedmanagement/edit-course/' . $p_slug)
                ->with('success_tab', "Tab deleted successfully.")
                ->with('tab', 'enabled');
        } else {
            return redirect('cp/contentfeedmanagement/edit-feed/' . $p_slug)
                ->with('success_tab', "Tab deleted successfully.")
                ->with('tab', 'enabled');
        }
    }

    public function postEdit($pid, $slug)
    {
        $data = $this->tabServ->getTabBySlug($pid, $slug);
        $data = array_merge($data, ['pid' => $pid]);
        echo view(
            'admin/theme/Catalog/tabs/edit',
            ['tabs' => $data]
        );
    }

    public function postEditSave()
    {
        $data = Input::all();
        $rules = [
            'title' => 'Required|regex:/^[a-zA-Z0-9-_ ]+$/|subdublicate',
            'description' => 'Required'
        ];
        Validator::extend('subdublicate', function ($attribute, $value, $parameters) {
            $data = Input::all();
            return $this->tabServ->cDuplicate($data['p_id'], $data['p_type'], $data['title'], $data['ctitle']);
        });

        $slug = Input::get('program_slug');
        $messages = [
            'subdublicate' => 'The title field is duplicate.',
            'title.regex' => 'The title allows only alphanumeric, space and hyphen.'
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        $json = [];
        foreach ($data as $key => $value) {
            $json[$key] = '';
        }
        foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
            $json = array_merge($json, [$key => $eachError[0]]);
        }
        if ($validation->fails()) {
            $json = array_merge($json, ['success' => 'error']);
            return response()->json($json);
        } elseif ($validation->passes()) {
            $this->tabServ->saveEditTab($data);
            $json = ['success' => URL::to("cp/tab/route-to-edit/$slug/".array_get($data, 'p_type', ''))];
            return response()->json($json);
        }
    }
}
