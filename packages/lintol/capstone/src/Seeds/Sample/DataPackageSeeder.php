<?php

namespace Lintol\Capstone\Seeds\Sample;

use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\DataPackage;
use Lintol\Capstone\Models\DataResource;
use App\User;
use File;

class DataPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataPath = __DIR__ . '/../../../examples/data/';

        $user = User::firstOrCreate([
            'name' => 'seeder'
        ]);
        DataPackage::where('name', 'LIKE', '%[seeder]')->delete();

        // $dataOwner = User::whereEmail('do@example.com')->first();
        $dataPackage = DataPackage::firstOrNew([
            'name' => 'NIEA - Authorised waste sites (treatment & storage) [seeder]',
        ]);
        $dataPackage->fill([
            'metadata' => File::get($dataPath . 'authorised-waste-sites-treatment-storage.json'),
            'remote_id' => 'authorised-waste-sites-treatment-storage',
            'url' => 'https://www.opendatani.gov.uk/dataset/authorised-waste-sites-treatment-storage',
            'source' => 'OpenDataNI',
            'user_id' => $user->id
        ]);
        $dataPackage->save();
        $dataPackage = DataPackage::firstOrNew([
            'name' => 'Notifiable Infectious Diseases [seeder]',
        ]);
        $dataPackage->fill([
            'metadata' => File::get($dataPath . 'notifiable-infectious-diseases-report-2016-week-17.json'),
            'remote_id' => '6bf61328-a250-44fd-a787-481503f02865',
            'url' => 'https://www.opendatani.gov.uk/dataset/notifiable-infectious-diseases-report-2016-week-17',
            'source' => 'OpenDataNI',
            'user_id' => $user->id
        ]);
        $dataPackage->save();
    }
}
