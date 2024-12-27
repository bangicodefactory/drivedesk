<?php
    $admin_logo = getSettingsValByName('company_logo');
    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $subscription = \App\Models\Subscription::find($authUser->subscription);
    $routeName = \Request::route()->getName();
?>
<aside class="codex-sidebar sidebar-<?php echo e($settings['sidebar_mode']); ?>">
    <div class="logo-gridwrap" style=" justify-content: center; align-items: center;">
        
            <a class="codexbrand-logo" href="<?php echo e(route('home')); ?>">
                <img class="img-fluid" style="max-height: 100px; "
                    src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png')); ?>"
                    alt="theeme-logo">
            </a>
        
        <a class="codex-darklogo" href="<?php echo e(route('home')); ?>">
            <img class="img-fluid"
                src="<?php echo e(asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png')); ?>"
                alt="theeme-logo"></a>
            
        <div class="sidebar-action" style="margin-left: auto;"><i data-feather="menu"></i></div>
    </div>
    <div class="icon-logo">
        <a href="<?php echo e(route('home')); ?>">
            <img class="img-fluid" src="<?php echo e(asset(Storage::url('upload/logo')) . '/' . $settings['company_favicon']); ?>"
                alt="theeme-logo">
        </a>
    </div>
    <div class="codex-menuwrapper">
        <ul class="codex-menu custom-scroll" data-simplebar>
            <li class="cdxmenu-title">
                <h5><?php echo e(__('Home')); ?></h5>
            </li>
            <li class="menu-item <?php echo e(in_array($routeName, ['dashboard', 'home', '']) ? 'active' : ''); ?>">
                <a href="<?php echo e(route('dashboard')); ?>">
                    <div class="icon-item"><i data-feather="home"></i></div>
                    <span><?php echo e(__('Dashboard')); ?></span>
                </a>
            </li>
            <?php if(\Auth::user()->type == 'super admin'): ?>
                <?php if(Gate::check('manage user')): ?>
                    <li class="menu-item <?php echo e(in_array($routeName, ['users.index']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('users.index')); ?>">
                            <div class="icon-item"><i data-feather="users"></i></div>
                            <span><?php echo e(__('Users')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <?php if(Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'active' : ''); ?>">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="users"></i></div>
                            <span><?php echo e(__('Staff Management')); ?></span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: <?php echo e(in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none'); ?>">
                            <?php if(Gate::check('manage role')): ?>
                                <li
                                    class=" <?php echo e(in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('role.index')); ?>"><?php echo e(__('Roles')); ?> </a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage user')): ?>
                                <li class="<?php echo e(in_array($routeName, ['users.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('users.index')); ?>"><?php echo e(__('Users')); ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if(Gate::check('manage logged history') && $subscription->enabled_logged_history == 1): ?>
                                <li class="<?php echo e(in_array($routeName, ['logged.history']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('logged.history')); ?>"><?php echo e(__('Logged History')); ?></a>
                                </li>
                            <?php endif; ?>

                        </ul>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php if(Gate::check('manage driver') ||
                    Gate::check('manage vehicle') ||
                    Gate::check('manage inspection') ||
                    Gate::check('manage booking') ||
                    Gate::check('manage expense') ||
                    Gate::check('manage rental agreement')): ?>
                <li class="cdxmenu-title">
                    <h5><?php echo e(__('Business Management')); ?></h5>
                </li>
                <?php if(Gate::check('manage driver')): ?>
                    <li class="menu-item <?php echo e(in_array($routeName, ['driver.index']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('driver.index')); ?>">
                            <div class="icon-item"><i data-feather="user-check"></i></div>
                            <span><?php echo e(__('Driver')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Gate::check('manage vehicle')): ?>
                    <li class="menu-item <?php echo e(in_array($routeName, ['vehicle.index']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('vehicle.index')); ?>">
                            <div class="icon-item"><i data-feather="truck"></i></div>
                            <span><?php echo e(__('Vehicle')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Gate::check('manage inspection')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['inspection.index', 'inspection.index', 'inspection.create', 'inspection.edit', 'inspection.show']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('inspection.index')); ?>">
                            <div class="icon-item"><i data-feather="user-check"></i></div>
                            <span><?php echo e(__('Inspection')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(Gate::check('manage booking')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['booking.index', 'booking.create', 'booking.edit', 'booking.show']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('booking.index')); ?>">
                            <div class="icon-item"><i data-feather="file"></i></div>
                            <span><?php echo e(__('Booking')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Gate::check('manage planning')): ?>
                    <li class="menu-item <?php echo e(in_array($routeName, ['planning']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('planning')); ?>">
                            <div class="icon-item"><i data-feather="calendar"></i></div>
                            <span><?php echo e(__('Planning')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Gate::check('manage rental agreement')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['rental-agreement.index', 'rental-agreement.show']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('rental-agreement.index')); ?>">
                            <div class="icon-item"><i data-feather="file-plus"></i></div>
                            <span><?php echo e(__('Rental Agreement')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Gate::check('manage expense')): ?>
                    <li class="menu-item <?php echo e(in_array($routeName, ['expense.index']) ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('expense.index')); ?>">
                            <div class="icon-item"><i data-feather="file-text"></i></div>
                            <span><?php echo e(__('Expense')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if(Gate::check('manage vehicle type') ||
                    Gate::check('manage inspection type') ||
                    Gate::check('manage expense type') ||
                    Gate::check('manage options') ||
                    Gate::check('manage addon') ||
                    Gate::check('manage notification')): ?>
                <li class="cdxmenu-title">
                    <h5><?php echo e(__('System Setup')); ?></h5>
                </li>

                <?php if(Gate::check('manage vehicle type') || Gate::check('manage inspection type') || Gate::check('manage expense type')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['vehicle-type.index', 'inspection-type.index', 'expense-type.index']) ? 'active' : ''); ?>">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="layers"></i></div>
                            <span><?php echo e(__('Types')); ?></span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: <?php echo e(in_array($routeName, ['vehicle-type.index', 'inspection-type.index', 'expense-type.index']) ? 'block' : 'none'); ?>">
                            <?php if(Gate::check('manage vehicle type')): ?>
                                <li class=" <?php echo e(in_array($routeName, ['vehicle-type.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('vehicle-type.index')); ?>"><?php echo e(__('Vehicle Type')); ?> </a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage inspection type')): ?>
                                <li class="<?php echo e(in_array($routeName, ['inspection-type.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('inspection-type.index')); ?>"><?php echo e(__('Inspection Type')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage expense type')): ?>
                                <li class="<?php echo e(in_array($routeName, ['expense-type.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('expense-type.index')); ?>"><?php echo e(__('Expense Type')); ?></a>
                                </li>
                            <?php endif; ?>


                        </ul>
                    </li>
                <?php endif; ?>

                <?php if(Gate::check('manage options') || Gate::check('manage addon') || Gate::check('manage place')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['option.index', 'addon.index', 'place.index']) ? 'active' : ''); ?>">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="wind"></i></div>
                            <span><?php echo e(__('Booking')); ?></span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: <?php echo e(in_array($routeName, ['option.index', 'addon.index', 'place.index']) ? 'block' : 'none'); ?>">
                            <?php if(Gate::check('manage options')): ?>
                                <li class=" <?php echo e(in_array($routeName, ['option.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('option.index')); ?>"><?php echo e(__('Options')); ?> </a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage inspection type')): ?>
                                <li class="<?php echo e(in_array($routeName, ['addon.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('addon.index')); ?>"><?php echo e(__('Addon')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage place')): ?>
                                <li class="<?php echo e(in_array($routeName, ['place.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('place.index')); ?>"><?php echo e(__('Places')); ?></a>
                                </li>
                            <?php endif; ?>


                        </ul>
                    </li>
                <?php endif; ?>

                <?php if(Gate::check('manage notification')): ?>
                    <li class="menu-item <?php echo e(Request::route()->getName() == 'notification.index' ? 'active' : ''); ?>">
                        <a href="<?php echo e(route('notification.index')); ?>">
                            <div class="icon-item"><i data-feather="bell"></i></div>
                            <span><?php echo e(__('Email Notification')); ?></span>
                        </a>
                    </li>
                <?php endif; ?>


            <?php endif; ?>
            <?php if(Gate::check('manage pricing packages') ||
                    Gate::check('manage pricing transation') ||
                    Gate::check('manage account settings') ||
                    Gate::check('manage password settings') ||
                    Gate::check('manage general settings') ||
                    Gate::check('manage company settings')): ?>
                <li class="cdxmenu-title">
                    <h5><?php echo e(__('System Settings')); ?></h5>
                </li>
                <?php if(Gate::check('manage pricing packages') || Gate::check('manage pricing transation')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'active' : ''); ?>">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="database"></i></div>
                            <span><?php echo e(__('Pricing')); ?></span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: <?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none'); ?>">
                            <?php if(Gate::check('manage pricing packages')): ?>
                                <li
                                    class="<?php echo e(in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('subscriptions.index')); ?>"><?php echo e(__('Packages')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage pricing transation')): ?>
                                <li class="<?php echo e(in_array($routeName, ['subscription.transaction']) ? 'active' : ''); ?> ">
                                    <a href="<?php echo e(route('subscription.transaction')); ?>"><?php echo e(__('Transactions')); ?></a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if(Gate::check('manage coupon') || Gate::check('manage coupon history')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : ''); ?>">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="gift"></i></div>
                            <span><?php echo e(__('Coupons')); ?></span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list"
                            style="display: <?php echo e(in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none'); ?>">
                            <?php if(Gate::check('manage coupon')): ?>
                                <li class="<?php echo e(in_array($routeName, ['coupons.index']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('coupons.index')); ?>"><?php echo e(__('All Coupon')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage coupon history')): ?>
                                <li class="<?php echo e(in_array($routeName, ['coupons.history']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('coupons.history')); ?>"><?php echo e(__('Coupon History')); ?></a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if(Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings')): ?>
                    <li
                        class="menu-item <?php echo e(in_array($routeName, ['setting.account', 'setting.password', 'setting.general', 'setting.company', 'setting.smtp', 'setting.payment', 'setting.site.seo', 'setting.google.recaptcha']) ? 'active' : ''); ?>">
                        <a href="javascript:void(0);">
                            <div class="icon-item"><i data-feather="settings"></i></div>
                            <span><?php echo e(__('Settings')); ?></span><i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="submenu-list "
                            style="display: <?php echo e(in_array($routeName, ['setting.account', 'setting.password', 'setting.general', 'setting.company', 'setting.smtp', 'setting.payment', 'setting.site.seo', 'setting.google.recaptcha']) ? 'block' : 'none'); ?>">
                            <?php if(Gate::check('manage account settings')): ?>
                                <li class="<?php echo e(in_array($routeName, ['setting.account']) ? 'active' : ''); ?> ">
                                    <a href="<?php echo e(route('setting.account')); ?>"><?php echo e(__('Account Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage password settings')): ?>
                                <li class="<?php echo e(in_array($routeName, ['setting.password']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('setting.password')); ?>"><?php echo e(__('Password Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage general settings')): ?>
                                <li class="<?php echo e(in_array($routeName, ['setting.general']) ? 'active' : ''); ?> ">
                                    <a href="<?php echo e(route('setting.general')); ?>"><?php echo e(__('General Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage company settings')): ?>
                                <li class="<?php echo e(in_array($routeName, ['setting.company']) ? 'active' : ''); ?>">
                                    <a href="<?php echo e(route('setting.company')); ?>"><?php echo e(__('Company Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage email settings')): ?>
                                <li class="<?php echo e(in_array($routeName, ['setting.smtp']) ? 'active' : ''); ?> ">
                                    <a href="<?php echo e(route('setting.smtp')); ?>"><?php echo e(__('Email Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage payment settings')): ?>
                                <li class="<?php echo e(in_array($routeName, ['setting.payment']) ? 'active' : ''); ?> ">
                                    <a href="<?php echo e(route('setting.payment')); ?>"><?php echo e(__('Payment Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage seo settings')): ?>
                                <li class="<?php echo e(in_array($routeName, ['setting.site.seo']) ? 'active' : ''); ?> ">
                                    <a href="<?php echo e(route('setting.site.seo')); ?>"><?php echo e(__('Site SEO Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if(Gate::check('manage google recaptcha settings')): ?>
                                <li
                                    class="<?php echo e(in_array($routeName, ['setting.google.recaptcha']) ? 'active' : ''); ?> ">
                                    <a
                                        href="<?php echo e(route('setting.google.recaptcha')); ?>"><?php echo e(__('ReCaptcha Setting')); ?></a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

            <?php endif; ?>
        </ul>
    </div>
</aside>
<!-- sidebar end-->
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/carrent/resources/views/admin/menu.blade.php ENDPATH**/ ?>