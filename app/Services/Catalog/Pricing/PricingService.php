<?php

namespace App\Services\Catalog\Pricing;

use App\Model\Catalog\Pricing\Repository\IPricingRepository;
use App\Model\Program;
use App\Services\Country\ICountryService;
use App\Events\User\EntityEnrollmentThroughSubscription;
use App\Model\Package\Entity\Package;
use App\Libraries\Timezone;
use App\Enums\User\UserEntity;

/**
 * Class PricingService
 * @package App\Services\Catalog\Pricing
 */
class PricingService implements IPricingService
{
    /**
     * @var IPricingRepository
     */
    private $pricing_repository;
    /**
     * @var mixed
     */
    private $country_list;
    /**
     * @var ICountryService
     */
    private $country_service;

    /**
     * PricingService constructor.
     * @param IPricingRepository $pricing_repository
     * @param ICountryService $country_service
     */
    public function __construct(
        IPricingRepository $pricing_repository,
        ICountryService $country_service
    )
    {

        $this->pricing_repository = $pricing_repository;
        $this->country_service = $country_service;
        $this->country_list = $this->country_service->supportedCurrencies();
    }


    /**********************************************************
     * Product Pricing
     ***********************************************************/
    private $p_select = ['price_id', 'vertical', 'subscription'];

    /**
     * @param $data
     * @return string
     */
    public function addPrice($data)
    {
        if (isset($data['sellable_id'], $data['sellable_type'])) {
            $p_exist = $this->priceFirst($data);
            if (empty($p_exist->all())) {
                if ($data['sellable_type'] != 'content_feed') {
                    $i_data = [
                        'sellable_id' => (int)$data['sellable_id'],
                        'sellable_type' => $data['sellable_type'],
                        'vertical' => [],
                        'tabs' => [],
                        'created_at' => time(),
                        'updated_at' => time()
                    ];
                } else {
                    $i_data = [
                        'sellable_id' => (int)$data['sellable_id'],
                        'sellable_type' => $data['sellable_type'],
                        'subscription' => [],
                        'tabs' => [],
                        'created_at' => time(),
                        'updated_at' => time()
                    ];
                }
                $this->pricing_repository->addPrice($i_data);
            }
        } else {
            return "price is can not created";
        }
    }

    /**
     * @param $data
     * @return array
     */
    private function doPrice($data)
    {
        $p_list = collect();
        if (isset($data['price'])) {
            return $data['price'];
        }
        if ($data['type'] === "free") {
            return $p_list->all();
        }
        if (!empty($this->country_list)) {
            foreach ($this->country_list as $value) {
                $cur = strtolower($value['currency_code']);
                $markprice = "mark_" . $cur;
                $c_data = [
                    'currency_code' => strtoupper($value['currency_code']),#,$data[$cur],
                    'price' => $data[$cur],
                    'markprice' => $data[$markprice]
                ];
                $p_list->push($c_data);
            }
        }
        return $p_list->all();
    }

    /**
     * @param $data
     * @return null
     */
    public function listVertical($data)
    {
        $c = $this->priceFirst($data);
        if (!isset($c->all()['vertical'])) {
            return null;
        }
        return $c->all()['vertical'];
    }

    /**
     * @param $data
     * @param $slug
     * @return mixed
     */
    public function getVerticalBySlug($data, $slug)
    {
        $c = $this->priceFirst($data);
        $verticals = $c->all()['vertical'];
        $r_vertical = collect($verticals)->filter(
            function ($item) use ($slug) {
                if ($item['slug'] === $this->mSlug($slug)) {
                    return $item;
                }
            }
        );
        return $r_vertical->first();
    }

    /**
     * @param $data
     * @return \Illuminate\Support\Collection
     */
    public function priceFirst($data)
    {
        $price = $this->pricing_repository->getPriceFirst($data['sellable_id'], $data['sellable_type'], $this->p_select);
        return collect($price);
    }

    /**
     * @param $p_data
     * @param $i_data
     * @param null $more_data
     * @return mixed|void
     */
    public function addVertical($p_data, $i_data, $more_data = null)
    {
        $v_data = [
            'title' => $i_data['title'],
            'slug' => $this->mSlug($i_data['title']),
            'desc' => $i_data['desc'],
            'type' => $i_data['type'],
            'price' => $this->doPrice($i_data)
        ];
        $price_id = $p_data->all()['price_id'];
        if (!empty($more_data)) {
            $v_data = array_merge($v_data, $more_data);
        }
        $this->pricing_repository->addVertical($price_id, $v_data);
    }

