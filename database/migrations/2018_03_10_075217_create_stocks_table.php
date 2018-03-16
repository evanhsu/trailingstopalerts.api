<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->string('symbol', 5);
            $table->string('name')->nullable();
            $table->decimal('open', 9, 3);
            $table->decimal('close', 9, 3);
            $table->decimal('high', 9, 3);
            $table->decimal('low', 9, 3);
            $table->dateTimeTz('quote_updated_at');

            $table->timestamps();

            $table->primary('symbol');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
