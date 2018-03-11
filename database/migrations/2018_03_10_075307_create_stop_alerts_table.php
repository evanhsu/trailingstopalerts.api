<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStopAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stop_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('symbol', 5);
            $table->unsignedInteger('user_id');
            $table->decimal('trail_amount', 8, 2);
            $table->string('trail_amount_units');

            $table->decimal('high_price', 8, 2);
            $table->dateTime('high_price_updated_at');

            $table->decimal('trigger_price', 8, 2);
            $table->boolean('triggered')->default(false);
            $table->timestamps();

            $table->foreign('symbol')->references('symbol')->on('stocks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stop_alerts');
    }
}
