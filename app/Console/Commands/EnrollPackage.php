<?php

namespace App\Console\Commands;

use App\Services\Package\IPackageService;
use Illuminate\Console\Command;
use App\Events\User\EntityEnrollmentByAdminUser;
use App\Services\Role\IRoleService;
use App\Model\Program;
use App\Model\User;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Enums\User\UserEntity;
use App\Enums\User\UserStatus;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Model\User\Entity\UserEnrollment;

use Timezone;
use Carbon;
use Log;

/**
 * Class EnrollPackage
 * @package App\Console\Commands
 * reminder
 */

class EnrollPackage extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'enroll:package {package_slug?}';

     /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To run enrol users based on specific package slug';
    
    /**
     *  @var IPackageService
     */
    private $package_service;

    /**
     *  @var IRoleService
     */
    private $role_service;

    /**
     * Create a new command instance.
     * @param PackageController
     */
    public function __construct(
        IPackageService $package_service,
        IRoleService $role_service
    ) {
        $this->package_service = $package_service;
        $this->role_service = $role_service;
        parent::__construct();
    }

    public function fire()
    {
        $this->line("Start enroll spcified package related users");
        Log::info('Start enroll spcified package related users');
        $package_slug = $this->argument('package_slug');
        if (!is_null($package_slug)) {
            $package = $this->package_service->getPackageBySlug('package_slug', $package_slug);
            $user_ids = $package->user_ids;
            if (!empty($user_ids)) {
                $this->updateUserRelation($package, $user_ids);
            } else {
                $this->line("Users are not assign to this package ".$package->package_title);
            }
        } else {
            $this->line("Please spcify package slug");
        }
        $this->line("Ended - Successfully enrolled spcified package related users");
    }

    private function updateUserRelation($package, $user_ids)
    {
        $auth = [
            'timezone' => config('app.default_timezone'),
            'username' => $package->created_by,
            'fullname' => isset($package->created_by_name) ? $package->created_by_name : $package->created_by

        ];
        $role_info = $this->role_service->getRoleDetails(SystemRoles::LEARNER, ['context']);
        $context_info = $this->role_service->getContextDetails(Contexts::PROGRAM, false);
        $role_id = array_get($role_info, 'id', '');
        $context_id = array_get($context_info, 'id', '');

        foreach ($user_ids as $value) {
            $is_enrolled = UserEnrollment::where('user_id', (int)$value)
                ->where('entity_type', 'PACKAGE')
                ->where('entity_id', $package->package_id)
                ->where('status', 'ENROLLED')
                ->where('source_type', 'DIRECT_ENROLLMENT')
                ->first();
            if (is_null($is_enrolled)) {
                event(
                    new EntityEnrollmentByAdminUser(
                        $value,
                        UserEntity::PACKAGE,
                        $package->package_id
                    )
                );

                $userdetails = User::getUserDetailsByID($value)->toArray();
                $email = isset($userdetails['email']) ? $userdetails['email'] : '' ;
                $program_ids = $package->program_ids;
                $now = time();
                if (!empty($program_ids)) {
                    foreach ($program_ids as $channel_id) {
                        $this->role_service->mapUserAndRole(
                            $value,
                            $context_id,
                            $role_id,
                            $channel_id
                        );
                        $channel = Program::getProgramDetailsByID($channel_id);
                        $trans_id = Transaction::uniqueTransactionId();

                        $transaction = [
                            'DAYOW' => Timezone::convertToUTC('@' . $now, $auth['timezone'], 'l'),
                            'DOM' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'j'),
                            'DOW' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'w'),
                            'DOY' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'z'),
                            'MOY' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'n'),
                            'WOY' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'W'),
                            'YEAR' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'Y'),
                            'trans_level' => 'user',
                            'id' => $value,
                            'created_date' => time(),
                            'email' => $email,
                            'trans_id' => (int)$trans_id,
                            'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                            'access_mode' => 'assigned_by_admin',
                            'added_by' => $auth['username'],
                            'added_by_name' => $auth['fullname'],
                            'created_at' => time(),
                            'updated_at' => time(),
                            'type' => 'subscription',
                            'status' => 'COMPLETE', // This is transaction status
                        ];

                        $transaction_details = [
                            'trans_level' => 'user',
                            'id' => $value,
                            'trans_id' => (int)$trans_id,
                            'program_id' => $channel['program_id'],
                            'package_id' => $package->package_id,
                            'program_slug' => $channel['program_slug'],
                            'type' => 'content_feed',
                            'program_sub_type' => 'collection',
                            'program_title' => $channel['program_title'],
                            'duration' => [ // Using the same structure from duration master
                                'label' => 'Forever',
                                'days' => 'forever',
                            ],
                            'start_date' => '', // Empty since the duration is forever
                            'end_date' => '', // Empty since the duration is forever
                            'created_at' => time(),
                            'updated_at' => time(),
                            'status' => 'COMPLETE',
                        ];
                        // Add record to user transaction table
                        Transaction::insert($transaction);
                        TransactionDetail::insert($transaction_details);
                    }
                }

                $trans_id = Transaction::uniqueTransactionId();

                $transaction = [
                    'DAYOW' => Timezone::convertToUTC('@' . $now, $auth['timezone'], 'l'),
                    'DOM' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'j'),
                    'DOW' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'w'),
                    'DOY' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'z'),
                    'MOY' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'n'),
                    'WOY' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'W'),
                    'YEAR' => (int)Timezone::convertToUTC('@' . $now, $auth['timezone'], 'Y'),
                    'trans_level' => 'user',
                    'id' => $value,
                    'created_date' => time(),
                    'email' => $email,
                    'trans_id' => (int)$trans_id,
                    'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                    'access_mode' => 'assigned_by_admin',
                    'added_by' => $auth['username'],
                    'added_by_name' => $auth['fullname'],
                    'created_at' => time(),
                    'updated_at' => time(),
                    'type' => 'subscription',
                    'status' => 'COMPLETE', // This is transaction status
                ];

                $transaction_details = [
                    'trans_level' => 'user',
                    'id' => $value,
                    'trans_id' => (int)$trans_id,
                    'program_id' => (int)$package->package_id,
                    'package_id' => (int)$package->package_id,
                    'program_slug' => $package->package_slug,
                    'type' => 'content_feed',
                    'program_sub_type' => 'collection',
                    'program_title' => $package->package_title,
                    'duration' => [ // Using the same structure from duration master
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                    'start_date' => '', // Empty since the duration is forever
                    'end_date' => '', // Empty since the duration is forever
                    'created_at' => time(),
                    'updated_at' => time(),
                    'status' => 'COMPLETE',
                ];
                // Add record to user transaction table
                Transaction::insert($transaction);
                TransactionDetail::insert($transaction_details);
            }
        }
    }
}
