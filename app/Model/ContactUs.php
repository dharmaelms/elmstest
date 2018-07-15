<?php
namespace App\Model;

use Moloquent;

class ContactUs extends Moloquent
{
    protected $collection = 'contact_us';

    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['contact_date'];

    public static function getUniqueId()
    {
        return Sequence::getSequence('contact_id');
    }
}
