<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Services\Country\ICountryService;
use Common;
use Config;
use Illuminate\Http\Request;
use App\Enums\Country\CountryPermission;
use App\Enums\Module\Module as ModuleEnum;
use Input;
use Redirect;
use Validator;

/**
 * Class CountryController
 * @package App\Http\Controllers\Admin
 */
class CountryController extends AdminBaseController
{
    /**
     * @var string
     */
    protected $layout = 'admin.theme.layout.master_layout';
    //Holds country service instance.
    /**
     * @var ICountryService
     */
    private $country;
    /**
     * @var bool
     */
    private $device_type;

    private $theme_path;


    /**
     * CountryController constructor.
     * @param ICountryService $country
     */
    public function __construct(ICountryService $country)
    {
        $this->country = $country;
        $this->theme_path = 'admin.theme';
        $this->device_type = false;
    }


    /**
     * function to get country list
     */
    public function getIndex()
    {

        $list = has_admin_permission(ModuleEnum::COUNTRY, CountryPermission::LIST_COUNTRY);
        if ($list == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/country.manage_country_list') => 'country',
            trans('admin/country.list_countries') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = trans('admin/country.list_country_pageicon');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'country');
        $data = $this->country->listCountries();

        $this->layout->pagetitle = trans('admin/country.list_country_pagetitle');
        $this->layout->pagedescription = trans('admin/country.list_country_pagedescription');
        $this->layout->content = view('admin.theme.country.list_countries')
            ->with('data', $data);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * function to display add country form
     * @return  \Illuminate\Http\Response
     */
    public function getAddCountry()
    {
        $list = has_admin_permission(ModuleEnum::COUNTRY, CountryPermission::ADD_COUNTRY);
        $payment_options = Config::get('app.payment_options');
        if ($list == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/country.manage_country_list') => 'country',
            trans('admin/country.add_country_pagetitle') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = trans('admin/country.add_country_pageicon');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'country');

        $this->layout->pagetitle = trans('admin/country.add_country_pagetitle');
        $this->layout->pagedescription = trans('admin/country.add_country_pagedescription');
        $this->layout->content = view('admin.theme.country.add_country')
            ->with('payment_options', $payment_options);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * function to validate and insert country deatils
     * @return  array|\Symfony\Component\HttpFoundation\Response
     */

    public function postAddCountry()
    {
        Input::flash();
        $response = [];
        $rules = [
            'name' => 'Required|Min:3|Regex:/^([A-Za-z ])+$/|unique:countries,name',
            'currency_code' => 'Required|Min:3|Regex:/^([A-Za-z])+$/',
            'currency_name' => 'Required|Min:3|Regex:/^([A-Za-z ])+$/',
            'currency_symbol' => 'Required',
            'iso_code_two' => 'Min:2|Max:2|Regex:/^([A-Za-z])+$/|unique:countries,country_code',
            'iso_code_three' => 'Required|Min:3|Max:3|Regex:/^([A-Za-z])+$/|unique:countries,iso3',
            'payment_option' => 'Required',
            'status' => 'Required',
        ];

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->fails()) {
            if ($this->device_type) {
                $response['status'] = "error";
                $response['message'] = $validation->messages();
                return $response;
            }

            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $data = Input::all();
            $addresponse = $this->country->addCountry($data);
            if ($this->device_type) {
                $response['status'] = "success";
                $response['response'] = $addresponse;
                return $response;
            }
            if ($addresponse) {
                return redirect('cp/country')->with('success', trans('admin/country.add_success'));
            } else {
                return redirect('cp/country')->with('error', trans('admin/country.add_error'));
            }
        }
    }

    /**
     * @param $id
     */
    public function getEditCountry($id)
    {
        $list = has_admin_permission(ModuleEnum::COUNTRY, CountryPermission::EDIT_COUNTRY);

        if ($list == false) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/country.manage_country_list') => 'country',
            trans('admin/country.edit_country_title') => '',
        ];
        $details = '';
        $s_list = ['name', 'currency_code', 'currency_name', 'currency_symbol', 'country_code', 'iso3', 'status', 'default', 'payment_options'];
        $country_details = $this->country->getCountryByCountryCode($id, $s_list);
        $payment_options = Config::get('app.payment_options');
        if (isset($country_details[0])) {
            $details = $country_details[0];
        }
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = trans('admin/country.edit_country_pageicon');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'country');
        $this->layout->pagetitle = trans('admin/country.edit_country_pagetitle');
        $this->layout->pagedescription = trans('admin/country.edit_country_pagedescription');
        $this->layout->content = view('admin.theme.country.edit_country')
            ->with('details', $details)
            ->with('payment_options', $payment_options);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\Response
     */
    public function postEditCountry()
    {
        Input::flash();
        $rules = [
            'currency_code' => 'Required|Min:3|Regex:/^([A-Za-z])+$/',
            'currency_name' => 'Required|Min:3|Regex:/^([A-Za-z ])+$/',
            'currency_symbol' => 'Required',
            'iso_code_three' => 'Required|Min:3|Max:3|Regex:/^([A-Za-z])+$/',
            'status' => 'Required',
            'payment_option' => 'Required'
        ];

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            if ($this->device_type) {
                $response['status'] = "error";
                $response['message'] = $validation->messages();
                return $response;
            }
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $data = Input::all();
            $editresponse = $this->country->editCountry($data);
            if ($this->device_type) {
                $response['status'] = "success";
                $response['response'] = $editresponse;
                return $response;
            }
            if ($editresponse) {
                return redirect('cp/country')->with('success', trans('admin/country.add_success'));
            } else {
                return redirect('cp/country')->with('error', trans('admin/country.add_error'));
            }
        }
    }


    /**
     * @return array
     */
    public function postListCountries()
    {
        $country_lists = $this->country->listCountries();
        $response = [];
        if (is_object($country_lists) && count($country_lists) > 0) {
            $response['status'] = "success";
        } else {
            $response['status'] = "error";
        }


        $response['response'] = $country_lists;
        unset($country_lists);
        return $response;
    }


    /**
     * @param Request $request
     * @param $operation
     * @return \Illuminate\Http\JsonResponse
     */
    public function postCountryApi(Request $request, $operation)
    {
        if (!isset($operation) || empty($operation)) {
            $msg = ['status' => 'error', 'message' => "please specify api operation"];
        } elseif ($request->isMethod('post') && $request->ajax() && !empty($operation)) {
            $arr = $request->all();

            if ('array' === gettype($arr) && array_key_exists('device_type', $arr) && $arr['device_type'] === "mobile") {
                $this->device_type = true;
                if (array_key_exists('auth_key', $arr)) {
                    $auth_key = Input::get('auth_key');
                    $userAuthKey = $this->checkAuthKeyExists($auth_key);
                    if ($userAuthKey == true) {
                        $res = [];
                        // manager code to decide the functions
                        switch (strtolower($operation)) {
                            case "add":
                                $res = $this->postAddCountry();
                                break;
                            case "list":
                                $res = $this->postListCountries();
                                break;
                            case "edit":
                                $res = $this->postEditCountry();
                                break;
                            case "country-by-country-code":
                                $code = Input::get('country_code');
                                $s_list = Input::get('s_list');
                                $res = $this->country->getCountryByCountryCode($code, $s_list);
                                break;
                            case "supported-currencies":
                                $res = $this->country->supportedCurrencies();
                                break;
                            case "country-by-currency-name":
                                $code = Input::get('country_code');
                                $s_list = Input::get('s_list');
                                $res = $this->country->countryByCurrencyName($code, $s_list);
                                break;
                            default:
                                return response()->json(['status' => 'error', 'message' => "please specify api operation"]);
                                break;
                        }
                        $msg = ['status' => 'success', 'response' => $res];
                    } else {
                        $msg = ['status' => 'error', 'message' => "Auth token not matched"];
                    }
                } else {
                    $msg = ['status' => 'error', 'message' => "Auth token not matched"];
                }
            } else {
                $msg = ['status' => 'error', 'message' => "Error with the request"];
            }
        } else {
            $msg = ['status' => 'error', 'message' => "Error with the request"];
        }

        $this->device_type = false;
        unset($arr);
        return response()->json($msg);
    }

    /**
     * @param $authkey
     * @return bool
     */
    protected function checkAuthKeyExists($authkey)
    {
        if (isset($authkey) && !empty($authkey)) {
            // call a db method to check the key in users table
            return $this->country->checkAuthKeyExists($authkey);
        }
        return false;
    }
}
