<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tva;
use Illuminate\Http\Request;
use Carbon\Carbon;


class TvaController extends Controller
{
    //
    public function index()
    {
        $tvaFiles = Tva::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
            
        return view('tva.index', compact('tvaFiles'));
    }
    public function create()
    {
        $books = Booking::where('parent_id', parentId())->get()->pluck('name', 'id');
        // $books->prepend(__('Select Vehicle'), '');


        return view('tva.create', compact('books'));
    }
}
