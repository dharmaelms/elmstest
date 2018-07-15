<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\AdminBaseController;
use App\Model\Program;
use App\Services\Catalog\AccessControl\IAccessControlService;
use App\Services\Catalog\CatList\ICatalogService;
use App\Services\Catalog\Order\IOrderService;
use App\Services\Catalog\Payment\IPaymentService;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Country\ICountryService;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\ECommerce\ECommercePermission;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\UserEntity;
use App\Model\Package\Entity\Package;
use App\Events\User\EntityEnrollmentThroughSubscription;
use Common;
use Input;
use Session;
use Carbon\Carbon;
use Timezone;
use Auth;

class OrderController extends AdminBaseController
{
    protected $catSer;
    protected $pricingSer;
    protected $paySer;
    protected $ordSer;
    protected $acServ;
    protected $pay_currency = "";
    protected $layout = 'admin.theme.layout.master_layout';
    protected $countryService = null;

    public function __construct(
        ICatalogService $catService,
        IPricingService $priceService,
        IPaymentService $paymentService,
        IOrderService $orderService,
        IAccessControlService $accessControlService,
        ICountryService $countryService
    )
    {
        parent::__construct();
        $this->catSer = $catService;
        $this->pricingSer = $priceService;
        $this->paySer = $paymentService;
        $this->ordSer = $orderService;
        $this->acServ = $accessControlService;
        $this->countryService = $countryService;
    }

