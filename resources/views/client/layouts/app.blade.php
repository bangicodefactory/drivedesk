
<!DOCTYPE html>
<html lang="en">
    <!--<< Header Area >>-->
    <head>
        <!-- ========== Meta Tags ========== -->
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!DOCTYPE html>
        <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
            <head>
                @include('client.layouts.partials.head')
            </head>
            <body class="antialiased">

                @include('client.layouts.partials.preloader')
                @include('client.layouts.partials.offcanvas')
                @include('client.layouts.partials.header-top')
                @include('client.layouts.partials.header')

                <main>
                    @yield('content')
                </main>

                @include('client.layouts.partials.footer')
                @include('client.layouts.partials.scripts')
            </body>
        {{-- </html>
                                    </div>
                                </div>
                                <div class="client-info-items d-flex align-items-center gap-3">
                                    <div class="client-img bg-cover" style="background-image: url('assets/img/testimonial/client-2.png');"></div>
                                    <div class="content">
                                        <h5>
                                            Kevin Martin
                                        </h5>
                                        <span>Customer</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="testimonial-card-items">
                                <div class="testimoni-bg-shape">
                                    <div class="testimonial-items-top">
                                        <div class="icon">
                                            <i class="fa-solid fa-quote-left"></i>
                                        </div>
                                        <p>
                                            I was very impresed by the remons service lorem ipsum is simply free text used by copy typing refreshing. Neque porro est qui dolorem ipsum quia.
                                        </p>
                                        <div class="star">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="client-info-items d-flex align-items-center gap-3">
                                    <div class="client-img bg-cover" style="background-image: url('assets/img/testimonial/client-3.png');"></div>
                                    <div class="content">
                                        <h5>
                                            Jessica Brown
                                        </h5>
                                        <span>Customer</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section Start -->
        <div class="gallery-section fix">
            <div class="gallery-wrapper">
                <div class="row g-4">
                    <div class="col-xxl-4 col-xl-5 col-lg-5">
                        <div class="row g-4">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                               <div class="row g-4">
                                    <div class="col-md-12">
                                        <div class="gallery-image">
                                            <img src="assets/img/gallery/g-1.jpg" alt="img">
                                            <div class="icon-box">
                                                <a href="assets/img/gallery/g-1.jpg" class="icon img-popup-2">
                                                    <i class="fa-solid fa-plus"></i>
                                                </a>
                                            </div>
                                            <div class="mask"></div>
                                            <div class="mask-second"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="gallery-image">
                                            <img src="assets/img/gallery/g-2.jpg" alt="img">
                                            <div class="icon-box">
                                                <a href="assets/img/gallery/g-2.jpg" class="icon img-popup-2">
                                                    <i class="fa-solid fa-plus"></i>
                                                </a>
                                            </div>
                                            <div class="mask"></div>
                                            <div class="mask-second"></div>
                                        </div>
                                    </div>
                               </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="gallery-image">
                                    <img src="assets/img/gallery/g-3.jpg" alt="img">
                                    <div class="icon-box">
                                        <a href="assets/img/gallery/g-3.jpg" class="icon img-popup-2 style-two">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="mask"></div>
                                    <div class="mask-second"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6 col-xl-7 col-lg-7">
                        <div class="row g-4">
                            <div class="col-md-4 col-sm-6">
                                <div class="gallery-image">
                                    <img src="assets/img/gallery/g-4.jpg" alt="img">
                                    <div class="icon-box">
                                        <a href="assets/img/gallery/g-4.jpg" class="icon img-popup-2">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="mask"></div>
                                    <div class="mask-second"></div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="gallery-image">
                                    <img src="assets/img/gallery/g-5.jpg" alt="img">
                                    <div class="icon-box">
                                        <a href="assets/img/gallery/g-5.jpg" class="icon img-popup-2">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="mask"></div>
                                    <div class="mask-second"></div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="gallery-image">
                                    <img src="assets/img/gallery/g-6.jpg" alt="img">
                                    <div class="icon-box">
                                        <a href="assets/img/gallery/g-6.jpg" class="icon img-popup-2">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="mask"></div>
                                    <div class="mask-second"></div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="gallery-image">
                                    <img src="assets/img/gallery/g-7.jpg" alt="img">
                                    <div class="icon-box">
                                        <a href="assets/img/gallery/g-7.jpg" class="icon img-popup-2">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="mask"></div>
                                    <div class="mask-second"></div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="gallery-image">
                                    <img src="assets/img/gallery/g-8.jpg" alt="img">
                                    <div class="icon-box">
                                        <a href="assets/img/gallery/g-8.jpg" class="icon img-popup-2">
                                            <i class="fa-solid fa-plus"></i>
                                        </a>
                                    </div>
                                    <div class="mask"></div>
                                    <div class="mask-second"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                        <div class="gallery-image style-2">
                            <img src="assets/img/gallery/g-9.jpg" alt="img">
                            <div class="icon-box">
                                <a href="assets/img/gallery/g-9.jpg" class="icon img-popup-2 style-two">
                                    <i class="fa-solid fa-plus"></i>
                                </a>
                            </div>
                            <div class="mask"></div>
                            <div class="mask-second"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- News Section Start -->
        <section class="news-section section-padding fix">
            <div class="container">
                <div class="section-title text-center">
                    <img src="assets/img/sub-icon.png" alt="icon-img" class="wow fadeInUp">
                    <span class="wow fadeInUp" data-wow-delay=".2s">From the Blog</span>
                    <h2 class="wow fadeInUp" data-wow-delay=".4s">
                        Latest News & <br>
                        Articles From the Blog
                    </h2>
                </div>
                <div class="row">
                    <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".3s">
                        <div class="news-card-items">
                            <div class="news-image">
                                <img src="assets/img/news/01.jpg" alt="news-img">
                                <div class="post-date">
                                    <h6>
                                        20 <br>
                                        Mar
                                    </h6>
                                </div>
                            </div>
                            <div class="news-content">
                                <div class="post-client">
                                    <img src="assets/img/news/client.png" alt="img">
                                </div>
                                <div class="news-cont">
                                    <span>by Mike Hardson</span>
                                    <h3><a href="news-details.html">The best fastest and most powerful road car</a></h3>
                                    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem…</p>
                                </div>
                                <ul>
                                    <li>
                                        <i class="fa-solid fa-comments"></i>
                                        2 Comments
                                    </li>
                                    <li>
                                       <a href="news-details.html">
                                            <i class="fa-solid fa-arrow-right-long"></i>
                                            More
                                       </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".5s">
                        <div class="news-card-items">
                            <div class="news-image">
                                <img src="assets/img/news/02.jpg" alt="news-img">
                                <div class="post-date">
                                    <h6>
                                        26 <br>
                                        Mar
                                    </h6>
                                </div>
                            </div>
                            <div class="news-content">
                                <div class="post-client">
                                    <img src="assets/img/news/client.png" alt="img">
                                </div>
                                <div class="news-cont">
                                    <span>by Mike Hardson</span>
                                    <h3><a href="news-details.html">The best fastest and most powerful road car</a></h3>
                                    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem…</p>
                                </div>
                                <ul>
                                    <li>
                                        <i class="fa-solid fa-comments"></i>
                                        2 Comments
                                    </li>
                                    <li>
                                       <a href="news-details.html">
                                            <i class="fa-solid fa-arrow-right-long"></i>
                                            More
                                       </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".7s">
                        <div class="news-card-items">
                            <div class="news-image">
                                <img src="assets/img/news/03.jpg" alt="news-img">
                                <div class="post-date">
                                    <h6>
                                        29 <br>
                                        Mar
                                    </h6>
                                </div>
                            </div>
                            <div class="news-content">
                                <div class="post-client">
                                    <img src="assets/img/news/client.png" alt="img">
                                </div>
                                <div class="news-cont">
                                    <span>by Mike Hardson</span>
                                    <h3><a href="news-details.html">The best fastest and most powerful road car</a></h3>
                                    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem…</p>
                                </div>
                                <ul>
                                    <li>
                                        <i class="fa-solid fa-comments"></i>
                                        2 Comments
                                    </li>
                                    <li>
                                       <a href="news-details.html">
                                            <i class="fa-solid fa-arrow-right-long"></i>
                                            More
                                       </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cta Rental Section Start -->
        <section class="cta-rental-section bg-cover fix section-padding" style="background-image: url('assets/img/cta/bg-app.jpg');">
            <div class="container">
                <div class="row g-4 justify-content-between align-items-center">
                    <div class="col-lg-6">
                        <div class="cta-rental-items">
                            <h4 class="wow fadeInUp" data-wow-delay=".3s">Faster, easier access to car rental services</h4>
                            <h2 class="wow fadeInUp" data-wow-delay=".5s">Premium Car Rental</h2>
                            <div class="rental-app-button">
                                <a href="index.html" class="app-button-items wow fadeInUp" data-wow-delay=".7s">
                                    <span class="button-icon"><i class="fa-solid fa-play"></i></span>
                                    <span class="button-text">
                                        <span class="text">Get in</span> <br>
                                        <span class="headding-text">Google Play</span>
                                    </span>
                                </a>
                                <a href="index.html" class="app-button-items style-2 wow fadeInUp" data-wow-delay=".8s">
                                    <span class="button-icon"><i class="fa-brands fa-apple"></i></span>
                                    <span class="button-text">
                                        <span class="text">Get in</span> <br>
                                        <span class="headding-text">Play Store</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 wow fadeInUp" data-wow-delay=".4s">
                        <div class="mobile-remons-image">
                            <img src="assets/img/mobile-remons.png" alt="img">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cta Cheap Rental Section Start -->
        <section class="cta-cheap-rental-section">
            <div class="container">
                <div class="cta-cheap-rental">
                    <div class="cta-cheap-rental-left wow fadeInUp" data-wow-delay="
                    .3s">
                        <div class="logo-thumb">
                            <a href="index.html">
                                <img src="assets/img/logo/white-logo.svg" alt="logo-img">
                            </a>
                        </div>
                        <h4 class="text-white">Save big with our cheap car rental</h4>
                    </div>
                    <div class="social-icon d-flex align-items-center wow fadeInUp" data-wow-delay="
                    .5s">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                        <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer Section Start -->
        <footer class="footer-section fix">
            <div class="container">
                <div class="footer-widgets-wrapper">
                    <div class="row justify-content-between">
                        <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                            <div class="single-footer-widget shape-map">
                                <div class="widget-head">
                                    <h4>Contact</h4>
                                </div>
                                <div class="footer-content">
                                    <p>
                                        66 Road Broklyn Golden Street, 600
                                        New York, USA
                                    </p>
                                    <ul class="contact-info">
                                        <li>
                                            <i class="fa-regular fa-envelope"></i>
                                            <a href="mailto:needhelp@company.com">needhelp@company.com</a>
                                        </li>
                                        <li>
                                            <i class="fa-solid fa-phone-volume"></i>
                                            <a href="tel:926668880000">+92 (666) 888 0000</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                            <div class="single-footer-widget">
                                <div class="widget-head">
                                    <h4>Contact</h4>
                                </div>
                                <ul class="list-items">
                                    <li>
                                        <a href="about.html">
                                            About Us
                                        </a>
                                    </li>
                                    <li>
                                        <a href="car-details.html">
                                            New Cars
                                        </a>
                                    </li>
                                    <li>
                                        <a href="news-details.html">
                                            Latest News
                                        </a>
                                    </li>
                                    <li>
                                        <a href="gallery.html">
                                            Gallery
                                        </a>
                                    </li>
                                    <li>
                                        <a href="contact.html">
                                            Contact
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".6s">
                            <div class="single-footer-widget">
                                <div class="widget-head">
                                    <h4>Gallery</h4>
                                </div>
                                <div class="footer-gallery">
                                    <div class="gallery-wrap">
                                        <div class="gallery-item">
                                            <div class="thumb">
                                                <a href="assets/img/footer/gallery-1.jpg" class="img-popup">
                                                    <img src="assets/img/footer/gallery-1.jpg" alt="gallery-img">
                                                    <div class="icon">
                                                        <i class="far fa-plus"></i>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="thumb">
                                                <a href="assets/img/footer/gallery-2.jpg" class="img-popup">
                                                    <img src="assets/img/footer/gallery-2.jpg" alt="gallery-img">
                                                    <div class="icon">
                                                        <i class="far fa-plus"></i>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="thumb">
                                                <a href="assets/img/footer/gallery-3.jpg" class="img-popup">
                                                    <img src="assets/img/footer/gallery-3.jpg" alt="gallery-img">
                                                    <div class="icon">
                                                        <i class="far fa-plus"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="gallery-item">
                                            <div class="thumb">
                                                <a href="assets/img/footer/gallery-4.jpg" class="img-popup">
                                                    <img src="assets/img/footer/gallery-4.jpg" alt="gallery-img">
                                                    <div class="icon">
                                                        <i class="far fa-plus"></i>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="thumb">
                                                <a href="assets/img/footer/gallery-5.jpg" class="img-popup">
                                                    <img src="assets/img/footer/gallery-5.jpg" alt="gallery-img">
                                                    <div class="icon">
                                                        <i class="far fa-plus"></i>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="thumb">
                                                <a href="assets/img/footer/gallery-6.jpg" class="img-popup">
                                                    <img src="assets/img/footer/gallery-6.jpg" alt="gallery-img">
                                                    <div class="icon">
                                                        <i class="far fa-plus"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".8s">
                            <div class="single-footer-widget">
                                <div class="widget-head">
                                    <h4>Newsletter</h4>
                                </div>
                                <div class="footer-content">
                                    <p>Subscribe our newsletter to get our latest update & news</p>
                                    <div class="footer-input">
                                        <input type="email" id="email2" placeholder="Email address">
                                        <button class="newsletter-btn" type="submit">
                                            <i class="fa-regular fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom">
                    <div class="footer-wrapper">
                        <p class="wow fadeInUp" data-wow-delay=".4s">
                            © Copyright 2024 by <a href="index.html">Remons.com</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer> --}}

        <!--<< All JS Plugins >>-->
        <script src="assets/js/client/jquery-3.7.1.min.js"></script>
        <!--<< Viewport Js >>-->
        <script src="assets/js/client/viewport.jquery.js"></script>
        <!--<< Bootstrap Js >>-->
        <script src="assets/js/client/bootstrap.bundle.min.js"></script>
        <!--<< Nice Select Js >>-->
        <script src="assets/js/client/jquery.nice-select.min.js"></script>

        <!--<< Waypoints Js >>-->
        <script src="assets/js/client/jquery.waypoints.js"></script>
        <!--<< Counterup Js >>-->
        <script src="assets/js/client/jquery.counterup.min.js"></script>
        <!--<< Datepicker Js >>-->
        <script src="assets/js/client/bootstrap-datepicker.js"></script>
        <!--<< Swiper Slider Js >>-->
        <script src="assets/js/client/swiper-bundle.min.js"></script>
        <!--<< MeanMenu Js >>-->
        <script src="assets/js/client/jquery.meanmenu.min.js"></script>
        <!--<< Magnific Popup Js >>-->
        <script src="assets/js/client/jquery.magnific-popup.min.js"></script>
        <!--<< GSAP Animation Js >>-->
        <script src="assets/js/client/animation.js"></script>
        <!--<< Wow Animation Js >>-->
        <script src="assets/js/client/wow.min.js"></script>
        <!--<< Main.js >>-->
        <script src="assets/js/client/main.js"></script>
    </body>
</html>
