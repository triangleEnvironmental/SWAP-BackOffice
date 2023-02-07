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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('description', 5000)->nullable();
            $table->point('location');
            $table->integer('count_like')->default(0);
            $table->foreignId('report_type_id')
                ->nullable()
                ->references('id')
                ->on('report_types')
                ->onDelete('SET NULL');
            $table->foreignId('reported_by_user_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
            $table->foreignId('report_status_id')
                ->nullable()
                ->references('id')
                ->on('report_statuses')
                ->onDelete('SET NULL');
            $table->foreignId('assigner_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
            $table->foreignId('assignee_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
            $table->timestamps();
            $table->softDeletes();
            $table->spatialIndex(['location']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
};
