<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Contact;
use App\Models\Custom;
use App\Models\Expense;
use App\Models\Fuel;
use App\Models\NoticeBoard;
use App\Models\PackageTransaction;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\Support;
use App\Models\User;
use App\Models\Place;
use App\Models\Reminder;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                $result['totalOrganization'] = User::where('type', 'owner')->count();

                if (feature('subscriptions')) {
                    $result['totalSubscription'] = Subscription::count();
                    $result['totalTransaction'] = PackageTransaction::count();
                    $result['totalIncome'] = PackageTransaction::sum('amount');
                    $result['paymentByMonth'] = $this->paymentByMonth();
                }

                $result['organizationByMonth'] = $this->organizationByMonth();

                if (config('app.inertia_enabled')) {
                    return Inertia::render('Dashboard', [
                        'stats' => [
                            'totalOrganization' => $result['totalOrganization'],
                            'totalSubscription' => $result['totalSubscription'] ?? null,
                            'totalTransaction'  => $result['totalTransaction']  ?? null,
                            'totalIncome'       => $result['totalIncome']       ?? null,
                        ],
                        'organizationByMonth' => $result['organizationByMonth'],
                        'paymentByMonth'      => $result['paymentByMonth'] ?? null,
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
                    $subscriptions = Subscription::get();
                    return view('layouts.landing', compact('subscriptions'));
                } else {
                    return redirect()->route('login');
                }
            }
        }
    }

    public function organizationByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $organization = [];
        while ($currentdate <= $end) {
            $organization['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $organization['data'][] = User::where('type', 'owner')->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
            $currentdate = strtotime('+1 month', $currentdate);
        }


        return $organization;
    }

    public function paymentByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['data'][] = PackageTransaction::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }


    public function incomeExpenseByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);
            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['income'][] = Booking::where('parent_id', parentId())->whereMonth('start_date', $month)->whereYear('start_date', $year)->sum('amount');

            $totalExpense = Expense::where('parent_id', parentId())->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $payment['expense'][] = $totalExpense;
            $currentdate = strtotime('+1 month', $currentdate);
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
