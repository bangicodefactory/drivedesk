<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * config/app.php's fallback for `url`. Reaching this value means APP_URL was
     * never set for the deploy, not that localhost is the intended origin.
     */
    private const UNCONFIGURED_APP_URL = 'http://localhost';

    /**
     * Route names that are public marketing surfaces. Everything not listed is
     * noindex. Kept as an allowlist on purpose: a new admin route should never
     * become indexable by default.
     *
     * @var array<int,string>
     */
    private const INDEXABLE_ROUTES = [
        // /landing — B2C storefront (feature: public_storefront).
        //
        // `contact` and `search` are deliberately absent: they are Route::view /
        // closure routes rendering client.layouts.app, not this shell, so listing
        // them here would be dead config implying an SEO treatment they never
        // receive. Move them onto the Inertia shell before adding them back.
        'client.home',
    ];

    /** @return array<string,mixed> */
    public static function forRequest(Request $request): array
    {
        $routeName  = optional($request->route())->getName();
        $isGateway  = self::isGateway($request, $routeName);
        $indexable  = self::isIndexable($routeName, $isGateway);
        $seo        = config('client.seo', []);

        return [
            'title'       => $seo['title'] ?? config('app.name', 'RentCar'),
            'description' => $seo['description'] ?? null,
            'canonical'   => self::canonical($request),
            'image'       => self::image($seo, self::baseUrl($request)),
            'siteName'    => config('client.name') ? ($seo['site_name'] ?? config('app.name')) : config('app.name'),
            'locale'      => app()->getLocale(),
            'htmlLang'    => self::htmlLang(),
            'dir'         => self::isRtl() ? 'rtl' : 'ltr',
            'indexable'   => $indexable,
            'twitterSite' => $seo['twitter'] ?? null,
            // hreflang only makes sense on the page that actually exists per
            // locale; the storefront has no locale URLs yet.
            'alternates'  => $isGateway && $indexable
                ? Locales::alternatesFor(self::baseUrl($request))
                : [],
        ];
    }

    /** The product's public home, unprefixed (`/`) or locale-prefixed (`/fr`). */
    private static function isGateway(Request $request, ?string $routeName): bool
    {
        return $request->path() === '/' || $routeName === 'public.home.locale';
    }

    /** Guests only ever reach a public page; anything else must not be indexed. */
    private static function isIndexable(?string $routeName, bool $isGateway): bool
    {
        // A crawler is never signed in, so nothing an authenticated user sees is
        // ever the indexable version of a URL. This is not only an SEO nit:
        // HomeController@index returns the *Dashboard* for a signed-in user at
        // "/" and "/{locale}", and "indexable" also selects the trimmed public
        // Ziggy group — so without this guard the dashboard rendered there with
        // 7 routes and its sidebar's route('booking.index') threw client-side.
        if (Auth::check()) {
            return false;
        }

        // The gateway only exists for clients that enable it.
        if ($isGateway) {
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
     * The site's own origin, from configuration — never from the request.
     *
     * `Host` is attacker-controlled (TrustHosts is disabled in app/Http/Kernel),
     * so deriving canonical / og:url / hreflang / JSON-LD from
     * getSchemeAndHttpHost() lets a spoofed header emit
     * `<link rel="canonical" href="http://evil.example.com/">`. Behind any
     * caching layer that is textbook cache-poisoning, and a canonical is exactly
     * the tag you least want an attacker to choose.
     *
     * APP_URL is set per deploy and is what these tags actually mean. The
     * request host is only a fallback for environments that never set it.
     *
     * Note config/app.php defaults `url` to the truthy string 'http://localhost',
     * so "APP_URL is unset" cannot be detected with a plain `?:` — trusting it
     * would silently point every canonical, hreflang and sitemap entry at
     * localhost in production, with nothing to notice: the deploy smoke test
     * curls vars.APP_URL directly rather than anything the page emits.
     *
     * The scheme matters too. Google treats http:// and https:// as different
     * URLs, so an http value on an https site produces canonicals pointing at a
     * URL that redirects. Both clients pin APP_URL to https://.
     */
    public static function baseUrl(Request $request): string
    {
        $configured = rtrim((string) config('app.url'), '/');

        if ($configured === '' || $configured === self::UNCONFIGURED_APP_URL) {
            return rtrim($request->getSchemeAndHttpHost(), '/');
        }

        return $configured;
    }

    /**
     * Canonical URL: origin + path, no query string.
     *
     * Query parameters on these pages are filters and tracking tags, never
     * distinct content, so folding them onto the bare path is correct and stops
     * ?utm_… variants competing with each other.
     */
    private static function canonical(Request $request): string
    {
        $base = self::baseUrl($request);
        $path = trim($request->path(), '/');

        return $path === '' ? $base.'/' : $base.'/'.$path;
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
    private static function image(array $seo, string $base): ?string
    {
        $image = $seo['og_image'] ?? null;

        if (! $image) {
            return null;
        }

        if (str_starts_with($image, 'http')) {
            return $image;
        }

        $relative = ltrim($image, '/');

        // Built from $base, not asset(): asset() resolves against the request
        // root, which carries the same Host-header problem as the canonical.
        return file_exists(public_path($relative)) ? $base.'/'.$relative : null;
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
        $routeName = optional($request->route())->getName();

        if (! self::isGateway($request, $routeName) || ! feature('demo_gateway')) {
            return null;
        }

        $seo  = config('client.seo', []);
        $name = $seo['site_name'] ?? config('app.name', 'RentCar');
        $url  = self::baseUrl($request);

        $organization = array_filter([
            '@type'       => 'Organization',
            '@id'         => $url.'/#organization',
            'name'        => $name,
            'url'         => $url,
            'logo'        => self::image($seo, $url),
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
