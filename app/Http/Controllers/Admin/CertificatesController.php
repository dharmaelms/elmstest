<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\OverAllChannelAnalytic;
use App\Services\UserCertificate\IUserCertificateService;
use Log;

class CertificatesController extends AdminBaseController
{
    public $certificate_service;

    public function __construct(IUserCertificateService $certificate_service)
    {
        $this->certificate_service = $certificate_service;
    }

    public function getGenerateCertificate()
    {
        try {
            Log::info('certificate cron called');
            $analytics = OverAllChannelAnalytic::where('completion', ">=", (int)100)
                ->where('is_certificate_generated', 'exists', false)
                ->get();
            if (!$analytics->isEmpty()) {
                foreach ($analytics as $analytic) {
                    $this->certificate_service->process($analytic->channel_id, $analytic->user_id);
                }
            }
            echo $analytics->count() . ' certificates generated';
        } catch (\Exception $e) {
            Log::info('Error in line'.$e->getLine(). 'Message '.$e->getMessage());
        }
    }
}
