<?php

namespace App\Http\Controllers\API; 

use App\Http\Controllers\Controller;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Exceptions\Program\ProgramNotFoundException;
use App\Exceptions\SSO\InvalidCredentialsException;
use App\Exceptions\SSO\MissingMandatoryFieldsException;
use App\Exceptions\SSO\SSOInvalidTokenException;
use App\Exceptions\SSO\SSOTokenExpiredException;
use App\Exceptions\User\UserNotFoundException;
use App\Model\API;
use App\Services\Program\IProgramService;
use App\Services\User\IUserService;
use App\Services\ClientSecurity\IClientSecurityService;
use App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository;
use Config;
use Exception;
use Illuminate\Http\Request;
use Log;
use Timezone;
use Validator;

/**
 * Class ProgramAPIController
 * @package App\Http\Controllers\API
 */
class ProgramAPIController extends Controller
{   

    /**
     * @var IProgramService
     */
    private $programService;

    /**
     * @var IUserService
     */
    private $user_service;

    /**
     * @var IOverAllChannalAnalyticRepository
     */
    private $over_all_channel_analytic_repository;

    /**
     * @var IClientSecurityService
     */
    private $client_security_service;

    /**
     * ProgramAPIController constructor.
     * @param Request $request
     * @param IProgramService $programService
     * @param IUserService $user_service
     * @param IOverAllChannalAnalyticRepository $over_all_channel_analytic_repository
     * @param IClientSecurityService $client_security_service
     */
    public function __construct(
        Request $request,
        IProgramService $programService,
        IUserService $user_service,
        IOverAllChannalAnalyticRepository $over_all_channel_analytic_repository,
        IClientSecurityService $client_security_service
    )
    {
        $this->programService = $programService;
        $this->user_service = $user_service;
        $this->over_all_channel_analytic_repository = $over_all_channel_analytic_repository;
        $this->client_security_service = $client_security_service;
    }

    /**
     * Method to generate token
     *
     * @param request
     * @return mixed Response
     */
    public function postGenerateToken(Request $request)
    {   
        try {
            $client_secret = $request->client_secret;
            $client_id = $request->client_id;
            if ((!$request->has('client_secret')) || (!$request->has('client_id'))) {
                throw new MissingMandatoryFieldsException;
            }
            if (($client_secret != config('app.client_security.client_secret')) || ($client_id != config('app.client_security.client_id'))) {
                throw new InvalidCredentialsException;
            }
            $access_token = str_random(60);
            $record = $this->client_security_service->getClientSecurityDetails();
            if (!empty($record)) {
                $updated_token = $this->updateToken($record->security_id);
                $updated_token_details = $this->client_security_service->getClientSecurityDetailsBySecurityId($record->security_id);
                $access_token = $updated_token_details->token;
            } else {
                $security_id = $this->client_security_service->getSequence();
                $client_security_details = [
                    'security_id' => $security_id, 
                    'token' => $access_token, 
                    'expired_at' => time() + 300, 
                    'created_at' => time(), 
                    'updated_at' => time() 
                ];
                $this->client_security_service->insertData($client_security_details);
            }
            Log::info('Token has been generated.');
            $message = "Token generated successfully.";

            return response()->json(['message' => $message, 'access_token' => $access_token]);
        } catch (MissingMandatoryFieldsException $e) {
            $status = '400';
            $message = 'Missing mandatory fields.';
        } catch (InvalidCredentialsException $e) {
            $status = '401';
            $message = 'Invalid Credentials.';
        } catch (Exception $e) {
            $status = '500';
            $message = 'Internal server error';
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
        }
        Log::info('message :'. $message);
        return response()->json(['status' => $status, 'message' => $message]);
    }

    /**
     * Method to update Token and expiry date
     *
     * @param request
     * @return boolean
     */
    public function updateToken($security_id)
    {
        $access_token = str_random(60);
        $expired_at = time() + 300;
        $updated_at = time();
        $updated_token = $this->client_security_service->updateTokenDetails($security_id, $access_token, $expired_at, $updated_at);
        return $updated_token;
    }

    /**
     * Method to check Token status
     * @return boolean
     */
    public function checkTokenStatus()
    {   
        $header_details = getallheaders();
        $token_exists = array_key_exists('token', $header_details);
        $token = array_get($header_details, 'token');
        if ((!$token_exists) || (empty($token))) {
            throw new SSOInvalidTokenException();
        }
        //Token field has some value
        $record = $this->client_security_service->getClientSecurityDetails();
        $access_token = $record->token;
        $expired_at = $record->expired_at;
        $security_id = $record->security_id;
        
        if ($token != $access_token) {
            throw new SSOInvalidTokenException();
        }
        
        if (($token == $access_token) && (time() > $expired_at) ){
            throw new SSOTokenExpiredException();
        }
        $this->client_security_service->updateTokenExpirtDate((int)$security_id, time() + 300, time());
    }

