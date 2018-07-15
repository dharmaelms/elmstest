<?php

namespace App\Services\Country;

use App\Model\Country\Repository\ICountryRepository;

/**
 * Class CountryService
 * @package App\Services\Country
 */
class CountryService implements ICountryService
{
    /**
     * @var ICountryRepository
     */
    private $country_repository;

    /**
     * CountryService constructor.
     * @param ICountryRepository $country_repository
     */
    public function __construct(ICountryRepository $country_repository)
    {
        $this->country_repository = $country_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function listCountries()
    {
        $list_countries = $this->country_repository->listCountries();
        return $list_countries;
    }

    /**
     * {@inheritdoc}
     */
    public function addCountry($data)
    {
        $data = $this->country_repository->addCountry($data);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function editCountry($data)
    {
        $data = $this->country_repository->editCountry($data);
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryByCountryCode($code, $s_list = [])
    {
        $data = [];
        if (!empty($code) && is_string($code) && strlen($code) == 3 && is_array($s_list)) {
            $d_list = ['name', 'country_code'];
            $f_list = array_unique(array_merge($d_list, $s_list));
            $data = $this->country_repository->getCountryByCountryCode($code, $f_list);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportedCurrencies()
    {
        $currencyArray = [];
        $final_currency_array = [];
        $currencies = $this->country_repository->getSiteSupportedCurrencies();
        if (is_array($currencies) && !empty($currencies)) {
            foreach ($currencies as $key => $eachcurrency) {
                if (!empty($eachcurrency['currency_code'])) {
                    $currencyArray['currency_code'] = strtolower($eachcurrency['currency_code']);
                    $currencyArray['currency_code_lower'] = strtoupper($eachcurrency['currency_code']);
                    $currencyArray['currency_symbol'] = $eachcurrency['currency_symbol'];
                    $currencyArray['currency_name'] = $eachcurrency['currency_name'];
                    $final_currency_array[] = $currencyArray;
                }
            }
        }

        return $final_currency_array;
    }

    /**
     * {@inheritdoc}
     */
    public function countryByCurrencyName($currency_code, $s_list)
    {

        $countries = [];
        if (!empty($currency_code) && is_string($currency_code) && strlen($currency_code) == 3 && is_array($s_list)) {
            $d_list = ['currency_name', 'currency_code'];
            $f_list = array_unique(array_merge($d_list, $s_list));
            $countries = $this->country_repository->getCountryByCurrencyName($currency_code, $f_list);
        }

        return $countries;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAuthKeyExists($authkey)
    {
        if (!empty($authkey) && $authkey != null) {
            $rauthkey = $this->country_repository->checkAuthKeyExists($authkey);
            if (isset($rauthkey[0])) {
                return true;
            }
            return false;
        }
        return false;
    }
}
