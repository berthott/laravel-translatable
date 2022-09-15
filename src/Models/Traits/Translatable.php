<?php

namespace berthott\Translatable\Models\Traits;

use berthott\Translatable\Facades\Translatable as FacadesTranslatable;
use berthott\Translatable\Models\TranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Translatable
{
    public static function translatableFields(): array
    {
        return [];
    }

    public static function translatableRules(mixed $id): array
    {
        $ret = [];
        foreach(self::translatableFields() as $field) {
            $ret[FacadesTranslatable::getColumnName($field)] = 'nullable|numeric';
            $ret[$field] = 'array';
            foreach(config('translatable.languages') as $language => $language_name) {
                $ret[$field.'.'.$language] = join('|', [
                    in_array($language, config('translatable.optional_languages')) || $id ? 'nullable' : 'required',
                    'string',
                ]);
            }
        };
        return $ret;
    }

    public static function bootTranslatable()
    {
        static::creating(function (Model $model) {
            static::updateTranslatableFields($model);
        });
        static::saving(function (Model $model) {
            static::updateTranslatableFields($model);
        });
        static::updating(function (Model $model) {
            static::updateTranslatableFields($model);
        });
        static::deleting(function (Model $model) {
            static::deleteTranslatableFields($model);
        });
    }

    public function initializeTranslatable()
    {
        $this->append(self::translatableFields());
        $id_columns = [];
        foreach(self::translatableFields() as $field) {
            $id_columns[] = FacadesTranslatable::getColumnName($field);
        }
        $fillable = array_diff(array_merge(array_diff(self::translatableFields()), $this->fillable), $id_columns);
        $this->fillable($fillable);
    }

    public function __get($key)
    {
        $ret = parent::__get($key);
        if ($ret) {
            return $ret;
        }
        foreach(self::translatableFields() as $field) {
            if ($key === $field) {
                return $this->getTranslatableField($field);
            }
        }
    }

    public function __call($method, $parameters)
    {
        foreach(self::translatableFields() as $field) {
            if ($method === 'get'.Str::studly($field).'Attribute') {
                return $this->getTranslatableField($field);
            }
        }
        return parent::__call($method, $parameters);
    }

    public static function updateTranslatableFields(Model $model)
    {
        foreach(self::translatableFields() as $field) {
            $model->setTranslatableField($field);
        }
    }

    // cascade delete not working in tests
    public static function deleteTranslatableFields(Model $model)
    {
        foreach(self::translatableFields() as $field) {
            $model->deleteTranslatableField($field);
        }
    }

    public function getTranslatableField(string $field): array
    {
        $ret = [];
        $column = FacadesTranslatable::getColumnName($field);
        if ($content = TranslatableContent::find($this->$column)) {
            $ret[config('translatable.default_language')] = $content->text;
            foreach($content->translatable_translations as $translation) {  
                $ret[$translation->language] = $translation->text;
            }
        }
        return $ret;
    }

    public function setTranslatableField(string $field)
    {
        if ($translations = $this->$field) {
            $column = FacadesTranslatable::getColumnName($field);
            $default_translation = $this->array_slice_assoc($translations, config('translatable.default_language'));
            $content = TranslatableContent::updateOrCreate(
                ['id' => $this->$column],
                [
                    'language' => config('translatable.default_language'),
                    'text' => $default_translation[config('translatable.default_language')
                ],
            ]);
            $this->$column = $content->id;
            $this->offsetUnset($field);
            foreach ($translations as $language => $translation) {
                $content->translatable_translations()->updateOrCreate(
                    ['language' => $language],
                    ['text' => $translation],
                );
            }
        }
    }

    public function deleteTranslatableField(string $field)
    {
        $column = FacadesTranslatable::getColumnName($field);
        $content = TranslatableContent::find($this->$column);
        $content->translatable_translations()->delete();
        $content->delete();
    }

    private function array_slice_assoc(&$array, $keys) {
        return array_splice($array, array_search($keys, $array), 1);
    }

}
