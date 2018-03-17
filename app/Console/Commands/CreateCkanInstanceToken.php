<?php

namespace App\Console\Commands;

use Crypt;
use Lintol\Capstone\Models\CkanInstance;
use Illuminate\Console\Command;

class CreateCkanInstanceToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltl:ckan-token {name} {uri} {client-id} {client-secret}';

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
        $name = $this->argument('name');
        $uri = $this->argument('uri');
        $clientId = $this->argument('client-id');
        $clientSecret = $this->argument('client-secret');

        $instance = CkanInstance::firstOrNew([
            'uri' => $uri
        ]);
        $instance->name = $name;
        $instance->client_id = Crypt::encrypt($clientId);
        $instance->client_secret = Crypt::encrypt($clientSecret);
        $instance->save();

        $accessToken = $instance->createToken('lintol-capstone-api-token')->accessToken;

        $this->info($accessToken);
    }
}
