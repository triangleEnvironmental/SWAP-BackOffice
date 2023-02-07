<?php

use App\Models\Report;
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
        Schema::create('master_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('notificationable_type')->nullable();
            $table->foreignId('notificationable_id')->nullable();
            $table->string('targetable_type')->nullable();
            $table->foreignId('targetable_id')->nullable();
            $table->integer('count_total_target_users')->nullable();
            $table->string('platform')->nullable(); // 'citizen' , 'moderator'
            $table->foreignId('institution_id')
                ->nullable()
                ->references('id')
                ->on('institutions')
                ->onDelete('SET NULL');
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
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
        Schema::dropIfExists('master_notifications');
    }
};
