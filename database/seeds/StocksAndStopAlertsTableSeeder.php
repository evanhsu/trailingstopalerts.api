<?php

use App\Domain\StopAlert;
use App\Domain\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StocksAndStopAlertsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('stop_alerts')->truncate();
        DB::table('stocks')->truncate();

        Schema::enableForeignKeyConstraints();

        User::all()->each(function($user) {
            factory(App\Domain\Stock::class, 3)
                ->create()
                ->each(function ($stock) use ($user) {
                    StopAlert::create([
                        'symbol'    => $stock->symbol,
                        'user_id'   => $user->id,
                        'trail_amount'  => 5,
                        'trail_amount_units'    => 'percent',
                        'high_price'            => $stock->price * 1.08,
                        'high_price_updated_at' => new DateTime(),
                        'trigger_price'         => $stock->price * 1.08 * 0.95,
                        'triggered'             => false,
                    ]);
                });
        });
    }
}
