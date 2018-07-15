<?php

namespace App\Http\Middleware;

use App\Services\Role\IRoleService;
use Illuminate\Support\Facades\Cache;
use Closure;

class RedirectIfCpAccessible
{
    /**
     * @var IRoleService
     */
    private $roleService;

    /**
     * RedirectIfCpAccessible constructor.
     * @param IRoleService $roleService
     */
    public function __construct(IRoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (has_admin_portal_access()) {
            if (!Cache::has($request->user()->uid)) {
                $this->roleService->createUserPermissionsListCacheFile($request->user()->uid);
            }
            return $next($request);
        } else {
            return redirect(url("/"));
        }
    }
}
