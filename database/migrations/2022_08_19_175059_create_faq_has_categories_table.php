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
        Schema::create('faq_has_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_category_id')
                ->nullable()
                ->references('id')
                ->on('faq_categories')
                ->onDelete('SET NULL');
            $table->foreignId('faq_id')
                ->nullable()
                ->references('id')
                ->on('faqs')
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
        Schema::dropIfExists('faq_has_categories');
    }
};
