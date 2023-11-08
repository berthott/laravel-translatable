<?php

namespace berthott\Translatable\Tests\Feature\Basic;

use berthott\Crudable\Models\Traits\Crudable;
use berthott\Translatable\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    use Crudable;
    use Translatable;
    use HasFactory;

    public static function translatableFields(): array
    {
        return [
            'user_input',
            'nullable_input',
        ];
    }

    protected static function newFactory()
    {
        return DummyFactory::new();
    }
}
