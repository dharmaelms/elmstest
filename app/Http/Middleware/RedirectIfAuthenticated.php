<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Input;

class RedirectIfAuthenticated
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
        if (Auth::guard($guard)->check()) {
            if (Input::get('view') == 'iframe') {
                return response(view('admin.theme.common.iframeredirect'));
            }
            if ($request->input('login_popup') === "yes") {
                return "yes";
            }
            return redirect(url('/'));
        }

        return $next($request);
    }
}
