<?php
namespace App\Http\Controllers\Admin\CustomFields;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Services\CustomFields\ICustomService;
use Auth;
use Input;
use Request;
use Timezone;
use URL;
use Validator;

class CustomFieldsController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    protected $customSer;

    public function __construct(Request $request, ICustomService $customService)
    {
        $this->theme_path = 'admin.theme';
        $this->customSer = $customService;

        // Stripping all html tags from the request body
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
    }

    public function getIndex()
    {
        /*if (!Common::checkPermission('admin', 'upcomingcourses', 'upcomingcourses')) {
            return parent::getAdminError($this->theme_path);
        }*/
        if (!is_null(Input::get('filter'))) {
            return redirect('cp/customfields')->with('filter', Input::get('filter'));
        }
        
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/customfields.manage_site') => 'customfields',
            trans('admin/customfields.customfields') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/customfields.manage_custom_field');
        $this->layout->pageicon = 'glyphicon glyphicon-th-list';
        $this->layout->pagedescription = trans('admin/customfields.manage_custom_field');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'customfields');

        $this->layout->content = view('admin.theme.customfields.listcustomfields');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getCustomfieldsAjax()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $filter = Input::get('filter');

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }

        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        $totalRecords = $this->customSer->getCustomFieldsCount($filter);
        $filteredRecords = $this->customSer->getCustomFieldsCount($filter, $searchKey);
        $filtereddata = $this->customSer->getCustomFields($filter, $searchKey, $start, $limit);

        $dataArr = [];

        foreach ($filtereddata as $key => $value) {
            $exist = $this->customSer->getValidateCustomFieldValue($filter, $value['fieldname']);
            if ($exist) {
                $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/customfields.action_delete_error') . '" ><i class="fa fa-trash-o"></i></a>';
            } else {
                $delete = '<a class="btn btn-circle show-tooltip deletefield" title="' . trans('admin/customfields.action_delete') . '" href="' . URL::to('cp/customfields/delete-field/' . $value['id'] . '?filter=' . $filter) . '" ><i class="fa fa-trash-o"></i></a>';
            }

            $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/customfields.action_edit') . '" href="' . URL::to('cp/customfields/edit-field/' . $value['id'] . '?filter=' . $filter) . '" ><i class="fa fa-edit"></i></a>';

            $temparr = [
                "<div>" . $value['fieldname'] . "</div>",
                $value['mark_as_mandatory'],
                Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_format')),
                $value['status'],
                $edit . $delete,
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

    public function getAddField()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/customfields.manage_custom_fields') => 'customfields',
            trans('admin/customfields.add_custom_field') => '',
        ];

        $filter = Input::get('filter');
        $tab = trans('admin/customfields.' . $filter);
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Add ' . $tab;
        $this->layout->pageicon = 'glyphicon glyphicon-th-list';
        $this->layout->pagedescription = trans('admin/customfields.add_custom_field');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'customfields');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.customfields.addcustomfields')->with('filter', $filter);
    }

    public function postAddField()
    {
        Input::flash();
        $filter = Input::get('filter');

        if (in_array($filter, ['userfields', 'channelfields', 'packagefields', 'coursefields', 'productfields'])) {
            switch ($filter) {
                case 'userfields':
                    $program_type = 'user';
                    $program_sub_type = '';
                    break;

                case 'channelfields':
                    $program_type = 'content_feed';
                    $program_sub_type = 'single';
                    break;

                case 'packagefields':
                    $program_type = 'content_feed';
                    $program_sub_type = 'collection';
                    break;

                case 'coursefields':
                    $program_type = 'course';
                    $program_sub_type = 'single';
                    break;

                case 'productfields':
                    $program_type = 'product';
                    $program_sub_type = '';
                    break;

                default:
                    break;
            }

            $rules = [
                'fieldname' => 'Required|Min:3|Max:30|unique:customfields,fieldname,NULL,id,program_type,' . $program_type . ',program_sub_type,' . $program_sub_type . '|unique_case:' . $filter,
                'fieldlabel' => 'Required|Min:3|Max:150'
            ];

            Validator::extend('unique_case', function ($attribute, $value, $parameters) {
                $fieldnames = $this->customSer->getFieldNames($parameters[0]);
                $fieldnames = array_map('strtolower', $fieldnames);

                $regex = "/^([a-zA-Z])([a-zA-Z0-9_]+)$/";
                if (!preg_match($regex, strval($value))) {
                    return false;
                }

                if (is_array($fieldnames) && in_array(strtolower($value), $fieldnames)) {
                    return false;
                }

                $field_exist = $this->customSer->getValidateCustomField($parameters[0], $value);
                if ($field_exist > 0) {
                    return false;
                }

                return true;
            });

            $messages = [
                'unique_case' => trans('admin/customfields.unique_error'),
            ];

            $validation = Validator::make(Input::all(), $rules, $messages);

            if ($validation->fails()) {
                return redirect('cp/customfields/add-field?filter=' . $filter)->withInput()->withErrors($validation);
            } elseif ($validation->passes()) {
                $input = Input::all();
                $this->customSer->insertCustomField($input, $filter, $program_type, $program_sub_type);
                Input::flush();
                return redirect('cp/customfields')
                    ->with('success', trans('admin/customfields.addcustomfield_success'))->with('filter', $filter);
            }
        } else {
            $error = trans('admin/customfields.missing_params');
            return redirect('cp/customfields')->with('error', $error);
        }
    }

    public function getEditField($id)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/customfields.manage_custom_fields') => 'customfields',
            trans('admin/customfields.edit_custom_field') => '',
        ];

        $filter = Input::get('filter');
        $tab = trans('admin/customfields.' . $filter);
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = 'Edit ' . $tab;
        $this->layout->pageicon = 'glyphicon glyphicon-th-list';
        $this->layout->pagedescription = trans('admin/customfields.edit_custom_field');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'web')
            ->with('submenu', 'customfields');
        $this->layout->footer = view('admin.theme.common.footer');
        $data = $this->customSer->editCustomField($id, $filter);
        if (isset($data) && !empty($data)) {
            $data = $data[0];
        } else {
            $data = [];
        }

        $field_exist = $this->customSer->getValidateCustomFieldValue($filter, $data['fieldname']);

        $this->layout->content = view('admin.theme.customfields.editcustomfields')->with('filter', $filter)->with('data', $data)->with('field_exist', $field_exist);
    }

    public function postEditField($id)
    {
        Input::flash();
        $filter = Input::get('filter');
        $field = Input::get('oldfield');
        $program_type = Input::get('program_type');
        $program_sub_type = Input::get('program_sub_type');

        if (in_array($filter, ['userfields', 'channelfields', 'packagefields', 'coursefields', 'productfields'])) {
            $record = $this->customSer->getCustomFieldById($id);
            $count = $this->customSer->getValidateCustomFieldValue($filter, $record[0]['fieldname']);

            Validator::extend('statuscheck', function ($attribute, $value, $parameters) {
                $status = Input::get('status');
                if ($status == 'ACTIVE') {
                    return true;
                }
                if ($status == 'IN-ACTIVE' && $parameters[0] > 0) {
                    return false;
                }
                return true;
            });

            $messages = [
                'statuscheck' => trans('admin/customfields.cannot_inactive_field'),
                'unique_case' => trans('admin/customfields.unique_error'),
            ];

            if ($count > 0) {
                $rules = [
                    'fieldlabel' => 'Required|Min:3|Max:150',
                    'status' => 'statuscheck:' . $count . '',
                ];
            } else {
                $rules = [
                    'fieldname' => 'Required|Min:3|Max:30|alpha_dash|unique:customfields,fieldname,' . $field . ',fieldname,program_type,' . $program_type . ',program_sub_type,' . $program_sub_type . '|unique_case:' . $filter . ',' . $id,
                    'fieldlabel' => 'Required|Min:3|Max:150',
                    'status' => 'statuscheck:' . $count . '',
                ];
            }

            Validator::extend('unique_case', function ($attribute, $value, $parameters) {
                $fieldnames = $this->customSer->getFieldNamesExcept($parameters[0], $parameters[1]);
                $fieldnames = array_map('strtolower', $fieldnames);

                $regex = "/^(?=.*[a-zA-Z])([a-zA-Z0-9_]+)$/";
                if (!preg_match($regex, strval($value))) {
                    return false;
                }

                if (is_array($fieldnames) && in_array(strtolower($value), $fieldnames)) {
                    return false;
                }

                $field_exist = $this->customSer->getValidateCustomFieldValue($parameters[0], $value);
                if ($field_exist) {
                    return false;
                }

                return true;
            });

            $validation = Validator::make(Input::all(), $rules, $messages);

            if ($validation->fails()) {
                return redirect('cp/customfields/edit-field/' . $id . '?filter=' . $filter)->withInput()->withErrors($validation);
            } elseif ($validation->passes()) {
                $input = Input::all();
                $this->customSer->updateCustomField($input, $id, $filter, $field);
                Input::flush();
                return redirect('cp/customfields')
                    ->with('success', trans('admin/customfields.editcustomfield_success'))->with('filter', $filter);
            }
        } else {
            $error = trans('admin/customfields.missing_params');
            return redirect('cp/customfields')->with('error', $error);
        }
    }

    public function getDeleteField($id)
    {
        $filter = Input::get('filter');
        $result = $this->customSer->deleteCustomField($id, $filter);
        if ($result) {
            $success = trans('admin/customfields.delete_success');
            return redirect('cp/customfields')->with('success', $success)->with('filter', $filter);
        } else {
            $error = trans('admin/customfields.delete_error');
            return redirect('cp/customfields')->with('error', $error)->with('filter', $filter);
        }
    }
}
