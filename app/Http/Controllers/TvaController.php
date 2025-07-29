<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tva;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
<<<<<<< HEAD
use Exception;
use Illuminate\Support\Facades\Log;
=======
>>>>>>> 9f8a5d90fb1a4ca6a01a63259c17911a8b9ccd1e


use App\Models\BookingPayment;

class TvaController extends Controller
{
    //
    public function index(Request $request)
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
        $query = Tva::where('parent_id', '=', parentId());
        if ($request->filled('filter_day')) {
            $query->whereDate('created_at', $request->filter_day);
        }

        // Filter by month
        if ($request->filled('filter_month')) {
            $query->whereMonth('created_at', $request->filter_month);
        }

        // Filter by year
        if ($request->filled('filter_year')) {
            $query->whereYear('created_at', $request->filter_year);
        }
        $perPage = $request->get('per_page', 30);
        $tvas = $query->paginate($perPage);
        
        $tvas->appends([
            'filter_day' => $request->filter_day,
            'filter_month' => $request->filter_month,
            'filter_year' => $request->filter_year,
            'per_page' => $perPage
        ]);


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
        $zipFileName = 'invoices_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path("app/public/{$zipFileName}");
        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($invoices as $invoice) {
                $items = [
                    (object) [
                        'description' => $invoice->designation,
                        'quantity' => $invoice->quantity,
                        'unit_price' => $invoice->unit_price_ht,
                        'total_ht' => $invoice->total_ht,
                    ]
                ];
                $invoice->items = $items;

                $settings = settings();
<<<<<<< HEAD
                $logoFile = $settings['company_logo'] ?? '2_logo.png'; // Updated default logo name

                // Try multiple possible logo paths
                $possiblePaths = [
                    storage_path('upload/logo/' . $logoFile),
                    storage_path('app/upload/logo/' . $logoFile),
                    public_path('storage/logo/' . $logoFile),
                    public_path('upload/logo/' . $logoFile)
                ];

                $logoPath = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && is_readable($path)) {
                        $logoPath = $path;
                        break;
                    }
                }

                // Convert to base64 for DomPDF compatibility if logo exists
                $logoBase64 = null;
                if ($logoPath && file_exists($logoPath)) {
                    try {
                        $imageData = file_get_contents($logoPath);
                        $imageInfo = getimagesize($logoPath);
                        if ($imageData && $imageInfo) {
                            $mimeType = $imageInfo['mime'];
                            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        }
                    } catch (Exception $e) {
                        // Log the error for debugging
                        Log::error('Logo loading error: ' . $e->getMessage() . ' for path: ' . $logoPath);
                        $logoBase64 = null;
                    }
                } else {
                    Log::info('No logo found. Searched paths: ' . implode(', ', $possiblePaths));
                }

                // $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
                //     'tva' => $invoice,
                //     'settings' => $settings,
                //     'logoPath' => $logoPath,
                // ]);
                // $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice2', [
                //     'tva' => $invoice,
                //     'settings' => $settings,
                //     'logoPath' => $logoPath,
                // ]);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice1', [
                    'tva' => $invoice,
                    'settings' => $settings,
                    'logoPath' => $logoBase64, // Use base64 instead of file path
=======
                $logoFile = $settings['company_logo'] ?? '2_logo.png'; // we dont have logo in settings db

                $logoPath = storage_path('upload/logo/' . $logoFile);

                if (!file_exists($logoPath)) {
                $logoPath = null; 
                }

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
                    'tva' => $invoice,
                    'settings' => $settings,
                    'logoPath' => $logoPath,
>>>>>>> 9f8a5d90fb1a4ca6a01a63259c17911a8b9ccd1e
                ]);
                $pdfContent = $pdf->output();
                $fileName = 'invoice_' . $invoice->facture_number . '.pdf';
                $zip->addFromString($fileName, $pdfContent);
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
    public function edit($id)
    {
        $tva = Tva::findOrFail($id);
        $books = Booking::where('parent_id', parentId())->pluck('booking_id', 'id'); // id => booking_id

        $vehicles = Vehicle::all(); // or however you're getting the vehicle list

        $booking = Booking::find($tva->booking_id); // to get the selected booking

        return view('tva.edit', compact('tva', 'books', 'vehicles', 'booking'));
    }


    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'facture_date'   => 'required|date',
        'montant_ttc'    => 'required|numeric',
        'unit_price_ht'  => 'required|numeric',
        'tva'            => 'required|numeric',
        'facture_number' => 'required|string|max:255',
    ]);

        $tva = Tva::findOrFail($id);

    $tva->facture_date   = $validated['facture_date'];
    $tva->montant_ttc    = $validated['montant_ttc'];
    $tva->unit_price_ht  = $validated['unit_price_ht'];
    $tva->tva            = $validated['tva'];
    $tva->facture_number = $validated['facture_number'];
    $tva->total_ht = $request->total_ht;


        $tva->save();

        return redirect()->route('tva.index')->with('success', __('TVA updated successfully.'));
    }


    public function show($id)
    {
        $tva = Tva::findOrFail($id);
        return view('tva.show', compact('tva'));
    }
    public function destroy($id)
    {
        $tva = Tva::findOrFail($id);
        $tva->delete();
        return redirect()->back()->with('success', 'The TVA has been deleted.');
    }


}