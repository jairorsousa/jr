<?php

namespace Database\Seeders;

use App\Models\CryptoAsset;
use App\Models\CryptoInstitution;
use App\Models\CryptoNetwork;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CryptoSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['symbol' => 'USDT', 'name' => 'Tether USD', 'decimals' => 6, 'is_stablecoin' => true],
            ['symbol' => 'USDC', 'name' => 'USD Coin', 'decimals' => 6, 'is_stablecoin' => true],
            ['symbol' => 'BTC', 'name' => 'Bitcoin', 'decimals' => 8, 'is_stablecoin' => false],
            ['symbol' => 'LTC', 'name' => 'Litecoin', 'decimals' => 8, 'is_stablecoin' => false],
            ['symbol' => 'ETH', 'name' => 'Ethereum', 'decimals' => 8, 'is_stablecoin' => false],
            ['symbol' => 'BNB', 'name' => 'BNB', 'decimals' => 8, 'is_stablecoin' => false],
            ['symbol' => 'TRX', 'name' => 'TRON', 'decimals' => 6, 'is_stablecoin' => false],
            ['symbol' => 'SOL', 'name' => 'Solana', 'decimals' => 8, 'is_stablecoin' => false],
        ];

        foreach ($assets as $asset) {
            CryptoAsset::firstOrCreate(
                ['symbol' => $asset['symbol']],
                [
                    'name' => $asset['name'],
                    'decimals' => $asset['decimals'],
                    'is_stablecoin' => $asset['is_stablecoin'],
                    'is_active' => true,
                ],
            );
        }

        $assetBySymbol = CryptoAsset::all()->keyBy('symbol');
        $networks = [
            ['code' => 'TRC20', 'name' => 'TRON TRC20', 'native' => 'TRX'],
            ['code' => 'ERC20', 'name' => 'Ethereum ERC20', 'native' => 'ETH'],
            ['code' => 'BEP20', 'name' => 'BNB Smart Chain BEP20', 'native' => 'BNB'],
            ['code' => 'BTC', 'name' => 'Bitcoin', 'native' => 'BTC'],
            ['code' => 'LTC', 'name' => 'Litecoin', 'native' => 'LTC'],
            ['code' => 'SOL', 'name' => 'Solana', 'native' => 'SOL'],
        ];

        foreach ($networks as $network) {
            CryptoNetwork::firstOrCreate(
                ['code' => $network['code']],
                [
                    'name' => $network['name'],
                    'native_asset_id' => $assetBySymbol->get($network['native'])?->id,
                    'is_active' => true,
                ],
            );
        }

        $institutions = [
            ['name' => 'Binance', 'type' => 'exchange', 'website' => 'https://www.binance.com', 'color' => '#F0B90B'],
            ['name' => 'CoinEx', 'type' => 'exchange', 'website' => 'https://www.coinex.com', 'color' => '#15a96f'],
            ['name' => 'Trust Wallet', 'type' => 'wallet', 'website' => 'https://trustwallet.com', 'color' => '#3375BB'],
            ['name' => 'Phantom', 'type' => 'wallet', 'website' => 'https://phantom.app', 'color' => '#5C6BC0'],
        ];

        foreach ($institutions as $institution) {
            CryptoInstitution::firstOrCreate(
                ['slug' => Str::slug($institution['name'])],
                [
                    'name' => $institution['name'],
                    'type' => $institution['type'],
                    'website' => $institution['website'],
                    'color' => $institution['color'],
                    'is_active' => true,
                ],
            );
        }
    }
}
