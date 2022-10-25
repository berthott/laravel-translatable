<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslatableContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translatable_contents', function (Blueprint $table) {
            $table->id();
            $table->string('language');
            $table->text('text');
            $table->timestamps();
        });
        
        Schema::table('translatable_contents', function (Blueprint $table) {
            $table->foreign('language', 'translatable_contents_language_fk')->references('id')->on('translatable_languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translatable_contents');
    }
}
