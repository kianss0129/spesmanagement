<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('reports')) {
            return;
        }

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employer_id');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
