<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Category;
use App\Model\Common;
use App\Model\Program;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Category\CategoryPermission;
use Auth;
use Input;
use Redirect;
use Request;
use Timezone;
use URL;
use Validator;

class CategoryManagementController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    public function __construct(Request $request)
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->theme_path = 'admin.theme';
    }

    public function getCategories($parent_slug = '')
    {
        $list_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::LIST_CATEGORY);
        $viewmode = Input::get('view', 'desktop');

        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.category.list_categories_iframe');
            $this->layout->footer = '';
        } else {
            if ($list_category == false) {
                return parent::getAdminError($this->theme_path);
            }
            if ($parent_slug == '') {
                $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/category.categories') => '',
                ];

                if (!is_null(Input::get('filter'))) {
                    $filter = Input::get('filter');
                } else {
                    $filter = 'ACTIVE';
                }
                $start_serv = 0;
                $length_page_serv = 10;

                if (!is_null(Input::get('start_serv')) && !is_null(Input::get('length_page_serv'))) {
                    $start_serv = (int)Input::get('start_serv');
                    $length_page_serv = (int)Input::get('length_page_serv');
                }
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $allparents = Category::getAdminParents();
                $this->layout->pagetitle = trans('admin/category.categories');
                $this->layout->pageicon = 'fa fa-folder';
                $this->layout->pagedescription = trans('admin/category.manage_categories');
                $this->layout->header = view('admin.theme.common.header');
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'category');
                $this->layout->footer = view('admin.theme.common.footer');
                $this->layout->content = view('admin.theme.category.list_categories')
                    ->with('filter', $filter)
                    ->with('start_serv', $start_serv)
                    ->with('length_page_serv', $length_page_serv);
            } else {
                $crumbs = [
                    trans('admin/dashboard.dashboard') => 'cp',
                    trans('admin/category.categories') => '',
                ];
                if (!is_null(Input::get('filter'))) {
                    $filter = Input::get('filter');
                } else {
                    $filter = 'ACTIVE';
                }
                $start_serv = 0;
                $length_page_serv = 10;

                if (!is_null(Input::get('start_serv')) && !is_null(Input::get('length_page_serv'))) {
                    $start_serv = (int)Input::get('start_serv');
                    $length_page_serv = (int)Input::get('length_page_serv');
                } else {
                    $start_serv = 0;
                    $length_page_serv = 10;
                }

                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $allparents = Category::getAdminParents();
                $this->layout->pagetitle = trans('admin/category.categories');
                $this->layout->pageicon = 'icon-folder-open';
                $this->layout->pagedescription = trans('admin/category.manage_categories');
                $this->layout->header = view('admin.theme.common.header');
                $this->layout->sidebar = view('admin.theme.common.sidebar')
                    ->with('mainmenu', 'category');
                $this->layout->footer = view('admin.theme.common.footer');
                $this->layout->content = view('admin.theme.category.list_sub_categories', ['parentslug' => $parent_slug])
                    ->with('filter', $filter)
                    ->with('start_serv', $start_serv)
                    ->with('length_page_serv', $length_page_serv);
            }
        }
    }

    public function getCategoryListAjax($parentslug = '')
    {
        $list_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::LIST_CATEGORY);
        $assign_feed = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ASSIGN_CHANNEL);

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];
        $viewmode = Input::get('view', 'desktop');

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['category_name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['status' => $order_by[0]['dir']];
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
        // $filter = strtolower($filter);
        if (!in_array($filter, ['ACTIVE', 'IN-ACTIVE', 'EMPTY'])) {
            $filter = 'all';
        }

        if ($viewmode == 'iframe') {
            $total_num_category = Category::getCategoryCount();
            $num_category_with_filter = Category::getCategoryCount($filter, $searchKey);
            $categories = Category::getAllFilteredCategoryWithPagination($filter, $start, $limit, $orderByArray, $searchKey);

            $dataArr = [];
            $delete = $add = $edit = '';
            $parentCatArr = null;
            $dataArr = [];
            /* Pick all parent categories */
            if (is_array($categories) && !empty($categories)) {
                foreach ($categories as $catkey => $value) {
                    $feed_rel = $assosiated_feeds_count = '';
                    $feed_rel = Category::getFeedsRelation($value['category_id']);
                    // construct the checkbox for a parent category
                    $parentCatArr = [
                        "<input type='checkbox' value=" . $value['category_id'] . " name='parentCategory' id=" . $value['category_id'] . ">",
                        html_entity_decode($value['category_name']),
                        Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                        $value['status'],
                    ];
                    $dataArr[] = $parentCatArr;
                    //check if child category exist , if yes then loop thorugh child and construct a child array
                    if (array_key_exists("children", $value) && !empty($value['children'])) {
                        $subCatArr = null;
                        $subCat = array_pull($value, "children");
                        if (is_array($subCat) && !empty($subCat)) {
                            foreach ($subCat as $key => $val) {
                                $subCatName = Category::getCategoryName($val['category_id']);
                                $subCatName = $subCatName[0];
                                //$categories[$catkey]['children'][$key] = array('subCatId'=>$val['category_id'],'subCategoryName'=> $subCatName);
                                //constructing the checkbox for the child array
                                $subCatArr = [
                                    "<input type='checkbox' value=" . $val['category_id'] . " style='margin-left:30px;' name ='sub_category' data-parentid=" . $value['category_id'] . ">",
                                    html_entity_decode($subCatName['category_name']),
                                    Timezone::convertFromUTC('@' . $subCatName['created_at'], Auth::user()->timezone, config('app.date_format')),
                                    $subCatName['status'],
                                ];
                                $dataArr[] = $subCatArr;
                            }
                        }
                    } else {
                        // $res[] = $parentCatArr;
                    }
                }
            }
            $finaldata = [
                'recordsTotal' => $total_num_category,
                'recordsFiltered' => $num_category_with_filter,
                'data' => $dataArr,
            ];
        } else {
            $delete = $add = $edit = '';
            $delete_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::DELETE_CATEGORY);
            $add_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ADD_CATEGORY);
            $edit_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::EDIT_CATEGORY);

            if ($list_category == false) {
                return parent::getAdminError($this->theme_path);
            }

            if ($parentslug == '') {
                $total_num_parent_category = Category::getCategoryCount();
                $num_category_with_filter = Category::getCategoryCount($filter, $searchKey);
                $categories = Category::getFilteredCategoryWithPagination($filter, $start, $limit, $orderByArray, $searchKey);
                // echo "<pre>"; print_r($categories); die;

                $dataArr = [];

                foreach ($categories as $value) {
                    $feed_rel = $assosiated_feeds_count = '';
                    $feed_rel = Category::getFeedsRelation($value['category_id']);
                    if (!empty($feed_rel) && isset($feed_rel[0]['relations']['assigned_feeds'])) {
                        $assigned_content_feed_ids = Program::whereIn(
                            "program_id",
                            $feed_rel[0]['relations']['assigned_feeds']
                        )->where("program_type", "content_feed")
                        ->where("program_sub_type", "single")->pluck("program_id")->toArray();

                        $assosiated_feeds_count = count($assigned_content_feed_ids);
                    }

                    if ($delete_category != false) {
                        if (empty($value['children'])) {
                            if ($assosiated_feeds_count == '') {
                                $delete = '<a class="btn btn-circle show-tooltip deletecategory" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('cp/categorymanagement/remove-parent-category/' . $value['category_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-trash-o"></i></a>';
                                $checkbox = '<input type="checkbox" value="' . $value['category_id'] . '">';
                            } else {
                                $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/category.cat_and_channel_cant_delete') . '"?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '><i class="fa fa-trash-o"></i></a>';
                                $checkbox = '<input type="checkbox"  disabled="disabled" />';
                            }
                        } else {
                            $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/category.cat_and_channel_cant_delete') . '"?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '  ><i class="fa fa-trash-o"></i></a>';
                            $checkbox = '<input type="checkbox"  disabled="disabled" />';
                        }
                    } else {
                        $delete = '';
                        $checkbox = '<input type="checkbox"  disabled="disabled" />';
                    }

                    if ($add_category != false) {
                        $add = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/category.add_sub_category') . '" href="' . URL::to('cp/categorymanagement/add-children/' . $value['slug']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-plus"></i></a>';
                    } else {
                        $add = '';
                    }

                    if ($edit_category != false) {
                        $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('cp/categorymanagement/edit-category/' . $value['slug']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-edit"></i></a>';
                    } else {
                        $edit = '';
                    }

                    $temparr = [
                        $checkbox,
                        '<a href="' . URL::to('cp/categorymanagement/categories/' . $value['slug']) . '">' . html_entity_decode($value['category_name']) . '</a>',
                        Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                        $value['status'],
                        $add . ' ' . $edit . ' ' . $delete,
                    ];
                    if ($viewmode != 'iframe') {
                        if ($assign_feed == true) {
                            $feeds_count = "<a href='" . URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&subtype=single&from=category&relid=' . $value['category_id']) . "' class='categoryrel badge badge-grey' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . '"' . html_entity_decode($value['category_name']) . '"' . "</b>' data-json=''>" . 0 . '</a>';
                        } else {
                            $feeds_count = "<a href='' onclick='return false' title='" . trans('admin/category.no_assign_permission') . "' class='show-tooltip badge badge-grey' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . '"' . html_entity_decode($value['category_name']) . '"' . "</b>' data-json=''>" . 0 . '</a>';
                        }
                        if (!empty($assigned_content_feed_ids) && isset($assigned_content_feed_ids)
                            && ($assosiated_feeds_count > 0)) {
                            if ($assign_feed == true) {
                                $feeds_count = "<a href='" . URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&subtype=single&from=category&relid=' . $value['category_id']) . "' class='categoryrel badge badge-success' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . '"' . $value['category_name'] . '"' . "</b>' data-json='" . json_encode($assigned_content_feed_ids) . "'>" . $assosiated_feeds_count . '</a>';
                            } else {
                                $feeds_count = "<a href='' onclick='return false' title='" . trans('admin/category.no_assign_permission') . "' class='show-tooltip badge badge-success' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . html_entity_decode($value['category_name']) . '"' . "</b>' data-json='" . json_encode($assigned_content_feed_ids) . "'>" . $assosiated_feeds_count . '</a>';
                            }
                        }
                        array_splice($temparr, 3, 0, [$feeds_count]);
                    }
                    $dataArr[] = $temparr;
                }

                $finaldata = [
                    'recordsTotal' => $total_num_parent_category,
                    'recordsFiltered' => $num_category_with_filter,
                    'data' => $dataArr,
                ];
            } else {
                $childrens = Category::getAdminChildrencode($parentslug);

                $chilecounter = 0;
                $totalchildren = count($childrens);

                $children = [];
                if (isset($childrens[$chilecounter]['category_id'])) {
                    while ($chilecounter < $totalchildren) {
                        $children[] = $childrens[$chilecounter]['category_id'];
                        ++$chilecounter;
                    }
                }

                $num_sub_category_with_filter = Category::getAdminChildrenCount($children, $filter, $searchKey);
                $subcategories = Category::getAdminChildren($children, $filter, $start, $limit, $orderByArray, $searchKey);
                $total_num_sub_category = count($subcategories);

                $dataArr = [];

                foreach ($subcategories as $value) {
                    $feed_rel = $assosiated_feeds_count = '';
                    $feed_rel = Category::getFeedsRelation($value['category_id']);
                    if (!empty($feed_rel) && isset($feed_rel[0]['relations']['assigned_feeds'])) {
                        $assigned_content_feed_ids = $assigned_content_feed_ids = Program::whereIn(
                            "program_id",
                            $feed_rel[0]['relations']['assigned_feeds']
                        )->where("program_type", "content_feed")
                            ->where("program_sub_type", "single")->pluck("program_id")->toArray();

                        $assosiated_feeds_count = count($assigned_content_feed_ids);
                    }

                    if ($edit_category != false) {
                        $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('cp/categorymanagement/edit-category/' . $value['slug'] . '/' . $value['parents']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '&parentslug=' . $parentslug . '"><i class="fa fa-edit"></i></a>';
                    } else {
                        $edit = '';
                    }
                    if ($delete_category == true) {
                        if ($assosiated_feeds_count == '') {
                            $delete = '<a class="btn btn-circle show-tooltip deletesubcategory" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('cp/categorymanagement/remove-child-category/' . $parentslug . '/' . $value['category_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-trash-o"></i></a>';
                            $checkbox = '<input type="checkbox" value="' . $value['category_id'] . '">';
                        } else {
                            $checkbox = '<input type="checkbox"  disabled="disabled" />';
                            $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/category.cant_delete_since_channel_assigned') . '" ><i class="fa fa-trash-o"></i></a>';
                        }
                    } else {
                        $checkbox = '<input type="checkbox"  disabled="disabled" />';
                        $delete = '';
                    }

                    $temparr = [
                        $checkbox,
                        html_entity_decode($value['category_name']),
                        Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                        $value['status'],
                        $edit . ' ' . $delete,
                    ];
                    if ($viewmode != 'iframe') {
                        $feed_rel = '';
                        $feed_rel = Category::getFeedsRelation($value['category_id']);
                        if ($assign_feed == true) {
                            $feeds_count = "<a href='" . URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&subtype=single&from=category&relid=' . $value['category_id']) . "' class='categoryrel badge badge-grey' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . '"' . html_entity_decode($value['category_name']) . '"' . "</b>' data-json=''>" . 0 . '</a>';
                        } else {
                            $feeds_count = "<a href='' onclick='return false' title='" . trans('admin/category.no_assign_permission') . "' class=' show-tooltip badge badge-grey' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . '"' . html_entity_decode($value['category_name']) . '"' . "</b>' data-json=''>" . 0 . '</a>';
                        }
                        if (!empty($feed_rel) && isset($assigned_content_feed_ids)) {
                            if ($assign_feed == true) {
                                $feeds_count = "<a href='" . URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&subtype=single&from=category&relid=' . $value['category_id']) . "' class='categoryrel badge badge-success' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . '"' . html_entity_decode($value['category_name']) . '"' . "</b>' data-json='" . json_encode($assigned_content_feed_ids) . "'>" . $assosiated_feeds_count . '</a>';
                            } else {
                                $feeds_count = "<a href='' onclick='return false' title='" . trans('admin/category.no_assign_permission') . "' class=' show-tooltip badge badge-success' data-key='" . $value['category_id'] . "' data-info='feed' data-text='Assign " . trans('admin/program.program') . ' to <b>' . '"' . html_entity_decode($value['category_name']) . '"' . "</b>' data-json='" . json_encode($assigned_content_feed_ids) . "'>" . $assosiated_feeds_count . '</a>';
                            }
                        }
                        array_splice($temparr, 3, 0, [$feeds_count]);
                    }

                    $dataArr[] = $temparr;
                }
                $finaldata = [
                    'recordsTotal' => $total_num_sub_category,
                    'recordsFiltered' => $num_sub_category_with_filter,
                    'data' => $dataArr,
                ];
            }
        }

        return response()->json($finaldata);
    }

    public function getAddParent()
    {
        $add_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ADD_CATEGORY);
        if ($add_category == false) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/category.categories') => 'categorymanagement/categories',
            trans('admin/category.add_category') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

        $this->layout->pagetitle = trans('admin/category.add_new_category');
        $this->layout->pageicon = 'fa fa-folder';
        $this->layout->pagedescription = trans('admin/category.add_new_category');

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'category');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.category.add_parent_category');
    }

    public function postAddParent()
    {
        Input::flash();

        $rules = [
            'category_name' => array_get($this->getCategoryValidationRules(), 'name'),
            'category_desc' => array_get($this->getCategoryValidationRules(), 'description'),
        ];

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->fails()) {
            return Redirect::back()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $parent_slug = Category::getCategorySlug(Input::get('category_name'));

            $all_parent_slugs = Category::getCategoriesSlug();

            if (in_array($parent_slug, $all_parent_slugs)) {
                $error = trans('admin/category.unique_category_alert');

                return Redirect::back()->with('category_exist', $error);
            }
            Category::addCategory(Input::all());

            $success = trans('admin/category.add_category_success');

            return redirect('cp/categorymanagement/success/' . $parent_slug)->with('success', $success);
        }
    }

    public function getSuccess($parent_slug = '', $cat_slug = '')
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/category.categories') => 'categorymanagement/categories',
            trans('admin/category.success') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

        $this->layout->pagetitle = trans('admin/category.add_category');
        $this->layout->pageicon = 'fa fa-folder';
        $this->layout->pagedescription = trans('admin/category.add_category');
        if ($cat_slug == '') {
            $slug = $parent_slug;
        } else {
            $slug = $cat_slug;
        }
        $cat_info = Category::getCategoyInfo($slug);

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'category');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.category.action_success', ['par_slug' => $parent_slug, 'cat_slug' => $cat_slug, 'cat_id' => $cat_info[0]['category_id']]);
    }

    public function getAddChildren($parentslug)
    {
        $add_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ADD_CATEGORY);
        if ($add_category == false) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/category.categories') => 'categorymanagement/categories',
            trans('admin/category.add_category') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

        $this->layout->pagetitle = trans('admin/category.add_new_sub_cat');
        $this->layout->pageicon = 'fa fa-folder';
        $this->layout->pagedescription = trans('admin/category.add_new_sub_cat');

        $cat_info = Category::where('slug', '=', $parentslug)->get(['category_name', 'category_id', 'slug'])->toArray();

        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ACTIVE';
        }

        if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
            $start_serv = (int)Input::get('start');
            $length_page_serv = (int)Input::get('limit');
        } else {
            $start_serv = 0;
            $length_page_serv = 10;
        }

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'category');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.category.add_child_category', ['parentslug' => $parentslug, 'cat_info' => $cat_info[0]])
            ->with('filter', $filter)
            ->with('start_serv', $start_serv)
            ->with('length_page_serv', $length_page_serv);
    }

    public function postAddChildren()
    {
        Input::flash();

        $rules = [
            'sub_category_name' => array_get($this->getCategoryValidationRules(), 'name'),
            'category_desc' => array_get($this->getCategoryValidationRules(), 'description'),
            'cat_image' => 'mimes:jpeg,jpg,bmp,png|max:200',
        ];

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->fails()) {
            return Redirect::back()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
        
            $cat_name = trim(Input::get('sub_category_name'));
            $cat_desc = trim(Input::get('category_desc'));
            $parent_slug = trim(Input::get('parent_slug'));
            $parent_id = trim(Input::get('parent_id'));
            $cat_slug = Category::getCategorySlug($cat_name);
            $cat_status = trim(Input::get('status'));
            $cat_id = Category::getCategoryID();
           
            $all_sub_cat_slugs = Category::getCategoriesSlug($parent_id);

            if (in_array($cat_slug, $all_sub_cat_slugs)) {
                $error = trans('admin/category.unique_category_alert');

                return Redirect::back()->with('category_exist', $error);
            }

            Category::where('slug', '=', $parent_slug)->where('Children', '!=', 'null')->push(
                'children',
                ['category_id' => $cat_id,
                    'created_date' => time()]
            );

            Category::insert(
                [
                    'category_id' => $cat_id,
                    'category_name' => htmlentities($cat_name, ENT_QUOTES),
                    'category_description' => htmlentities($cat_desc, ENT_QUOTES),
                    'slug' => $cat_slug,
                    'custom' => true,
                    'parents' => (int)$parent_id,
                    'feature_image_file' => null,
                    'created_at' => time(),
                    'updated_at' => time(),
                    'status' => $cat_status,
                    'count' => 0,
                ]
            );
            $success = trans('admin/category.add_sub_category_success');

            return redirect('cp/categorymanagement/success/' . $parent_slug . '/' . $cat_slug)->with('success', $success);
        }
    }

    public function getEditCategory($slug, $parent = null)
    {
        $edit_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::EDIT_CATEGORY);
        if ($edit_category == false) {
            return parent::getAdminError($this->theme_path);
        }

        if ($parent != '') {
            $parent = (int)$parent;
        }
        $cat_information = Category::where('slug', '=', $slug)->where('parents', '=', $parent)->get()->toArray();

        $first_level_parent = '';
        if ($slug != '' && $parent != '' && empty($cat_information[0]['children'])) {
            $first_level_parent = Category::getAdminParents();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/category.categories') => 'categorymanagement/categories',
            trans('admin/category.edit_category') => '',
        ];

        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ACTIVE';
        }

        if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
            $start_serv = (int)Input::get('start');
            $length_page_serv = (int)Input::get('limit');
        } else {
            $start_serv = 0;
            $length_page_serv = 10;
        }
        if (!is_null(Input::get('parentslug'))) {
            $parentslug = Input::get('parentslug');
        } else {
            $parentslug = '';
        }

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

        $pagetitle = trans('admin/category.edit_category');
        $pagedescription = trans('admin/category.edit_category');
        if ($parent != null) {
            $pagetitle = trans('admin/category.edit_new_sub_cat');
            $pagedescription = trans('admin/category.edit_new_sub_cat');
        }

        $this->layout->pagetitle = $pagetitle;
        $this->layout->pageicon = 'icon-folder-open';
        $this->layout->pagedescription = $pagedescription;

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'category');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.category.edit_category', ['cat_info' => $cat_information[0], 'categories' => $first_level_parent, 'parent_slug' => $parent])
            ->with('filter', $filter)
            ->with('start_serv', $start_serv)
            ->with('slug', $parentslug)
            ->with('length_page_serv', $length_page_serv);
    }

    public function postEditCategory()
    {
        $parent_id = trim(Input::get('parent_id'));
        $cat_id = trim(Input::get('cat_code'));
        $cat_name = trim(Input::get('category_name'));
        $cat_slug = Category::getCategorySlug($cat_name);

        if ($parent_id == '') {
            $cat_slugs = Category::getCategoriesSlug($parent = '', $cat_id);

            if (in_array($cat_slug, $cat_slugs)) {
                $error = trans('admin/category.unique_category_alert');

                return Redirect::back()->with('category_exist', $error);
            }
            $redirect = '/cp/categorymanagement/categories';
            $success = trans('admin/category.edit_category_success');
        } else {
            $cat_slugs = Category::getCategoriesSlug($parent_id, $cat_id);

            if (in_array($cat_slug, $cat_slugs)) {
                $error = trans('admin/category.unique_category_alert');

                return Redirect::back()->with('category_exist', $error);
            }

            $parent_slug = Category::where('category_id', '=', (int)Input::get('parent_id'))->where('parents', '=', null)->value('slug');
            $redirect = '/cp/categorymanagement/categories/' . $parent_slug;
            $success = trans('admin/category.edit_sub_category_success');
        }

        $rules = [
            'category_name' => array_get($this->getCategoryValidationRules(), 'name'),
            'category_desc' => array_get($this->getCategoryValidationRules(), 'description'),
            'cat_image' => 'mimes:jpeg,jpg,bmp,png|max:200',
        ];

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->fails()) {
            return Redirect::back()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $parent_id = trim(Input::get('parent_id'));
            $cat_desc = trim(Input::get('category_desc'));
            $newpar_id = trim(Input::get('category'));
            $old_slug = trim(Input::get('old_slug'));
            $cat_status = trim(Input::get('status'));

            if ($newpar_id != '' && (int)$newpar_id != (int)$parent_id) {
                $child_info = Category::where('category_id', '=', (int)$newpar_id)->push('children', ['category_id' => (int)$cat_id, 'created_date' => time()]);
                Category::where('category_id', '=', (int)$parent_id)->pull('children', ['category_id' => (int)$cat_id]);
            }
            if ($parent_id != '') {
                $parent_id = (int)$parent_id;
            } else {
                $parent_id = null;
            }
            if ($newpar_id != '') {
                $newpar_id = (int)$newpar_id;
            } else {
                $newpar_id = null;
            }

            if ($cat_status != '') {
                Category::where('slug', '=', $old_slug)->where('parents', '=', $parent_id)->update(
                    [
                        'category_name' => htmlentities($cat_name, ENT_QUOTES),
                        'category_description' => htmlentities($cat_desc, ENT_QUOTES),
                        'updated_at' => time(),
                        'parents' => $newpar_id,
                        'slug' => $cat_slug,
                        'feature_image_file' => null,
                        'status' => $cat_status,
                    ]
                );
            } else {
                Category::where('slug', '=', $old_slug)->where('parents', '=', $parent_id)->update(
                    [
                        'category_name' => htmlentities($cat_name, ENT_QUOTES),
                        'category_description' => htmlentities($cat_desc, ENT_QUOTES),
                        'updated_at' => time(),
                        'parents' => $newpar_id,
                        'feature_image_file' => null,
                        'slug' => $cat_slug,
                    ]
                );
            }

            /* Cache Creation */
            // $category_cache_path=Config::get('app.cache_path').'category_cache.txt';

            //     $parentcategories = Category::getParents();
            //     $categories_serialized = serialize($parentcategories);
            //     $fp = fopen($category_cache_path, 'w');
            //     fwrite($fp, $categories_serialized, strlen($categories_serialized));
            //     fclose($fp);
            /* End of Cache Creation */
            return redirect($redirect)
                ->with('success', $success);
        }
    }

    public function getRemoveParentCategory($id = null)
    {
        $delete_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::DELETE_CATEGORY);
        if ($delete_category == false) {
            return parent::getAdminError($this->theme_path);
        }

        Category::deleteParentCategory($id);

        $success = trans('admin/category.remove_category_success');

        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '2 desc');
        $filter = Input::get('filter', 'ALL');

        $totalRecords = Category::getAllCategoryCount();
        if ($totalRecords <= $start) {
            $start -= $limit;
            if ($start < 0) {
                $start = 0;
            }
        }
        $redirect = '/cp/categorymanagement/categories?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by;

        return redirect($redirect)->with('success', $success);
    }

    public function getRemoveChildCategory($parent_slug = null, $id = null)
    {
        $delete_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::DELETE_CATEGORY);
        if ($delete_category == false) {
            return parent::getAdminError($this->theme_path);
        }

        Category::deleteChildCategory($parent_slug, $id);

        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '2 desc');
        $filter = Input::get('filter', 'ALL');

        $totalRecords = count(Category::getAdminChildrencode($parent_slug));
        if ($totalRecords <= $start) {
            $start -= $limit;
            if ($start < 0) {
                $start = 0;
            }
        }
        $redirect = '/cp/categorymanagement/categories/' . $parent_slug . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by;

        $success = trans('admin/category.remove_child_category_success');

        return redirect($redirect)->with('success', $success);
    }

    public function postBulkDelete($parent = '')
    {
        $delete_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::DELETE_CATEGORY);
        if ($delete_category == false) {
            return parent::getAdminError($this->theme_path);
        }
        if ($parent == '') {
            $categories_checked = Input::get('ids');
            $categories_checked = explode(',', trim($categories_checked, ','));

            foreach ($categories_checked as $category) {
                Category::deleteParentCategory($category);
            }
            $success = trans('admin/category.remove_multiple_category_success');

            return redirect('cp/categorymanagement/categories')->with('success', $success);
        } else {
            $categories_checked = Input::get('ids');
            $categories_checked = explode(',', trim($categories_checked, ','));
            foreach ($categories_checked as $category) {
                Category::deleteChildCategory($parent, $category);
            }
            $success = trans('admin/category.remove_multiple_sub_category_success');

            return redirect('/cp/categorymanagement/categories/' . $parent)->with('success', $success);
        }
    }

    public function postAssignFeed($action = null, $key = null)
    {
        $ids = Input::get('ids');
        $empty = Input::get('empty');
        //Category::UpdateCategoryFeedRelation($key, $ids);
        $category = Category::getFeedsRelation($key);
        $category = $category[0];
        $Fids = [];
        if (isset($category['relations']['assigned_feeds']) && !empty($category['relations']['assigned_feeds'])) {
            if ($Fids) {
                $Fids = array_map('intval', explode(',', $ids));
            } else {
                $Fids = [];
            }
            $diff_arr = array_values(array_diff($category['relations']['assigned_feeds'], $Fids));
            if (!empty($diff_arr)) {
                foreach ($diff_arr as $diff) {
                    $a = array_map('intval', explode(',', $key));
                    $diff_pro = Program::getProgramDetailsByID($diff)->toArray();
                    Program::removeFeedCategories($diff_pro['program_id'], $a);
                }
            }
        }
        Category::updateCategoryFeedRelation($key, $ids);
        $msg = trans('admin/program.channel_assigned_success');

        return response()->json(['flag' => 'success', 'message' => $msg]);
    }

    public function getListCategories($parent_slug = '')
    {

        $viewmode = Input::get('view', 'desktop');
        $this->layout->breadcrumbs = '';
        $this->layout->pagetitle = '';
        $this->layout->pageicon = '';
        $this->layout->pagedescription = '';
        $this->layout->header = '';
        $this->layout->sidebar = '';
        $this->layout->content = view('admin.theme.category.list_product_categories_iframe');
        $this->layout->footer = '';
    }

    public function getCategoryData()
    {
        $search = Input::get('category');
        $s_data = Category::getCategoryData($search);
        return response()->json([
            'status' => !empty($s_data),
            'data' => array_map(function ($element) {
                return html_entity_decode($element);
            }, $s_data),
        ]);
    }

    private function getCategoryValidationRules()
    {
        $rules['name'] = 'Required|Regex:/^[a-zA-Z0-9 !@&% -_]+$/|max:50';
        $rules['description'] = 'Regex:/^[a-zA-Z0-9 !@&% -_\s\r\n]+$/';

        return $rules;
    }
}
