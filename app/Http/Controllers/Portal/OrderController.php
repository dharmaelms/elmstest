<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Services\Catalog\AccessControl\IAccessControlService;
use App\Services\Catalog\CatList\ICatalogService;
use App\Services\Catalog\Order\IOrderService;
use App\Services\Catalog\Payment\IPaymentService;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Country\ICountryService;
use Auth;
use Input;

class OrderController extends PortalBaseController
{
    protected $catSer;
    protected $pricingSer;
    protected $paySer;
    protected $ordSer;
    protected $acServ;
    protected $pay_currency = "";
    protected $countryService = null;

    public function __construct(
        ICatalogService $catService,
        IPricingService $priceService,
        IPaymentService $paymentService,
        IOrderService $orderService,
        IAccessControlService $accessControlService,
        ICountryService $countryService
    ) {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->catSer = $catService;
        $this->pricingSer = $priceService;
        $this->paySer = $paymentService;
        $this->ordSer = $orderService;
        $this->acServ = $accessControlService;
        $this->countryService = $countryService;
    }

    public function getListOrder()
    {
        if (Auth::check()) {
            $u_id = Auth::user()->uid;
            $data = $this->ordSer->getOrderPagination($u_id);
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $suppoted_currency = $this->countryService->supportedCurrencies();
            $this->layout->content = view($this->theme_path . '.user.order', ['data' => $data, 'suppoted_currency' => $suppoted_currency]);
        } else {
            return redirect('/');
        }
    }

    public function getViewOrder($orderID)
    {
        if (Auth::check()) {
            $data = $this->ordSer->getOrder($orderID);
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $currency_symbol = $this->getCurrencySymbol($data['currency_code']);
            $this->layout->content = view($this->theme_path . '.catalog.summary', ['o_data' => $data, 'currency_symbol' => $currency_symbol, 'requestUrl' => Input::get('requestUrl')]);
        } else {
            return redirect('/');
        }
    }

    protected function getCurrencySymbol($currency)
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
