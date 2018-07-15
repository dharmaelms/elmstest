<?php
namespace App\Http\Controllers\Portal;

use App\Exceptions\SSO\InSecureConnectionException;
use App\Exceptions\SSO\InvalidCredentialsException;
use App\Exceptions\SSO\InvalidRequestException;
use App\Exceptions\SSO\MissingMandatoryFieldsException;
use App\Exceptions\SSO\SSOInvalidTokenException;
use App\Exceptions\SSO\SSOTokenExpiredException;
use App\Exceptions\SSO\SSOTokenNotFoundException;
use App\Exceptions\User\InActiveUserException;
use App\Exceptions\User\UserNotFoundException;
use App\Http\Controllers\PortalBaseController;
use App\Services\SSO\ISSOService;
use Illuminate\Http\Request;
use Log;
use Validator;

/**
 * Class SSOController
 * @package App\Http\Controllers\Portal
 */
class SSOController extends PortalBaseController
{

    /**
     * @var App\Services\SSO\ISSOService
     */
    private $sso_service;

    public function __construct(
        ISSOService $sso_service
    ) {
        $this->sso_service = $sso_service;
    }
    public function postToken(Request $request)
    {
        try {
            $this->sso_service->validataRequest($request);
            $request->merge(['email_id' => strtolower($request->input('email_id'))]);
            $rules = [
                'email_id' => 'bail|required|email|min:3|max:254',
                'first_name' => 'bail|min:1|max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
                'last_name' => 'bail|min:1|max:30|Regex:/^([A-Za-z\'. ])+$/',
                'phone_number' => 'bail|max:15|regex:/[0-9+-]{1,15}$/',
            ];
            $validation = Validator::make($request->all(), $rules);
            if ($validation->passes()) {
                $access_token = $this->sso_service->generateAccessToken($request);
                $status = 200;
                $message = trans('sso.token_created');
                $url = url('sso/login') . '?' . http_build_query(['email' => $request->input('email_id'), 'token' => $access_token]);
                $data = [
                    'status' => $status,
                    'message' => $message,
                    'access_token' => $access_token,
                    'email_id' => $request->input('email_id'),
                    'return_url' => $url
                ];
                $this->sso_service->log($request, $message, $status, $data);
                return response()->json($data);
            } else {
                $status = 400;
                $message = $validation->getMessageBag();
                $this->sso_service->log($request, $message->toArray(), $status);
                return response()->json(['errors' => $message, 'status' => $status]);
            }
        } catch (InSecureConnectionException $e) {
            $message = $e->getMessage();
            $status = '400';
        } catch (InvalidRequestException $e) {
            $message = $e->getMessage();
            $status = '401';
        } catch (InvalidCredentialsException $e) {
            $message = $e->getMessage();
            $status = '401';
        } catch (InActiveUserException $e) {
            $message = $e->getMessage();
            $status = '401';
        } catch (MissingMandatoryFieldsException $e) {
            $message = $e->getMessage();
            $status = '400';
        } catch (\Exception $e) {
            Log::error($e);
            $message = $e->getMessage() . ' at line ' . $e->getLine() . ' in file '. $e->getFile();
            $status = '500';
        }
        $this->sso_service->log($request, $message, $status);
        return response()->json(['errors' => [$message], 'status' => $status]);
    }

    /**
     * Method to login with access token and email id
     *
     * @param request
     * @return mixed Response
     */
    public function getLogin(Request $request)
    {
        try {
            $data = ['email' => $request->input('email'), 'token' => $request->input('token')];
            $rules = [
                'email' => 'bail|required|email|min:3|max:254',
                'token' => 'required',
            ];
            $validation = Validator::make($data, $rules);
            if ($validation->passes()) {
                $this->sso_service->validateToken($request);
                return redirect('dashboard')->with('success', trans('user.register_success'));
            } else {
                $status = 400;
                $message = $validation->getMessageBag();
                $this->sso_service->log($request, $message->toArray(), $status);
                return response()->json(["errors" => $message, 'status' => 401]);
            }
        } catch (SSOInvalidTokenException $e) {
            $message = $e->getMessage();
            $status = '400';
        } catch (SSOTokenExpiredException $e) {
            $message = $e->getMessage();
            $status = '401';
        } catch (UserNotFoundException $e) {
            $message = $e->getMessage();
            $status = '401';
        } catch (SSOTokenNotFoundException $e) {
            $message = $e->getMessage();
            $status = '401';
        } catch (InActiveUserException $e) {
            $message = $e->getMessage();
            $status = '401';
        }
        $this->sso_service->log($request, $message, $status);
        return response()->json(['errors' => [$message], 'status' => $status]);
    }
}
