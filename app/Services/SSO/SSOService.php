<?php
namespace App\Services\SSO;

use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\SSOTokenStatus;
use App\Enums\User\UserEntity;
use App\Enums\User\UserStatus;
use App\Events\Auth\Registered;
use App\Events\User\EntityEnrollmentThroughUserGroup;
use App\Exceptions\SSO\InSecureConnectionException;
use App\Exceptions\SSO\InvalidCredentialsException;
use App\Exceptions\SSO\InvalidRequestException;
use App\Exceptions\SSO\MissingMandatoryFieldsException;
use App\Exceptions\SSO\SSOInvalidTokenException;
use App\Exceptions\SSO\SSOTokenExpiredException;
use App\Exceptions\SSO\SSOTokenNotFoundException;
use App\Exceptions\User\InActiveUserException;
use App\Exceptions\User\UserNotFoundException;
use App\Model\SSO\Repository\ISSOLogRepository;
use App\Model\User\Repository\IUserRepository;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Model\Common;
use App\Model\MyActivity\Repository\IMyActivityRepository;
use App\Model\Role;
use App\Model\User;
use App\Services\Role\IRoleService;
use Auth;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Session;

/**
 * class SSOService
 * @package App\Services\SSO
 */
class SSOService implements ISSOService, AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins, Authenticatable, Authorizable, CanResetPassword;

    /**
     * @var App\Model\MyActivity\Repository\IMyActivityRepository
     */
    private $activity_repository;

    /**
     * @var App\Services\Role\IRoleService
     */
    private $role_service;

    /**
     * @var App\Model\SSO\Repository\ISSOLogRepository
     */
    private $sso_log_repository;

    /**
     * @var App|Model|User|Repository|IUserRepository
     */
    private $user_repository;

    /**
     * @var App\Model\UserGroup\Repository\IUserGroupRepository
     */
    private $usergroup_repository;

    public function __construct(
        IMyActivityRepository $activity_repository,
        IUserRepository $user_repository,
        ISSOLogRepository $sso_log_repository,
        IRoleService $role_service,
        IUserGroupRepository $usergroup_repository
    ) {
        $this->activity_repository = $activity_repository;
        $this->user_repository = $user_repository;
        $this->sso_log_repository = $sso_log_repository;
        $this->role_service = $role_service;
        $this->usergroup_repository = $usergroup_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function generateAccessToken($request)
    {
        $user = $this->user_repository->findByEmail($request->input('email_id'))->first();
        if (!is_null($user)) {
            if ($user->status != UserStatus::ACTIVE) {
                throw new InActiveUserException();
            }
            if (array_get($user, 'sso_token.status') == SSOTokenStatus::USED) {
                $access_token = str_random(60);
                $user->sso_token = ['token' => $access_token, 'expired_at' => time() + 300, 'status' => SSOTokenStatus::NOT_USED];
            } elseif (array_get($user, 'sso_token.status') == SSOTokenStatus::NOT_USED) {
                $access_token = $user->sso_token['token'];
                $user->sso_token = ['token' => $access_token, 'expired_at' => time() + 300, 'status' => SSOTokenStatus::NOT_USED];
            } else {
                $access_token = str_random(60);
                $user->sso_token = ['token' => $access_token, 'expired_at' => time() + 300, 'status' => SSOTokenStatus::NOT_USED];
            }
            $user->save();
            return $access_token;
        } else {
            $request->merge(['role_id' => $this->role_service->getRoleDetails(SystemRoles::REGISTERED_USER)["id"]]);
            $user = $this->user_repository->newSSOUser($request);
            event(new Registered($user['uid']));
            $this->assignUserToGroup($user['uid'], config('app.sso.usergroup_id'));
            return $user['access_token'];
        }
    }


    /**
     * {@inheritdoc}
     */
    public function validataRequest($request)
    {
        if (!$request->isJson() || !$request->has('client_secret') || !$request->has('client_id')) {
            throw new InvalidRequestException;
        }
        if ($request->client_secret != config('app.sso.client_secret') || $request->client_id != config('app.sso.client_id')) {
            throw new InvalidCredentialsException;
        }
        if (!$request->has('email_id') || !$request->has('first_name')) {
            throw new MissingMandatoryFieldsException;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateToken($request)
    {
        $user = $this->user_repository->findByEmail($request->input('email'))->first();
        if (!is_null($user)) {
            if ($user->status != UserStatus::ACTIVE) {
                throw new InActiveUserException();
            }
            $log_data = [
                'url' => $request->url(),
                'parameters' => $request->all(),
                'created_at' => time(),
            ];
            if (!array_get($user, 'sso_token', false)) {
                throw new SSOTokenNotFoundException();
            }
            if ($user->sso_token['status'] == SSOTokenStatus::USED) {
                $log_data['message'] = trans('sso.token_used');
                throw new SSOInvalidTokenException();
            } elseif ($user->sso_token['expired_at'] < time()) {
                $log_data['message'] = trans('sso.token_expired');
                throw new SSOTokenExpiredException();
            } elseif ($user->sso_token['token'] == $request->input('token')) {
                Auth::Login($user, true);
                $log_data['message'] = trans('sso.login_success');
                $token = $user->sso_token;
                $token['status'] = SSOTokenStatus::USED;
                $array = [
                    'module' => 'general',
                    'action' => 'Sign In',
                    'module_name' => 'SSO',
                    'module_id' => '',
                    'url' => $request->path(),
                ];
                $this->activity_repository->addActivity($array);
                $session_ids[] = Session::getId();
                $last_login_time = Carbon::now()->timestamp;
                User::where('uid', (int)$user->uid)->update(['session_ids' => $session_ids, 'sso_token' => $token,
                    'last_login_time' => $last_login_time]);
            } else {
                throw new SSOInvalidTokenException();
            }
            $this->sso_log_repository->addLog($log_data);
        } else {
            throw new UserNotFoundException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function log($request, $message, $status, $response = '')
    {
        $log_data = [
            'url' => $request->url(),
            'parameters' => $request->all(),
            'created_at' => time(),
            'message' => $message,
            'status' => $status,
        ];
        if (!empty($response)) {
            $log_data['response'] = $response;
        }
        $this->sso_log_repository->addLog($log_data);
    }

    /**
     * {@inheritdoc}
     */
    public function assignUserToGroup($user_id, $usergroup_id)
    {
        $usergroup = $this->usergroup_repository->get(['ugid' => (int)$usergroup_id])->first()->toArray();
        if (!empty($usergroup)) {
            $role_info = $this->role_service->getRoleDetails(SystemRoles::LEARNER, ['context']);
            $context_info = $this->role_service->getContextDetails(Contexts::PROGRAM, false);
            $role_id = array_get($role_info, 'id', '');
            $context_id = array_get($context_info, 'id', '');
            $relations = array_get($usergroup, 'relations', '');
            $instance_ids = array_get($usergroup, 'relations.usergroup_feed_rel', []);
            if (!empty($instance_ids)) {
                foreach ($instance_ids as $instance_id) {
                    event(new EntityEnrollmentThroughUserGroup(
                        $user_id,
                        UserEntity::PROGRAM,
                        $instance_id,
                        (int)$usergroup_id
                    ));
                    $this->role_service->mapUserAndRole($user_id, $context_id, $role_id, $instance_id);
                }
            }
            $relations['active_user_usergroup_rel'] = array_merge(array_get($usergroup, 'relations.active_user_usergroup_rel', []), [$user_id]);
            $this->usergroup_repository->updateByKey('ugid', (int)$usergroup_id, ['relations' => $relations]);
        }
    }
}
