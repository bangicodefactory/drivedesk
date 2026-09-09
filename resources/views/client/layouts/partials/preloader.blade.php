@php
    // settings(), not a bare pluck: this returns the acting tenant's rows
    // defaulted from settingsKeys(), so a key the deployment never set is
    // an empty string rather than a missing index. Unguarded reads of
    // company_name here 500'd /contact and /search on any deployment whose
    // branding_seed omits it -- drivedesk's does. The bare pluck was also
    // unscoped, merging every tenant's settings with the last one winning.
    $settings = settings();
@endphp
<!-- Preloader Start -->
<div id="preloader" class="preloader">
    <div class="animation-preloader">
        <div class="spinner"></div>
        <div class="txt-loading">
            @foreach(str_split($settings['company_name']) as $letter)
                <span data-text-preloader="{{ $letter }}" class="letters-loading">{{ $letter }}</span>
            @endforeach
        </div>
        <p class="text-center">Loading</p>
    </div>
    <div class="loader">
        <div class="row">
            <div class="col-3 loader-section section-left"><div class="bg"></div></div>
            <div class="col-3 loader-section section-left"><div class="bg"></div></div>
            <div class="col-3 loader-section section-right"><div class="bg"></div></div>
            <div class="col-3 loader-section section-right"><div class="bg"></div></div>
        </div>
    </div>
</div>
<!-- Back To Top Start -->
<div class="scroll-up">
    <svg class="scroll-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
    </svg>
</div>
