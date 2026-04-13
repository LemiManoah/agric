<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UgandaLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $regions = [
            [
                'name' => 'Central',
                'code' => 'UG-C',
                'districts' => [
                    [
                        'name' => 'Kampala',
                        'code' => 'UG-C-KLA',
                        'subcounties' => [
                            [
                                'name' => 'Nakawa',
                                'code' => 'UG-C-KLA-NAK',
                                'parishes' => [
                                    'Naguru',
                                    'Kiwatule',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Wakiso',
                        'code' => 'UG-C-WAK',
                        'subcounties' => [
                            [
                                'name' => 'Nangabo',
                                'code' => 'UG-C-WAK-NAN',
                                'parishes' => [
                                    'Kira',
                                    'Bweyogerere',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Eastern',
                'code' => 'UG-E',
                'districts' => [
                    [
                        'name' => 'Jinja',
                        'code' => 'UG-E-JIN',
                        'subcounties' => [
                            [
                                'name' => 'Budondo',
                                'code' => 'UG-E-JIN-BUD',
                                'parishes' => [
                                    'Wairaka',
                                    'Buwenge',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Northern',
                'code' => 'UG-N',
                'districts' => [
                    [
                        'name' => 'Gulu',
                        'code' => 'UG-N-GUL',
                        'subcounties' => [
                            [
                                'name' => 'Pece-Laroo',
                                'code' => 'UG-N-GUL-PEC',
                                'parishes' => [
                                    'Pece',
                                    'Layibi',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Western',
                'code' => 'UG-W',
                'districts' => [
                    [
                        'name' => 'Mbarara',
                        'code' => 'UG-W-MBA',
                        'subcounties' => [
                            [
                                'name' => 'Biharwe',
                                'code' => 'UG-W-MBA-BIH',
                                'parishes' => [
                                    'Biharwe',
                                    'Ruti',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($regions as $regionData) {
            DB::table('regions')->updateOrInsert(
                ['code' => $regionData['code']],
                ['name' => $regionData['name'], 'updated_at' => $now, 'created_at' => $now]
            );

            $regionId = DB::table('regions')->where('code', $regionData['code'])->value('id');

            foreach ($regionData['districts'] as $districtData) {
                DB::table('districts')->updateOrInsert(
                    ['code' => $districtData['code']],
                    [
                        'region_id' => $regionId,
                        'name' => $districtData['name'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $districtId = DB::table('districts')->where('code', $districtData['code'])->value('id');

                foreach ($districtData['subcounties'] as $subcountyData) {
                    DB::table('subcounties')->updateOrInsert(
                        ['code' => $subcountyData['code']],
                        [
                            'district_id' => $districtId,
                            'name' => $subcountyData['name'],
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );

                    $subcountyId = DB::table('subcounties')->where('code', $subcountyData['code'])->value('id');

                    foreach ($subcountyData['parishes'] as $parishName) {
                        DB::table('parishes')->updateOrInsert(
                            ['subcounty_id' => $subcountyId, 'name' => $parishName],
                            ['updated_at' => $now, 'created_at' => $now]
                        );

                        $parishId = DB::table('parishes')
                            ->where('subcounty_id', $subcountyId)
                            ->where('name', $parishName)
                            ->value('id');

                        DB::table('villages')->updateOrInsert(
                            ['parish_id' => $parishId, 'name' => $parishName.' Central'],
                            ['updated_at' => $now, 'created_at' => $now]
                        );

                        DB::table('villages')->updateOrInsert(
                            ['parish_id' => $parishId, 'name' => $parishName.' West'],
                            ['updated_at' => $now, 'created_at' => $now]
                        );
                    }
                }
            }
        }
    }
}
