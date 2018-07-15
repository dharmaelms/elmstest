<?php namespace App\Model\Country\Repository;

/**
 * Interface ICountryRepository
 * @package App\Model\Country\Repository
 */
interface ICountryRepository
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
     * @param $code
     * @param $s_list
     * @return mixed
     */
    public function getCountryByCountryCode($code, $s_list);

    /**
     * @param $data
     * @return mixed
     */
    public function editCountry($data);

    /**
     * @return mixed
     */
    public function getSiteSupportedCurrencies();

    /**
     * @param $currency_code
     * @param $s_list
     * @return mixed
     */
    public function getCountryByCurrencyName($currency_code, $s_list);

    /**
     * @param $authkey
     * @return mixed
     */
    public function checkAuthKeyExists($authkey);
}
