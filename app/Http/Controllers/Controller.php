<?php namespace App\Http\Controllers;

use App\Services\Role\IRoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    /**
     * @var IRoleService
     */
    protected $roleService;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \Illuminate\Container\Container
     */
    protected $app;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->app = App::make("app");
        $this->request = $this->app["request"];
        $this->roleService = $this->app->make(IRoleService::class);
    }
}
