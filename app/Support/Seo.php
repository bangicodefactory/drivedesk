<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Server-rendered SEO metadata for the Blade shell (BAN-262).
 *
 * The app is an Inertia SPA with SSR deliberately off (perf-audit F-23), so
 * anything React sets client-side — including the page title — does not exist
 * for a crawler that cannot run JavaScript. Google renders eventually; social
 * scrapers (LinkedIn, WhatsApp, Slack, Facebook) and most AI crawlers never do.
 * Before this, sharing any URL produced a bare link titled with the app name and
 * no description or image.
 *
 * So the handful of tags that must be in the initial HTML are emitted here, by
 * PHP, per client. React still owns the title once it mounts; this just means
 * the document is not empty before that.
 *
 * Only genuinely public pages are indexable. Everything else — the admin app,
 * auth screens, the installer — is explicitly noindex rather than merely
 * unlinked, so a leaked URL cannot end up in the index.
 */
class Seo
{
    /**
     * Route names that are public marketing surfaces. Everything not listed is
     * noindex. Kept as an allowlist on purpose: a new admin route should never
     * become indexable by default.
     *
     * @var array<int,string>
     */
    private const INDEXABLE_ROUTES = [
        'client.home',   // /landing — B2C storefront (feature: public_storefront)
        'contact',
        'search',
    ];

    /** @return array<string,mixed> */
    public static function forRequest(Request $request): array
    {
        $routeName  = optional($request->route())->getName();
        $isHome     = $request->path() === '/';
        $indexable  = self::isIndexable($routeName, $isHome);
        $seo        = config('client.seo', []);

        return [
            'title'       => $seo['title'] ?? config('app.name', 'RentCar'),
            'description' => $seo['description'] ?? null,
            'canonical'   => self::canonical($request),
            'image'       => self::image($seo),
            'siteName'    => config('client.name') ? ($seo['site_name'] ?? config('app.name')) : config('app.name'),
            'locale'      => app()->getLocale(),
            'htmlLang'    => self::htmlLang(),
            'dir'         => self::isRtl() ? 'rtl' : 'ltr',
            'indexable'   => $indexable,
            'twitterSite' => $seo['twitter'] ?? null,
        ];
    }

    /** Guests only ever reach a public page; anything else must not be indexed. */
    private static function isIndexable(?string $routeName, bool $isHome): bool
    {
        // The demo gateway lives at "/" and only exists for clients that enable it.
        if ($isHome) {
            return (bool) feature('demo_gateway');
        }

        if ($routeName === null) {
            return false;
        }

        // The storefront family is indexable only while the client serves it.
        if (in_array($routeName, self::INDEXABLE_ROUTES, true)) {
            return (bool) feature('public_storefront');
        }

        return false;
    }

    /**
     * Canonical URL: scheme + host + path, no query string.
     *
     * Query parameters on these pages are filters and tracking tags, never
     * distinct content, so folding them onto the bare path is correct and stops
     * ?utm_… variants competing with each other.
     */
    private static function canonical(Request $request): string
    {
        return rtrim($request->getSchemeAndHttpHost().'/'.ltrim($request->path(), '/'), '/')
            ?: $request->getSchemeAndHttpHost();
    }

    /**
     * Absolute URL for the social preview image, if the client configured one
     * *and* it exists.
     *
     * A local path is checked on disk first: an og:image pointing at a 404 is
     * worse than none — LinkedIn and Slack render a broken card rather than
     * falling back to the text-only one. So a client can configure the filename
     * ahead of the asset landing, and the tag simply starts working when the
     * file appears.
     */
    private static function image(array $seo): ?string
    {
        $image = $seo['og_image'] ?? null;

        if (! $image) {
            return null;
        }

        if (str_starts_with($image, 'http')) {
            return $image;
        }

        $relative = ltrim($image, '/');

        return file_exists(public_path($relative)) ? asset($relative) : null;
    }

    /**
     * The language to declare on <html>.
     *
     * `ary` (Moroccan Arabic) is a valid locale for the app's own switcher, but
     * the DriveDesk copy under it was rewritten into Modern Standard Arabic —
     * declaring `ary` tells search engines the page is in a language it is not.
     * Map it to `ar`, which is what the text actually is.
     */
    private static function htmlLang(): string
    {
        $locale = app()->getLocale();

        return $locale === 'ary' ? 'ar' : str_replace('_', '-', $locale);
    }

    private static function isRtl(): bool
    {
        return in_array(app()->getLocale(), ['ar', 'ary'], true);
    }

    /**
     * Organization + SoftwareApplication JSON-LD for the product's own gateway.
     *
     * Emitted only on the demo gateway: it describes DriveDesk-the-product, so
     * it would be a lie on a tenant's B2C storefront.
     *
     * @return array<string,mixed>|null
     */
    public static function jsonLd(Request $request): ?array
    {
        if ($request->path() !== '/' || ! feature('demo_gateway')) {
            return null;
        }

        $seo  = config('client.seo', []);
        $name = $seo['site_name'] ?? config('app.name', 'RentCar');
        $url  = $request->getSchemeAndHttpHost();

        $organization = array_filter([
            '@type'       => 'Organization',
            '@id'         => $url.'/#organization',
            'name'        => $name,
            'url'         => $url,
            'logo'        => self::image($seo),
            'description' => $seo['description'] ?? null,
        ]);

        $application = array_filter([
            '@type'               => 'SoftwareApplication',
            'name'                => $name,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem'     => 'Web',
            'url'                 => $url,
            'description'         => $seo['description'] ?? null,
            'publisher'           => ['@id' => $url.'/#organization'],
        ]);

        return [
            '@context' => 'https://schema.org',
            '@graph'   => [$organization, $application],
        ];
    }
}
