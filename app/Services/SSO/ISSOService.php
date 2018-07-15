<?php
namespace App\Services\SSO;

/**
 * Interface ISSOService
 * @package App\Services\SSO
 */
interface ISSOService
{
    /**
     * Method to generate access token
     *
     * @param $request
     * @return Response
     */
    public function generateAccessToken($request);

    /**
     * Method to validate the incoming request for token generation
     *
     * @param Request $request
     * @throws InvalidRequestException
     * @throws InvalidCredentialsException
     * @throws MissingMandatoryFieldsException
     * @return boolean
     */
    public function validataRequest($request);

    /**
     * Method to validate the token and log in the user if token is valid
     *
     * @param object $request
     * @throws SSOTokenNotFoundException
     * @throws SSOInvalidTokenException
     * @throws SSOTokenExpiredException
     * @throws UserNotFoundException
     * @return Response
     */
    public function validateToken($request);

    /**
     * Method to log the request and response
     *
     * @param Request $request
     * @param string $message
     * @param int $status
     * @param mixed $response
     */
    public function log($request, $message, $status, $response = '');

    /**
     * Method to assign new user to default user group
     *
     * @param int $user_id
     * @param int $usergroup_id
     * @return Response
     */
    public function assignUserToGroup($user_id, $usergroup_id);
}
