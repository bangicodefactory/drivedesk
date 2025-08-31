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
                            <h4>Quick Links</h4>
                        </div>
                        <ul class="list-items">
                            <li>
                                <a href="{{ url('/') }}">
                                    Home
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/cars') }}">
                                    Our Cars
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/about') }}">
                                    About Us
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/blog') }}">
                                    Latest News
                                </a>
                            </li>
                            <li>
                                <a href="{{ url('/contact') }}">
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
                                        <a href="{{ asset('assets/images/client/footer/gallery-1.jpg') }}" class="img-popup">
                                            <img src="{{ asset('assets/images/client/footer/gallery-1.jpg') }}" alt="gallery-img">
                                            <div class="icon">
                                                <i class="far fa-plus"></i>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="thumb">
                                        <a href="{{ asset('assets/images/client/footer/gallery-2.jpg') }}" class="img-popup">
                                            <img src="{{ asset('assets/images/client/footer/gallery-2.jpg') }}" alt="gallery-img">
                                            <div class="icon">
                                                <i class="far fa-plus"></i>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="thumb">
                                        <a href="{{ asset('assets/images/client/footer/gallery-3.jpg') }}" class="img-popup">
                                            <img src="{{ asset('assets/images/client/footer/gallery-3.jpg') }}" alt="gallery-img">
                                            <div class="icon">
                                                <i class="far fa-plus"></i>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="gallery-item">
                                    <div class="thumb">
                                        <a href="{{ asset('assets/images/client/footer/gallery-4.jpg') }}" class="img-popup">
                                            <img src="{{ asset('assets/images/client/footer/gallery-4.jpg') }}" alt="gallery-img">
                                            <div class="icon">
                                                <i class="far fa-plus"></i>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="thumb">
                                        <a href="{{ asset('assets/images/client/footer/gallery-5.jpg') }}" class="img-popup">
                                            <img src="{{ asset('assets/images/client/footer/gallery-5.jpg') }}" alt="gallery-img">
                                            <div class="icon">
                                                <i class="far fa-plus"></i>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="thumb">
                                        <a href="{{ asset('assets/images/client/footer/gallery-6.jpg') }}" class="img-popup">
                                            <img src="{{ asset('assets/images/client/footer/gallery-6.jpg') }}" alt="gallery-img">
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
                    © Copyright {{ date('Y') }} by <a href="{{ url('/') }}">Remons.com</a>
                </p>
            </div>
        </div>
    </div>
</footer>
