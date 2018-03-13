<?php

namespace Lintol\Capstone\Seeds\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\Profile;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\ProcessorConfiguration;
use App\User;

class ProfilesTableSeeder extends Seeder
{
    public function createProfile($dataOwner, $props, $procTag)
    {
        $profile = Profile::firstOrNew([
            'name' => $props['name'],
        ]);

        $profile->fill($props);
        $profile->creator()->associate($dataOwner);
        $profile->save();

        $processor = Processor::whereUniqueTag($procTag)->firstOrFail();

        $configuration = $profile->configurations()->whereProcessorId($processor->id)->first();
        if (!$configuration) {
            $configuration = new ProcessorConfiguration;
            $configuration->profile()->associate($profile);
            $configuration->processor()->associate($processor);
        }
        $configuration->updateDefinition();

        $configuration->save();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataOwner = User::whereEmail('do@example.com')->first();

        $this->createProfile(
            $dataOwner,
            [
                'name' => 'CSV profile [test]',
                'description' => 'csv description',
                'version' => 'version 7',
                'unique_tag' => 'uniq-44'
            ],
            'theodi/csvlint.rb:1'
        );

        $this->createProfile(
            $dataOwner,
            [
                'name' => 'PII Checker [test]',
                'description' => 'PII description',
                'version' => 'version 1',
                'unique_tag' => 'uniq-48',
            ],
            'lintol/ds-pii-legacy:1'
        );

        $this->createProfile(
            $dataOwner,
            [
                'name' => 'Boundary Checker [test]',
                'description' => 'Boundary description',
                'version' => 'version 1',
                'unique_tag' => 'uniq-50',
            ],
            'lintol/ds-boundary-checker-py:1'
        );

    }
}
