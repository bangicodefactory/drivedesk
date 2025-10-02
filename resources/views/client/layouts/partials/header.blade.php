@php
    $settings = \App\Models\Setting::pluck('value', 'name')->toArray();
    $languages = [
        'ar' => 'العربية',
        'fr' => 'Français',
        'en' => 'English',
    ];

    // Get current locale from app (which is set by middleware)
    $currentLang = app()->getLocale();
@endphp

<header id="header-sticky" class="header-1">
    <div class="container-fluid">
        <div class="mega-menu-wrapper">
            <div class="header-main">
                <div class="header-left">
                    <div class="logo">
                        <a class="codexbrand-logo" href="{{ route('home') }}">
                            <img class="img-fluid" style="max-height: 100px;"
                                src="{{ asset(Storage::url('upload/logo/' . $settings['company_logo'])) }}"
                                alt="theme-logo">
                        </a>
                    </div>

                    <div class="mean__menu-wrapper">
                        <div class="main-menu">
                            <nav id="mobile-menu">
                                <ul>
                                    <li><a href="{{ url('/') }}">{{ __('menu_home') }}</a></li>
                                    <li><a href="#">{{ __('menu_cars') }}</a></li>
                                    <li><a href="#">{{ __('menu_about') }}</a></li>
                                    {{-- <li><a href="#">{{ __('menu_blog') }}</a></li> --}}
                                    <li><a href="{{ route('contact') }}">{{ __('menu_contact') }}</a></li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="header-right d-flex justify-content-end align-items-center">
                    <div class="icon-items">
                        <div class="icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="content">
                            <p>{{ __('header_call_anytime') }}</p>
                            <h6><a href="tel:{{ $settings['company_phone'] }}">{{ $settings['company_phone'] }}</a>
                            </h6>
                        </div>
                    </div>
                    <!-- Language Switcher -->
                    <!-- Font-Awesome Language Switcher -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                            id="lang-dd" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe fa-fw"></i>
                            <span
                                class="d-none d-md-inline">{{ $languages[$currentLang] ?? ucfirst($currentLang) }}</span>
                            <i class="fas fa-chevron-down fa-xs"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-1 p-1"
                            style="min-width: 140px">
                            @foreach ($languages as $code => $name)
                                @if ($code !== $currentLang)
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2 py-1"
                                            href="{{ route('language.change', $code) }}">
                                            <i class="fas fa-language fa-fw"></i>
                                            <span>{{ $name }}</span>
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    <div class="header-button">
                        <a href="{{ route('client.home') }}#search" class="header-btn">{{ __('header_find_a_car') }}</a>
                    </div>
                    <div class="header__hamburger d-xl-none my-auto">
                        <div class="sidebar__toggle">
                            <i class="fa fa-bars"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
