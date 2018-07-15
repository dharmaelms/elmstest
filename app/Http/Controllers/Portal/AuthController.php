<?php

namespace App\Http\Controllers\Portal;

use App\Enums\RolesAndPermissions\SystemRoles;
use App\Events\Auth\Registered;
use App\Enums\User\NDAStatus as NDA;
use App\Http\Controllers\PortalBaseController;
use App\Model\Common;
use App\Model\MyActivity;
use App\Model\Role;
use App\Model\SiteSetting;
use App\Model\User;
use App\Services\Catalog\AccessControl\IAccessControlService;
use App\Services\Leadsquared\ILeadsquaredService;
use App\Services\Playlyfe\IPlaylyfeService;
use Auth;
use Carbon\Carbon;
use File;
use Input;
use Laravel\Socialite\Facades\Socialite;
use Log;
use Request;
use Session;
use Timezone;
use URL;
use Validator;

class AuthController extends PortalBaseController
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    //TODO: Customer specific authentication logic should be moved from core

    protected $playlyfe;

    protected $accSer;

    protected $Leadsquared;

    /**
     * AuthController constructor.
     * @param Request $request
     * @param IPlaylyfeService $playlyfe
     * @param IAccessControlService $accessControlService
     * @param ILeadsquaredService $Leadsquared
     */
    public function __construct(
        Request $request,
        IPlaylyfeService $playlyfe,
        IAccessControlService $accessControlService,
        ILeadsquaredService $Leadsquared
    )
    {

        parent::__construct();

        $input = $request::input();
        $this->accSer = $accessControlService;
        array_walk(
            $input,
            function (&$i) {
                (is_string($i)) ? $i = htmlentities($i) : '';
            }
        );
        $request::merge($input);

        $this->playlyfe = $playlyfe;
        $this->Leadsquared = $Leadsquared;

        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->middleware('guest', ['except' => ['getLogout', 'getSample', 'getUserNda', 'postUserNda']]);
    }

    public function getIndex()
    {
        if (Auth::check()) {
            return redirect('/');
        } else {
            return redirect()->guest('/');
        }
    }

    public function getLogin()
    {
        return redirect('/');
    }

    public function postLogin()
    {
        /* allowing username as email*/
        $login_status = false;
        if (strpos(Input::get('email'), '@') !== false) {
            $user = [
                'email' => Input::get('email'),
                'password' => Input::get('password'),
                'status' => 'ACTIVE',
            ];

            if (Auth::attempt($user)) {
                $login_status = true;
            } else {
                array_pull($user, 'email');
                $user['username'] = Input::get('email');
                if (Auth::attempt($user)) {
                    $login_status = true;
                }
            }
        } else {
            $username = Input::get('email');
            Input::merge(['email' => strtolower($username)]);
            $user = [
                'username' => Input::get('email'),
                'password' => Input::get('password'),
                'status' => 'ACTIVE',
            ];

            if (Auth::attempt($user)) {
                $login_status = true;
            }
        }

        if ($login_status) {
            $uid = Auth::user()->uid;
            $array = [
                'module' => 'general',
                'action' => 'Sign In',
                'module_name' => '',
                'module_id' => '',
                'url' => Request::path(),
            ];

            $this->cronSubscription($uid);
            MyActivity::getInsertActivity($array);
            //PlayLyfe Login
            $this->loginPlayLyfe($uid);
            //ends
            User::getUpdateLastLogin($uid);
            User::updateSessionID($uid);
            $login_popup = Input::get('login_popup');
            if ($login_popup === 'yes') {
                return "yes";
            }
        } else {
            if (is_array($user)) {
                if (empty($user['username']) && (empty($user['password']))) {
                    return trans('user.email_password_error');
                }

                if (empty($user['username'])) {
                    return trans('user.email_required');
                }
                if (empty($user['password'])) {
                    return trans('user.password_required');
                }
            }

            $login_popup = Input::get('login_popup');
            if ($login_popup === 'yes') {
                return trans('user.login_error');
            }
            Input::flush();

            return redirect()->back()->with('error', trans('user.login_error'));
        }
    }

    protected function cronSubscription($uid)
    {
        $this->accSer->unEnrollSubscription($uid);
    }

    public function getLogout()
    {
        if (Auth::check()) {
            $uid = Auth::user()->uid;
            $path = Request::input('nda_logout');
            (!is_null($path)) ? $url = 'auth/nda_logout' : $url = Request::path();

            MyActivity::getInsertActivity([
                'module' => 'general',
                'action' => 'Sign Out',
                'module_name' => '',
                'module_id' => '',
                'url' => $url,
            ]);

            /* Below if condition is to update the user table when the nda_status != 'Accepted' and to insert the time field*/
            $user = Auth::user();
            if ($url == 'auth/nda_logout') {
                if ($user->nda_status != NDA::ACCEPTED) {
                    $user->nda_status = NDA::DECLINED;
                    $user->nda_response_time = time();
                    $user->save();
                }
            }

            $path = storage_path() . '/framework/sessions/' . Session::getId();
            User::removeSessionID((int)$uid);

            Auth::logout();

            if (config('app.enable_saml') === 'on') {
                Session::put('saml_login', false);
                Session::put('saml_logged_out', true);
            }

            if (File::exists($path)) {
                File::delete($path);
            }
            //redirect(moodle30/login/logout.php);
            //Playlyfe integration code for Logout action.
            //Added by Muniraju N.

            // $currentTime = Carbon::now()->timestamp;
            // $lastLoginTime = User::getLastLoginTime($uid);
            // $playlyfeEvent = [
            //     "type" => "action",
            //     "data" => [
            //        "user_id" => $uid,
            //        "action_id" => "logout",
            //        "session_duration" => $currentTime - $lastLoginTime
            //     ]
            // ];

            // $this->playlyfe->processEvent($playlyfeEvent);

            //Playlyfe integration code ends.
        }

        return redirect('/');
    }

    public function getRegister()
    {
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.auth.register')
            // ->with('customFieldList', $customFieldList)
            ->with('timezones', Timezone::get())
            ->with('frequent_tz', Timezone::frequent());
    }

    public function postRegister()
    {
        Input::flash();
        $username = Input::get('username');
        $email = Input::get('email');
        $firstname = Input::get('firstname');
        $lastname = Input::get('lastname');
        $mobile = Input::get('mobile');

        Input::merge(['username' => strtolower(trim($username))]);
        Input::merge(['email' => strtolower($email)]);
        Input::merge(['firstname' => trim($firstname)]);
        Input::merge(['lastname' => trim($lastname)]);

        $rules = [
            'firstname' => 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'lastname' => "Min:2|Max:30|Regex:/^([A-Za-z\'. ])+$/",
            'email' => 'Required|email|unique:users',
            // 'mobile' => 'Required|Max:15|Regex:/^((\+){0,1}91(\s){0,1}(\-){0,1}(\s){0,1})?([0-9]{10, 15})$/',
            'mobile' => 'required|max:15|regex:/[0-9+-]{10,15}$/',
            'username' => 'Required|Min:3|Max:35|unique:users|checkUserNameRegex|checkusername:'. strtolower(trim($username)) . '',
            // 'password' => 'Required|Min:6|Max:24|Regex:/^[0-9A-Za-z!^@#$%_.-()]{6,24}$/|Confirmed',
            'password' => 'Required|Min:6|Max:24|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|Confirmed',
            'password_confirmation' => 'Required',
        ];

        $niceNames = [
            'firstname' => strtolower(trans('user.first_name')),
            'lastname' => strtolower(trans('user.lastname')),
            'email' => strtolower(trans('user.email')),
            'username' => strtolower(trans('user.username')),
            'mobile' => strtolower(trans('user.mobile')),
            'password' => strtolower(trans('user.password')),
            'password_confirmation' => strtolower(trans('user.password_confirmation')),
        ];

        $messages = [];
        $messages += [
            // 'checkUserNameRegex' => 'Symbolic characters not allowed',
            'password.regex' => trans('admin/user.password_regex_msg'),
            'firstname.regex' => 'Numbers & symbols are not allowed except _ & -',
            'lastname.regex' => 'Numbers & symbols are not allowed except _ & -',
            'firstname.required' => trans('user.first_name_required'),
            'firstname.min' => trans('user.first_name_min'),
            'firstname.max' => trans('user.first_name_max'),
            'lastname.min' => trans('user.last_name_min'),
            'lastname.max' => trans('user.last_name_max'),
            'checkusername' => trans('user.check_username_exists'),
        ];

        Validator::extend('checkUserNameRegex', function ($attribute, $value, $parameters) {
            $pattern = (strpos($value, '@') !== false) ? "/^([a-zA-Z0-9])(([a-zA-Z0-9])*([\._-])?([a-zA-Z0-9]))*@(([a-zA-Z0-9\-])+(\.))+([a-zA-Z]{2,4})+$/" : "/^[a-zA-Z0-9._]*$/";
            return preg_match($pattern, $value);
        });

        Validator::extend('checkusername', function ($attribute, $value, $parameters) {
            $username = strtolower($parameters[0]);
            $return_val = User::where('username', 'like', $username)->get(['uid'])->toArray();
            if (empty($return_val)) {
                return true;
            }
            return false;
        });

        $validation = Validator::make(Input::all(), $rules, $messages, $niceNames);
        if ($validation->fails()) {
            $register_popup = Input::get('register_popup');
            if ($register_popup === 'yes') {
                return $validation->messages()->toJson();
            }
            return redirect('auth/register')
                ->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $registered_user_role_id = $this->roleService->getRoleDetails(SystemRoles::REGISTERED_USER)["id"];
            $userID = User::getRegisterUser(array_merge(Input::all(), ["role_id" => $registered_user_role_id]));

            event(new Registered($userID));

            // leadsquared registration starts
            if (config("app.leadsquared.enabled")) {
                $this->registerLeadsquared($firstname, $email, $mobile);
            }

            $this->registerPlayLyfe($userID, Input::get('username'), Input::get('firstname'));
            $this->registerPointsPlayLyfe($userID);
            $register_popup = Input::get('register_popup');
            if ($register_popup === 'yes') {
                return "success";
            }
            Input::flush();
            return redirect('auth/register')
                ->with('success', trans('user.register_success'));
        }
    }

    protected function registerPlayLyfe($userID, $u_Name, $f_Name)
    {
        $this->playlyfe->processEvent([
            "type" => "create-user",
            "data" => [
                "user_id" => $userID,
                "player_id" => $u_Name,
                "player_alias" => $f_Name
            ]
        ]);

        return;
    }

    protected function registerPointsPlayLyfe($userID)
    {
        $this->playlyfe->processEvent([
            "type" => "action",
            "data" => [
                "user_id" => $userID,
                "action_id" => "signup"
            ]
        ]);

        return;
    }

    protected function loginPlayLyfe($uid)
    {
        $currentLoginTime = Carbon::now()->timestamp;
        $lastLoginTime = User::getLastLoginTime($uid);

        if (is_null($lastLoginTime) || (!Carbon::createFromTimestamp($lastLoginTime)->isToday())) {
            if (is_null($lastLoginTime)) {
                $lastLoginTime = $currentLoginTime;
            }

            $playlyfeEvent = [
                "type" => "action",
                "data" => [
                    "user_id" => $uid,
                    "action_id" => "login",
                    "current_login" => $currentLoginTime,
                    "last_login" => $lastLoginTime
                ]
            ];

            $this->playlyfe->processEvent($playlyfeEvent);
        }
        return;
    }

    public function redirectToProvider($provider)
    {
        $channel_slug = request()->input('subscription_slug');
        Session::put('registration_url', URL::previous());
        return Socialite::driver($provider)->with(['state' => $channel_slug])->redirect();
    }

    protected function prepareFacebookData($user, $provider)
    {
        $firstname = $lastname = '';
        $name = explode(" ", $user->user['name']);
        if (isset($name[0])) {
            $firstname = $name[0];
        }
        if (isset($name[1])) {
            $lastname = $name[1];
        }
        if (isset($user->user['first_name'])) {
            $firstname = $user->user['first_name'];
        }
        if (isset($user->user['last_name'])) {
            $lastname = $user->user['last_name'];
        }

        return [
            'email' => $user->getEmail(),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'provider' => $provider
        ];
    }

    protected function prepareGoogleData($user, $provider)
    {
        return [
            'email' => $user->getEmail(),
            'firstname' => $user->user['name']['givenName'],
            'lastname' => $user->user['name']['familyName'],
            'provider' => $provider
        ];
    }

    protected function socialiteRegister($provider, $user)
    {
        // stroing data to our use table
        switch ($provider) {
            case 'facebook':
                $data = $this->prepareFacebookData($user, $provider);
                break;
            case 'google':
                $data = $this->prepareGoogleData($user, $provider);
                break;
        }
        $data["role_id"] = $this->roleService->getRoleDetails(SystemRoles::REGISTERED_USER)["id"];
        $regUser = User::registerSocialite($data);
        event(new Registered($regUser));
        if ($regUser === "abort") {
            return "abort";
        }
        if ($regUser != "no") {
            $this->registerPlayLyfe($regUser, $data['email'], $data['firstname']);

            // leadsquared registration starts
            if (config("app.leadsquared.enabled")) {
                $this->registerLeadsquared($data['firstname'], $data['email'], "");
            }
            // leadsquared registration ends
        }
        if ($regUser === "no") {
            return "no";
        }
        return;
    }

    protected function socialiteLogin($provider, $user)
    {
        $authentication = [
            'email' => $user->getEmail()
        ];

        Auth::login(User::firstOrCreate($authentication));
        $this->logUserData($provider);
    }

    protected function logUserData($provider)
    {
        $uid = Auth::user()->uid;

        $array = [
            'module' => 'general',
            'action' => 'Sign In',
            'module_name' => '',
            'module_id' => '',
            'url' => Request::path(),
            'socialite' => $provider
        ];

        $this->logUserLogData($array, $uid);
        return;
    }

    protected function logUserLogData($array, $uid)
    {
        MyActivity::getInsertActivity($array);
        User::getUpdateLastLogin($uid);
        User::updateSessionID($uid);
        $this->loginPlayLyfe($uid);
    }

    public function handleProviderCallback($provider)
    {
        //notice we are not doing any validation, you should do it
        //
        if (Input::has('error')) {
            return redirect('/');
        }
        if (Input::has('error_code')) {
            return redirect('/');
        }

        $user = Socialite::driver($provider)->stateless()->user();
        if (!empty($user->getEmail())) {
            $register = SiteSetting::module('Socialite', 'register');
            if ($register === 'on') {
                $data = $this->socialiteRegister($provider, $user);
                if ($data === "abort") {
                    return parent::getError($this->theme, $this->theme_path, 401, "We found that your account is not in use, please contact Site Admin. ");
                }
                $login = SiteSetting::module('Socialite', 'login');
                if ($login === 'on') {
                    $this->socialiteLogin($provider, $user);
                }
            }
        } else {
            return parent::getError($this->theme, $this->theme_path, 401, "We found that you have provided your phone number as primary email in Facebook. Please provide primary email id and try to signing.");
        }

/* While doing socialite login to check any program and subscription slug is present, if present auto redirecting to dashboard  */
        $redirect_url = request()->input('state');

        if (!is_null($redirect_url)) {
            if (str_contains($redirect_url, "general-")) {
                $slug = explode('/', substr($redirect_url, 8));
                return redirect(URL::to('program/enroll-user-to-product/'.$slug[0].'/'.$slug[1]));
            } elseif (str_contains($redirect_url, "restricted-")) {
                $slug = explode('/', substr($redirect_url, 11));
                return redirect(URL::to('/checkout/place-order/'.$slug[0].'/'.$slug[1]));
            }
        }
        /* To check the user has already registered or not */
        if ($data == "no") {
            $url = (config('app.enable_registration_redirect') == true) ? config('app.registration_default_redirect') : config('app.socialite_redirect');
            return redirect($url);
        } else {
            return redirect(Session::get('registration_url'));
        }
    }

    public function registerLeadsquared($uname, $email, $mobile)
    {
        $this->Leadsquared->processLeads([
            "type" => "convert-lead",
            "data" => [
                "name" => $uname,
                "email" => $email,
                "mobile" => $mobile

            ]
        ]);
    }

    public function getVerifyMe($auth_key)
    {
        if (!is_string($auth_key)) {
            return redirect('/');
        }

        if (true === User::verfiyAuthKey($auth_key)) {
            $result = User::updateEmailVerification($auth_key);

            Session::put(
                'authentication',
                [
                    'email' => $result['email'],
                    'security_key' => time(),
                    'current_url' => urldecode(Request::get('current_url')),
                    'catalog_url' => Request::get('catalog_url'),
                    'posts_url' => Request::get('posts_url')
                ]
            );

            Session::flash('email_verify_status', true);
        } else {
            Session::flash('email_verify_status', false);
        }
        return redirect('auth/finish-email-verification');
    }

    public function getRegisterSuccess()
    {
        if (Auth::check()) {
            return redirect(config('app.redirect_default_login'));
        }

        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.auth.register_success');
    }

    public function getFinishEmailVerification()
    {
        if (Auth::check()) {
            return redirect(config('app.redirect_default_login'));
        }

        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.auth.finish_register_verification')
            ->with('email_verify_status', Session::get('email_verify_status'));
    }

    public function getAutoLogin()
    {
        if (Auth::check()) {
            return redirect(config('app.redirect_default_login'));
        }

        $security_key = Request::get('security_key');
        $authentication = Session::get('authentication');

        if (is_null($security_key) || is_null($authentication)) {
            return parent::getError($this->theme, $this->theme_path, 401);
        }

        if ($security_key == $authentication['security_key']) {
            Auth::login(User::firstOrCreate(['email' => $authentication['email']]));

            $array = [
                'module' => 'general',
                'action' => 'Sign In',
                'module_name' => '',
                'module_id' => '',
                'url' => Request::path()
            ];
            $this->logUserLogData($array, Auth::user()->uid);

        /* During registration if subcription slug is present then redirecting to the checkout page */
            if (isset($authentication['catalog_url']) && !empty($authentication['catalog_url'])) {
                $slug = explode('/', $authentication['catalog_url']);
                Log::info('Subscribe programs details during registration: ', ['Date' => date("Y-m-d H:i:s"), 'Program Slug' => $slug[0], 'Program Subscription Slug' => $slug[1], 'Post Slug' => " ", 'Registration Mode' => " "]);
                return redirect(URL::to('/checkout/place-order/'.$slug[0].'/'.$slug[1]));
            }

        /* During registration if post slug is present then redirecting to the packet page */
            if (isset($authentication['posts_url']) && !empty($authentication['posts_url'])) {
                $post = explode('/', $authentication['posts_url']);
                Log::info('Subscribe programs details during registration: ', ['Date' => date("Y-m-d H:i:s"), 'Program Slug' => $post[0], 'Program Subscription Slug' => " ", 'Post Slug' => $post[1], 'Registration Mode' => " "]);
                return redirect(URL::to('program/enroll-user-to-product/'.$post[0].'/'.$post[1]));
            }

            if (config('app.enable_registration_redirect') == true) {
                return redirect(config('app.registration_default_redirect'));
            } else {
                return redirect(URL::to($authentication['current_url']));
            }
        } else {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
    }

    public function saml($attribute = '')
    {
        if (config('app.enable_saml') === 'on') {
            $arr = [];
            $array_var = unserialize(base64_decode($attribute));

            if (is_array($array_var) && !empty($array_var)) {
                $arr['firstname'] = '';
                $arr['lastname'] = '';
                $arr['email'] = '';
                $arr['username'] = '';

                foreach ($array_var as $key => $val) {
                    if (stristr($key, 'upn')) {
                        $arr['email'] = strtolower(trim($array_var[$key][0]));
                        $arr['username'] = $arr['email'];
                    }

                    if (stristr($key, 'uid')) {
                        $arr['firstname'] = $array_var[$key][0];
                    }

                    if (stristr($key, 'windowsaccountname')) {
                        $arr['username'] = strtolower(trim($array_var[$key][0]));
                    }

                    if (stristr($key, 'givenname')) {
                        $arr['firstname'] = $array_var[$key][0];
                    }


                    if (stristr($key, 'CommonName')) {
                        $pieces = explode(" ", $array_var[$key][0]);
                        $arr['firstname'] = (!empty($pieces[0])) ? $pieces[0] : "";
                        $arr['lastname'] = (!empty($pieces[1])) ? $pieces[1] : "";
                    }
                }
            }

            $arr['password'] = time();
            $arr['mobile'] = '0000000000';
            $user_exits = User::checkIfUserNameExists($arr['username']);

            if ($user_exits <= 0) {
                $user_exits = User::samlRegister($arr);
            }

            if ($user_exits > 0) {
                $uid = User::getIdBy($arr, 'username');
                $authentication = ['username' => $arr['username'], 'uid' => $uid];
                Auth::login(User::firstOrCreate($authentication));

                Session::put('saml_login', true);
                $uid = Auth::user()->uid;

                $array = [
                    'module' => 'general',
                    'action' => 'Sign In',
                    'module_name' => 'SAML',
                    'module_id' => '',
                    'url' => Request::path(),
                ];
                MyActivity::getInsertActivity($array);
                User::getUpdateLastLogin($uid);
                User::updateSessionID($uid);
            }

            unset($arr, $array, $array_var, $authentication);
        }
        if (Auth::check()) {
            $redirect_url = parse_url(Session::get('url')['intended']);
            if(strlen(array_get($redirect_url, 'path', '')) > 1) {
                return redirect($redirect_url['path'].'?'.array_get($redirect_url, 'query', ''));
            } else {
                return redirect('/dashboard');
            }
        } else {
            return redirect('/');
        }
    }

    /**
     * Method to load the NDA page
     *
     */
    public function getUserNda()
    {
        $this->layout = view($this->theme_path . '.layout.one_columnlayout_frontend')->with('logo', true);
        $this->layout->content = view($this->theme_path . '.auth.usernda')->with('redirect_url', Request::input('redirect_url'));
    }

    /**
     * Method to update the user collection when NDA is accepted by user.
     * Redirects to the current URL
     */
    public function postUserNda()
    {
        $user = Auth::user();
        if (!is_null($user)) {
            if ($user->nda_status != NDA::ACCEPTED) {
                $user->nda_status = NDA::ACCEPTED;
                $user->nda_response_time = time();
                $user->save();
            }
        }
        if (Request::input('redirect_url')) {
            return redirect('/' . Request::input('redirect_url'));
        } else {
            return redirect('/dashboard');
        }
    }
}
