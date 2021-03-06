<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('short_id', 8)->unique();
            $table->integer('user_id')->unsigned();
            // $table->string('street_name');
            // $table->string('house_number');
            // $table->string('city');
            // $table->string('postal_code');
            // $table->string('country');
            // $table->string('first_name');
            // $table->string('last_name');
            // $table->date('delivery_date');
            $table->float('price');
            $table->boolean('free_shipping')->default(false);
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('orders');
    }
}
