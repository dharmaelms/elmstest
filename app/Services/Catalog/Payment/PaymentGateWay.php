<?php
namespace App\Services\Catalog\Payment;

/**
 * Class PaymentGateWay
 * @package App\Services\Catalog\Payment
 */
class PaymentGateWay
{
    /**
     * @param string $gateway
     * @return PayPal|PayUMoney
     */
    public function getPaymentGateWay($gateway = "PayUMoney")
    {
        switch ($gateway) {
            case 'PayUMoney':
                return new PayUMoney();
                break;
            case 'PayPal':
                return new PayPal();
                break;
            default:
                return new PayPal();
                break;
        }
    }
}
