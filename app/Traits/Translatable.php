<?php

namespace App\Traits;

use App\Models\Cms\Translation;


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
            return Translation::selectRaw('COALESCE(locale.id, fallback.id) AS id,'.self::getFallbackCoalesce())
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

    public static function getFallbackCoalesce(array $attributes = [], string $alias1 = 'locale', string $alias2 = 'fallback'): string
    {
        $attributes = (empty($attributes)) ? Translation::getTranslatableAttributes() : $attributes;
        $coalesce = '';

        foreach ($attributes as $attribute) {
            $coalesce .= 'COALESCE('.$alias1.'.'.$attribute.', '.$alias2.'.'.$attribute.') AS '.$attribute.',';
        }

        return substr($coalesce, 0, -1);
    }
}
