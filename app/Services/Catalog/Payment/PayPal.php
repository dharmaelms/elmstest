<?php
namespace App\Services\Catalog\Payment;

use Config;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Input;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;
use Redirect;
use Session;
use URL;

/**
 * Class PayPal
 * @package App\Services\Catalog\Payment
 */
class PayPal implements IPayment
{
    use ValidatesRequests;
    /**
     * @var ApiContext
     */
    private $_api_context;

    /**
     * PayPal constructor.
     */
    public function __construct()
    {
        // setup PayPal api context
        $paypal_conf = Config::get('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
        $this->_api_context->setConfig($paypal_conf['settings']);
        // Set the default currency defined for site
        $this->currency = Config::get('app.site_currency');
    }

    /**
     * @param $itemData
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function makePayment($itemData)
    {
        //dd($itemData);
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item_1 = new Item();
        /*
         *  As of now the product supports only one item & single qty checkout & all product will have same currency as site currency
         *  Todo: We need get the currency of item being checked out & pass it to PayPal
         *        When we start supporting multi currency
         * */
        $item_currency = $this->currency;
        $item_1->setName($itemData['pname'])// item name
        ->setCurrency($item_currency)
            ->setQuantity(1)
            ->setPrice($itemData['amount']); // unit price

        // add item to list
        $item_list = new ItemList();
        $item_list->setItems([$item_1]);

        $amount = new Amount();
        $amount->setCurrency($item_currency)
            ->setTotal($itemData['amount']);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Your transaction description');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('payment.status'))
            ->setCancelUrl(URL::route('payment.status'));
        $payment = new Payment();
        $payment->setId($itemData['txnid']);
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions([$transaction]);
        try {
            $payment->create($this->_api_context);
        } catch (PayPalConnectionException $ex) {
            if (\Config::get('app.debug')) {
                echo "Exception: " . $ex->getMessage() . PHP_EOL;
                $err_data = json_decode($ex->getData(), true);
                exit;
            } else {
                die('Some error occur, sorry for inconvenient');
            }
        }

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        // add payment ID to session
        Session::put('paypal_payment_id', $itemData['txnid']);

        if (isset($redirect_url)) {
            // redirect to paypal
            return $redirect_url;
        }

        return Redirect::route('original.route')
            ->with('error', 'Unknown error occurred');
    }

    /**
     * @return array
     */
    public function checkPayment()
    {
        $payment_id = Input::get('paymentId');
        $order_data = ['txnid' => Session::get('paypal_payment_id'), 'status' => 'CANCELED'];

        if (empty(Input::get('PayerID')) || empty(Input::get('token'))) {
            return ['txnid' => Session::get('paypal_payment_id'), 'status' => 'CANCELED'];
        }

        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId(Input::get('PayerID'));
        $result = $payment->execute($execution, $this->_api_context);

        if ($result->getState() == 'approved') {
            return ['txnid' => Session::get('paypal_payment_id'), 'status' => 'COMPLETED'];
        } else {
            return ['txnid' => Session::get('paypal_payment_id'), 'status' => 'CANCELED'];
        }
    }
}
