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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('text')->nullable();
            $table->foreignId('report_id')
                ->references('id')
                ->on('reports')
                ->onDelete('CASCADE');
            $table->foreignId('commented_by_user_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
            $table->boolean('is_public')
                ->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
