<?php

namespace Lintol\Capstone\Seeds\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\DataPackage;
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
        $this->call(DataPackageSeeder::class);

        $user = User::firstOrCreate([
            'name' => 'seeder'
        ]);

        // $dataOwner = User::whereEmail('do@example.com')->first();
        $dataResource = DataResource::firstOrNew([
            'filename' => 'waste-sites.geojson',
        ]);
        $dataResource->fill([
            'url' => 'https://cc-p-ni.ckan.io/dataset/4c9ae0a2-0238-459e-8b4d-1172bec9dc3c/resource/58cb3ac3-3263-40b7-bf0c-e4c686432708/download/wastesitesdec18.geojson',
            'filetype' => 'geojson',
            'status' => 'report run',
            'source' => 'External Link',
            'reportid' => '',
            'user_id' => $user->id,
            'package_id' => DataPackage::whereName('NIEA - Authorised waste sites (treatment & storage) [seeder]')->first()->id
        ]);
        $dataResource->save();

        $dataResource = DataResource::firstOrNew([
            'filename' => 'file_with_long_file_name_to_test_text_wrapping_and_spacing.csv',
        ]);
        $dataResource->fill([
            'url' => 'https://www.opendatani.gov.uk/dataset/4c9ae0a2-0238-459e-8b4d-1172bec9dc3c/resource/51556b4d-85bd-4625-936d-840724f3a877/download/file_with_long_file_name_to_test_text_wrapping_and_spacing.csv',
            'filetype' => 'csv',
            'status' => 'report run',
            'source' => 'External Link',
            'reportid' => '',
            'user_id' => $user->id
        ]);
        $dataResource->save();

        $dataResource = DataResource::firstOrNew([
            'filename' => 'really-long-file-name-that-is-100-characters-long-for-odd-open-data.csv',
        ]);
        $dataResource->fill([
            'url' => 'https://www.opendatani.gov.uk/dataset/4c9ae0a2-0238-459e-8b4d-1172bec9dc3c/resource/51556b4d-85bd-4625-936d-840724f3a877/download/really-long-file-name-that-is-100-characters-long-for-odd-open-data.csv',
            'filetype' => 'csv',
            'status' => 'report run',
            'source' => 'External Link',
            'reportid' => '',
            'user_id' => $user->id
        ]);
        $dataResource->save();

        $package = DataPackage::whereName('Notifiable Infectious Diseases [seeder]')->first();
        for ($i = 0 ; $i < 50 ; $i++) {
            $dataResource = DataResource::firstOrNew([
                'filename' => 'noids-2017-18-wk-' . $i . '.csv',
            ]);
            $dataResource->fill([
                'package_id' => $package->id,
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
