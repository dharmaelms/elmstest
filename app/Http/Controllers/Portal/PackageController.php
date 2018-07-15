<?php
namespace App\Http\Controllers\Portal;

use Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\Package\IPackageService;
use App\Http\Controllers\PortalBaseController;
use App\Exceptions\Package\PackageNotFoundException;

/**
 * Class PackageController
 * @package App\Http\Controllers\Portal
 */
class PackageController extends PortalBaseController
{
    /**
     * @var App\Services\Package\IPackageService
     */
    private $package_service;

    /**
     * Construct and create package service instance
     *
     * @param App\Services\Package\IPackageService
     */
    public function __construct(IPackageService $package_service)
    {
        $this->package_service = $package_service;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->package_service = $package_service;
    }

    /**
     * List assigned package to users
     *
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request, $page = 1, $limit = 10)
    {
        try {
            $filter = $request->input('filter', 'new');
            $categories = $request->input('categories', []);
            $others = $request->input('others', []);
            $data = $this->package_service->getPackageList($page, $limit, $filter, $categories, $others);
            return view($this->theme_path.'.packages.list', ['data' => $data]);
        } catch (\Exception $e) {
            Log::info('error in package retrieval. ' . $e->getMessage());
            return response()->json(['status' => false]);
        }
    }

    /**
     * Method to get details of package with channels and categories
     *
     * @param string $slug
     * @return Response
     */
    public function getDetail($slug)
    {
        try {
            $data = $this->package_service->getPackageDetails($slug);
            $this->layout->pagetitle = $data->package_title;
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.packages.detail')->with('package', $data);
        } catch (PackageNotFoundException $e) {
            Log::info($slug . ' not found');
            abort(404);
        }
    }
}
