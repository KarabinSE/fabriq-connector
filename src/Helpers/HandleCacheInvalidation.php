<?php

namespace Karabin\FabriqConnector\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandleCacheInvalidation
{
    public static function clearCache(array $locales, Request $request, $debug = false)
    {
        if (config('app.debug')) {
            $debug = true;
        }

        foreach ($locales as $locale) {
            // Flush general tags that are dependant on updates and are localized
            // $hash = hash('sha256', 'fabriq_product_tree_locale_'.$locale);

            // Cache::forget($hash);

            foreach ($request->invalid_cache_keys as $tag) {
                // Example fabriq_articles_slug_the_slug_en
                $hash = hash('sha256', $tag.'_'.$locale);
                Cache::forget($hash);
                if ($debug) {
                    Log::debug('forgetting: '.$tag.'_'.$locale."({$hash})");
                }
            }
        }

        // Forgetting cache tags without cache prefixes
        foreach ($request->invalid_cache_keys as $tag) {
            // Example fabriq_articles_slug_the_slug
            $hash = hash('sha256', $tag);
            Cache::forget($hash);
            if ($debug) {
                Log::debug('forgetting: '.$tag."({$hash})");
            }
        }

        // Flush tags
        if ($request->has('invalid_cache_tags') && $request->has('invalid_cache_tags')) {
            if ($debug) {
                Log::debug('flushing ', $request->invalid_cache_tags ?? 'nothing!');
            }
            Cache::tags($request->invalid_cache_tags)->flush();
        }
    }
}
