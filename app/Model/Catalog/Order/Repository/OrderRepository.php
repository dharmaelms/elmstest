<?php

namespace App\Model\Catalog\Order\Repository;

use App\Model\Catalog\Order\Entity\Order;
use App\Model\Program;
use App\Model\User;

/**
 * Class OrderRepository
 * @package App\Model\Catalog\Order\Repository
 */
class OrderRepository implements IOrderRepository
{
    /**
     * {@inheritdoc}
     */
    public function createOrder($data)
    {
        $order_id = Order::uniqueId();
        $time = time();
        $package = Program::getOrderPackage($data['items_details']['p_slug']);
        if (is_array($package)) {
            $data['items_details']['package'] = $package;
        }

        Order::insert([
            'order_id' => $order_id,
            'order_label' => 'ORD' . sprintf('%07d', (string)$order_id),
            'user_details' => $data['user'],
            'items_details' => $data['items_details'],
            'address' => $data['address'],
            'promo_code' => $data['promo_code'],
            'sub_total' => $data['sub_total'],
            'net_total' => $data['net_total'],
            'discount' => $data['discount'],
            'payment_type' => $data['payment_type'],
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'created_at' => $time,
            'updated_at' => $time,
            'currency_code' => $data['currency_code']
        ]);
        return $order_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($u_id, $s_list)
    {
        return User::where('uid', '=', (int)$u_id)->get($s_list)->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($order_id)
    {
        return Order::where('order_id', '=', (int)$order_id)->get()->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderPagination($u_id = null)
    {
        if (!empty($u_id)) {
            return Order::where('user_details.uid', '=', $u_id)
                ->orderBy('order_id', 'desc')
                ->paginate(10);
        } else {
            return Order::orderBy('order_id', 'desc')->paginate(10);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderByFilterPagination($type_filter = null, $date_filter = null)
    {
        $start_date = '';
        $end_date = '';
        if (isset($type_filter) && isset($date_filter) && !empty($type_filter) && !empty($date_filter)) {
            $date_array = explode('to', $date_filter);
            if (isset($date_array[0])) {
                $start_date = $date_array[0];
                $start_date = strtotime($start_date);
            }
            if (isset($date_array[1])) {
                $end_date = $date_array[1];
                $end_date = strtotime($end_date);

                $end_date = $end_date + 86400;
            }

            return Order::where('payment_type', '=', $type_filter)
                ->whereBetween('updated_at', [$start_date, $end_date])
                ->orderBy('order_id', 'desc')
                ->paginate(10);
        } elseif (isset($date_filter) && !empty($date_filter)) {
            $date_array = explode('to', $date_filter);
            if (isset($date_array[0])) {
                $start_date = $date_array[0];
                $start_date = strtotime($start_date);
            }
            if (isset($date_array[1])) {
                $end_date = $date_array[1];
                $end_date = strtotime($end_date);

                $end_date = $end_date + 86400;
            }
            return Order::whereBetween('updated_at', [$start_date, $end_date])
                ->orderBy('order_id', 'desc')
                ->paginate(10);
        } elseif (isset($type_filter) && !empty($type_filter)) {
            return Order::where('payment_type', '=', $type_filter)
                ->orderBy('order_id', 'desc')
                ->paginate(10);
        } else {
            return Order::orderBy('order_id', 'desc')->paginate(10);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderByFilter($type_filter = null, $date_filter = null)
    {
        $start_date = '';
        $end_date = '';
        if (isset($type_filter) && isset($date_filter) && !empty($type_filter) && !empty($date_filter)) {
            $date_array = explode('to', $date_filter);
            if (isset($date_array[0])) {
                $start_date = $date_array[0];
                $start_date = strtotime($start_date);
            }
            if (isset($date_array[1])) {
                $end_date = $date_array[1];
                $end_date = strtotime($end_date);

                $end_date = $end_date + 86400;
            }

            return Order::where('payment_type', '=', $type_filter)
                ->whereBetween('updated_at', [$start_date, $end_date])
                ->orderBy('order_id', 'desc')
                ->get();
        } elseif (isset($date_filter) && !empty($date_filter)) {
            $date_array = explode('to', $date_filter);
            if (isset($date_array[0])) {
                $start_date = $date_array[0];
                $start_date = strtotime($start_date);
            }
            if (isset($date_array[1])) {
                $end_date = $date_array[1];
                $end_date = strtotime($end_date);

                $end_date = $end_date + 86400;
            }
            return Order::whereBetween('updated_at', [$start_date, $end_date])
                ->orderBy('order_id', 'desc')
                ->get();
        } elseif (isset($type_filter) && !empty($type_filter)) {
            return Order::where('payment_type', '=', $type_filter)
                ->orderBy('order_id', 'desc')
                ->get();
        } else {
            return Order::orderBy('order_id', 'desc')->get();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateOrder($u_data)
    {
        $data['order_id'] = (int)$u_data['order_id'];
        $data['status'] = $u_data['status'];
        if (isset($u_data['payment_status'])) {
            $data['payment_status'] = $u_data['payment_status'];
        }

        if (isset($u_data['comment'])) {
            $data['comment'] = $u_data['comment'];
        }

        $data['updated_at'] = time();

        Order::where('order_id', (int)$data['order_id'])
            ->update($data, ['upsert' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function migrateOrder()
    {
        $order_list = Order::get();
        if (!$order_list->isEmpty()) {
            foreach ($order_list as $key => $value) {
                $created_date = strtotime(date($value->created_at));
                $updated_at = strtotime(date($value->updated_at));

                if (isset($value->currency_code)) {
                    $data = [
                        'created_at' => $created_date,
                        'updated_at' => $updated_at
                    ];
                    Order::where('order_id', (int)$value->order_id)
                        ->update($data, ['upsert' => true]);//update..!!!!!
                } else {
                    $data = [
                        'currency_code' => 'INR',
                        'created_at' => $created_date,
                        'updated_at' => $updated_at
                    ];
                    Order::where('order_id', (int)$value->order_id)
                        ->update($data, ['upsert' => true]);//update..!!!!!
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderInPandingLastMinute($program_id, $batch_slug)
    {
        $order_list = Order::where('items_details.s_slug', '=', $batch_slug)
            ->where('status', '=', 'Pending')
            ->where('created_at', '>', (time() - 420))
            ->get();
        if ($order_list->isEmpty()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSlug($slug, $new_slug)
    {   
       return Order::where('items_details.p_slug', '=', $slug)
           ->update(['items_details.p_slug' => $new_slug]);
    }
}
