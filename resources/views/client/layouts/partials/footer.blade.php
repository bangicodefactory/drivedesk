<!-- Footer Section Start -->
<footer class="footer-section fix">
    <div class="container">
        <div class="footer-widgets-wrapper">
            <div class="row justify-content-between">
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                    <div class="single-footer-widget shape-map">
                        <div class="widget-head">
                            <h5>About</h5>
                        </div>
                        <div class="footer-content">
                            <p>Premium car rental service providing quality vehicles.</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                    <div class="single-footer-widget">
                        <div class="widget-head">
                            <h5>Links</h5>
                        </div>
                        <ul class="list-items">
                            <li><a href="{{ url('/') }}">Home</a></li>
                            <li><a href="#">Cars</a></li>
                            <li><a href="#">About</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".6s">
                    <div class="single-footer-widget">
                        <div class="widget-head">
                            <h5>Gallery</h5>
                        </div>
                        <div class="footer-gallery">
                            <div class="row g-2">
                                @for($i=1;$i<=6;$i++)

<div class="col-4">
                                <img src="{{ asset('assets/img/car/0'.($i <= 4 ? $i : $i - 2).'.jpg') }}" alt="car" class="img-fluid">
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".8s">
                    <div class="single-footer-widget">
                        <div class="widget-head">
                            <h5>Newsletter</h5>
                        </div>
                        <div class="footer-content">
                            <p>Subscribe to get latest updates.</p>
                            <form action="{{ route('newsletter.subscribe') }}" method="post">
                                @csrf
                                <div class="input-group mb-2">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                    <button class="theme-btn" type="submit"><i class="fa fa-paper-plane"></i></button>
                                </div>
                            </form>
                            <div class="social-icon d-flex align-items-center mt-3">
                                <a href="#"><i class="fab fa-facebook-f"></i></a>
                                <a href="#"><i class="fab fa-twitter"></i></a>
                                <a href="#"><i class="fa-brands fa-linkedin-in"></i></a>
                                <a href="#"><i class="fa-brands fa-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-wrapper">
                <p class="wow fadeInUp" data-wow-delay=".4s">© {{ date('Y') }} <a href="{{ url('/') }}">Remons.com</a></p>
            </div>
        </div>
    </div>
</footer>
