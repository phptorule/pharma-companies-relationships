<?php

namespace App\Console\Commands;

use App\Models\AppVersion;
use Illuminate\Console\Command;

class SetAppMinorVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:set-app-minor-version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the minor version of the app in DB to equal to commits number in master branch';

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
        $currentVersion = AppVersion::last();

        $number = exec('git rev-list --count master');

        if(!$currentVersion || $currentVersion->minor != $number) {
            AppVersion::create([
                'main' => !$currentVersion ? 1 : $currentVersion->main,
                'major' => !$currentVersion ? 0 : $currentVersion->major,
                'minor' => $number
            ]);
        }
    }
}