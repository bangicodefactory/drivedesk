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
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                $result['totalOrganization'] = User::where('type', 'owner')->count();
                $result['totalSubscription'] = Subscription::count();
                $result['totalTransaction'] = PackageTransaction::count();
                $result['totalIncome'] = PackageTransaction::sum('amount');

                $result['organizationByMonth'] = $this->organizationByMonth();
                $result['paymentByMonth'] = $this->paymentByMonth();

                return view('dashboard.super_admin', compact('result'));
            } else {
                $result['totalUser'] = User::where('parent_id', parentId())->count();
                $result['totalDriver'] = User::where('type','driver')->where('parent_id', parentId())->count();
                $result['totalBooking'] = Booking::where('parent_id', parentId())->count();
                $result['totalIncome'] = Booking::where('parent_id', parentId())->sum('amount');
                $totalExpense=Expense::where('parent_id', parentId())->sum('amount');
                $result['totalExpense'] = $totalExpense;
                $result['incomeExpenseByMonth'] = $this->incomeExpenseByMonth();
                $result['settings']=settings();
                return view('dashboard.index', compact('result'));
            }
        } else {
            if (!file_exists(setup())) {
                header('location:install');
                die;
            } else {
                $landingPage=getSettingsValByName('landing_page');

                if($landingPage=='on'){
                    $subscriptions=Subscription::get();
                    return view('layouts.landing',compact('subscriptions'));
                }else{
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

            $totalExpense=Expense::where('parent_id', parentId())->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $payment['expense'][] = $totalExpense;
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;

    }

}

