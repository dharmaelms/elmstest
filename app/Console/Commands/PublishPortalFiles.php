<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PortalManager\Commands\InstallDependencies;
use Symfony\Component\Console\Input\ArrayInput;

class PublishPortalFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:publish {--p}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish portal packages configuration and assets into core';

    /**
     * Property which holds the portal package info
     *
     * @var stdClass
     */
    protected $portalPackage;

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

        if ($this->isPortalInstalled()) {

            $this->resolveDependencies();

            $this->line('Cleaning....');

            // Clean portal views
            $this->clean(resource_path('views'));

            // Clean portal lang files
            $this->clean(resource_path('lang'));

            $this->line('Copying....');

            // Publish portal assets
            $this->call('vendor:publish', [
                '--provider' => 'Portal\Providers\PortalServiceProvider',
                '--force' => true
            ]);
        }
    }

    /**
     * Remove files and directories
     *
     * @param string $path
     */
    private function clean($path)
    {
        if ($this->isGitRepo($path)) {
            // Following command will remove the files which are
            // skipped by .gitignore file
            exec('git clean -dfX '.$path);
        }

        $this->status($path);
    }

    /**
     * Check Git repo
     *
     * @param string $path
     * @return bool
     */
    private function isGitRepo($path)
    {
        return (bool) exec('cd '.$path.' && git rev-parse --is-inside-work-tree');
    }

    /**
     * Write a status message to the console.
     *
     * @param string $path
     */
    private function status($path)
    {
        $path = str_replace(base_path(), '', realpath($path));

        $this->line('<info>Cleaned Directory</info> <comment>['.$path.']</comment>');
    }

    /**
     * Check if portal is installed
     *
     * @return Boolean
     */
    private function isPortalInstalled()
    {
        $packageList = json_decode(\File::get(base_path('vendor/composer/installed.json')));

        $package = array_first($packageList, function ($key, $package) {

            return preg_match("/^elms\/[a-z0-9]+-fe$/", $package->name);

        });

        $this->portalPackage = $package;

        return !is_null($package);
    }

    /**
     * Method which executes `InstallDependencies` symfony command to resolve and install the dependencies
     */
    private function resolveDependencies()
    {
        $console_app = $this->getApplication();

        $console_app->add(new InstallDependencies());

        $command = $this->getApplication()->find('portal:install-dependencies');

        $arguments = [
            'path' => "vendor/" . $this->portalPackage->name,
            'production' => $this->option('p')
        ];

        $inputArgs = new ArrayInput($arguments);

        $command->run($inputArgs, $this->output);
    }
}
