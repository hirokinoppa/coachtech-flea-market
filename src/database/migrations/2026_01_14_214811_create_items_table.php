<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description');
            $table->string('brand', 255)->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('condition', 20);
            $table->integer('price');
            $table->boolean('is_sold')->default(false);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
}