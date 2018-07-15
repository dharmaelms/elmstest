<?php
namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Program;
use App\Services\Catalog\AccessControl\IAccessControlService;
use App\Services\Catalog\CatList\ICatalogService;
use App\Services\Catalog\Order\IOrderService;
use App\Services\Catalog\Payment\IPaymentService;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Catalog\Promocode\IPromoCodeService;
use App\Services\Country\ICountryService;
use App\Services\Package\IPackageService;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\UserEntity;
use App\Model\Package\Entity\Package;
use App\Events\User\EntityEnrollmentThroughSubscription;
use App\Model\User\Entity\UserEnrollment;
use App\Exceptions\Package\PackageNotFoundException;
use Carbon\Carbon;
use Timezone;
use Auth;
use Common;
use Config;
use Input;
use Redirect;
use Session;
use URL;
use Validator;

/**
 * Class CheckoutController
 * @package App\Http\Controllers\Portal
 */
class CheckoutController extends PortalBaseController
{
    /**
     * @var ICatalogService
     */
    protected $catSer;
    /**
     * @var IPricingService
     */
    protected $pricingSer;
    /**
     * @var IPaymentService
     */
    protected $paySer;
    /**
     * @var IOrderService
     */
    protected $ordSer;
    /**
     * @var IAccessControlService
     */
    protected $acServ;
    /**
     * @var IPromoCodeService
     */
    protected $promoServ;
    /**
     * @var IPackageService
     */
    protected $packageService;
    /**
     * @var mixed|string
     */
    protected $pay_currency = '';
    /**
     * @var ICountryService|null
     */
    protected $countryService = null;

    /**
     * CheckoutController constructor.
     * @param ICatalogService $catService
     * @param IPricingService $priceService
     * @param IPaymentService $paymentService
     * @param IOrderService $orderService
     * @param IPackageService $packageService
     * @param IAccessControlService $accessControlService
     * @param IPromoCodeService $promoService
     * @param ICountryService $countryService
     */
    public function __construct(
        ICatalogService $catService,
        IPricingService $priceService,
        IPaymentService $paymentService,
        IOrderService $orderService,
        IAccessControlService $accessControlService,
        IPromoCodeService $promoService,
        ICountryService $countryService,
        IPackageService $packageService
    )
    {
        parent::__construct();
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout_frontend';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->catSer = $catService;
        $this->pricingSer = $priceService;
        $this->paySer = $paymentService;
        $this->ordSer = $orderService;
        $this->acServ = $accessControlService;
        $this->promoServ = $promoService;
        $this->pay_currency = config('app.site_currency');
        $this->countryService = $countryService;
        $this->packageService = $packageService;
    }

    /**
     * @return Redirect
     */
    public function getIndex()
    {
        return redirect('catalog');
    }

