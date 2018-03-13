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
        $user = User::firstOrCreate([
            'name' => 'martin'
        ]);

        // $dataOwner = User::whereEmail('do@example.com')->first();
        $dataResource = DataResource::firstOrNew([
            'filename' => 'waste-sites.geojson',
            'url' => 'https://www.opendatani.gov.uk/dataset/4c9ae0a2-0238-459e-8b4d-1172bec9dc3c/resource/51556b4d-85bd-4625-936d-840724f3a877/download/waste-sites.geojson',
            'filetype' => 'geojson',
            'status' => 'report run',
            'source' => 'CKAN',
            'reportid' => '',
            'user_id' => $user->id
        ]);
        $dataResource->save();

        for ($i = 0 ; $i < 50 ; $i++) {
            $dataResource = DataResource::firstOrNew([
                'filename' => 'noids-2017-18-wk-' . $i . '.csv',
                'url' => 'https://www.opendatani.gov.uk/dataset/6bf61328-a250-44fd-a787-481503f02865/resource/b213ec96-85cb-489c-abaa-fd4b0eb69fb1/download/noids-2017-18-wk-' . $i . '.csv',
                'filetype' => 'csv',
                'status' => 'new resource',
                'source' => 'External Link',
                'reportid' => '',
                'user_id' => $user->id
            ]);
            $dataResource->save();
        }
    }
}
