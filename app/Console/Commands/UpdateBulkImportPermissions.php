<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Role;
use Illuminate\Database\Schema\Blueprint;

class UpdateBulkImportPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:bulkImportReports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UpdateBulkImportPermissions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $roles = Role::WhereIn('rid',[1,2,5,6])->get()->toArray();
        if (!empty($roles)) {
            foreach($roles as $role) {
                $permissions = [
                    'module' => 'Bulkimportreports',
                    'slug' => 'bulkimportreports',
                    'action' => [
                        ['name' => 'View Import Reports', 'slug' => 'view-import-reports', 'is_default' => true],
                        ['name' => 'Export Reports', 'slug' => 'export-reports', 'is_default' => true],
                        ['name' => 'Download Templates', 'slug' => 'download-templates', 'is_default' => true]
                    ]
                ];
                Role::where('rid', (int)$role['rid'])->push('admin_capabilities',$permissions, true);
            }
        }
    }
}
