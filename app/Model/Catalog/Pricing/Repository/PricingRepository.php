<?php

namespace App\Model\Catalog\Pricing\Repository;

use App\Model\Catalog\Pricing\Entity\Price;
use App\Model\User;

class PricingRepository implements IPricingRepository
{
    /**
     * [store - Creating Sellable Entity]
     * @method store
     * @param  [type] $insertData [fillable attribute]
     * @return [type]             [description]
     * @author Rudragoud Patil
     */
    public function store($insertData)
    {
        $choice = isset($insertData['sellable_mode']) ? 'entity' : 'offering';
        switch ($choice) {
            case 'offering':
                if (isset($insertData['sellable_subscription']) && $insertData['sellable_subscription'] === 'yes') {
                    $this->offStoreSubscription($insertData);
                } else {
                    $this->offStore($insertData);
                }
                break;
            case 'entity':
                //Sellable Entity Management
                break;
        }
    }

    /**
     * [offStore - Without Subscription Flat price]
     * @method offStore
     * @param  [type]   $insertData [description]
     * @return [type]               [description]
     * @author Rudragoud Patil
     */
    private function offStore($insertData)
    {
        if (empty($this->getPrice($insertData['sellable_id'], $insertData['sellable_type'], 'YES'))) {
            $priceObj = new Price();
        } else {
            $priceObj = Price::where('sellable_id', '=', $insertData['sellable_id'])->where('sellable_type', '=', $insertData['sellable_type'])->first();
        }
        $priceObj->sellable_id = (int)$insertData['sellable_id'];
        $priceObj->sellable_type = $insertData['sellable_type'];
        $priceObj->sellable_subscription = isset($insertData['sellable_subscription']) ? $insertData['sellable_subscription'] : 'off';
        $priceObj->sellable_mode = isset($insertData['sellable_mode']) ? 'entity' : 'offering';
        $priceObj->price = $insertData['price'];
        $priceObj->type_pricing = $insertData['type_pricing'];
        $priceObj->save();
    }

    /**
     * [offStoreSubscription - Subscription based Store]
     * @method offStoreSubscription
     * @param  [type]               $insertData [description]
     * @return [type]                           [description]
     * @author Rudragoud Patil
     */
    private function offStoreSubscription($insertData)
    {
        if (empty($this->getSubscription($insertData['sellable_id'], $insertData['sellable_type'], 'YES'))) {
            $priceObj = new Price();
        } else {
            $priceObj = Price::where('sellable_id', '=', (int)$insertData['sellable_id'])->where('sellable_type', '=', $insertData['sellable_type'])->first();
        }
        $priceObj->sellable_id = (int)$insertData['sellable_id'];
        $priceObj->sellable_type = $insertData['sellable_type'];
        $priceObj->sellable_subscription = isset($insertData['sellable_subscription']) ? $insertData['sellable_subscription'] : 'off';
        $priceObj->sellable_mode = isset($insertData['sellable_mode']) ? 'entity' : 'offering';
        $priceObj->subscription = $insertData['subscription'];
        $priceObj->save();
    }

    public function getPrice($sal_id, $sal_type, $repo = '')
    {
        $query = Price::where('sellable_id', '=', (int)$sal_id);
        $returnData = $query->where('sellable_type', '=', $sal_type)
            ->get()
            ->toArray();
        if (!empty($returnData)) {
            foreach ($returnData as $value) {
                if (isset($value['price'])) {
                    if ($repo === 'YES') {
                        return "YES";
                    }
                    return $value['price'];
                } else {
                    if ($repo === 'YES') {
                        return "YES";
                    }
                    return null;
                }
            }
        } else {
            return null;
        }
    }

    /**
     * [getSubscription Sellable entity subscription list]
     * @method getSubscription
     * @param  [type]          $sal_id   [description]
     * @param  [type]          $sal_type [description]
     * @param  string $repo [Repository using - parameter on update]
     * @return [type]                    [list of subscription]
     * @author Rudragoud Patil
     */
    public function getSubscription($sal_id, $sal_type, $repo = '')
    {
        $query = Price::where('sellable_id', '=', (int)$sal_id);
        $returnData = $query->where('sellable_type', '=', $sal_type)
            ->get()
            ->toArray();
        if (!empty($returnData)) {
            foreach ($returnData as $value) {
                if (isset($value['subscription'])) {
                    if ($repo === 'YES') {
                        return "YES";
                    } else {
                        return $value['subscription'];
                    }
                } elseif (isset($value['vertical'])) {
                    if ($repo === 'YES') {
                        return "YES";
                    } else {
                        return $value['vertical'];
                    }
                } else {
                }
            }
        } else {
            return null;
        }
    }

