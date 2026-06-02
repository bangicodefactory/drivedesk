<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Custom;
use App\Models\Expense;
use App\Models\Fuel;
use App\Models\NoticeBoard;
use App\Models\Service;
use App\Models\Support;
use App\Models\User;
use App\Models\Place;
use App\Models\Reminder;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                $result['totalOrganization'] = User::where('type', 'owner')->count();

                $result['organizationByMonth'] = $this->organizationByMonth();

                if (config('app.inertia_enabled')) {
                    return Inertia::render('Dashboard', [
                        'stats' => [
                            'totalOrganization' => $result['totalOrganization'],
                        ],
                        'organizationByMonth' => $result['organizationByMonth'],
                    ]);
                }

                return view('dashboard.super_admin', compact('result'));
            } else {
                $result['totalUser'] = User::where('parent_id', parentId())->count();
                $result['totalDriver'] = User::where('type', 'driver')->where('parent_id', parentId())->count();
                $result['totalBooking'] = Booking::where('parent_id', parentId())->count();
                $result['totalIncome'] = Booking::where('parent_id', parentId())->sum('amount');
                $totalExpense = Expense::where('parent_id', parentId())->sum('amount');
                $result['totalExpense'] = $totalExpense;
                $result['incomeExpenseByMonth'] = $this->incomeExpenseByMonth();
                $result['settings'] = settings();

                if (\Auth::user()->can('manage reminder')) {
                    $reminders = Reminder::with('vehicles')  // Eager load vehicles
                        ->where('parent_id', '=', parentId())
                        ->orderBy('reminder_date', 'asc')
                        ->take(5)
                        ->get();
                } else {
                    $reminders = collect([]);
                }

                if (config('app.inertia_enabled')) {
                    return Inertia::render('Dashboard', [
                        'stats' => [
                            'totalUser'    => $result['totalUser'],
                            'totalDriver'  => $result['totalDriver'],
                            'totalBooking' => $result['totalBooking'],
                            'totalIncome'  => $result['totalIncome'],
                            'totalExpense' => $result['totalExpense'],
                        ],
                        'reminders'           => $reminders->map(fn ($r) => [
                            'id'            => $r->id,
                            'reminder_date' => optional($r->reminder_date)->toDateString(),
                            'note'          => $r->note,
                            'status'        => $r->status,
                            'vehicle'       => $r->vehicles ? [
                                'name'          => $r->vehicles->name,
                                'license_plate' => $r->vehicles->license_plate,
                            ] : null,
                        ])->values()->all(),
                        'incomeExpenseByMonth' => $result['incomeExpenseByMonth'],
                    ]);
                }

                return view('dashboard.index', compact('result', 'reminders'));
            }
        } else {
            if (!file_exists(setup())) {
                header('location:install');
                die;
            } else {
                $landingPage = getSettingsValByName('landing_page');

                if ($landingPage == 'on') {
                    if (config('app.inertia_enabled')) {
                        return Inertia::render('Public/Landing', $this->landingProps());
                    }
                    return view('layouts.landing');
                } else {
                    return redirect()->route('login');
                }
            }
        }
    }

    public function organizationByMonth(): array
    {
        $year = (int) date('Y');

        $counts = DB::table('users')
            ->selectRaw('MONTH(created_at) as mo, COUNT(*) as cnt')
            ->where('type', 'owner')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('cnt', 'mo');

        $organization = ['label' => [], 'data' => []];
        for ($m = 1; $m <= 12; $m++) {
            $organization['label'][] = date('M-Y', mktime(0, 0, 0, $m, 1, $year));
            $organization['data'][]  = (int) ($counts[$m] ?? 0);
        }

        return $organization;
    }

    public function incomeExpenseByMonth(): array
    {
        $year = (int) date('Y');
        $pid  = parentId();

        $income = DB::table('bookings')
            ->selectRaw('MONTH(start_date) as mo, SUM(amount) as total')
            ->where('parent_id', $pid)
            ->whereYear('start_date', $year)
            ->groupByRaw('MONTH(start_date)')
            ->pluck('total', 'mo');

        $expense = DB::table('expenses')
            ->selectRaw('MONTH(date) as mo, SUM(amount) as total')
            ->where('parent_id', $pid)
            ->whereYear('date', $year)
            ->groupByRaw('MONTH(date)')
            ->pluck('total', 'mo');

        $payment = ['label' => [], 'income' => [], 'expense' => []];
        for ($m = 1; $m <= 12; $m++) {
            $payment['label'][]   = date('M-Y', mktime(0, 0, 0, $m, 1, $year));
            $payment['income'][]  = (float) ($income[$m] ?? 0);
            $payment['expense'][] = (float) ($expense[$m] ?? 0);
        }

        return $payment;
    }

    public function landing()
    {
        if (config('app.inertia_enabled')) {
            return Inertia::render('Public/Landing', $this->landingProps());
        }
        return view('client.home');
    }

    private function landingProps(): array
    {
        $s = settings();

        $heroImages = [];
        foreach (['image_home_1', 'image_home_2'] as $key) {
            $path = 'upload/home/' . ($s[$key] ?? '');
            $heroImages[] = Storage::exists($path) ? Storage::url($path) : null;
        }

        return [
            'vehicles'     => Vehicle::select('id', 'name', 'model', 'daily_rate', 'number_of_seats', 'gearbox', 'fuel_type', 'picture')->get(),
            'vehicleTypes' => VehicleType::select('id', 'type')->get(),
            'places'       => Place::select('id', 'name')->get(),
            'heroImages'   => $heroImages,
        ];
    }
}
