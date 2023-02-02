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
            'language' => 'de',
            'text' => $expected['user_input']['de'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
            'text' => $expected['user_input']['en'],
        ]);
        $this->get(route('dummies.index'))
            ->assertStatus(200)
            ->assertJsonFragment(array_merge($expected, ['user_input_translatable_content_id' => 1]));
    }

    public function test_show(): void
    {
        $dummy = Dummy::factory()->create();
        $this->get(route('dummies.show', ['dummy' => $dummy->id]))
            ->assertStatus(200)
            ->assertJsonFragment($dummy->toArray());
    }

    public function test_validation(): void
    {
        $this->post(route('dummies.store'))
            ->assertJsonValidationErrors(['user_input.en', 'user_input.de']);
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
            'language' => 'de',
            'text' => $expected['user_input']['de'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
            'text' => $expected['user_input']['en'],
        ]);
    }

    public function test_update(): void
    {
        $dummy = Dummy::factory()->create();
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'de',
            'text' => $dummy->user_input['de'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
            'text' => $dummy->user_input['en'],
        ]);
        $new_user_input = [
            'user_input' => [
                'en' => 'English Content',
                'de' => 'German Content',
            ]
        ];
        $this->put(route('dummies.update', ['dummy' => $dummy->id]), $new_user_input)
            ->assertSuccessful()
            ->assertJsonFragment($new_user_input);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'de',
            'text' => $new_user_input['user_input']['de'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
            'text' => $new_user_input['user_input']['en'],
        ]);
    }

    public function test_update_optional(): void
    {
        $dummy = Dummy::factory()->create();
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'de',
            'text' => $dummy->user_input['de'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
            'text' => $dummy->user_input['en'],
        ]);
        $this->assertDatabaseMissing('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'fr',
        ]);
        $new_user_input = [
            'user_input' => [
                'en' => 'English Content',
                'de' => 'German Content',
                'fr' => 'Frensh Content',
            ]
        ];
        $this->put(route('dummies.update', ['dummy' => $dummy->id]), $new_user_input)
            ->assertSuccessful()
            ->assertJsonFragment($new_user_input);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'de',
            'text' => $new_user_input['user_input']['de'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
            'text' => $new_user_input['user_input']['en'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 2,
            'translatable_content_id' => 1,
            'language' => 'fr',
            'text' => $new_user_input['user_input']['fr'],
        ]);
    }

    public function test_update_optional_to_empty(): void
    {
        $dummy = Dummy::factory()->create();
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseMissing('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'fr',
        ]);
        $first_user_input = [
            'user_input' => [
                'en' => 'English Content',
                'de' => 'German Content',
                'fr' => 'Frensh Content',
            ]
        ];
        $this->put(route('dummies.update', ['dummy' => $dummy->id]), $first_user_input)
            ->assertSuccessful()
            ->assertJsonFragment($first_user_input);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 2,
            'translatable_content_id' => 1,
            'language' => 'fr',
            'text' => $first_user_input['user_input']['fr'],
        ]);
        $second_user_input = [
            'user_input' => [
                'en' => 'English Content',
                'de' => 'German Content',
            ]
        ];
        $this->put(route('dummies.update', ['dummy' => $dummy->id]), $second_user_input)
            ->assertSuccessful()
            ->assertJsonFragment($second_user_input);
            $this->assertDatabaseMissing('translatable_translations', [
                'id' => 2,
                'translatable_content_id' => 1,
                'language' => 'fr',
            ]);
    }

    public function test_update_optional_to_null(): void
    {
        $dummy = Dummy::factory()->create();
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseMissing('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'fr',
        ]);
        $first_user_input = [
            'user_input' => [
                'en' => 'English Content',
                'de' => 'German Content',
                'fr' => 'Frensh Content',
            ]
        ];
        $this->put(route('dummies.update', ['dummy' => $dummy->id]), $first_user_input)
            ->assertSuccessful()
            ->assertJsonFragment($first_user_input);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 2,
            'translatable_content_id' => 1,
            'language' => 'fr',
            'text' => $first_user_input['user_input']['fr'],
        ]);
        $second_user_input = [
            'user_input' => [
                'en' => 'English Content',
                'de' => 'German Content',
                'fr' => null,
            ]
        ];
        $this->put(route('dummies.update', ['dummy' => $dummy->id]), $second_user_input)
            ->assertSuccessful()
            ->assertJsonFragment([
                'user_input' => [
                    'en' => 'English Content',
                    'de' => 'German Content',
                ]
            ]);
            $this->assertDatabaseMissing('translatable_translations', [
                'id' => 2,
                'translatable_content_id' => 1,
                'language' => 'fr',
            ]);
    }

    public function test_delete(): void
    {
        $dummy = Dummy::factory()->create();
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'de',
            'text' => $dummy->user_input['de'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
            'text' => $dummy->user_input['en'],
        ]);
        $this->delete(route('dummies.update', ['dummy' => $dummy->id]))
            ->assertSuccessful();
        $this->assertDatabaseMissing('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseMissing('translatable_contents', [
            'id' => 1,
            'language' => 'de',
        ]);
        $this->assertDatabaseMissing('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'en',
        ]);
    }
}
