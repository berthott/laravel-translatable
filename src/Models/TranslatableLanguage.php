<?php

namespace berthott\Translatable\Models;

use berthott\Crudable\Models\Traits\Crudable;
use Illuminate\Database\Eloquent\Model;

/**
 * Model for translatable languages
 */
class TranslatableLanguage extends Model
{
    use Crudable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'optional',
        'default',
    ];
    
    /**
     * Get the content associated with this language.
     */
    public function translatable_content()
    {
        return $this->hasMany(TranslatableContent::class);
    }
    
    /**
     * Get the translations associated with this language.
     */
    public function translatable_translations()
    {
        return $this->hasMany(TranslatableTranslation::class);
    }
}
