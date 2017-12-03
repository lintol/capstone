<?php

use Illuminate\Database\Seeder;

class ProfilesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('profiles')->insert([
            'name' => 'csv profile',
            'description' => 'csv description',
            'creator' => 'martin',
            'version' => 'version 7',
            'uniqueTag' => 'uniq-44',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('profiles')->insert([
            'name' => 'json profile',
            'description' => 'json description',
            'creator' => 'dan',
            'version' => 'version 8',
            'uniqueTag' => 'uniq-43',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