    /**
     * @param $p_data
     * @param $i_data
     * @param null $more_data
     * @return mixed|void
     */
    public function updateVertical($p_data, $i_data, $more_data = null)
    {
        $v_data = [
            'title' => $i_data['title'],
            'ctitle' => $this->mSlug($i_data['ctitle']),
            'slug' => $this->mSlug($i_data['title']),
            'desc' => $i_data['desc'],
            'type' => $i_data['type'],
            'price' => $this->doPrice($i_data)
        ];
        $price_id = $p_data->all()['price_id'];
        if (!empty($more_data)) {
            $v_data = array_merge($more_data, $v_data);
        }
        $this->pricing_repository->updateVertical($price_id, $v_data);
    }

    /**
     * @param $p_data
     * @param $i_data
     * @return mixed|void
     */
    public function deleteVertical($p_data, $i_data)
    {
        $v_data = [
            'slug' => $this->mSlug($i_data['title'])
        ];
        $price_id = $p_data->all()['price_id'];
        $this->pricing_repository->deleteVertical($price_id, $v_data);
    }
    /**********************************************************
     * Product Pricing Ends
     ***********************************************************/

    /**********************************************************
     * Subscription Pricing
     **********************************************************
     * @param $p_data
     * @param $i_data
     * @return mixed|void
     */

    public function addSubscriptions($p_data, $i_data)
    {
        $v_data = [
            'title' => $i_data['title'],
            'slug' => $this->mSlug($i_data['title']),
            'desc' => $i_data['desc'],
            'duration_type' => $i_data['duration_type'],
            'duration_count' => $i_data['duration_count'],
            'type' => $i_data['type'],
            'price' => $this->doPrice($i_data)
        ];
        $price_id = $p_data->all()['price_id'];
        $this->pricing_repository->addSubscription($price_id, $v_data);
    }

    /**
     * @param $p_data
     * @param $i_data
     * @return mixed|void
     */
    public function updateSubscription($p_data, $i_data)
    {
        $v_data = [
            'title' => $i_data['title'],
            'ctitle' => $this->mSlug($i_data['ctitle']),
            'slug' => $this->mSlug($i_data['title']),
            'desc' => $i_data['desc'],
            'duration_type' => $i_data['duration_type'],
            'duration_count' => $i_data['duration_count'],
            'type' => $i_data['type'],
            'price' => $this->doPrice($i_data)
        ];
        $price_id = $p_data->all()['price_id'];
        $this->pricing_repository->updateSubscription($price_id, $v_data);
    }

    /**
     * @param $p_data
     * @param $i_data
     * @return mixed|void
     */
    public function deleteSubscriptions($p_data, $i_data)
    {
        $v_data = [
            'slug' => $this->mSlug($i_data['title'])
        ];
        $price_id = $p_data->all()['price_id'];
        $this->pricing_repository->deleteSubscription($price_id, $v_data);
    }

    /**
     * [mSlug - Making subscription slug]
     * @method mSlug
     * @param $title
     * @return string [type]        [o/p]
     * @internal param $ [type] $title [i/p]
     * @author Rudragoud Patil
     */

    private function mSlug($title)
    {
        $slug = str_replace(" ", "-", $title);
        return strtolower($slug);
    }

    /**
     * @param $sal_id
     * @param $sal_type
     * @return null
     */
    public function getPriceList($sal_id, $sal_type)
    {
        $pricelist = $this->pricing_repository->getPrice($sal_id, $sal_type);
        if (!empty($pricelist)) {
            $i = 0;
            $tempdata = null;
            foreach ($pricelist as $value) {
                $tempdata[$i]['country_name'] = $this->getCountryName($value['country_code']);
                $tempdata[$i]['currency_code'] = $value['currency_code'];
                $tempdata[$i]['price'] = $value['price'];
                $i++;
            }
            return $tempdata;
        } else {
            return null;
        }
    }

    /**
     * @param $country_code
     * @return string
     */
    private function getCountryName($country_code)
    {
        if ($country_code === "IND") {
            return "INDIA";
        } else {
            return "UNITED STATES";
        }
    }

    /**
     * @param $sal_id
     * @param $sal_type
     * @param $title
     * @param null $c_slug
     * @return bool
     */
    public function checkDubSubscription($sal_id, $sal_type, $title, $c_slug = null)
    {
        $data = $this->pricing_repository->getSubscription($sal_id, $sal_type);
        if (!empty($data)) {
            $slug = $this->mSlug($title);
            if (empty($c_slug)) {
                foreach ($data as $value) {
                    if ($value['slug'] === $slug) {
                        return false;
                    }
                }
                return true;
            } else {
                $c_slug = $this->mSlug($c_slug);
                if ($c_slug === $slug) {
                    return true;
                } else {
                    foreach ($data as $value) {
                        if ($value['slug'] === $slug) {
                            return false;
                        }
                    }
                    return true;
                }
            }
        }
        return true;
    }


