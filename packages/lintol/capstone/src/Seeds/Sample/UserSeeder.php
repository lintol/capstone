<?php

namespace Seeders\Sample;

use Illuminate\Database\Seeder;
use App\User;
use Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sampleAdmin = User::firstOrNew([
            'email' => 'admin@example.com',
        ]);
        $sampleAdmin->fill([
            'name' => 'Ad Min',
            'password' => Hash::make('password')
        ]);
        $sampleAdmin->save();
        if (!$sampleAdmin->hasRole('administrator')) {
            $sampleAdmin->assignRole('administrator');
        }

        $sampleOwner = User::firstOrNew([
            'email' => 'do@example.com',
        ]);
        $sampleOwner->fill([
            'name' => 'Dat Aowner',
            'password' => Hash::make('password')
        ]);
        $sampleOwner->save();
        if (!$sampleAdmin->hasRole('administrator')) {
            $sampleOwner->assignRole('data-owner');
        }
    }
}
