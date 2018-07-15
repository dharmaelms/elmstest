<?php
namespace App\Services\Catalog\Payment;

/**
 * Class PaymentService
 * @package App\Services\Catalog\Payment
 */
class PaymentService implements IPaymentService
{
    /**
     * [$paymentGateWay holds active payment gateway object]
     * @var [type]
     */
    private $payment_gateway;

    /**
     * [itemPayment make payment with help of gateway]
     * @method itemPayment
     * @param  [type]      $p_data         [description]
     * @param  [type]      $payment_getway [description]
     * @author Rudragoud Patil
     * @return string
     */
    public function itemPayment($p_data, $payment_gateway)
    {
        $this->bindPaymentGateWay($payment_gateway);
        if (isset($p_data['pname']) && !empty($p_data['pname']) &&
            isset($p_data['uid']) && !empty($p_data['uid']) &&
            isset($p_data['fullname']) && !empty($p_data['fullname']) &&
            isset($p_data['email']) && !empty($p_data['email']) &&
            isset($p_data['amount']) && !empty($p_data['amount'])
        ) {
            return $this->payment_gateway->makePayment($p_data);
        } else {
            return "Please verify product parameter info";
        }
    }

    /**
     * [itemPaymentStatus getting payment status]
     * @method itemPaymentStatus
     * @param  [type]            $payment_getway [description]
     * @return [type]                            [description]
     * @author Rudragoud Patil
     */
    public function itemPaymentStatus($payment_getway)
    {
        $this->bindPaymentGateWay($payment_getway);
        return $this->payment_gateway->checkPayment();
    }


    /**
     * @param $payment_getway
     */
    public function bindPaymentGateWay($payment_getway)
    {
        $paymentGateWayObj = new PaymentGateWay();
        $this->payment_gateway = $paymentGateWayObj->getPaymentGateWay($payment_getway);
    }
}
