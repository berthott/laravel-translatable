<?php

namespace berthott\Translatable\Services;

use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

const CACHE_KEY = 'TranslatableService-Cache-Key';

class TranslatableService
{
    /**
     * Collection with all translatable classes.
     */
    private Collection $translatables;

    /**
     * The Constructor.
     */
    public function __construct()
    {
        $this->initTranslatableClasses();
    }

    /**
     * Get the translatable classes collection.
     */
    public function getTranslatableClasses(): Collection
    {
        return $this->translatables;
    }

    /**
     * Initialize the translatable classes collection.
     */
    private function initTranslatableClasses(): void
    {
        $this->translatables = Cache::sear(CACHE_KEY, function () {
            $translatables = [];
            $namespaces = config('translatable.namespace');
            foreach (is_array($namespaces) ? $namespaces : [$namespaces] as $namespace) {
                foreach (ClassFinder::getClassesInNamespace($namespace, config('translatable.namespace_mode')) as $class) {
                    foreach (class_uses_recursive($class) as $trait) {
                        if ('berthott\Translatable\Models\Traits\Translatable' == $trait) {
                            array_push($translatables, $class);
                        }
                    }
                }
            }
            return collect($translatables);
        });
    }

    /**
     * Get the target model.
     */
    public function getTarget(): string
    {
        if (!request()->segments() || $this->translatables->isEmpty()) {
            return '';
        }
        $model = Str::studly(Str::singular(request()->segment(count(explode('/', config('permissions.prefix'))) + 1)));

        return $this->translatables->first(function ($class) use ($model) {
            return Arr::last(explode('\\', $class)) === $model;
        }) ?: '';
    }

    /**
     * Get the translatable column name.
     */
    public function getColumnName(string $key): string
    {
        return $key.'_translatable_content_id';
    }
}