    /**
     * [getPricing - list of subscription details]
     * @method getPricing
     * @param $data
     * @return bool|mixed [type]           [description]
     * @internal param $ [type]     $data [description]
     * @author Rudragoud Patil
     */
    public function getPricing($data)
    {
        if (isset($data['sellable_id']) && !empty($data['sellable_id']) &&
            isset($data['sellable_type']) && !empty($data['sellable_type'])
        ) {
            $returnData = $this->pricing_repository->getPricing($data);
            return $returnData;
        } else {
            return false;
        }
    }

    /**
     * @param $sellable_id
     * @param $sellable_type
     * @param $slug
     * @return mixed
     */
    public function getSubscriptionDetails($sellable_id, $sellable_type, $slug)
    {
        $data = [
            'sellable_id' => $sellable_id,
            'sellable_type' => $sellable_type,
            'slug' => $slug
        ];
        $sellableEntity = $this->getPricing($data);
        if (!empty($sellableEntity)) {
            if ($sellable_type === "course") {
                $subscription_data = $sellableEntity["vertical"];
            } else {
                $subscription_data = $sellableEntity["subscription"];
            }

            foreach ($subscription_data as $entity) {
                if ($entity['slug'] === $data['slug']) {
                    return $entity;
                }
            }
        }
    }

    /**
     * @param $s_data
     * @return string
     */
    public function subscribeUser($s_data, $p_type = null)
    {
        if (isset($s_data['p_type']) && !empty($s_data['p_type']) &&
            isset($s_data['s_slug']) && !empty($s_data['s_slug']) &&
            isset($s_data['p_id']) && !empty($s_data['p_id'])
        ) {
            $data = $this->getSubscriptionDetails($s_data['p_id'], $s_data['p_type'], $s_data['s_slug']);
            $sub_details['title'] = $data['title'];
            
            if (isset($data['price']) && !empty($data['price'])) {
                $retData = null;
                foreach ($data['price'] as $key => $value) {
                    $retData[$value['currency_code']] = $value['price'];
                }
                $sub_details['price'] = $retData;
                if ($p_type === "course") {
                    $program = Program::find($data["course_id"]);
                    $s_duration = ["sTime" => $program->program_startdate, "eTime" => $program->program_enddate];
                } else {
                    $count = $this->timeCalculation($data['duration_type'], $data['duration_count']);
                    $s_duration = $this->timeFrame($count);
                }

                $sub_insert = ['program_id' => $s_data['p_id'],
                    'program_type' => $s_data['p_type'],
                    'subscription_slug' => $s_data['s_slug'],
                    'start_time' => $s_duration['sTime'],
                    'end_time' => $s_duration['eTime']
                ];
                
                $sub_insert = $this->getSubscriptions($s_data['u_id'], $sub_insert, $p_type);

                $this->pricing_repository->addSubscriptionUser($sub_insert, $s_data['u_id']);
                if (isset($s_data['p_type']) && $s_data['p_type'] == "package") {
                    event(
                        new EntityEnrollmentThroughSubscription(
                            $s_data['u_id'],
                            UserEntity::PACKAGE,
                            $s_data['p_id'],
                            $s_duration['sTime'],
                            $s_duration['eTime'],
                            $s_data['s_slug']
                        )
                    );
                } elseif (isset($s_data['p_type']) && $s_data['p_type'] == "content_feed") {
                    event(
                        new EntityEnrollmentThroughSubscription(
                            $s_data['u_id'],
                            UserEntity::PROGRAM,
                            $s_data['p_id'],
                            $s_duration['sTime'],
                            $s_duration['eTime'],
                            $s_data['s_slug']
                        )
                    );
                } elseif (isset($s_data['p_type']) && $s_data['p_type'] == "course") {
                    event(
                        new EntityEnrollmentThroughSubscription(
                            $s_data['u_id'],
                            UserEntity::BATCH,
                            $data["course_id"],
                            Timezone::getTimeStamp($s_duration['sTime']),
                            Timezone::getTimeStamp($s_duration['eTime']),
                            $s_data['s_slug']
                        )
                    );
                }
                return $sub_details;
            } else {
                if ($p_type === "course") {
                    $program = Program::find($data["course_id"]);
                    $s_duration = ["sTime" => $program->program_startdate, "eTime" => $program->program_enddate];
                } else {
                    $count = $this->timeCalculation($data['duration_type'], $data['duration_count']);
                    $s_duration = $this->timeFrame($count);
                }

                $sub_insert = ['program_id' => $s_data['p_id'],
                    'program_type' => $s_data['p_type'],
                    'subscription_slug' => $s_data['s_slug'],
                    'start_time' => $s_duration['sTime'],
                    'end_time' => $s_duration['eTime']
                ];
                $sub_insert = $this->getSubscriptions($s_data['u_id'], $sub_insert);
                $this->pricing_repository->addSubscriptionUser($sub_insert, $s_data['u_id']);
                if (isset($s_data['p_type']) && $s_data['p_type'] == "package") {
                    event(
                        new EntityEnrollmentThroughSubscription(
                            $s_data['u_id'],
                            UserEntity::PACKAGE,
                            $s_data['p_id'],
                            $s_duration['sTime'],
                            $s_duration['eTime'],
                            $s_data['s_slug']
                        )
                    );
                } elseif (isset($s_data['p_type']) && $s_data['p_type'] == "content_feed") {
                    event(
                        new EntityEnrollmentThroughSubscription(
                            $s_data['u_id'],
                            UserEntity::PROGRAM,
                            $s_data['p_id'],
                            $s_duration['sTime'],
                            $s_duration['eTime'],
                            $s_data['s_slug']
                        )
                    );
                } elseif (isset($s_data['p_type']) && $s_data['p_type'] == "course") {
                    event(
                        new EntityEnrollmentThroughSubscription(
                            $s_data['u_id'],
                            UserEntity::BATCH,
                            $data["course_id"],
                            Timezone::getTimeStamp($s_duration['sTime']),
                            Timezone::getTimeStamp($s_duration['eTime']),
                            $s_data['s_slug']
                        )
                    );
                }
                return "free";
            }
        }
    }

