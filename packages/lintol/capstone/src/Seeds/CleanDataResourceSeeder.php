<?php

namespace Lintol\Capstone\Seeds;

use File;
use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\DataResource;
use Lintol\Capstone\Models\DataPackage;
use App\User;

class CleanDataResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DataResource::where('name', '!=', '[DNE]')->delete();
        DataPackage::where('name', '!=', '[DNE]')->delete();
    }
}
