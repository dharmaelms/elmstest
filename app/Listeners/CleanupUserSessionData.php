<?php

namespace App\Listeners;

use App\Services\Role\IRoleService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Session;

class CleanupUserSessionData
{
    /**
     * @var IRoleService
     */
    private $roleService;

    /**
     * Create the event listener.
     * @param IRoleService $roleService
     */
    public function __construct(IRoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $this->roleService->deleteUserPermissionsListCacheFile($event->user->uid);
        Session::flush();
    }
}
