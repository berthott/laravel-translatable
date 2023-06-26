<?php

namespace berthott\Translatable\Services;

use berthott\Targetable\Services\TargetableService;
use berthott\Translatable\Facades\Translatable;

/**
 * TargetableService implementation for an targetable class.
 * 
 * @link https://docs.syspons-dev.com/laravel-targetable
 */
class TranslatableService extends TargetableService
{
    public function __construct()
    {
        parent::__construct(Translatable::class, 'translatable');
    }

    /**
     * Get the translatable column name.
     */
    public function getColumnName(string $key): string
    {
        return $key.'_translatable_content_id';
    }
}