    /**
     * @param null $p_slug
     * @param null $s_slug
     * @return Redirect
     */
    public function getPlaceOrder(
        $p_slug = null,
        $s_slug = null,
        $p_type = null,
        $course_id = null
    ) {
        if (Auth::check()) {
            
            $data = $this->mOrderData($p_slug, $s_slug, $p_type);
            $data = array_merge($data, ['program_type' => $p_type]);
            $u_id = Auth::user()->uid;
            
            //Added the below lines to check whether the user is already subscribed to the program or not
            
            if (isset($data) && $data['p_type'] == "package") {
                $entity_type = UserEntity::PACKAGE;
                try {
                    $package = $this->packageService->getPackageBySlug('package_slug', $p_slug);
                    $p_id = $package->package_id;
                } catch (PackageNotFoundException $e) {
                    return parent::getError($this->theme, $this->theme_path, 300);
                }
            } elseif (isset($data) && $data['p_type'] == "course") {
                $entity_type = UserEntity::BATCH;
                $p_id = (int)$course_id;
            } elseif (isset($data) && $data['p_type'] == "content_feed") {
                $entity_type = UserEntity::PROGRAM;
                $p_id = Program::getIDbySlug($p_slug);
            }

            $user_enrollment = UserEnrollment::where('user_id', $u_id)
                        ->where('entity_type', $entity_type)
                        ->where('entity_id', $p_id)
                        ->where('source_id', $s_slug)
                        ->active()
                        ->first();

            if (!is_null($user_enrollment) && $data['p_type'] == "package") {
                return redirect('catalog/course/'.$p_slug.'/package');
            } elseif (!is_null($user_enrollment)) {
                return redirect('catalog/course/'.$p_slug);
            }


            if (!empty($data)) {
                if (array_get($data, 'priceService', '') == "free") {
                    $data = ["p_slug" => $p_slug,
                    "s_slug" => $s_slug,
                    "fullname" => "",
                    "address" => "",
                    "region_state" => "",
                    "city" => "",
                    "country" => "",
                    "post_code" => "",
                    "telephone" => "",
                    "promo_code" => "",
                    "d_hidden" => "",
                    "net_total_input" => "0",
                    "h_net_total" => "",
                    "pay_way" => "FREE"];
                $u_email = Auth::user()->email;
                $p_data = $this->mOrderData($p_slug, $s_slug, $p_type);
                $orderID = $this->ordSer->placeOrder($data, $u_id, $p_data);
                $o_data = $this->ordSer->getOrder($orderID);
                $this->sendOrderMail($o_data);
                $url = $this->getSubscribe($p_slug, $s_slug, $o_data, $p_type);
                return redirect(URL::to(Config::get('app.program_auto_redirect')));
            }

            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $s_data = ['relations', 'parents'];
            $crumbs = [
                'Home' => '/',
                'Catalog' => 'catalog',
                'Checkout' => ''
            ];

            $this->layout->breadcrumbs = Common::getEFEBreadCrumbs($crumbs);
            $c_list = $this->catSer->catWithProgram("list", $s_data);
            $currency_symbol = $this->getCurrencySymbol(config('app.site_currency'));
            
            if (isset($p_type) && $p_type == "package") {
                $data['promocode'] = $this->promoServ->getPackagePromoCodeList($p_slug, $data, $u_id, $p_type);
            } else {
                $data['promocode'] = $this->promoServ->getPromoCodeList($p_slug, $data, $u_id);
            }

            $this->layout->content = view($this->theme_path . '.catalog.checkout', ['items_details' => $data, 'currency_symbol' => $currency_symbol]);

            } else {
                /* When invalid program slug/subscription slug is found, then redirecting the user to 404 page. */
                return parent::getError($this->theme, $this->theme_path, 404);
            }
        } else {
            Session::put('cr_URL', "checkout/place-order/{$p_slug}/{$s_slug}");
            return redirect('auth/login');
        }
    }

