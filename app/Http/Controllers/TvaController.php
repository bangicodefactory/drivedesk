<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tva;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;


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
    public function bulkDownload(Request $request)
{
    $request->validate([
        'invoice_ids' => 'required|array',
    ]);

    $invoices = Tva::whereIn('id', $request->invoice_ids)->get();

    // Create temporary zip file
    $zipFileName = 'invoices_' . now()->format('Ymd_His') . '.zip';
    $zipPath = storage_path("app/public/{$zipFileName}");
    $zip = new ZipArchive;
    
    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        foreach ($invoices as $invoice) {
            $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
            $pdfContent = $pdf->output();
            $fileName = 'invoice_' . $invoice->facture_number . '.pdf';
            $zip->addFromString($fileName, $pdfContent);
        }
        $zip->close();
    }

    return response()->download($zipPath)->deleteFileAfterSend(true);
}
}
