<?php

use Illuminate\Database\Migrations\Migration;
//use Illuminate\Database\Schema\Blueprint;
use MStaack\LaravelPostgis\Schema\Blueprint;
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
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_km')->nullable();
            $table->multiPolygon('area');
            $table->boolean('is_administrative')->default(false);
            $table->foreignId('institution_id')
                ->nullable()
                ->references('id')
                ->on('institutions')
                ->onDelete('CASCADE');
            $table->timestamps();
            $table->spatialIndex(['area']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('areas');
    }
};
