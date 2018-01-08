<?php

namespace Lintol\Capstone\Seeds;

use File;
use Illuminate\Database\Seeder;
use Lintol\Capstone\Models\Processor;
use Lintol\Capstone\Models\Rule;
use App\User;

class RulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rule = Rule::firstOrNew([
            'name' => '/[Ll]int/'
        ]);
        $rule['definition'] = [
            'name' => '/[Ll]int/'
        ];
        $rule->save();
        $rule = Rule::firstOrNew([
            'name' => '/[Gg]ood[Tt]ables/'
        ]);
        $rule['definition'] = [
            'name' => '/[Gg]ood[Tt]ables/'
        ];
        $rule->save();
        $rule = Rule::firstOrNew([
            'name' => '/PII/'
        ]);
        $rule['definition'] = [
            'name' => '/PII/'
        ];
        $rule->save();
        $rule = Rule::firstOrNew([
            'name' => '/[Bb]oundar/'
        ]);
        $rule['definition'] = [
            'name' => '/[Bb]oundar/'
        ];
        $rule->save();
        $rule = Rule::firstOrNew([
            'name' => '/[Cc]ountr/'
        ]);
        $rule['definition'] = [
            'name' => '/[Cc]ountr/'
        ];
        $rule->save();
    }
}