    /**
     * @param $p_slug
     * @param $s_slug
     * @return array
     */
    private function mOrderData($p_slug, $s_slug, $p_type)
    {
        if (isset($p_type) && $p_type == "package") {
            $p_data = $this->catSer->getPackage($p_slug);
            $tempdata = [];
            if (!empty($p_data)) {
                foreach ($p_data as $value) {
                    $s_data = $this->pricingSer->getSubscriptionDetails($value['package_id'], 'package', $s_slug);
                    $tempdata['p_tite'] = $value['package_title'];
                    $tempdata['p_slug'] = $value['package_slug'];
                    $tempdata['p_type'] = $p_type;
                    $tempdata['p_img'] = $value['package_cover_media'];
                    $tempdata['s_title'] = $s_data['title'];
                    $tempdata['s_slug'] = $s_data['slug'];
                    $duration_type = "Days";
                    if (isset($s_data['duration_type'])) {
                        if ($s_data['duration_type'] === "DD") {
                            $duration_type = "Days";
                        } elseif ($s_data['duration_type'] === "MM") {
                            $duration_type = "Months";
                        } elseif ($s_data['duration_type'] === "WW") {
                            $duration_type = "Weeks";
                        } else {
                            $duration_type = "Years";
                        }
                    }
                    if (isset($s_data['duration_count'])) {
                        $tempdata['s_duration'] = $s_data['duration_count'] . " " . $duration_type;
                    }
                    if (isset($s_data['price']) && !empty($s_data['price'])) {
                        foreach ($s_data['price'] as $eachPrice) {
                            if ($eachPrice['currency_code'] === config('app.site_currency')) {
                                $tempdata['priceService'] = "paid";
                                if (!empty($eachPrice['markprice'])) {
                                    $tempdata['price'] = $eachPrice['markprice'];
                                } else {
                                    $tempdata['price'] = $eachPrice['price'];
                                }
                                $tempdata['m_price'] = $eachPrice['markprice'];
                            }
                        }
                    } else {
                        $tempdata['priceService'] = "free";
                        $tempdata['price'] = "0";
                        $tempdata['m_price'] = "0";
                    }
                    $tempdata['d_addrs'] = $this->ordSer->getDefaultAddress(Auth::user()->uid);
                }
            }
        } else {
            $p_data = $this->catSer->getCourse($p_slug);
            $tempdata = [];
            if (!empty($p_data)) {
                foreach ($p_data as $value) {
                    if ($value['program_type'] === "product") {
                        $data['sellable_id'] = $value['program_id'];
                        $data['sellable_type'] = $value['program_type'];
                        $s_data = $this->pricingSer->getVerticalBySlug($data, $s_slug);
                    } elseif ($value['program_type'] === "course") {
                        $data['sellable_id'] = $value['program_id'];
                        $data['sellable_type'] = $value['program_type'];
                        $s_data = $this->pricingSer->getVerticalBySlug($data, $s_slug);
                        if ($s_data['batch_maximum_enrollment'] - $s_data['batch_enrolled'] === 1) {
                            Session::flash('last_item', 'yes');
                            if ($this->ordSer->getOrderInPendingLastMinute($value['program_id'], $s_slug)) {
                                Session::flash('no_item', 'yes');
                            }
                        }
                    } else {
                        $s_data = $this->pricingSer->getSubscriptionDetails($value['program_id'], $value['program_type'], $s_slug);
                    }
                    if (!is_null($s_data)) {

                        $tempdata['p_tite'] = $value['program_title'];
                        $tempdata['p_type'] = $value['program_type'];
                        $tempdata['p_slug'] = $value['program_slug'];
                        $tempdata['p_img'] = $value['program_cover_media'];
                        $tempdata['s_title'] = $s_data['title'];
                        $tempdata['s_slug'] = $s_data['slug'];
                        $duration_type = "Days";
                        if (isset($s_data['duration_type'])) {
                            if ($s_data['duration_type'] === "DD") {
                                $duration_type = "Days";
                            } elseif ($s_data['duration_type'] === "MM") {
                                $duration_type = "Months";
                            } elseif ($s_data['duration_type'] === "WW") {
                                $duration_type = "Weeks";
                            } else {
                                $duration_type = "Years";
                            }
                        }

                        if (isset($s_data['duration_count'])) {
                            $tempdata['s_duration'] = $s_data['duration_count'] . " " . $duration_type;
                        }
                        if (isset($s_data['price']) && !empty($s_data['price'])) {
                            foreach ($s_data['price'] as $eachPrice) {
                                if ($eachPrice['currency_code'] === config('app.site_currency')) {
                                    $tempdata['priceService'] = "paid";
                                    if (!empty($eachPrice['markprice'])) {
                                        $tempdata['price'] = $eachPrice['markprice'];
                                    } else {
                                        $tempdata['price'] = $eachPrice['price'];
                                    }
                                    $tempdata['m_price'] = $eachPrice['markprice'];
                                }
                            }
                        } else {
                            $tempdata['priceService'] = "free";
                            $tempdata['price'] = "0";
                            $tempdata['m_price'] = "0";
                        }
                        $tempdata['d_addrs'] = $this->ordSer->getDefaultAddress(Auth::user()->uid);
                    }
                }
            }
        }

        return $tempdata;
    }

