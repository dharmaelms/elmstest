<?php

use App\Model\User;
use App\Services\Role\IRoleService;
use App\Enums\RolesAndPermissions\SystemRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Schema as Schema;
use Illuminate\Support\Facades\App;
use App\Enums\User\NDAStatus as NDA;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Collection name
        $collection = 'users';

        // Creating a collection schema with required index fields
        Schema::create($collection, function ($collection) {
            $collection->unique('uid');
            $collection->index('username');
            $collection->index('email');
            $collection->index('password');
        });

        $roleService = App::make(IRoleService::class);

        // Inserting default super admin
        DB::collection($collection)->insert([
            'uid' => User::getNextSequence(),
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => 'rams@openlink.in',
            'username' => 'openlinkadmin',
            'password' => '$2y$10$lAATSfN4dMxO2HdHqwn3Sei1HTgbqIv0NRAS248r/VoV6ruCbKYly',
            'role' => $roleService->getRoleDetails(SystemRoles::SUPER_ADMIN)["id"],
            'super_admin' => true,
            'timezone' => 'Asia/Kolkata',
            'status' => 'ACTIVE',
            'created_by' => '',
            'created_at' => time(),
            'nda_status' => NDA::NO_RESPONSE,
        ]);

        // Inserting default site admin
        DB::collection($collection)->insert([
            'uid' => User::getNextSequence(),
            'firstname' => 'Site',
            'lastname' => 'Admin',
            'email' => 'admin@openlink.in',
            'username' => 'admin',
            'password' => '$2y$10$lAATSfN4dMxO2HdHqwn3Sei1HTgbqIv0NRAS248r/VoV6ruCbKYly',
            'role' => $roleService->getRoleDetails(SystemRoles::SITE_ADMIN)["id"],
            'super_admin' => true,
            'timezone' => 'Asia/Kolkata',
            'status' => 'ACTIVE',
            'created_by' => '',
            'created_at' => time(),
            'authkey' => 'YWRtaW51bHRyb25kV3gwY205dVlXUnRhVzQ9',
            'nda_status' => NDA::NO_RESPONSE,
        ]);
    }
}
