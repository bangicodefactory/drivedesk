<?php

namespace App\Http\Controllers;

use App\Models\Credit;
use App\Models\LoggedHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->orderBy('name')
            ->get();

        return view('credit.index', compact('credits', 'drivers'));
    }

    public function create()
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $drivers = User::where('parent_id', parentId())
            ->where('type', 'driver')
            ->orderBy('name')
            ->get();
        $statuses = Credit::$statuses;

        return view('credit.create', compact('drivers', 'statuses'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('manage driver')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $request->validate([
            'driver_id' => 'required|exists:users,id',
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

        if ($request->ajax() || $request->wantsJson()) {
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
            ->orderBy('name')
            ->get();
        $statuses = Credit::$statuses;

        return view('credit.edit', compact('credit', 'drivers', 'statuses'));
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
            'driver_id' => 'required|exists:users,id',
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
            ->orderBy('name')
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
}
