<?php
namespace App\Services\Catalog\Payment;

interface IPayment
{
    /**
     * [makePayment prepare data gateway]
     * @method makePayment
     * @param  [type]      $product_info [description]
     * @return [type]                    [description]
     * @author Rudragoud Patil
     */
    public function makePayment($product_info);

    /**
     * [checkPayment validating payment w.r.t status]
     * @method checkPayment
     * @return [type]       [description]
     * @author Rudragoud Patil
     */
    public function checkPayment();
}
