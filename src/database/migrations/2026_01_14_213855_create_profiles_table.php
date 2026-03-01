<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained()
            ->cascadeDelete()
            ->unique();

            $table->string('name', 255);
            $table->string('image_path', 255)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('building', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
