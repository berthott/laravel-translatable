<?php

namespace berthott\Translatable\Models\Traits;

use berthott\Translatable\Facades\Translatable as FacadesTranslatable;
use berthott\Translatable\Models\TranslatableContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Translatable
{
    /**
     * Bootstrap the trait.
     * 
     * Intercept crud methods with translation logic.
     */
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

    /**
     * Initialize the trait.
     */
    public function initializeTranslatable()
    {
        // set appended fields
        $this->append(self::translatableFields());

        // set fillable fields
        $id_columns = [];
        foreach(self::translatableFields() as $field) {
            $id_columns[] = FacadesTranslatable::getColumnName($field);
        }
        $fillable = array_diff(array_merge(array_diff(self::translatableFields()), $this->fillable), $id_columns);
        $this->fillable($fillable);
    }

    /**
     * Override default attribute behavior.
     * 
     * Return the translations for translatable fields.
     */
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

    /**
     * Override default attribute behavior.
     * 
     * Return the translations for translatable fields.
     */
    public function __call($method, $parameters)
    {
        foreach(self::translatableFields() as $field) {
            if ($method === 'get'.Str::studly($field).'Attribute') {
                return $this->getTranslatableField($field);
            }
        }
        return parent::__call($method, $parameters);
    }

    /**
     * Returns an array of translatable fields.
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     * 
     * @api
     */
    public static function translatableFields(): array
    {
        return [];
    }

    /**
     * Returns an array of optionally translatable fields.
     * 
     * **optional**
     * 
     * Defaults to `[]`.
     * 
     * @api
     */
    public static function translatableOptionalFields(): array
    {
        return [];
    }

    /**
     * Return the generated validation rules for the translatable fields.
     * 
     * Can be used for validation.
     * 
     * @api
     */
    public static function translatableRules(mixed $id): array
    {
        $ret = [];
        foreach(self::translatableFields() as $field) {
            $ret[FacadesTranslatable::getColumnName($field)] = 'nullable|numeric';
            $ret[$field] = 'array';
            foreach(config('translatable.languages') as $language => $language_name) {
                $ret[$field.'.'.$language] = join('|', [
                    in_array($language, self::optionalFields($field)) || $id ? 'nullable' : 'required',
                    'string',
                ]);
            }
        };
        return $ret;
    }

    /**
     * Merge the optional fields from the trait or the config.
     */
    private static function optionalFields(string $field): array
    {
        $additional = array_key_exists($field, self::translatableOptionalFields()) 
            ? self::translatableOptionalFields()[$field]
            : [];
        return array_merge(config('translatable.optional_languages'), $additional);
    }

    /**
     * Update all translatable fields.
     */
    private static function updateTranslatableFields(Model $model)
    {
        foreach(self::translatableFields() as $field) {
            $model->setTranslatableField($field);
        }
    }

    /**
     * Delete all translatable fields.
     * 
     * Important: cascade delete not working in tests
     */
    private static function deleteTranslatableFields(Model $model)
    {
        foreach(self::translatableFields() as $field) {
            $model->deleteTranslatableField($field);
        }
    }

    /**
     * Return the translations of a translatable field.
     */
    private function getTranslatableField(string $field): array
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

    /**
     * Update a specific translatable field.
     * 
     * The model has an attribute with the translatable content set for the name `$field`.
     * This is saved to the translatable content table and associated with it.
     */
    private function setTranslatableField(string $field)
    {
        // call of magic __get()
        if ($translations = $this->$field) {
            $column = FacadesTranslatable::getColumnName($field);
            $default_translation = $this->array_slice_assoc($translations, config('translatable.default_language'));
            // set default translation
            $content = TranslatableContent::updateOrCreate(
                ['id' => $this->$column],
                [
                    'language' => config('translatable.default_language'),
                    'text' => $default_translation[config('translatable.default_language')
                ],
            ]);
            $this->$column = $content->id;
            $this->offsetUnset($field);
            // delete when empty
            $content->translatable_translations()->whereNotIn('language', array_keys($translations))->delete();
            // set the translations
            foreach ($translations as $language => $translation) {
                if (!$translation) { // delete when null
                    $content->translatable_translations()->where('language', $language)->delete();
                    continue;
                }
                $content->translatable_translations()->updateOrCreate(
                    ['language' => $language],
                    ['text' => $translation],
                );
            }
        }
    }

    /**
     * Delete a specific translatable field.
     */
    private function deleteTranslatableField(string $field)
    {
        $column = FacadesTranslatable::getColumnName($field);
        $content = TranslatableContent::find($this->$column);
        $content->translatable_translations()->delete();
        $content->delete();
    }

    /**
     * Slice an array from a given key
     */
    private function array_slice_assoc(array &$array, string $keys) {
        return array_splice($array, array_search($keys, array_keys($array)), 1);
    }

}
