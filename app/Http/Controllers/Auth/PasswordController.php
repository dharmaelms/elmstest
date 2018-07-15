<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\PortalBaseController;
use App\Model\User;
use Common;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PasswordController extends PortalBaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->subject = 'Your Password Reset Link';
        $this->middleware('guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function getEmail()
    {
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.auth.forgot_password');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postEmail(Request $request)
    {
        $request->merge(['email' => strtolower($request->email)]);
        return $this->sendResetLinkEmail($request);
    }

    /**
     * Validate the request of sending reset link.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    protected function validateSendResetLinkEmail(Request $request)
    {
        $this->validate(
            $request,
            ['email' => 'required|email|exists:users,email,status,ACTIVE'],
            ['exists' => trans('passwords.user')]
        );
    }

    /**
     * Get reset password page where user can
     * fill in their new password
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getReset(Request $request, $token)
    {
        $email = old('email');
        if (empty($email)) {
            $reset = \DB::collection(config('auth.passwords.users.table'))
                ->where('token', '=', $token)
                ->first();

            if (empty($reset)) {
                return redirect('password/forgot');
            }

            $email = $reset['email'];
        }

        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.auth.reset')
            ->with('email', $email)
            ->with('token', $token);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postReset(Request $request)
    {
        return $this->reset($request);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param  string $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();
    }

    /**
     * Get the response for after a successful password reset.
     *
     * @param  string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResetSuccessResponse($response)
    {
        // On success of reset password, update LMS user account password if needed
        User::updateLmsPassword($this->request->get('email'), $this->request->get('password'));

        return redirect()->back()
            ->withInput(['email' => $this->request->get('email')])
            ->with('status', trans('passwords.reset'));
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function getResetValidationRules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|max:24|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|confirmed',
        ];
    }

    protected function getResetValidationMessages()
    {
        return [
            'password.regex' => trans('admin/user.password_regex_msg'),
        ];
    }
}
