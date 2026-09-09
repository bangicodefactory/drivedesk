<?php

namespace App\Http\Controllers;

use App\Models\Tva;
use App\Services\TvaRenumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TvaRenumberController extends Controller
{
    protected TvaRenumberService $service;

    public function __construct(TvaRenumberService $service)
    {
        $this->service = $service;
    }

    /**
     * BAN-304: none of the three actions on this controller had a permission
     * check. The suite covering them said so out loud -- "any authenticated
     * user may call all three routes. Tests below document that behavior" --
     * which made a hole look like a decision. `apply()` rewrites the invoice
     * numbers on legally numbered documents, and `previewJson()` returns every
     * number and date for a year, so both are gated on `manage tva`: the same
     * permission the TVA screens this feature edits already require.
     *
     * The routes also carry `feature:tva_renumber` now. The flag existed and
     * was enforced nowhere; it is `true` in `_default` and in `drivedesk`, so
     * enforcing it changes nothing for either (CLAUDE.md 10.2.2).
     */
    public function index(Request $request)
    {
        if (! \Auth::user()->can('manage tva')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $selectedYear = (int) $request->query('year', now()->year);

        $preview = $this->service->preview($selectedYear);

        // Distinct years derived from facture_date only.
        // BAN-300: acrossTenants() to match TvaRenumberService, which is pinned
        // for the legacy-NULL reason. Scoped, a year whose invoices all predate
        // 2025-07-11 vanished from this dropdown — making the very rows the
        // service protects unreachable from the UI.
        $years = Tva::acrossTenants()->withoutTrashed()
            ->whereNotNull('facture_date')
            ->selectRaw('YEAR(facture_date) as y')
            ->groupBy('y')
            ->orderByDesc('y')
            ->pluck('y');

        return Inertia::render('Tva/Renumber', [
            'preview'      => $preview,
            'selectedYear' => $selectedYear,
            'years'        => $years->values(),
        ]);
    }

    public function apply(Request $request)
    {
        if (! \Auth::user()->can('manage tva')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $maxYear = now()->year + 1;
        $data = $request->validate([
            'year' => 'required|integer|min:2020|max:' . $maxYear,
        ]);

        try {
            $result = $this->service->renumber((int) $data['year']);

            return back()->with('success', __('Renumbered :n invoices for :y', [
                'n' => $result['updated'],
                'y' => $result['year'],
            ]));
        } catch (\Throwable $e) {
            Log::error('TVA renumber failed', [
                'year'  => $data['year'],
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('Renumbering failed: ') . $e->getMessage());
        }
    }

    public function previewJson(Request $request)
    {
        // JSON endpoint: a redirect would be read as a 302 to the dashboard by
        // the fetch() that calls it, so deny in the shape the caller expects.
        if (! \Auth::user()->can('manage tva')) {
            return response()->json(['message' => __('Permission Denied.')], 403);
        }

        $maxYear = now()->year + 1;
        $data = $request->validate([
            'year' => 'required|integer|min:2020|max:' . $maxYear,
        ]);

        return response()->json($this->service->preview((int) $data['year']));
    }
}
