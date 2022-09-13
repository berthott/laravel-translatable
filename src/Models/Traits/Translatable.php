<?php

namespace berthott\Translatable\Models\Traits;

use berthott\Translatable\Facades\Translatable as FacadesTranslatable;
use berthott\Translatable\Models\TranslatableContent;
use Illuminate\Database\Eloquent\Model;

trait Translatable
{
    public static function translatableFields(): array
    {
        return [];
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
    }

    public function initializeTranslatable()
    {
        $this->append(self::translatableFields());
    }

    protected function mutateAttribute($key, $value)
    {
        if (in_array($key, self::translatableFields())) {
            return $this->getTranslatableField($key);
        }
        return parent::mutateAttribute($key, $value);
    }

    public static function updateTranslatableFields(Model $model)
    {
        foreach(self::translatableFields() as $field) {
            $model->setTranslatableField($field);
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
            $content = TranslatableContent::find($this->$column) ?: TranslatableContent::updateOrCreate([
                'language' => config('translatable.default_language'),
                'text' => $default_translation[config('translatable.default_language')],
            ]);
            $this->$column = $content->id;
            $this->offsetUnset($field);
            foreach ($this->assoc_array_to_translation_array($translations) as $translation) {
                $content->translatable_translations()->updateOrCreate($translation);
            }
        }
    }

    private function assoc_array_to_translation_array($translations): array {
        $ret = [];
        foreach ($translations as $language => $translation) {
            array_push($ret, [
                'language' => $language,
                'text' => $translation,
            ]);
        }
        return $ret;
    }

    private function array_slice_assoc(&$array, $keys) {
        return array_splice($array, array_search($keys, $array), 1);
    }

}
