<?php
namespace App\Model\ClientSecurity\Entity;

use App\Model\Sequence;
use Auth;
use Moloquent;

/**
 * class ClientSecurity
 * @package App\Model\ClientSecurity\Entity
 */
class ClientSecurity extends Moloquent
{
    protected $table = 'client_security';

    protected $primaryKey = 'security_id';
    
    public $timestamps = false;

    public static function getSequence()
    {
        return Sequence::getSequence('security_id');
    }
}

