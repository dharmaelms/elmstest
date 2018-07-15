<?php

namespace App\Services\UserCertificate;

use App\Model\Notification;
use App\Model\OverAllChannelAnalytic;
use App\Model\Program\IProgramRepository;
use App\Model\Quiz\IQuizRepository;
use App\Model\User;
use App\Model\UserCertificates\Repository\IUserCertificatesRepository;
use App\Model\UserCertificates\UserCertificates;
use App\Model\User\Repository\IUserRepository;
use Auth;
use Carbon\Carbon;
use DB;
use Log;

/**
 * Class CertificateService
 *
 * @package App\Services\Certificate
 */
class UserCertificateService implements IUserCertificateService
{
    /**
     * @var \App\Model\Program\IProgramRepository
     */
    private $program_repository;

    /**
     * @var $user_certificate_repository
     */
    private $user_certificate_repository;

    /**
    * @var $quiz_repository
    */
    private $quiz_repository;

    /**
     * @var $user_certificate_repository
     */
    private $user_repository;

    /**
     * UserCertificateService constructor.
     * @param IProgramRepository $program_repository
     * @param IUserCertificatesRepository $user_certificate_repository
     * @param IQuizRepository $quiz_repository
     */
    public function __construct(
        IProgramRepository $program_repository,
        IUserCertificatesRepository $user_certificate_repository,
        IQuizRepository $quiz_repository,
        IUserRepository $user_repository
    ) {
        $this->program_repository = $program_repository;
        $this->user_certificate_repository = $user_certificate_repository;
        $this->quiz_repository = $quiz_repository;
        $this->user_repository = $user_repository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($channel_id, $user_id)
    {
        $benchmark = true;
        $channel_data = $this->getChannelBenchmark($channel_id)->first(); //getting channel benchmark
        $channel_quiz = $this->quiz_repository->getQuizChannel($channel_id);
        $analytic_data = $this->getChannelAnalyticsData($channel_id, $user_id); //getting channel analytics data

        if (!$channel_quiz->isEmpty()) {
            $benchmark = $this->validateBenchmark($channel_data, $analytic_data);
        }
        if ($benchmark) {
            if ($this->generateCertificate($analytic_data, $channel_data)) {
                $this->updateChannelAnalytics($channel_id, $user_id); //put entry as certificate generated
                // TODO notification
                $data = [
                'notification_id' => Notification::getMaxID(),
                'message' => 'Certificate has been generated for <strong>' . $channel_data->program_title . '</strong>',
                'from_module' => $channel_data->program_type,
                'user_id' => $analytic_data->user_id,
                'is_read' => false,
                'created_at' => time(),
                ];
                Notification::insert($data);
            } //inserting certificate
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelBenchmark($channel_id)
    {
        return $this->program_repository->getProgramDataByAttribute('program_id', $channel_id, ['benchmarks', 'program_title', 'program_type', 'program_display_enddate']);
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelAnalyticsData($channel_id, $user_id)
    {
        return OverAllChannelAnalytic::where('channel_id', (int)$channel_id)
            ->where('user_id', (int)$user_id)
            ->where('is_certificate_generated', 'exists', false)
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function validateBenchmark($channel_data, $analytic_data)
    {
        $benchmark = false;
        if (!is_null($channel_data)) {
            if ($channel_data->program_display_enddate->timestamp >= time()) {
                if ($channel_data->benchmarks['score'] >= 0) {
                    if (isset($analytic_data->score)) {
                        $user = User::where('uid', $analytic_data->user_id)->where('status', 'ACTIVE')->first();
                        if (!empty($user)) {
                            if ($channel_data->benchmarks['score'] > $analytic_data->score) {
                                Log::info("User $user->username score($analytic_data->score) is less than channel($channel_data->program_title) benchmark " . $channel_data->benchmarks['score']);
                            } else {
                                $benchmark = true;
                                Log::info("User $analytic_data->username score($analytic_data->score) matches the channel($channel_data->program_title) benchmark " . $channel_data->benchmarks['score']);
                            }
                        } else {
                            Log::info("User id $analytic_data->user_id not exist is not active");
                        }
                    } else {
                        Log::info("Analytic score not available for $channel_data->program_title");
                    }
                } else {
                    Log::info("Channel score not available");
                }
            } else {
                Log::info("Channel $channel_data->program_title expired");
            }
        }
        return $benchmark;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCertificate($analytic_data, $channel_data)
    {
        $certificate_id = UserCertificates::getMaxId();
        $html = $this->getCertificateContent($analytic_data, $channel_data);
        if (!empty($html)) {
            $data = [
                'certificate_id' => $certificate_id,
                'user_id' => (int)$analytic_data->user_id,
                'program_id' => $analytic_data->channel_id,
                'program_title' => $channel_data->program_title,
                'program_type' => $channel_data->program_type,
                'completion' => $analytic_data->completion,
                'content' => $html,
                'post_completion' => $analytic_data->post_completion,
                'item_details' => $analytic_data->item_details,
                'score' => isset($analytic_data->score) ? $analytic_data->score : 0,
                'is_admin_generated' => 0,
                'created_at' => time(),
                'updated_at' => time(),
                'status' => 'ACTIVE',
            ];
            return UserCertificates::insert($data);
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function listUserCertificates($page = 1, $limit = 10, $condition = '', $title = '')
    {
        $data = [];
        $start = ($page * $limit) - $limit;
        //TODO throw custom exception if result is empty
        $certificate_lists = $this->user_certificate_repository->getUserCertificates($start, $limit, $condition, $title);
        $timezone = Auth::user()->timezone;
        foreach ($certificate_lists as $key => $certificate) {
            $data[$key]['id'] = $certificate->_id;
            $data[$key]['name'] = $certificate->program_title;
            $data[$key]['date'] = Carbon::createFromTimestamp($certificate->updated_at)->timezone($timezone)->format('d M Y');
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificateContent($analytic_data, $channel_data)
    {
        try {
            $content = '';
            $certificate = DB::collection('program_certificates')
                ->where('id', (int)config('app.certificate_template'))
                ->first();
            if (!is_null($certificate)) {
                $data['host'] = config('app.url');
                $data['username'] = $this->user_repository->find($analytic_data->user_id)->fullname;
                $data['program_title'] = $channel_data->program_title;
                $data['generated_at'] = Carbon::now()->format('d M, Y');
                $data['site_logo'] = config('app.default_logo_path');

                if (config('app.certificate_template') == 2) {
                    $data['second_logo'] = config('app.second_logo');
                    $data['signature_image'] = config('app.signature_image');
                    $data['signature_name'] = config('app.signature_name');
                }
                $content = $certificate['content'];
                foreach ($data as $pattern => $value) {
                    $content = preg_replace("/\{$pattern\}/", $value, $content);
                }
            } else {
                echo 'Run program certificates table seeder';
                Log::warning('run program_certificates table seeder');
            }
            return $content;
        } catch (\Exception $e) {
            Log::info('Error in line '.$e->getLine(). ', Message -> '.$e->getMessage(). 'user id = '. $analytic_data->user_id);
            return $content;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateChannelAnalytics($channel_id, $user_id)
    {
        return OverAllChannelAnalytic::where('channel_id', $channel_id)->where('user_id', $user_id)->update(['is_certificate_generated' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserCertificateById($id)
    {
        $this->user_certificate_repository->getDataByColumn($id, '_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecifiedChannelCertifiedUsers($channel_id, $user_ids)
    {
        return $this->user_certificate_repository->getSpecifiedChannelCertifiedUsers($channel_id, $user_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountByProgramId($program_id)
    {
        return $this->user_certificate_repository->getCountByProgramId($program_id);   
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificatesByProgramId($program_id, $column, $orderby = ['created_at' => 'desc'], $start = 0, $limit = 0)
    {
        return $this->user_certificate_repository->getCertificatesByProgramId($program_id, $column, $orderby, $start, $limit);   
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificatesByProgramAndUsers($program_id, $user_ids, $column)
    {
        return $this->user_certificate_repository->getCertificatesByProgramAndUsers($program_id, $user_ids, $column);   
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificateByUserAndProgramId($user_id, $progarm_id)
    {
        return $this->user_certificate_repository->getCertificateByUserAndProgramId($user_id, $progarm_id);  
    }

    /**
     * {@inheritdoc}
     */
    public function getCertifiedUsersLists($program_id, $user_ids, $column, $orderby, $start, $limit)
    {
        return $this->user_certificate_repository->getCertifiedUsersLists($program_id, $user_ids, $column, $orderby, $start, $limit); 
    }
}
