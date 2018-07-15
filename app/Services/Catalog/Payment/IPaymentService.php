<?php

namespace App\Services\Catalog\Payment;

interface IPaymentService
{
    /**
     * [itemPayment direct Payment]
     * @method itemPayment
     * @param  [type]      $p_data          [description]
     * @param  [type]      $payment_gateway [description]
     * @return [type]                       [description]
     * @author Rudragoud Patil
     */
    public function itemPayment($p_data, $payment_gateway);

    /**
     * [itemPaymentStatus direct payment status]
     * @method itemPaymentStatus
     * @param  [type]            $payment_gateway [description]
     * @return [type]                            [description]
     * @author Rudragoud Patil
     */
    public function itemPaymentStatus($payment_gateway);

    /**
     * [bindPaymentGateWay create active paymentgateway object]
     * @method bindPaymentGateWay
     * @param  [type]             $payment_gateway [description]
     * @return [type]                             [description]
     * @author Rudragoud Patil
     */
    public function bindPaymentGateWay($payment_gateway);
}
