<?php

namespace Database\Seeders;

use App\Models\BetUser;
use App\Models\BettingHouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BetSeeder extends Seeder
{
    public function run(): void
    {
        $houses = [
            ['name' => 'Betano', 'website' => 'https://www.betano.bet.br', 'color' => '#ff6f00'],
            ['name' => 'Bet365', 'website' => 'https://www.bet365.com', 'color' => '#15a96f'],
            ['name' => 'Sportingbet', 'website' => 'https://sports.sportingbet.bet.br', 'color' => '#1a73e8'],
            ['name' => 'Superbet', 'website' => 'https://www.superbet.bet.br', 'color' => '#EF5350'],
        ];

        foreach ($houses as $house) {
            BettingHouse::firstOrCreate(
                ['slug' => Str::slug($house['name'])],
                [
                    'name' => $house['name'],
                    'website' => $house['website'],
                    'country' => 'Brasil',
                    'color' => $house['color'],
                    'is_active' => true,
                ],
            );
        }

        BetUser::firstOrCreate(
            ['name' => 'Jairo Rodrigues'],
            [
                'nickname' => 'Jairo',
                'color' => '#ff6f00',
                'is_active' => true,
            ],
        );
    }
}
