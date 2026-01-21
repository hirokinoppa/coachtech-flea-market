<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->foreignId('good_id')
            ->constrained('goods')
            ->cascadeOnDelete();

            $table->foreignId('buyer_id')
            ->constrained('users')
            ->cascadeOnDelete();

            $table->integer('price');
            $table->tinyInteger('status')->default(1);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
            $table->unique('good_id');
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
