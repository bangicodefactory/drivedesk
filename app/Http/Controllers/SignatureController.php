<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
// use Creagia\LaravelSignPad\Signature;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Signature;


class SignatureController extends Controller
{
    public function index(){
        if (\Auth::user()->can('manage driver')) {
            $drivers = User::where('parent_id', parentId())
            ->where('type', 'driver')
            ->with('drivers')  // Eager load the drivers relationship
            ->orderBy('created_at', 'desc')
            ->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('signature.index', compact('drivers'));
    }
    public function create(){    
        
        $users = User::where('id', parentId())->orderBy('created_at', 'desc')->get();
        

        $drivers = User::where('parent_id', parentId())
                   ->where('type', 'driver')
                   ->orderBy('created_at', 'desc')
                   ->get();
        $gender = User::$gender;

        return view('signature.create', compact( 'users', 'gender' , 'drivers'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'signature' => 'required'
        ]);

        // Get the base64 image data
        $signature = $request->input('signature');
        $image_parts = explode(";base64,", $signature);
        $image_base64 = base64_decode($image_parts[1]);

        // Generate unique filename
        $filename = 'signature_' . $request->user_id . '_' . time() . '.png';
        
        // Store the file
        Storage::disk('public')->put('signatures/' . $filename, $image_base64);

        // Create signature record
        Signature::create([
            'user_id' => $request->user_id,
            'signature_path' => 'signatures/' . $filename
        ]);

        return redirect()->route('signature.index')
            ->with('success', 'Signature saved successfully');
    }

}
