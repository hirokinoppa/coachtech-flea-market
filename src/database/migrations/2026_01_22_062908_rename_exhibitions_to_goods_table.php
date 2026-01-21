<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::rename('exhibitions', 'goods');
    }

    public function down()
    {
        Schema::rename('goods', 'exhibitions');
    }
};