    /**
     * @return $this|\Illuminate\Http\RedirectResponse|Redirect
     */
    public function postPay()
    {
        $rules = [];
        $data = Input::all();
        $data['pay_way'] = Input::get('pay_way', "FREE");
        if ($data['pay_way'] != "FREE") {
            $rules = [
                'pay_way' => 'required',
            ];
        }

        $rules = [
            'address' => 'required|max:255|regex:/[a-zA-Z#.,0-9- ]+$/',
            'region_state' => 'required|max:75|regex:/[a-zA-Z ]+$/',
            'telephone' => 'required|max:20|regex:/[0-9+-]{10,15}$/',
            'country' => 'required|max:20',
            'city' => 'required|regex:/^[a-zA-Z ]+$/|max:75',
            'post_code' => 'required|regex:/^[0-9]{6}$/|max:11',
            'fullname' => 'required|min:4|max:30|regex:/[a-zA-Z ]+$/'
        ];
        $messages = [
            'telephone.required' => 'Mobile number field is required',
            'telephone.regex' => 'Mobile number field format is invalid.'
        ];
        $validator = Validator::make(array_map('trim', Input::all()), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {
            Session::put('pay_way', $data['pay_way']);
            $u_id = Auth::user()->uid;
            $u_email = Auth::user()->email;
            $p_data = $this->mOrderData(Input::get('p_slug'), Input::get('s_slug'), Input::get('program_type'));
            $orderID = $this->ordSer->placeOrder($data, $u_id, $p_data);
            $o_data = $this->ordSer->getOrder($orderID);
            $this->sendOrderMail($o_data);
            $url = $this->getSubscribe(Input::get('p_slug'), Input::get('s_slug'), $o_data, Input::get('program_type'));
            if ($url === "osummary") {
                Session::flash('order_placed', 'yes');
                return redirect(URL::to('ord/view-order/' . $o_data['order_id']));
            } else {
                $data = $this->paySer->itemPayment($url, Input::get('pay_way'));
                if (Session::get('pay_way') === "PayPal") {
                    return Redirect::away($data);
                }
                if (is_array($data)) {
                    echo view('payment/payumoney', ['posted' => $data]);
                } else {
                    return redirect(URL::to('ord/view-order/' . $o_data['order_id']));
                }
            }
        }
    }


    /**
     * @param null $p_slug
     * @param null $s_slug
     * @param null $order
     * @return null|string
     */
    public function getSubscribe($p_slug = null, $s_slug = null, $order = null, $p_type = null)
    {
        $pay_way = $order['payment_type'];
        if (!empty($p_slug) && !empty($s_slug) && Auth::check()) {
            $s_data = [
                's_user' => null,
                's_pgr' => null,
                's_subs' => null,
                's_price' => null
            ];
            
            if (isset($p_type) && $p_type == "package") {
                $p_data = $this->catSer->getPackage($p_slug);
                $u_data = [
                    'u_id' => Auth::user()->uid,
                    'p_id' => $p_data[0]['package_id'],
                    'p_type' => 'package',
                    'p_slug' => $p_data[0]['package_slug'],
                    'p_title' => $p_data[0]['package_title'],
                    's_slug' => $s_slug
                ];
            } else {
                $p_data = $this->catSer->getCourse($p_slug);
                $u_data = [
                    'u_id' => Auth::user()->uid,
                    'p_id' => $p_data[0]['program_id'],
                    'p_type' => $p_data[0]['program_type'],
                    'p_slug' => $p_data[0]['program_slug'],
                    'p_title' => $p_data[0]['program_title'],
                    's_slug' => $s_slug
                ];
            }
            

            if ($pay_way === "PayUMoney") {
                return $payData = $this->mPaymentData(Auth::user(), $p_data, $order, $p_type);
            } elseif ($pay_way === "PayPal") {
                return $payData = $this->mPaymentData(Auth::user(), $p_data, $order, $p_type);
            } else {
                if ($pay_way === "FREE") {
                    $s_data = $this->pricingSer->subscribeUser($u_data, $p_type);
                    $this->acServ->enrollUser($u_data);
                    
                    if (isset($u_data) && $u_data['p_type'] == "package") {
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
                    } elseif (isset($u_data) && $u_data['p_type'] == "content_feed") {
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
                    } elseif (isset($u_data) && $u_data['p_type'] == "course") {
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
                    }

                }
                return 'osummary';
            }
        } else {
            return 'osummary';
        }
    }

    /**
     * @param $userdata
     * @param $p_data
     * @param $s_data
     * @return null
     */
    public function mPaymentData($userdata, $p_data, $s_data, $p_type)
    {
        $retPayData = null;
        if (!empty($p_data)) {
            if ($p_type == "package") {
                foreach ($p_data as $key => $value) {
                    $retPayData['pname'] = $value['package_title'];
                    $retPayData['uid'] = $userdata['uid'];
                    $retPayData['fullname'] = $userdata['firstname'] . " " . $userdata['lastname'];
                    $retPayData['email'] = $userdata['email'];
                    $retPayData['phone'] = $userdata['mobile'];
                    $retPayData['amount'] = $s_data['net_total'];
                    $retPayData['txnid'] = $s_data['order_label'];
                }
            } else {
                foreach ($p_data as $key => $value) {
                    $retPayData['pname'] = $value['program_title'];
                    $retPayData['uid'] = $userdata['uid'];
                    $retPayData['fullname'] = $userdata['firstname'] . " " . $userdata['lastname'];
                    $retPayData['email'] = $userdata['email'];
                    $retPayData['phone'] = $userdata['mobile'];
                    $retPayData['amount'] = $s_data['net_total'];
                    $retPayData['txnid'] = $s_data['order_label'];
                }
            }
        } else {
            return null;
        }
        return $retPayData;
    }

    /**
     *
     */
    public function postApplyCoupon()
    {
        $cCode = Input::get('coupanCode');
        $price = Input::get('price');
        $program_slug = Input::get('program_slug');
        $program_type = Input::get('program_type');
        $uid = Auth::user()->uid;
        $discount = $this->promoServ->valPromoCode($cCode, $program_slug, $price, $uid, null, $program_type);
        $total = $price - $discount;
        if (!empty($discount) && $discount != "promocode_used") {
            $resultError = [
                "success" => "Promo code applied successfully",
                "discount" => ($discount > 0) ? $discount : 0,
                "net_total" => ($total > 0) ? $total : 0,
                "numbeber_format_discount" => number_format(($discount > 0) ? $discount : 0),
                "number_format_net_total" => number_format(($total > 0) ? $total : 0)
            ];
            echo json_encode($resultError);
        } else {
            $resultError = ["error" => "Invalid promo code"];
            if ($discount === "promocode_used") {
                $resultError = ["error" => "Promo code is already used."];
            }
            echo json_encode($resultError);
        }
        exit;
    }

    /**
     *
     */
    public function postSuccess()
    {
        $o_data = $this->ordSer->uoStatus($_POST, "COMPLETED", "PAID");
        $u_timezone = 'Asia/Kolkata';
        $p_slug = $o_data['items_details']['p_slug'];
        $s_slug = $o_data['items_details']['s_slug'];
        $program_type = $o_data['items_details']['p_type'];
        $s_data = [
            's_user' => null,
            's_pgr' => null,
            's_subs' => null,
            's_price' => null
        ];
        
        if (isset($program_type) && $program_type == 'package') {
            $p_data = $this->catSer->getPackage($p_slug);
            $p_data[0]['program_type'] = 'package';
            $u_data = [
                'u_id' => $o_data['user_details']['uid'],
                'p_id' => $p_data[0]['package_id'],
                'p_type' => 'package',
                'p_slug' => $p_data[0]['package_slug'],
                'p_title' => $p_data[0]['package_title'],
                's_slug' => $s_slug
            ];
        } else {
            $p_data = $this->catSer->getCourse($p_slug);
            $u_data = [
                'u_id' => $o_data['user_details']['uid'],
                'p_id' => $p_data[0]['program_id'],
                'p_type' => $p_data[0]['program_type'],
                'p_slug' => $p_data[0]['program_slug'],
                'p_title' => $p_data[0]['program_title'],
                's_slug' => $s_slug
            ];
        }


        if ($p_data[0]['program_type'] === "content_feed") {
            $s_data = $this->pricingSer->subscribeUser($u_data);
            $this->acServ->enrollUser($u_data);
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
        } elseif ($p_data[0]['program_type'] === "package") {
            $s_data = $this->pricingSer->subscribeUser($u_data);
            $this->acServ->enrollUser($u_data);
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
        }

        $this->updateBatchInfo($p_data[0]['program_type'], $u_data);
        if (isset($o_data['items_details']['ordered_from']) && $o_data['items_details']['ordered_from'] == 'mobile') {
            $this->sendOrderMail($o_data);
            exit();
        }
        $this->sendOrderMail($o_data);
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $currency_symbol = $this->getCurrencySymbol(config('app.site_currency'));
        $this->layout->content = view($this->theme_path . '.catalog.summary', ['o_data' => $o_data, 'currency_symbol' => $currency_symbol, 'requestUrl' => null ]);
    }

    /**
     * @return Redirect
     */
    public function postCancel()
    {
        $p_data = $this->ordSer->uoStatus($_POST, "CANCELED", "NOT-PAID");
        $u_timezone = 'Asia/Kolkata';

        if (isset($p_data['items_details']['ordered_from']) && $p_data['items_details']['ordered_from'] == 'mobile') {
            $this->sendOrderMail($p_data);
            exit();
        }
        $this->sendOrderMail($p_data);
        Session::flash('order_placed', 'no');
        return redirect(URL::to('ord/view-order/' . $p_data['order_id']));
    }

    /**
     * @return Redirect
     */
    public function getPaymentStatus()
    {
        $payment_gateway = Session::get('pay_way');
        $payment_data = $this->paySer->itemPaymentStatus($payment_gateway);
        if ($payment_data['status'] === "COMPLETED") {
            $data = $this->ordSer->uoStatus($payment_data, "COMPLETED", "PAID");
        } else {
            $data = $this->ordSer->uoStatus($payment_data, "CANCELED", "NOT-PAID");
        }
        if ($data['status'] === "COMPLETED") {
            $u_timezone = 'Asia/Kolkata';
            $p_slug = $data['items_details']['p_slug'];
            $s_slug = $data['items_details']['s_slug'];
            $program_type = $data['items_details']['p_type'];

            $s_data = [
                's_user' => null,
                's_pgr' => null,
                's_subs' => null,
                's_price' => null
            ];

            if (isset($program_type) && $program_type == 'package') {
                $p_data = $this->catSer->getPackage($p_slug);
                $p_data[0]['program_type'] = 'package';
                $u_data = [
                    'u_id' => $data['user_details']['uid'],
                    'p_id' => $p_data[0]['package_id'],
                    'p_type' => 'package',
                    'p_slug' => $p_data[0]['package_slug'],
                    'p_title' => $p_data[0]['package_title'],
                    's_slug' => $s_slug
                ];
            } else {
                $p_data = $this->catSer->getCourse($p_slug);
                $u_data = [
                    'u_id' => $data['user_details']['uid'],
                    'p_id' => $p_data[0]['program_id'],
                    'p_type' => $p_data[0]['program_type'],
                    'p_slug' => $p_data[0]['program_slug'],
                    'p_title' => $p_data[0]['program_title'],
                    's_slug' => $s_slug
                ];
            }

            if ($p_data[0]['program_type'] != "product") {
                $this->updateBatchInfo($p_data[0]['program_type'], $u_data);
                if ($p_data[0]['program_type'] === "content_feed") {
                    $s_data = $this->pricingSer->subscribeUser($u_data);
                    $this->acServ->enrollUser($u_data);
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
                } elseif ($p_data[0]['program_type'] === "package") {
                    $s_data = $this->pricingSer->subscribeUser($u_data);
                    $this->acServ->enrollUser($u_data);
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
                }
            }

            if (isset($data['items_details']['ordered_from']) && $data['items_details']['ordered_from'] == 'mobile') {
                $this->sendOrderMail($data);
                exit();
            }

            $this->sendOrderMail($data);
            Session::flash('order_placed', 'yes');
            return redirect(URL::to('ord/view-order/' . $data['order_id']));
        } else {
            $u_timezone = 'Asia/Kolkata';
            if (isset($data['items_details']['ordered_from']) && $data['items_details']['ordered_from'] == 'mobile') {
                $this->sendOrderMail($data);
                exit();
            }
            $this->sendOrderMail($data);
            Session::flash('order_placed', 'no');
            return redirect(URL::to('ord/view-order/' . $data['order_id']));
        }
    }

    /**
     * [sendOrderMail send order mail to user]
     * @method sendOrderMail
     * @param  [type]        $data [description]
     * @return [type]              [description]
     * @author Rudragoud Patil
     */
    public function sendOrderMail($data)
    {
        $currency_symbol = $this->getCurrencySymbol($data['currency_code']);
        $from = [];
        Common::sendMail(
            'emails.order',
            [
                'o_data' => $data,
                'user_timezone' => $data['user_details']['timezone'],
                'currency_symbol' => $currency_symbol
            ],
            "Order Details - " . config('app.site_name'),
            $data['user_details']['email'],
            $from,
            config('app.admin_order_email')
        );
    }

    /**
     * @param $currency
     * @return string
     */
    protected function getCurrencySymbol($currency)
    {
        $data = $this->countryService->countryByCurrencyName($currency, ['name', 'currency_symbol']);
        if (!$data->isEmpty()) {
            foreach ($data->toArray() as $key => $value) {
                if (isset($value['currency_symbol']) && !empty($value['currency_symbol'])) {
                    return $value['currency_symbol'];
                }
            }
            return '&#x20B9;';
        } else {
            return '&#x20B9;';
        }
    }

    /**
     * [getMigrateOrderCollection - migrate order data]
     * @method getMigrateOrderCollection
     * @return [type]                    [null]
     * @author Rudragoud Patil
     */
    public function getMigrateOrderCollection()
    {
        $this->ordSer->migrateOrder();
        echo "*******...Ordered Migration is successful...*********";
        exit;
    }

    /**
     * @param $program_type
     * @param $u_data
     */
    public function updateBatchInfo($program_type, $u_data)
    {
        if ($program_type === "course") {
            $price_data = ['sellable_id' => $u_data['p_id'], 'sellable_type' => $u_data['p_type']];
            $v_data = $this->pricingSer->getVerticalBySlug($price_data, $u_data['s_slug']);
            if ($v_data['batch_enrolled'] < $v_data['batch_maximum_enrollment']) { //limited enrollment
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
            } else {
                //TODO: Rudragoud, what is $payment_data supposed to be?
                $data = $this->ordSer->uoStatus($payment_data, "Pending", "PAID");
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
        }
    }
}
