<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Input;
use App\Enums\User\NDAStatus as NDA;
use App\Model\SiteSetting;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized', 401);
            } else {
                if (Input::get('view') == 'iframe') {
                    return response(view('admin.theme.common.iframeredirect'));
                }
                return redirect()->guest('auth/login');
            }
        } else {
            $nda_enabled = SiteSetting::module('UserSetting', 'nda_acceptance');
            if ($nda_enabled == 'on') {
                if ($request->user()->nda_status != NDA::ACCEPTED) {
                    $url = $request->path();
                    return redirect(url('auth/user-nda?redirect_url='.$url));
                }
            }
        }
        return $next($request);
    }
}