    /**
     * @param $type
     * @param $count
     * @return mixed
     */
    private function timeCalculation($type, $count)
    {
        switch ($type) {
            case "DD":
                return ($count * 24 * 60 * 60);
                break;
            case "MM":
                return ($count * 30 * 24 * 60 * 60);
                break;
            case "WW":
                return ($count * 7 * 24 * 60 * 60);
                break;
            case "YY":
                return ($count * 12 * 30 * 24 * 60 * 60);
                break;
        }
    }

    /**
     * @param $count
     * @return array
     */
    private function timeFrame($count)
    {
        $startTime = time();
        $endTime = time() + $count;
        return ['sTime' => $startTime, 'eTime' => $endTime];
    }

    /**
     * @param $uid
     * @param $data
     * @return array|null
     */
    private function getSubscriptions($uid, $data)
    {
        if (isset($data['program_type']) && $data['program_type'] == "package") {
            $program = Package::getPackageDetailsByID($data['program_id']);
            $data['package_id'] = $data['program_id'];
        } else {
            $program = Program::getProgramDetailsByID($data['program_id']);
        }

        $s_data = ['uid', 'subscription'];
        
        $sublist = $this->pricing_repository->getUserSubscription($uid, $s_data);

        $tempArray = null;

        $i = 0;
        if (!empty($sublist[0]['subscription'])) {
            $flag = 1;
            foreach ($sublist[0]['subscription'] as $value) {
                if (isset($value['program_id']) && isset($data['program_type']) && isset($data['subscription_slug'])) {
                    if ($value['program_id'] === $data['program_id'] &&
                        $value['program_type'] === $data['program_type'] &&
                        $value['subscription_slug'] === $data['subscription_slug']
                    ) {
                        $flag = 0;
                        $tempArray[$i] = $data;
                    } else {
                        if (empty($tempArray)) {
                            $tempArray[$i] = $value;
                        } else {
                            $tempArray[$i] = $value;
                        }
                    }
                }
                $i++;
            }
            if ($flag) {
                $tempArray[$i] = $data;
            }
        } else {
            $tempArray[$i] = $data;
        }

        if (isset($data['program_type']) && $data['program_type'] == "package") {
            foreach ($program['program_ids'] as $child_id) {
                $temp['program_id'] = (int)$child_id;
                $temp['package_id'] = $data['program_id'];
                $temp['program_type'] = $data['program_type'];
                $temp['subscription_slug'] = $data['subscription_slug'];
                $temp['start_time'] = $data['start_time'];
                $temp['end_time'] = $data['end_time'];
                $tempArray[] = $temp;
            }
        }

        return $tempArray;
    }

    /**
     * @param $program_id
     * @return mixed
     */
    public function getSubscriptionArray($program_id)
    {
        return $this->pricing_repository->getSubscriptionArray($program_id);
    }

    /**********************************************************
     * Subscription Pricing Ends
     ***********************************************************/
    /**
     * @param array $filter_params
     * @return mixed
     */
    public function getPricingDetails($filter_params = [])
    {
        return $this->pricing_repository->get($filter_params);
    }
}
