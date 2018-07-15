<?php
namespace App\Services\ClientSecurity;


use App\Model\ClientSecurity\Repository\IClientSecurityRepository;

use App\Enums\User\SSOTokenStatus;
use App\Enums\User\UserEntity;
use App\Enums\User\UserStatus;
use App\Exceptions\SSO\InSecureConnectionException;
use App\Exceptions\SSO\InvalidCredentialsException;
use App\Exceptions\SSO\InvalidRequestException;
use App\Exceptions\SSO\MissingMandatoryFieldsException;
use App\Exceptions\SSO\SSOInvalidTokenException;
use App\Exceptions\SSO\SSOTokenExpiredException;
use App\Exceptions\SSO\SSOTokenNotFoundException;
use App\Exceptions\User\InActiveUserException;
use App\Exceptions\User\UserNotFoundException;
use App\Model\Common;
use App\Model\User;
use Auth;
use Carbon\Carbon;
use Session;

/**
 * class ClientSecurityService
 * @package App\Services\ClientSecurity
 */
class ClientSecurityService implements IClientSecurityService
{
    
    /**
     * @var App\Model\ClientSecurity\Repository\ClientSecurityRepository
     */
    private $client_security_repository;

    
    public function __construct(
        IClientSecurityRepository $client_security_repository
    ) {
        $this->client_security_repository = $client_security_repository;
    }

    /**
     * @inheritdoc
     */
    public function getSequence()
    {
        return $this->client_security_repository->getSequence();
    }

    /**
     * @inheritdoc
     */
    public function getClientSecurityDetails()
    {
        return $this->client_security_repository->getClientSecurityDetails();
    }

    /**
     * @inheritdoc
     */
    public function updateTokenDetails($security_id, $access_token, $expired_at, $updated_at)
    {
        return $this->client_security_repository->updateTokenDetails($security_id, $access_token, $expired_at, $updated_at);
    }

    /**
     * @inheritdoc
     */
    public function getClientSecurityDetailsBySecurityId($security_id)
    {
        return $this->client_security_repository->getClientSecurityDetailsBySecurityId($security_id);
    }

    /**
     * @inheritdoc
     */
    public function updateTokenExpirtDate($security_id, $expired_at, $updated_at)
    {
        return $this->client_security_repository->updateTokenExpirtDate($security_id, $expired_at, $updated_at);
    }

    /**
     * @inheritdoc
     */
    public function insertData($client_security_details)
    {
        return $this->client_security_repository->insertData($client_security_details);
    }
}
