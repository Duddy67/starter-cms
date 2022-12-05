<?php

namespace App\Traits;

use App\Models\Translation;


trait Translatable
{
    /**
     * Get all of the item's translations.
     */
    public function translations(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get a given item's translation.
     */
    public function getTranslation(string $locale, bool $fallback = false): Translation|null
    {
        if ($fallback) {
            return Translation::select('*')
                ->where('translatable_id', $this->id)
                ->where('translatable_type', get_class($this))
                ->where(function ($query) use($locale) {
                     $query->where('locale', $locale)
                           ->orWhere('locale', config('app.fallback_locale'));
            })->first();
        }

        return $this->morphMany(Translation::class, 'translatable')->where('locale', $locale)->first();
    }

    /**
     * Get a given item's translation or create it if it doesn't exist.
     */
    public function getOrCreateTranslation(string $locale): Translation
    {
        $translation = $this->getTranslation($locale);

        if ($translation === null) {
            $translation =  Translation::create(['locale' => $locale]);
            $this->translations()->save($translation);
        }

        return $translation;
    }
}
