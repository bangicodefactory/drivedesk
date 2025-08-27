<!-- Gallery Section Start -->
<div class="gallery-section fix">
    <div class="gallery-wrapper">
        <div class="row g-4">
            <div class="col-xxl-4 col-xl-5 col-lg-5">
                <div class="row g-4">
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <div class="row g-4"></div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6">
                        <div class="gallery-image"></div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-6 col-xl-7 col-lg-7">
                <div class="row g-4">
                    @for($i=1;$i<=5;$i++)
                        <div class="col-md-4 col-sm-6"><div class="gallery-image"></div></div>
                    @endfor
                    <div class="col-md-8"><div class="gallery-image"></div></div>
                </div>
            </div>
            <div class="col-xxl-2 col-xl-4 col-lg-4 col-md-6 col-sm-6">
                <div class="gallery-image style-2">
                    <img src="{{ asset('assets/img/gallery/g-9.jpg') }}" alt="img">
                    <div class="icon-box">
                        <a href="{{ asset('assets/img/gallery/g-9.jpg') }}" class="icon img-popup-2 style-two"></a>
                    </div>
                    <div class="mask"></div>
                    <div class="mask-second"></div>
                </div>
            </div>
        </div>
    </div>
</div>
