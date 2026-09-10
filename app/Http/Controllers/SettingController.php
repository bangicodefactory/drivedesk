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

        return Inertia::render('Settings/Account', [
            'loginUser' => [
                'id'      => $loginUser->id,
                'name'    => $loginUser->name,
                'email'   => $loginUser->email,
                'profile' => $loginUser->profile,
            ],
        ]);
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

            $request->file('profile')->storeAs('upload/profile/', $profileToStore, 'public');
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

        return Inertia::render('Settings/Password', [
            'loginUser' => [
                'id'    => $loginUser->id,
                'name'  => $loginUser->name,
                'email' => $loginUser->email,
            ],
        ]);
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

        return Inertia::render('Settings/General', [
            'loginUser' => ['type' => $loginUser->type],
            'settings'  => $this->settingsSubset([
                'app_name', 'admin_signature',
                // Home-banner filenames, rendered as previews.
                'image_home_1', 'image_home_1_desktop', 'image_home_1_mobile',
            ]),
        ]);
    }

    public function generalData(Request $request)
    {
        if (\Auth::user()->type == 'super admin') {
            // One merged validator, not a rule per conditional block — the
            // previous per-field \Validator::make() calls each overwrote
            // $validator, so only the LAST field checked ever actually failed
            // the request; earlier invalid uploads silently passed through.
            // Home banners accept a wider set of formats than logo/favicon/
            // landing_logo, which stay PNG-only.
            $rules = ['application_name' => 'required'];
            $imageFields = [
                'logo' => 'png', 'landing_logo' => 'png', 'favicon' => 'png',
                'image_home_1' => 'png,jpg,jpeg,webp', 'image_home_2' => 'png,jpg,jpeg,webp',
                'image_home_1_desktop' => 'png,jpg,jpeg,webp', 'image_home_1_mobile' => 'png,jpg,jpeg,webp',
                'image_home_2_desktop' => 'png,jpg,jpeg,webp', 'image_home_2_mobile' => 'png,jpg,jpeg,webp',
            ];
            foreach ($imageFields as $field => $mimes) {
                if ($request->hasFile($field)) {
                    $rules[$field] = "required|mimes:{$mimes}";
                }
            }
            $validator = \Validator::make($request->all(), $rules);

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

            if ($request->hasFile('logo')) {
                $superadminLogoName = 'logo.png';
                $request->file('logo')->storeAs('upload/logo/', $superadminLogoName, 'public');
            }

            if ($request->hasFile('landing_logo')) {
                $superadminLandLogoName = 'landing_logo.png';
                $request->file('landing_logo')->storeAs('upload/logo/', $superadminLandLogoName, 'public');
            }

            if ($request->hasFile('favicon')) {
                $superadminFavicon = 'favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $superadminFavicon, 'public');
            }
            if ($request->hasFile('favicon')) {
                $superadminFavicon = 'favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $superadminFavicon, 'public');
            }
            if ($request->hasFile('favicon')) {
                $superadminFavicon = 'favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $superadminFavicon, 'public');
            }

            // Extension follows the actual uploaded file (MIME-derived, not the
            // client-supplied filename) — home banners now accept jpg/jpeg/webp
            // too, so a fixed ".png" suffix would silently mislabel them.
            foreach (['image_home_1', 'image_home_2', 'image_home_1_desktop', 'image_home_1_mobile', 'image_home_2_desktop', 'image_home_2_mobile'] as $field) {
                if ($request->hasFile($field)) {
                    $fileName = "{$field}." . $request->file($field)->extension();
                    $request->file($field)->storeAs('upload/home/', $fileName, 'public');
                    // The row, not just the file. HomeController reads Setting
                    // rows to find a banner; writing only to disk meant a super
                    // admin was told the upload succeeded while the storefront
                    // kept its gradient. parent_id = 1 is the global bucket
                    // ClientInstall seeds and the guest fallback reads.
                    \DB::insert(
                        'insert into settings (`value`, `name`, `parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                        [$fileName, $field, 1]
                    );
                }
            }

        } elseif (\Auth::user()->type == 'owner') {
            // Same consolidation as the super-admin branch above.
            $rules = ['application_name' => 'required'];
            $imageFields = [
                'logo' => 'png', 'favicon' => 'png',
                'image_home_1' => 'png,jpg,jpeg,webp', 'image_home_2' => 'png,jpg,jpeg,webp',
                'image_home_1_desktop' => 'png,jpg,jpeg,webp', 'image_home_1_mobile' => 'png,jpg,jpeg,webp',
                'image_home_2_desktop' => 'png,jpg,jpeg,webp', 'image_home_2_mobile' => 'png,jpg,jpeg,webp',
            ];
            foreach ($imageFields as $field => $mimes) {
                if ($request->hasFile($field)) {
                    $rules[$field] = "required|mimes:{$mimes}";
                }
            }
            $validator = \Validator::make($request->all(), $rules);

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

            if ($request->hasFile('logo')) {
                $ownerLogoName = parentId() . '_logo.png';
                $request->file('logo')->storeAs('upload/logo/', $ownerLogoName, 'public');

                \DB::insert(
                    'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $ownerLogoName,
                        'company_logo',
                        parentId(),
                    ]
                );
            }

            if ($request->hasFile('favicon')) {
                $ownerFaviconName = parentId() . '_favicon.png';
                $request->file('favicon')->storeAs('upload/logo/', $ownerFaviconName, 'public');

                \DB::insert(
                    'insert into settings (`value`, `name`,`parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $ownerFaviconName,
                        'company_favicon',
                        parentId(),
                    ]
                );
            }

            // Extension follows the actual uploaded file (MIME-derived) rather
            // than a fixed ".png" — see the matching comment in the super-admin
            // branch above. The field name doubles as the settings row name for
            // all 6 of these, so one loop covers every combination.
            foreach (['image_home_1', 'image_home_2', 'image_home_1_desktop', 'image_home_1_mobile', 'image_home_2_desktop', 'image_home_2_mobile'] as $field) {
                if ($request->hasFile($field)) {
                    $fileName = parentId() . "_{$field}." . $request->file($field)->extension();
                    $request->file($field)->storeAs('upload/home/', $fileName, 'public');
                    \DB::insert(
                        'insert into settings (`value`, `name`, `parent_id`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                        [$fileName, $field, parentId()]
                    );
                }
            }

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        flushSettingsCache();
        return redirect()->back()->with('success', __('General setting successfully saved.'));
    }
    //    ---------------------- SMTP --------------------------------------------------------

    public function smtp()
    {
        return Inertia::render('Settings/Smtp', [
            'settings' => $this->settingsSubset([
                'SERVER_DRIVER', 'SERVER_HOST', 'SERVER_PORT', 'SERVER_USERNAME',
                'SERVER_PASSWORD', 'SERVER_ENCRYPTION', 'FROM_EMAIL', 'FROM_NAME',
            ]),
        ]);
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

            flushSettingsCache();
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

    /**
     * The settings a page is allowed to receive, and nothing else.
     *
     * Inertia serialises props into the page's HTML, so `'settings' => settings()`
     * ships every row this tenant has to every settings screen -- including
     * credentials belonging to a different screen entirely. settingsFor() merges
     * DB rows over the defaults, so trimming settingsKeys() does not help: a row
     * saved before the default was removed survives it.
     *
     * Each page therefore declares what it reads. Missing keys come back null,
     * which is what the JSX already defaults for (`settings?.X ?? ''`).
     *
     * Note the two screens whose own subject is a secret -- SMTP and reCAPTCHA.
     * The allow-list stops them carrying *other* screens' credentials; it does
     * not stop them round-tripping their own, which is what an editable
     * credential field does by construction. Masking those is a separate change.
     */
    private function settingsSubset(array $allowed): array
    {
        $s = settings();

        return collect($allowed)
            ->mapWithKeys(fn ($key) => [$key => $s[$key] ?? null])
            ->all();
    }

    //    ---------------------- Payment --------------------------------------------------------

    public function payment()
    {
        // An explicit allow-list, not settings() wholesale.
        //
        // settingsFor() merges DB rows *over* the defaults, so any deployment
        // that ever saved a gateway credential still has a `settings` row named
        // STRIPE_SECRET / paypal_secret_key / flutterwave_secret_key -- and
        // sharing the whole array serialised those secrets into the HTML of
        // this page, readable by anyone who could open it or by anything that
        // cached the response. Removing the keys from settingsKeys() does not
        // fix that on its own, which is why this lands first and separately.
        //
        // Deliberately not gated on a `manage payment settings` permission:
        // that permission is granted to the super-admin role only
        // (DefaultDataUsersTableSeeder), so adding a check here would lock every
        // owner out of their own currency and bank-transfer settings. Doing it
        // properly needs a permission backfill and touches the permissions
        // matrix, which CLAUDE.md §4 calls sacred -- its own ticket.
        return Inertia::render('Settings/Payment', [
            'settings' => $this->settingsSubset([
                'CURRENCY',
                'CURRENCY_SYMBOL',
                'bank_transfer_payment',
                'bank_name',
                'bank_holder_name',
                'bank_account_number',
                'bank_ifsc_code',
                'bank_other_details',
            ]),
        ]);
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
            // bank_transfer_payment is written by the bank block below, not
            // here. It used to be `?? 'off'`, so any save that did not carry the
            // field -- which was every save, since the page never registered it
            // -- silently switched bank transfer off.
            // The STRIPE_PAYMENT / paypal_payment master toggles went with their
            // blocks (BAN-335). They were written on every save regardless of
            // whether the form carried them, so a save from the trimmed page
            // would have quietly flipped both to 'off' -- writing rows nothing
            // reads, which is how they got here in the first place.
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
        //
        // Required only when the method is actually switched on. It used to be
        // required whenever the field was present at all, which is why the page
        // could not afford to send it -- and not sending it is what made every
        // bank edit vanish while the screen said "Payment successfully saved."
        if ($request->has('bank_transfer_payment')) {
            if ($request->input('bank_transfer_payment') === 'on') {
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
            }

            $bankArray = ['bank_transfer_payment' => $request->input('bank_transfer_payment')];

            // Only the detail fields the request actually carried.
            //
            // settings.value is NOT NULL, and these were read straight off the
            // request -- fine while the required-validation above ran on every
            // save, since it guaranteed they were present. Once turning the
            // method *off* stopped requiring them, a toggle-only save passed
            // null straight into the insert and produced a 500. A request that
            // omits a field is not asking to blank it either.
            foreach ([
                'bank_name',
                'bank_holder_name',
                'bank_account_number',
                'bank_ifsc_code',
                'bank_other_details',
            ] as $field) {
                if ($request->has($field)) {
                    $bankArray[$field] = (string) $request->input($field, '');
                }
            }

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

        // Stripe, PayPal and Flutterwave blocks removed (BAN-335). None of
        // the three was ever wired to anything: no SDK in composer.json, no
        // route, no controller, no webhook. What was here was a credential
        // form that wrote ten settings rows nothing ever read.
        //
        // The rows themselves are not deleted. A down() that cannot restore a
        // secret is not a real down(), and dropping a populated row in
        // production is what CLAUDE.md §8 forbids -- if a deployment saved a
        // live key, rotate it at the provider and remove it out of band.

        flushSettingsCache();
        return redirect()->back()->with('success', __('Payment successfully saved.'));
    }

    //    ---------------------- Company  --------------------------------------------------------

    public function company()
    {
        $timezones = config('timezones');

        return Inertia::render('Settings/Company', [
            'settings'  => $this->settingsSubset([
                'company_name', 'company_email', 'company_phone', 'company_address',
                'company_date_format', 'company_time_format', 'timezone',
                'CURRENCY', 'CURRENCY_SYMBOL',
                'booking_number_prefix', 'client_number_prefix',
                'driver_number_prefix', 'vehicle_number_prefix',
                'rental_agreement_number_prefix', 'rental_agreement_terms',
                'rc', 'ice', 'patente', 'if',
            ]),
            'timezones' => $timezones ?? [],
        ]);
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


        flushSettingsCache();
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

        flushSettingsCache();
        return redirect()->back()->with('success', __('Theme settings save successfully.'));
    }

    //    ---------------------- SEO Settings --------------------------------------------------------

    public function siteSEO()
    {
        return Inertia::render('Settings/SiteSeo', [
            'settings' => $this->settingsSubset([
                'meta_seo_title', 'meta_seo_description', 'meta_seo_keyword', 'meta_seo_image',
            ]),
        ]);
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


            $request->file('meta_seo_image')->storeAs('upload/seo/', $seoFileName, 'public');


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

        flushSettingsCache();
        return redirect()->back()->with('success', __('Site SEO settings save successfully.'));
    }

    //    ---------------------- Google ReCaptcha Settings --------------------------------------------------------

    public function googleRecaptcha()
    {
        return Inertia::render('Settings/Recaptcha', [
            'settings' => $this->settingsSubset([
                'google_recaptcha', 'recaptcha_key', 'recaptcha_secret',
            ]),
        ]);
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

        flushSettingsCache();
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

    flushSettingsCache();

    // The signature pad posts via Inertia (router.post), so respond with a
    // redirect rather than JSON — Inertia follows it and re-renders the
    // settings page with the freshly saved admin_signature.
    return redirect()->back()->with('success', __('Signature uploaded successfully.'));
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

    // ── BAN-244: Branding & Theme settings ──────────────────────────────────

    public function branding()
    {
        $user = \Auth::user();
        if ($user->type !== 'owner' && $user->type !== 'super admin' && !\Gate::allows('manage general settings')) {
            abort(403);
        }

        return Inertia::render('Settings/Branding', [
            'settings' => $this->settingsSubset([
                'brand_color', 'accent_color', 'brand_neutral', 'layout_mode',
            ]),
        ]);
    }

    public function brandingData(Request $request)
    {
        $user = \Auth::user();
        if ($user->type !== 'owner' && $user->type !== 'super admin' && !\Gate::allows('manage general settings')) {
            abort(403);
        }

        $validated = $request->validate([
            'brand_color'  => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_neutral' => ['nullable', 'in:cool,neutral,warm'],
            'layout_mode'  => ['nullable', 'in:lightmode,darkmode,systemmode'],
        ]);

        $owner = \Auth::user();
        $parentId = ($owner->type === 'owner') ? $owner->id : ($owner->parent_id ?? $owner->id);

        $fields = ['brand_color', 'accent_color', 'brand_neutral', 'layout_mode'];
        foreach ($fields as $field) {
            if (array_key_exists($field, $validated)) {
                Setting::updateOrCreate(
                    ['name' => $field, 'parent_id' => $parentId],
                    ['value' => $validated[$field] ?? '']
                );
            }
        }

        flushSettingsCache();

        return redirect()->back()->with('success', __('Branding settings saved successfully.'));
    }
}
