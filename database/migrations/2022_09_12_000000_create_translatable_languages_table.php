<?php

use berthott\Translatable\Models\TranslatableLanguage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTranslatableLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translatable_languages', function (Blueprint $table) {
            $table->string('id')->primary()->unique();
            $table->string('name');
            $table->boolean('optional')->default(false);
            $table->boolean('default')->default(false);
            $table->timestamps();
        });

        foreach (config('translatable.languages') as $id => $name) {
            DB::table('translatable_languages')->insert([
                'id' => $id,
                'name' => $name,
                'optional' => in_array($id, config('translatable.optional_languages')),
                'default' => $id === config('translatable.default_language'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translatable_languages');
    }
}
