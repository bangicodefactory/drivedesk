@php
    $admin_logo = getSettingsValByName('company_logo');
    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $subscription = \App\Models\Subscription::find($authUser->subscription);
    $routeName = \Request::route()->getName();
@endphp
<aside class="codex-sidebar sidebar-{{ $settings['sidebar_mode'] }}">
    <div class="logo-gridwrap" style=" justify-content: center; align-items: center;">

            <a class="codexbrand-logo" href="{{ route('home') }}">
                <img class="img-fluid" style="max-height: 100px; "
                    src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
                    alt="theeme-logo">
            </a>

        <a class="codex-darklogo" href="{{ route('home') }}">
            <img class="img-fluid"
                src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
                alt="theeme-logo"></a>

        <div class="sidebar-action" style="margin-left: auto;"><i data-feather="menu"></i></div>
    </div>
    <div class="icon-logo">
        <a href="{{ route('home') }}">
            <img class="img-fluid" src="{{ asset(Storage::url('upload/logo')) . '/' . $settings['company_favicon'] }}"
                alt="theeme-logo">
        </a>
    </div>
    <div class="codex-menuwrapper">
        <ul class="codex-menu custom-scroll" data-simplebar>
            <li class="cdxmenu-title">
                <h5>{{ __('Home') }}</h5>
            </li>
            <li class="menu-item {{ in_array($routeName, ['dashboard', 'home', '']) ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <div class="icon-item"><i data-feather="home"></i></div>
                    <span>{{ __('Dashboard') }}</span>
                </a>
            </li>
            @if (\Auth::user()->type == 'super admin')
                @if (Gate::check('manage user'))
                    <li class="menu-item {{ in_array($routeName, ['users.index']) ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}">
                            <div class="icon-item"><i data-feather="users"></i></div>
                            <span>{{ __('Users') }}</span>
                        </a>
                    </li>
                @endif
            @else
                @if (Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history'))
                    <li
                        class="menu-item {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'active' : '' }}">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="users"></i></div>
                            <span>{{ __('Staff Management') }}</span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none' }}">
                            @if (Gate::check('manage role'))
                                <li
                                    class=" {{ in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : '' }}">
                                    <a href="{{ route('role.index') }}">{{ __('Roles') }} </a>
                                </li>
                            @endif
                            @if (Gate::check('manage user'))
                                <li class="{{ in_array($routeName, ['users.index']) ? 'active' : '' }}">
                                    <a href="{{ route('users.index') }}">{{ __('Users') }}</a>
                                </li>
                            @endif

                            @if (Gate::check('manage logged history') && $subscription->enabled_logged_history == 1)
                                <li class="{{ in_array($routeName, ['logged.history']) ? 'active' : '' }}">
                                    <a href="{{ route('logged.history') }}">{{ __('Logged History') }}</a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif
            @endif

            @if (Gate::check('manage driver') ||
                    Gate::check('manage vehicle') ||
                    Gate::check('manage inspection') ||
                    Gate::check('manage booking') ||

                    Gate::check('manage expense') ||
                    Gate::check('manage reminder') ||
                    Gate::check('manage rental agreement'))
                <li class="cdxmenu-title">
                    <h5>{{ __('Business Management') }}</h5>
                </li>
                @if (Gate::check('manage driver'))
                    <li class="menu-item {{ in_array($routeName, ['driver.index']) ? 'active' : '' }}">
                        <a href="{{ route('driver.index') }}">
                            <div class="icon-item"><i data-feather="user-check"></i></div>
                            <span>{{ __('Driver') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage vehicle'))
                    <li class="menu-item {{ in_array($routeName, ['vehicle.index']) ? 'active' : '' }}">
                        <a href="{{ route('vehicle.index') }}">
                            <div class="icon-item"><i data-feather="truck"></i></div>
                            <span>{{ __('Vehicle') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage inspection'))
                    <li
                        class="menu-item {{ in_array($routeName, ['inspection.index', 'inspection.index', 'inspection.create', 'inspection.edit', 'inspection.show']) ? 'active' : '' }}">
                        <a href="{{ route('inspection.index') }}">
                            <div class="icon-item"><i data-feather="user-check"></i></div>
                            <span>{{ __('Inspection') }}</span>
                        </a>
                    </li>
                @endif

                @if (Gate::check('manage booking'))
                    <li
                        class="menu-item {{ in_array($routeName, ['booking.index', 'booking.create', 'booking.edit', 'booking.show']) ? 'active' : '' }}">
                        <a href="{{ route('booking.index') }}">
                            <div class="icon-item"><i data-feather="file"></i></div>
                            <span>{{ __('Booking') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage booking'))
                <li
                    class="menu-item {{ in_array($routeName, ['booking_requests.index', 'booking_requests.create', 'booking_requests.edit', 'booking_requests.show']) ? 'active' : '' }}">
                    <a href="{{ route('booking_requests.index') }}">
                        <div class="icon-item"><i data-feather="file"></i></div>
                        <span>{{ __('Booking requests') }}</span>
                    </a>
                </li>
            @endif
                @if (Gate::check('manage planning'))
                    <li class="menu-item {{ in_array($routeName, ['planning']) ? 'active' : '' }}">
                        <a href="{{ route('planning') }}">
                            <div class="icon-item"><i data-feather="calendar"></i></div>
                            <span>{{ __('Planning') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage rental agreement'))
                    <li
                        class="menu-item {{ in_array($routeName, ['rental-agreement.index', 'rental-agreement.show']) ? 'active' : '' }}">
                        <a href="{{ route('rental-agreement.index') }}">
                            <div class="icon-item"><i data-feather="file-plus"></i></div>
                            <span>{{ __('Rental Agreement') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage expense'))
                    <li class="menu-item {{ in_array($routeName, ['expense.index']) ? 'active' : '' }}">
                        <a href="{{ route('expense.index') }}">
                            <div class="icon-item"><i data-feather="file-text"></i></div>
                            <span>{{ __('Expense') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage reminder'))
                    <li class="menu-item {{ in_array($routeName, ['reminder.index']) ? 'active' : '' }}">
                        <a href="{{ route('reminder.index') }}">
                            <div class="icon-item"><i data-feather="file-text"></i></div>
                            <span>{{ __('Reminder') }}</span>
                        </a>
                    </li>
                @endif
                @if (Gate::check('manage reminder'))
                    <li
                        class="menu-item {{ in_array($routeName, ['tva.index', 'tva.report']) ? 'active' : '' }}">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="file-text"></i></div>
                            <span>{{ __('TVA') }}</span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: {{ in_array($routeName, ['tva.index', 'tva.report']) ? 'block' : 'none' }}">
                            <li class=" {{ in_array($routeName, ['tva.index']) ? 'active' : '' }}">
                                <a href="{{ route('tva.index') }}">{{ __('TVA Management') }} </a>
                            </li>
                            <li class="{{ in_array($routeName, ['tva.report']) ? 'active' : '' }}">
                                <a href="{{ route('tva.report') }}">{{ __('TVA Report') }}</a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (Gate::check('manage reminder'))
                    <li class="menu-item {{ in_array($routeName, ['signature.index']) ? 'active' : '' }}">
                        <a href="{{ route('signature.index') }}">
                            <div class="icon-item"><i data-feather="file-text"></i></div>
                            <span>{{ __('Signature') }}</span>
                        </a>
                    </li>
                @endif
            @endif
            @if (Gate::check('manage vehicle type') ||
                    Gate::check('manage inspection type') ||
                    Gate::check('manage expense type') ||
                    Gate::check('manage options') ||
                    Gate::check('manage addon') ||
                    Gate::check('manage reminder') ||
                    Gate::check('manage notification'))
                <li class="cdxmenu-title">
                    <h5>{{ __('System Setup') }}</h5>
                </li>

                @if (Gate::check('manage vehicle type') || Gate::check('manage inspection type') || Gate::check('manage expense type'))
                    <li
                        class="menu-item {{ in_array($routeName, ['vehicle-type.index', 'inspection-type.index', 'expense-type.index']) ? 'active' : '' }}">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="layers"></i></div>
                            <span>{{ __('Types') }}</span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: {{ in_array($routeName, ['vehicle-type.index', 'inspection-type.index', 'expense-type.index']) ? 'block' : 'none' }}">
                            @if (Gate::check('manage vehicle type'))
                                <li class=" {{ in_array($routeName, ['vehicle-type.index']) ? 'active' : '' }}">
                                    <a href="{{ route('vehicle-type.index') }}">{{ __('Vehicle Type') }} </a>
                                </li>
                            @endif
                            @if (Gate::check('manage inspection type'))
                                <li class="{{ in_array($routeName, ['inspection-type.index']) ? 'active' : '' }}">
                                    <a href="{{ route('inspection-type.index') }}">{{ __('Inspection Type') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage expense type'))
                                <li class="{{ in_array($routeName, ['expense-type.index']) ? 'active' : '' }}">
                                    <a href="{{ route('expense-type.index') }}">{{ __('Expense Type') }}</a>
                                </li>
                            @endif
                            {{-- @if (Gate::check('manage reminder'))
                                <li class="{{ in_array($routeName, ['reminder-type.index']) ? 'active' : '' }}">
                                    <a href="{{ route('reminder-type.index') }}">{{ __('Reminder Type') }}</a>
                                </li>
                            @endif --}}


                        </ul>
                    </li>
                @endif

                @if (Gate::check('manage options') || Gate::check('manage addon') || Gate::check('manage place'))
                    <li
                        class="menu-item {{ in_array($routeName, ['option.index', 'addon.index', 'place.index']) ? 'active' : '' }}">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="wind"></i></div>
                            <span>{{ __('Booking') }}</span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: {{ in_array($routeName, ['option.index', 'addon.index', 'place.index']) ? 'block' : 'none' }}">
                            @if (Gate::check('manage options'))
                                <li class=" {{ in_array($routeName, ['option.index']) ? 'active' : '' }}">
                                    <a href="{{ route('option.index') }}">{{ __('Options') }} </a>
                                </li>
                            @endif
                            @if (Gate::check('manage inspection type'))
                                <li class="{{ in_array($routeName, ['addon.index']) ? 'active' : '' }}">
                                    <a href="{{ route('addon.index') }}">{{ __('Addon') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage place'))
                                <li class="{{ in_array($routeName, ['place.index']) ? 'active' : '' }}">
                                    <a href="{{ route('place.index') }}">{{ __('Places') }}</a>
                                </li>
                            @endif


                        </ul>
                    </li>
                @endif

                @if (Gate::check('manage notification'))
                    <li class="menu-item {{ Request::route()->getName() == 'notification.index' ? 'active' : '' }}">
                        <a href="{{ route('notification.index') }}">
                            <div class="icon-item"><i data-feather="bell"></i></div>
                            <span>{{ __('Email Notification') }}</span>
                        </a>
                    </li>
                @endif


            @endif
            @if (Gate::check('manage pricing packages') ||
                    Gate::check('manage pricing transation') ||
                    Gate::check('manage account settings') ||
                    Gate::check('manage password settings') ||
                    Gate::check('manage general settings') ||
                    Gate::check('manage company settings'))
                <li class="cdxmenu-title">
                    <h5>{{ __('System Settings') }}</h5>
                </li>
                @if (Gate::check('manage pricing packages') || Gate::check('manage pricing transation'))
                    <li
                        class="menu-item {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'active' : '' }}">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="database"></i></div>
                            <span>{{ __('Pricing') }}</span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none' }}">
                            @if (Gate::check('manage pricing packages'))
                                <li
                                    class="{{ in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : '' }}">
                                    <a href="{{ route('subscriptions.index') }}">{{ __('Packages') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage pricing transation'))
                                <li class="{{ in_array($routeName, ['subscription.transaction']) ? 'active' : '' }} ">
                                    <a href="{{ route('subscription.transaction') }}">{{ __('Transactions') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (Gate::check('manage coupon') || Gate::check('manage coupon history'))
                    <li
                        class="menu-item {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : '' }}">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="gift"></i></div>
                            <span>{{ __('Coupons') }}</span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none' }}">
                            @if (Gate::check('manage coupon'))
                                <li class="{{ in_array($routeName, ['coupons.index']) ? 'active' : '' }}">
                                    <a href="{{ route('coupons.index') }}">{{ __('All Coupon') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage coupon history'))
                                <li class="{{ in_array($routeName, ['coupons.history']) ? 'active' : '' }}">
                                    <a href="{{ route('coupons.history') }}">{{ __('Coupon History') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
                @if (Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings'))
                    <li
                        class="menu-item {{ in_array($routeName, ['setting.account', 'setting.password', 'setting.general', 'setting.company', 'setting.smtp', 'setting.payment', 'setting.site.seo', 'setting.google.recaptcha']) ? 'active' : '' }}">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="settings"></i></div>
                            <span>{{ __('Settings') }}</span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list "
                            style="display: {{ in_array($routeName, ['setting.account', 'setting.password', 'setting.general', 'setting.company', 'setting.smtp', 'setting.payment', 'setting.site.seo', 'setting.google.recaptcha']) ? 'block' : 'none' }}">
                            @if (Gate::check('manage account settings'))
                                <li class="{{ in_array($routeName, ['setting.account']) ? 'active' : '' }} ">
                                    <a href="{{ route('setting.account') }}">{{ __('Account Setting') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage password settings'))
                                <li class="{{ in_array($routeName, ['setting.password']) ? 'active' : '' }}">
                                    <a href="{{ route('setting.password') }}">{{ __('Password Setting') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage general settings'))
                                <li class="{{ in_array($routeName, ['setting.general']) ? 'active' : '' }} ">
                                    <a href="{{ route('setting.general') }}">{{ __('General Setting') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage company settings'))
                                <li class="{{ in_array($routeName, ['setting.company']) ? 'active' : '' }}">
                                    <a href="{{ route('setting.company') }}">{{ __('Company Setting') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage email settings'))
                                <li class="{{ in_array($routeName, ['setting.smtp']) ? 'active' : '' }} ">
                                    <a href="{{ route('setting.smtp') }}">{{ __('Email Setting') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage payment settings'))
                                <li class="{{ in_array($routeName, ['setting.payment']) ? 'active' : '' }} ">
                                    <a href="{{ route('setting.payment') }}">{{ __('Payment Setting') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage seo settings'))
                                <li class="{{ in_array($routeName, ['setting.site.seo']) ? 'active' : '' }} ">
                                    <a href="{{ route('setting.site.seo') }}">{{ __('Site SEO Setting') }}</a>
                                </li>
                            @endif
                            @if (Gate::check('manage google recaptcha settings'))
                                <li
                                    class="{{ in_array($routeName, ['setting.google.recaptcha']) ? 'active' : '' }} ">
                                    <a
                                        href="{{ route('setting.google.recaptcha') }}">{{ __('ReCaptcha Setting') }}</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

            @endif
        </ul>
    </div>
</aside>
<!-- sidebar end-->
