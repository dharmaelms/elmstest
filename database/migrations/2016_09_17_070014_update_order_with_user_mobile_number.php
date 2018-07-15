<?php

use App\Model\Catalog\Order\Entity\Order;
use App\Model\User;
use Illuminate\Database\Migrations\Migration;

/*
Class UpdateOrderWithUserMobileNumber
 */

class UpdateOrderWithUserMobileNumber extends Migration
{
    /**
     * Run the migrations to update order information with user mobile number.
     *
     * @return void
     */
    public function up()
    {
        //Orders which does not have mobile number under user details
        $orders = Order::where('user_details.mobile', 'exists', false)->get();

        foreach ($orders as $order) {
            $user = User::where('uid', $order['user_details']['uid'])->first();

            if ($user && $user->mobile) {
                Order::where('order_id', $order['order_id'])
                    ->update(['user_details.mobile' => $user->mobile, 'updated_at' => strtotime($order['updated_at'])]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
