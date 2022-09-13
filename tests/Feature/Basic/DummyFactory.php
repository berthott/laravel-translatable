<?php

namespace berthott\Translatable\Tests\Feature\Basic;

use Illuminate\Database\Eloquent\Factories\Factory;

class DummyFactory extends Factory
{
    protected $model = Dummy::class;

    public function definition()
    {
        return [
            'user_input' => [
                'en' => $this->faker->text(),
                'de' => $this->faker->text(),
            ],
        ];
    }
}
