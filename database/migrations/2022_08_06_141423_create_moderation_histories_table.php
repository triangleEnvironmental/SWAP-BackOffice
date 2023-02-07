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
        Schema::create('moderation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_status_id')
                ->nullable()
                ->references('id')
                ->on('report_statuses')
                ->onDelete('SET NULL');
            $table->foreignId('to_status_id')
                ->nullable()
                ->references('id')
                ->on('report_statuses')
                ->onDelete('SET NULL');
            $table->foreignId('report_id')
                ->references('id')
                ->on('reports')
                ->onDelete('CASCADE');
            $table->foreignId('moderated_by_user_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
            $table->foreignId('comment_id')
                ->nullable()
                ->references('id')
                ->on('comments')
                ->onDelete('SET NULL');
            $table->foreignId('master_notification_id')
                ->nullable()
                ->references('id')
                ->on('master_notifications')
                ->onDelete('SET NULL');
            $table->softDeletes();
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
        Schema::dropIfExists('moderation_histories');
    }
};
