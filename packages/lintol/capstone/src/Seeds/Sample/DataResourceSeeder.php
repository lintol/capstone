<?php

namespace Lintol\Capstone\Seeds\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\DataResource;
use App\User;

class DataResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $dataOwner = User::whereEmail('do@example.com')->first();

        $dataResource = DataResource::firstOrNew([
            'filename' => 'noids-2017-18-wk-52.csv',
            'url' => 'www.opendatani.gov.uk/dataset/6bf61328-a250-44fd-a787-481503f02865/resource/b213ec96-85cb-489c-abaa-fd4b0eb69fb1/download/noids-2017-18-wk-52.csv',
            'filetype' => 'csv',
            'status' => 'profile run',
            'stored' => 'External Link',
            'reportid' => ''
        ]);
        // $dataResource->creator()->associate($dataOwner);
        $dataResource->save();
    }
}
