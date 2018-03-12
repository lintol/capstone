<?php

namespace Lintol\Capstone\Seeds\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\ProcessorConfiguration;
use App\User;

class ProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataOwner = User::whereEmail('do@example.com')->first();

        $profile = Profile::firstOrNew([
            'name' => 'csv profile [test]',
        ]);
        $profile->fill([
            'description' => 'csv description',
            'version' => 'version 7',
            'unique_tag' => 'uniq-44',
        ]);
        $profile->creator()->associate($dataOwner);
        $profile->save();

        $processor = Processor::whereUniqueTag('theodi/csvlint.rb:1')->firstOrFail();

        $configuration = $profile->configurations()->whereProcessorId($processor->id)->first();
        if (!$configuration) {
            $configuration = new ProcessorConfiguration;
            $configuration->profile()->associate($profile);
            $configuration->processor()->associate($processor);
        }
        $configuration->updateDefinition();

        $configuration->save();

        $profile = Profile::firstOrNew([
            'name' => 'json profile [test]',
        ]);
        $profile->fill([
            'description' => 'json description',
            'version' => 'version 8',
            'unique_tag' => 'uniq-43',
        ]);
        $profile->creator()->associate($dataOwner);
        $profile->save();

        $dataOwner = User::whereEmail('do@example.com')->first();

        $profile = Profile::firstOrNew([
            'name' => 'PII Checker [test]',
        ]);
        $profile->fill([
            'description' => 'PII description',
            'version' => 'version 1',
            'unique_tag' => 'uniq-48',
        ]);
        $profile->creator()->associate($dataOwner);
        $profile->save();

        $processor = Processor::whereUniqueTag('lintol/ds-pii-legacy:1')->firstOrFail();

        $configuration = $profile->configurations()->whereProcessorId($processor->id)->first();
        if (!$configuration) {
            $configuration = new ProcessorConfiguration;
            $configuration->profile()->associate($profile);
            $configuration->processor()->associate($processor);
        }
        $configuration->updateDefinition();

        $configuration->save();

        $dataOwner = User::whereEmail('do@example.com')->first();

        $profile = Profile::firstOrNew([
            'name' => 'Boundary Checker [test]',
        ]);
        $profile->fill([
            'description' => 'Boundary description',
            'version' => 'version 1',
            'unique_tag' => 'uniq-48',
        ]);
        $profile->creator()->associate($dataOwner);
        $profile->save();

        $processor = Processor::whereUniqueTag('lintol/ds-boundary-checker-py:1')->firstOrFail();

        $configuration = $profile->configurations()->whereProcessorId($processor->id)->first();
        if (!$configuration) {
            $configuration = new ProcessorConfiguration;
            $configuration->profile()->associate($profile);
            $configuration->processor()->associate($processor);
        }
        $configuration->updateDefinition();

        $configuration->save();

    }
}
