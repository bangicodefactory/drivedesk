<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\Request;

/**
 * sitemap.xml and llms.txt (BAN-262).
 *
 * Both are generated rather than static files because which pages exist depends
 * on the client's feature flags — DriveDesk has no B2C storefront, so listing
 * /landing there would point crawlers at a 404.
 */
class SeoController extends Controller
{
    /** XML sitemap covering only pages this client actually serves and indexes. */
    public function sitemap(Request $request)
    {
        // Configured origin, never the request Host — see Seo::baseUrl().
        $base = Seo::baseUrl($request);
        $urls = [];

        if (feature('demo_gateway')) {
            $urls[] = ['loc' => $base.'/', 'priority' => '1.0', 'changefreq' => 'weekly'];

            // Each locale has its own indexable URL (BAN-263); listing them is
            // how the alternates get discovered without waiting for a crawl of
            // the x-default page.
            foreach (\App\Support\Locales::forPublicUrls() as $locale) {
                $urls[] = ['loc' => $base.'/'.$locale, 'priority' => '0.9', 'changefreq' => 'weekly'];
            }
        }

        if (feature('public_storefront')) {
            $urls[] = ['loc' => $base.'/landing', 'priority' => '0.9', 'changefreq' => 'weekly'];
            // /contact and /search are intentionally absent: they render
            // client.layouts.app rather than the Inertia shell, so they carry no
            // canonical or robots directive, and /contact was returning 500 on
            // at least one client. Listing a page we do not control the SEO of —
            // and have not verified renders — is how you get 500s into a sitemap.
        }

        // A sitemap listing nothing is worse than none — it tells Google the
        // site has no indexable pages. Clients with no public surface (the
        // internal-only tenants) get a 404 instead.
        if ($urls === []) {
            abort(404);
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /**
     * llms.txt — a plain-text summary for AI crawlers.
     *
     * Worth more here than on a typical site: with SSR off, an assistant that
     * does not execute JavaScript sees an empty document, so this is the only
     * prose about the product it can read.
     */
    public function llms(Request $request)
    {
        if (! feature('demo_gateway')) {
            abort(404);
        }

        $seo  = config('client.seo', []);
        $name = $seo['site_name'] ?? config('app.name');
        $base = Seo::baseUrl($request);

        $body = "# {$name}\n\n";

        if (! empty($seo['description'])) {
            $body .= "> {$seo['description']}\n\n";
        }

        // Client-specific prose lives with the rest of the client's SEO copy, not
        // hardcoded in shared controller code.
        if (! empty($seo['llms_summary'])) {
            $body .= $seo['llms_summary']."\n\n";
        }

        $body .= "## Pages\n\n"
            ."- [Home]({$base}/): product overview and demo request form\n";

        foreach (\App\Support\Locales::forPublicUrls() as $locale) {
            $body .= "- [Home ({$locale})]({$base}/{$locale})\n";
        }

        $body .= "\n## Contact\n\nBook a demo from the home page.\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
