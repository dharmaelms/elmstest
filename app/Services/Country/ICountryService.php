<?php namespace App\Services\Country;

/**
 * Interface ICountryService
 * @package App\Services\Country
 */
interface ICountryService
{

    /**
     * @return mixed
     */
    public function listCountries();

    /**
     * @param $data
     * @return mixed
     */
    public function addCountry($data);

    /**
     * @param $data
     * @return mixed
     */
    public function editCountry($data);

    /**
     * @param $code
     * @param array $s_list
     * @return mixed
     */
    public function getCountryByCountryCode($code, $s_list = []);

    /**
     * @return mixed
     */
    public function supportedCurrencies();

    /**
     * @param $currency_code
     * @param $s_list
     * @return mixed
     */
    public function countryByCurrencyName($currency_code, $s_list);

    /**
     * @param $authkey
     * @return mixed
     */
    public function checkAuthKeyExists($authkey);
}
