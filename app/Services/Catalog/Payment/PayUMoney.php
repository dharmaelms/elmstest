<?php
namespace App\Services\Catalog\Payment;

use URL;

/**
 * Class PayUMoney
 * @package App\Services\Catalog\Payment
 */
class PayUMoney
{
    /**
     * @var string
     */
    private $txn_id = "";
    /**
     * @var string
     */
    private $hash_sequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
    /**
     * @var mixed|string
     */
    private $salt = "";
    /**
     * @var mixed|string
     */
    private $merchant_key = "";
    /**
     * @var mixed|string
     */
    private $action = "";

    /**
     * PayUMoney constructor.
     */
    public function __construct()
    {
        $this->merchant_key = config('payumoney.merchant_key');
        $this->action = config('payumoney.base_url');
        $this->salt = config('payumoney.salt');
    }

    /**
     * @param $payData
     * @return array
     */
    public function makePayment($payData)
    {
        $this->txn_id = $payData['txnid'];
        $encryptPayUMoney = [
            'key' => $this->merchant_key,
            'hash' => '',
            'txnid' => $this->generateTxnID($this->txn_id),
            'amount' => $payData['amount'],
            'firstname' => $payData['fullname'],
            'email' => $payData['email'],
            'phone' => $payData['phone'],
            'product_info' => json_encode([
                0 => [
                    'name' => preg_replace('/[^A-Za-z0-9\-]/', '', $payData['pname']),
                    'description' => '',
                    'value' => $payData['amount'],
                    'isRequired' => 'false',
                ]
            ]),
            'surl' => URL::to('checkout/success'),
            'furl' => URL::to('checkout/cancel'),
            'service_provider' => 'payu_paisa'
        ];
        $encryptPayUMoneyResult = $this->generateHashString($encryptPayUMoney);
        $encryptPayUMoney['hash'] = $encryptPayUMoneyResult;
        $encryptPayUMoney['action'] = $this->action;
        $encryptPayUMoney['merchantKey'] = $this->merchant_key;
        $encryptPayUMoney['merchantKey'] = $this->merchant_key;
        return $encryptPayUMoney;
    }

    /**
     *
     */
    public function checkPayment()
    {
    }

    /**
     * [generateTxnID Randam Key Generation followed By PayUMoney]
     * @param  [type] $txnid [description]
     */

    private function generateTxnID($txnid)
    {
        if (empty($txnid)) {
            // Generate random transaction id
            $this->txn_id = $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        } else {
            $this->txn_id = $txnid;
        }
        return $this->txn_id;
    }

    /**
     * [generateHashString formatting data as per PayUMoney]
     * @param  [type] $posted [list of parameter to send Payment GAtaeway]
     * @return string [type]         [encrypted String for PayUMoney Gateway]
     */
    private function generateHashString($posted)
    {
        $hashSequence = $this->hash_sequence;
        $posted['productinfo'] = $posted['product_info'];
        $hashVarsSeq = explode('|', $hashSequence);
        $hash_string = '';
        foreach ($hashVarsSeq as $hash_var) {
            $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
            $hash_string .= '|';
        }
        $hash_string .= $this->salt;
        $hash = strtolower(hash('sha512', $hash_string));
        return $hash;
    }
}
