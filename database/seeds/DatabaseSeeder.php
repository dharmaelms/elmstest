<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call('CountryTableSeeder');
        $this->call('ContextTypeSeeder');
        $this->call('ModulesSeeder');
        $this->call('PermissionSeeder');
        $this->call('RolesTableSeeder');
        $this->call('UsersTableSeeder');
        $this->call('UserRoleAssignmentsSeeder');
        $this->call('EmailsTableSeeder');
        $this->call('SiteSettingsTableSeeder');
        $this->call('QuestionBankSeeder');
        $this->call('AttributesTableSeeder');
        $this->call('ProductTypeTableSeeder');
        $this->call('StaticPageTableSeeder');
        $this->call('StatesSeeder');
        $this->call('ProgramCertificatesTableSeeder');
    }
}
