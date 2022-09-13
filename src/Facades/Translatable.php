<?php

namespace berthott\Translatable\Facades;

use Illuminate\Support\Facades\Facade;

class Translatable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'Translatable';
    }
}
