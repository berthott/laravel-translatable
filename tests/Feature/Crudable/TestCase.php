<?php

namespace berthott\Translatable\Tests\Feature\Crudable;

use berthott\Crudable\CrudableServiceProvider;
use berthott\Scopeable\ScopeableServiceProvider;
use berthott\Translatable\TranslatableServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpTable();
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslatableServiceProvider::class,
            CrudableServiceProvider::class,
            ScopeableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        Config::set('translatable.namespace', __NAMESPACE__);
        Config::set('translatable.languages', ['en' => 'English', 'de' => 'German', 'fr' => 'Frensh']);
        Config::set('translatable.optional_languages', ['fr']);
        Config::set('translatable.default_language', 'de');
        Config::set('crudable.namespace', [__NAMESPACE__, 'berthott\Translatable\Models']);
    }

    private function setUpTable(): void
    {
        Schema::create('dummies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->translatable('user_input');
            $table->timestamps();
        });

        // for delete cascadation, is disabled in sqlite by default
        DB::statement(DB::raw('PRAGMA foreign_keys=1')->getValue(DB::connection()->getQueryGrammar()));
    }
}
