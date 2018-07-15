<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\RolesAndPermissions\RolePermission;
use App\Model\Common;
use App\Model\Role;
use App\Model\User;
use App\Services\Module\IModuleService;
use Auth;
use Input;
use Redirect;
use Request;
use Session;
use Timezone;
use URL;
use Validator;

class RoleManagementController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    /**
     * @var IModuleService
     */
    private $moduleService;

    /**
     * Display a listing of the resource
     * @param Request $request
     * @param IModuleService $moduleService
     */
    public function __construct(Request $request, IModuleService $moduleService)
    {
        parent::__construct();
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->theme_path = 'admin.theme';
        $this->moduleService = $moduleService;
    }

    public function getUserRoles()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.manage_user') => '',
            trans('admin/role.user_roles') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/role.roles');
        $this->layout->pageicon = 'fa fa-user-md';
        $this->layout->pagedescription = trans('admin/role.manage_user_roles');
        $filter = Input::get('filter');

        $user_roles = Role::getAllUserRoles($filter);

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'role');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.roles.list_roles', ['role' => $user_roles]);
    }

    public function getRoleListAjax()
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::LIST_ROLE)) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]
            );
        }

        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '5') {
                $orderByArray = ['updated_at' => $order_by[0]['dir']];
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
        if (!in_array($filter, ['ACTIVE', 'IN-ACTIVE'])) {
            $filter = 'all';
        }

        $total_num_role = Role::getRolesCount();
        $num_roles_with_filter = Role::getRolesCount($filter, $searchKey);
        $roles = Role::getFilteredRolesWithPagination($filter, $start, $limit, $orderByArray, $searchKey);
        $delete = $edit = '';
        $dataArr = [];

        $edit_role = has_admin_permission(ModuleEnum::ROLE, RolePermission::EDIT_ROLE);
        $delete_role = has_admin_permission(ModuleEnum::ROLE, RolePermission::DELETE_ROLE);

        foreach ($roles as $value) {
            $checkbox = '';
            $assigned = $this->roleService->isRoleAssignedToUser($value['rid']);

            if ((isset($value['system_role']) && $value['system_role'] == true) || $assigned) {
                $checkbox = '<input type="checkbox" value=" " id=" " disabled="disabled" />';
            } else {
                $checkbox = '<input type="checkbox" value="' . $value['rid'] . '">';
            }
            if ($edit_role == true) {
                if (isset($value['system_role']) && $value['system_role'] == true) {
                    $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/role.no_permission_to_edit_role') . '"><i class="fa fa-edit"></i></a>';
                } else {
                    $edit = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('cp/rolemanagement/edit-role/' . $value['rid']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-edit"></i></a>';
                }
            }
            if ($delete_role == true) {
                if (isset($value['system_role']) && $value['system_role'] == true) {
                    $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/role.cant_delete_system_role') . '"  ><i class="fa fa-trash-o"></i></a>';
                } elseif ($assigned) {
                    $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/role.no_permission_to_delete_role') . '"><i class="fa fa-trash-o"></i></a>';
                } else {
                    $delete = '<a class="btn btn-circle show-tooltip deleterole" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('cp/rolemanagement/delete-role/' . $value['rid']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '"><i class="fa fa-trash-o"></i></a>';
                }
            }

            $parent = 'No Parent';
            if ($value['parent'] != '') {
                $role = Role::getRoleinfo($value['parent']);
                if (isset($role) && !empty($role)) {
                    $parent = $role[0]['name'];
                }
            }
            $updated_at = 'N/A';
            if ($value['updated_at'] != null) {
                $updated_at = Timezone::convertFromUTC('@' . $value['updated_at'], Auth::user()->timezone, config('app.date_format'));
            }

            $temparr = [
                $checkbox,
                $value['name'],
                $parent,
                implode(",", array_column($value["contexts"], "name")),
                Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                $updated_at,
                $value['status'],
                $edit . ' ' . $delete,
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

    public function getAddRole()
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::ADD_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/role.user_roles') => 'rolemanagement/user-roles',
            trans('admin/role.add_role') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

        $this->layout->pagetitle = trans('admin/role.roles');
        $this->layout->pageicon = 'fa fa-user-md';
        $this->layout->pagedescription = trans('admin/role.add_new_role');

        $contexts = $this->roleService->getContexts();
        $roles = $this->roleService->getInheritableRoles(true);

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'role');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view(
            'admin.theme.roles.add_role',
            [
                "roles" => $roles,
                "contexts" => $contexts,
            ]
        );
    }
    /**
     *Adding custome roles   
     */
    public function postAddRole()
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::ADD_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        Input::flash();
        Session::forget('edit');
        $rules = [
            'parent_role' => '',
            'role_name' => 'Required|unique:roles|Max:50|Regex:/^[A-Za-z][A-Za-z0-9 \-]+$/',
            'description' => "Max:200|Regex:/^([a-zA-Z0-9:.',\-@#&()\/+\n- ])+$/",
        ];

        $messages = [];
        $messages += [
            'role_name.regex' => trans('admin/role.role_name_regex_msg')
        ];
        //To get role by role_slug
        $role_info = $this->roleService->getRoleDetails(Input::get('parent_role'), ['context']);
        $context_info = array_get($role_info, 'contexts', '');
        $admin_flag =  array_get($role_info, 'is_admin_role', '');
        //context information only filtering ids
        $context_ids = array_column($context_info, 'id');
        $validation = Validator::make(Input::all(), $rules, $messages);

        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            $array = Role::getRoleArray();
            if (in_array(strtolower(Input::get('role_name')), $array)) {
                $error = trans('admin/role.unique_role_alert');

                return Redirect::back()->with('role_exist', $error);
            }
            $role_id = Role::getNextRoleId();
            $parent_role = Input::get('parent_role');
            //inserting into role table
            Role::AddRole(
                array_merge(
                    Input::all(),
                    ["id" => $role_id, "parent_role_slug" => $parent_role, "context_ids" => $context_ids, "is_admin_role" => $admin_flag ]
                )
            );

            Input::flush();
            return redirect('cp/rolemanagement/add-permissions/' . $parent_role . '/' . $role_id);
        }
    }
    /**
     *Get add permissions to add the permissions to specific roles   
     */
    public function getAddPermissions($parent_role, $id)
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::ADD_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/role.user_roles') => 'rolemanagement/user-roles',
            trans('admin/role.add_role') => '',
            trans('admin/role.permissions') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        // To get role information by roles_slug
        $data = $this->roleService->getRoleDetails($parent_role, ['permissions']);
        $modules = [];
        $data['portal_capabilities'] = array_get($data, 'portal_permissions', '');
        $data['admin_capabilities'] = array_get($data, 'admin_permissions', '');
        if (!empty($data['admin_capabilities'])) {
            foreach ($data['admin_capabilities'] as $each) {
                $modules[$each['slug']] = $each['module'];
            }
        }

        if (isset($data['portal_capabilities'])) {
            foreach ($data['portal_capabilities'] as $each) {
                $modules[$each['slug']] = $each['module'];
            }
        }
        $modules = array_unique($modules);

        $this->layout->pagetitle = trans('admin/role.roles');
        $this->layout->pageicon = 'fa fa-user-md';
        $this->layout->pagedescription = trans('admin/role.add_user_permissions');

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'role');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.roles.add_permissions')->with('id', $id)->with('data', $data)->with('parent_role', $parent_role)->with('modules', $modules);
    }

    public function postAddPermissions($parent = '', $id)
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::ADD_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $admin_permissions = [];
        $portal_permissions = [];
        $action_flag = Input::get('action_flag');
        $module = Input::get('modules');
        if (!empty($module)) {
            foreach ($module as $modules) {
                $pieces = explode('|', $modules);
                $admin_actions = Input::get('admin_' . str_replace(' ', '', $pieces[1]));

                if (!empty($admin_actions)) {
                    $array2 = [];
                    $i = 0;
                    foreach ($admin_actions as $action) {
                        $data = explode('|', $action);
                        $permission_type = PermissionType::ADMIN;
                        $module_permission_data = $this->moduleService->getModulePermissionDetails(
                            $pieces[0],
                            $data[1],
                            $permission_type
                        );
                        $pid = array_get($module_permission_data, 'permission.id', '');
                        $array2[$i]['id'] = $pid;
                        $array2[$i]['name'] = $data[0];
                        $array2[$i]['slug'] = $data[1];
                        if ($data[2] == 1) {
                            $array2[$i]['is_default'] = (int)$data[2];
                        } else {
                            $array2[$i]['is_default'] = '';
                        }

                        ++$i;
                    }
                    $array1 = ['module' => $pieces[1],
                        'slug' => $pieces[0],
                        'action' => $array2,];
                    $admin_permissions[]= $array1;
                }

                $portal_actions = Input::get('portal_' . str_replace(' ', '', $pieces[1]));
                if (!empty($portal_actions)) {
                    $array2 = [];
                    $i = 0;
                    foreach ($portal_actions as $action) {
                        $data = explode('|', $action);
                        $permission_type = PermissionType::PORTAL;
                        $module_permission_data = $this->moduleService->getModulePermissionDetails(
                            $pieces[0],
                            $data[1],
                            $permission_type
                        );
                        $pid = array_get($module_permission_data, 'permission.id', '');
                        $array2[$i]['id'] = $pid;
                        $array2[$i]['name'] = $data[0];
                        $array2[$i]['slug'] = $data[1];
                        if ($data[2] == 1) {
                            $array2[$i]['is_default'] = (int)$data[2];
                        } else {
                            $array2[$i]['is_default'] = '';
                        }
                        ++$i;
                    }
                    $array1 = ['module' => $pieces[1],
                        'slug' => $pieces[0],
                        'action' => $array2,];
                    $portal_permissions[] = $array1;
                }
            }
        }

        $permission_info = array_collapse(
            array_merge(
                array_column($portal_permissions, 'action'),
                array_column($admin_permissions, 'action')
            )
        );
        $permission_ids = array_column($permission_info, 'id');
        /* Creationg roles cache file */

        if ($action_flag === 'add') {
            if (!empty($permission_ids)) {
                $this->roleService->mapRoleAndPermissions($id, $permission_ids);
            }
        } else {
            $this->roleService->syncRoleAndPermissions($id, $permission_ids);
        }

        if (Session::has('edit')) {
            $success = trans('admin/role.edit_role_success');

            return redirect('cp/rolemanagement/success/' . $id)->with('success', $success);
        } else {
            $success = trans('admin/role.add_role_success');

            return redirect('cp/rolemanagement/success/' . $id)->with('success', $success);
        }

    }

    public function getSuccess($role_id)
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/role.user_roles') => 'rolemanagement/user-roles',
            trans('admin/role.success') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

        $role_info = Role::getRole($role_id);

        $this->layout->pagetitle = trans('admin/role.roles');
        $this->layout->pageicon = 'fa fa-user-md';
        $this->layout->pagedescription = trans('admin/role.manage_roles');

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'role');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.roles.action_success', ['role_id' => $role_id, 'role_info' => $role_info]);
    }

    public function getEditRole($id)
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::EDIT_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/role.user_roles') => 'rolemanagement/user-roles',
            trans('admin/role.edit_role') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/role.roles');
        $this->layout->pageicon = 'fa fa-user-md';
        $this->layout->pagedescription = trans('admin/role.manage_user_roles');

        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '3 desc');
        $filter = Input::get('filter', 'all');

        $role = Role::getRole($id);
        $role = $role[0];
        $parents = $this->roleService->getInheritableRoles(true);
        $contexts = $this->roleService->getContexts();
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'role');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.roles.edit_role', ['role' => $role, 'id' => $id, 'parent' => $parents, 'contexts'=>$contexts]);
    }

    public function postEditRole($id)
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::EDIT_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $rules = [

            'description' => "Max:200|Regex:/^([a-zA-Z0-9:.',\-@#&()\/+\n- ])+$/",
            'role_name' => 'Required|unique:roles|Max:50|Regex:/^[A-Za-z][A-Za-z0-9 \-]+$/',
        ];
        $messages = [];
        $messages += [
            'role_name.regex' => trans('admin/role.role_name_regex_msg')
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);

        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            Session::put('edit', 'edited');
            $array = Role::getRoleArray($id);
            if (in_array(strtolower(Input::get('role_name')), $array)) {
                $error = trans('admin/role.unique_role_alert');

                return Redirect::back()->with('role_exist', $error);
            }
            $current_parent = Input::get('current_parent');
            $parent_role = Input::get('parent_role');
            $permissions_info = Role::getRole($id);
            $parent_role_info = $this->roleService->getRoleDetails(Input::get('parent_role'), ['context']);
            $admin_flag =  array_get($parent_role_info, 'is_admin_role', '');
            if ($parent_role == '') {
                $error = trans('admin/role.no_permission_to_edit_role');
                return redirect('cp/rolemanagement/user-roles/')->with('error', $error);
            }

            Role::UpdateRole(array_merge(Input::all(), ["is_admin_role" => $admin_flag]), $id);
            
            if ($current_parent == $parent_role) {
                return redirect('cp/rolemanagement/edit-permissions/' . $parent_role . '/' . $id);
            } else {
                Input::flush();
                Role::RemovePermissions($id);
                return redirect('cp/rolemanagement/add-permissions/' . $parent_role . '/' . $id);
            }
        }
    }

    public function getEditPermissions($parent_role = '', $id)
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::EDIT_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/role.user_roles') => 'rolemanagement/user-roles',
            trans('admin/role.edit_role') => '',
            trans('admin/role.permissions') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
           // To get role information by roles_slug
        $data = $this->roleService->getRoleDetails($parent_role, ['permissions']);
        $data['portal_capabilities'] = array_get($data, 'portal_permissions', '');
        $data['admin_capabilities'] = array_get($data, 'admin_permissions', '');
        if (!empty($data['admin_capabilities'])) {
            foreach ($data['admin_capabilities'] as $each) {
                $modules[$each['slug']] = $each['module'];
            }
        }

        if (isset($data['portal_capabilities'])) {
            foreach ($data['portal_capabilities'] as $each) {
                $modules[$each['slug']] = $each['module'];
            }
        }
        $modules = array_unique($modules);
        // $current_permissions = $this->roleService->getPermissionsArray($id);

        $roles_info =  $this->roleService->getRoleDetails((int)$id, ['permissions']);
        $permission['portal_capabilities'] = array_get($roles_info, 'portal_permissions', '');
        $permission['admin_capabilities'] = array_get($roles_info, 'admin_permissions', '');

        $current_permissions = [];
        if (!empty($permission['admin_capabilities'])) {
            foreach ($permission['admin_capabilities'] as $each) {
                $action = array_get($each, 'action', '');
                $action_list = array_column($action, 'name');
                $current_permissions['admin_capabilities'][$each['module']] = $action_list;
            }
        }

        if (isset($permission['portal_capabilities'])) {
            foreach ($permission['portal_capabilities'] as $each) {
                $action = array_get($each, 'action', '');
                $action_list = array_column($action, 'name');
                $current_permissions['portal_capabilities'][$each['module']] = $action_list;
            }
        }

        $this->layout->pagetitle = trans('admin/role.portal_roles');
        $this->layout->pageicon = 'fa fa-users';
        $this->layout->pagedescription = trans('admin/role.edit_user_permissions');

        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'users_groups')
            ->with('submenu', 'role');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.roles.edit_permissions')
            ->with('id', $id)
            ->with('data', $data)
            ->with('parent_role', $parent_role)
            ->with('current_permissions', $current_permissions)
            ->with('modules', $modules);
    }

    public function getDeleteRole($id = null, $name = null)
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::DELETE_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '3 desc');
        $filter = Input::get('filter', 'all');

        if ($id) {
            $role_info = Role::where('rid', '=', (int)$id)->where('system_role', '=', true)->value('rid');
            $role = Role::where('rid', '=', (int)$id)->value('rid');

            if (!empty($role_info)) {
                $error = 'You can not delete the system role.';

                return redirect('cp/rolemanagement/user-roles?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                    ->with('error', $error);
            } elseif (empty($role)) {
                $error = 'Role does not exist.';

                return redirect('cp/rolemanagement/user-roles?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                    ->with('error', $error);
            } elseif ($this->roleService->isRoleAssignedToUser($id)) {
                $error = trans("admin/role.no_permission_to_delete_role");

                return redirect('cp/rolemanagement/user-roles?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                    ->with('error', $error);
            }

            Role::getDeleteRole($id);
            $totalRecords = Role::getRolesCount($filter, $search);
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }
            $success = trans('admin/role.role_delete');
        }

        return redirect('cp/rolemanagement/user-roles?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
            ->with('success', $success);
    }

    public function postBulkDelete()
    {
        if (!has_admin_permission(ModuleEnum::ROLE, RolePermission::DELETE_ROLE)) {
            return parent::getAdminError($this->theme_path);
        }

        $roles_checked = Input::get('ids');

        $roles_checked = explode(',', trim($roles_checked, ' ,'));

        foreach ($roles_checked as $roles) {
            if ($roles != '') {
                if (!$this->roleService->isRoleAssignedToUser($roles)) {
                    Role::getDeleteRole($roles);
                }
            }
        }

        $success = trans('admin/role.multiple_role_delete');

        return redirect('cp/rolemanagement/user-roles')->with('success', $success);
    }
}
