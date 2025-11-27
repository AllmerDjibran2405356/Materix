<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('list_komponen_desain', function (Blueprint $table) {
            // Ini akan otomatis membuat kolom 'created_at' dan 'updated_at'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('list_komponen_desain', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
