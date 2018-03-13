<?php

namespace App\Console\Commands;

use Lintol\Capstone\Models\CkanInstance;
use Illuminate\Console\Command;

class CreateCkanInstanceToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltl:ckan-token {ckan-instance-id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $ckanInstanceId = $this->argument('ckan-instance-id');

        $instance = CkanInstance::findOrFail($ckanInstanceId);

        $accessToken = $instance->createToken('lintol-capstone-api-token')->accessToken;

        $this->info($accessToken);
    }
}