    public function getListOrder()
    {
        $type_filter = Input::get('type_filter');
        $date_filter = Input::get('date_filter');

        if (is_null($date_filter)) {
            $date_filter = date('d-m-Y', strtotime('-1 month')) . " to " . date('d-m-Y');
        }

        Session::put('type_filter', $type_filter);
        Session::put('date_filter', $date_filter);
        if (!empty($type_filter) || !empty($date_filter)) {
            $data = $this->ordSer->getOrderByFilterPagination($type_filter, $date_filter);
        } else {
            $data = $this->ordSer->getOrderPagination();
        }
        $suppoted_currency = $this->countryService->supportedCurrencies();

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/order.manage_order') => 'order/list-order',
            trans('admin/order.list_of_order') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/order.manage_order');
        $this->layout->pageicon = 'fa fa-code-fork';
        $this->layout->pagedescription = trans('admin/order.list_of_order');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'order')
            ->with('submenu', 'transaction');
        $this->layout->content = view('admin.theme.Catalog.order.list')
            ->with('data', $data)
            ->with('suppoted_currency', $suppoted_currency);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getViewOrder($orderID)
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::VIEW_ORDER)) {
            return parent::getAdminError();
        }
        $data = $this->ordSer->getOrder($orderID);

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/order.manage_order') => 'order/list-order',
            trans('admin/order.details') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/order.manage_order');
        $this->layout->pageicon = 'fa fa-code-fork';
        $this->layout->pagedescription = trans('admin/order.list_of_order');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'order')
            ->with('submenu', 'transaction');
        $currency_symbol = $this->getCurrencySymbol($data['currency_code']);
        $this->layout->content = view('admin.theme.Catalog.order.view')
            ->with('o_data', $data)
            ->with('currency_symbol', $currency_symbol);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getEditOrder($orderID)
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EDIT_ORDER)) {
            return parent::getAdminError();
        }
        $data = $this->ordSer->getOrder($orderID);

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/order.manage_order') => 'order/list-order',
            trans('admin/order.edit') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/order.manage_order');
        $this->layout->pageicon = 'fa fa-code-fork';
        $this->layout->pagedescription = trans('admin/order.list_of_order');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'order')
            ->with('submenu', 'transaction');
        $this->layout->content = view('admin.theme.Catalog.order.edit')
            ->with('o_data', $data);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postSaveOrder()
    {
        $data = Input::all();
        $this->ordSer->updateOrder($data);
        if ($data['order_status'] === "COMPLETED" && $data['payment_status'] === "PAID") {
            $data = $this->ordSer->getOrder($data['order_id']);
            $program_type = array_get($data, 'items_details.p_type', '');
            if (isset($program_type) && $program_type == "package") {
                $p_data = $this->catSer->getPackage($data['items_details']['p_slug']);
                $p_data[0]['program_type'] = "package";
            } else {
                $p_data = $this->catSer->getCourse($data['items_details']['p_slug']);
            }

            if ($p_data[0]['program_type'] != 'product') {
                
                if (isset($program_type) && $program_type == "package") {
                    $u_data = [
                        'u_id' => (int)$data['user_details']['uid'],
                        'p_id' => $p_data[0]['package_id'],
                        'p_type' => $program_type,
                        'p_slug' => $p_data[0]['package_slug'],
                        'p_title' => $p_data[0]['package_title'],
                        's_slug' => $data['items_details']['s_slug']
                    ];
                } else {
                    $u_data = [
                        'u_id' => (int)$data['user_details']['uid'],
                        'p_id' => $p_data[0]['program_id'],
                        'p_type' => $p_data[0]['program_type'],
                        'p_slug' => $p_data[0]['program_slug'],
                        'p_title' => $p_data[0]['program_title'],
                        's_slug' => $data['items_details']['s_slug']
                    ];
                }

                if ($p_data[0]['program_type'] === "course") {
                    $price_data = ['sellable_id' => $u_data['p_id'], 'sellable_type' => $u_data['p_type']];
                    $v_data = $this->pricingSer->getVerticalBySlug($price_data, $u_data['s_slug']);
                    if ($v_data['batch_enrolled'] <= $v_data['batch_maximum_enrollment']) { //limited enrollment
                        $v_data = array_merge($v_data, ['batch_enrolled' => ($v_data['batch_enrolled'] + 1)]);
                        $pv_data = $this->pricingSer->priceFirst($price_data);
                        $this->pricingSer->updateVertical($pv_data, array_merge($v_data, ['ctitle' => $v_data['batch_name']]), $v_data);
                        $program = Program::getProgramDetailsByID($v_data['course_id']);
                        $u_data = array_merge($u_data, [
                            'p_id' => $v_data['course_id'],
                            'p_type' => $program->program_type,
                            'p_slug' => $program->program_slug,
                            'p_title' => $program->program_title
                        ]);
                    } elseif ($v_data['batch_maximum_enrollment'] === 0) { //unlimited enrollment
                        $v_data = array_merge($v_data, ['batch_enrolled' => ($v_data['batch_enrolled'] + 1)]);
                        $pv_data = $this->pricingSer->priceFirst($price_data);
                        $this->pricingSer->updateVertical($pv_data, array_merge($v_data, ['ctitle' => $v_data['batch_name']]), $v_data);
                        $program = Program::getProgramDetailsByID($v_data['course_id']);
                        $u_data = array_merge($u_data, [
                            'p_id' => $v_data['course_id'],
                            'p_type' => $program->program_type,
                            'p_slug' => $program->program_slug,
                            'p_title' => $program->program_title
                        ]);
                    } else { //error
                        return redirect('cp/order/list-order')->with('error', 'Seats are full.');
                    }
                    $this->acServ->enrollUser($u_data);
                    
                    $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
                    $context_info = $this->roleService->getContextDetails(Contexts::BATCH, false);
                    $role_id = array_get($role_info, 'id', '');
                    $context_id = array_get($context_info, 'id', '');
                    
                    $this->roleService->mapUserAndRole(
                        $u_data['u_id'],
                        $context_id,
                        $role_id,
                        $u_data['p_id']
                    );
                    
                    event(
                        new EntityEnrollmentThroughSubscription(
                            $u_data['u_id'],
                            UserEntity::BATCH,
                            $u_data['p_id'],
                            (int)Timezone::convertToUTC($v_data['batch_start_date'], Auth::user()->timezone, 'U'),
                            Carbon::createFromFormat('d-m-Y', $v_data['batch_end_date'], Auth::user()->timezone)->endOfDay()->timestamp,
                            $v_data['slug']
                        )
                    );

                } else {
                    if (isset($p_data[0]['program_type']) && $p_data[0]['program_type'] == "package") {
                        $this->acServ->enrollUser($u_data);
                        $s_data = $this->pricingSer->subscribeUser($u_data);
                        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
                        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
                        $role_id = array_get($role_info, 'id', '');
                        $context_id = array_get($context_info, 'id', '');
                        $package = Package::getPackageDetailsByID($u_data['p_id']);
                        $package->user()->attach($u_data['u_id']);
                        foreach ($package['program_ids'] as $child_id) {
                            $this->roleService->mapUserAndRole(
                                $u_data['u_id'],
                                $context_id,
                                $role_id,
                                $child_id
                            );
                        }
                    } elseif (isset($p_data[0]['program_type']) && $p_data[0]['program_type'] =="content_feed") {
                        $this->acServ->enrollUser($u_data);
                        $s_data = $this->pricingSer->subscribeUser($u_data);
                        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
                        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
                        $role_id = array_get($role_info, 'id', '');
                        $context_id = array_get($context_info, 'id', '');
                        
                        $this->roleService->mapUserAndRole(
                            $u_data['u_id'],
                            $context_id,
                            $role_id,
                            $u_data['p_id']
                        );
                    }
                }
            }
            
            $currency_symbol = $this->getCurrencySymbol($data['currency_code']);
            $u_email = $data['user_details']['email'];
            $from = [];
            Common::sendMail(
                'emails.order',
                ['o_data' => $data, 'currency_symbol' => $currency_symbol],
                "Order Confirmation",
                $u_email,
                $from,
                config('app.admin_order_email')
            );
        }
        return redirect('cp/order/list-order')->with('success', 'Order updated successfully.');
    }

    public function getExportOrders()
    {
        if (!has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EXPORT_ORDER)) {
            return parent::getAdminError();
        }
        $type_filter = Session::get('type_filter');
        $date_filter = Session::get('date_filter');
        $data = $this->ordSer->getOrderByFilter($type_filter, $date_filter);
        if (count($data) > 0) {
            return view('admin.theme.Catalog.order.export')->with('data', $data);
        } else {
            return redirect('cp/order/list-order')->with('warning', 'No data to export.');
        }
    }

    private function getCurrencySymbol($currency)
    {
        $data = $this->countryService->countryByCurrencyName($currency, ['name', 'currency_symbol']);
        if (!$data->isEmpty()) {
            foreach ($data->toArray() as $key => $value) {
                return $value['currency_symbol'];
            }
        } else {
            return '&#x20B9;';
        }
    }
}
