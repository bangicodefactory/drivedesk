<?php

namespace App\Http\Controllers;

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
        $base = $request->getSchemeAndHttpHost();
        $urls = [];

        if (feature('demo_gateway')) {
            $urls[] = ['loc' => $base.'/', 'priority' => '1.0', 'changefreq' => 'weekly'];
        }

        if (feature('public_storefront')) {
            $urls[] = ['loc' => $base.'/landing', 'priority' => '0.9', 'changefreq' => 'weekly'];
            $urls[] = ['loc' => $base.'/contact', 'priority' => '0.5', 'changefreq' => 'monthly'];
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
        $base = $request->getSchemeAndHttpHost();

        $body = "# {$name}\n\n";

        if (! empty($seo['description'])) {
            $body .= "> {$seo['description']}\n\n";
        }

        $body .= "{$name} is car rental management software for rental agencies. It covers "
            ."fleet management, bookings, rental contracts with in-app e-signature, invoicing "
            ."and VAT, expenses, and a visual planning board. It is multilingual and "
            ."white-label: each agency runs it on its own domain and branding.\n\n"
            ."## Pages\n\n"
            ."- [Home]({$base}/): product overview and demo request form\n\n"
            ."## Contact\n\n"
            ."Book a 20-minute demo from the home page.\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
