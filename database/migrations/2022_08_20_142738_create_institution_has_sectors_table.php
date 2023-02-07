<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('institution_has_sectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')
                ->references('id')
                ->on('institutions')
                ->onDelete('CASCADE');
            $table->foreignId('sector_id')
                ->references('id')
                ->on('sectors')
                ->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('institution_has_sectors');
    }
};
