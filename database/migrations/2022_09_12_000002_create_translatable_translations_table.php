<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslatableTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translatable_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('translatable_content_id');
            $table->string('language');
            $table->string('text');
            $table->timestamps();
        });
        
        Schema::table('translatable_translations', function (Blueprint $table) {
            $table->unique(['translatable_content_id', 'language'], 'translatable_translations_language_unique');
            $table->foreign('language', 'translatable_translations_language_fk')->references('id')->on('translatable_languages')->onDelete('cascade');
            $table->foreign('translatable_content_id', 'translatable_translations_content_fk')->references('id')->on('translatable_contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translatable_translations');
    }
}
