<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Tva;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Driver;

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
                            Log::info('Logo loaded successfully: ' . $logoPath . ' - Size: ' . strlen($imageData) . ' bytes');
                        }
                    } catch (Exception $e) {
                        // Log the error for debugging
                        Log::error('Logo loading error: ' . $e->getMessage() . ' for path: ' . $logoPath);
                        $logoBase64 = null;
                    }
                } else {
                    Log::info('No logo found. Searched paths: ' . implode(', ', $possiblePaths));
                }

                // Debug: Always log what we're passing to the template
                Log::info('LogoBase64 status: ' . ($logoBase64 ? 'Generated successfully' : 'NULL'));
                $ttcInWords = $this->numberToFrenchWords(floor($invoice->montant_ttc)) . ' dirhams';
                if (fmod($invoice->montant_ttc, 1) > 0) {
                    $ttcInWords .= ' et ' . round(fmod($invoice->montant_ttc, 1) * 100) . ' centimes';
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
                    'logoPath' => $logoBase64,
                    'ttcInWords' => $ttcInWords
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
            'facture_date' => 'required|date',
            'montant_ttc' => 'required|numeric',
            'unit_price_ht' => 'required|numeric',
            'tva' => 'required|numeric',
            'facture_number' => 'required|string|max:255',
        ]);

        $tva = Tva::findOrFail($id);

        $tva->facture_date = $validated['facture_date'];
        $tva->montant_ttc = $validated['montant_ttc'];
        $tva->unit_price_ht = $validated['unit_price_ht'];
        $tva->tva = $validated['tva'];
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
    protected function numberToFrenchWords($num)
    {
        if (!is_numeric($num)) {
            throw new \Exception('Le nombre doit être numérique');
        }

        $num = (float) $num;
        if ($num < 0 || $num > 999999999999) {
            throw new \Exception('Le nombre doit être entre 0 et 999 999 999 999');
        }

        if ($num === 0) {
            return 'Zéro';
        }

        $units = [
            '',
            'un',
            'deux',
            'trois',
            'quatre',
            'cinq',
            'six',
            'sept',
            'huit',
            'neuf',
            'dix',
            'onze',
            'douze',
            'treize',
            'quatorze',
            'quinze',
            'seize',
            'dix-sept',
            'dix-huit',
            'dix-neuf'
        ];

        $tens = [
            '',
            '',
            'vingt',
            'trente',
            'quarante',
            'cinquante',
            'soixante',
            'soixante',
            'quatre-vingt',
            'quatre-vingt'
        ];

        $convertUnder100 = function ($n) use ($units, $tens) {
            if ($n < 20) {
                return ucfirst($units[$n]);
            }

            $ten = floor($n / 10);
            $unit = $n % 10;

            if ($ten === 7) {
                return $unit === 1 ? 'Soixante-et-onze' : 'Soixante-' . ($unit === 0 ? 'dix' : $units[10 + $unit]);
            }
            if ($ten === 8) {
                return $unit === 0 ? 'Quatre-vingts' : 'Quatre-vingt-' . $units[$unit];
            }
            if ($ten === 9) {
                return $unit === 0 ? 'Quatre-vingt-dix' : 'Quatre-vingt-' . $units[10 + $unit];
            }

            return ucfirst($tens[$ten]) . ($unit === 0 ? '' :
                ($unit === 1 && $ten !== 8 && $ten !== 9 ? '-et-un' : '-' . $units[$unit]));
        };

        $convertUnder1000 = function ($n) use ($convertUnder100, $units) {
            if ($n === 0) {
                return '';
            }

            $hundreds = floor($n / 100);
            $remainder = $n % 100;
            $result = '';

            if ($hundreds > 0) {
                $result = $hundreds === 1 ? 'Cent' : ucfirst($units[$hundreds]) . ' cent';
                if ($remainder === 0 && $hundreds > 1) {
                    $result .= 's';
                }
            }

            if ($remainder > 0) {
                $result .= $result ? ' ' : '';
                $result .= $convertUnder100($remainder);
            }

            return $result;
        };

        $billions = floor($num / 1000000000);
        $millions = floor(($num % 1000000000) / 1000000);
        $thousands = floor(($num % 1000000) / 1000);
        $remainder = $num % 1000;

        $result = '';

        if ($billions > 0) {
            $result .= $convertUnder1000($billions) . ($billions === 1 ? ' milliard' : ' milliards');
        }

        if ($millions > 0) {
            $result .= $result ? ' ' : '';
            $result .= $convertUnder1000($millions) . ($millions === 1 ? ' million' : ' millions');
        }

        if ($thousands > 0) {
            $result .= $result ? ' ' : '';
            $result .= $thousands === 1 ? 'mille' : $convertUnder1000($thousands) . ' mille';
        }

        if ($remainder > 0) {
            $result .= $result ? ' ' : '';
            $result .= $convertUnder1000($remainder);
        }

        // Capitalize first letter of the entire result
        return ucfirst(strtolower($result));
    }

