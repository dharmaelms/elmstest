<?php namespace App\Http\Controllers;

use App\Exceptions\Common\AssetNotFoundException;
use App\Exceptions\Common\WrongAssetRequestException;
use Session;
use Auth;

class AdminBaseController extends Controller
{
    /**
     * AdminBaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function setupLayout()
    {
        if (isset($this->layout)) {
            $this->layout = view($this->layout);
        }
    }

    public function callAction($method, $parameters)
    {
        $this->setupLayout();
        $response = call_user_func_array([$this, $method], $parameters);
        if (is_null($response) && isset($this->layout) && !is_null($this->layout)) {
            $response = $this->layout;
        }
        return $response;
    }

    /**
     * @param string $theme_path
     */
    public function getAdminError($theme_path = 'admin.theme')
    {
        $this->layout->pagetitle = 'Error';
        $this->layout->pageicon = '';
        $this->layout->pagedescription = '';
        $this->layout->header = view($theme_path . '.common.header');
        $this->layout->footer = view($theme_path . '.common.footer');
        $this->layout->sidebar = view('admin.theme.common.sidebar');
        $this->layout->content = view($theme_path . '.common.admin_error');
    }

    public function getAdmin404Error($theme_path = 'admin.theme')
    {
        $this->layout->pagetitle = 'Error';
        $this->layout->pageicon = '';
        $this->layout->pagedescription = '';
        $this->layout->header = view($theme_path . '.common.header');
        $this->layout->footer = view($theme_path . '.common.footer');
        $this->layout->sidebar = view('admin.theme.common.sidebar');
        $this->layout->content = view($theme_path . '.common.admin_404_error');
    }

    /**
     * Downloads requested file
     * @param  $template string
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws AssetNotFoundException
     * @throws WrongAssetRequestException
     */
    public function downloadTemplate($template)
    {
        switch ($template) {
            case 'questionbanks_bulk_import':
                $file = config('app.upload_templates.questionbanks_bulk_import');
                break;

            default:
                $file = null;
                break;
        }

        if ($file == null) {
            throw new WrongAssetRequestException();
        } elseif (!file_exists($file)) {
            throw new AssetNotFoundException();
        } else {
            return response()->download($file);
        }
    }
}
