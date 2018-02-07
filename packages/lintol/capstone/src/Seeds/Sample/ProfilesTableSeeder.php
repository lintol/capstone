<?php

namespace Lintol\Capstone\Seeds\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\Profile;
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
    }
}
