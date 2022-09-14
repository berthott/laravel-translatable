<?php

namespace berthott\Translatable\Tests\Feature\Basic;

class BasicTranslatableTest extends TestCase
{
    public function test_basic(): void
    {
        $dummy = Dummy::factory()->make();
        $expected = $dummy->user_input;
        $dummy->save();
        $this->assertDatabaseHas('dummies', ['user_input_translatable_content_id' => 1]);
        $this->assertDatabaseHas('translatable_contents', [
            'id' => 1,
            'language' => 'en',
            'text' => $expected['en'],
        ]);
        $this->assertDatabaseHas('translatable_translations', [
            'id' => 1,
            'translatable_content_id' => 1,
            'language' => 'de',
            'text' => $expected['de'],
        ]);
        $this->assertSame($expected, $dummy->user_input);
    }
}
