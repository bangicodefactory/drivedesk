<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InspectionTypeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RentalAgreementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';

Route::get('/', [HomeController::class, 'index'])->middleware(
    [

        'XSS',
    ]
);
Route::get('home', [HomeController::class, 'index'])->name('home')->middleware(
    [

        'XSS',
    ]
);
Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware(
    [

        'XSS',
    ]
);

//-------------------------------User-------------------------------------------

Route::resource('users', UserController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);


//-------------------------------Subscription-------------------------------------------


Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::resource('subscriptions', SubscriptionController::class);
    Route::get('coupons/history', [CouponController::class, 'history'])->name('coupons.history');
    Route::delete('coupons/history/{id}/destroy', [CouponController::class, 'historyDestroy'])->name('coupons.history.destroy');
    Route::get('coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
    Route::resource('coupons', CouponController::class);
    Route::get('subscription/transaction', [SubscriptionController::class, 'transaction'])->name('subscription.transaction');
}
);

//-------------------------------Subscription Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::post('subscription/{id}/stripe/payment', [SubscriptionController::class, 'stripePayment'])->name('subscription.stripe.payment');
}
);
//-------------------------------Settings-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {
    Route::get('settings/account', [SettingController::class, 'account'])->name('setting.account');
    Route::post('settings/account', [SettingController::class, 'accountData'])->name('setting.account');
    Route::delete('settings/account/delete', [SettingController::class, 'accountDelete'])->name('setting.account.delete');

    Route::get('settings/password', [SettingController::class, 'password'])->name('setting.password');
    Route::post('settings/password', [SettingController::class, 'passwordData'])->name('setting.password');

    Route::get('settings/general', [SettingController::class, 'general'])->name('setting.general');
    Route::post('settings/general', [SettingController::class, 'generalData'])->name('setting.general');

    Route::get('settings/smtp', [SettingController::class, 'smtp'])->name('setting.smtp');
    Route::post('settings/smtp', [SettingController::class, 'smtpData'])->name('setting.smtp');

    Route::get('settings/smtp-test', [SettingController::class, 'smtpTest'])->name('setting.smtp.test');
    Route::post('settings/smtp-test', [SettingController::class, 'smtpTestMailSend'])->name('setting.smtp.testing');

    Route::get('settings/payment', [SettingController::class, 'payment'])->name('setting.payment');
    Route::post('settings/payment', [SettingController::class, 'paymentData'])->name('setting.payment');

    Route::get('settings/company', [SettingController::class, 'company'])->name('setting.company');
    Route::post('settings/company', [SettingController::class, 'companyData'])->name('setting.company');

    Route::get('language/{lang}', [SettingController::class, 'lanquageChange'])->name('language.change');
    Route::post('theme/settings', [SettingController::class, 'themeSettings'])->name('theme.settings');

    Route::get('settings/site-seo', [SettingController::class, 'siteSEO'])->name('setting.site.seo');
    Route::post('settings/site-seo', [SettingController::class, 'siteSEOData'])->name('setting.site.seo');

    Route::get('settings/google-recaptcha', [SettingController::class, 'googleRecaptcha'])->name('setting.google.recaptcha');
    Route::post('settings/google-recaptcha', [SettingController::class, 'googleRecaptchaData'])->name('setting.google.recaptcha');
}
);


//-------------------------------Role & Permissions-------------------------------------------
Route::resource('permission', PermissionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::resource('role', RoleController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);


//-------------------------------logged History-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::get('logged/history', [UserController::class, 'loggedHistory'])->name('logged.history');
    Route::get('logged/{id}/history/show', [UserController::class, 'loggedHistoryShow'])->name('logged.history.show');
    Route::delete('logged/{id}/history', [UserController::class, 'loggedHistoryDestroy'])->name('logged.history.destroy');


});


//-------------------------------Plan Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
    Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
    Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
    Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
    Route::post('subscription/{id}/flutterwave', [PaymentController::class, 'subscriptionFlutterwave'])->name('subscription.flutterwave')->middleware(['XSS']);
    Route::get('subscription/flutterwave/{id}/{txref}', [PaymentController::class, 'subscriptionFlutterwaveStatus'])->name('subscription.flutterwave.status');
}
);


//-------------------------------driver-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::get('driver/new/create', [DriverController::class, 'newCreate'])->name('driver.new.create');
    Route::resource('driver', DriverController::class);
}
);


//-------------------------------Vehicle Type-------------------------------------------
Route::resource('vehicle-type', VehicleTypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------Vehicle-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::get('vehicle/rate/calculation', [VehicleController::class, 'getVehicleRateCalculation'])->name('vehicle.rate.calculation');
    Route::get('vehicle/available', [VehicleController::class, 'getAvailableVehicle'])->name('available.vehicle');
    Route::resource('vehicle', VehicleController::class);
}
);


//-------------------------------Inspection-------------------------------------------
Route::resource('inspection', InspectionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Inspection Type-------------------------------------------
Route::resource('inspection-type', InspectionTypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Booking-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::get('planning', [BookingController::class, 'planning'])->name('planning');
    Route::get('booking/{id}/payment/create', [BookingController::class, 'paymentCreate'])->name('booking.payment.create');
    Route::post('booking/{id}/payment/store', [BookingController::class, 'paymentStore'])->name('booking.payment.store');
    Route::delete('booking/{id}/payment/{pid}/destroy', [BookingController::class,'paymentDestroy'])->name('booking.payment.destroy');
    Route::resource('booking', BookingController::class);
});

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::resource('expense', ExpenseController::class);
});
//-------------------------------Option-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::resource('option', OptionController::class);
});
//-------------------------------Addon-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {

    Route::get('addon/rate/calculation', [AddonController::class, 'getAddonRateCalculation'])->name('addon.rate.calculation');
    // reduction function 
    Route::get('addon/rate/reduction', [AddonController::class, 'getReductionRateCalculation'])->name('addon.rate.reduction');
    Route::resource('addon', AddonController::class);
});
//-------------------------------Place-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {
    Route::get('place/rate/calculation', [PlaceController::class, 'getPlaceRateCalculation'])->name('place.rate.calculation');
    Route::resource('place', PlaceController::class);
});

//-------------------------------Expense Type-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {
    Route::resource('expense-type', ExpenseTypeController::class);
});
//-------------------------------Rental Agreement-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function () {
    Route::resource('rental-agreement', RentalAgreementController::class);
});



//-------------------------------Notification-------------------------------------------
Route::resource('notification', NotificationController::class)->middleware(
    [
        'auth',
        'XSS',

    ]
 );

 Route::get('email-verification/{token}', [VerifyEmailController::class, 'verifyEmail'])->name('email-verification')->middleware(
    [
        'XSS',
    ]
);
