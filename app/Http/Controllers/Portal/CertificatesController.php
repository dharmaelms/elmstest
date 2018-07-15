<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\SiteSetting;
use App\Model\UserCertificates\UserCertificates;
use App\Services\UserCertificate\IUserCertificateService;
use Auth;
use Dompdf\Dompdf;
use Exception;
use Input;
use Log;


/**
 * Class CertificatesController
 *
 * @package App\Http\Controllers\Portal
 */
class CertificatesController extends PortalBaseController
{
    /**
     * @var $user_certificate_service
     */
    public $user_certificate_service;

    public function __construct(IUserCertificateService $user_certificate_service)
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->user_certificate_service = $user_certificate_service;
    }

    /**
     * Default action for this controller
     * @return [type] [description]
     */
    public function getIndex()
    {
        $my_certificate = SiteSetting::where('module', 'certificates')->first(['setting']);
        if (isset($my_certificate->setting['visibility']) && $my_certificate->setting['visibility'] == 'true') {
            $this->layout->pagetitle = 'Certificates list';
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.certificates.list');
        } else {
            abort(404);
        }
    }

    /**
     * Method to get certificates data
     * @param int $page
     * @param int $limit
     * @return
     */
    public function getDetails($page = 1, $limit = 10)
    {

        try {
            $condition = $search = '';
            if (Input::get('condition')) {
                $condition = Input::get('condition');
            }
            if (Input::get('search')) {
                $search = Input::get('search');
            }
            $data = $this->user_certificate_service->listUserCertificates($page, $limit, $condition, $search);
            if (empty($data)) {
                return response()->json(['status' => false, 'data' => []]);
            }
            return response()->json(['status' => true, 'data' => $data]);
        } catch (Exception $e) { //TODO custom exception implementation
            Log::info('Error in retrieving certificates ' . $e->getMessage());
            response()->json(['status' => false, 'message' => 'error']);
        }
    }

    /**
     * Method to view/download pdf
     * @param  int $attachment 0/1
     * @param  string $id
     */
    public function getPdf($attachment, $id)
    {
        $certificate = UserCertificates::where('_id', '=', $id)->where('user_id', Auth::user()->uid)->first();
        if (empty($certificate)) {
            abort(404);
        }

        // instantiate and use the dompdf class
        $dompdf = new Dompdf([
            'enable_remote' => true,
            'enable_html5parser' => true,
            'enable_fontsubsetting' => true,
            'unicode_enabled' => true,
        ]);

        $dompdf->loadHtml($certificate->content);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A3', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream($certificate->program_title . '_certificate', ['Attachment' => $attachment]);
        exit(); //fix for preview
    }
}
