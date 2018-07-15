<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\ManageAttribute;
use App\Model\Product;
use Auth;
use Input;
use Redirect;
use Timezone;
use URL;
use Validator;

class ManageAttributeController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    /**
     * @no param
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->theme_path = 'admin.theme';
    }

    /**
     *Display a listing of the attributes.
     */
    public function getIndex()
    {

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/attribute.manage_sites') => 'manageweb',
            trans('admin/attribute.manage_attribute') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-wrench';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'attribute');
        $this->layout->pagetitle = trans('admin/attribute.manage_attribute');
        $this->layout->pagedescription = '';
        $this->layout->content = view('admin.theme.sitesettings.list_attribute');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     *Display create attribute.
     */
    public function getCreateAttribute()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/attribute.manage_attribute') => 'manageattribute',
            trans('admin/attribute.add_attribute') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-wrench';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'attribute');
        $this->layout->pagetitle = trans('admin/attribute.create_attribute');
        $this->layout->pagedescription = '';
        $this->layout->content = view('admin.theme.sitesettings.attribute');
        $this->layout->footer = view('admin.theme.common.footer');
    }


    /**
     *Function to create a new attribute.
     */
    public function postAddAttribute()
    {
        $path = '';
        $input = Input::all();
        $rules = [
            'attribute_type' => 'Required|Regex:/^[a-zA-Z0-9 !&@%]+$/|max:50',
            'attribute_name' => 'Required|Regex:/^[a-zA-Z0-9 !&@%]+$/|max:50',
            'attribute_label' => 'Required|Regex:/^[a-zA-Z0-9 !&@%]+$/|max:50',
        ];

        $validation = Validator::make(Input::all(), $rules);
        $count = ManageAttribute::checkattribute($input['attribute_name'], $input['attribute_type']);

        if ($validation->fails()) {
            //  return Redirect::back()->withErrors($validation);
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($count > 0) {
            $error = trans('admin/attribute.unique_attribute_alert');
            //return Redirect::back()->with('attributename_exist', $error);
            return Redirect::back()->withInput()->with('attributename_exist', $error);
        } else {
            $record = ManageAttribute::addAttribute(Input::all());
            $success = trans('admin/attribute.add_attribute_success');
            return redirect('cp/manageattribute/')->with('success', $success);
        }
    }

    /**
     *To display list of attributes
     */
    public function getAttributeListAjax()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['attribute_name' => $order_by[0]['dir']];
            }

            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
        }

        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');

        /*if (!in_array($filter, array('ACTIVE', 'IN-ACTIVE', 'EMPTY'))) {
        $filter = 'all';
    }*/

        $total_num_role = ManageAttribute::getAttributeCount();

        $num_roles_with_filter = ManageAttribute::getAttributeCount($filter, $searchKey);

        $roles = ManageAttribute::getFilteredAttributesWithPagination($filter, $start, $limit, $orderByArray, $searchKey);
        //print_r($roles);
        //die;
        $delete = $edit = '';
        $dataArr = [];

        foreach ($roles as $value) {
            $feed_rel = $assosiated_feeds_count = '';
            $feed_rel = ManageAttribute::getFeedsRelation($value['attribute_id']);
            if (!empty($feed_rel) && isset($feed_rel[0]['relations']['assigned_product'])) {
                $assosiated_feeds_count = count($feed_rel[0]['relations']['assigned_product']);
            }


            $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/attribute.action_edit') . '"
                href="' . URL::to('cp/manageattribute/edit-attribute/' . $value['attribute_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-edit"></i></a>';


            $created_at = 'N/A';
            if ($value['created_at'] != null) {
                $created_at = Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format'));
            }
            $value['visibility'] = ($value['visibility'] > 0) ? 'Yes' : 'No';
            $value['mandatory'] = ($value['mandatory'] > 0) ? 'Yes' : 'No';
            if ($assosiated_feeds_count == 0) {
                $assign = "<a href='" . URL::to('/cp/manageattribute/list-feeds?filter=ACTIVE&view=iframe') . "'
                class='categoryrel badge badge-grey' data-key='" . $value['attribute_id'] . "'
                data-info='feed' data-text='Assign " . trans('admin/attribute.product') . '
                to <b>' . '"' . $value['attribute_name'] . '"' . "</b>' data-json=''>" . 0 . '</a>';
            } else {
                $assign = "<a href='" . URL::to('/cp/manageattribute/list-feeds?filter=ACTIVE&view=iframe') . "'
                class='categoryrel badge badge-success' data-key='" . $value['attribute_id'] . "'
                data-info='feed' data-text='Assign " . trans('admin/attribute.product') . '
                to <b>' . '"' . $value['attribute_name'] . '"' . "</b>' data-json='" . json_encode($feed_rel[0]['relations']['assigned_product']) . "'>
                " . count($feed_rel[0]['relations']['assigned_product']) . '</a>';
            }
            if ($assosiated_feeds_count > 0 || $value['attribute_id'] < 4) {
                $checkbox = '<input type="checkbox" value="' . $value['attribute_id'] . '" disabled="disabled">';
                $c_msg = trans('admin/attribute.u_cant_del_since_prod_assigned');
                $p_msg = trans('admin/attribute.u_cant_del_primary_attribute');
                $title = ($assosiated_feeds_count > 0) ? $c_msg : $p_msg;
                $delete = '<a class="btn btn-circle show-tooltip" title="' . $title . '"?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '
                &search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '
                "><i class="fa fa-trash-o"></i></a>';
            } else {
                $checkbox = '<input type="checkbox" value="' . $value['attribute_id'] . '">';
                $delete = '<a class="btn btn-circle show-tooltip deleteattribute" title="' . trans('admin/attribute.action_delete') . '"
                href="' . URL::to('cp/manageattribute/remove-attribute/' . $value['attribute_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-trash-o"></i></a>';
            }
            $temparr = [
                $checkbox,
                $value['attribute_type'],
                $value['attribute_name'],
                $value['attribute_label'],
                $assign,
                $value['visibility'],
                $value['mandatory'],
                $created_at,
                $edit . '' . $delete,
            ];
            $dataArr[] = $temparr;
        }


        $finaldata = [
            'recordsTotal' => $total_num_role,
            'recordsFiltered' => $num_roles_with_filter,
            'data' => $dataArr,
        ];
        return response()->json($finaldata);
    }

    /**
     *To bulk delete
     */
    public function postBulkDelete()
    {

        $attributes_checked = Input::get('ids');
        $attributes_checked = explode(',', trim($attributes_checked, ','));

        foreach ($attributes_checked as $attribute) {
            ManageAttribute::deleteAttribute($attribute);
        }
        $success = trans('admin/attribute.remove_multiple_attribute_success');

        return redirect('cp/manageattribute/')->with('success', $success);
    }

    /**
     *To delete perticular attribute
     */
    public function getRemoveAttribute($id = null)
    {
        ManageAttribute::deleteAttribute($id);

        $success = trans('admin/attribute.remove_attribute_success');

        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '2 desc');
        $filter = Input::get('filter', 'ALL');

        $totalRecords = ManageAttribute::getAttributeCount();
        if ($totalRecords <= $start) {
            $start -= $limit;
            if ($start < 0) {
                $start = 0;
            }
        }
        $redirect = '/cp/manageattribute?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by;

        return redirect($redirect)->with('success', $success);
    }

    /**
     *Showing edit attribute form
     */
    public function getEditAttribute($id)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/attribute.manage_attribute') => 'manageattribute',
            trans('admin/attribute.update_attribute') => '',
        ];
        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        }
        $start = 0;
        $limit = 10;

        if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
            $start = (int)Input::get('start');
            $limit = (int)Input::get('limit');
        }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/attribute.edit_attribute');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/attribute.edit_attribute');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'attribute');
        $this->layout->footer = view('admin.theme.common.footer');
        $attribute = ManageAttribute::getAttributeUsingID($id);
        $attribute = $attribute[0];
        $this->layout->content = view('admin.theme.sitesettings.editattribute')
            ->with('filter', $filter)
            ->with('start', $start)
            ->with('limit', $limit)
            ->with(['attribute' => $attribute]);
    }

    /**
     *Updating existing attribute
     */
    public function postEditAttribute($id)
    {
        $path = '';
        $input = Input::all();
        $rules = [
            'attribute_type' => 'Required|Regex:/^[a-zA-Z0-9 !&@%]+$/|max:50',
            'attribute_name' => 'Required|Regex:/^[a-zA-Z0-9 !&@%]+$/|max:50',
            'attribute_label' => 'Required|Regex:/^[a-zA-Z0-9 !&@%]+$/|max:50',
        ];

        $validation = Validator::make(Input::all(), $rules);
        $count = ManageAttribute::checkEditAttribute($input['attribute_name'], $id, $input['attribute_type']);


        if ($validation->fails()) {
            //return Redirect::back()->withErrors($validation);
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($count > 0) {
            $error = trans('admin/attribute.unique_attribute_alert');
            //return Redirect::back()->with('attributename_exist', $error);
            return Redirect::back()->withInput()->with('attributename_exist', $error);
        } else {
            $updaterecord = ManageAttribute::updateAttribute(Input::all(), $id);
            $success = trans('admin/attribute.update_attribute_success');
            return redirect('cp/manageattribute/')->with('success', $success);
        }
    }

    public function getListFeeds()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            'Manage ' . trans('admin/program.programs') => 'contentfeedmanagement',
            'List ' . trans('admin/program.programs') => '',
        ];
        $viewmode = Input::get('view', 'desktop');
        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.sitesettings.listattributefeediframe')
                ->with('relfilter', $relfilter)
                ->with('from', $from)
                ->with('relid', $relid);
            $this->layout->footer = '';
        }
    }

    public function getFeedListAjax()
    {
        $start = 0;
        $limit = 10;
        $viewmode = Input::get('view', 'desktop');
        if ($viewmode != 'iframe') {
            $finaldata = [
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ];

            return response()->json($finaldata);
        }
        $search = Input::get('search');

        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['product_type' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['product_type' => $order_by[0]['dir']];
            }
        }
        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        $filter = strtolower($filter);
        if (!in_array($filter, ['active', 'in-active'])) {
            $filter = 'all';
        } else {
            $filter = strtoupper($filter);
        }
        $relfilter = Input::get('relfilter', 'all');
        $from = Input::get('from', 'none');
        $relid = Input::get('relid', 'none');
        $relinfo = [$from => $relid];
        //, $relinfo
        $totalRecords = Product::getContentFeedCount('all', null);
        $filteredRecords = Product::getContentFeedCount($relfilter, $searchKey);
        $filtereddata = Product::getContentFeedWithTypeAndPagination($relfilter, $start, $limit, $orderByArray, $searchKey);


        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $temparr = [
                '<input type="checkbox" value="' . $value['id'] . '">',
                $value['product_type'],
            ];

            $dataArr[] = $temparr;
        }

        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function postAssignFeed($action = null, $key = null)
    {
        $ids = Input::get('ids');
        $empty = Input::get('empty');

        ManageAttribute::updateAttributeFeedRelation($key, $ids);

        return response()->json(['flag' => 'success']);
    }
}
