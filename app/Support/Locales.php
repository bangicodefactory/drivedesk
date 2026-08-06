<?php

namespace App\Support;

use App\Http\Middleware\SetLocale;

/**
 * Which locales get their own public URL (BAN-263).
 *
 * Locale used to live only in the session, so all languages shared one URL and
 * a crawler — which has no session — only ever saw the client's guest default.
 * hreflang was impossible to express. Public marketing pages are now also served
 * under a locale prefix (/fr, /en, /ar) so each language has a real, indexable
 * URL that can be cross-referenced.
 */
class Locales
{
    /**
     * Locales that get a prefixed public URL, in a stable order.
     *
     * Two filters, both deliberate:
     *
     *  - Intersected with SetLocale::SUPPORTED. A client may list a locale in
     *    `supported_locales` that the app cannot actually serve — directonderweg
     *    lists `nl`, but SetLocale falls it back to French. Publishing /nl would
     *    advertise a URL that silently serves the wrong language.
     *  - `ary` is excluded. It is the app's switcher code for Moroccan Arabic,
     *    but the copy under it was rewritten into Modern Standard Arabic, so
     *    /ary and /ar would be the same page at two URLs. `ar` is what the text
     *    actually is.
     *
     * @return array<int,string>
     */
    public static function forPublicUrls(): array
    {
        $supported = (array) config('client.supported_locales', []);

        return array_values(array_filter(
            $supported,
            fn ($locale) => $locale !== 'ary' && in_array($locale, SetLocale::SUPPORTED, true)
        ));
    }

    /**
     * Regex constraint for the {locale} route parameter.
     *
     * Anchored to the exact list so the prefix route cannot swallow a literal
     * path like /login or /landing. Returns a never-matching pattern when the
     * client has no public locales, which keeps the route registered but inert.
     */
    public static function routeConstraint(): string
    {
        $locales = self::forPublicUrls();

        return $locales === [] ? '(?!)' : implode('|', array_map('preg_quote', $locales));
    }

    /**
     * hreflang alternates for a public page, plus x-default.
     *
     * x-default is the unprefixed URL, which serves the client's guest default —
     * the standard pattern for "we picked one for visitors who match nothing".
     *
     * @return array<int,array{hreflang:string,href:string}>
     */
    public static function alternatesFor(string $baseUrl, string $path = ''): array
    {
        $locales = self::forPublicUrls();

        if (count($locales) < 2) {
            // A single language needs no alternates, and emitting a lone
            // self-referencing hreflang is noise.
            return [];
        }

        $suffix     = $path === '' ? '' : '/'.ltrim($path, '/');
        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[] = [
                'hreflang' => $locale,
                'href'     => $baseUrl.'/'.$locale.$suffix,
            ];
        }

        $alternates[] = [
            'hreflang' => 'x-default',
            'href'     => $baseUrl.($suffix ?: '/'),
        ];

        return $alternates;
    }
}
