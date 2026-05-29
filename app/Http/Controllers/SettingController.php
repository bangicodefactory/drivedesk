<?php

namespace App\Http\Controllers;

use App\Models\Custom;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SettingController extends Controller
{

    //    ---------------------- Account --------------------------------------------------------
    public function account()
    {
        $loginUser = \Auth::user();

        if (config('app.inertia_enabled')) {
            return Inertia::render('Settings/Account', [
                'loginUser' => [
                    'id'      => $loginUser->id,
                    'name'    => $loginUser->name,
                    'email'   => $loginUser->email,
                    'profile' => $loginUser->profile,
                ],
            ]);
        }

        return view('settings.account', compact('loginUser'));
    }

    public function accountData(Request $request)
    {
        $loginUser = \Auth::user();
        $user = User::find($loginUser->id);
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        if ($request->hasFile('profile')) {
            $profileWithExt = $request->file('profile')->getClientOriginalName();
            $profile = pathinfo($profileWithExt, PATHINFO_FILENAME);
            $extension = $request->file('profile')->getClientOriginalExtension();
            $profileToStore = $profile . '_' . time() . '.' . $extension;

            $directory = storage_path('uploads/profile/');
            $image_path = $directory . $loginUser->avatar;

            if (\File::exists($image_path)) {
                \File::delete($image_path);
            }

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            $request->file('profile')->storeAs('upload/profile/', $profileToStore);
        }

        if (!empty($request->profile)) {
            $user->profile = $profileToStore;
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();


        return redirect()->back()->with('success', 'Account settings successfully updated.');
    }

    public function accountDelete(Request $request)
    {
        $loginUser = \Auth::user();
        $loginUser->delete();

        return redirect()->back()->with('success', 'Your account successfully deleted.');
    }

    //    ---------------------- Password --------------------------------------------------------

    public function password()
    {
        $loginUser = \Auth::user();

        if (config('app.inertia_enabled')) {
            return Inertia::render('Settings/Password', [
                'loginUser' => [
                    'id'    => $loginUser->id,
                    'name'  => $loginUser->name,
                    'email' => $loginUser->email,
                ],
            ]);
        }

        return view('settings.password', compact('loginUser'));
    }

    public function passwordData(Request $request)
    {
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $loginUser = \Auth::user();
            $data = $request->All();

            $current_password = $loginUser->password;
            if (Hash::check($data['current_password'], $current_password)) {
                $user_id = $loginUser->id;
                $user = User::find($user_id);
                $user->password = Hash::make($data['new_password']);
                ;
                $user->save();

                return redirect()->back()->with('success', __('Password successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Please enter valid current password.'));
            }
        } else {
            return redirect()->back()->with('error', __('Invalid user.'));
        }
    }

    //    ---------------------- General --------------------------------------------------------

    public function general()
    {
        $loginUser = \Auth::user();

        return view('settings.general', compact('loginUser'));
    }

    public function generalData(Request $request)
    {
        if (\Auth::user()->type == 'super admin') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'application_name' => 'required',
                ]
            );

            if ($request->logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'logo' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->landing_logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'landing_logo' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->favicon) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'favicon' => 'required|mimes:png',
                    ]
                );
            }
            if ($request->image_home_1) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'image_home_1' => 'required|mimes:png',
                    ]
                );
            }
            if ($request->image_home_2) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'image_home_2' => 'required|mimes:png',
                    ]
                );
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->application_name)) {
                $array = [
                    'APP_NAME' => $request->application_name,
                ];
                Custom::setCommon($array);
            }

            if ($request->logo) {
                $superadminLogoName = 'logo.png';
                $request->file('logo')->storeAs('upload/logo/', $superadminLogoName);
            }

            if ($request->landing_logo) {
                $superadminLandLogoName = 'landing_logo.png';
                $request->file('landing_logo')->storeAs('upload/logo/', $superadminLandLogoName);
            }

            if ($request->favicon) {
                $superadminFavicon = 'favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $superadminFavicon);
            }
            if ($request->favicon) {
                $superadminFavicon = 'favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $superadminFavicon);
            }
            if ($request->favicon) {
                $superadminFavicon = 'favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $superadminFavicon);
            }

            if ($request->image_home_1) {
                $request->file('image_home_1')->storeAs('upload/home/', 'image_home_1.png');
            }

            if ($request->image_home_2) {
                $request->file('image_home_2')->storeAs('upload/home/', 'image_home_2.png');
            }

        } elseif (\Auth::user()->type == 'owner') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'application_name' => 'required',
                ]
            );

            if ($request->logo) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'logo' => 'required|mimes:png',
                    ]
                );
            }

            if ($request->favicon) {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'favicon' => 'required|mimes:png',
                    ]
                );
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->application_name)) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $request->application_name,
                        'app_name',
                        parentId(),
                    ]
                );
            }

            if ($request->logo) {
                $ownerLogoName = parentId() . '_logo.png';
                $request->file('logo')->storeAs('upload/logo/', $ownerLogoName);

                \DB::insert(
                    'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $ownerLogoName,
                        'company_logo',
                        parentId(),
                    ]
                );
            }

            if ($request->favicon) {
                $ownerFaviconName = parentId() . '_favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $ownerFaviconName);

                \DB::insert(
                    'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $ownerFaviconName,
                        'company_favicon',
                        parentId(),
                    ]
                );
            }

            if ($request->image_home_1) {
                $fileName = parentId() . '_image_home_1.png';
                $request->file('image_home_1')->storeAs('upload/home/', $fileName);
                \DB::insert(
                    'insert into settings (`value`, `name`, `parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [$fileName, 'image_home_1', parentId()]
                );
            }

            if ($request->image_home_2) {
                $fileName = parentId() . '_image_home_2.png';
                $request->file('image_home_2')->storeAs('upload/home/', $fileName);
                \DB::insert(
                    'insert into settings (`value`, `name`, `parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                    [$fileName, 'image_home_2', parentId()]
                );
            }

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        return redirect()->back()->with('success', __('General setting successfully saved.'));
    }
    //    ---------------------- SMTP --------------------------------------------------------

    public function smtp()
    {
        $loginUser = \Auth::user();

        return view('settings.smtp', compact('loginUser'));
    }

    public function smtpData(Request $request)
    {
        if (\Auth::Check()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'sender_name' => 'required',
                    'sender_email' => 'required',
                    'server_driver' => 'required',
                    'server_host' => 'required',
                    'server_port' => 'required',
                    'server_username' => 'required',
                    'server_password' => 'required',
                    'server_encryption' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $smtpArray = [
                'FROM_NAME' => $request->sender_name,
                'FROM_EMAIL' => $request->sender_email,
                'SERVER_DRIVER' => $request->server_driver,
                'SERVER_HOST' => $request->server_host,
                'SERVER_PORT' => $request->server_port,
                'SERVER_USERNAME' => $request->server_username,
                'SERVER_PASSWORD' => $request->server_password,
                'SERVER_ENCRYPTION' => $request->server_encryption,
            ];
            foreach ($smtpArray as $key => $val) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'smtp',
                        parentId(),
                    ]
                );
            }

            return redirect()->back()->with('success', __('SMTP settings successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Invalid user.'));
        }
    }


    public function smtpTest(Request $request)
    {
        return view('settings.testmail');
    }



    public function smtpTestMailSend(Request $request)
    {
        if (\Auth::check()) {
            $to = $request->email;
            $errorMessage = '';
            // Data for email
            $data = [
                'module' => 'test_mail',
                'subject' => 'Test Mail',
                'message' => __('This is a test mail.'),
            ];

            // Send email
            $response = sendEmail($to, $data);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
                return redirect()->back()->with('error', $errorMessage);
            } else {
                $errorMessage = $response['message'];
                return redirect()->back()->with('success', $errorMessage);
            }
        }

        return redirect()->back()->with('error', __('Invalid user.'));
    }

    //    ---------------------- Payment --------------------------------------------------------

    public function payment()
    {
        $loginUser = \Auth::user();

        return view('settings.payment', compact('loginUser'));
    }

    public function paymentData(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'CURRENCY' => 'required',
                'CURRENCY_SYMBOL' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $currencyArray = [
            'CURRENCY' => $request->CURRENCY,
            'CURRENCY_SYMBOL' => $request->CURRENCY_SYMBOL,
            'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
            'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
            'paypal_payment' => $request->paypal_payment ?? 'off',
        ];
        foreach ($currencyArray as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    'payment',
                    parentId(),
                ]
            );
        }

        //        For Bank Transfer Settings
        if (isset($request->bank_transfer_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'bank_name' => 'required',
                    'bank_holder_name' => 'required',
                    'bank_account_number' => 'required',
                    'bank_ifsc_code' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $bankArray = [
                'bank_transfer_payment' => $request->bank_transfer_payment ?? 'off',
                'bank_name' => $request->bank_name,
                'bank_holder_name' => $request->bank_holder_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_ifsc_code' => $request->bank_ifsc_code,
                'bank_other_details' => !empty($request->bank_other_details) ? $request->bank_other_details : '',
            ];

            foreach ($bankArray as $key => $val) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }

        //        For Strip Settings
        if (isset($request->stripe_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'stripe_key' => 'required',
                    'stripe_secret' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $stripeArray = [
                'STRIPE_PAYMENT' => $request->stripe_payment ?? 'off',
                'STRIPE_KEY' => $request->stripe_key,
                'STRIPE_SECRET' => $request->stripe_secret,
            ];

            foreach ($stripeArray as $key => $val) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }


        //        For Paypal Settings

        if (isset($request->paypal_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $paypalArray = [
                'paypal_payment' => $request->paypal_payment ?? 'off',
                'paypal_mode' => $request->paypal_mode,
                'paypal_client_id' => $request->paypal_client_id,
                'paypal_secret_key' => $request->paypal_secret_key,
            ];

            foreach ($paypalArray as $key => $val) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }

        //  For Flutterwave Settings

        if (isset($request->flutterwave_payment)) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'flutterwave_public_key' => 'required',
                    'flutterwave_secret_key' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $flutterwaveArray = [
                'flutterwave_payment' => $request->flutterwave_payment ?? 'off',
                'flutterwave_public_key' => $request->flutterwave_public_key,
                'flutterwave_secret_key' => $request->flutterwave_secret_key,
            ];

            foreach ($flutterwaveArray as $key => $val) {
                \DB::insert(
                    'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'payment',
                        parentId(),
                    ]
                );
            }
        }


        return redirect()->back()->with('success', __('Payment successfully saved.'));
    }

    //    ---------------------- Company  --------------------------------------------------------

    public function company()
    {
        $settings = settings();
        $timezones = config('timezones');

        return view('settings.company', compact('settings', 'timezones'));
    }

    public function companyData(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'company_name' => 'required',
                'company_email' => 'required',
                'company_phone' => 'required',
                'company_address' => 'required',
                'patente' => 'nullable',  // Add new validation rules
                'rc' => 'nullable',
                'if' => 'nullable',
                'ice' => 'nullable',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);

        foreach ($settings as $key => $val) {
            $value = $val ?? '';
            \DB::insert(
                'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $value,
                    $key,
                    parentId(),
                ]
            );
        }


        return redirect()->back()->with('success', __('Company setting successfully saved.'));
    }

    //    ---------------------- Language --------------------------------------------------------

    public function languageChange($lang)
    {
        // Debug: Log that the method is being called
        \Log::info('Language change called with: ' . $lang);
        \Log::info('User authenticated: ' . (\Auth::check() ? 'Yes' : 'No'));

        if (\Auth::check()) {
            $user = \Auth::user();
            $user->lang = $lang;
            $user->save();
            session(['locale' => $lang]);
            \Log::info('Language saved for user: ' . $user->id);
        } else {
            session(['locale' => $lang]);
            \Log::info('Language saved in session for guest');
        }

        app()->setLocale($lang);
        \Log::info('App locale set to: ' . app()->getLocale());

        return redirect()->back()->with('success', __('Language successfully changed.'));
    }


    public function themeSettings(Request $request)
    {

        $themeSettings = $request->all();
        unset($themeSettings['_token']);
        if (\Auth::user()->type == 'super admin') {
            if (isset($request->landing_page)) {
                $themeSettings['landing_page'] = $request->landing_page;
            } else {
                $themeSettings['landing_page'] = 'off';
            }

            if (isset($request->register_page)) {
                $themeSettings['register_page'] = $request->register_page;
            } else {
                $themeSettings['register_page'] = 'off';
            }

            if (isset($request->owner_email_verification)) {
                $themeSettings['owner_email_verification'] = $request->owner_email_verification;
            } else {
                $themeSettings['owner_email_verification'] = 'off';
            }
        }
        foreach ($themeSettings as $key => $val) {
            if (!empty($val)) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $val,
                        $key,
                        'common',
                        parentId(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Theme settings save successfully.'));
    }

    //    ---------------------- SEO Settings --------------------------------------------------------

    public function siteSEO()
    {
        $settings = settings();
        return view('settings.site_seo', compact('settings'));
    }

    public function siteSEOData(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'meta_seo_title' => 'required',
                'meta_seo_keyword' => 'required',
                'meta_seo_description' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);
        if ($request->meta_seo_image) {
            $seoFilenameWithExt = $request->file('meta_seo_image')->getClientOriginalName();
            $seoFilename = pathinfo($seoFilenameWithExt, PATHINFO_FILENAME);
            $supportExtension = $request->file('meta_seo_image')->getClientOriginalExtension();
            $seoFileName = $seoFilename . '_' . time() . '.' . $supportExtension;


            $request->file('meta_seo_image')->storeAs('upload/seo/', $seoFileName);


            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $seoFileName,
                    'meta_seo_image',
                    'SEO',
                    parentId(),
                ]
            );
        }
        unset($settings['meta_seo_image']);
        foreach ($settings as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    'SEO',
                    parentId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Site SEO settings save successfully.'));
    }

    //    ---------------------- Google ReCaptcha Settings --------------------------------------------------------

    public function googleRecaptcha()
    {
        $settings = settings();
        return view('settings.recaptcha', compact('settings'));
    }

    public function googleRecaptchaData(Request $request)
    {

        $validator = \Validator::make(
            $request->all(),
            [
                'recaptcha_key' => 'required',
                'recaptcha_secret' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $settings = $request->all();
        unset($settings['_token']);

        $recaptchaArray = [
            'google_recaptcha' => $request->google_recaptcha ?? 'off',
            'recaptcha_key' => $request->recaptcha_key,
            'recaptcha_secret' => $request->recaptcha_secret,
        ];

        foreach ($recaptchaArray as $key => $val) {
            \DB::insert(
                'insert into settings (`value`, `name`, `type`,`parent_id`) values (?, ?, ?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $val,
                    $key,
                    'recaptcha',
                    parentId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Google Recaptcha settings save successfully.'));
    }
    //=============Store Admin Signature========================
    public function storeSignature(Request $request)
{
    $request->validate([
        'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
    ]);

    $filename = 'signature_' . auth()->id() . '_' . time() . '.png';

    $path = $request->file('signature')->storeAs(
        'upload/signature-admin',
        $filename,
        'public'
    );

    Setting::updateOrCreate(
        ['name' => 'admin_signature', 'parent_id' => 2],
        [
            'signature_path' => $path,
            'value' => $path
        ]
    );

    return response()->json([
        'success' => true,
        'message' => 'Signature uploaded successfully.',
        'path' => asset('storage/' . $path)
    ]);
}

    // public function getSignature()
    // {
    //     $setting = Setting::where('name', 'admin_signature')->first();

    //     if (!$setting || !$setting->signature_path || !Storage::disk('public')->exists($setting->signature_path)) {
    //         return response()->json(['message' => 'Signature not found.'], 404);
    //     }

    //     // Return the full URL to the file
    //     return response()->json([
    //         'signature_url' => Storage::url($setting->signature_path),
    //         'signature_path' => $setting->signature_path
    //     ]);
    // }


    // public function updateSignature(Request $request)
    // {
    //     Log::info('Starting updateSignature process.');

    //     $request->validate([
    //         'signature' => 'required|image|mimes:png,jpg,jpeg|max:2048',
    //     ]);
    //     Log::info('Validation passed.');

    //     if (!$request->hasFile('signature') || !$request->file('signature')->isValid()) {
    //         Log::error('Invalid file upload.');
    //         return redirect()->back()->with('error', 'Invalid file upload.');
    //     }
    //     Log::info('File upload is valid.');

    //     $setting = Setting::where('name', 'admin_signature')->first();
    //     Log::info('Setting retrieved.', ['setting' => $setting ? $setting->toArray() : null]);

    //     if ($setting && $setting->value && Storage::disk('public')->exists($setting->value)) {
    //         Storage::disk('public')->delete($setting->value);
    //         Log::info('Old signature deleted.', ['old_path' => $setting->value]);
    //     } else {
    //         Log::info('No old signature to delete.');
    //     }

    //     $path = $request->file('signature')->store('signatures', 'public');
    //     Log::info('New signature stored.', ['path' => $path]);

    //     if (!$setting) {
    //         $setting = new Setting();
    //         $setting->name = 'admin_signature';
    //         Log::info('New setting instance created.');
    //     }

    //     $setting->value = $path;
    //     $setting->save();
    //     Log::info('Setting saved.', ['setting' => $setting->toArray()]);

    //     return redirect()->back()->with('success', 'Signature updated successfully.');
    // }



    // public function deleteSignature()
    // {
    //     $setting = Setting::where('name', 'admin_signature')->first();

    //     if (!$setting) {
    //         return redirect()->back()->with('error', 'No signature found to delete.');
    //     }

    //     if ($setting->value && Storage::disk('public')->exists($setting->value)) {
    //         Storage::disk('public')->delete($setting->value);
    //     }

    //     $setting->update([
    //         'value' => null,
    //     ]);

    //     return redirect()->back()->with('success', 'Signature deleted successfully.');
    // }

}
