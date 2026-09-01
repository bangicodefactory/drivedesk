<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\LoggedHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $query = Credit::query();
        if (function_exists('parentId') && parentId()) {
            $query->where('parent_id', parentId());
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->get('driver_id'));
        }
        if ($request->filled('driver_name')) {
            $name = $request->get('driver_name');
            $query->whereHas('driver', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        }

        $credits = $query->with('driver')->orderByDesc('created_at')->get();

        $drivers = User::where('parent_id', parentId())
            ->where('type', 'driver')
            ->orderBy('created_at', 'desc') // newest driver first (unified across pickers)
            ->orderBy('id', 'desc')         // tie-break: imported drivers share a created_at
            ->get();

        return Inertia::render('Credit/Index', [
            'credits' => $credits->map(fn($c) => [
                'id'          => $c->id,
                'driver_name' => $c->driver?->name,
                'amount'      => $c->amount,
                'status'      => $c->status,
                'credit_date' => $c->credit_date?->format('Y-m-d'),
            ]),
            'drivers' => $drivers->map(fn($d) => ['id' => $d->id, 'name' => $d->name]),
        ]);
    }

    /**
     * Show all credits for the driver of the given credit.
     */
    public function show(Credit $credit)
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (function_exists('parentId') && $credit->parent_id != parentId()) {
            return redirect()->route('credit.index')->with('error', __('Permission Denied.'));
        }

        $driver = $credit->driver;
        if (!$driver) {
            return redirect()->route('credit.index')->with('error', __('Driver not found.'));
        }

        $credits = Credit::where('driver_id', $driver->id)
            ->where('parent_id', $credit->parent_id)
            ->orderByDesc('credit_date')
            ->orderByDesc('created_at')
            ->get();

        // Chart 1: Credits by status (amount)
        $paidAmount = $credits->where('status', 'payé')->sum('amount');
        $unpaidAmount = $credits->where('status', 'non payé')->sum('amount');
        $chartStatus = [
            'labels' => [__('credit.status_paid'), __('credit.status_unpaid')],
            'amounts' => [(float) $paidAmount, (float) $unpaidAmount],
        ];

        // Chart 2: Credits amount by month (last 12 months)
        $byMonth = $credits->groupBy(function ($c) {
            $date = $c->credit_date ?? $c->created_at;
            return $date ? \Carbon\Carbon::parse($date)->format('Y-m') : null;
        })->map->sum('amount')->filter();

        $last12 = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $last12->put($key, (float) ($byMonth->get($key, 0)));
        }
        $chartByMonth = [
            'months' => $last12->keys()->map(function ($m) {
                return \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('M Y');
            })->values()->toArray(),
            'amounts' => $last12->values()->toArray(),
        ];

        return Inertia::render('Credit/Show', [
            'credit'       => ['id' => $credit->id, 'driver_id' => $credit->driver_id, 'amount' => $credit->amount, 'status' => $credit->status, 'credit_date' => $credit->credit_date?->format('Y-m-d')],
            'driver'       => $driver ? ['id' => $driver->id, 'name' => $driver->name] : null,
            'credits'      => $credits->map(fn($c) => ['id' => $c->id, 'amount' => $c->amount, 'status' => $c->status, 'credit_date' => $c->credit_date?->format('Y-m-d')]),
            'chartStatus'  => $chartStatus,
            'chartByMonth' => $chartByMonth,
        ]);
    }

    public function create()
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $drivers = User::where('parent_id', parentId())
            ->where('type', 'driver')
            ->orderBy('created_at', 'desc') // newest driver first (unified across pickers)
            ->orderBy('id', 'desc')         // tie-break: imported drivers share a created_at
            ->get();
        $statuses = Credit::$statuses;

        return Inertia::render('Credit/Create', [
            'drivers'  => $drivers->map(fn($d) => ['id' => $d->id, 'name' => $d->name]),
            'statuses' => $statuses,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $request->validate([
            // BAN-296: tenant-scoped. users has no global scope (it is the auth
            // provider), so this rule is what stops crediting another tenant's driver.
            'driver_id' => ['required', tenantExistsRule('users')],
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:non payé,payé',
            'credit_date' => 'nullable|date',
        ]);

        $credit = new Credit();
        $credit->driver_id = $request->driver_id;
        $credit->amount = $request->amount;
        $credit->status = $request->get('status', Credit::STATUS_NON_PAYE);
        $credit->credit_date = $request->filled('credit_date') ? $request->credit_date : now()->toDateString();
        $credit->parent_id = parentId() ?? 0;
        $credit->save();

        $this->logCreditAction('credit_create', $credit->id, __('Credit #:id created', ['id' => $credit->id]));

        if (!$request->hasHeader('X-Inertia') && ($request->ajax() || $request->wantsJson())) {
            return response()->json(['success' => true, 'message' => __('Credit created successfully.')]);
        }

        return redirect()->route('credit.index')->with('success', __('Credit created successfully.'));
    }

    public function edit(Credit $credit)
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (function_exists('parentId') && $credit->parent_id != parentId()) {
            return redirect()->route('credit.index')->with('error', __('Permission Denied.'));
        }

        $drivers = User::where('parent_id', parentId())
            ->where('type', 'driver')
            ->orderBy('created_at', 'desc') // newest driver first (unified across pickers)
            ->orderBy('id', 'desc')         // tie-break: imported drivers share a created_at
            ->get();
        $statuses = Credit::$statuses;

        return Inertia::render('Credit/Edit', [
            'credit'   => ['id' => $credit->id, 'driver_id' => $credit->driver_id, 'amount' => $credit->amount, 'status' => $credit->status, 'credit_date' => $credit->credit_date?->format('Y-m-d')],
            'drivers'  => $drivers->map(fn($d) => ['id' => $d->id, 'name' => $d->name]),
            'statuses' => $statuses,
        ]);
    }

    public function update(Request $request, Credit $credit)
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (function_exists('parentId') && $credit->parent_id != parentId()) {
            return redirect()->route('credit.index')->with('error', __('Permission Denied.'));
        }

        $request->validate([
            // BAN-296: tenant-scoped. users has no global scope (it is the auth
            // provider), so this rule is what stops crediting another tenant's driver.
            'driver_id' => ['required', tenantExistsRule('users')],
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:non payé,payé',
            'credit_date' => 'nullable|date',
        ]);

        $credit->driver_id = $request->driver_id;
        $credit->amount = $request->amount;
        $credit->status = $request->get('status', Credit::STATUS_NON_PAYE);
        $credit->credit_date = $request->filled('credit_date') ? $request->credit_date : $credit->credit_date;
        $credit->save();

        $this->logCreditAction('credit_update', $credit->id, __('Credit #:id updated', ['id' => $credit->id]));

        return redirect()->route('credit.index')->with('success', __('Credit updated successfully.'));
    }

    public function destroy(Credit $credit)
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if (function_exists('parentId') && $credit->parent_id != parentId()) {
            return redirect()->route('credit.index')->with('error', __('Permission Denied.'));
        }

        $id = $credit->id;
        $credit->delete();

        $this->logCreditAction('credit_delete', $id, __('Credit #:id deleted', ['id' => $id]));

        return redirect()->route('credit.index')->with('success', __('Credit deleted successfully.'));
    }

    /**
     * Search drivers by name (for modal autocomplete).
     */
    public function searchDrivers(Request $request)
    {
        $q = $request->get('q', '');
        $drivers = User::where('parent_id', parentId())
            ->where('type', 'driver')
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->orderBy('created_at', 'desc') // newest driver first (unified across pickers)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get(['id', 'name', 'email']);

        return response()->json($drivers);
    }

    /**
     * Store a log entry for credit actions (create, update, delete).
     */
    private function logCreditAction(string $type, int $creditId, string $message): void
    {
        $user = Auth::user();
        $details = json_encode([
            'action' => $type,
            'credit_id' => $creditId,
            'message' => $message,
            'user_id' => $user->id,
            'user_name' => $user->name,
        ]);

        LoggedHistory::create([
            'user_id' => $user->id,
            'ip' => request()->ip(),
            'date' => now(),
            'details' => $details,
            'type' => $type,
            'parent_id' => function_exists('parentId') ? (parentId() ?? 0) : 0,
        ]);
    }

    public function getDriverCredit($driver_id)
    {
        if (Auth::user()->can('manage rental agreement') || Auth::user()->can('manage driver') || Auth::user()->can('create rental agreement')) {
             $credits = Credit::where('driver_id', $driver_id)
                ->where('parent_id', parentId())
                ->get();

            $totalUnpaid = $credits->where('status', 'non payé')->sum('amount');
            $history = $credits->sortByDesc('created_at')->take(5)->map(function ($credit) {
                return [
                    'amount' => $credit->amount,
                    'status' => $credit->status,
                    'date' => $credit->credit_date ? $credit->credit_date->format('Y-m-d') : $credit->created_at->format('Y-m-d'),
                ];
            });

            return response()->json([
                'total_unpaid' => $totalUnpaid,
                'history' => $history
            ]);
        }
        return response()->json(['error' => 'Permission Denied'], 403);
    }
}
