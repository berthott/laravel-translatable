<?php

namespace berthott\Translatable\Tests\Feature\Crudable;

use Illuminate\Support\Facades\Route;

class CrudableTranslatableTest extends TestCase
{
    public function test_routes_exist(): void
    {
        $expectedRoutes = [
            'translatable_languages.index',
            'translatable_languages.store',
            'translatable_languages.show',
            'translatable_languages.update',
            'translatable_languages.destroy',
            
            'translatable_languages.schema',

            'dummies.index',
            'dummies.store',
            'dummies.show',
            'dummies.update',
            'dummies.destroy',

            'dummies.schema',
        ];
        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes);
        }
    }

    public function test_index(): void
    {
        $dummy = Dummy::factory()->make();
        $expected = ['user_input' => $dummy->user_input];
        $dummy->save();
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'en',
            'text' => $expected['user_input']['en'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'de',
            'text' => $expected['user_input']['de'],
        ]);
        $this->get(route('dummies.index'))
            ->assertStatus(200)
            ->assertJsonFragment(array_merge($expected, ['user_input_translatable_content_id' => 1]));
    }

    public function test_store(): void
    {
        $dummy = Dummy::factory()->make();
        $expected = ['user_input' => $dummy->user_input];
        $this->post(route('dummies.store'), $expected)
            ->assertSuccessful()
            ->assertJsonFragment(array_merge($expected, ['user_input_translatable_content_id' => 1]));
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'en',
            'text' => $expected['user_input']['en'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'de',
            'text' => $expected['user_input']['de'],
        ]);
    }
}
