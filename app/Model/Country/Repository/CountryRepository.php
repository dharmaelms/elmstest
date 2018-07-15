<?php

namespace App\Model\Country\Repository;

use App\Model\Country;
use App\Model\User;

class CountryRepository implements ICountryRepository
{
    /**
     * {@inheritdoc}
     */
    public function listCountries()
    {
        return Country::get();
    }

    /**
     * {@inheritdoc}
     */
    public function addCountry($data)
    {
        $insert_array = [
            'name' => strtoupper($data['name']),
            'country_name' => strtolower($data['name']),
            'currency_code' => $data['currency_code'],
            'currency_name' => $data['currency_name'],
            'currency_symbol' => $data['currency_symbol'],
            'country_code' => $data['iso_code_two'],
            'iso3' => $data['iso_code_three'],
            'status' => $data['status'],
            'payment_options' => $data['payment_option']
        ];

        if (isset($data['default']) && $data['default'] == "YES") {
            $default_country = Country::where('default', 'YES')->value('iso3');
            if ($default_country != $data['iso_code_three']) {
                Country::where('iso3', $default_country)->update([
                    'default' => 'NO'
                ]);
                $insert_array['default'] = $data['default'];
            }
        } else {
            $insert_array['default'] = "NO";
        }
        $response = Country::insert($insert_array);
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryByCountryCode($code, $s_list)
    {
        $data = Country::where('iso3', $code)->get($s_list);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function editCountry($data)
    {
        $update_array = [
            'currency_code' => $data['currency_code'],
            'currency_name' => $data['currency_name'],
            'currency_symbol' => $data['currency_symbol'],
            'iso3' => $data['iso_code_three'],
            'status' => $data['status'],
            'payment_options' => $data['payment_option']
        ];
        if (isset($data['default']) && $data['default'] == "YES") {
            $default_country = Country::where('default', 'YES')->value('iso3');
            if ($default_country != $data['iso_code_three']) {
                Country::where('iso3', $default_country)->update([
                    'default' => 'NO'
                ]);
                $update_array['default'] = $data['default'];
            }
        } else {
            $update_array['default'] = "NO";
        }
        $response = Country::where('iso3', $data['iso_code_three'])->update($update_array);
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getSiteSupportedCurrencies()
    {
        $data = Country::where('status', 'ACTIVE')
            ->get(['currency_code', 'currency_symbol', 'currency_name'])
            ->toArray();
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryByCurrencyName($currency_code, $s_list)
    {
        $data = Country::where('currency_code', $currency_code)->get($s_list);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAuthKeyExists($authkey)
    {
        $data = User::where('api.access_token', $authkey)->get(['api']);
        return $data;
    }
}