public function generateMonthlyTva(Request $request)
{
    $request->validate([
        'month' => 'required|date_format:Y-m',
    ]);

    $monthStart = Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
    $monthEnd = $monthStart->copy()->endOfMonth();

    $parentId = parentId();
    if (!$parentId) {
        return redirect()->back()->with('error', 'Parent ID not found. Please check your authentication.');
    }
    // dd(parentId());

    $bookings = Booking::with(['drivers', 'vehicles'])
        ->whereBetween('start_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
        ->get();

    $createdCount = 0;
    $setting = settings();

    //  last facture number for this user(parent id)
    $lastFacture = Tva::where('parent_id', $parentId)
                      ->orderByDesc('id')
                      ->first();
    $lastNumber = 0;
    if ($lastFacture && preg_match('/\d+$/', $lastFacture->facture_number, $matches)) {
        $lastNumber = (int) $matches[0];
    }
    // logic for facture number
    $factureCounter = $lastNumber;

    foreach ($bookings as $booking) {
        if (!$booking->id) {
            continue;
        }

        $exists = Tva::where('booking_id', $booking->booking_id)
                     ->where('month', $monthStart->month)
                     ->where('year', $monthStart->year)
                     ->exists();

        if ($exists) continue;

        $factureCounter++;
        $factureNumber = $factureCounter;

        $driverName = 'N/A';
        $driverAddress = '';
        if ($booking->drivers) {
            $driverName = $booking->drivers->name ?? 'N/A';
            $driver = Driver::where('user_id', $booking->driver)->first();
            $driverAddress = $driver->address ?? '';
        }

        $vehicleDetails = json_decode($booking->vehicle_details, true);
        $vehicleName = $vehicleDetails['name'] ?? '';
        $vehicleLicensePlate = $vehicleDetails['license_plate'] ?? '';

        // Quantity
        $startDate = Carbon::parse($booking->start_date);
        $endDate = Carbon::parse($booking->end_date);
        $totalDays = $startDate->diffInDays($endDate) ?: 1;

        //  amounts : HT TVA TTC
        $totalHt = round($booking->amount * 0.8, 2);
        $tvaAmount = round($booking->amount * 0.2, 2);
        $unitPriceHt = $totalDays > 0 ? round($totalHt / $totalDays, 2) : 0;

        $tva = new Tva();

        $tva->booking_id = $booking->booking_id;
        $tva->parent_id = $parentId;
        $tva->month = $monthStart->month;
        $tva->year = $monthStart->year;
        $tva->facture_number = $factureNumber;
        $tva->facture_date = $booking->created_at ?? now();
        $tva->client_name = $driverName;
        $tva->client_address = $driverAddress;
        $tva->company_name = $setting['company_name'];
        $tva->company_address = $setting['company_address'];
        $tva->designation = $vehicleName . ' - ' . $vehicleLicensePlate;
        $tva->quantity = $totalDays;
        $tva->unit_price_ht = $unitPriceHt;
        $tva->total_ht = $totalHt;
        $tva->tva = $tvaAmount;
        $tva->montant_ttc = $booking->amount;
        $tva->ice_number = $setting['ice'];
        $tva->rc_number = $setting['rc'];
        $tva->nif_number = $setting['if'];
        $tva->generated_date = now();
        $tva->total_amount = $booking->amount;
        $tva->tva_amount = $tvaAmount;

        $tva->save();

        $createdCount++;
    }

    return redirect()->back()->with('success', "$createdCount TVA(s) généré pour {$monthStart->format('F Y')}");
}


}
