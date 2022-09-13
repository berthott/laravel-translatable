<?php

namespace berthott\Translatable\Tests\Feature\Basic;

use berthott\Crudable\CrudableServiceProvider;
use berthott\Scopeable\ScopeableServiceProvider;
use berthott\Translatable\TranslatableServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
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
        $this->setUpTable();
        $this->setUpMigrationTables();
        Config::set('translatable.namespace', __NAMESPACE__);
        Config::set('crudable.namespace', [__NAMESPACE__, 'berthott\Translatable\Models']);
    }

    private function setUpTable(): void
    {
        Schema::create('dummies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->translatable('user_input');
            $table->timestamps();
        });
    }

    private function setUpMigrationTables(): void
    {
        foreach (glob(__DIR__.'/../../../database/migrations/*.php') as $filename) {
            include_once $filename;
        }
        (new \CreateTranslatableLanguagesTable)->up();
        (new \CreateTranslatableContentsTable)->up();
        (new \CreateTranslatableTranslationsTable)->up();
    }
}