    /**
     * Method to get all channels
     *
     * @param request
     * @return mixed Response
     */
    public function getAllChannels(Request $request, $channel_id = null)
    {   
        try {
            $this->checkTokenStatus();
            $channels = [];
            if ((!empty($channel_id)) || ($channel_id == "0")) {
                $channels = [];
                $program = $this->programService->getChannelByProgramId((int)$channel_id);
                if (empty($program)) {
                    throw new ProgramNotFoundException();
                }
                $channels['channel_id'] = $program->program_id;
                $channels['name'] = $program->program_title;
                $channels['slug'] = $program->program_slug;
                return response()->json($channels);
            } else {
                $channels_collection = $this->programService->getAllActiveChannels();
                if ($channels_collection->isEmpty()) {
                    return response()->json(['message' => 'No Active Channels']);
                }
                foreach ($channels_collection as $key => $value) {
                    $channels[$key]['channel_id'] = $value->program_id;
                    $channels[$key]['name'] = $value->program_title;
                    $channels[$key]['slug'] = $value->program_slug;
                }
                return response()->json(['channels' => $channels]);
            }
        } catch (SSOInvalidTokenException $e) {
            $status = '401';
            $message = 'Invalid access token.';
        } catch (SSOTokenExpiredException $e) {
            $status = '401';
            $message = 'Token Expired.';
        } catch (ProgramNotFoundException $e) {
            $status = '401';
            $message = 'Invalid channel';
        } catch (Exception $e) {
            $status = '500';
            $message = 'Internal server error';
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
        }
        Log::info($message);
        return response()->json(['status' => $status, 'message' => $message]);
    }

    /**
     * Method to get all user certificate details
     *
     * @param request
     * @return mixed Response
     */
    public function getUserCertificatesDetails(Request $request, $email_id = null, $channel_id = null)
    {
        try {
            $this->checkTokenStatus();
            $request->merge(['email_id' => strtolower($email_id)]);
            $rules = [
                'email_id' => 'bail|required|email|min:3|max:254',
            ];
            $certificate_details = [];
            $validation = Validator::make($request->all(), $rules);
            if ($validation->passes()) {
                $uid = (int)$this->user_service->getIdByEmail(strtolower($email_id));
                if (empty($uid)) {
                    throw new UserNotFoundException();
                }
                //Get user related channel id's
                $channels = $this->programService->getAllProgramsAssignedToUser($uid);
                $channel_ids = array_unique(array_get($channels, 'channel_ids', []));

                if ((!empty($channel_id)  && !in_array($channel_id, $channel_ids)) || ($channel_id == "0")) {
                    throw new ProgramNotFoundException();
                } elseif (!empty($channel_id)) {
                    $channel_ids = [(int)$channel_id];
                }
                //Get channel details
                $programs = $this->programService->getProgramsDetailsById($channel_ids);
                //Get user & channel related certificate details
                $certificate_collection = $this->over_all_channel_analytic_repository->getUserChannelCompletionDetails($channel_ids, $uid)->keyBy('channel_id');
                foreach ($programs as $program_id => $program) {
                    $completion_status = $completion_date = $certificate_status = $completion_percentage = "";
                    $avg_score = 0;
                    $certificate_detail = $certificate_collection->get($program_id);
                    $program_title = $program->program_title;
                    $program_slug = $program->program_slug;
                    $certificate_status = "not issued";
                    if (is_null($certificate_detail)) {
                        $completion_status = "yet to start";
                        $completion_date = "";
                    }else {
                        
                        $completion_percentage = $certificate_detail->completion;
                        if ($completion_percentage >= 100) {
                            $completion_status = "completed";
                        } elseif (($completion_percentage > 0) && ($completion_percentage < 100)) {
                            $completion_status = "inprogress";
                        }
                        if (!is_null($certificate_detail->score)) {
                            $avg_score = $certificate_detail->score;
                        }
                        if (!empty($certificate_detail->is_certificate_generated)) {
                            $certificate_status = "issued";
                        }
                        if ($completion_status == "inprogress") {
                            $certificate_status = "not issued";
                        }
                        $completed_at = array_get($certificate_detail->completed_at, 0, $certificate_detail->updated_at);
                        if (!empty($completed_at)){
                            $completion_date = Timezone::convertFromUTC($completed_at, config('app.default_timezone'), 'd-m-Y');
                        }
                        
                    }
                    $a_certificate_detail = [];
                    $a_certificate_detail = [
                        'channel_id' => $program_id,
                        'name' => $program_title,
                        'slug' => $program_slug,
                        'completion_status' => $completion_status,
                        'completion_percentage' => $completion_percentage,
                        'avg_score' => $avg_score,
                        'certificate_status' => $certificate_status,
                        'completion_date' => $completion_date

                    ];
                    $certificate_details[] = $a_certificate_detail;
                    if(!empty($channel_id)) {
                        $certificate_details = array_first($certificate_details);
                    } 
                }
                
                if (empty($certificate_details)) {
                    throw new NoProgramAssignedException();
                }
                return response()->json(['email' => $email_id, 'channels' => $certificate_details]);
            } else {
                $message = $validation->getMessageBag()->toArray();
                $message = implode("", array_flatten($message));
                return response()->json(['status' => '400', 'message' => $message]);
            }   

        } catch (UserNotFoundException $e) {
            $status = '401';
            $message = 'User does not exists.';
        } catch (ProgramNotFoundException $e) {
            $status = '401';
            $message = 'Channel does not exists.';
        } catch (SSOInvalidTokenException $e) {
            $status = '401';
            $message = 'Invalid access token.';
        } catch (SSOTokenExpiredException $e) {
            $status = '401';
            $message = 'Token Expired.';
        }  catch (NoProgramAssignedException $e) {
            $status = '204';
            $message = 'No Channels assigned to this user.';
        }  catch (Exception $e) {
            $status = '500';
            $message = 'Internal server error';
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
        }
        Log::info($message);
        return response()->json(['status' => $status, 'message' => $message]);
    }

}