    /**
     * [getPricing - return sellable entity]
     * @method getPricing
     * @param  [type]     $data [condition data]
     * @return [type]           [sellable data]
     * @author Rudragoud Patil
     */
    public function getPricing($data)
    {
        $query = Price::where('sellable_id', '=', (int)$data['sellable_id']);
        $returnData = $query->where('sellable_type', '=', $data['sellable_type'])
            ->get()
            ->toArray();
        if (!empty($returnData)) {
            return $returnData[0];
        } else {
            return null;
        }
    }

    public function addSubscriptionUser($s_data, $uid)
    {
        User::where('uid', '=', (int)$uid)->update(['subscription' => $s_data]);
    }

    public function getUserSubscription($uid, $s_list)
    {
        return User::where('uid', '=', (int)$uid)->get($s_list)->toArray();
    }


    /**********************************************************
     * Price Document
     ***********************************************************/
    public function addPrice($data)
    {
        $i_data = array_merge(['price_id' => Price::uniqueId()], $data);
        Price::insert($i_data);
    }

    public function getPriceFirst($id, $type, $s_attr)
    {
        return Price::where('sellable_id', '=', (int)$id)
            ->where('sellable_type', '=', $type)
            ->first($s_attr);
    }

    public function getPriceList()
    {
        return Price::get();
    }
    /**********************************************************
     * Price Document
     ***********************************************************/

    /**********************************************************
     * Product Pricing
     ***********************************************************/

    public function addVertical($price_id, $vertical)
    {
        Price::where('price_id', (int)$price_id)
            ->push('vertical', $vertical);
    }

    public function updateVertical($price_id, $vertical)
    {
        $data = Price::where('price_id', (int)$price_id)
            //->where('vertical.slug',$vertical['slug'])
            ->first(['vertical']);
        $slug = $vertical['ctitle'];
        $verticals = collect($data)->pull('vertical');
        $r_vertical = collect($verticals)->filter(
            function ($item) use ($slug) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        );
        Price::where('price_id', (int)$price_id)
            //->where('vertical.slug',$vertical['slug'])
            ->pull('vertical', ['slug' => $r_vertical->first()['slug']]);
        Price::where('price_id', (int)$price_id)
            ->push('vertical', $vertical);
    }

    public function deleteVertical($price_id, $vertical)
    {
        $data = Price::where('price_id', (int)$price_id)
            ->where('vertical.slug', $vertical['slug'])
            ->first(['vertical']);
        $slug = $vertical['slug'];
        $verticals = collect($data)->pull('vertical');
        $r_vertical = collect($verticals)->filter(
            function ($item) use ($slug) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        );
        Price::where('price_id', (int)$price_id)
            ->pull('vertical', $r_vertical->first());
    }

    /**********************************************************
     * Product Pricing Ends
     ***********************************************************/
    /**********************************************************
     * Product Subscription Pricing
     ***********************************************************/

    public function addSubscription($price_id, $vertical)
    {
        Price::where('price_id', (int)$price_id)
            ->push('subscription', $vertical);
    }

    public function updateSubscription($price_id, $vertical)
    {
        $data = Price::where('price_id', (int)$price_id)
            //->where('vertical.slug',$vertical['slug'])
            ->first(['subscription']);
        $slug = $vertical['ctitle'];
        $verticals = collect($data)->pull('subscription');
        $r_vertical = collect($verticals)->filter(
            function ($item) use ($slug) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        );
        Price::where('price_id', (int)$price_id)
            //->where('vertical.slug',$vertical['slug'])
            ->pull('subscription', ['slug' => $r_vertical->first()['slug']]);
        Price::where('price_id', (int)$price_id)
            ->push('subscription', $vertical);
    }

    public function deleteSubscription($price_id, $vertical)
    {
        $data = Price::where('price_id', (int)$price_id)
            ->where('subscription.slug', $vertical['slug'])
            ->first(['subscription']);
        $slug = $vertical['slug'];
        $verticals = collect($data)->pull('subscription');
        $r_vertical = collect($verticals)->filter(
            function ($item) use ($slug) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        );
        Price::where('price_id', (int)$price_id)
            ->pull('subscription', $r_vertical->first());
    }

    public function getSubscriptionArray($program_id)
    {
        return Price::where('sellable_id', '=', (int)$program_id)->value('subscription');
    }

    /**********************************************************
     * Product Subscription Pricing Ends
     ***********************************************************/

    /**********************************************************
     * Pricing Migration Starts
     **********************************************************/
    public function updatePriceID($data)
    {
        $i_data = array_merge(['price_id' => Price::uniqueId()], $data);
        Price::where('sellable_id', '=', $data['sellable_id'])
            ->where('sellable_type', '=', $data['sellable_type'])
            ->update($i_data, ['upsert' => true]);
    }
    /**********************************************************
     * Pricing Migration End
     **********************************************************/
    /**
     * @param array $filter_params
     * @return mixed
     */
    public function get($filter_params = [])
    {
        return Price::filter($filter_params)->get();
    }
}
