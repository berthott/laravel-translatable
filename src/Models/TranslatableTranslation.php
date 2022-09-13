<?php

namespace berthott\Translatable\Models;

use Illuminate\Database\Eloquent\Model;

class TranslatableTranslation extends Model
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
    
    /**
     * Get the routes associated with this permission.
     */
    public function translatable_language()
    {
        return $this->belongsTo(TranslatableLanguage::class);
    }
    
    /**
     * Get the routes associated with this permission.
     */
    public function translatable_content()
    {
        return $this->belongsTo(TranslatableContent::class);
    }
}
