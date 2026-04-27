<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'PUSPANITA Kebangsaan', 'code' => 'PKB'],
            ['name' => 'Johor', 'code' => 'JHR'],
            ['name' => 'Kedah', 'code' => 'KDH'],
            ['name' => 'Kelantan', 'code' => 'KTN'],
            ['name' => 'Kuala Lumpur', 'code' => 'KUL'],
            ['name' => 'Melaka', 'code' => 'MLK'],
            ['name' => 'Negeri Sembilan', 'code' => 'NSN'],
            ['name' => 'Pahang', 'code' => 'PHG'],
            ['name' => 'Perak', 'code' => 'PRK'],
            ['name' => 'Perlis', 'code' => 'PLS'],
            ['name' => 'Pulau Pinang', 'code' => 'PNG'],
            ['name' => 'Putrajaya', 'code' => 'PTJ'],
            ['name' => 'Sabah', 'code' => 'SBH'],
            ['name' => 'Sarawak', 'code' => 'SWK'],
            ['name' => 'Selangor', 'code' => 'SGR'],
            ['name' => 'Terengganu', 'code' => 'TRG'],
        ];

        foreach ($branches as $branch) {
            Branch::query()->updateOrCreate(
                ['code' => $branch['code']],
                ['name' => $branch['name'], 'is_active' => true],
            );
        }
    }
}
