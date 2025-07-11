<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tva;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\BookingPayment;

class TvaController extends Controller
{
    //
    public function index()
    {
        // if (\Auth::user()->can('manage booking')) {
        //     $bookings = Booking::where('parent_id', '=', parentId())->orderBy('created_at', 'desc')->get();
        // } else {
        //     return redirect()->back()->with('error', __('Permission Denied.'));
        // }
        // return view('tva.index', compact('bookings'));
        if (\Auth::user()->can('manage booking')) {
            $tvas = Tva::where('parent_id', '=', parentId())->orderBy('created_at', 'desc')->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('tva.index', compact('tvas'));
    }
    public function create()
    {
        $books = Booking::where('parent_id', parentId())->get()->pluck('name', 'id');
        // $books->prepend(__('Select Vehicle'), '');


        return view('tva.create', compact('books'));
    }
}
