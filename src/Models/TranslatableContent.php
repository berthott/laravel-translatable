<?php

namespace berthott\Translatable\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for translatable content
 */
class TranslatableContent extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'language',
        'text',
    ];

    protected $with = [
        'translatable_translations',
        'translatable_language',
    ];
    
    /**
     * Get the routes associated with this permission.
     */
    public function translatable_language()
    {
        return $this->belongsTo(TranslatableLanguage::class);
    }
    
    /**
     * Get the translations associated with this language.
     */
    public function translatable_translations()
    {
        return $this->hasMany(TranslatableTranslation::class);
    }
}
