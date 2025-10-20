@php
    $settings = \App\Models\Setting::pluck('value', 'name')->toArray();
@endphp

<footer class="footer-section fix">
    <div class="container">
        <div class="footer-widgets-wrapper">
            <div class="row justify-content-between">

                <!-- Contact -->
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                    <div class="single-footer-widget shape-map">
                        <div class="widget-head">
                            <h4>{{ __('footer_contact') }}</h4>
                        </div>
                        <div class="footer-content">
                            <p>{{ $settings['company_address'] ?? '123 Street, City, Country' }}</p>
                            <ul class="contact-info">
                                <li>
                                    <i class="fa-regular fa-envelope"></i>
                                    <a href="mailto:{{$settings['company_email']}}">{{$settings['company_email']}}</a>
                                </li>
                                <li>
                                    <i class="fa-solid fa-phone-volume"></i>
                                    <a href="tel:{{$settings['company_phone']}}">{{$settings['company_phone']}}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-xl-2 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                    <div class="single-footer-widget">
                        <div class="widget-head">
                            <h4>{{ __('footer_quick_links') }}</h4>
                        </div>
                        <ul class="list-items">
                            <li><a href="{{ url('/') }}">{{ __('footer_home') }}</a></li>
                            <li><a href="{{ url('/cars') }}">{{ __('footer_cars') }}</a></li>
                            <li><a href="{{ url('/about') }}">{{ __('footer_about') }}</a></li>
                            <li><a href="{{ url('/blog') }}">{{ __('footer_blog') }}</a></li>
                            <li><a href="{{ url('/contact') }}">{{ __('footer_contact_us') }}</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Gallery -->
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".6s">
                    <div class="single-footer-widget">
                        <div class="widget-head">
                            <h4>{{ __('footer_gallery') }}</h4>
                        </div>
                        <div class="footer-gallery">
                            <div class="gallery-wrap">
                                @for ($i = 1; $i <= 4; $i++)
                                    <div class="gallery-item">
                                        <div class="thumb">
                                            <a href="{{ asset('assets/images/client/footer/gallery-' . $i . '.jpg') }}" class="img-popup">
                                                <img src="{{ asset('assets/images/client/footer/gallery-' . $i . '.jpg') }}" alt="gallery-img">
                                                <div class="icon"><i class="far fa-plus"></i></div>
                                            </a>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".8s">
                    <div class="single-footer-widget">
                        <div class="widget-head">
                            <h4>{{ __('footer_newsletter') }}</h4>
                        </div>
                        <div class="footer-content">
                            <p>{{ __('footer_newsletter_text') }}</p>
                            <div class="footer-input">
                                <input type="email" id="email2" placeholder="{{ __('footer_email_placeholder') }}">
                                <button class="newsletter-btn" type="submit">
                                    <i class="fa-regular fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-wrapper">
                <p class="wow fadeInUp" data-wow-delay=".4s">
                    © {{ date('Y') }} {{ __('footer_copyright') }} <a href="https://bangicode.ma/">Bangicode.ma</a>
                </p>
            </div>
        </div>
    </div>
</footer>
