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
            $attributes = Translation::getTranslatableAttributes();
            $coalesce = 'COALESCE(locale.id, fallback.id) AS id,';

            foreach ($attributes as $attribute) {
                $coalesce .= 'COALESCE(locale.'.$attribute.', fallback.'.$attribute.') AS '.$attribute.',';
            }

            $coalesce = substr($coalesce, 0, -1);

            return Translation::selectRaw($coalesce)
                  ->from('translations')
                  ->where('translations.translatable_id', $this->id)
                  ->where('translations.translatable_type', get_class($this))
                  ->leftJoin('translations AS locale', function ($join) use($locale) { 
                      $join->on('locale.translatable_id', 'translations.translatable_id')
                           ->on('locale.translatable_type', 'translations.translatable_type')
                           ->where('locale.locale', $locale);
                  })->leftJoin('translations AS fallback', function ($join) {
                        $join->on('fallback.translatable_id', 'translations.translatable_id')
                             ->on('fallback.translatable_type', 'translations.translatable_type')
                             ->where('fallback.locale', config('app.fallback_locale'));
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
