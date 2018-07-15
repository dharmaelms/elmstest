<?php

namespace App\Model\ContactUs;

/**
 * Class IContactUsRepository
 *
 * @package App\Model\ContactUs
 */
interface IContactUsRepository
{
    /**
     * Insert contact/enquiry data
     *
     * @param array $data
     * @param string $type
     */
    public function save(array $data, $type = 'general');
}
